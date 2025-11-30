<?php

namespace App\Services;

use App\Models\Signal;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
                $query->where('is_active', true);
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
                $this->tradeExecutionService->executeSignalForUser($signal, $user);
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

    public function propagateManualTrade($symbol, $type, $entryPrice, $stopLoss, $takeProfit, $positionSizePercent = 5)
    {
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

        return $this->propagateSignalToAllUsers($signal);
    }
}