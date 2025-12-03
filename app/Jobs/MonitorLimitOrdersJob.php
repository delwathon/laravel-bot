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
        //
    }

    public function handle(): void
    {
        try {
            // Get all pending trades (limit orders not yet filled)
            $pendingTrades = Trade::where('status', 'pending')
                ->whereNotNull('exchange_order_id')
                ->with(['user', 'exchangeAccount'])
                ->get();

            if ($pendingTrades->isEmpty()) {
                return;
            }

            Log::info("Monitoring {$pendingTrades->count()} pending limit orders");

            $filled = 0;
            $stillPending = 0;
            $cancelled = 0;

            foreach ($pendingTrades as $trade) {
                try {
                    $this->checkAndFillOrder($trade);
                    
                    // Reload to get updated status
                    $trade->refresh();
                    
                    if ($trade->status === 'open') {
                        $filled++;
                    } elseif ($trade->status === 'pending') {
                        $stillPending++;
                    } elseif ($trade->status === 'cancelled') {
                        $cancelled++;
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to check limit order {$trade->id}: " . $e->getMessage());
                }
            }

            Log::info("Limit order monitoring completed", [
                'filled' => $filled,
                'still_pending' => $stillPending,
                'cancelled' => $cancelled,
            ]);

        } catch (\Exception $e) {
            Log::error('Limit order monitoring job failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function checkAndFillOrder(Trade $trade)
    {
        // Determine which credentials to use
        if ($trade->user->is_admin) {
            $adminAccount = ExchangeAccount::getBybitAccount();
            if (!$adminAccount) {
                Log::warning("No admin account for trade {$trade->id}");
                return;
            }
            $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
            $exchangeAccountId = $adminAccount->id;
        } else {
            if (!$trade->exchangeAccount) {
                Log::warning("No exchange account for trade {$trade->id}");
                return;
            }
            $bybit = new BybitService($trade->exchangeAccount->api_key, $trade->exchangeAccount->api_secret);
            $exchangeAccountId = $trade->exchangeAccount->id;
        }

        // Check order status from Bybit
        $orderStatus = $bybit->getOrderStatus($trade->symbol, $trade->exchange_order_id);

        if (!$orderStatus) {
            Log::warning("Could not fetch order status for trade {$trade->id}, order {$trade->exchange_order_id}");
            return;
        }

        // Handle different order statuses
        $status = $orderStatus['orderStatus'] ?? null;

        if ($status === 'Filled') {
            // Order has been filled - get execution price
            $executionPrice = $orderStatus['avgPrice'] ?? $trade->entry_price;

            DB::beginTransaction();

            try {
                // Update trade
                $trade->update([
                    'entry_price' => $executionPrice,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);

                // Create position
                Position::create([
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

                DB::commit();

                Log::info("Limit order filled for trade {$trade->id}", [
                    'user_id' => $trade->user_id,
                    'symbol' => $trade->symbol,
                    'execution_price' => $executionPrice,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to process filled order for trade {$trade->id}: " . $e->getMessage());
                throw $e;
            }

        } elseif ($status === 'Cancelled' || $status === 'Rejected') {
            // Order was cancelled or rejected
            $trade->update([
                'status' => 'cancelled',
            ]);

            Log::info("Limit order cancelled/rejected for trade {$trade->id}", [
                'user_id' => $trade->user_id,
                'bybit_status' => $status,
            ]);

        } elseif (in_array($status, ['New', 'PartiallyFilled', 'Untriggered'])) {
            // Order is still active/pending - do nothing
            Log::debug("Limit order still pending for trade {$trade->id}, status: {$status}");

        } else {
            // Unknown status
            Log::warning("Unknown order status '{$status}' for trade {$trade->id}");
        }
    }
}