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
    protected $maxStopLossPercent = 2.0; // Max SL as percentage

    public function __construct()
    {
        // Get Timeframes from Database, defaulting to common SMC settings
        $this->primaryTimeframe = Setting::get('signal_primary_timeframe', '15');
        $this->higherTimeframe = Setting::get('signal_higher_timeframe', '240');
        $this->maxStopLossPercent = (float) Setting::get('signal_max_sl', 2.0);
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
     * Calculate stop loss with percentage cap
     * This prevents SL from being too far from entry price
     */
    protected function calculateStopLossWithCap($entryPrice, $rawStopLoss, $direction = 'long')
    {
        // Calculate the percentage distance
        $slPercent = abs(($entryPrice - $rawStopLoss) / $entryPrice) * 100;
        
        // If SL is within acceptable range, use it as-is
        if ($slPercent <= $this->maxStopLossPercent) {
            return $rawStopLoss;
        }
        
        // Otherwise, cap it at max percentage
        $maxDistance = $entryPrice * ($this->maxStopLossPercent / 100);
        
        if ($direction === 'long') {
            $cappedStopLoss = $entryPrice - $maxDistance;
        } else {
            $cappedStopLoss = $entryPrice + $maxDistance;
        }
        
        Log::debug('[SignalGenerator] Stop loss capped', [
            'entry_price' => $entryPrice,
            'raw_sl' => $rawStopLoss,
            'raw_sl_percent' => round($slPercent, 2), //
            'capped_sl' => $cappedStopLoss,
            'capped_sl_percent' => $this->maxStopLossPercent,
            'direction' => $direction
        ]);
        
        return $cappedStopLoss;
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
            $maxPairs = (int) Setting::get('signal_max_pairs', 50); // NEW: Maximum pairs to analyze
            
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

        // OPTIMIZED: Batch fetch all data first, then analyze
        $signals = $this->analyzeSymbolsOptimized($symbols, $primaryTimeframe, $minConfidence);

        return $signals;
    }

    /**
     * OPTIMIZED: Fetch all data in batches, then analyze locally (10x faster)
     * The bottleneck is API calls - fetching data for 50 symbols sequentially takes forever
     * Solution: Fetch all data upfront in small batches, then analyze everything locally
     */
    protected function analyzeSymbolsOptimized($symbols, $primaryTimeframe, $minConfidence)
    {
        $startTime = microtime(true);
        
        Log::info('[SignalGenerator] Starting OPTIMIZED batch analysis', [
            'total_symbols' => count($symbols),
            'strategy' => 'batch_fetch_then_local_analysis'
        ]);

        try {
            $bybit = $this->getAdminBybitService();
            
            // STEP 1: Batch fetch ALL klines data (this is the slow part)
            $allData = [];
            $batchSize = 5; // Process 5 symbols at a time to avoid rate limits
            $symbolBatches = array_chunk($symbols, $batchSize);
            $fetchedCount = 0;
            
            foreach ($symbolBatches as $batchIndex => $batch) {
                $batchStart = microtime(true);
                
                foreach ($batch as $symbol) {
                    try {
                        $allData[$symbol] = [
                            'primary' => $bybit->getKlines($symbol, $primaryTimeframe, 200),
                            'htf' => $bybit->getKlines($symbol, $this->higherTimeframe, 100),
                            'currentPrice' => $bybit->getCurrentPrice($symbol)
                        ];
                        $fetchedCount++;
                    } catch (\Exception $e) {
                        Log::error("[SignalGenerator] Failed to fetch data for {$symbol}: " . $e->getMessage());
                        continue;
                    }
                }
                
                $batchDuration = round(microtime(true) - $batchStart, 2);
                Log::info("[SignalGenerator] Batch {$batchIndex}/" . count($symbolBatches) . " fetched", [
                    'symbols_fetched' => $fetchedCount,
                    'batch_duration' => $batchDuration . 's'
                ]);
                
                // Small delay to avoid hitting rate limits (adjust based on your API limits)
                if ($batchIndex < count($symbolBatches) - 1) {
                    usleep(50000); // 50ms delay between batches
                }
            }
            
            Log::info('[SignalGenerator] Data fetch completed', [
                'symbols_fetched' => count($allData),
                'fetch_duration' => round(microtime(true) - $startTime, 2) . 's'
            ]);
            
            // STEP 2: Analyze all symbols locally (FAST - no API calls)
            $analysisStart = microtime(true);
            $signals = [];
            $analyzed = 0;
            
            foreach ($allData as $symbol => $data) {
                if (empty($data['primary']) || empty($data['htf'])) {
                    continue;
                }
                
                try {
                    $signal = $this->analyzeSymbolWithData(
                        $symbol, 
                        $data['primary'], 
                        $data['htf'],
                        $data['currentPrice'],
                        $primaryTimeframe,
                        $minConfidence
                    );
                    
                    if ($signal) {
                        $signals[] = $signal;
                    }
                    
                    $analyzed++;
                    
                    // Progress logging every 10 symbols
                    if ($analyzed % 10 === 0) {
                        Log::debug("[SignalGenerator] Analyzed {$analyzed}/" . count($allData));
                    }
                    
                } catch (\Exception $e) {
                    Log::error("[SignalGenerator] Analysis failed for {$symbol}: " . $e->getMessage());
                }
            }
            
            $totalDuration = round(microtime(true) - $startTime, 2);
            $analysisDuration = round(microtime(true) - $analysisStart, 2);
            
            Log::info('[SignalGenerator] Optimized batch analysis completed', [
                'total_signals_found' => count($signals),
                'symbols_analyzed' => $analyzed,
                'total_duration' => $totalDuration . 's',
                'fetch_time' => round($totalDuration - $analysisDuration, 2) . 's',
                'analysis_time' => $analysisDuration . 's',
                'avg_per_symbol' => round($totalDuration / count($symbols), 3) . 's'
            ]);
            
            return $signals;
            
        } catch (\Exception $e) {
            Log::error('[SignalGenerator] Optimized batch analysis failed: ' . $e->getMessage());
            
            // Fallback to old sequential method if batch fails
            Log::warning('[SignalGenerator] Falling back to sequential processing');
            return $this->analyzeSymbolsSequential($symbols, $primaryTimeframe, $minConfidence);
        }
    }

    /**
     * Fallback: Sequential processing (original slow method)
     */
    protected function analyzeSymbolsSequential($symbols, $primaryTimeframe, $minConfidence)
    {
        $signals = [];
        
        Log::info('[SignalGenerator] Using sequential processing (SLOW)', [
            'total_symbols' => count($symbols)
        ]);
        
        foreach ($symbols as $index => $symbol) {
            if ($index > 0 && $index % 10 === 0) {
                Log::info('[SignalGenerator] Sequential progress: ' . $index . '/' . count($symbols));
            }
            
            try {
                $signal = $this->analyzeSymbol($symbol, $primaryTimeframe, $this->higherTimeframe, $minConfidence);
                
                if ($signal) {
                    $signals[] = $signal;
                }
            } catch (\Exception $e) {
                Log::error("[SignalGenerator] Failed to analyze {$symbol}: " . $e->getMessage());
            }
        }
        
        return $signals;
    }

    /**
     * Analyze symbol with pre-fetched data (no API calls - super fast)
     */
    protected function analyzeSymbolWithData($symbol, $primaryKlines, $htfKlines, $currentPrice, $primaryTimeframe, $minConfidence)
    {
        $primaryCandles = $this->formatKlines($primaryKlines);
        $htfCandles = $this->formatKlines($htfKlines);
        
        if ($currentPrice === null || $currentPrice <= 0) {
            $currentPrice = $primaryCandles[0]['close'];
        }

        // --- STEP 1: CONTEXT ANALYSIS ---
        $structureBias = $this->detectMarketStructure($primaryCandles, 100);
        $htfBias = $this->detectHTFTrend($htfCandles);

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
            
            $signal['initial_confidence'] = $signal['confidence'];
            $confidence = $signal['initial_confidence'];
            $type = $signal['type']; 
            
            $scoreStructure = 0;
            $scoreHTF = 0;
            $scoreDiscountPremium = 0;

            if (!$this->validateSignalDirection($signal)) {
                continue;
            }

            if (!$this->validateEntryVsCurrentPrice($signal)) {
                continue;
            }

            if (($type === 'long' && $structureBias === 'bullish') || 
                ($type === 'short' && $structureBias === 'bearish')) {
                $scoreStructure = 10;
            } elseif ($structureBias === 'ranging') {
                $scoreStructure = -5;
            } else {
                $scoreStructure = -15;
            }
            $confidence += $scoreStructure;
            
            if ($type === $htfBias) {
                $scoreHTF = 15;
            } else {
                $scoreHTF = -10;
            }
            $confidence += $scoreHTF;

            $discountPremiumStatus = $this->checkDiscountPremium($htfCandles, $type, $signal['entry_price']);
            if ($discountPremiumStatus === 'favorable') {
                $scoreDiscountPremium = 10;
            } elseif ($discountPremiumStatus === 'unfavorable') {
                 $scoreDiscountPremium = -10;
            }
            $confidence += $scoreDiscountPremium;

            $signal['confidence'] = max(0, min(100, $confidence));
            
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

            if ($signal['confidence'] >= $minConfidence) {
                return $signal;
            }
        }

        return null;
    }

    /**
     * Analyze a single symbol for SMC trading signals (with API calls - slow)
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

        // 2. Get REAL-TIME current price
        try {
            $currentPrice = $bybit->getCurrentPrice($symbol);
            if ($currentPrice === null || $currentPrice <= 0) {
                $currentPrice = $this->formatKlines($primaryKlines)[0]['close'];
            }
        } catch (\Exception $e) {
            Log::warning("[SignalGenerator] Failed to get current price for {$symbol}, using last close");
            $currentPrice = $this->formatKlines($primaryKlines)[0]['close'];
        }

        return $this->analyzeSymbolWithData($symbol, $primaryKlines, $htfKlines, $currentPrice, $primaryTimeframe, $minConfidence);
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
                $rawStopLoss = $obLow - ($atr * 1.5);
                $stopLoss = $this->calculateStopLossWithCap($entryPrice, $rawStopLoss, 'long');
                $riskDistance = $entryPrice - $stopLoss;
                $takeProfit = $entryPrice + ($riskDistance * 2.5);
                
                Log::info("[{$symbol}] Bullish Order Block detected", [
                    'entry' => $entryPrice,
                    'current_price' => $currentPrice,
                    'ob_low' => $obLow,
                    'ob_high' => $obHigh,
                    'sl' => $stopLoss,
                    'sl_percent' => round(abs(($entryPrice - $stopLoss) / $entryPrice) * 100, 2)
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
                $rawStopLoss = $obHigh + ($atr * 1.5);
                $stopLoss = $this->calculateStopLossWithCap($entryPrice, $rawStopLoss, 'short');
                $riskDistance = $stopLoss - $entryPrice;
                $takeProfit = $entryPrice - ($riskDistance * 2.5);
                
                Log::info("[{$symbol}] Bearish Order Block detected", [
                    'entry' => $entryPrice,
                    'current_price' => $currentPrice,
                    'ob_low' => $obLow,
                    'ob_high' => $obHigh,
                    'sl' => $stopLoss,
                    'sl_percent' => round(abs(($entryPrice - $stopLoss) / $entryPrice) * 100, 2)
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
                $rawStopLoss = $gapLow - ($atr * 1.0);
                $stopLoss = $this->calculateStopLossWithCap($entryPrice, $rawStopLoss, 'long');
                $riskDistance = $entryPrice - $stopLoss;
                $takeProfit = $entryPrice + ($riskDistance * 2.5);
                
                Log::info("[{$symbol}] Bullish FVG detected", [
                    'entry' => $entryPrice,
                    'gap_low' => $gapLow,
                    'gap_high' => $gapHigh,
                    'gap_size' => $gapSize,
                    'sl' => $stopLoss,
                    'sl_percent' => round(abs(($entryPrice - $stopLoss) / $entryPrice) * 100, 2)
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
                $rawStopLoss = $gapHigh + ($atr * 1.0);
                $stopLoss = $this->calculateStopLossWithCap($entryPrice, $rawStopLoss, 'short');
                $riskDistance = $stopLoss - $entryPrice;
                $takeProfit = $entryPrice - ($riskDistance * 2.5);
                
                Log::info("[{$symbol}] Bearish FVG detected", [
                    'entry' => $entryPrice,
                    'gap_low' => $gapLow,
                    'gap_high' => $gapHigh,
                    'gap_size' => $gapSize,
                    'sl' => $stopLoss,
                    'sl_percent' => round(abs(($entryPrice - $stopLoss) / $entryPrice) * 100, 2)
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
                    $rawStopLoss = $recentLow - ($atr * 1.0);
                    $stopLoss = $this->calculateStopLossWithCap($entryPrice, $rawStopLoss, 'long');
                    $riskDistance = $entryPrice - $stopLoss;
                    $takeProfit = $entryPrice + ($riskDistance * 2.5);
                    
                    Log::info("[{$symbol}] Bullish Liquidity Sweep detected", [
                        'entry' => $entryPrice,
                        'swept_low' => $recentLow,
                        'current_price' => $currentPrice,
                        'sl' => $stopLoss,
                        'sl_percent' => round(abs(($entryPrice - $stopLoss) / $entryPrice) * 100, 2)
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
                    $rawStopLoss = $recentHigh + ($atr * 1.0);
                    $stopLoss = $this->calculateStopLossWithCap($entryPrice, $rawStopLoss, 'short');
                    $riskDistance = $stopLoss - $entryPrice;
                    $takeProfit = $entryPrice - ($riskDistance * 2.5);
                    
                    Log::info("[{$symbol}] Bearish Liquidity Sweep detected", [
                        'entry' => $entryPrice,
                        'swept_high' => $recentHigh,
                        'current_price' => $currentPrice,
                        'sl' => $stopLoss,
                        'sl_percent' => round(abs(($entryPrice - $stopLoss) / $entryPrice) * 100, 2)
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