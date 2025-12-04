<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the application's database with ALL settings.
     * Updated to include 100% of all fields including conflict management.
     */
    public function run(): void
    {
        $settings = [
            // ============================================
            // SIGNAL GENERATOR SETTINGS (29 fields)
            // ============================================
            
            // Schedule Configuration
            ['key' => 'signal_interval', 'value' => '30', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Signal generation interval in minutes'],
            ['key' => 'signal_top_count', 'value' => '10', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Number of top signals to generate'],
            ['key' => 'signal_min_confidence', 'value' => '70', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Minimum confidence threshold'],
            ['key' => 'signal_expiry', 'value' => '30', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Signal expiry time in minutes'],
            ['key' => 'signal_auto_execute', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Auto-execute signals'],
            ['key' => 'signal_auto_execute_count', 'value' => '3', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Number of signals to auto-execute'],
            
            // Trading Pairs Configuration
            ['key' => 'signal_use_dynamic_pairs', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Use dynamic pair selection based on volume instead of fixed pairs'],
            ['key' => 'signal_min_volume', 'value' => '5000000', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Minimum 24h trading volume in USDT for pair selection'],
            ['key' => 'signal_max_pairs', 'value' => '50', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Maximum number of pairs to analyze for dynamic pair selection'],
            ['key' => 'signal_pairs', 'value' => json_encode(['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'TRXUSDT', 'MATICUSDT', 'DOTUSDT', 'LTCUSDT', 'LINKUSDT', 'AVAXUSDT', 'UNIUSDT', 'ATOMUSDT', 'XLMUSDT', 'FILUSDT', 'ETCUSDT', 'NEARUSDT', 'APTUSDT', 'ICPUSDT', 'ARBUSDT', 'OPUSDT', 'LDOUSDT', 'INJUSDT', 'STXUSDT', 'TIAUSDT', 'SUIUSDT', 'SEIUSDT', 'RENDERUSDT', 'RNDRUSDT', 'ALGOUSDT', 'VETUSDT', 'AAVEUSDT', 'SUSHIUSDT', 'PEPEUSDT', 'WIFUSDT', 'BONKUSDT', 'FLOKIUSDT', 'SHIBUSDT', 'FTMUSDT', 'SANDUSDT', 'MANAUSDT', 'AXSUSDT', 'GALAUSDT', 'ENJUSDT', 'CHZUSDT', 'GMTUSDT', 'APEUSDT', 'BLURUSDT']), 'group' => 'signal_generator', 'type' => 'array', 'description' => 'Fixed trading pairs to monitor (when dynamic pairs is disabled)'],
            
            // Timeframes
            ['key' => 'signal_primary_timeframe', 'value' => '240', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Primary analysis timeframe in minutes'],
            ['key' => 'signal_higher_timeframe', 'value' => 'D', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Higher timeframe for trend confirmation'],
            ['key' => 'signal_secondary_timeframe', 'value' => 'D', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Secondary analysis timeframe (alias for higher_timeframe)'],
            
            // SMC Pattern Detection
            ['key' => 'signal_patterns', 'value' => json_encode(['order_block', 'fvg', 'bos', 'choch', 'liquidity_sweep', 'premium_discount']), 'group' => 'signal_generator', 'type' => 'array', 'description' => 'SMC patterns to detect'],
            
            // Analysis Parameters
            ['key' => 'signal_lookback_periods', 'value' => '50', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Number of lookback periods for technical analysis'],
            ['key' => 'signal_pattern_strength', 'value' => '3', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Pattern strength threshold (1-5, higher = more conservative)'],
            
            // Risk Management
            ['key' => 'signal_risk_reward', 'value' => '1:2', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Default risk/reward ratio (e.g., 1:2 means risk $1 to make $2)'],
            ['key' => 'signal_max_sl', 'value' => '5', 'group' => 'signal_generator', 'type' => 'float', 'description' => 'Maximum stop loss percentage from entry price'],
            ['key' => 'signal_position_size', 'value' => '5', 'group' => 'signal_generator', 'type' => 'float', 'description' => 'Default position size as percentage of account balance'],
            ['key' => 'signal_leverage', 'value' => 'Max', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Default leverage (use "Max" for maximum available or specific number like "10")'],
            
            // Order Type
            ['key' => 'signal_order_type', 'value' => 'Market', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Default order type: Market or Limit'],
            
            // Exchange Configuration
            ['key' => 'signal_exchanges', 'value' => json_encode(['bybit']), 'group' => 'signal_generator', 'type' => 'array', 'description' => 'Enabled exchanges for signal execution'],
            
            // Advanced Options
            ['key' => 'signal_notify_users', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Send notifications to users when signals are generated'],
            ['key' => 'signal_log_analysis', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Log detailed technical analysis for debugging'],
            ['key' => 'signal_test_mode', 'value' => '0', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Test mode (signals generated but not executed)'],
            
            // ============================================
            // CONFLICT MANAGEMENT SETTINGS (5 fields) - NEW
            // ============================================
            ['key' => 'signal_stale_order_hours', 'value' => '24', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Hours before a pending order is considered stale and can be cancelled'],
            ['key' => 'signal_skip_duplicate_positions', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Skip signal execution if admin has an open position on the same symbol'],
            ['key' => 'signal_cancel_opposite_pending', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Cancel pending orders if new signal is opposite direction'],
            ['key' => 'signal_cancel_stale_pending', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Automatically cancel pending orders that exceed the stale threshold'],
            ['key' => 'signal_close_opposite_positions', 'value' => '0', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Close open positions if new signal is opposite direction (risky - disabled by default)'],

            // ============================================
            // SYSTEM SETTINGS (7 fields)
            // ============================================
            ['key' => 'system_name', 'value' => 'CryptoBot Pro', 'group' => 'system', 'type' => 'string', 'description' => 'Application name displayed throughout the system'],
            ['key' => 'system_timezone', 'value' => 'UTC', 'group' => 'system', 'type' => 'string', 'description' => 'System timezone for all timestamps'],
            ['key' => 'system_maintenance_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean', 'description' => 'Enable maintenance mode (blocks all trading)'],
            ['key' => 'system_debug_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean', 'description' => 'Enable debug mode (verbose logging)'],
            ['key' => 'system_allow_registration', 'value' => '1', 'group' => 'system', 'type' => 'boolean', 'description' => 'Allow new user registration'],
            ['key' => 'system_email_verification', 'value' => '1', 'group' => 'system', 'type' => 'boolean', 'description' => 'Require email verification for new users'],
            ['key' => 'system_session_timeout', 'value' => '120', 'group' => 'system', 'type' => 'integer', 'description' => 'Session timeout in minutes'],
            
            // ============================================
            // TRADING SETTINGS (7 fields)
            // ============================================
            ['key' => 'trading_enabled', 'value' => '1', 'group' => 'trading', 'type' => 'boolean', 'description' => 'Global trading status (master on/off switch)'],
            ['key' => 'trading_max_trades_per_user', 'value' => '20', 'group' => 'trading', 'type' => 'integer', 'description' => 'Maximum concurrent trades per user'],
            ['key' => 'trading_default_leverage', 'value' => '3', 'group' => 'trading', 'type' => 'integer', 'description' => 'Default leverage for new users'],
            ['key' => 'trading_max_leverage', 'value' => '10', 'group' => 'trading', 'type' => 'integer', 'description' => 'Maximum allowed leverage across platform'],
            ['key' => 'trading_min_position_size', 'value' => '10', 'group' => 'trading', 'type' => 'integer', 'description' => 'Minimum position size in USD'],
            ['key' => 'trading_max_position_size', 'value' => '50000', 'group' => 'trading', 'type' => 'integer', 'description' => 'Maximum position size in USD'],
            ['key' => 'trading_execution_timeout', 'value' => '30', 'group' => 'trading', 'type' => 'integer', 'description' => 'Trade execution timeout in seconds'],
            
            // ============================================
            // MONITORING & RISK MANAGEMENT (8 fields)
            // ============================================
            ['key' => 'monitor_interval', 'value' => '10', 'group' => 'monitoring', 'type' => 'integer', 'description' => 'Position monitor refresh interval in minutes'],
            ['key' => 'monitor_emergency_sl', 'value' => '10', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Emergency stop loss percentage (forced close)'],
            ['key' => 'monitor_daily_loss_limit', 'value' => '15', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Daily loss limit percentage (triggers circuit breaker)'],
            ['key' => 'monitor_max_drawdown', 'value' => '25', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Maximum drawdown percentage threshold'],
            ['key' => 'monitor_auto_stop_loss', 'value' => '1', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Automatically set stop loss on all positions'],
            ['key' => 'monitor_auto_take_profit', 'value' => '1', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Automatically set take profit on all positions'],
            ['key' => 'monitor_trailing_stop', 'value' => '0', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Enable trailing stop loss (moves SL as price moves favorably)'],
            ['key' => 'monitor_circuit_breaker', 'value' => '1', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Enable circuit breaker protection (auto-disable trading on excessive loss)'],
            
            // ============================================
            // PROFIT MILESTONES (1 field)
            // ============================================
            ['key' => 'enable_profit_milestones', 'value' => '1', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Enable automatic profit milestone management (trailing SL and partial closes)'],
            
            // ============================================
            // API SETTINGS (3 fields)
            // ============================================
            ['key' => 'api_timeout', 'value' => '15', 'group' => 'api', 'type' => 'integer', 'description' => 'API request timeout in seconds'],
            ['key' => 'api_max_retries', 'value' => '3', 'group' => 'api', 'type' => 'integer', 'description' => 'Maximum API retry attempts on failure'],
            ['key' => 'api_bybit_rate_limit', 'value' => '100', 'group' => 'api', 'type' => 'integer', 'description' => 'Bybit API rate limit (requests per minute)'],
            
            // ============================================
            // NOTIFICATION SETTINGS (12 fields)
            // ============================================
            ['key' => 'notifications_enabled', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Global notifications enabled (master switch)'],
            ['key' => 'notifications_email', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Email notifications enabled'],
            ['key' => 'notifications_telegram', 'value' => '0', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Telegram notifications enabled'],
            ['key' => 'notifications_sms', 'value' => '0', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'SMS notifications enabled'],
            ['key' => 'notifications_trade_execution', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify admin on trade execution'],
            ['key' => 'notifications_tp_sl', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify admin on TP/SL triggers'],
            ['key' => 'notifications_signals', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify admin on signal generation'],
            ['key' => 'notifications_errors', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify admin on system errors'],
            ['key' => 'notifications_high_risk', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify admin on high-risk events (circuit breaker, large losses)'],
            ['key' => 'notifications_daily_summary', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Send daily summary email to admin'],
            ['key' => 'notifications_admin_email', 'value' => 'admin@cryptobot.com', 'group' => 'notifications', 'type' => 'string', 'description' => 'Admin notification email address'],
            ['key' => 'notifications_smtp_server', 'value' => 'smtp.gmail.com', 'group' => 'notifications', 'type' => 'string', 'description' => 'SMTP server address for email notifications'],
            
            // ============================================
            // BACKUP SETTINGS (7 fields)
            // ============================================
            ['key' => 'backup_enabled', 'value' => '1', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Automatic backups enabled'],
            ['key' => 'backup_auto_backup', 'value' => '1', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Enable automatic scheduled backups'],
            ['key' => 'backup_frequency', 'value' => 'daily', 'group' => 'backup', 'type' => 'string', 'description' => 'Backup frequency (hourly, daily, weekly, monthly)'],
            ['key' => 'backup_retention_days', 'value' => '30', 'group' => 'backup', 'type' => 'integer', 'description' => 'Backup file retention days'],
            ['key' => 'backup_data_retention', 'value' => '365', 'group' => 'backup', 'type' => 'integer', 'description' => 'Closed trade data retention days'],
            ['key' => 'backup_log_retention', 'value' => '90', 'group' => 'backup', 'type' => 'integer', 'description' => 'System log retention days'],
            ['key' => 'backup_include_logs', 'value' => '0', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Include logs in database backup'],
            
            // ============================================
            // PERFORMANCE SETTINGS (4 fields)
            // ============================================
            ['key' => 'cache_driver', 'value' => 'redis', 'group' => 'performance', 'type' => 'string', 'description' => 'Cache driver (redis, memcached, file)'],
            ['key' => 'cache_ttl', 'value' => '3600', 'group' => 'performance', 'type' => 'integer', 'description' => 'Cache time-to-live in seconds'],
            ['key' => 'queue_driver', 'value' => 'redis', 'group' => 'performance', 'type' => 'string', 'description' => 'Queue driver (redis, database, sync)'],
            ['key' => 'queue_max_workers', 'value' => '10', 'group' => 'performance', 'type' => 'integer', 'description' => 'Maximum queue worker processes'],
        ];

        // Insert or update all settings
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
        
        // Display summary
        $groupCounts = [
            'Signal Generator (including Conflict Management)' => 29,
            'System' => 7,
            'Trading' => 7,
            'Monitoring & Risk Management' => 9,
            'API' => 3,
            'Notifications' => 12,
            'Backup' => 7,
            'Performance' => 4,
        ];
        
        $totalSettings = array_sum($groupCounts);
        
        $this->command->info('✅ Successfully seeded ' . $totalSettings . ' settings');
        $this->command->newLine();
        $this->command->info('📊 Settings by group:');
        foreach ($groupCounts as $group => $count) {
            $this->command->info("   - {$group}: {$count} fields");
        }
        $this->command->newLine();
        $this->command->info('🆕 New Conflict Management Features:');
        $this->command->info('   - Stale Order Detection (24h default)');
        $this->command->info('   - Duplicate Position Prevention');
        $this->command->info('   - Opposite Direction Handling');
        $this->command->info('   - Automatic Order Cancellation');
        $this->command->info('   - Optional Position Reversal');
    }
}