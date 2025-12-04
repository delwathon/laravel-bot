<?php

namespace App\Services;

use App\Models\Signal;
use App\Models\User;
use App\Models\Trade;
use App\Models\Position;
use App\Models\Setting;
use App\Models\ExchangeAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TradePropagationService
{
    protected $tradeExecutionService;

    public function __construct(TradeExecutionService $tradeExecutionService)
    {
        $this->tradeExecutionService = $tradeExecutionService;
    }

    /**
     * Execute trade for admin FIRST
     * Returns success/failure - DON'T create any records if admin fails
     */
    public function executeAdminTrade(Signal $signal, $positionSizePercent = 5, $orderType = 'Market')
    {
        Log::info("[TradePropagationService] Executing admin trade for signal {$signal->id} as {$orderType} Order");
        
        try {
            // Get admin account
            $adminAccount = ExchangeAccount::where('is_admin', true)
                ->where('is_active', true)
                ->first();
            
            if (!$adminAccount) {
                return [
                    'success' => false,
                    'error' => 'No active admin account found',
                    'trade' => null
                ];
            }
            
            $adminUser = $adminAccount->user;
            
            if (!$adminUser) {
                return [
                    'success' => false,
                    'error' => 'Admin user not found',
                    'trade' => null
                ];
            }
            
            Log::info("[TradePropagationService] Admin account found", [
                'user_id' => $adminUser->id,
                'account_id' => $adminAccount->id
            ]);
            
            // Execute trade using TradeExecutionService
            $result = $this->tradeExecutionService->executeTradeForUser($signal, $adminUser);
            
            if ($result['success']) {
                Log::info("[TradePropagationService] Admin trade executed successfully", [
                    'trade_id' => $result['trade']->id,
                    'actual_price' => $result['actual_execution_price'] ?? $signal->entry_price
                ]);
                
                return [
                    'success' => true,
                    'trade' => $result['trade'],
                    'position' => $result['position'] ?? null,
                    'error' => null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Unknown error',
                    'trade' => null
                ];
            }
            
        } catch (\Exception $e) {
            Log::error("[TradePropagationService] Admin trade execution exception", [
                'signal_id' => $signal->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'trade' => null
            ];
        }
    }

    /**
     * Propagate signal to all users (called ONLY after admin succeeds)
     */
    public function propagateSignalToAllUsers(Signal $signal, $orderType = 'Market')
    {
        $users = User::where('is_admin', false)
            ->whereHas('exchangeAccount', function($query) {
                $query->where('is_active', true)->where('is_admin', false);
            })
            ->get();

        $results = [
            'total' => $users->count(),
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($users as $user) {
            try {
                $this->executeTradeForUser($signal, $user, $orderType);
                $results['successful']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $errorMessage = $e->getMessage();
                
                $results['errors'][] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'error' => $errorMessage,
                ];
                
                Log::error("Failed to propagate signal to user {$user->id}: " . $errorMessage);
                
                // Create failed trade record with failure reason
                try {
                    Trade::create([
                        'user_id' => $user->id,
                        'signal_id' => $signal->id,
                        'exchange_account_id' => $user->exchangeAccount->id ?? null,
                        'symbol' => $signal->symbol,
                        'exchange' => 'bybit',
                        'type' => $signal->type,
                        'order_type' => $orderType,
                        'entry_price' => $signal->entry_price,
                        'stop_loss' => $signal->stop_loss,
                        'take_profit' => $signal->take_profit,
                        'quantity' => 0,
                        'leverage' => 1,
                        'status' => 'failed',
                        'failure_reason' => $errorMessage,
                    ]);
                } catch (\Exception $createException) {
                    Log::error("Failed to create failed trade record for user {$user->id}: " . $createException->getMessage());
                }
            }
        }

        Log::info("Signal propagation completed", [
            'signal_id' => $signal->id,
            'total_users' => $results['total'],
            'successful' => $results['successful'],
            'failed' => $results['failed'],
        ]);

        return $results;
    }

    protected function executeTradeForUser(Signal $signal, User $user, $orderType = 'Market')
    {
        $exchangeAccount = $user->exchangeAccount;

        if (!$exchangeAccount || !$exchangeAccount->isConnected()) {
            throw new \Exception("User {$user->id} has no active exchange account");
        }

        $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
        $balance = $bybit->getBalance();

        if ($balance <= 0) {
            throw new \Exception("Insufficient balance for user {$user->id}");
        }

        // Get instrument info to validate quantity rules
        $instrumentInfo = $bybit->getInstrumentInfo($signal->symbol);

        // Get position size percentage from settings
        $positionSizePercent = Setting::get('signal_position_size', 5);
        
        // Get leverage from settings
        $leverageSetting = Setting::get('signal_leverage', 'Max');
        $leverage = $this->calculateLeverage($leverageSetting, $signal->symbol, $bybit);

        // Calculate quantity
        $riskAmount = ($balance * $positionSizePercent) / 100;
        $stopLossDistance = abs($signal->entry_price - $signal->stop_loss);
        
        if ($stopLossDistance <= 0) {
            throw new \Exception("Invalid stop loss distance for symbol {$signal->symbol}");
        }
        
        $quantity = $riskAmount / $stopLossDistance;

        // Adjust quantity to meet Bybit's requirements
        $quantity = $this->adjustQuantityToInstrument($quantity, $instrumentInfo);

        DB::beginTransaction();

        try {
            // Create trade record with signal_id linkage
            $trade = Trade::create([
                'user_id' => $user->id,
                'signal_id' => $signal->id,
                'exchange_account_id' => $exchangeAccount->id,
                'symbol' => $signal->symbol,
                'exchange' => 'bybit',
                'type' => $signal->type,
                'order_type' => $orderType,
                'entry_price' => $signal->entry_price,
                'stop_loss' => $signal->stop_loss,
                'take_profit' => $signal->take_profit,
                'quantity' => $quantity,
                'leverage' => $leverage,
                'status' => 'pending',
            ]);

            // Execute trade on Bybit with proper order type
            $side = $signal->type === 'long' ? 'Buy' : 'Sell';
            
            // For Limit orders, use entry_price as the limit price
            $limitPrice = ($orderType === 'Limit') ? $signal->entry_price : null;
            
            $orderResult = $bybit->placeOrder(
                $signal->symbol,
                $side,
                $quantity,
                $orderType,
                $limitPrice,
                $signal->stop_loss,
                $signal->take_profit,
                $leverage
            );

            if (!$orderResult || !isset($orderResult['orderId'])) {
                throw new \Exception('Failed to place order on Bybit');
            }

            $orderId = $orderResult['orderId'];
            Log::info("Order placed for user {$user->id}: Order ID {$orderId}");

            if ($orderType === 'Market') {
                // For Market orders: Get actual execution price
                $actualExecutionPrice = $bybit->waitForOrderFillAndGetPrice($signal->symbol, $orderId, 10, 500);
                
                if ($actualExecutionPrice === null || $actualExecutionPrice <= 0) {
                    Log::warning("Could not get execution price for order {$orderId}, falling back to signal entry price");
                    $actualExecutionPrice = $signal->entry_price;
                }

                Log::info("Actual execution price for user {$user->id}: {$actualExecutionPrice}");

                // Update trade with actual execution price and create position
                $trade->update([
                    'exchange_order_id' => $orderId,
                    'entry_price' => $actualExecutionPrice,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);

                // Create position immediately for Market orders
                Position::create([
                    'user_id' => $user->id,
                    'trade_id' => $trade->id,
                    'exchange_account_id' => $exchangeAccount->id,
                    'symbol' => $signal->symbol,
                    'exchange' => 'bybit',
                    'side' => $signal->type,
                    'entry_price' => $actualExecutionPrice,
                    'current_price' => $actualExecutionPrice,
                    'quantity' => $quantity,
                    'leverage' => $leverage,
                    'stop_loss' => $signal->stop_loss,
                    'take_profit' => $signal->take_profit,
                    'is_active' => true,
                    'last_updated_at' => now(),
                ]);

            } else {
                // For Limit orders: Keep as pending until filled
                $trade->update([
                    'exchange_order_id' => $orderId,
                    'status' => 'pending',
                ]);

                Log::info("Limit order placed for user {$user->id}, awaiting fill");
            }

            DB::commit();
            
            Log::info("Trade executed for user {$user->id}: {$signal->symbol} {$signal->type} with {$leverage}x leverage", [
                'trade_id' => $trade->id,
                'order_id' => $orderId,
                'order_type' => $orderType,
            ]);

            return [
                'success' => true,
                'trade' => $trade,
                'position' => isset($position) ? $position : null,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Trade execution failed for user {$user->id}: " . $e->getMessage());
            
            if (isset($trade)) {
                $trade->update([
                    'status' => 'failed',
                    'failure_reason' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    public function propagateManualTrade($symbol, $type, $entryPrice, $stopLoss, $takeProfit, $positionSizePercent = 5, $orderType = 'Market')
    {
        DB::beginTransaction();
        
        try {
            // Create the signal
            $signal = Signal::create([
                'symbol' => $symbol,
                'exchange' => 'bybit',
                'type' => $type,
                'order_type' => $orderType,
                'timeframe' => Setting::get('signal_primary_timeframe', '15'),
                'pattern' => 'Manual Trade',
                'confidence' => 100,
                'entry_price' => $entryPrice,
                'stop_loss' => $stopLoss,
                'take_profit' => $takeProfit,
                'risk_reward_ratio' => abs(($takeProfit - $entryPrice) / ($entryPrice - $stopLoss)),
                'position_size_percent' => $positionSizePercent,
                'status' => 'pending',
            ]);

            // Execute admin trade FIRST - if this fails, nothing propagates
            $adminResult = $this->executeAdminTrade($signal, $positionSizePercent, $orderType);
            
            if (!$adminResult['success']) {
                // Mark signal as failed and rollback
                $signal->update([
                    'status' => 'failed',
                    'notes' => 'Admin execution failed: ' . $adminResult['error']
                ]);
                
                DB::commit(); // Commit the signal with failed status
                
                throw new \Exception('Failed to execute admin trade: ' . $adminResult['error']);
            }

            // Admin succeeded - propagate to users
            $userResults = $this->propagateSignalToAllUsers($signal, $orderType);

            // Update signal status
            $signal->update([
                'status' => 'executed',
                'executed_at' => now(),
            ]);

            DB::commit();

            return [
                'admin_trade' => $adminResult['trade'],
                'total' => $userResults['total'],
                'successful' => $userResults['successful'],
                'failed' => $userResults['failed'],
                'errors' => $userResults['errors'],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual trade propagation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate leverage based on setting
     */
    protected function calculateLeverage($leverageSetting, $symbol, $bybit)
    {
        if (strtolower($leverageSetting) === 'max') {
            return $bybit->getMaxLeverage($symbol);
        }
        
        return (float) $leverageSetting;
    }

    /**
     * Adjust quantity to meet instrument requirements
     */
    protected function adjustQuantityToInstrument($quantity, $instrumentInfo)
    {
        $minQty = $instrumentInfo['minOrderQty'];
        $maxQty = $instrumentInfo['maxOrderQty'];
        $qtyStep = $instrumentInfo['qtyStep'];
        
        // Ensure quantity is within min/max bounds
        if ($quantity < $minQty) {
            $quantity = $minQty;
        }
        
        if ($quantity > $maxQty) {
            $quantity = $maxQty;
        }
        
        // Round to nearest valid step
        $adjusted = floor($quantity / $qtyStep) * $qtyStep;
        
        // Ensure we didn't round below minimum
        if ($adjusted < $minQty) {
            $adjusted = $minQty;
        }
        
        // Get decimal places from qtyStep
        $decimals = strlen(substr(strrchr((string)$qtyStep, "."), 1));
        $adjusted = round($adjusted, $decimals);
        
        return $adjusted;
    }

    /**
     * Close all positions by signal_id (admin + all users)
     */
    public function closeAllPositionsBySignal($signalId)
    {
        Log::info("[TradePropagationService] Closing all positions for signal {$signalId}");
        
        $trades = Trade::where('signal_id', $signalId)
            ->where('status', 'open')
            ->with(['user', 'exchangeAccount', 'position'])
            ->get();

        $results = [
            'closed' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($trades as $trade) {
            try {
                if (!$trade->exchangeAccount || !$trade->exchangeAccount->is_active) {
                    throw new \Exception('Exchange account not active');
                }

                $bybit = new BybitService(
                    $trade->exchangeAccount->api_key,
                    $trade->exchangeAccount->api_secret
                );

                $side = $trade->type === 'long' ? 'Buy' : 'Sell';
                $bybit->closePosition($trade->symbol, $side);

                $currentPrice = $bybit->getCurrentPrice($trade->symbol);

                DB::beginTransaction();

                $trade->update([
                    'exit_price' => $currentPrice,
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

                $trade->calculatePnl();

                if ($trade->position) {
                    $trade->position->close($currentPrice);
                }

                DB::commit();

                $results['closed']++;

                Log::info("Position closed for user {$trade->user_id}", [
                    'trade_id' => $trade->id,
                    'symbol' => $trade->symbol,
                    'pnl' => $trade->realized_pnl
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                
                $results['failed']++;
                $results['errors'][] = [
                    'user_id' => $trade->user_id,
                    'trade_id' => $trade->id,
                    'error' => $e->getMessage()
                ];

                Log::error("Failed to close position for user {$trade->user_id}: " . $e->getMessage());
            }
        }

        return $results;
    }
}