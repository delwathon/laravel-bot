<?php

namespace App\Services;

use App\Models\Signal;
use App\Models\Setting;
use App\Models\ExchangeAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class SignalGeneratorService
{
    protected $bybitService;
    protected $symbols = ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT', 'XRPUSDT'];
    protected $primaryTimeframe; // e.g., '15' for 15 minutes
    protected $higherTimeframe;  // e.g., '240' for 4 hours
    protected $minConfidence = 70;

    public function __construct()
    {
        // Get Timeframes from Database, defaulting to common SMC settings
        $this->primaryTimeframe = Setting::get('signal_primary_timeframe', '15');
        $this->higherTimeframe = Setting::get('signal_higher_timeframe', '240');
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
     * Updated to support max pair limit
     */
    public function generateSignals($symbols = null, $primaryTimeframe = null, $minConfidence = null)
    {
        $primaryTimeframe = $primaryTimeframe ?? $this->primaryTimeframe;
        $minConfidence = $minConfidence ?? $this->minConfidence;

        // Dynamic Pair Selection Logic
        $useDynamicPairs = (bool) Setting::get('signal_use_dynamic_pairs', false);
        
        if ($useDynamicPairs && $symbols === null) {
            $minVolume = (float) Setting::get('signal_min_volume', 5000000);
            $maxPairs = (int) Setting::get('signal_max_pairs', 25); // NEW: Maximum pairs to analyze
            
            Log::info('[SignalGenerator] Using dynamic pair selection', [
                'min_volume' => $minVolume,
                'max_pairs' => $maxPairs
            ]);
            
            try {
                $bybit = $this->getAdminBybitService();
                $symbols = $bybit->getHighVolumeTradingPairs($minVolume);
                
                // NEW: Limit to max pairs
                if (count($symbols) > $maxPairs) {
                    Log::info('[SignalGenerator] Limiting pairs from ' . count($symbols) . ' to ' . $maxPairs);
                    $symbols = array_slice($symbols, 0, $maxPairs);
                }
            } catch (\Exception $e) {
                Log::error('[SignalGenerator] Failed to get dynamic pairs: ' . $e->getMessage());
                $symbols = null;
            }
            
            if (empty($symbols)) {
                Log::warning('[SignalGenerator] No pairs found with dynamic selection, falling back to default pairs');
                $symbols = $this->symbols;
            } else {
                Log::info('[SignalGenerator] Analyzing ' . count($symbols) . ' pairs with volume >= ' . number_format($minVolume));
            }
        } else {
            $symbols = $symbols ?? $this->symbols;
            
            // NEW: Also apply limit to fixed pairs if specified
            $maxPairs = (int) Setting::get('signal_max_pairs', 999999);
            if (count($symbols) > $maxPairs) {
                Log::info('[SignalGenerator] Limiting fixed pairs from ' . count($symbols) . ' to ' . $maxPairs);
                $symbols = array_slice($symbols, 0, $maxPairs);
            }
            
            Log::info('[SignalGenerator] Using fixed pair list', ['pairs_count' => count($symbols)]);
        }

        // Parallel processing for faster analysis
        $signals = $this->analyzeSymbolsInParallel($symbols, $primaryTimeframe, $minConfidence);

        return $signals;
    }

    /**
     * Analyze multiple symbols in parallel for faster processing
     */
    protected function analyzeSymbolsInParallel($symbols, $primaryTimeframe, $minConfidence)
    {
        $signals = [];
        $chunkSize = 5; 
        
        Log::info('[SignalGenerator] Starting parallel analysis', [
            'total_symbols' => count($symbols),
            'chunk_size' => $chunkSize
        ]);

        $chunks = array_chunk($symbols, $chunkSize);
        
        foreach ($chunks as $chunkIndex => $chunk) {
            Log::info('[SignalGenerator] Processing chunk ' . ($chunkIndex + 1) . '/' . count($chunks));
            
            $chunkResults = [];
            foreach ($chunk as $symbol) {
                try {
                    // Pass the primary timeframe, HTF, and min confidence
                    $signal = $this->analyzeSymbol($symbol, $primaryTimeframe, $this->higherTimeframe, $minConfidence);
                    
                    if ($signal) {
                        $chunkResults[] = $signal;
                    } 
                } catch (\Exception $e) {
                    Log::error("[SignalGenerator] Failed to analyze {$symbol}: " . $e->getMessage(), [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
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
     * Analyze a single symbol for SMC trading signals
     */
    protected function analyzeSymbol($symbol, $primaryTimeframe, $higherTimeframe, $minConfidence)
    {
        try {
            $bybit = $this->getAdminBybitService();
        } catch (\Exception $e) {
            Log::error("[SignalGenerator] Failed to get admin Bybit service: " . $e->getMessage());
            return null;
        }
        
        // 1. Fetch Primary and Higher Timeframe data
        try {
            $primaryKlines = $bybit->getKlines($symbol, $primaryTimeframe, 200);
            $htfKlines = $bybit->getKlines($symbol, $higherTimeframe, 100);
        } catch (\Exception $e) {
            Log::error("[SignalGenerator] Failed to fetch klines for {$symbol}: " . $e->getMessage());
            return null;
        }

        if (empty($primaryKlines) || empty($htfKlines)) {
            Log::warning("[SignalGenerator] Incomplete kline data for {$symbol} on one or both timeframes.");
            return null;
        }

        $primaryCandles = $this->formatKlines($primaryKlines);
        $htfCandles = $this->formatKlines($htfKlines);
        
        // 2. Get REAL-TIME current price
        try {
            $currentPrice = $bybit->getCurrentPrice($symbol);
            if ($currentPrice === null || $currentPrice <= 0) {
                $currentPrice = $primaryCandles[0]['close']; // Fallback
            }
        } catch (\Exception $e) {
            Log::warning("[SignalGenerator] Failed to get current price for {$symbol}, using last close");
            $currentPrice = $primaryCandles[0]['close'];
        }

        // --- STEP 1: CONTEXT ANALYSIS (SMC RELIABILITY) ---
        
        // a. Determine Market Structure Bias (BOS/CHoCH) on Primary TF
        $structureBias = $this->detectMarketStructure($primaryCandles, 100);
        Log::info("[{$symbol}] Primary Structure Bias: {$structureBias}");

        // b. Determine Higher Timeframe (HTF) Trend Bias
        $htfBias = $this->detectHTFTrend($htfCandles);
        Log::info("[{$symbol}] HTF Trend Bias: {$htfBias}");

        // --- STEP 2: PATTERN DETECTION ---
        
        $signals = [];
        $signals[] = $this->detectOrderBlock($primaryCandles, $symbol, $currentPrice);
        $signals[] = $this->detectFairValueGap($primaryCandles, $symbol, $currentPrice);
        $signals[] = $this->detectLiquiditySweep($primaryCandles, $symbol, $currentPrice);

        // --- STEP 3: SIGNAL FILTERING & SCORING ---
        
        foreach ($signals as $signal) {
            if (!$signal) continue;

            $signal['current_price'] = $currentPrice;
            $signal['order_type'] = $this->determineOrderType($currentPrice, $signal['entry_price']);
            
            // Store initial confidence before adjustments
            $signal['initial_confidence'] = $signal['confidence'];
            $confidence = $signal['initial_confidence'];
            $type = $signal['type']; 
            
            // Initialize score adjustments
            $scoreStructure = 0;
            $scoreHTF = 0;
            $scoreDiscountPremium = 0;

            // a. Filter 1: Validate Stop Loss/Take Profit Placement (Risk Check)
            if (!$this->validateSignalDirection($signal)) {
                Log::debug("[{$symbol}] Signal rejected: Invalid SL/TP placement.");
                continue;
            }

            // b. Filter 2: Validate Entry Price vs. Current Price (Proximity Check)
            if (!$this->validateEntryVsCurrentPrice($signal)) {
                Log::debug("[{$symbol}] Signal rejected: Entry is too far from current price in wrong direction.");
                continue;
            }

            // c. Scoring 1: Market Structure Alignment
            if (($type === 'long' && $structureBias === 'bullish') || 
                ($type === 'short' && $structureBias === 'bearish')) {
                $scoreStructure = 10; // +10 for aligned structure
            } elseif ($structureBias === 'ranging') {
                $scoreStructure = -5; // -5 for ranging market
            } else {
                $scoreStructure = -15; // -15 for counter-structure trade
            }
            $confidence += $scoreStructure;
            
            // d. Scoring 2: HTF Bias Alignment (The MTF Filter)
            if ($type === $htfBias) {
                $scoreHTF = 15; // +15 for aligned HTF trend (Highest boost)
            } else {
                $scoreHTF = -10; // -10 for counter-trend HTF trade
            }
            $confidence += $scoreHTF;

            // e. Scoring 3: Discount/Premium Check
            $discountPremiumStatus = $this->checkDiscountPremium($htfCandles, $type, $signal['entry_price']);
            if ($discountPremiumStatus === 'favorable') {
                $scoreDiscountPremium = 10; // +10 for favorable zone entry
            } elseif ($discountPremiumStatus === 'unfavorable') {
                 $scoreDiscountPremium = -10; // -10 for unfavorable zone entry
            }
            $confidence += $scoreDiscountPremium;

            $signal['confidence'] = max(0, min(100, $confidence)); // Keep confidence between 0-100
            
            // --- Preparation for analysis_data column ---
            $signal['analysis_data_package'] = [
                'market_context' => [
                    'primary_structure_bias' => $structureBias,
                    'htf_trend_bias' => $htfBias,
                    'discount_premium_zone' => $discountPremiumStatus,
                    'order_type_reason' => $signal['order_type'],
                ],
                'confidence_breakdown' => [
                    'base_confidence' => $signal['initial_confidence'],
                    'structure_adjustment' => $scoreStructure,
                    'htf_adjustment' => $scoreHTF,
                    'dp_adjustment' => $scoreDiscountPremium,
                    'final_confidence_score' => $signal['confidence']
                ],
            ];

            // Final Confidence Check
            if ($signal['confidence'] >= $minConfidence) {
                Log::info("[{$symbol}] Signal meets confidence threshold", [
                    'pattern' => $signal['pattern'],
                    'confidence' => $signal['confidence'],
                    'type' => $signal['type']
                ]);
                return $signal;
            } else {
                Log::debug("[{$symbol}] Signal rejected: Confidence {$signal['confidence']} < {$minConfidence}");
            }
        }

        return null;
    }

    // =========================================================================
    // SMC CONTEXT ANALYSIS METHODS
    // =========================================================================

    /**
     * Determines Market Structure (BOS/CHoCH) by looking for HH/HL or LH/LL.
     * Simplistic 3-point swing analysis.
     */
    protected function detectMarketStructure($candles, $lookback = 100)
    {
        if (count($candles) < $lookback) {
            return 'ranging';
        }
        $recentCandles = array_slice($candles, 0, $lookback);

        $highs = array_column($recentCandles, 'high');
        $lows = array_column($recentCandles, 'low');
        
        $currentHigh = $recentCandles[0]['high'];
        $currentLow = $recentCandles[0]['low'];

        // Get the last 3 major swings (can be improved with a proper swing detector)
        $recentMax = max(array_slice($highs, 0, 10)); 
        $recentMin = min(array_slice($lows, 0, 10));

        // Use a simple lookback window to find the highest high (HH) and lowest low (LL)
        $hhIndex = array_search(max($highs), $highs);
        $llIndex = array_search(min($lows), $lows);

        if ($hhIndex < $llIndex && $currentHigh > $recentMax) {
            // Price is making a new high after a clear low
            return 'bullish'; // Higher Highs (HH)
        } elseif ($llIndex < $hhIndex && $currentLow < $recentMin) {
            // Price is making a new low after a clear high
            return 'bearish'; // Lower Lows (LL)
        }
        
        // Final check based on close price relative to the average
        $avgPrice = array_sum($highs) / $lookback;
        $lastClose = $recentCandles[0]['close'];

        if ($lastClose > $avgPrice * 1.01 && $recentCandles[0]['close'] > $recentCandles[0]['open']) {
            return 'bullish';
        } elseif ($lastClose < $avgPrice * 0.99 && $recentCandles[0]['close'] < $recentCandles[0]['open']) {
            return 'bearish';
        }

        return 'ranging';
    }

    /**
     * Determines Higher Timeframe (HTF) trend bias using simple moving averages (SMAs).
     */
    protected function detectHTFTrend($htfCandles)
    {
        if (count($htfCandles) < 50) {
            return 'ranging';
        }

        // Use 21-period and 50-period SMAs (common trend filter)
        $closes = array_column($htfCandles, 'close');
        
        $sma21 = $this->calculateSMA($closes, 21);
        $sma50 = $this->calculateSMA($closes, 50);

        $currentClose = $htfCandles[0]['close'];

        if ($currentClose > $sma21 && $sma21 > $sma50) {
            return 'long'; // Bullish alignment
        } elseif ($currentClose < $sma21 && $sma21 < $sma50) {
            return 'short'; // Bearish alignment
        }

        return 'ranging'; // Price is in chop or consolidation
    }

    /**
     * Checks if the signal entry price falls within a Discount (LONG) or Premium (SHORT) zone.
     */
    protected function checkDiscountPremium($htfCandles, $type, $entryPrice)
    {
        if (count($htfCandles) < 50) {
            return 'neutral';
        }

        $lookback = 50;
        $closes = array_column(array_slice($htfCandles, 0, $lookback), 'close');
        
        // Define the trading range based on the highest high and lowest low of the lookback period
        $rangeHigh = max($closes);
        $rangeLow = min($closes);
        
        $midpoint = ($rangeHigh + $rangeLow) / 2;

        if ($type === 'long') {
            // Favorable LONG entry is in the Discount Zone (lower 50% of the range)
            if ($entryPrice < $midpoint) {
                return 'favorable'; // Entry is in Discount
            }
            // Unfavorable LONG entry is in the Premium Zone (upper 50% of the range)
            if ($entryPrice > $midpoint) {
                return 'unfavorable';
            }
        } elseif ($type === 'short') {
            // Favorable SHORT entry is in the Premium Zone (upper 50% of the range)
            if ($entryPrice > $midpoint) {
                return 'favorable'; // Entry is in Premium
            }
            // Unfavorable SHORT entry is in the Discount Zone (lower 50% of the range)
            if ($entryPrice < $midpoint) {
                return 'unfavorable';
            }
        }

        return 'neutral';
    }

    // =========================================================================
    // CORE VALIDATION METHODS
    // =========================================================================

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

    /**
     * Validate signal direction logic (Entry/SL/TP relationship)
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
     */
    protected function validateEntryVsCurrentPrice($signal)
    {
        $entry = $signal['entry_price'];
        $current = $signal['current_price'];
        $type = $signal['type'];
        
        // Set a wider tolerance for the entry price to be valid for a LIMIT order pullback.
        $maxDeviation = 0.10; 
        
        $distance = abs(($current - $entry) / $current);

        if ($distance > $maxDeviation) {
             Log::debug("[{$signal['symbol']}] Signal rejected: Entry ($entry) is too far from Current Price ($current). Deviation: " . round($distance * 100, 2) . "%");
             return false;
        }

        // Prevent a LONG signal where the Entry is far ABOVE the Current Price
        if ($type === 'long' && $entry > $current * 1.005 && $signal['order_type'] === 'Market') {
             Log::warning("[{$signal['symbol']}] LONG Market rejected: Entry ($entry) is above Current Price ($current).");
             return false;
        }
        
        // Prevent a SHORT signal where the Entry is far BELOW the Current Price
        if ($type === 'short' && $entry < $current * 0.995 && $signal['order_type'] === 'Market') {
             Log::warning("[{$signal['symbol']}] SHORT Market rejected: Entry ($entry) is below Current Price ($current).");
             return false;
        }
        
        return true;
    }

    /**
     * Determine order type based on distance from current price to entry
     */
    protected function determineOrderType($currentPrice, $entryPrice)
    {
        if ($currentPrice <= 0 || $entryPrice <= 0) {
            return 'Market'; 
        }

        $priceDistance = abs(($currentPrice - $entryPrice) / $currentPrice) * 100;

        // If within 0.5% of entry → Market order (immediate execution)
        $orderType = $priceDistance <= 0.5 ? 'Market' : 'Limit';
        
        return $orderType;
    }

    // =========================================================================
    // TECHNICAL INDICATOR UTILITIES
    // =========================================================================

    /**
     * Calculate Simple Moving Average (SMA)
     */
    protected function calculateSMA(array $closes, $period)
    {
        if (count($closes) < $period) {
            return 0;
        }
        $recentCloses = array_slice($closes, 0, $period);
        return array_sum($recentCloses) / $period;
    }

    /**
     * Calculate Average True Range for dynamic SL/TP
     */
    protected function calculateATR($candles, $period = 14)
    {
        if (count($candles) < $period + 1) {
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

    // =========================================================================
    // PATTERN DETECTION METHODS - FULLY IMPLEMENTED
    // =========================================================================

    /**
     * Detect Order Block Pattern
     * 
     * Order Block: A consolidation zone before a strong move that often acts as support/resistance
     */
    protected function detectOrderBlock($candles, $symbol, $currentPrice)
    {
        if (count($candles) < 50) {
            return null;
        }
        
        $atr = $this->calculateATR($candles, 14);
        
        // Look for Order Blocks in the last 20-30 candles
        $lookback = 30;
        $recentCandles = array_slice($candles, 0, $lookback);
        
        // === BULLISH ORDER BLOCK ===
        // Look for: Down move → Consolidation → Strong up move
        for ($i = 5; $i < count($recentCandles) - 5; $i++) {
            $current = $recentCandles[$i];
            
            // Check for strong bullish candle (body > 1.5 * ATR)
            $bodySize = abs($current['close'] - $current['open']);
            $isBullishCandle = $current['close'] > $current['open'];
            
            if (!$isBullishCandle || $bodySize < ($atr * 1.5)) {
                continue;
            }
            
            // Check if previous 3-5 candles were consolidating (small bodies)
            $consolidating = true;
            for ($j = $i + 1; $j <= $i + 3; $j++) {
                if ($j >= count($recentCandles)) break;
                $prevCandle = $recentCandles[$j];
                $prevBody = abs($prevCandle['close'] - $prevCandle['open']);
                
                if ($prevBody > $atr * 0.8) {
                    $consolidating = false;
                    break;
                }
            }
            
            if (!$consolidating) {
                continue;
            }
            
            // Order Block found - the last bearish candle before the bullish move
            $obCandle = $recentCandles[$i + 1];
            $obLow = $obCandle['low'];
            $obHigh = $obCandle['high'];
            $entryPrice = ($obLow + $obHigh) / 2;
            
            // Check if current price is near the order block (within 3% for pullback)
            $distancePercent = abs(($currentPrice - $entryPrice) / $entryPrice) * 100;
            
            if ($distancePercent <= 5) {
                // Price is near the order block - valid signal
                $stopLoss = $obLow - ($atr * 1.5);
                $riskDistance = $entryPrice - $stopLoss;
                $takeProfit = $entryPrice + ($riskDistance * 2.5);
                
                Log::info("[{$symbol}] Bullish Order Block detected", [
                    'entry' => $entryPrice,
                    'current_price' => $currentPrice,
                    'ob_low' => $obLow,
                    'ob_high' => $obHigh
                ]);
                
                return [
                    'symbol' => $symbol,
                    'type' => 'long',
                    'pattern' => 'Order Block (Bullish)',
                    'confidence' => 85,
                    'entry_price' => $entryPrice,
                    'stop_loss' => $stopLoss,
                    'take_profit' => $takeProfit,
                ];
            }
        }
        
        // === BEARISH ORDER BLOCK ===
        // Look for: Up move → Consolidation → Strong down move
        for ($i = 5; $i < count($recentCandles) - 5; $i++) {
            $current = $recentCandles[$i];
            
            // Check for strong bearish candle
            $bodySize = abs($current['close'] - $current['open']);
            $isBearishCandle = $current['close'] < $current['open'];
            
            if (!$isBearishCandle || $bodySize < ($atr * 1.5)) {
                continue;
            }
            
            // Check if previous 3-5 candles were consolidating
            $consolidating = true;
            for ($j = $i + 1; $j <= $i + 3; $j++) {
                if ($j >= count($recentCandles)) break;
                $prevCandle = $recentCandles[$j];
                $prevBody = abs($prevCandle['close'] - $prevCandle['open']);
                
                if ($prevBody > $atr * 0.8) {
                    $consolidating = false;
                    break;
                }
            }
            
            if (!$consolidating) {
                continue;
            }
            
            // Order Block found
            $obCandle = $recentCandles[$i + 1];
            $obLow = $obCandle['low'];
            $obHigh = $obCandle['high'];
            $entryPrice = ($obLow + $obHigh) / 2;
            
            // Check if current price is near the order block
            $distancePercent = abs(($currentPrice - $entryPrice) / $entryPrice) * 100;
            
            if ($distancePercent <= 5) {
                $stopLoss = $obHigh + ($atr * 1.5);
                $riskDistance = $stopLoss - $entryPrice;
                $takeProfit = $entryPrice - ($riskDistance * 2.5);
                
                Log::info("[{$symbol}] Bearish Order Block detected", [
                    'entry' => $entryPrice,
                    'current_price' => $currentPrice,
                    'ob_low' => $obLow,
                    'ob_high' => $obHigh
                ]);
                
                return [
                    'symbol' => $symbol,
                    'type' => 'short',
                    'pattern' => 'Order Block (Bearish)',
                    'confidence' => 85,
                    'entry_price' => $entryPrice,
                    'stop_loss' => $stopLoss,
                    'take_profit' => $takeProfit,
                ];
            }
        }

        return null;
    }

    /**
     * Detect Fair Value Gap (FVG) Pattern
     * 
     * FVG: A gap between two candles that price tends to fill
     */
    protected function detectFairValueGap($candles, $symbol, $currentPrice)
    {
        if (count($candles) < 20) {
            return null;
        }
        
        $atr = $this->calculateATR($candles, 14);
        
        // Look for FVGs in the last 15 candles
        $lookback = 15;
        $recentCandles = array_slice($candles, 0, $lookback);
        
        // === BULLISH FVG ===
        // Three candle pattern: candle[2].low > candle[0].high (gap up)
        for ($i = 0; $i < count($recentCandles) - 2; $i++) {
            $candle1 = $recentCandles[$i];
            $candle2 = $recentCandles[$i + 1];
            $candle3 = $recentCandles[$i + 2];
            
            // Check if there's a gap
            $gapExists = $candle3['high'] < $candle1['low'];
            
            if (!$gapExists) {
                continue;
            }
            
            // Calculate gap size
            $gapLow = $candle3['high'];
            $gapHigh = $candle1['low'];
            $gapSize = $gapHigh - $gapLow;
            
            // Gap must be significant (> 0.5 * ATR)
            if ($gapSize < ($atr * 0.5)) {
                continue;
            }
            
            // Check if price is near the gap (within 3%)
            $gapMid = ($gapLow + $gapHigh) / 2;
            $distancePercent = abs(($currentPrice - $gapMid) / $gapMid) * 100;
            
            if ($distancePercent <= 3) {
                // Price is near the gap - it may fill it
                $entryPrice = $gapMid;
                $stopLoss = $gapLow - ($atr * 1.0);
                $riskDistance = $entryPrice - $stopLoss;
                $takeProfit = $entryPrice + ($riskDistance * 2.5);
                
                Log::info("[{$symbol}] Bullish FVG detected", [
                    'entry' => $entryPrice,
                    'gap_low' => $gapLow,
                    'gap_high' => $gapHigh,
                    'gap_size' => $gapSize
                ]);
                
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
        
        // === BEARISH FVG ===
        // Three candle pattern: candle[2].high < candle[0].low (gap down)
        for ($i = 0; $i < count($recentCandles) - 2; $i++) {
            $candle1 = $recentCandles[$i];
            $candle2 = $recentCandles[$i + 1];
            $candle3 = $recentCandles[$i + 2];
            
            // Check if there's a gap
            $gapExists = $candle3['low'] > $candle1['high'];
            
            if (!$gapExists) {
                continue;
            }
            
            // Calculate gap size
            $gapLow = $candle1['high'];
            $gapHigh = $candle3['low'];
            $gapSize = $gapHigh - $gapLow;
            
            // Gap must be significant
            if ($gapSize < ($atr * 0.5)) {
                continue;
            }
            
            // Check if price is near the gap
            $gapMid = ($gapLow + $gapHigh) / 2;
            $distancePercent = abs(($currentPrice - $gapMid) / $gapMid) * 100;
            
            if ($distancePercent <= 3) {
                $entryPrice = $gapMid;
                $stopLoss = $gapHigh + ($atr * 1.0);
                $riskDistance = $stopLoss - $entryPrice;
                $takeProfit = $entryPrice - ($riskDistance * 2.5);
                
                Log::info("[{$symbol}] Bearish FVG detected", [
                    'entry' => $entryPrice,
                    'gap_low' => $gapLow,
                    'gap_high' => $gapHigh,
                    'gap_size' => $gapSize
                ]);
                
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

        return null;
    }

    /**
     * Detect Liquidity Sweep Pattern
     * 
     * Liquidity Sweep: Price breaks a recent high/low to trigger stops, then reverses
     */
    protected function detectLiquiditySweep($candles, $symbol, $currentPrice)
    {
        if (count($candles) < 30) {
            return null;
        }
        
        $atr = $this->calculateATR($candles, 14);
        
        // Look for liquidity sweeps in the last 20 candles
        $lookback = 20;
        $recentCandles = array_slice($candles, 0, $lookback);
        
        // === BULLISH LIQUIDITY SWEEP ===
        // Price breaks below recent low, then reverses strongly upward
        
        // Find the recent low (excluding the last 2 candles)
        $lows = array_column(array_slice($recentCandles, 2), 'low');
        $recentLow = min($lows);
        $recentLowIndex = array_search($recentLow, array_column($recentCandles, 'low'));
        
        if ($recentLowIndex === false) {
            $recentLowIndex = 0;
        }
        
        // Check if price recently broke below this low
        $sweepOccurred = false;
        for ($i = 0; $i < min(3, count($recentCandles)); $i++) {
            $candle = $recentCandles[$i];
            if ($candle['low'] < $recentLow) {
                $sweepOccurred = true;
                break;
            }
        }
        
        if ($sweepOccurred) {
            // Check if there's a strong bullish reversal
            $lastCandle = $recentCandles[0];
            $isBullishReversal = ($lastCandle['close'] > $lastCandle['open']) && 
                                 (abs($lastCandle['close'] - $lastCandle['open']) > $atr * 1.0);
            
            if ($isBullishReversal) {
                // Check if current price is above the swept low (confirming reversal)
                if ($currentPrice > $recentLow * 1.001) {
                    $entryPrice = $recentLow * 1.002; // Entry slightly above swept low
                    $stopLoss = $recentLow - ($atr * 1.0);
                    $riskDistance = $entryPrice - $stopLoss;
                    $takeProfit = $entryPrice + ($riskDistance * 2.5);
                    
                    Log::info("[{$symbol}] Bullish Liquidity Sweep detected", [
                        'entry' => $entryPrice,
                        'swept_low' => $recentLow,
                        'current_price' => $currentPrice
                    ]);
                    
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
        }
        
        // === BEARISH LIQUIDITY SWEEP ===
        // Price breaks above recent high, then reverses strongly downward
        
        // Find the recent high (excluding the last 2 candles)
        $highs = array_column(array_slice($recentCandles, 2), 'high');
        $recentHigh = max($highs);
        $recentHighIndex = array_search($recentHigh, array_column($recentCandles, 'high'));
        
        if ($recentHighIndex === false) {
            $recentHighIndex = 0;
        }
        
        // Check if price recently broke above this high
        $sweepOccurred = false;
        for ($i = 0; $i < min(3, count($recentCandles)); $i++) {
            $candle = $recentCandles[$i];
            if ($candle['high'] > $recentHigh) {
                $sweepOccurred = true;
                break;
            }
        }
        
        if ($sweepOccurred) {
            // Check if there's a strong bearish reversal
            $lastCandle = $recentCandles[0];
            $isBearishReversal = ($lastCandle['close'] < $lastCandle['open']) && 
                                 (abs($lastCandle['close'] - $lastCandle['open']) > $atr * 1.0);
            
            if ($isBearishReversal) {
                // Check if current price is below the swept high (confirming reversal)
                if ($currentPrice < $recentHigh * 0.999) {
                    $entryPrice = $recentHigh * 0.998; // Entry slightly below swept high
                    $stopLoss = $recentHigh + ($atr * 1.0);
                    $riskDistance = $stopLoss - $entryPrice;
                    $takeProfit = $entryPrice - ($riskDistance * 2.5);
                    
                    Log::info("[{$symbol}] Bearish Liquidity Sweep detected", [
                        'entry' => $entryPrice,
                        'swept_high' => $recentHigh,
                        'current_price' => $currentPrice
                    ]);
                    
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
        }

        return null;
    }

    /**
     * Create signal with order type and analysis data
     */
    public function createSignal($signalData, $primaryTimeframe)
    {
        $riskReward = abs(($signalData['take_profit'] - $signalData['entry_price']) / 
                         ($signalData['entry_price'] - $signalData['stop_loss']));
        
        $expiry_time = Setting::get('signal_expiry', 30);

        return Signal::create([
            'symbol' => $signalData['symbol'],
            'exchange' => 'bybit',
            'type' => $signalData['type'],
            'order_type' => $signalData['order_type'] ?? 'Market',
            'timeframe' => $primaryTimeframe,
            'pattern' => $signalData['pattern'],
            'confidence' => $signalData['confidence'],
            'current_price' => $signalData['current_price'],
            'entry_price' => $signalData['entry_price'],
            'stop_loss' => $signalData['stop_loss'],
            'take_profit' => $signalData['take_profit'],
            'risk_reward_ratio' => $riskReward,
            'position_size_percent' => Setting::get('signal_position_size', 5),
            'status' => 'pending', 
            'expires_at' => now()->addMinutes($expiry_time),
            
            // Save the detailed analysis data as JSON
            'analysis_data' => json_encode($signalData['analysis_data_package'] ?? []), 
        ]);
    }
}