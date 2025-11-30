<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\Position;
use App\Models\Signal;
use App\Models\User;
use App\Models\ExchangeAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TradeExecutionService
{
    public function executeSignalForUser(Signal $signal, User $user, $positionSizePercent = null)
    {
        if (!$user->hasConnectedExchange()) {
            throw new \Exception('User has no connected exchange account');
        }

        $exchangeAccount = $user->exchangeAccount;
        
        if (!$exchangeAccount->is_active) {
            throw new \Exception('Exchange account is not active');
        }

        $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
        
        $balance = $bybit->getBalance();
        
        $positionSize = $positionSizePercent ?? $signal->position_size_percent;
        $riskAmount = ($balance * $positionSize) / 100;
        
        $stopLossDistance = abs($signal->entry_price - $signal->stop_loss);
        $quantity = $riskAmount / $stopLossDistance;
        
        $quantity = round($quantity, 3);

        DB::beginTransaction();
        
        try {
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
                'leverage' => 1,
                'status' => 'pending',
            ]);

            $side = $signal->type === 'long' ? 'Buy' : 'Sell';
            
            $orderResult = $bybit->placeOrder(
                $signal->symbol,
                $side,
                $quantity,
                'Market',
                null,
                $signal->stop_loss,
                $signal->take_profit
            );

            if (!$orderResult || !isset($orderResult['orderId'])) {
                throw new \Exception('Failed to place order on Bybit');
            }

            $trade->update([
                'exchange_order_id' => $orderResult['orderId'],
                'status' => 'open',
                'opened_at' => now(),
            ]);

            $position = Position::create([
                'user_id' => $user->id,
                'trade_id' => $trade->id,
                'exchange_account_id' => $exchangeAccount->id,
                'symbol' => $signal->symbol,
                'exchange' => 'bybit',
                'side' => $signal->type,
                'entry_price' => $signal->entry_price,
                'current_price' => $signal->entry_price,
                'quantity' => $quantity,
                'leverage' => 1,
                'stop_loss' => $signal->stop_loss,
                'take_profit' => $signal->take_profit,
                'is_active' => true,
                'last_updated_at' => now(),
            ]);

            DB::commit();
            
            Log::info("Trade executed for user {$user->id}: {$signal->symbol} {$signal->type}", [
                'trade_id' => $trade->id,
                'order_id' => $orderResult['orderId'],
            ]);

            return [
                'success' => true,
                'trade' => $trade,
                'position' => $position,
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