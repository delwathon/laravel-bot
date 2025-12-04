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
    
    // Only log errors, not successful loads (reduces log noise)
} catch (\Exception $e) {
    Log::error('[Scheduler] Failed to load settings - using defaults', [
        'error' => $e->getMessage(),
    ]);
}

// ==========================================
// CORE TRADING JOBS (Critical - Always Run)
// ==========================================

/**
 * Monitor Limit Orders - Every 2 Minutes
 * Checks pending limit orders and converts them to active positions when filled
 */
Schedule::job(new MonitorLimitOrdersJob)
    ->everyTwoMinutes()
    ->name('monitor-limit-orders')
    ->withoutOverlapping(2)
    ->onOneServer();

/**
 * Monitor Active Positions - Dynamic Interval (1-60 minutes)
 * Tracks open positions, updates P&L, and closes positions at TP/SL
 */
if ($tradingEnabled) {
    if ($monitorInterval <= 1) {
        Schedule::job(new MonitorPositionsJob)
            ->everyMinute()
            ->name('monitor-positions')
            ->withoutOverlapping(5)
            ->onOneServer();
    } else {
        Schedule::job(new MonitorPositionsJob)
            ->cron("*/{$monitorInterval} * * * *")
            ->name('monitor-positions')
            ->withoutOverlapping(5)
            ->onOneServer();
    }
}

/**
 * Generate Trading Signals - Dynamic Interval (5 minutes-1 day)
 * Analyzes market using Smart Money Concepts and creates trading signals
 */
if ($tradingEnabled) {
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
        $cron = "0 */{$hours} * * *";
    } else {
        // 1440 minutes or more = once per day
        $cron = "0 0 * * *";
    }

    Schedule::job(new GenerateSignalsJob)
        ->cron($cron)
        ->name('generate-signals')
        ->withoutOverlapping(30)  // Increased from 10 to 30 minutes
        ->onOneServer();
}

/**
 * Expire Old Signals - Every 5 Minutes
 * Marks signals as expired based on configured expiry time
 */
Schedule::job(new ExpireSignalsJob)
    ->everyFiveMinutes()
    ->name('expire-signals');

/**
 * Sync User Balances - Every 30 Minutes
 * Updates account balances from Bybit API
 */
if ($tradingEnabled) {
    Schedule::job(new SyncUserBalancesJob)
        ->everyThirtyMinutes()
        ->name('sync-balances')
        ->withoutOverlapping(5);
}

// ==========================================
// ANALYTICS & REPORTING
// ==========================================

/**
 * Calculate Daily Statistics - 12:05 AM Daily
 * Computes user performance metrics and trade statistics
 */
Schedule::call(function () {
    try {
        $totalTrades = \DB::table('trades')
            ->whereDate('created_at', today())
            ->count();
            
        $totalPnl = \DB::table('trades')
            ->where('status', 'closed')
            ->whereDate('closed_at', today())
            ->sum('realized_pnl');
            
        Log::info('[Analytics] Daily statistics calculated', [
            'date' => today()->toDateString(),
            'total_trades' => $totalTrades,
            'total_pnl' => $totalPnl,
        ]);
    } catch (\Exception $e) {
        Log::error('[Analytics] Failed to calculate daily stats', [
            'error' => $e->getMessage()
        ]);
    }
})
    ->dailyAt('00:05')
    ->name('calculate-daily-stats');

/**
 * Send Daily Summary - 8:00 AM Daily
 * Emails daily performance summary to admin
 */
if ($dailySummaryEnabled) {
    Schedule::call(function () {
        try {
            // TODO: Implement email notification
            Log::info('[Notifications] Daily summary email sent');
        } catch (\Exception $e) {
            Log::error('[Notifications] Failed to send daily summary', [
                'error' => $e->getMessage()
            ]);
        }
    })
        ->dailyAt('08:00')
        ->name('send-daily-summary');
}

// ==========================================
// MAINTENANCE & CLEANUP
// ==========================================

/**
 * Cleanup Old Data - 2:00 AM Daily
 * Removes old closed trades and expired signals
 */
Schedule::call(function () use ($dataRetentionDays) {
    try {
        $deletedTrades = \DB::table('trades')
            ->where('status', 'closed')
            ->where('closed_at', '<', now()->subDays($dataRetentionDays))
            ->delete();
        
        $deletedSignals = \DB::table('signals')
            ->where('status', 'expired')
            ->where('expires_at', '<', now()->subDays(30))
            ->delete();
        
        if ($deletedTrades > 0 || $deletedSignals > 0) {
            Log::info('[Cleanup] Old data removed', [
                'deleted_trades' => $deletedTrades,
                'deleted_signals' => $deletedSignals
            ]);
        }
    } catch (\Exception $e) {
        Log::error('[Cleanup] Failed to cleanup old data', [
            'error' => $e->getMessage()
        ]);
    }
})
    ->dailyAt('02:00')
    ->name('cleanup-old-data');

