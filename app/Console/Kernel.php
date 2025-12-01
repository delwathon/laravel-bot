<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\MonitorPositionsJob;
use App\Jobs\GenerateSignalsJob;
use App\Jobs\ExpireSignalsJob;
use App\Jobs\SyncUserBalancesJob;
use App\Models\Setting;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ==========================================
        // DYNAMIC SETTINGS LOADING
        // ==========================================
        
        // Signal Generator Settings
        $signalInterval = (int) Setting::get('signal_interval', 15);
        
        // Trading Settings
        $tradingEnabled = (bool) Setting::get('trading_enabled', true);
        
        // Monitoring Settings  
        $monitorInterval = (int) Setting::get('monitor_interval', 1); // in minutes
        
        // Backup Settings
        $backupEnabled = (bool) Setting::get('backup_auto_backup', false);
        $backupFrequency = Setting::get('backup_frequency', 'daily');
        $dataRetentionDays = (int) Setting::get('backup_data_retention', 365);
        $logRetentionDays = (int) Setting::get('backup_log_retention', 90);
        
        // Notification Settings
        $dailySummaryEnabled = (bool) Setting::get('notifications_daily_summary', true);

        // ==========================================
        // CRITICAL: Position Monitoring (Dynamic Interval)
        // ==========================================
        
        if ($tradingEnabled) {
            if ($monitorInterval <= 1) {
                // Every minute monitoring
                $schedule->job(new MonitorPositionsJob)
                    ->everyMinute()
                    ->name('monitor-positions')
                    ->withoutOverlapping(5)
                    ->onSuccess(function () {
                        \Log::info('Position monitoring completed successfully');
                    })
                    ->onFailure(function () {
                        \Log::error('Position monitoring failed');
                    });
            } else {
                // Custom interval monitoring
                $schedule->job(new MonitorPositionsJob)
                    ->cron("*/{$monitorInterval} * * * *")
                    ->name('monitor-positions')
                    ->withoutOverlapping(5)
                    ->onSuccess(function () {
                        \Log::info('Position monitoring completed successfully');
                    })
                    ->onFailure(function () {
                        \Log::error('Position monitoring failed');
                    });
            }
        }

        // ==========================================
        // Signal Generation (Dynamic Interval from Settings)
        // ==========================================
        
        if ($tradingEnabled) {
            $schedule->job(new GenerateSignalsJob)
                ->cron("*/{$signalInterval} * * * *")
                ->name('generate-signals')
                ->withoutOverlapping(10)
                ->onSuccess(function () {
                    \Log::info('Signal generation completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Signal generation failed');
                });
        }

        // ==========================================
        // Signal Expiration Check (Every 5 Minutes)
        // ==========================================
        
        $schedule->job(new ExpireSignalsJob)
            ->everyFiveMinutes()
            ->name('expire-signals')
            ->onSuccess(function () {
                \Log::info('Signal expiration check completed');
            });

        // ==========================================
        // Balance Synchronization (Every 30 Minutes)
        // ==========================================
        
        if ($tradingEnabled) {
            $schedule->job(new SyncUserBalancesJob)
                ->everyThirtyMinutes()
                ->name('sync-balances')
                ->withoutOverlapping(5);
        }

        // ==========================================
        // Daily Statistics & Reports
        // ==========================================
        
        // Calculate daily user statistics
        $schedule->call(function () {
            \Artisan::call('app:calculate-user-stats');
        })
            ->dailyAt('00:05')
            ->name('calculate-daily-stats')
            ->runInBackground();

        // Generate and send daily performance reports
        if ($dailySummaryEnabled) {
            $schedule->call(function () {
                \Artisan::call('app:send-daily-reports');
            })
                ->dailyAt('08:00')
                ->name('send-daily-reports')
                ->runInBackground();
        }

        // ==========================================
        // Maintenance & Cleanup Tasks
        // ==========================================

        // Clean up expired signals (older than 24 hours)
        $schedule->call(function () {
            $deleted = \DB::table('signals')
                ->where('status', 'expired')
                ->where('created_at', '<', now()->subDay())
                ->delete();
                
            if ($deleted > 0) {
                \Log::info("Cleaned up {$deleted} expired signals");
            }
        })
            ->daily()
            ->at('02:00')
            ->name('cleanup-expired-signals');

        // Clean up old closed trades (based on data retention setting)
        $schedule->call(function () use ($dataRetentionDays) {
            $deleted = \DB::table('trades')
                ->where('status', 'closed')
                ->where('closed_at', '<', now()->subDays($dataRetentionDays))
                ->delete();
                
            if ($deleted > 0) {
                \Log::info("Archived {$deleted} old trades (retention: {$dataRetentionDays} days)");
            }
        })
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->name('archive-old-trades');

        // Clean up failed jobs (older than 7 days)
        $schedule->command('queue:flush')
            ->weekly()
            ->name('cleanup-failed-jobs');

        // Clean up old log files (based on log retention setting)
        $schedule->call(function () use ($logRetentionDays) {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/*.gz');
            
            $deleted = 0;
            foreach ($files as $file) {
                if (filemtime($file) < strtotime("-{$logRetentionDays} days")) {
                    unlink($file);
                    $deleted++;
                }
            }
            
            if ($deleted > 0) {
                \Log::info("Deleted {$deleted} old log files (retention: {$logRetentionDays} days)");
            }
        })
            ->weekly()
            ->name('delete-old-logs');

        // Prune old telescope/horizon entries if installed
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            $schedule->command('telescope:prune --hours=168')
                ->daily()
                ->name('prune-telescope');
        }

        if (class_exists(\Laravel\Horizon\Horizon::class)) {
            $schedule->command('horizon:snapshot')
                ->everyFiveMinutes()
                ->name('horizon-snapshot');
        }

        // ==========================================
        // Database Backup (Dynamic from Settings)
        // ==========================================
        
        if ($backupEnabled) {
            $backupJob = $schedule->call(function () {
                \Artisan::call('backup:run');
                \Log::info('Database backup completed');
            })->name('database-backup');

            match ($backupFrequency) {
                'hourly' => $backupJob->hourly(),
                'daily' => $backupJob->dailyAt('01:00'),
                'weekly' => $backupJob->weekly()->sundays()->at('01:00'),
                'monthly' => $backupJob->monthly(),
                default => $backupJob->dailyAt('01:00'),
            };
        }

        // ==========================================
        // Health Checks & Monitoring
        // ==========================================

        // Check system health and send alerts if needed
        $schedule->call(function () {
            // Check if trading is still enabled
            if (!Setting::get('trading_enabled', true)) {
                \Log::warning('Trading is currently disabled');
                return;
            }
            
            // Check for stuck positions
            $stuckPositions = \DB::table('positions')
                ->where('is_active', true)
                ->where('last_updated_at', '<', now()->subHours(2))
                ->count();
                
            if ($stuckPositions > 0) {
                \Log::error("Found {$stuckPositions} stuck positions (not updated in 2+ hours)");
                // TODO: Send admin alert
            }
            
            // Check for failed API connections
            $failedAccounts = \DB::table('exchange_accounts')
                ->where('is_active', true)
                ->where('last_synced_at', '<', now()->subHours(1))
                ->count();
                
            if ($failedAccounts > 0) {
                \Log::warning("{$failedAccounts} exchange accounts haven't synced in 1+ hour");
            }
        })
            ->everyFifteenMinutes()
            ->name('system-health-check');

        // Monitor queue size and alert if too large
        $schedule->call(function () {
            $queueSize = \DB::table('jobs')->count();
            $failedJobs = \DB::table('failed_jobs')->whereDate('failed_at', today())->count();
            
            if ($queueSize > 1000) {
                \Log::warning("Queue size is high: {$queueSize} jobs pending");
                // TODO: Send admin notification
            }
            
            if ($failedJobs > 50) {
                \Log::error("High number of failed jobs today: {$failedJobs}");
                // TODO: Send admin notification
            }
        })
            ->everyTenMinutes()
            ->name('queue-monitoring');

        // ==========================================
        // Circuit Breaker Check
        // ==========================================
        
        if (Setting::get('monitor_circuit_breaker', true)) {
            $schedule->call(function () {
                $dailyLossLimit = (float) Setting::get('monitor_daily_loss_limit', 15);
                $maxDrawdown = (float) Setting::get('monitor_max_drawdown', 25);
                
                // Check daily losses across all users
                $dailyLoss = \DB::table('trades')
                    ->where('status', 'closed')
                    ->whereDate('closed_at', today())
                    ->sum('realized_pnl');
                    
                // Get total account balances
                $totalBalance = \DB::table('exchange_accounts')
                    ->where('is_active', true)
                    ->sum('balance');
                    
                if ($totalBalance > 0) {
                    $lossPercent = abs($dailyLoss / $totalBalance) * 100;
                    
                    if ($dailyLoss < 0 && $lossPercent >= $dailyLossLimit) {
                        \Log::critical("CIRCUIT BREAKER: Daily loss limit reached ({$lossPercent}% loss)");
                        
                        // Disable trading
                        Setting::set('trading_enabled', false);
                        
                        // TODO: Send critical admin alert
                        // TODO: Close all open positions
                    }
                }
            })
                ->everyThirtyMinutes()
                ->name('circuit-breaker-check');
        }

        // ==========================================
        // Log Rotation
        // ==========================================
        
        // Compress old log files (older than 7 days)
        $schedule->call(function () {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/laravel-*.log');
            
            $compressed = 0;
            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-7 days')) {
                    exec("gzip {$file}");
                    $compressed++;
                }
            }
            
            if ($compressed > 0) {
                \Log::info("Compressed {$compressed} old log files");
            }
        })
            ->weekly()
            ->name('compress-logs');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     */
    protected function scheduleTimezone(): string
    {
        return Setting::get('system_timezone', config('app.timezone', 'UTC'));
    }
}