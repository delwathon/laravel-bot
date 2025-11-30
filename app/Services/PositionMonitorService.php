<?php

namespace App\Services;

use App\Models\Position;
use App\Models\ExchangeAccount;
use Illuminate\Support\Facades\Log;

class PositionMonitorService
{
    public function monitorAllPositions()
    {
        $positions = Position::where('is_active', true)
            ->with(['user', 'exchangeAccount', 'trade'])
            ->get();

        $results = [
            'total' => $positions->count(),
            'updated' => 0,
            'closed' => 0,
            'errors' => 0,
        ];

        foreach ($positions as $position) {
            try {
                $this->monitorPosition($position);
                $results['updated']++;
                
                if (!$position->is_active) {
                    $results['closed']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Failed to monitor position {$position->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    public function monitorPosition(Position $position)
    {
        if (!$position->is_active) {
            return;
        }

        $exchangeAccount = $position->exchangeAccount;
        
        if (!$exchangeAccount || !$exchangeAccount->is_active) {
            return;
        }

        $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
        
        $currentPrice = $bybit->getCurrentPrice($position->symbol);
        
        if (!$currentPrice) {
            Log::warning("Could not get current price for {$position->symbol}");
            return;
        }

        $position->updateMetrics($currentPrice);

        if ($position->shouldClose()) {
            $this->closePosition($position, $currentPrice);
        }
    }

    protected function closePosition(Position $position, $exitPrice)
    {
        $trade = $position->trade;
        
        if (!$trade) {
            Log::error("Position {$position->id} has no associated trade");
            return;
        }

        try {
            $exchangeAccount = $position->exchangeAccount;
            $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
            
            $side = $position->side === 'long' ? 'Buy' : 'Sell';
            $bybit->closePosition($position->symbol, $side);

            $position->close($exitPrice);

            Log::info("Position auto-closed", [
                'position_id' => $position->id,
                'symbol' => $position->symbol,
                'exit_price' => $exitPrice,
                'pnl' => $trade->realized_pnl,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to close position {$position->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function monitorUserPositions($userId)
    {
        $positions = Position::where('is_active', true)
            ->where('user_id', $userId)
            ->with(['exchangeAccount', 'trade'])
            ->get();

        foreach ($positions as $position) {
            try {
                $this->monitorPosition($position);
            } catch (\Exception $e) {
                Log::error("Failed to monitor position {$position->id} for user {$userId}: " . $e->getMessage());
            }
        }
    }
}