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
                $results['errors'][] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'error' => $e->getMessage(),
                ];
                
                Log::error("Failed to propagate signal to user {$user->id}: " . $e->getMessage());
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

        // Get position size percentage from settings
        $positionSizePercent = Setting::get('signal_position_size', 5);
        
        // Get leverage from settings
        $leverageSetting = Setting::get('signal_leverage', 'Max');
        $leverage = $this->calculateLeverage($leverageSetting, $signal->symbol, $bybit);

        // Calculate quantity
        $riskAmount = ($balance * $positionSizePercent) / 100;
        $stopLossDistance = abs($signal->entry_price - $signal->stop_loss);
        $quantity = $riskAmount / $stopLossDistance;
        $quantity = round($quantity, 3);

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
                $orderType,  // Use the specified order type
                $limitPrice,  // Pass limit price for Limit orders
                $signal->stop_loss,
                $signal->take_profit,
                $leverage
            );

            if (!$orderResult || !isset($orderResult['orderId'])) {
                throw new \Exception('Failed to place order on Bybit');
            }

            $orderId = $orderResult['orderId'];
            Log::info("Order placed for user {$user->id}: Order ID {$orderId}, Type: {$orderType}");

            // Handle execution price based on order type
            if ($orderType === 'Market') {
                // For Market orders: Wait for fill and get actual execution price
                $actualExecutionPrice = $bybit->waitForOrderFillAndGetPrice($signal->symbol, $orderId, 10, 500);
                
                if ($actualExecutionPrice === null || $actualExecutionPrice <= 0) {
                    Log::warning("Could not get execution price for order {$orderId}, falling back to signal entry price");
                    $actualExecutionPrice = $signal->entry_price; // Fallback
                }

                Log::info("Actual execution price for user {$user->id}: {$actualExecutionPrice}");

                // Update trade with ACTUAL execution price
                $trade->update([
                    'exchange_order_id' => $orderId,
                    'entry_price' => $actualExecutionPrice,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);

                // Create active position
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
                    'status' => 'pending', // Stays pending until filled
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
                'timeframe' => Setting::get('signal_primary_timeframe', '15'),
                'pattern' => 'Manual Trade',
                'confidence' => 100,
                'entry_price' => $entryPrice,
                'stop_loss' => $stopLoss,
                'take_profit' => $takeProfit,
                'risk_reward_ratio' => abs(($takeProfit - $entryPrice) / ($entryPrice - $stopLoss)),
                'position_size_percent' => $positionSizePercent,
                'status' => 'active',
            ]);

            // Execute admin trade FIRST - if this fails, nothing propagates
            $adminResult = $this->executeAdminTrade($signal, $positionSizePercent, $orderType);
            
            if (!$adminResult['success']) {
                throw new \Exception('Failed to execute admin trade: ' . $adminResult['error']);
            }

            // Then propagate to all users with same order type
            $userResults = $this->propagateSignalToAllUsers($signal, $orderType);

            // Mark signal as executed
            $signal->update([
                'status' => 'executed',
                'executed_at' => now(),
            ]);

            DB::commit();

            // Combine results
            return [
                'total' => $userResults['total'],
                'successful' => $userResults['successful'],
                'failed' => $userResults['failed'],
                'errors' => $userResults['errors'],
                'admin_trade' => $adminResult['trade'],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual trade propagation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function executeAdminTrade(Signal $signal, $positionSizePercent, $orderType = 'Market')
    {
        try {
            // Get admin exchange account
            $adminAccount = ExchangeAccount::getBybitAccount();

            if (!$adminAccount) {
                throw new \Exception('No admin Bybit account configured. Please add one in settings.');
            }

            // Get admin user (first admin user)
            $admin = User::where('is_admin', true)->first();
            
            if (!$admin) {
                throw new \Exception('No admin user found in system');
            }

            // Initialize Bybit service with admin credentials
            $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);

            // Get admin account balance
            $balance = $bybit->getBalance();
            
            if ($balance <= 0) {
                throw new \Exception('Insufficient admin account balance');
            }
            
            // Get leverage from settings
            $leverageSetting = Setting::get('signal_leverage', 'Max');
            $leverage = $this->calculateLeverage($leverageSetting, $signal->symbol, $bybit);

            // Calculate position size
            $riskAmount = ($balance * $positionSizePercent) / 100;
            $stopLossDistance = abs($signal->entry_price - $signal->stop_loss);
            $quantity = $riskAmount / $stopLossDistance;
            $quantity = round($quantity, 3);

            // Create admin's trade record with signal_id linkage
            $trade = Trade::create([
                'user_id' => $admin->id,
                'signal_id' => $signal->id,
                'exchange_account_id' => $adminAccount->id,
                'symbol' => $signal->symbol,
                'exchange' => 'bybit',
                'type' => $signal->type,
                'entry_price' => $signal->entry_price,
                'stop_loss' => $signal->stop_loss,
                'take_profit' => $signal->take_profit,
                'quantity' => $quantity,
                'leverage' => $leverage,
                'status' => 'pending',
            ]);

            // Execute actual trade on Bybit
            $side = $signal->type === 'long' ? 'Buy' : 'Sell';
            
            // For Limit orders, use entry_price as the limit price
            $limitPrice = ($orderType === 'Limit') ? $signal->entry_price : null;
            
            $orderResult = $bybit->placeOrder(
                $signal->symbol,
                $side,
                $quantity,
                $orderType,  // Use the selected order type
                $limitPrice,  // Pass limit price for Limit orders
                $signal->stop_loss,
                $signal->take_profit,
                $leverage
            );

            if (!$orderResult || !isset($orderResult['orderId'])) {
                throw new \Exception('Failed to place order on Bybit: Invalid response');
            }

            $orderId = $orderResult['orderId'];
            Log::info("Admin order placed: Order ID {$orderId}, Type: {$orderType}");

            // Handle execution price based on order type
            if ($orderType === 'Market') {
                // Fetch actual execution price for Market orders
                $actualPrice = $bybit->waitForOrderFillAndGetPrice($signal->symbol, $orderId);
                if (!$actualPrice) $actualPrice = $signal->entry_price; // Fallback

                // Update trade with actual price
                $trade->update([
                    'exchange_order_id' => $orderId,
                    'entry_price' => $actualPrice,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);

                // Create active position record for admin
                $position = Position::create([
                    'user_id' => $admin->id,
                    'trade_id' => $trade->id,
                    'exchange_account_id' => $adminAccount->id,
                    'symbol' => $signal->symbol,
                    'exchange' => 'bybit',
                    'side' => $signal->type,
                    'entry_price' => $actualPrice,
                    'current_price' => $actualPrice,
                    'quantity' => $quantity,
                    'leverage' => $leverage,
                    'stop_loss' => $signal->stop_loss,
                    'take_profit' => $signal->take_profit,
                    'is_active' => true,
                    'last_updated_at' => now(),
                ]);

            } else {
                // For Limit orders, keep as pending
                $trade->update([
                    'exchange_order_id' => $orderId,
                    'status' => 'pending', // Stays pending until filled
                ]);

                Log::info("Admin limit order placed, awaiting fill");
                $position = null;
            }

            Log::info("Admin trade executed successfully with {$leverage}x leverage", [
                'trade_id' => $trade->id,
                'order_id' => $orderId,
                'order_type' => $orderType,
                'signal_id' => $signal->id,
                'symbol' => $signal->symbol,
                'quantity' => $quantity,
                'leverage' => $leverage,
            ]);

            return [
                'success' => true,
                'trade' => $trade,
                'position' => $position,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to execute admin trade: ' . $e->getMessage(), [
                'signal_id' => $signal->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Calculate leverage based on setting
     */
    protected function calculateLeverage($leverageSetting, $symbol, $bybit)
    {
        // If leverage is 'Max', fetch max from Bybit
        if (strtolower($leverageSetting) === 'max') {
            return $bybit->getMaxLeverage($symbol);
        }
        
        // Otherwise use the numeric value
        return (float) $leverageSetting;
    }

    /**
     * Close all positions for a specific signal (admin + all users)
     */
    public function closeAllPositionsBySignal($signalId)
    {
        $results = [
            'total' => 0,
            'closed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Get all trades for this signal
        $trades = Trade::where('signal_id', $signalId)
            ->where('status', 'open')
            ->with(['user', 'exchangeAccount', 'position'])
            ->get();

        $results['total'] = $trades->count();

        foreach ($trades as $trade) {
            try {
                $this->closeTradePosition($trade);
                $results['closed']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'trade_id' => $trade->id,
                    'user_id' => $trade->user_id,
                    'error' => $e->getMessage(),
                ];
                
                Log::error("Failed to close trade {$trade->id}: " . $e->getMessage());
            }
        }

        Log::info("Closed all positions for signal {$signalId}", $results);

        return $results;
    }

    /**
     * Close a single trade position
     */
    protected function closeTradePosition(Trade $trade)
    {
        if ($trade->status !== 'open') {
            throw new \Exception('Trade is not open');
        }

        // Determine which credentials to use
        if ($trade->user->is_admin) {
            $adminAccount = ExchangeAccount::getBybitAccount();
            if (!$adminAccount) {
                throw new \Exception('No admin Bybit account configured');
            }
            $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
        } else {
            $exchangeAccount = $trade->exchangeAccount;
            if (!$exchangeAccount) {
                throw new \Exception('No exchange account found for this trade');
            }
            $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
        }

        $side = $trade->type === 'long' ? 'Buy' : 'Sell';
        
        $closeResult = $bybit->closePosition($trade->symbol, $side);

        if (!$closeResult) {
            throw new \Exception('Failed to close position on Bybit');
        }

        $currentPrice = $bybit->getCurrentPrice($trade->symbol);

        $trade->update([
            'exit_price' => $currentPrice,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $trade->calculatePnl();

        if ($trade->position) {
            $trade->position->close($currentPrice);
        }

        Log::info("Trade closed for user {$trade->user_id}: {$trade->symbol}", [
            'trade_id' => $trade->id,
            'pnl' => $trade->realized_pnl,
        ]);

        return $trade;
    }
}