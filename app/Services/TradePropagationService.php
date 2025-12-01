<?php

namespace App\Services;

use App\Models\Signal;
use App\Models\User;
use App\Models\Trade;
use App\Models\Position;
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

    public function propagateSignalToAllUsers(Signal $signal)
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
                $this->tradeExecutionService->executeTradeForUser($signal, $user);
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

    public function propagateManualTrade($symbol, $type, $entryPrice, $stopLoss, $takeProfit, $positionSizePercent = 5, $orderType = 'Market')
    {
        DB::beginTransaction();
        
        try {
            // Create the signal
            $signal = Signal::create([
                'symbol' => $symbol,
                'exchange' => 'bybit',
                'type' => $type,
                'timeframe' => '15m',
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

            // Then propagate to all users
            $userResults = $this->propagateSignalToAllUsers($signal);

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

    protected function executeAdminTrade(Signal $signal, $positionSizePercent, $orderType = 'Market')
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
            $leverageSetting = \App\Models\Setting::get('signal_leverage', 'Max');
            $leverage = $this->calculateLeverage($leverageSetting, $signal->symbol, $bybit);

            // Calculate position size
            $riskAmount = ($balance * $positionSizePercent) / 100;
            $stopLossDistance = abs($signal->entry_price - $signal->stop_loss);
            $quantity = $riskAmount / $stopLossDistance;
            $quantity = round($quantity, 3);

            // Create admin's trade record
            $trade = Trade::create([
                'user_id' => $admin->id,
                'signal_id' => $signal->id,
                'exchange_account_id' => null,
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
                $leverage  // Pass leverage to Bybit
            );

            if (!$orderResult || !isset($orderResult['orderId'])) {
                throw new \Exception('Failed to place order on Bybit: Invalid response');
            }

            $orderId = $orderResult['orderId'];

            // ADD THIS: Fetch actual execution price
            $actualPrice = $bybit->waitForOrderFillAndGetPrice($signal->symbol, $orderId);
            if (!$actualPrice) $actualPrice = $signal->entry_price; // Fallback

            // Update trade with order details
            $trade->update([
                'exchange_order_id' => $orderId,
                'entry_price' => $actualPrice,  // ← CHANGED
                'status' => 'open',
                'opened_at' => now(),
            ]);

            // Create position record for admin
            $position = Position::create([
                'user_id' => $admin->id,
                'trade_id' => $trade->id,
                'exchange_account_id' => null,
                'symbol' => $signal->symbol,
                'exchange' => 'bybit',
                'side' => $signal->type,
                'entry_price' => $actualPrice,      // ← CHANGED
                'current_price' => $actualPrice,     // ← CHANGED
                'quantity' => $quantity,
                'leverage' => $leverage,
                'stop_loss' => $signal->stop_loss,
                'take_profit' => $signal->take_profit,
                'is_active' => true,
                'last_updated_at' => now(),
            ]);

            Log::info("Admin trade executed successfully with {$leverage}x leverage", [
                'trade_id' => $trade->id,
                'order_id' => $orderResult['orderId'],
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
}