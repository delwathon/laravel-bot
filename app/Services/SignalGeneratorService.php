<?php

namespace App\Services;

use App\Models\Signal;
use App\Models\AdminExchangeAccount;
use Illuminate\Support\Facades\Log;

class SignalGeneratorService
{
    protected $bybitService;
    protected $symbols = ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT', 'XRPUSDT'];
    protected $timeframe = '15';
    protected $minConfidence = 70;

    public function __construct()
    {
        //
    }

    protected function getAdminBybitService()
    {
        $adminAccount = AdminExchangeAccount::getBybitAccount();
        
        if (!$adminAccount) {
            throw new \Exception('No admin Bybit account configured. Please add one in settings.');
        }

        return new BybitService($adminAccount->api_key, $adminAccount->api_secret);
    }

    public function generateSignals($symbols = null, $timeframe = null, $minConfidence = null)
    {
        $symbols = $symbols ?? $this->symbols;
        $timeframe = $timeframe ?? $this->timeframe;
        $minConfidence = $minConfidence ?? $this->minConfidence;

        $signals = [];

        foreach ($symbols as $symbol) {
            try {
                $signal = $this->analyzeSymbol($symbol, $timeframe);
                
                if ($signal && $signal['confidence'] >= $minConfidence) {
                    $signals[] = $this->createSignal($signal);
                }
            } catch (\Exception $e) {
                Log::error("Failed to analyze {$symbol}: " . $e->getMessage());
            }
        }

        return $signals;
    }

    protected function analyzeSymbol($symbol, $timeframe)
    {
        $bybit = $this->getAdminBybitService();
        
        $klines = $bybit->getKlines($symbol, $timeframe, 200);
        
        if (empty($klines)) {
            return null;
        }

        $candles = $this->formatKlines($klines);
        
        $orderBlockSignal = $this->detectOrderBlock($candles, $symbol);
        if ($orderBlockSignal) {
            return $orderBlockSignal;
        }

        $fvgSignal = $this->detectFairValueGap($candles, $symbol);
        if ($fvgSignal) {
            return $fvgSignal;
        }

        $liquiditySignal = $this->detectLiquiditySweep($candles, $symbol);
        if ($liquiditySignal) {
            return $liquiditySignal;
        }

        return null;
    }

    protected function formatKlines($klines)
    {
        $candles = [];
        
        foreach ($klines as $kline) {
            $candles[] = [
                'timestamp' => $kline[0],
                'open' => (float) $kline[1],
                'high' => (float) $kline[2],
                'low' => (float) $kline[3],
                'close' => (float) $kline[4],
                'volume' => (float) $kline[5],
            ];
        }

        return array_reverse($candles);
    }

    protected function detectOrderBlock($candles, $symbol)
    {
        if (count($candles) < 20) {
            return null;
        }

        $recentCandles = array_slice($candles, -20);
        $currentPrice = $recentCandles[count($recentCandles) - 1]['close'];

        $highestHigh = max(array_column($recentCandles, 'high'));
        $lowestLow = min(array_column($recentCandles, 'low'));

        $bullishOrderBlock = $this->findBullishOrderBlock($recentCandles);
        if ($bullishOrderBlock) {
            $entryPrice = $bullishOrderBlock['low'];
            $stopLoss = $entryPrice - ($entryPrice * 0.02);
            $takeProfit = $entryPrice + ($entryPrice * 0.04);
            
            return [
                'symbol' => $symbol,
                'type' => 'long',
                'pattern' => 'Bullish Order Block',
                'confidence' => 75,
                'entry_price' => $entryPrice,
                'stop_loss' => $stopLoss,
                'take_profit' => $takeProfit,
            ];
        }

        $bearishOrderBlock = $this->findBearishOrderBlock($recentCandles);
        if ($bearishOrderBlock) {
            $entryPrice = $bearishOrderBlock['high'];
            $stopLoss = $entryPrice + ($entryPrice * 0.02);
            $takeProfit = $entryPrice - ($entryPrice * 0.04);
            
            return [
                'symbol' => $symbol,
                'type' => 'short',
                'pattern' => 'Bearish Order Block',
                'confidence' => 75,
                'entry_price' => $entryPrice,
                'stop_loss' => $stopLoss,
                'take_profit' => $takeProfit,
            ];
        }

        return null;
    }

    protected function findBullishOrderBlock($candles)
    {
        for ($i = count($candles) - 5; $i >= 3; $i--) {
            $candle = $candles[$i];
            
            if ($candle['close'] > $candle['open']) {
                $bodySize = $candle['close'] - $candle['open'];
                $totalRange = $candle['high'] - $candle['low'];
                
                if ($bodySize / $totalRange > 0.7) {
                    $prevCandle = $candles[$i - 1];
                    if ($prevCandle['close'] < $prevCandle['open']) {
                        return $candle;
                    }
                }
            }
        }
        
        return null;
    }

    protected function findBearishOrderBlock($candles)
    {
        for ($i = count($candles) - 5; $i >= 3; $i--) {
            $candle = $candles[$i];
            
            if ($candle['close'] < $candle['open']) {
                $bodySize = $candle['open'] - $candle['close'];
                $totalRange = $candle['high'] - $candle['low'];
                
                if ($bodySize / $totalRange > 0.7) {
                    $prevCandle = $candles[$i - 1];
                    if ($prevCandle['close'] > $prevCandle['open']) {
                        return $candle;
                    }
                }
            }
        }
        
        return null;
    }

