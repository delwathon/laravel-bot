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
            // Create trade record with SUBMITTED entry price (not actual yet)
            $trade = Trade::create([
                'user_id' => $user->id,
                'signal_id' => $signal->id,
                'exchange_account_id' => $exchangeAccount->id,
                'symbol' => $signal->symbol,
                'exchange' => 'bybit',
                'type' => $signal->type,
                'entry_price' => $signal->entry_price, // This will be updated with actual price
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
                'Market',
                null,
                $signal->stop_loss,
                $signal->take_profit,
                $leverage
            );

            if (!$orderResult || !isset($orderResult['orderId'])) {
                throw new \Exception('Failed to place order on Bybit');
            }

            $orderId = $orderResult['orderId'];
            Log::info("Order placed for user {$user->id}: Order ID {$orderId}");

            // CRITICAL: Wait for order to fill and get ACTUAL execution price
            $actualExecutionPrice = $bybit->waitForOrderFillAndGetPrice($signal->symbol, $orderId, 10, 500);
            
            if ($actualExecutionPrice === null || $actualExecutionPrice <= 0) {
                Log::warning("Could not get execution price for order {$orderId}, falling back to signal entry price");
                $actualExecutionPrice = $signal->entry_price; // Fallback
            }

            Log::info("Actual execution price for user {$user->id}: {$actualExecutionPrice} (Submitted: {$signal->entry_price})");

            // Update trade with ACTUAL execution price
            $trade->update([
                'exchange_order_id' => $orderId,
                'entry_price' => $actualExecutionPrice, // Update with ACTUAL price
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
                'entry_price' => $actualExecutionPrice, // ACTUAL execution price
                'current_price' => $actualExecutionPrice, // Start with execution price
                'quantity' => $quantity,
                'leverage' => $leverage,
                'stop_loss' => $signal->stop_loss,
                'take_profit' => $signal->take_profit,
                'is_active' => true,
                'last_updated_at' => now(),
            ]);

            DB::commit();
            
            Log::info("Trade executed for user {$user->id}: {$signal->symbol} {$signal->type} @ {$actualExecutionPrice} with {$leverage}x leverage", [
                'trade_id' => $trade->id,
                'order_id' => $orderId,
                'submitted_price' => $signal->entry_price,
                'actual_price' => $actualExecutionPrice,
                'slippage' => abs($actualExecutionPrice - $signal->entry_price),
            ]);

            return [
                'success' => true,
                'trade' => $trade,
                'position' => $position,
                'actual_execution_price' => $actualExecutionPrice,
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