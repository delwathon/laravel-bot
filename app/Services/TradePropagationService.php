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
     * Check admin conflicts before signal execution
     * This determines if signal should be skipped, cancelled, or executed
     * 
     * @param Signal $signal
     * @return array
     */
    public function checkAdminConflicts(Signal $signal)
    {
        Log::info("[TradePropagationService] Checking admin conflicts", [
            'signal_id' => $signal->id,
            'symbol' => $signal->symbol,
            'type' => $signal->type
        ]);
        
        $adminAccount = ExchangeAccount::getBybitAccount();
        
        if (!$adminAccount) {
            Log::error('[TradePropagationService] No admin account configured');
            return [
                'has_conflict' => false,
                'action' => 'proceed',
                'reason' => 'No admin account configured - will fail at execution'
            ];
        }
        
        $adminUser = $adminAccount->user;
        
        if (!$adminUser) {
            Log::error('[TradePropagationService] Admin user not found');
            return [
                'has_conflict' => false,
                'action' => 'proceed',
                'reason' => 'Admin user not found - will fail at execution'
            ];
        }
        
        // Check for open positions
        $openPosition = Trade::where('user_id', $adminUser->id)
            ->where('symbol', $signal->symbol)
            ->where('status', 'open')
            ->first();
        
        if ($openPosition) {
            $isSameDirection = ($openPosition->type === $signal->type);
            
            Log::info("[TradePropagationService] Admin has open position", [
                'symbol' => $signal->symbol,
                'existing_direction' => $openPosition->type,
                'new_direction' => $signal->type,
                'same_direction' => $isSameDirection
            ]);
            
            // Check if we should close opposite positions
            if (!$isSameDirection && Setting::get('signal_close_opposite_positions', false)) {
                return [
                    'has_conflict' => true,
                    'conflict_type' => 'open_position_opposite',
                    'same_direction' => false,
                    'action' => 'close_and_execute',
                    'reason' => "Admin has opposite {$openPosition->type} position on {$signal->symbol} - will close and execute new",
                    'existing_trade' => $openPosition
                ];
            }
            
            // Skip if configured to skip duplicate positions
            if (Setting::get('signal_skip_duplicate_positions', true)) {
                return [
                    'has_conflict' => true,
                    'conflict_type' => $isSameDirection ? 'open_position_same' : 'open_position_opposite',
                    'same_direction' => $isSameDirection,
                    'action' => 'skip',
                    'reason' => "Admin has open {$openPosition->type} position on {$signal->symbol}",
                    'existing_trade' => $openPosition
                ];
            }
        }
        
        // Check for pending orders
        $pendingOrder = Trade::where('user_id', $adminUser->id)
            ->where('symbol', $signal->symbol)
            ->where('status', 'pending')
            ->first();
        
        if ($pendingOrder) {
            $age = now()->diffInHours($pendingOrder->created_at);
            $isSameDirection = ($pendingOrder->type === $signal->type);
            $staleThreshold = (int) Setting::get('signal_stale_order_hours', 24);
            
            Log::info("[TradePropagationService] Admin has pending order", [
                'symbol' => $signal->symbol,
                'existing_direction' => $pendingOrder->type,
                'new_direction' => $signal->type,
                'same_direction' => $isSameDirection,
                'age_hours' => $age,
                'stale_threshold' => $staleThreshold
            ]);
            
            // Opposite direction - cancel if configured
            if (!$isSameDirection && Setting::get('signal_cancel_opposite_pending', true)) {
                return [
                    'has_conflict' => true,
                    'conflict_type' => 'pending_opposite',
                    'same_direction' => false,
                    'action' => 'cancel_and_execute',
                    'reason' => "Admin has opposite {$pendingOrder->type} pending order on {$signal->symbol} - will cancel and execute new",
                    'existing_trade' => $pendingOrder
                ];
            }
            
            // Same direction but stale - cancel if configured
            if ($isSameDirection && $age >= $staleThreshold && Setting::get('signal_cancel_stale_pending', true)) {
                return [
                    'has_conflict' => true,
                    'conflict_type' => 'pending_stale',
                    'same_direction' => true,
                    'action' => 'cancel_and_execute',
                    'reason' => "Admin has stale {$pendingOrder->type} pending order ({$age}h old) on {$signal->symbol} - will cancel and execute new",
                    'existing_trade' => $pendingOrder
                ];
            }
            
            // Same direction and fresh - skip
            if ($isSameDirection) {
                return [
                    'has_conflict' => true,
                    'conflict_type' => 'pending_valid',
                    'same_direction' => true,
                    'action' => 'skip',
                    'reason' => "Admin has valid {$pendingOrder->type} pending order ({$age}h old) on {$signal->symbol}",
                    'existing_trade' => $pendingOrder
                ];
            }
            
            // Opposite direction but cancel is disabled - skip
            if (!$isSameDirection) {
                return [
                    'has_conflict' => true,
                    'conflict_type' => 'pending_opposite',
                    'same_direction' => false,
                    'action' => 'skip',
                    'reason' => "Admin has opposite {$pendingOrder->type} pending order on {$signal->symbol} (cancel opposite disabled)",
                    'existing_trade' => $pendingOrder
                ];
            }
        }
        
        // No conflicts
        Log::info("[TradePropagationService] No conflicts detected for {$signal->symbol}");
        
        return [
            'has_conflict' => false,
            'conflict_type' => null,
            'action' => 'proceed',
            'reason' => 'No conflicts detected',
            'existing_trade' => null
        ];
    }

    /**
     * Cancel ALL pending orders for a symbol (admin + all users)
     * 
     * @param string $symbol
     * @return array
     */
    public function cancelAllPendingOrdersBySymbol($symbol)
    {
        Log::info("[TradePropagationService] Cancelling all pending orders for symbol: {$symbol}");
        
        $pendingTrades = Trade::where('symbol', $symbol)
            ->where('status', 'pending')
            ->with(['user', 'exchangeAccount'])
            ->get();
        
        $results = [
            'total' => $pendingTrades->count(),
            'cancelled' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        if ($pendingTrades->isEmpty()) {
            Log::info("[TradePropagationService] No pending orders found for {$symbol}");
            return $results;
        }
        
        foreach ($pendingTrades as $trade) {
            try {
                // Determine which account to use
                if ($trade->user->is_admin) {
                    $adminAccount = ExchangeAccount::getBybitAccount();
                    if (!$adminAccount) {
                        throw new \Exception('No admin account found');
                    }
                    $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
                } else {
                    $exchangeAccount = $trade->exchangeAccount;
                    if (!$exchangeAccount || !$exchangeAccount->is_active) {
                        throw new \Exception('Exchange account not active');
                    }
                    $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
                }
                
                // Cancel on Bybit if order ID exists
                if ($trade->exchange_order_id) {
                    try {
                        $bybit->cancelOrder($trade->symbol, $trade->exchange_order_id);
                        Log::info("[TradePropagationService] Cancelled order on Bybit", [
                            'trade_id' => $trade->id,
                            'user_id' => $trade->user_id,
                            'order_id' => $trade->exchange_order_id
                        ]);
                    } catch (\Exception $e) {
                        // Order might already be filled or cancelled
                        Log::warning("[TradePropagationService] Bybit cancel failed (order may not exist): " . $e->getMessage());
                    }
                }
                
                // Update trade status in database
                $trade->update([
                    'status' => 'cancelled',
                    'closed_at' => now(),
                ]);
                
                $results['cancelled']++;
                
                Log::info("[TradePropagationService] Trade marked as cancelled", [
                    'trade_id' => $trade->id,
                    'user_id' => $trade->user_id,
                    'symbol' => $trade->symbol
                ]);
                
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'trade_id' => $trade->id,
                    'user_id' => $trade->user_id,
                    'user_name' => $trade->user->name ?? 'Unknown',
                    'error' => $e->getMessage()
                ];
                
                Log::error("[TradePropagationService] Failed to cancel pending order", [
                    'trade_id' => $trade->id,
                    'user_id' => $trade->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        Log::info("[TradePropagationService] Cancellation completed for {$symbol}", $results);
        
        return $results;
    }

    /**
     * Close ALL open positions for a symbol (admin + all users)
     * Used when signal_close_opposite_positions is enabled
     * 
     * @param string $symbol
     * @return array
     */
    public function closeAllPositionsBySymbol($symbol)
    {
        Log::info("[TradePropagationService] Closing all open positions for symbol: {$symbol}");
        
        $openTrades = Trade::where('symbol', $symbol)
            ->where('status', 'open')
            ->with(['user', 'exchangeAccount', 'position'])
            ->get();
        
        $results = [
            'total' => $openTrades->count(),
            'closed' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        if ($openTrades->isEmpty()) {
            Log::info("[TradePropagationService] No open positions found for {$symbol}");
            return $results;
        }
        
        foreach ($openTrades as $trade) {
            try {
                if (!$trade->exchangeAccount || !$trade->exchangeAccount->is_active) {
                    throw new \Exception('Exchange account not active');
                }
                
                $bybit = new BybitService(
                    $trade->exchangeAccount->api_key,
                    $trade->exchangeAccount->api_secret
                );
                
                // Close position on Bybit
                $side = $trade->type === 'long' ? 'Buy' : 'Sell';
                $bybit->closePosition($trade->symbol, $side);
                
                // Get current price for P&L calculation
                $currentPrice = $bybit->getCurrentPrice($trade->symbol);
                
                DB::beginTransaction();
                
                // Update trade
                $trade->update([
                    'exit_price' => $currentPrice,
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);
                
                // Calculate P&L
                $trade->calculatePnl();
                
                // Close position
                if ($trade->position) {
                    $trade->position->close($currentPrice);
                }
                
                DB::commit();
                
                $results['closed']++;
                
                Log::info("[TradePropagationService] Position closed", [
                    'trade_id' => $trade->id,
                    'user_id' => $trade->user_id,
                    'symbol' => $trade->symbol,
                    'pnl' => $trade->realized_pnl
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                
                $results['failed']++;
                $results['errors'][] = [
                    'trade_id' => $trade->id,
                    'user_id' => $trade->user_id,
                    'user_name' => $trade->user->name ?? 'Unknown',
                    'error' => $e->getMessage()
                ];
                
                Log::error("[TradePropagationService] Failed to close position", [
                    'trade_id' => $trade->id,
                    'user_id' => $trade->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        Log::info("[TradePropagationService] Position closure completed for {$symbol}", $results);
        
        return $results;
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

            // Handle Market vs Limit orders
            if ($orderType === 'Market') {
                // Wait for fill and get actual execution price
                $actualExecutionPrice = $bybit->waitForOrderFillAndGetPrice($signal->symbol, $orderId, 10, 500);
                
                if ($actualExecutionPrice === null || $actualExecutionPrice <= 0) {
                    Log::warning("Market order {$orderId} did not fill, falling back to signal entry price");
                    $actualExecutionPrice = $signal->entry_price;
                }

                $trade->update([
                    'exchange_order_id' => $orderId,
                    'entry_price' => $actualExecutionPrice,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);

                // Create position
                $position = Position::create([
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

            Log::info("[TradePropagationService] Manual signal created", [
                'signal_id' => $signal->id,
                'symbol' => $symbol,
                'type' => $type
            ]);

            // Check admin conflicts BEFORE any execution
            $conflictCheck = $this->checkAdminConflicts($signal);
            
            if ($conflictCheck['action'] === 'skip') {
                $signal->update([
                    'status' => 'skipped',
                    'notes' => $conflictCheck['reason']
                ]);
                
                DB::commit();
                
                Log::warning("[TradePropagationService] Manual signal skipped due to conflict", [
                    'signal_id' => $signal->id,
                    'reason' => $conflictCheck['reason']
                ]);
                
                throw new \Exception($conflictCheck['reason']);
            }
            
            $cancelResults = null;
            $closeResults = null;
            
            if ($conflictCheck['action'] === 'cancel_and_execute') {
                Log::info("[TradePropagationService] Cancelling existing pending orders", [
                    'signal_id' => $signal->id,
                    'reason' => $conflictCheck['reason']
                ]);
                
                // Cancel ALL pending orders for this symbol (admin + users)
                $cancelResults = $this->cancelAllPendingOrdersBySymbol($signal->symbol);
                
                Log::info("[TradePropagationService] Cancellation completed", $cancelResults);
                
                $signal->update([
                    'notes' => "Cancelled {$cancelResults['cancelled']} existing orders. " . $conflictCheck['reason']
                ]);
            }
            
            if ($conflictCheck['action'] === 'close_and_execute') {
                Log::info("[TradePropagationService] Closing existing positions", [
                    'signal_id' => $signal->id,
                    'reason' => $conflictCheck['reason']
                ]);
                
                // Close ALL positions for this symbol (admin + users)
                $closeResults = $this->closeAllPositionsBySymbol($signal->symbol);
                
                Log::info("[TradePropagationService] Position closure completed", $closeResults);
                
                $signal->update([
                    'notes' => "Closed {$closeResults['closed']} existing positions. " . $conflictCheck['reason']
                ]);
            }

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
                'signal' => $signal,
                'action_taken' => $conflictCheck['action'],
                'admin_trade' => $adminResult['trade'],
                'total' => $userResults['total'],
                'successful' => $userResults['successful'],
                'failed' => $userResults['failed'],
                'cancelled_orders' => $cancelResults ? $cancelResults['cancelled'] : 0,
                'closed_positions' => $closeResults ? $closeResults['closed'] : 0,
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