    protected function detectFairValueGap($candles, $symbol)
    {
        if (count($candles) < 10) {
            return null;
        }

        $recentCandles = array_slice($candles, -10);

        for ($i = count($recentCandles) - 3; $i >= 2; $i--) {
            $current = $recentCandles[$i];
            $prev = $recentCandles[$i - 1];
            $next = $recentCandles[$i + 1];

            if ($prev['high'] < $next['low']) {
                $gapSize = $next['low'] - $prev['high'];
                $avgRange = ($current['high'] - $current['low']);
                
                if ($gapSize > $avgRange * 0.5) {
                    $entryPrice = ($prev['high'] + $next['low']) / 2;
                    $stopLoss = $prev['high'] - ($entryPrice * 0.015);
                    $takeProfit = $entryPrice + ($entryPrice * 0.03);
                    
                    return [
                        'symbol' => $symbol,
                        'type' => 'long',
                        'pattern' => 'Fair Value Gap (Bullish)',
                        'confidence' => 80,
                        'entry_price' => $entryPrice,
                        'stop_loss' => $stopLoss,
                        'take_profit' => $takeProfit,
                    ];
                }
            }

            if ($prev['low'] > $next['high']) {
                $gapSize = $prev['low'] - $next['high'];
                $avgRange = ($current['high'] - $current['low']);
                
                if ($gapSize > $avgRange * 0.5) {
                    $entryPrice = ($prev['low'] + $next['high']) / 2;
                    $stopLoss = $prev['low'] + ($entryPrice * 0.015);
                    $takeProfit = $entryPrice - ($entryPrice * 0.03);
                    
                    return [
                        'symbol' => $symbol,
                        'type' => 'short',
                        'pattern' => 'Fair Value Gap (Bearish)',
                        'confidence' => 80,
                        'entry_price' => $entryPrice,
                        'stop_loss' => $stopLoss,
                        'take_profit' => $takeProfit,
                    ];
                }
            }
        }

        return null;
    }

    protected function detectLiquiditySweep($candles, $symbol)
    {
        if (count($candles) < 30) {
            return null;
        }

        $recentCandles = array_slice($candles, -30);
        $lastCandle = $recentCandles[count($recentCandles) - 1];

        $swingLows = $this->findSwingLows($recentCandles);
        $swingHighs = $this->findSwingHighs($recentCandles);

        foreach ($swingLows as $swingLow) {
            if ($lastCandle['low'] < $swingLow['low'] && $lastCandle['close'] > $swingLow['low']) {
                $entryPrice = $lastCandle['close'];
                $stopLoss = $lastCandle['low'] - ($entryPrice * 0.01);
                $takeProfit = $entryPrice + ($entryPrice * 0.03);
                
                return [
                    'symbol' => $symbol,
                    'type' => 'long',
                    'pattern' => 'Liquidity Sweep (Bullish)',
                    'confidence' => 85,
                    'entry_price' => $entryPrice,
                    'stop_loss' => $stopLoss,
                    'take_profit' => $takeProfit,
                ];
            }
        }

        foreach ($swingHighs as $swingHigh) {
            if ($lastCandle['high'] > $swingHigh['high'] && $lastCandle['close'] < $swingHigh['high']) {
                $entryPrice = $lastCandle['close'];
                $stopLoss = $lastCandle['high'] + ($entryPrice * 0.01);
                $takeProfit = $entryPrice - ($entryPrice * 0.03);
                
                return [
                    'symbol' => $symbol,
                    'type' => 'short',
                    'pattern' => 'Liquidity Sweep (Bearish)',
                    'confidence' => 85,
                    'entry_price' => $entryPrice,
                    'stop_loss' => $stopLoss,
                    'take_profit' => $takeProfit,
                ];
            }
        }

        return null;
    }

    protected function findSwingLows($candles)
    {
        $swingLows = [];
        
        for ($i = 2; $i < count($candles) - 2; $i++) {
            $current = $candles[$i];
            $prev1 = $candles[$i - 1];
            $prev2 = $candles[$i - 2];
            $next1 = $candles[$i + 1];
            $next2 = $candles[$i + 2];
            
            if ($current['low'] < $prev1['low'] && 
                $current['low'] < $prev2['low'] && 
                $current['low'] < $next1['low'] && 
                $current['low'] < $next2['low']) {
                $swingLows[] = $current;
            }
        }
        
        return array_slice($swingLows, -5);
    }

    protected function findSwingHighs($candles)
    {
        $swingHighs = [];
        
        for ($i = 2; $i < count($candles) - 2; $i++) {
            $current = $candles[$i];
            $prev1 = $candles[$i - 1];
            $prev2 = $candles[$i - 2];
            $next1 = $candles[$i + 1];
            $next2 = $candles[$i + 2];
            
            if ($current['high'] > $prev1['high'] && 
                $current['high'] > $prev2['high'] && 
                $current['high'] > $next1['high'] && 
                $current['high'] > $next2['high']) {
                $swingHighs[] = $current;
            }
        }
        
        return array_slice($swingHighs, -5);
    }

    protected function createSignal($signalData)
    {
        $riskReward = abs(($signalData['take_profit'] - $signalData['entry_price']) / 
                         ($signalData['entry_price'] - $signalData['stop_loss']));

        return Signal::create([
            'symbol' => $signalData['symbol'],
            'exchange' => 'bybit',
            'type' => $signalData['type'],
            'timeframe' => $this->timeframe . 'm',
            'pattern' => $signalData['pattern'],
            'confidence' => $signalData['confidence'],
            'entry_price' => $signalData['entry_price'],
            'stop_loss' => $signalData['stop_loss'],
            'take_profit' => $signalData['take_profit'],
            'risk_reward_ratio' => $riskReward,
            'position_size_percent' => 5,
            'status' => 'active',
            'expires_at' => now()->addMinutes(30),
        ]);
    }
}