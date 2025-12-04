<?php

namespace App\Services;

use App\Models\Signal;
use App\Models\Setting;
use App\Models\ExchangeAccount;
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
        $adminAccount = ExchangeAccount::getBybitAccount();
        
        if (!$adminAccount) {
            throw new \Exception('No admin Bybit account configured. Please add one in settings.');
        }

        return new BybitService($adminAccount->api_key, $adminAccount->api_secret);
    }

    public function generateSignals($symbols = null, $timeframe = null, $minConfidence = null)
    {
        $timeframe = $timeframe ?? $this->timeframe;
        $minConfidence = $minConfidence ?? $this->minConfidence;

        // Check if dynamic pair selection is enabled
        $useDynamicPairs = (bool) Setting::get('signal_use_dynamic_pairs', false);
        
        if ($useDynamicPairs && $symbols === null) {
            // Fetch pairs dynamically based on volume
            $minVolume = (float) Setting::get('signal_min_volume', 5000000);
            
            Log::info('[SignalGenerator] Using dynamic pair selection', [
                'min_volume' => $minVolume
            ]);
            
            $bybit = $this->getAdminBybitService();
            $symbols = $bybit->getHighVolumeTradingPairs($minVolume);
            
            if (empty($symbols)) {
                Log::warning('[SignalGenerator] No pairs found with dynamic selection, falling back to default pairs');
                $symbols = $this->symbols;
            } else {
                Log::info('[SignalGenerator] Analyzing ' . count($symbols) . ' pairs with volume >= ' . number_format($minVolume));
            }
        } else {
            // Use provided symbols or default fixed pairs
            $symbols = $symbols ?? $this->symbols;
            
            Log::info('[SignalGenerator] Using fixed pair list', [
                'pairs_count' => count($symbols)
            ]);
        }

        $signals = [];

        foreach ($symbols as $symbol) {
            try {
                $signal = $this->analyzeSymbol($symbol, $timeframe);
                
                // Check minimum confidence threshold
                if ($signal && $signal['confidence'] >= $minConfidence) {
                    // Validate signal direction before adding
                    if ($this->validateSignalDirection($signal)) {
                        $signals[] = $signal;
                    } else {
                        Log::warning("Signal rejected due to invalid direction", [
                            'symbol' => $symbol,
                            'type' => $signal['type'],
                            'entry' => $signal['entry_price'],
                            'tp' => $signal['take_profit'],
                            'sl' => $signal['stop_loss']
                        ]);
                    }
                } else {
                    Log::debug("Signal rejected due to low confidence", [
                        'symbol' => $symbol,
                        'confidence' => $signal['confidence'] ?? 0,
                        'min_required' => $minConfidence
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to analyze {$symbol}: " . $e->getMessage());
            }
        }

        return $signals;
    }

    /**
     * Validate signal direction logic
     * Long: entry < TP and entry > SL
     * Short: entry > TP and entry < SL
     */
    protected function validateSignalDirection($signal)
    {
        $entry = $signal['entry_price'];
        $tp = $signal['take_profit'];
        $sl = $signal['stop_loss'];
        $type = $signal['type'];

        if ($type === 'long') {
            // For long: entry should be below TP and above SL
            return ($entry < $tp) && ($entry > $sl);
        } else {
            // For short: entry should be above TP and below SL
            return ($entry > $tp) && ($entry < $sl);
        }
    }

    /**
     * Determine order type based on distance from current price to entry
     * Market: Price is at or very near entry (within 0.5%)
     * Limit: Price is away from entry (more than 0.5%)
     */
    protected function determineOrderType($currentPrice, $entryPrice)
    {
        if ($currentPrice <= 0 || $entryPrice <= 0) {
            return 'Market'; // Fallback to Market if invalid prices
        }

        // Calculate percentage distance from current price to entry
        $priceDistance = abs(($currentPrice - $entryPrice) / $currentPrice) * 100;

        // If within 0.5% of entry → Market order (immediate execution)
        // If more than 0.5% away → Limit order (wait for price to reach entry)
        return $priceDistance <= 0.5 ? 'Market' : 'Limit';
    }

    protected function analyzeSymbol($symbol, $timeframe)
    {
        $bybit = $this->getAdminBybitService();
        
        $klines = $bybit->getKlines($symbol, $timeframe, 200);
        
        if (empty($klines)) {
            return null;
        }

        $candles = $this->formatKlines($klines);
        $currentPrice = $candles[0]['close']; // Most recent candle
        
        // Try each pattern detection method
        $orderBlockSignal = $this->detectOrderBlock($candles, $symbol, $currentPrice);
        if ($orderBlockSignal) {
            return $orderBlockSignal;
        }

        $fvgSignal = $this->detectFairValueGap($candles, $symbol, $currentPrice);
        if ($fvgSignal) {
            return $fvgSignal;
        }

        $liquiditySignal = $this->detectLiquiditySweep($candles, $symbol, $currentPrice);
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

        // Most recent candle first
        return array_reverse($candles);
    }

    protected function detectOrderBlock($candles, $symbol, $currentPrice)
    {
        if (count($candles) < 20) {
            return null;
        }

        $recentCandles = array_slice($candles, 0, 20);

        // Try bullish order block
        $bullishOrderBlock = $this->findBullishOrderBlock($recentCandles);
        if ($bullishOrderBlock) {
            $obLow = $bullishOrderBlock['low'];
            $obHigh = $bullishOrderBlock['high'];
            
            // Check if current price is within or near the order block zone
            if ($currentPrice >= $obLow * 0.995 && $currentPrice <= $obHigh * 1.005) {
                $entryPrice = $currentPrice;
                
                // Calculate ATR for dynamic SL/TP
                $atr = $this->calculateATR($recentCandles, 14);
                
                // SL below order block
                $stopLoss = $obLow - ($atr * 1.5);
                
                // TP using 2:1 risk/reward minimum
                $risk = $entryPrice - $stopLoss;
                $takeProfit = $entryPrice + ($risk * 2);
                
                // Determine order type based on price distance
                $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                
                return [
                    'symbol' => $symbol,
                    'type' => 'long',
                    'order_type' => $orderType,
                    'pattern' => 'Bullish Order Block',
                    'confidence' => 75,
                    'entry_price' => $entryPrice,
                    'stop_loss' => $stopLoss,
                    'take_profit' => $takeProfit,
                ];
            }
        }

        // Try bearish order block
        $bearishOrderBlock = $this->findBearishOrderBlock($recentCandles);
        if ($bearishOrderBlock) {
            $obLow = $bearishOrderBlock['low'];
            $obHigh = $bearishOrderBlock['high'];
            
            // Check if current price is within or near the order block zone
            if ($currentPrice <= $obHigh * 1.005 && $currentPrice >= $obLow * 0.995) {
                $entryPrice = $currentPrice;
                
                // Calculate ATR for dynamic SL/TP
                $atr = $this->calculateATR($recentCandles, 14);
                
                // SL above order block
                $stopLoss = $obHigh + ($atr * 1.5);
                
                // TP using 2:1 risk/reward minimum
                $risk = $stopLoss - $entryPrice;
                $takeProfit = $entryPrice - ($risk * 2);
                
                // Determine order type based on price distance
                $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                
                return [
                    'symbol' => $symbol,
                    'type' => 'short',
                    'order_type' => $orderType,
                    'pattern' => 'Bearish Order Block',
                    'confidence' => 75,
                    'entry_price' => $entryPrice,
                    'stop_loss' => $stopLoss,
                    'take_profit' => $takeProfit,
                ];
            }
        }

        return null;
    }

    protected function findBullishOrderBlock($candles)
    {
        // Look for strong bullish candle after bearish move
        for ($i = 1; $i < min(10, count($candles) - 1); $i++) {
            $candle = $candles[$i];
            $prevCandle = $candles[$i - 1];
            
            // Must be bullish candle
            if ($candle['close'] > $candle['open']) {
                $bodySize = $candle['close'] - $candle['open'];
                $totalRange = $candle['high'] - $candle['low'];
                
                // Strong bullish body (>60% of range)
                if ($totalRange > 0 && ($bodySize / $totalRange) > 0.6) {
                    // Previous candle should be bearish
                    if ($prevCandle['close'] < $prevCandle['open']) {
                        // Check if there's a downtrend before
                        $hasDowntrend = true;
                        for ($j = $i - 1; $j < min($i - 1 + 3, count($candles)); $j++) {
                            if ($candles[$j]['close'] > $candles[$j]['open']) {
                                $hasDowntrend = false;
                                break;
                            }
                        }
                        
                        if ($hasDowntrend) {
                            return $candle;
                        }
                    }
                }
            }
        }
        
        return null;
    }

    protected function findBearishOrderBlock($candles)
    {
        // Look for strong bearish candle after bullish move
        for ($i = 1; $i < min(10, count($candles) - 1); $i++) {
            $candle = $candles[$i];
            $prevCandle = $candles[$i - 1];
            
            // Must be bearish candle
            if ($candle['close'] < $candle['open']) {
                $bodySize = $candle['open'] - $candle['close'];
                $totalRange = $candle['high'] - $candle['low'];
                
                // Strong bearish body (>60% of range)
                if ($totalRange > 0 && ($bodySize / $totalRange) > 0.6) {
                    // Previous candle should be bullish
                    if ($prevCandle['close'] > $prevCandle['open']) {
                        // Check if there's an uptrend before
                        $hasUptrend = true;
                        for ($j = $i - 1; $j < min($i - 1 + 3, count($candles)); $j++) {
                            if ($candles[$j]['close'] < $candles[$j]['open']) {
                                $hasUptrend = false;
                                break;
                            }
                        }
                        
                        if ($hasUptrend) {
                            return $candle;
                        }
                    }
                }
            }
        }
        
        return null;
    }

    protected function detectFairValueGap($candles, $symbol, $currentPrice)
    {
        if (count($candles) < 10) {
            return null;
        }

        $recentCandles = array_slice($candles, 0, 10);

        // Look for bullish FVG
        for ($i = 2; $i < count($recentCandles); $i++) {
            $candle1 = $recentCandles[$i];     // Oldest
            $candle2 = $recentCandles[$i - 1]; // Middle
            $candle3 = $recentCandles[$i - 2]; // Newest
            
            // Bullish FVG: candle1 high < candle3 low (gap up)
            if ($candle1['high'] < $candle3['low']) {
                $gapSize = $candle3['low'] - $candle1['high'];
                $avgRange = ($candle2['high'] - $candle2['low']);
                
                // Gap must be significant
                if ($avgRange > 0 && $gapSize > $avgRange * 0.3) {
                    // Check if current price is in or near gap
                    if ($currentPrice >= $candle1['high'] * 0.998 && $currentPrice <= $candle3['low'] * 1.002) {
                        $entryPrice = $currentPrice;
                        $atr = $this->calculateATR($recentCandles, 14);
                        
                        $stopLoss = $candle1['high'] - ($atr * 1.0);
                        $risk = $entryPrice - $stopLoss;
                        $takeProfit = $entryPrice + ($risk * 2.5);
                        
                        // Determine order type based on price distance
                        $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                        
                        return [
                            'symbol' => $symbol,
                            'type' => 'long',
                            'order_type' => $orderType,
                            'pattern' => 'Fair Value Gap (Bullish)',
                            'confidence' => 80,
                            'entry_price' => $entryPrice,
                            'stop_loss' => $stopLoss,
                            'take_profit' => $takeProfit,
                        ];
                    }
                }
            }

            // Bearish FVG: candle1 low > candle3 high (gap down)
            if ($candle1['low'] > $candle3['high']) {
                $gapSize = $candle1['low'] - $candle3['high'];
                $avgRange = ($candle2['high'] - $candle2['low']);
                
                if ($avgRange > 0 && $gapSize > $avgRange * 0.3) {
                    if ($currentPrice <= $candle1['low'] * 1.002 && $currentPrice >= $candle3['high'] * 0.998) {
                        $entryPrice = $currentPrice;
                        $atr = $this->calculateATR($recentCandles, 14);
                        
                        $stopLoss = $candle1['low'] + ($atr * 1.0);
                        $risk = $stopLoss - $entryPrice;
                        $takeProfit = $entryPrice - ($risk * 2.5);
                        
                        // Determine order type based on price distance
                        $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                        
                        return [
                            'symbol' => $symbol,
                            'type' => 'short',
                            'order_type' => $orderType,
                            'pattern' => 'Fair Value Gap (Bearish)',
                            'confidence' => 80,
                            'entry_price' => $entryPrice,
                            'stop_loss' => $stopLoss,
                            'take_profit' => $takeProfit,
                        ];
                    }
                }
            }
        }

        return null;
    }

    protected function detectLiquiditySweep($candles, $symbol, $currentPrice)
    {
        if (count($candles) < 30) {
            return null;
        }

        $recentCandles = array_slice($candles, 0, 30);
        $lastCandle = $recentCandles[0];

        $swingLows = $this->findSwingLows($recentCandles);
        $swingHighs = $this->findSwingHighs($recentCandles);

        // Bullish liquidity sweep
        foreach ($swingLows as $swingLow) {
            // Last candle swept below swing low but closed above it
            if ($lastCandle['low'] < $swingLow['low'] && $lastCandle['close'] > $swingLow['low']) {
                // Confirm reversal pattern
                if ($lastCandle['close'] > $lastCandle['open']) { // Bullish close
                    $entryPrice = $currentPrice;
                    $atr = $this->calculateATR($recentCandles, 14);
                    
                    $stopLoss = $lastCandle['low'] - ($atr * 1.0);
                    $risk = $entryPrice - $stopLoss;
                    $takeProfit = $entryPrice + ($risk * 2.5);
                    
                    // Determine order type based on price distance
                    $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                    
                    return [
                        'symbol' => $symbol,
                        'type' => 'long',
                        'order_type' => $orderType,
                        'pattern' => 'Liquidity Sweep (Bullish)',
                        'confidence' => 85,
                        'entry_price' => $entryPrice,
                        'stop_loss' => $stopLoss,
                        'take_profit' => $takeProfit,
                    ];
                }
            }
        }

        // Bearish liquidity sweep
        foreach ($swingHighs as $swingHigh) {
            // Last candle swept above swing high but closed below it
            if ($lastCandle['high'] > $swingHigh['high'] && $lastCandle['close'] < $swingHigh['high']) {
                // Confirm reversal pattern
                if ($lastCandle['close'] < $lastCandle['open']) { // Bearish close
                    $entryPrice = $currentPrice;
                    $atr = $this->calculateATR($recentCandles, 14);
                    
                    $stopLoss = $lastCandle['high'] + ($atr * 1.0);
                    $risk = $stopLoss - $entryPrice;
                    $takeProfit = $entryPrice - ($risk * 2.5);
                    
                    // Determine order type based on price distance
                    $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                    
                    return [
                        'symbol' => $symbol,
                        'type' => 'short',
                        'order_type' => $orderType,
                        'pattern' => 'Liquidity Sweep (Bearish)',
                        'confidence' => 85,
                        'entry_price' => $entryPrice,
                        'stop_loss' => $stopLoss,
                        'take_profit' => $takeProfit,
                    ];
                }
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

    /**
     * Calculate Average True Range for dynamic SL/TP
     */
    protected function calculateATR($candles, $period = 14)
    {
        if (count($candles) < $period + 1) {
            // Fallback to simple range average
            $ranges = array_map(function($c) {
                return $c['high'] - $c['low'];
            }, array_slice($candles, 0, min($period, count($candles))));
            return array_sum($ranges) / count($ranges);
        }

        $trueRanges = [];
        
        for ($i = 0; $i < min($period, count($candles) - 1); $i++) {
            $current = $candles[$i];
            $previous = $candles[$i + 1];
            
            $tr = max(
                $current['high'] - $current['low'],
                abs($current['high'] - $previous['close']),
                abs($current['low'] - $previous['close'])
            );
            
            $trueRanges[] = $tr;
        }
        
        return array_sum($trueRanges) / count($trueRanges);
    }

    /**
     * Create signal with order type
     */
    public function createSignal($signalData, $timeframe)
    {
        $riskReward = abs(($signalData['take_profit'] - $signalData['entry_price']) / 
                         ($signalData['entry_price'] - $signalData['stop_loss']));
        
        $expiry_time = Setting::get('signal_expiry', 30);

        return Signal::create([
            'symbol' => $signalData['symbol'],
            'exchange' => 'bybit',
            'type' => $signalData['type'],
            'order_type' => $signalData['order_type'] ?? 'Market',
            'timeframe' => $timeframe,
            'pattern' => $signalData['pattern'],
            'confidence' => $signalData['confidence'],
            'entry_price' => $signalData['entry_price'],
            'stop_loss' => $signalData['stop_loss'],
            'take_profit' => $signalData['take_profit'],
            'risk_reward_ratio' => $riskReward,
            'position_size_percent' => 5,
            'status' => 'pending', // Start as pending, not active
            'expires_at' => now()->addMinutes($expiry_time),
        ]);
    }
}