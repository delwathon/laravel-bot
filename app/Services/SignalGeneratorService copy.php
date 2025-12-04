<?php

namespace App\Services;

use App\Models\Signal;
use App\Models\Setting;
use App\Models\ExchangeAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Collection;

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

    /**
     * Generate signals with parallel processing
     */
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

        // Parallel processing for faster analysis
        $signals = $this->analyzeSymbolsInParallel($symbols, $timeframe, $minConfidence);

        return $signals;
    }

    /**
     * Analyze multiple symbols in parallel for faster processing
     * Similar to Python's ThreadPoolExecutor
     */
    protected function analyzeSymbolsInParallel($symbols, $timeframe, $minConfidence)
    {
        $signals = [];
        $chunkSize = 5; // Process 5 symbols at a time
        
        Log::info('[SignalGenerator] Starting parallel analysis', [
            'total_symbols' => count($symbols),
            'chunk_size' => $chunkSize
        ]);

        // Split symbols into chunks for batch processing
        $chunks = array_chunk($symbols, $chunkSize);
        
        foreach ($chunks as $chunkIndex => $chunk) {
            Log::info('[SignalGenerator] Processing chunk ' . ($chunkIndex + 1) . '/' . count($chunks));
            
            // Process each symbol in the chunk
            $chunkResults = [];
            foreach ($chunk as $symbol) {
                try {
                    $signal = $this->analyzeSymbol($symbol, $timeframe);
                    
                    // Check minimum confidence threshold
                    if ($signal && $signal['confidence'] >= $minConfidence) {
                        
                        // NOTE: order_type is added in analyzeSymbol and its sub-methods
                        
                        // 1. Validate signal direction (SL/TP relative to Entry)
                        if ($this->validateSignalDirection($signal)) {
                            
                            // 2. Validate Entry Price relative to Current Price (THE FIX)
                            if ($this->validateEntryVsCurrentPrice($signal)) {
                                $chunkResults[] = $signal;
                            } else {
                                // Log already handled in validateEntryVsCurrentPrice
                            }
                        } else {
                            Log::warning("Signal rejected due to invalid direction (SL/TP placement)", [
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
            
            $signals = array_merge($signals, $chunkResults);
        }

        Log::info('[SignalGenerator] Parallel analysis completed', [
            'total_signals_found' => count($signals)
        ]);

        return $signals;
    }

    /**
     * Validate signal direction logic (Entry/SL/TP relationship)
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
     * Validate that the signal's entry price is not drastically chasing the current price.
     * This prevents LONG signals where Entry is FAR above Current, and SHORT 
     * signals where Entry is FAR below Current, solving the user's issue.
     */
    protected function validateEntryVsCurrentPrice($signal)
    {
        $entry = $signal['entry_price'];
        $current = $signal['current_price'];
        $type = $signal['type'];
        
        // Allow a small tolerance (e.g., 2% deviation) for limit orders waiting for a pullback
        $tolerance = 0.02; 
        
        // Calculate the maximum percentage distance between current and entry
        $distance = abs(($current - $entry) / $current);

        if ($distance > $tolerance) {
             // If the distance is greater than tolerance, check the direction
             
             if ($type === 'long' && $entry > $current) {
                // REJECT LONG if Entry is significantly ABOVE Current Price
                Log::warning("LONG signal rejected: Entry ($entry) is significantly above Current Price ($current) [Deviation: " . round($distance * 100, 2) . "%]", [
                    'symbol' => $signal['symbol'],
                ]);
                return false;
            } elseif ($type === 'short' && $entry < $current) {
                // REJECT SHORT if Entry is significantly BELOW Current Price
                Log::warning("SHORT signal rejected: Entry ($entry) is significantly below Current Price ($current) [Deviation: " . round($distance * 100, 2) . "%]", [
                    'symbol' => $signal['symbol'],
                ]);
                return false;
            }
        }
        
        // If within tolerance, or if the deviation is favorable (e.g. Current > Entry for a SHORT limit order)
        return true;
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
        $orderType = $priceDistance <= 0.5 ? 'Market' : 'Limit';
        
        Log::debug('[SignalGenerator] Order type determination', [
            'current_price' => $currentPrice,
            'entry_price' => $entryPrice,
            'distance_percent' => round($priceDistance, 2),
            'order_type' => $orderType
        ]);
        
        return $orderType;
    }

    /**
     * Analyze a single symbol for trading signals
     * Now fetches real-time current price instead of using historical kline data
     */
    protected function analyzeSymbol($symbol, $timeframe)
    {
        $bybit = $this->getAdminBybitService();
        
        // Get historical klines for pattern analysis
        $klines = $bybit->getKlines($symbol, $timeframe, 200);
        
        if (empty($klines)) {
            Log::warning("[SignalGenerator] No kline data available for {$symbol}");
            return null;
        }

        $candles = $this->formatKlines($klines);
        
        // ✅ Get REAL-TIME current price from ticker instead of using kline data
        $currentPrice = $bybit->getCurrentPrice($symbol);
        
        if ($currentPrice === null) {
            Log::warning("[SignalGenerator] Could not fetch real-time price for {$symbol}, falling back to kline data");
            $currentPrice = $candles[0]['close']; // Fallback only
        } else {
            Log::info("[SignalGenerator] Using real-time price for {$symbol}: {$currentPrice}");
        }
        
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
            
            // Entry at the middle of order block zone (not current price!)
            $entryPrice = ($obLow + $obHigh) / 2;
            
            // Calculate ATR for dynamic SL/TP
            $atr = $this->calculateATR($recentCandles, 14);
            
            // SL below order block
            $stopLoss = $obLow - ($atr * 1.5);
            
            // TP using 2:1 risk/reward minimum
            $risk = $entryPrice - $stopLoss;
            $takeProfit = $entryPrice + ($risk * 2.5);
            
            // CRITICAL: Validate stop loss is below entry for LONG
            if ($stopLoss >= $entryPrice) {
                Log::warning("Order Block (Bullish) rejected - SL above entry", [
                    'symbol' => $symbol,
                    'entry' => $entryPrice,
                    'sl' => $stopLoss
                ]);
                return null;
            }
            
            // Determine order type based on distance from CURRENT price to ENTRY price
            $orderType = $this->determineOrderType($currentPrice, $entryPrice);
            
            // Only generate signal if current price is within or near the order block zone
            if ($currentPrice >= $obLow * 0.99 && $currentPrice <= $obHigh * 1.01) {
                return [
                    'symbol' => $symbol,
                    'type' => 'long',
                    'order_type' => $orderType,
                    'pattern' => 'Order Block (Bullish)',
                    'confidence' => 85,
                    'current_price' => $currentPrice,
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
            
            // Entry at the middle of order block zone (not current price!)
            $entryPrice = ($obLow + $obHigh) / 2;
            
            $atr = $this->calculateATR($recentCandles, 14);
            
            // SL above order block
            $stopLoss = $obHigh + ($atr * 1.5);
            
            $risk = $stopLoss - $entryPrice;
            $takeProfit = $entryPrice - ($risk * 2.5);
            
            // CRITICAL: Validate stop loss is above entry for SHORT
            if ($stopLoss <= $entryPrice) {
                Log::warning("Order Block (Bearish) rejected - SL below entry", [
                    'symbol' => $symbol,
                    'entry' => $entryPrice,
                    'sl' => $stopLoss
                ]);
                return null;
            }
            
            // Determine order type based on distance from CURRENT price to ENTRY price
            $orderType = $this->determineOrderType($currentPrice, $entryPrice);
            
            // Only generate signal if current price is within or near the order block zone
            if ($currentPrice >= $obLow * 0.99 && $currentPrice <= $obHigh * 1.01) {
                return [
                    'symbol' => $symbol,
                    'type' => 'short',
                    'order_type' => $orderType,
                    'pattern' => 'Order Block (Bearish)',
                    'confidence' => 85,
                    'current_price' => $currentPrice,
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
        for ($i = 1; $i < count($candles) - 1; $i++) {
            $current = $candles[$i];
            $prev = $candles[$i - 1];
            $next = $candles[$i + 1];
            
            // Strong bearish candle followed by reversal
            if ($current['close'] < $current['open'] && 
                ($current['open'] - $current['close']) > ($current['high'] - $current['low']) * 0.6) {
                
                if ($next['close'] > $next['open']) {
                    return [
                        'low' => $current['low'],
                        'high' => $current['open'],
                    ];
                }
            }
        }
        return null;
    }

    protected function findBearishOrderBlock($candles)
    {
        for ($i = 1; $i < count($candles) - 1; $i++) {
            $current = $candles[$i];
            $prev = $candles[$i - 1];
            $next = $candles[$i + 1];
            
            // Strong bullish candle followed by reversal
            if ($current['close'] > $current['open'] && 
                ($current['close'] - $current['open']) > ($current['high'] - $current['low']) * 0.6) {
                
                if ($next['close'] < $next['open']) {
                    return [
                        'low' => $current['close'],
                        'high' => $current['high'],
                    ];
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

        for ($i = 0; $i < count($recentCandles) - 3; $i++) {
            $candle1 = $recentCandles[$i];
            $candle2 = $recentCandles[$i + 1];
            $candle3 = $recentCandles[$i + 2];

            // Bullish FVG: candle1 high < candle3 low (gap up)
            if ($candle1['high'] < $candle3['low']) {
                $gapSize = $candle3['low'] - $candle1['high'];
                $avgRange = ($candle2['high'] - $candle2['low']);
                
                if ($avgRange > 0 && $gapSize > $avgRange * 0.3) {
                    // Entry at the middle of the gap (not current price!)
                    $entryPrice = ($candle1['high'] + $candle3['low']) / 2;
                    
                    $atr = $this->calculateATR($recentCandles, 14);
                    
                    $stopLoss = $candle1['high'] - ($atr * 1.0);
                    $risk = $entryPrice - $stopLoss;
                    $takeProfit = $entryPrice + ($risk * 2.5);
                    
                    // CRITICAL: Validate stop loss is below entry for LONG
                    if ($stopLoss >= $entryPrice) {
                        Log::warning("FVG (Bullish) rejected - SL above entry", [
                            'symbol' => $symbol,
                            'entry' => $entryPrice,
                            'sl' => $stopLoss
                        ]);
                        continue;
                    }
                    
                    // Determine order type based on distance from CURRENT price to ENTRY price
                    $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                    
                    // Only generate if current price is near the gap
                    if ($currentPrice >= $candle1['high'] * 0.98 && $currentPrice <= $candle3['low'] * 1.02) {
                        return [
                            'symbol' => $symbol,
                            'type' => 'long',
                            'order_type' => $orderType,
                            'pattern' => 'Fair Value Gap (Bullish)',
                            'confidence' => 80,
                            'current_price' => $currentPrice,
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
                    // Entry at the middle of the gap (not current price!)
                    $entryPrice = ($candle1['low'] + $candle3['high']) / 2;
                    
                    $atr = $this->calculateATR($recentCandles, 14);
                    
                    $stopLoss = $candle1['low'] + ($atr * 1.0);
                    $risk = $stopLoss - $entryPrice;
                    $takeProfit = $entryPrice - ($risk * 2.5);
                    
                    // CRITICAL: Validate stop loss is above entry for SHORT
                    if ($stopLoss <= $entryPrice) {
                        Log::warning("FVG (Bearish) rejected - SL below entry", [
                            'symbol' => $symbol,
                            'entry' => $entryPrice,
                            'sl' => $stopLoss
                        ]);
                        continue;
                    }
                    
                    // Determine order type based on distance from CURRENT price to ENTRY price
                    $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                    
                    // Only generate if current price is near the gap
                    if ($currentPrice <= $candle1['low'] * 1.02 && $currentPrice >= $candle3['high'] * 0.98) {
                        return [
                            'symbol' => $symbol,
                            'type' => 'short',
                            'order_type' => $orderType,
                            'pattern' => 'Fair Value Gap (Bearish)',
                            'confidence' => 80,
                            'current_price' => $currentPrice,
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
                    // Entry above the swing low that was swept (optimal reentry point)
                    $entryPrice = $swingLow['low'] * 1.002; // Slightly above swept low
                    
                    $atr = $this->calculateATR($recentCandles, 14);
                    
                    $stopLoss = $lastCandle['low'] - ($atr * 1.0);
                    $risk = $entryPrice - $stopLoss;
                    $takeProfit = $entryPrice + ($risk * 2.5);
                    
                    // CRITICAL: Validate that current price makes sense for this setup
                    // For LONG: current price should be near or below entry (not way above)
                    // Skip if current price is more than 5% above entry (pattern is stale)
                    $priceDeviation = (($currentPrice - $entryPrice) / $entryPrice) * 100;
                    
                    if ($priceDeviation > 5) {
                        Log::debug("Liquidity Sweep (Bullish) rejected - price moved too far from pattern", [
                            'symbol' => $symbol,
                            'current_price' => $currentPrice,
                            'entry_price' => $entryPrice,
                            'deviation_percent' => round($priceDeviation, 2)
                        ]);
                        continue; // Skip this pattern
                    }
                    
                    // Also skip if price is way below entry (pattern already played out)
                    if ($priceDeviation < -20) {
                        Log::debug("Liquidity Sweep (Bullish) rejected - price moved too far below pattern", [
                            'symbol' => $symbol,
                            'current_price' => $currentPrice,
                            'entry_price' => $entryPrice,
                            'deviation_percent' => round($priceDeviation, 2)
                        ]);
                        continue;
                    }
                    
                    // Determine order type based on distance from CURRENT price to ENTRY price
                    $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                    
                    return [
                        'symbol' => $symbol,
                        'type' => 'long',
                        'order_type' => $orderType,
                        'pattern' => 'Liquidity Sweep (Bullish)',
                        'confidence' => 85,
                        'current_price' => $currentPrice,
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
                    // Entry below the swing high that was swept (optimal reentry point)
                    $entryPrice = $swingHigh['high'] * 0.998; // Slightly below swept high
                    
                    $atr = $this->calculateATR($recentCandles, 14);
                    
                    $stopLoss = $lastCandle['high'] + ($atr * 1.0);
                    $risk = $stopLoss - $entryPrice;
                    $takeProfit = $entryPrice - ($risk * 2.5);
                    
                    // CRITICAL: Validate that current price makes sense for this setup
                    // For SHORT: current price should be near or above entry (not way below)
                    // Skip if current price is more than 5% below entry (pattern is stale)
                    $priceDeviation = (($entryPrice - $currentPrice) / $entryPrice) * 100;
                    
                    if ($priceDeviation > 5) {
                        Log::debug("Liquidity Sweep (Bearish) rejected - price moved too far from pattern", [
                            'symbol' => $symbol,
                            'current_price' => $currentPrice,
                            'entry_price' => $entryPrice,
                            'deviation_percent' => round($priceDeviation, 2)
                        ]);
                        continue; // Skip this pattern
                    }
                    
                    // Also skip if price is way above entry (pattern already played out)
                    if ($priceDeviation < -20) {
                        Log::debug("Liquidity Sweep (Bearish) rejected - price moved too far above pattern", [
                            'symbol' => $symbol,
                            'current_price' => $currentPrice,
                            'entry_price' => $entryPrice,
                            'deviation_percent' => round($priceDeviation, 2)
                        ]);
                        continue;
                    }
                    
                    // Determine order type based on distance from CURRENT price to ENTRY price
                    $orderType = $this->determineOrderType($currentPrice, $entryPrice);
                    
                    return [
                        'symbol' => $symbol,
                        'type' => 'short',
                        'order_type' => $orderType,
                        'pattern' => 'Liquidity Sweep (Bearish)',
                        'confidence' => 85,
                        'current_price' => $currentPrice,
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
            'current_price' => $signalData['current_price'],
            'entry_price' => $signalData['entry_price'],
            'stop_loss' => $signalData['stop_loss'],
            'take_profit' => $signalData['take_profit'],
            'risk_reward_ratio' => $riskReward,
            'position_size_percent' => Setting::get('signal_position_size', 5),
            'status' => 'pending', // Start as pending, not active
            'expires_at' => now()->addMinutes($expiry_time),
        ]);
    }
}