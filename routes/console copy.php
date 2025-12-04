<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Jobs\MonitorPositionsJob;
use App\Jobs\GenerateSignalsJob;
use App\Jobs\ExpireSignalsJob;
use App\Jobs\SyncUserBalancesJob;
use App\Jobs\MonitorLimitOrdersJob;
use App\Models\Setting;

/**
 * CryptoBot Pro - Console Routes & Scheduled Tasks
 * 
 * Laravel 11 uses routes/console.php instead of app/Console/Kernel.php
 * All scheduled tasks and artisan commands are defined here.
 */

// ==========================================
// ARTISAN COMMANDS
// ==========================================

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// LOAD SETTINGS WITH FALLBACKS
// ==========================================

Log::info('[Scheduler] Loading settings from database');

// Set default values first
$signalInterval = 15;
$tradingEnabled = true;
$monitorInterval = 1;
$backupEnabled = false;
$backupFrequency = 'daily';
$dataRetentionDays = 365;
$logRetentionDays = 90;
$dailySummaryEnabled = true;
$circuitBreakerEnabled = true;

try {
    $signalInterval = (int) Setting::get('signal_interval', 15);
    $tradingEnabled = (bool) Setting::get('trading_enabled', true);
    $monitorInterval = (int) Setting::get('monitor_interval', 1);
    $backupEnabled = (bool) Setting::get('backup_auto_backup', false);
    $backupFrequency = Setting::get('backup_frequency', 'daily');
    $dataRetentionDays = (int) Setting::get('backup_data_retention', 365);
    $logRetentionDays = (int) Setting::get('backup_log_retention', 90);
    $dailySummaryEnabled = (bool) Setting::get('notifications_daily_summary', true);
    $circuitBreakerEnabled = (bool) Setting::get('monitor_circuit_breaker', true);
    
    Log::info('[Scheduler] Settings loaded successfully', [
        'signal_interval' => $signalInterval,
        'trading_enabled' => $tradingEnabled,
        'monitor_interval' => $monitorInterval,
        'backup_enabled' => $backupEnabled,
        'circuit_breaker_enabled' => $circuitBreakerEnabled
    ]);
} catch (\Exception $e) {
    Log::warning('[Scheduler] Failed to load settings - using defaults', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

// ==========================================
// CORE TRADING JOBS (Critical - Always Run)
// ==========================================

/**
 * Monitor Limit Orders - Every 2 Minutes
 * Checks pending limit orders and converts them to active positions when filled
 */
Log::info('[Scheduler] Scheduling MonitorLimitOrdersJob (every 2 minutes)');
Schedule::job(new MonitorLimitOrdersJob)
    ->everyTwoMinutes()
    ->name('monitor-limit-orders')
    ->withoutOverlapping(2)
    ->onOneServer()
    ->before(function() {
        Log::info('[Scheduler] MonitorLimitOrdersJob about to run');
    })
    ->after(function() {
        Log::info('[Scheduler] MonitorLimitOrdersJob completed');
    });

/**
 * Monitor Active Positions - Dynamic Interval (1-60 minutes)
 * Tracks open positions, updates P&L, and closes positions at TP/SL
 */
if ($tradingEnabled) {
    Log::info("[Scheduler] Trading enabled - scheduling MonitorPositionsJob (interval: {$monitorInterval} min)");
    
    if ($monitorInterval <= 1) {
        Schedule::job(new MonitorPositionsJob)
            ->everyMinute()
            ->name('monitor-positions')
            ->withoutOverlapping(5)
            ->onOneServer()
            ->before(function() {
                Log::info('[Scheduler] MonitorPositionsJob about to run');
            })
            ->after(function() {
                Log::info('[Scheduler] MonitorPositionsJob completed');
            });
    } else {
        Schedule::job(new MonitorPositionsJob)
            ->cron("*/{$monitorInterval} * * * *")
            ->name('monitor-positions')
            ->withoutOverlapping(5)
            ->onOneServer()
            ->before(function() {
                Log::info('[Scheduler] MonitorPositionsJob about to run');
            })
            ->after(function() {
                Log::info('[Scheduler] MonitorPositionsJob completed');
            });
    }
} else {
    Log::info('[Scheduler] Trading disabled - skipping MonitorPositionsJob');
}

/**
 * Generate Trading Signals - Dynamic Interval (5-60 minutes)
 * Analyzes market using Smart Money Concepts and creates trading signals
 */
// if ($tradingEnabled) {
//     Log::info("[Scheduler] Trading enabled - scheduling GenerateSignalsJob (interval: {$signalInterval} min)");
    
//     Schedule::job(new GenerateSignalsJob)
//         ->cron("*/{$signalInterval} * * * *")
//         ->name('generate-signals')
//         ->withoutOverlapping(10)
//         ->onOneServer()
//         ->before(function() {
//             Log::info('[Scheduler] GenerateSignalsJob about to run');
//         })
//         ->after(function() {
//             Log::info('[Scheduler] GenerateSignalsJob completed');
//         });
// } else {
//     Log::info('[Scheduler] Trading disabled - skipping GenerateSignalsJob');
// }

/**
 * Generate Trading Signals - Dynamic Interval (5 minutes-1 day)
 * Analyzes market using Smart Money Concepts and creates trading signals
 */
if ($tradingEnabled) {

    Log::info("[Scheduler] Trading enabled - scheduling GenerateSignalsJob (interval: {$signalInterval} min)");

    // Convert interval from minutes to hours
    $hours = intdiv($signalInterval, 60);
    $minutes = $signalInterval % 60;

    // Build cron expression based on interval
    if ($signalInterval < 60) {
        // 5–59 minutes
        $cron = "*/{$signalInterval} * * * *";
    } elseif ($signalInterval == 60) {
        // Exactly 1 hour
        $cron = "0 * * * *";
    } elseif ($signalInterval < 1440) {
        // More than 1 hour but less than 24 hours
        // Example: 180 minutes → every 3 hours
        $cron = "0 */{$hours} * * *";
    } else {
        // 1440 minutes or more = once per day
        $cron = "0 0 * * *"; // Midnight every day (you can change the time)
    }

    Schedule::job(new GenerateSignalsJob)
        ->cron($cron)
        ->name('generate-signals')
        ->withoutOverlapping(10)
        ->onOneServer()
        ->before(function () {
            Log::info('[Scheduler] GenerateSignalsJob about to run');
        })
        ->after(function () {
            Log::info('[Scheduler] GenerateSignalsJob completed');
        });

} else {
    Log::info('[Scheduler] Trading disabled - skipping GenerateSignalsJob');
}


/**
 * Expire Old Signals - Every 5 Minutes
 * Marks signals as expired based on configured expiry time
 */
Log::info('[Scheduler] Scheduling ExpireSignalsJob (every 5 minutes)');
Schedule::job(new ExpireSignalsJob)
    ->everyFiveMinutes()
    ->name('expire-signals')
    ->before(function() {
        Log::info('[Scheduler] ExpireSignalsJob about to run');
    })
    ->after(function() {
        Log::info('[Scheduler] ExpireSignalsJob completed');
    });

/**
 * Sync User Balances - Every 30 Minutes
 * Updates account balances from Bybit API
 */
if ($tradingEnabled) {
    Log::info('[Scheduler] Trading enabled - scheduling SyncUserBalancesJob (every 30 minutes)');
    
    Schedule::job(new SyncUserBalancesJob)
        ->everyThirtyMinutes()
        ->name('sync-balances')
        ->withoutOverlapping(5)
        ->before(function() {
            Log::info('[Scheduler] SyncUserBalancesJob about to run');
        })
        ->after(function() {
            Log::info('[Scheduler] SyncUserBalancesJob completed');
        });
} else {
    Log::info('[Scheduler] Trading disabled - skipping SyncUserBalancesJob');
}

// ==========================================
// ANALYTICS & REPORTING
// ==========================================

/**
 * Calculate Daily Statistics - 12:05 AM Daily
 * Computes user performance metrics and trade statistics
 */
Log::info('[Scheduler] Scheduling calculate-daily-stats (12:05 AM daily)');
Schedule::call(function () {
    Log::info('[Scheduler] Running calculate-daily-stats');
    
    // Calculate win rate
    $totalTrades = \DB::table('trades')->where('status', 'closed')->count();
    $winningTrades = \DB::table('trades')
        ->where('status', 'closed')
        ->where('realized_pnl', '>', 0)
        ->count();
    
    $winRate = $totalTrades > 0 ? ($winningTrades / $totalTrades) * 100 : 0;
    
    // Calculate total P&L
    $totalPnL = \DB::table('trades')
        ->where('status', 'closed')
        ->sum('realized_pnl');
    
    Log::info('[Scheduler] Daily stats calculated', [
        'total_trades' => $totalTrades,
        'winning_trades' => $winningTrades,
        'win_rate' => round($winRate, 2),
        'total_pnl' => $totalPnL
    ]);
})
    ->dailyAt('00:05')
    ->name('calculate-daily-stats');

/**
 * Send Daily Summary - 8:00 AM Daily (if enabled)
 * Emails performance summary to users
 */
if ($dailySummaryEnabled) {
    Log::info('[Scheduler] Daily summary enabled - scheduling send-daily-summary (8:00 AM daily)');
    
    Schedule::call(function () {
        Log::info('[Scheduler] Running send-daily-summary');
        
        // TODO: Implement email notification service
        // Get yesterday's performance
        $yesterdayTrades = \DB::table('trades')
            ->where('status', 'closed')
            ->whereDate('closed_at', today()->subDay())
            ->count();
            
        $yesterdayPnL = \DB::table('trades')
            ->where('status', 'closed')
            ->whereDate('closed_at', today()->subDay())
            ->sum('realized_pnl');
        
        Log::info('[Scheduler] Daily summary prepared', [
            'trades' => $yesterdayTrades,
            'pnl' => $yesterdayPnL
        ]);
    })
        ->dailyAt('08:00')
        ->name('send-daily-summary');
} else {
    Log::info('[Scheduler] Daily summary disabled - skipping send-daily-summary');
}

// ==========================================
// MAINTENANCE & CLEANUP
// ==========================================

/**
 * Cleanup Old Data - 2:00 AM Daily
 * Removes old logs, expired sessions, and stale data
 */
Log::info('[Scheduler] Scheduling cleanup-old-data (2:00 AM daily)');
Schedule::call(function () use ($dataRetentionDays, $logRetentionDays) {
    Log::info('[Scheduler] Running cleanup-old-data');
    
    // Delete old closed trades (beyond retention period)
    $deletedTrades = \DB::table('trades')
        ->where('status', 'closed')
        ->where('closed_at', '<', now()->subDays($dataRetentionDays))
        ->delete();
    
    // Delete old expired signals
    $deletedSignals = \DB::table('signals')
        ->where('status', 'expired')
        ->where('expires_at', '<', now()->subDays(30))
        ->delete();
    
    Log::info('[Scheduler] cleanup-old-data completed', [
        'deleted_trades' => $deletedTrades,
        'deleted_signals' => $deletedSignals
    ]);
})
    ->dailyAt('02:00')
    ->name('cleanup-old-data');

/**
 * Prune Failed Jobs - Daily
 * Removes old failed queue jobs
 */
Log::info('[Scheduler] Scheduling prune-failed-jobs (daily)');
Schedule::command('queue:prune-failed --hours=168')
    ->daily()
    ->name('prune-failed-jobs')
    ->before(function() {
        Log::info('[Scheduler] Running prune-failed-jobs command');
    })
    ->after(function() {
        Log::info('[Scheduler] prune-failed-jobs command completed');
    });

/**
 * Clear Cache - 3:00 AM Daily
 * Clears application cache to prevent memory issues
 */
Log::info('[Scheduler] Scheduling clear-cache (3:00 AM daily)');
Schedule::call(function () {
    Log::info('[Scheduler] Running clear-cache');
    
    try {
        \Artisan::call('cache:clear');
        \Artisan::call('config:cache');
        Log::info('[Scheduler] Cache cleared and recached successfully');
    } catch (\Exception $e) {
        Log::error('[Scheduler] Cache clear failed', [
            'error' => $e->getMessage()
        ]);
    }
})
    ->dailyAt('03:00')
    ->name('clear-cache');

/**
 * Prune Telescope Entries - Daily (if installed)
 * Keeps Telescope database lean
 */
if (class_exists(\Laravel\Telescope\Telescope::class)) {
    Log::info('[Scheduler] Telescope detected - scheduling prune-telescope (daily)');
    
    Schedule::command('telescope:prune')
        ->daily()
        ->name('prune-telescope')
        ->before(function() {
            Log::info('[Scheduler] Running telescope:prune command');
        })
        ->after(function() {
            Log::info('[Scheduler] telescope:prune command completed');
        });
} else {
    Log::info('[Scheduler] Telescope not installed - skipping prune-telescope');
}

/**
 * Horizon Snapshots - Every 5 Minutes (if installed)
 * Captures queue metrics for Horizon dashboard
 */
if (class_exists(\Laravel\Horizon\Horizon::class)) {
    Log::info('[Scheduler] Horizon detected - scheduling horizon-snapshot (every 5 minutes)');
    
    Schedule::command('horizon:snapshot')
        ->everyFiveMinutes()
        ->name('horizon-snapshot')
        ->before(function() {
            Log::info('[Scheduler] Running horizon:snapshot command');
        })
        ->after(function() {
            Log::info('[Scheduler] horizon:snapshot command completed');
        });
} else {
    Log::info('[Scheduler] Horizon not installed - skipping horizon-snapshot');
}

// ==========================================
// SYSTEM MONITORING & HEALTH CHECKS
// ==========================================

/**
 * System Health Check - Every 15 Minutes
 * Monitors stuck positions and failed API connections
 */
Log::info('[Scheduler] Scheduling system-health-check (every 15 minutes)');
Schedule::call(function () {
    Log::info('[Scheduler] Running system-health-check');
    
    if (!Setting::get('trading_enabled', true)) {
        Log::info('[Scheduler] Trading disabled - skipping health check');
        return;
    }
    
    // Check for stuck positions (not updated in 2+ hours)
    $stuckPositions = \DB::table('positions')
        ->where('is_active', true)
        ->where('last_updated_at', '<', now()->subHours(2))
        ->count();
        
    if ($stuckPositions > 0) {
        Log::error("Found {$stuckPositions} stuck positions (not updated in 2+ hours)");
    }
    
    // Check for failed API connections
    $failedAccounts = \DB::table('exchange_accounts')
        ->where('is_active', true)
        ->where('last_synced_at', '<', now()->subHours(1))
        ->count();
        
    if ($failedAccounts > 0) {
        Log::warning("{$failedAccounts} exchange accounts haven't synced in 1+ hour");
    }
    
    Log::info('[Scheduler] system-health-check completed');
})
    ->everyFifteenMinutes()
    ->name('system-health-check');

/**
 * Queue Size Monitoring - Every 10 Minutes
 * Alerts on high queue size or excessive failures
 */
Log::info('[Scheduler] Scheduling queue-monitoring (every 10 minutes)');
Schedule::call(function () {
    Log::info('[Scheduler] Running queue-monitoring');
    
    $queueSize = \DB::table('jobs')->count();
    $failedJobs = \DB::table('failed_jobs')->whereDate('failed_at', today())->count();
    
    if ($queueSize > 1000) {
        Log::warning("Queue size is high: {$queueSize} jobs pending");
    }
    
    if ($failedJobs > 50) {
        Log::error("High number of failed jobs today: {$failedJobs}");
    }
    
    Log::info('[Scheduler] queue-monitoring completed', [
        'queue_size' => $queueSize,
        'failed_jobs_today' => $failedJobs
    ]);
})
    ->everyTenMinutes()
    ->name('queue-monitoring');

/**
 * Circuit Breaker - Every 30 Minutes
 * Auto-disables trading if daily loss limit is exceeded
 */
if ($circuitBreakerEnabled) {
    Log::info('[Scheduler] Circuit breaker enabled - scheduling circuit-breaker-check (every 30 minutes)');
    
    Schedule::call(function () {
        Log::info('[Scheduler] Running circuit-breaker-check');
        
        $dailyLossLimit = (float) Setting::get('monitor_daily_loss_limit', 15);
        
        $dailyLoss = \DB::table('trades')
            ->where('status', 'closed')
            ->whereDate('closed_at', today())
            ->sum('realized_pnl');
            
        $totalBalance = \DB::table('exchange_accounts')
            ->where('is_active', true)
            ->sum('balance');
            
        if ($totalBalance > 0) {
            $lossPercent = abs($dailyLoss / $totalBalance) * 100;
            
            Log::info('[Scheduler] Circuit breaker check completed', [
                'daily_loss' => $dailyLoss,
                'total_balance' => $totalBalance,
                'loss_percent' => $lossPercent,
                'limit_percent' => $dailyLossLimit
            ]);
            
            if ($dailyLoss < 0 && $lossPercent >= $dailyLossLimit) {
                Log::critical("CIRCUIT BREAKER: Daily loss limit reached ({$lossPercent}% loss)");
                Setting::set('trading_enabled', false);
                // TODO: Send critical admin alert
                // TODO: Close all open positions
            }
        }
    })
        ->everyThirtyMinutes()
        ->name('circuit-breaker-check');
} else {
    Log::info('[Scheduler] Circuit breaker disabled - skipping circuit-breaker-check');
}

// ==========================================
// DATABASE BACKUP (Optional)
// ==========================================

/**
 * Database Backup - Frequency Based on Settings
 * Creates automated database backups
 */
if ($backupEnabled) {
    Log::info("[Scheduler] Backup enabled - scheduling database-backup ({$backupFrequency})");
    
    $backupSchedule = Schedule::call(function () {
        Log::info('[Scheduler] Running database-backup');
        
        try {
            \Artisan::call('backup:run');
            Log::info('Database backup completed successfully');
        } catch (\Exception $e) {
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    })->name('database-backup');

    match ($backupFrequency) {
        'hourly' => $backupSchedule->hourly(),
        'daily' => $backupSchedule->dailyAt('01:00'),
        'weekly' => $backupSchedule->weekly()->sundays()->at('01:00'),
        'monthly' => $backupSchedule->monthly(),
        default => $backupSchedule->dailyAt('01:00'),
    };
} else {
    Log::info('[Scheduler] Backup disabled - skipping database-backup');
}

Log::info('[Scheduler] Configuration completed - all jobs scheduled');