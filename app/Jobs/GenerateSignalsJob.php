<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\Signal;
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
        //
    }

    public function handle(
        SignalGeneratorService $signalGenerator,
        TradePropagationService $tradePropagation
    ): void
    {
        try {
            // ========================================
            // Load ALL Signal Generator Settings
            // ========================================
            
            // Schedule Configuration
            $symbols = Setting::get('signal_pairs', ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT', 'XRPUSDT']);
            $topSignalsCount = (int) Setting::get('signal_top_count', 5);
            $minConfidence = (int) Setting::get('signal_min_confidence', 70);
            $signalExpiry = (int) Setting::get('signal_expiry', 30);
            
            // SMC Analysis Parameters
            $primaryTimeframe = Setting::get('signal_primary_timeframe', '15m');
            $higherTimeframe = Setting::get('signal_higher_timeframe') 
                ?? Setting::get('signal_secondary_timeframe', '1h');
            $enabledPatterns = Setting::get('signal_patterns', [
                'order_block', 'fvg', 'bos', 'choch', 'liquidity_sweep', 'premium_discount'
            ]);
            $lookbackPeriods = (int) Setting::get('signal_lookback_periods', 200);
            $patternStrength = (int) Setting::get('signal_pattern_strength', 3);
            
            // Risk Management
            $riskRewardRatio = Setting::get('signal_risk_reward', '1:2');
            $maxStopLossPercent = (float) Setting::get('signal_max_sl', 2.0);
            $defaultPositionSize = (float) Setting::get('signal_position_size', 5.0);
            $defaultLeverage = Setting::get('signal_leverage', 'Max');
            
            // Exchange Configuration
            $enabledExchanges = Setting::get('signal_exchanges', ['bybit']);
            
            // Advanced Options
            $autoExecute = (bool) Setting::get('signal_auto_execute', true);
            $notifyUsers = (bool) Setting::get('signal_notify_users', true);
            $logAnalysis = (bool) Setting::get('signal_log_analysis', true);
            $testMode = (bool) Setting::get('signal_test_mode', false);
            
            // ========================================
            // Log Configuration (if enabled)
            // ========================================
            
            if ($logAnalysis) {
                Log::info('Starting automated signal generation with configuration', [
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
            
            // Pass configuration to signal generator
            $signalGenerator->configure([
                'primary_timeframe' => $primaryTimeframe,
                'higher_timeframe' => $higherTimeframe,
                'enabled_patterns' => $enabledPatterns,
                'lookback_periods' => $lookbackPeriods,
                'pattern_strength' => $patternStrength,
                'log_analysis' => $logAnalysis,
            ]);
            
            // Generate signals with timeframe (remove 'm' suffix)
            $timeframeValue = str_replace('m', '', $primaryTimeframe);
            $signals = $signalGenerator->generateSignals($symbols, $timeframeValue, $minConfidence);

            if (empty($signals)) {
                Log::info('No signals generated - market conditions not met');
                return;
            }

            Log::info('Generated ' . count($signals) . ' signals');

            // ========================================
            // Filter Signals by Risk/Reward & Stop Loss
            // ========================================
            
            $filteredSignals = collect($signals)->filter(function ($signal) use ($riskRewardRatio, $maxStopLossPercent, $logAnalysis) {
                // Parse risk reward ratio (e.g., "1:2" => 2.0)
                $minRR = floatval(explode(':', $riskRewardRatio)[1] ?? 2);
                
                // Check risk/reward ratio
                if ($signal->risk_reward_ratio < $minRR) {
                    if ($logAnalysis) {
                        Log::debug("Signal {$signal->id} rejected: R:R {$signal->risk_reward_ratio} < {$minRR}");
                    }
                    return false;
                }
                
                // Check stop loss percentage
                $slPercent = abs(($signal->entry_price - $signal->stop_loss) / $signal->entry_price) * 100;
                if ($slPercent > $maxStopLossPercent) {
                    if ($logAnalysis) {
                        Log::debug("Signal {$signal->id} rejected: SL {$slPercent}% > {$maxStopLossPercent}%");
                    }
                    return false;
                }
                
                return true;
            });

            if ($filteredSignals->isEmpty()) {
                Log::info('All signals filtered out due to risk management rules');
                return;
            }

            // ========================================
            // Update Signal Parameters
            // ========================================
            
            foreach ($filteredSignals as $signal) {
                $signal->update([
                    'position_size_percent' => $defaultPositionSize,
                    'expires_at' => now()->addMinutes($signalExpiry),
                ]);
            }

            // ========================================
            // Sort and Select Top Signals
            // ========================================
            
            $topSignals = $filteredSignals
                ->sortByDesc('confidence')
                ->take($topSignalsCount);

            Log::info("Selected top {$topSignalsCount} signals for execution", [
                'total_generated' => count($signals),
                'after_filtering' => $filteredSignals->count(),
                'top_selected' => $topSignals->count(),
            ]);

            // ========================================
            // Execute or Queue Signals
            // ========================================
            
            if ($testMode) {
                Log::info('TEST MODE: Signals generated but not executed', [
                    'signals' => $topSignals->pluck('id')->toArray(),
                ]);
                
                // Mark as pending in test mode
                $topSignals->each(function ($signal) {
                    $signal->update(['status' => 'pending']);
                });
                
                return;
            }

            if ($autoExecute) {
                foreach ($topSignals as $signal) {
                    try {
                        // Execute admin trade FIRST, then propagate to users
                        $results = $this->executeSignalWithAdmin($signal, $tradePropagation);
                        
                        Log::info("Signal auto-executed", [
                            'signal_id' => $signal->id,
                            'symbol' => $signal->symbol,
                            'type' => $signal->type,
                            'confidence' => $signal->confidence,
                            'admin_executed' => isset($results['admin_trade']),
                            'successful' => $results['successful'],
                            'failed' => $results['failed'],
                        ]);

                        $signal->update([
                            'status' => 'executed',
                            'executed_at' => now(),
                        ]);
                        
                        // Send notifications if enabled
                        if ($notifyUsers) {
                            // TODO: Implement user notification system
                            // \Notification::send($users, new SignalExecutedNotification($signal));
                        }
                        
                    } catch (\Exception $e) {
                        Log::error("Failed to auto-execute signal {$signal->id}: " . $e->getMessage());
                        
                        $signal->update(['status' => 'failed']);
                    }
                }
            } else {
                Log::info('Auto-execute disabled. Signals marked as active for manual execution.');
                
                $topSignals->each(function ($signal) {
                    $signal->update(['status' => 'active']);
                });
            }

            Log::info('Signal generation job completed successfully');

        } catch (\Exception $e) {
            Log::error('Signal generation job failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Execute signal for admin first, then propagate to all users
     */
    protected function executeSignalWithAdmin($signal, $tradePropagation)
    {
        $positionSize = Setting::get('signal_position_size', 5);
        
        // Get admin account
        $adminAccount = \App\Models\AdminExchangeAccount::getBybitAccount();
        
        if (!$adminAccount) {
            Log::warning('No admin Bybit account configured. Executing only for users.');
            return $tradePropagation->propagateSignalToAllUsers($signal);
        }

        // Get admin user
        $admin = \App\Models\User::where('is_admin', true)->first();
        
        if (!$admin) {
            Log::warning('No admin user found. Executing only for users.');
            return $tradePropagation->propagateSignalToAllUsers($signal);
        }

        // Execute admin trade via TradeExecutionService
        $bybit = new \App\Services\BybitService($adminAccount->api_key, $adminAccount->api_secret);
        
        try {
            // Get admin balance
            $balance = $bybit->getBalance();
            
            if ($balance <= 0) {
                Log::warning('Insufficient admin balance. Executing only for users.');
                return $tradePropagation->propagateSignalToAllUsers($signal);
            }

            // Calculate quantity
            $riskAmount = ($balance * $positionSize) / 100;
            $stopLossDistance = abs($signal->entry_price - $signal->stop_loss);
            $quantity = $riskAmount / $stopLossDistance;
            $quantity = round($quantity, 3);

            // Create admin trade
            $trade = \App\Models\Trade::create([
                'user_id' => $admin->id,
                'signal_id' => $signal->id,
                'exchange_account_id' => null,
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

            // Execute on Bybit
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
                throw new \Exception('Failed to place admin order on Bybit');
            }

            // Update trade
            $trade->update([
                'exchange_order_id' => $orderResult['orderId'],
                'status' => 'open',
                'opened_at' => now(),
            ]);

            // Create position
            \App\Models\Position::create([
                'user_id' => $admin->id,
                'trade_id' => $trade->id,
                'exchange_account_id' => null,
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

            Log::info("Admin trade executed for auto-generated signal", [
                'trade_id' => $trade->id,
                'order_id' => $orderResult['orderId'],
            ]);

            // Now propagate to users
            $userResults = $tradePropagation->propagateSignalToAllUsers($signal);

            return [
                'admin_trade' => $trade,
                'total' => $userResults['total'],
                'successful' => $userResults['successful'],
                'failed' => $userResults['failed'],
                'errors' => $userResults['errors'],
            ];

        } catch (\Exception $e) {
            Log::error("Failed to execute admin trade for signal {$signal->id}: " . $e->getMessage());
            
            // Still try to execute for users even if admin trade fails
            return $tradePropagation->propagateSignalToAllUsers($signal);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Signal generation job failed permanently after ' . $this->tries . ' attempts', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // TODO: Send critical alert to admin
        // \Notification::route('mail', Setting::get('notifications_admin_email'))
        //     ->notify(new SignalGenerationFailedNotification($exception));
    }
}