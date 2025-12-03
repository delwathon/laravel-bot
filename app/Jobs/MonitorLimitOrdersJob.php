<?php

namespace App\Jobs;

use App\Models\Trade;
use App\Models\Position;
use App\Models\ExchangeAccount;
use App\Services\BybitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MonitorLimitOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public function __construct()
    {
        Log::info('[MonitorLimitOrdersJob] Job instance created');
    }

    public function handle(): void
    {
        Log::info('[MonitorLimitOrdersJob] Starting execution');
        
        try {
            Log::info('[MonitorLimitOrdersJob] Querying for pending trades');
            
            // Get all pending trades (limit orders not yet filled)
            $pendingTrades = Trade::where('status', 'pending')
                ->whereNotNull('exchange_order_id')
                ->with(['user', 'exchangeAccount'])
                ->get();

            Log::info('[MonitorLimitOrdersJob] Query completed', [
                'pending_trades_count' => $pendingTrades->count()
            ]);

            if ($pendingTrades->isEmpty()) {
                Log::info('[MonitorLimitOrdersJob] No pending trades to monitor');
                return;
            }

            Log::info("[MonitorLimitOrdersJob] Monitoring {$pendingTrades->count()} pending limit orders");

            $filled = 0;
            $stillPending = 0;
            $cancelled = 0;

            foreach ($pendingTrades as $trade) {
                Log::info("[MonitorLimitOrdersJob] Checking trade {$trade->id}", [
                    'user_id' => $trade->user_id,
                    'symbol' => $trade->symbol,
                    'exchange_order_id' => $trade->exchange_order_id
                ]);
                
                try {
                    $this->checkAndFillOrder($trade);
                    
                    // Reload to get updated status
                    $trade->refresh();
                    
                    Log::info("[MonitorLimitOrdersJob] Trade {$trade->id} status after check: {$trade->status}");
                    
                    if ($trade->status === 'open') {
                        $filled++;
                    } elseif ($trade->status === 'pending') {
                        $stillPending++;
                    } elseif ($trade->status === 'cancelled') {
                        $cancelled++;
                    }

                } catch (\Exception $e) {
                    Log::error("[MonitorLimitOrdersJob] Failed to check limit order {$trade->id}", [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
            }

            Log::info("[MonitorLimitOrdersJob] Limit order monitoring completed", [
                'filled' => $filled,
                'still_pending' => $stillPending,
                'cancelled' => $cancelled,
            ]);

        } catch (\Exception $e) {
            Log::error('[MonitorLimitOrdersJob] Critical exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function checkAndFillOrder(Trade $trade)
    {
        Log::info("[MonitorLimitOrdersJob] checkAndFillOrder() called for trade {$trade->id}");
        
        // Determine which credentials to use
        if ($trade->user->is_admin) {
            Log::info("[MonitorLimitOrdersJob] Trade {$trade->id} belongs to admin user");
            
            $adminAccount = ExchangeAccount::getBybitAccount();
            if (!$adminAccount) {
                Log::warning("[MonitorLimitOrdersJob] No admin account found for trade {$trade->id}");
                return;
            }
            
            Log::info("[MonitorLimitOrdersJob] Using admin account ID {$adminAccount->id}");
            $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
            $exchangeAccountId = $adminAccount->id;
        } else {
            Log::info("[MonitorLimitOrdersJob] Trade {$trade->id} belongs to regular user");
            
            if (!$trade->exchangeAccount) {
                Log::warning("[MonitorLimitOrdersJob] No exchange account found for trade {$trade->id}");
                return;
            }
            
            Log::info("[MonitorLimitOrdersJob] Using exchange account ID {$trade->exchangeAccount->id}");
            $bybit = new BybitService($trade->exchangeAccount->api_key, $trade->exchangeAccount->api_secret);
            $exchangeAccountId = $trade->exchangeAccount->id;
        }

        // Check order status from Bybit
        Log::info("[MonitorLimitOrdersJob] Fetching order status from Bybit for trade {$trade->id}");
        $orderStatus = $bybit->getOrderStatus($trade->symbol, $trade->exchange_order_id);

        if (!$orderStatus) {
            Log::warning("[MonitorLimitOrdersJob] Could not fetch order status for trade {$trade->id}, order {$trade->exchange_order_id}");
            return;
        }

        // Handle different order statuses
        $status = $orderStatus['orderStatus'] ?? null;
        
        Log::info("[MonitorLimitOrdersJob] Bybit order status received", [
            'trade_id' => $trade->id,
            'bybit_status' => $status
        ]);

        if ($status === 'Filled') {
            Log::info("[MonitorLimitOrdersJob] Order filled for trade {$trade->id}");
            
            // Order has been filled - get execution price
            $executionPrice = $orderStatus['avgPrice'] ?? $trade->entry_price;
            
            Log::info("[MonitorLimitOrdersJob] Execution price: {$executionPrice}");

            DB::beginTransaction();

            try {
                Log::info("[MonitorLimitOrdersJob] Starting database transaction for trade {$trade->id}");
                
                // Update trade
                $trade->update([
                    'entry_price' => $executionPrice,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);
                
                Log::info("[MonitorLimitOrdersJob] Trade {$trade->id} updated to 'open' status");

                // Create position
                $position = Position::create([
                    'user_id' => $trade->user_id,
                    'trade_id' => $trade->id,
                    'exchange_account_id' => $exchangeAccountId,
                    'symbol' => $trade->symbol,
                    'exchange' => 'bybit',
                    'side' => $trade->type,
                    'entry_price' => $executionPrice,
                    'current_price' => $executionPrice,
                    'quantity' => $trade->quantity,
                    'leverage' => $trade->leverage,
                    'stop_loss' => $trade->stop_loss,
                    'take_profit' => $trade->take_profit,
                    'is_active' => true,
                    'last_updated_at' => now(),
                ]);
                
                Log::info("[MonitorLimitOrdersJob] Position created with ID {$position->id}");

                DB::commit();
                
                Log::info("[MonitorLimitOrdersJob] Database transaction committed");

                Log::info("[MonitorLimitOrdersJob] Limit order filled for trade {$trade->id}", [
                    'user_id' => $trade->user_id,
                    'symbol' => $trade->symbol,
                    'execution_price' => $executionPrice,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("[MonitorLimitOrdersJob] Failed to process filled order for trade {$trade->id}", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                throw $e;
            }

        } elseif ($status === 'Cancelled' || $status === 'Rejected') {
            Log::info("[MonitorLimitOrdersJob] Order cancelled/rejected for trade {$trade->id}");
            
            // Order was cancelled or rejected
            $trade->update([
                'status' => 'cancelled',
            ]);

            Log::info("[MonitorLimitOrdersJob] Limit order cancelled/rejected for trade {$trade->id}", [
                'user_id' => $trade->user_id,
                'bybit_status' => $status,
            ]);

        } elseif (in_array($status, ['New', 'PartiallyFilled', 'Untriggered'])) {
            // Order is still active/pending - do nothing
            Log::debug("[MonitorLimitOrdersJob] Limit order still pending for trade {$trade->id}, status: {$status}");

        } else {
            // Unknown status
            Log::warning("[MonitorLimitOrdersJob] Unknown order status '{$status}' for trade {$trade->id}");
        }
    }
}