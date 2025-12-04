<?php

namespace App\Services;

use App\Models\Signal;
use App\Models\Trade;
use App\Models\Position;
use App\Models\User;
use App\Models\ExchangeAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TradeExecutionService
{
    public function executeTradeForUser(Signal $signal, User $user)
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

        // Calculate quantity based on risk management
        $riskAmount = ($balance * $positionSizePercent) / 100;
        $stopLossDistance = abs($signal->entry_price - $signal->stop_loss);
        
        if ($stopLossDistance <= 0) {
            throw new \Exception("Invalid stop loss distance for symbol {$signal->symbol}");
        }
        
        $quantity = $riskAmount / $stopLossDistance;

        // Adjust quantity to meet Bybit's requirements
        $quantity = $this->adjustQuantityToInstrument($quantity, $instrumentInfo);

        Log::info("Calculated quantity for user {$user->id}", [
            'symbol' => $signal->symbol,
            'balance' => $balance,
            'risk_amount' => $riskAmount,
            'calculated_qty' => $quantity,
            'min_qty' => $instrumentInfo['minOrderQty'],
            'qty_step' => $instrumentInfo['qtyStep'],
        ]);

        DB::beginTransaction();

        try {
            // Create trade record with SUBMITTED entry price (not actual yet)
            $trade = Trade::create([
                'user_id' => $user->id,
                'signal_id' => $signal->id,
                'exchange_account_id' => $exchangeAccount->id,
                'symbol' => $signal->symbol,
                'exchange' => 'bybit',
                'type' => $signal->type,
                'order_type' => $signal->order_type,
                'entry_price' => $signal->entry_price,
                'stop_loss' => $signal->stop_loss,
                'take_profit' => $signal->take_profit,
                'quantity' => $quantity,
                'leverage' => $leverage,
                'status' => 'pending',
            ]);

            // Execute trade on Bybit
            $side = $signal->type === 'long' ? 'Buy' : 'Sell';
            
            $orderResult = $bybit->placeOrder(
                $signal->symbol,
                $side,
                $quantity,
                $signal->order_type,
                $signal->entry_price,
                $signal->stop_loss,
                $signal->take_profit,
                $leverage
            );

            if (!$orderResult || !isset($orderResult['orderId'])) {
                throw new \Exception('Failed to place order on Bybit');
            }

            $orderId = $orderResult['orderId'];
            Log::info("Order placed for user {$user->id}: Order ID {$orderId}");

            // Handle Market vs Limit orders differently
            if ($signal->order_type === 'Market') {
                // For Market orders: Wait for fill and get ACTUAL execution price
                $actualExecutionPrice = $bybit->waitForOrderFillAndGetPrice($signal->symbol, $orderId, 10, 500);
                
                if ($actualExecutionPrice === null || $actualExecutionPrice <= 0) {
                    Log::warning("Market order {$orderId} did not fill, falling back to signal entry price");
                    $actualExecutionPrice = $signal->entry_price;
                }

                Log::info("Actual execution price for user {$user->id}: {$actualExecutionPrice} (Submitted: {$signal->entry_price})");

                // Update trade with ACTUAL execution price and mark as OPEN
                $trade->update([
                    'exchange_order_id' => $orderId,
                    'entry_price' => $actualExecutionPrice,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);

                // Create position with ACTUAL execution price
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
                // For Limit orders: Just record the order, don't create position yet
                Log::info("Limit order placed for user {$user->id}, awaiting fill");
                
                $trade->update([
                    'exchange_order_id' => $orderId,
                    'status' => 'pending', // Keep as pending until filled
                    'entry_price' => $signal->entry_price, // Submitted price, not actual yet
                ]);
                
                // Don't create position yet - will be created when order fills
                $position = null;
            }

            DB::commit();
            
            Log::info("Trade executed for user {$user->id}: {$signal->symbol} {$signal->type} with {$leverage}x leverage", [
                'trade_id' => $trade->id,
                'order_id' => $orderId,
                'order_type' => $signal->order_type,
                'status' => $trade->status,
            ]);

            return [
                'success' => true,
                'trade' => $trade,
                'position' => $position,
                'actual_execution_price' => $signal->order_type === 'Market' ? ($actualExecutionPrice ?? $signal->entry_price) : null,
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

    public function closeTradeForUser(Trade $trade)
    {
        if ($trade->status !== 'open') {
            throw new \Exception('Trade is not open');
        }

        $user = $trade->user;
        $exchangeAccount = $trade->exchangeAccount;

        $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);

        DB::beginTransaction();

        try {
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

            DB::commit();

            Log::info("Trade closed for user {$user->id}: {$trade->symbol}", [
                'trade_id' => $trade->id,
                'pnl' => $trade->realized_pnl,
            ]);

            return [
                'success' => true,
                'trade' => $trade,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Trade close failed for user {$user->id}: " . $e->getMessage());
            
            throw $e;
        }
    }
}