<?php

namespace App\Jobs;

use App\Models\ExchangeAccount;
use App\Models\Position;
use App\Models\Setting;
use App\Models\Signal;
use App\Models\Trade;
use App\Models\User;
use App\Services\BybitService;
use App\Services\SignalGeneratorService;
use App\Services\TradePropagationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSignalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    public function __construct()
    {
        Log::info('[GenerateSignalsJob] Job instance created');
    }

    public function handle(
        SignalGeneratorService $signalGenerator,
        TradePropagationService $tradePropagation
    ): void
    {
        Log::info('[GenerateSignalsJob] Starting execution');
        
        try {
            // ========================================
            // Load ALL Signal Generator Settings
            // ========================================
            
            Log::info('[GenerateSignalsJob] Loading settings from database');
            
            // Schedule Configuration
            $symbols = Setting::get('signal_pairs', ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT', 'XRPUSDT']);
            $topSignalsCount = (int) Setting::get('signal_top_count', 5);
            $minConfidence = (int) Setting::get('signal_min_confidence', 70);
            $signalExpiry = (int) Setting::get('signal_expiry', 30);
            
            Log::info('[GenerateSignalsJob] Schedule configuration loaded', [
                'symbols_count' => count($symbols),
                'top_signals_count' => $topSignalsCount,
                'min_confidence' => $minConfidence,
                'signal_expiry_minutes' => $signalExpiry
            ]);
            
            // SMC Analysis Parameters
            $primaryTimeframe = Setting::get('signal_primary_timeframe', '15m');
            $higherTimeframe = Setting::get('signal_higher_timeframe') 
                ?? Setting::get('signal_secondary_timeframe', '1h');
            $enabledPatterns = Setting::get('signal_patterns', [
                'order_block', 'fvg', 'bos', 'choch', 'liquidity_sweep', 'premium_discount'
            ]);
            $lookbackPeriods = (int) Setting::get('signal_lookback_periods', 200);
            $patternStrength = (int) Setting::get('signal_pattern_strength', 3);
            
            Log::info('[GenerateSignalsJob] SMC parameters loaded', [
                'primary_timeframe' => $primaryTimeframe,
                'higher_timeframe' => $higherTimeframe,
                'enabled_patterns_count' => count($enabledPatterns),
                'lookback_periods' => $lookbackPeriods,
                'pattern_strength' => $patternStrength
            ]);
            
            // Risk Management
            $riskRewardRatio = Setting::get('signal_risk_reward', '1:2');
            $maxStopLossPercent = (float) Setting::get('signal_max_sl', 2.0);
            $defaultPositionSize = (float) Setting::get('signal_position_size', 5.0);
            $defaultLeverage = Setting::get('signal_leverage', 'Max');
            
            Log::info('[GenerateSignalsJob] Risk management settings loaded', [
                'risk_reward_ratio' => $riskRewardRatio,
                'max_stop_loss_percent' => $maxStopLossPercent,
                'default_position_size' => $defaultPositionSize,
                'default_leverage' => $defaultLeverage
            ]);
            
            // Order Type
            $orderType = Setting::get('signal_order_type', 'Market');
            
            // Exchange Configuration
            $enabledExchanges = Setting::get('signal_exchanges', ['bybit']);
            
            // Advanced Options
            $autoExecute = (bool) Setting::get('signal_auto_execute', true);
            $notifyUsers = (bool) Setting::get('signal_notify_users', true);
            $logAnalysis = (bool) Setting::get('signal_log_analysis', true);
            $testMode = (bool) Setting::get('signal_test_mode', false);
            
            Log::info('[GenerateSignalsJob] Advanced options loaded', [
                'order_type' => $orderType,
                'enabled_exchanges' => $enabledExchanges,
                'auto_execute' => $autoExecute,
                'notify_users' => $notifyUsers,
                'log_analysis' => $logAnalysis,
                'test_mode' => $testMode
            ]);
            
            // ========================================
            // Log Configuration (if enabled)
            // ========================================
            
            if ($logAnalysis) {
                Log::info('[GenerateSignalsJob] Full configuration summary', [
                    'symbols' => $symbols,
                    'primary_timeframe' => $primaryTimeframe,
                    'higher_timeframe' => $higherTimeframe,
                    'patterns_enabled' => $enabledPatterns,
                    'lookback_periods' => $lookbackPeriods,
                    'min_confidence' => $minConfidence,
                    'test_mode' => $testMode,
                ]);
            }
            
            // ========================================
            // Generate Signals
            // ========================================
            
            Log::info('[GenerateSignalsJob] Calling SignalGeneratorService->generateSignals()');
            
            // Generate signals with timeframe (remove 'm' suffix)
            $timeframeValue = $primaryTimeframe;
            $signals = $signalGenerator->generateSignals($symbols, $timeframeValue, $minConfidence);

            Log::info('[GenerateSignalsJob] Signal generation completed', [
                'signals_generated' => count($signals)
            ]);

            if (empty($signals)) {
                Log::info('[GenerateSignalsJob] No signals generated - market conditions not met');
                return;
            }

            Log::info("[GenerateSignalsJob] Generated " . count($signals) . " signals");

            // ========================================
            // Filter Signals by Risk/Reward & Stop Loss
            // ========================================
            
            Log::info('[GenerateSignalsJob] Starting signal filtering process');
            
            $filteredSignals = collect($signals)->filter(function ($signal) use ($riskRewardRatio, $maxStopLossPercent, $logAnalysis) {
                // Parse risk reward ratio (e.g., "1:2" => 2.0)
                $minRR = floatval(explode(':', $riskRewardRatio)[1] ?? 2);
                
                // Check risk/reward ratio
                if ($signal->risk_reward_ratio < $minRR) {
                    if ($logAnalysis) {
                        Log::debug("[GenerateSignalsJob] Signal {$signal->id} rejected: R:R {$signal->risk_reward_ratio} < {$minRR}");
                    }
                    return false;
                }
                
                // Check stop loss percentage
                $slPercent = abs(($signal->entry_price - $signal->stop_loss) / $signal->entry_price) * 100;
                if ($slPercent > $maxStopLossPercent) {
                    if ($logAnalysis) {
                        Log::debug("[GenerateSignalsJob] Signal {$signal->id} rejected: SL {$slPercent}% > {$maxStopLossPercent}%");
                    }
                    return false;
                }
                
                return true;
            });

            Log::info('[GenerateSignalsJob] Filtering completed', [
                'signals_before' => count($signals),
                'signals_after' => $filteredSignals->count()
            ]);

            if ($filteredSignals->isEmpty()) {
                Log::info('[GenerateSignalsJob] All signals filtered out due to risk management rules');
                return;
            }

            // ========================================
            // Update Signal Parameters
            // ========================================
            
            Log::info('[GenerateSignalsJob] Updating signal parameters');
            
            foreach ($filteredSignals as $signal) {
                $signal->update([
                    'position_size_percent' => $defaultPositionSize,
                    'expires_at' => now()->addMinutes($signalExpiry),
                ]);
            }

            Log::info('[GenerateSignalsJob] Signal parameters updated');

            // ========================================
            // Sort and Select Top Signals
            // ========================================
            
            Log::info('[GenerateSignalsJob] Selecting top signals');
            
            $topSignals = $filteredSignals
                ->sortByDesc('confidence')
                ->take($topSignalsCount);

            Log::info("[GenerateSignalsJob] Selected top {$topSignalsCount} signals for execution", [
                'total_generated' => count($signals),
                'after_filtering' => $filteredSignals->count(),
                'top_selected' => $topSignals->count(),
            ]);

            // ========================================
            // Execute or Queue Signals
            // ========================================
            
            if ($testMode) {
                Log::info('[GenerateSignalsJob] TEST MODE: Signals generated but not executed', [
                    'signals' => $topSignals->pluck('id')->toArray(),
                ]);
                
                // Mark as pending in test mode
                $topSignals->each(function ($signal) {
                    $signal->update(['status' => 'pending']);
                });
                
                Log::info('[GenerateSignalsJob] Test mode execution completed');
                return;
            }

            if ($autoExecute) {
                Log::info('[GenerateSignalsJob] Auto-execute enabled, processing signals');
                
                foreach ($topSignals as $signal) {
                    try {
                        Log::info("[GenerateSignalsJob] Executing signal {$signal->id}");
                        
                        // Execute admin trade FIRST, then propagate to users
                        $results = $this->executeSignalWithAdmin($signal, $tradePropagation, $orderType);
                        
                        Log::info("[GenerateSignalsJob] Signal auto-executed", [
                            'signal_id' => $signal->id,
                            'symbol' => $signal->symbol,
                            'type' => $signal->type,
                            'order_type' => $orderType,
                            'confidence' => $signal->confidence,
                            'admin_executed' => isset($results['admin_trade']),
                            'successful' => $results['successful'],
                            'failed' => $results['failed'],
                        ]);

                        $signal->update([
                            'status' => 'executed',
                            'executed_at' => now(),
                        ]);
                        
                        Log::info("[GenerateSignalsJob] Signal {$signal->id} marked as executed");
                        
                        // Send notifications if enabled
                        if ($notifyUsers) {
                            Log::info("[GenerateSignalsJob] Notifications enabled for signal {$signal->id}");
                            // TODO: Implement user notification system
                            // \Notification::send($users, new SignalExecutedNotification($signal));
                        }
                        
                    } catch (\Exception $e) {
                        Log::error("[GenerateSignalsJob] Failed to auto-execute signal {$signal->id}", [
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        
                        $signal->update(['status' => 'failed']);
                    }
                }
            } else {
                Log::info('[GenerateSignalsJob] Auto-execute disabled. Signals marked as active for manual execution.');
                
                $topSignals->each(function ($signal) {
                    $signal->update(['status' => 'active']);
                });
            }

            Log::info('[GenerateSignalsJob] Signal generation job completed successfully');

        } catch (\Exception $e) {
            Log::error('[GenerateSignalsJob] Critical exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Execute signal for admin first, then propagate to all users
     */
    protected function executeSignalWithAdmin($signal, $tradePropagation, $orderType = 'Market')
    {
        Log::info("[GenerateSignalsJob] executeSignalWithAdmin() called for signal {$signal->id}");
        
        $positionSize = Setting::get('signal_position_size', 5);
        
        Log::info("[GenerateSignalsJob] Position size loaded: {$positionSize}%");
        
        // Execute admin trade FIRST using TradePropagationService
        Log::info("[GenerateSignalsJob] Executing admin trade for signal {$signal->id}");
        $adminResult = $tradePropagation->executeAdminTrade($signal, $positionSize, $orderType);
        
        if (!$adminResult['success']) {
            Log::warning("[GenerateSignalsJob] Admin trade failed, not propagating to users", [
                'signal_id' => $signal->id,
                'error' => $adminResult['error']
            ]);
            
            return [
                'admin_trade' => null,
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => [],
            ];
        }

        Log::info("[GenerateSignalsJob] Admin trade successful, propagating to users");

        // Now propagate to users with same order type
        $userResults = $tradePropagation->propagateSignalToAllUsers($signal, $orderType);

        Log::info("[GenerateSignalsJob] User propagation completed", [
            'total' => $userResults['total'],
            'successful' => $userResults['successful'],
            'failed' => $userResults['failed']
        ]);

        return [
            'admin_trade' => $adminResult['trade'],
            'total' => $userResults['total'],
            'successful' => $userResults['successful'],
            'failed' => $userResults['failed'],
            'errors' => $userResults['errors'],
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[GenerateSignalsJob] Job failed permanently after ' . $this->tries . ' attempts', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // TODO: Send critical alert to admin
        // \Notification::route('mail', Setting::get('notifications_admin_email'))
        //     ->notify(new SignalGenerationFailedNotification($exception));
    }
}