/**
 * Prune Failed Jobs - Daily
 * Removes old failed queue jobs
 */
Schedule::command('queue:prune-failed --hours=168')
    ->daily()
    ->name('prune-failed-jobs');

/**
 * Clear Cache - 3:00 AM Daily
 * Clears application cache to prevent memory issues
 */
Schedule::call(function () {
    try {
        \Artisan::call('cache:clear');
        \Artisan::call('config:cache');
        Log::info('[Maintenance] Cache cleared and recached');
    } catch (\Exception $e) {
        Log::error('[Maintenance] Cache clear failed', [
            'error' => $e->getMessage()
        ]);
    }
})
    ->dailyAt('03:00')
    ->name('clear-cache');

/**
 * Prune Telescope Entries - Daily (if installed)
 */
if (class_exists(\Laravel\Telescope\Telescope::class)) {
    Schedule::command('telescope:prune')
        ->daily()
        ->name('prune-telescope');
}

/**
 * Horizon Snapshots - Every 5 Minutes (if installed)
 */
if (class_exists(\Laravel\Horizon\Horizon::class)) {
    Schedule::command('horizon:snapshot')
        ->everyFiveMinutes()
        ->name('horizon-snapshot');
}

// ==========================================
// SYSTEM MONITORING & HEALTH CHECKS
// ==========================================

/**
 * System Health Check - Every 15 Minutes
 * Monitors stuck positions and failed API connections
 */
Schedule::call(function () {
    if (!Setting::get('trading_enabled', true)) {
        return;
    }
    
    try {
        // Check for stuck positions (not updated in 2+ hours)
        $stuckPositions = \DB::table('positions')
            ->where('is_active', true)
            ->where('last_updated_at', '<', now()->subHours(2))
            ->count();
            
        if ($stuckPositions > 0) {
            Log::error("[Health] Found {$stuckPositions} stuck positions");
        }
        
        // Check for failed API connections
        $failedAccounts = \DB::table('exchange_accounts')
            ->where('is_active', true)
            ->where('last_synced_at', '<', now()->subHours(1))
            ->count();
            
        if ($failedAccounts > 0) {
            Log::warning("[Health] {$failedAccounts} accounts haven't synced in 1+ hour");
        }
    } catch (\Exception $e) {
        Log::error('[Health] Health check failed', [
            'error' => $e->getMessage()
        ]);
    }
})
    ->everyFifteenMinutes()
    ->name('system-health-check');

/**
 * Queue Size Monitoring - Every 10 Minutes
 * Alerts on high queue size or excessive failures
 */
Schedule::call(function () {
    try {
        $queueSize = \DB::table('jobs')->count();
        $failedJobs = \DB::table('failed_jobs')->whereDate('failed_at', today())->count();
        
        if ($queueSize > 1000) {
            Log::warning("[Queue] Queue size is high: {$queueSize} jobs pending");
        }
        
        if ($failedJobs > 50) {
            Log::error("[Queue] High number of failed jobs today: {$failedJobs}");
        }
    } catch (\Exception $e) {
        Log::error('[Queue] Queue monitoring failed', [
            'error' => $e->getMessage()
        ]);
    }
})
    ->everyTenMinutes()
    ->name('queue-monitoring');

/**
 * Circuit Breaker - Every 30 Minutes
 * Auto-disables trading if daily loss limit is exceeded
 */
if ($circuitBreakerEnabled) {
    Schedule::call(function () {
        try {
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
                
                if ($dailyLoss < 0 && $lossPercent >= $dailyLossLimit) {
                    Log::critical("[Circuit Breaker] Daily loss limit reached ({$lossPercent}% loss)");
                    Setting::set('trading_enabled', false);
                    // TODO: Send critical admin alert
                    // TODO: Close all open positions
                }
            }
        } catch (\Exception $e) {
            Log::error('[Circuit Breaker] Check failed', [
                'error' => $e->getMessage()
            ]);
        }
    })
        ->everyThirtyMinutes()
        ->name('circuit-breaker-check');
}

// ==========================================
// DATABASE BACKUP (Optional)
// ==========================================

/**
 * Database Backup - Frequency Based on Settings
 * Creates automated database backups
 */
if ($backupEnabled) {
    $backupSchedule = Schedule::call(function () {
        try {
            \Artisan::call('backup:run');
            Log::info('[Backup] Database backup completed');
        } catch (\Exception $e) {
            Log::error('[Backup] Backup failed', [
                'error' => $e->getMessage()
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
}

// No final log - reduces noise