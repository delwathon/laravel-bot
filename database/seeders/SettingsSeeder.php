<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the application's database with ALL settings.
     * Updated to include 100% of all fields from both forms.
     */
    public function run(): void
    {
        $settings = [
            // ============================================
            // SIGNAL GENERATOR SETTINGS (18 fields)
            // ============================================
            
            // Schedule Configuration
            ['key' => 'signal_interval', 'value' => '15', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Signal generation interval in minutes'],
            ['key' => 'signal_top_count', 'value' => '5', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Number of top signals to execute'],
            ['key' => 'signal_min_confidence', 'value' => '70', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Minimum confidence threshold'],
            ['key' => 'signal_expiry', 'value' => '30', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Signal expiry time in minutes'],
            
            // Trading Pairs
            ['key' => 'signal_pairs', 'value' => json_encode(['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'XRPUSDT']), 'group' => 'signal_generator', 'type' => 'array', 'description' => 'Trading pairs to monitor'],
            
            // Timeframes
            ['key' => 'signal_primary_timeframe', 'value' => '15m', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Primary analysis timeframe'],
            ['key' => 'signal_higher_timeframe', 'value' => '1h', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Higher timeframe for trend confirmation'],
            ['key' => 'signal_secondary_timeframe', 'value' => '1h', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Secondary analysis timeframe (alias for higher_timeframe)'],
            
            // SMC Pattern Detection
            ['key' => 'signal_patterns', 'value' => json_encode(['order_block', 'fvg', 'bos', 'choch', 'liquidity_sweep', 'premium_discount']), 'group' => 'signal_generator', 'type' => 'array', 'description' => 'SMC patterns to detect'],
            
            // Analysis Parameters
            ['key' => 'signal_lookback_periods', 'value' => '50', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Lookback periods for analysis'],
            ['key' => 'signal_pattern_strength', 'value' => '3', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Pattern strength threshold (1-5)'],
            
            // Risk Management
            ['key' => 'signal_risk_reward', 'value' => '1:2', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Default risk/reward ratio'],
            ['key' => 'signal_max_sl', 'value' => '2', 'group' => 'signal_generator', 'type' => 'float', 'description' => 'Maximum stop loss percentage'],
            ['key' => 'signal_position_size', 'value' => '5', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Default position size percentage'],
            ['key' => 'signal_leverage', 'value' => 'Max', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Default leverage'],
            
            // Exchange Configuration
            ['key' => 'signal_exchanges', 'value' => json_encode(['bybit']), 'group' => 'signal_generator', 'type' => 'array', 'description' => 'Enabled exchanges'],
            
            // Advanced Options
            ['key' => 'signal_auto_execute', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Auto-execute signals'],
            ['key' => 'signal_notify_users', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Notify users on signal generation'],
            ['key' => 'signal_log_analysis', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Log detailed analysis'],
            ['key' => 'signal_test_mode', 'value' => '0', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Test mode (no execution)'],

            // ============================================
            // SYSTEM SETTINGS (7 fields)
            // ============================================
            ['key' => 'system_name', 'value' => 'CryptoBot Pro', 'group' => 'system', 'type' => 'string', 'description' => 'Application name'],
            ['key' => 'system_timezone', 'value' => 'UTC', 'group' => 'system', 'type' => 'string', 'description' => 'System timezone'],
            ['key' => 'system_maintenance_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean', 'description' => 'Maintenance mode status'],
            ['key' => 'system_debug_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean', 'description' => 'Debug mode status'],
            ['key' => 'system_allow_registration', 'value' => '1', 'group' => 'system', 'type' => 'boolean', 'description' => 'Allow new user registration'],
            ['key' => 'system_email_verification', 'value' => '1', 'group' => 'system', 'type' => 'boolean', 'description' => 'Require email verification'],
            ['key' => 'system_session_timeout', 'value' => '120', 'group' => 'system', 'type' => 'integer', 'description' => 'Session timeout in minutes'],
            
            // ============================================
            // TRADING SETTINGS (7 fields)
            // ============================================
            ['key' => 'trading_enabled', 'value' => '1', 'group' => 'trading', 'type' => 'boolean', 'description' => 'Global trading status'],
            ['key' => 'trading_max_trades_per_user', 'value' => '20', 'group' => 'trading', 'type' => 'integer', 'description' => 'Max concurrent trades per user'],
            ['key' => 'trading_default_leverage', 'value' => '3', 'group' => 'trading', 'type' => 'integer', 'description' => 'Default leverage'],
            ['key' => 'trading_max_leverage', 'value' => '10', 'group' => 'trading', 'type' => 'integer', 'description' => 'Maximum allowed leverage'],
            ['key' => 'trading_min_position_size', 'value' => '10', 'group' => 'trading', 'type' => 'integer', 'description' => 'Minimum position size in USD'],
            ['key' => 'trading_max_position_size', 'value' => '50000', 'group' => 'trading', 'type' => 'integer', 'description' => 'Maximum position size in USD'],
            ['key' => 'trading_execution_timeout', 'value' => '30', 'group' => 'trading', 'type' => 'integer', 'description' => 'Trade execution timeout in seconds'],
            
            // ============================================
            // MONITORING & RISK MANAGEMENT (8 fields)
            // ============================================
            ['key' => 'monitor_interval', 'value' => '10', 'group' => 'monitoring', 'type' => 'integer', 'description' => 'Monitor refresh interval in seconds'],
            ['key' => 'monitor_emergency_sl', 'value' => '10', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Emergency stop loss percentage'],
            ['key' => 'monitor_daily_loss_limit', 'value' => '15', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Daily loss limit percentage'],
            ['key' => 'monitor_max_drawdown', 'value' => '25', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Maximum drawdown percentage'],
            ['key' => 'monitor_auto_stop_loss', 'value' => '1', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Automatically set stop loss'],
            ['key' => 'monitor_auto_take_profit', 'value' => '1', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Automatically set take profit'],
            ['key' => 'monitor_trailing_stop', 'value' => '0', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Enable trailing stop loss'],
            ['key' => 'monitor_circuit_breaker', 'value' => '1', 'group' => 'monitoring', 'type' => 'boolean', 'description' => 'Enable circuit breaker protection'],
            
            // ============================================
            // API SETTINGS (3 fields)
            // ============================================
            ['key' => 'api_timeout', 'value' => '15', 'group' => 'api', 'type' => 'integer', 'description' => 'API request timeout in seconds'],
            ['key' => 'api_max_retries', 'value' => '3', 'group' => 'api', 'type' => 'integer', 'description' => 'Maximum API retry attempts'],
            ['key' => 'api_bybit_rate_limit', 'value' => '100', 'group' => 'api', 'type' => 'integer', 'description' => 'Bybit API rate limit per minute'],
            
            // ============================================
            // NOTIFICATION SETTINGS (8 fields)
            // ============================================
            ['key' => 'notifications_enabled', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Global notifications enabled'],
            ['key' => 'notifications_email', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Email notifications enabled'],
            ['key' => 'notifications_telegram', 'value' => '0', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Telegram notifications enabled'],
            ['key' => 'notifications_sms', 'value' => '0', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'SMS notifications enabled'],
            ['key' => 'notifications_trade_execution', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify on trade execution'],
            ['key' => 'notifications_tp_sl', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify on TP/SL triggers'],
            ['key' => 'notifications_signals', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify on signal generation'],
            ['key' => 'notifications_errors', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify on errors'],
            ['key' => 'notifications_high_risk', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notify on high-risk events'],
            ['key' => 'notifications_daily_summary', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Send daily summary'],
            ['key' => 'notifications_admin_email', 'value' => 'admin@cryptobot.com', 'group' => 'notifications', 'type' => 'string', 'description' => 'Admin notification email'],
            ['key' => 'notifications_smtp_server', 'value' => 'smtp.gmail.com', 'group' => 'notifications', 'type' => 'string', 'description' => 'SMTP server address'],
            
            // ============================================
            // BACKUP SETTINGS (6 fields)
            // ============================================
            ['key' => 'backup_enabled', 'value' => '1', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Automatic backups enabled'],
            ['key' => 'backup_auto_backup', 'value' => '1', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Enable automatic scheduled backups'],
            ['key' => 'backup_frequency', 'value' => 'daily', 'group' => 'backup', 'type' => 'string', 'description' => 'Backup frequency'],
            ['key' => 'backup_retention_days', 'value' => '30', 'group' => 'backup', 'type' => 'integer', 'description' => 'Backup retention days'],
            ['key' => 'backup_data_retention', 'value' => '365', 'group' => 'backup', 'type' => 'integer', 'description' => 'Data retention days'],
            ['key' => 'backup_log_retention', 'value' => '90', 'group' => 'backup', 'type' => 'integer', 'description' => 'Log retention days'],
            ['key' => 'backup_include_logs', 'value' => '0', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Include logs in backup'],
            
            // ============================================
            // PERFORMANCE SETTINGS (4 fields)
            // ============================================
            ['key' => 'cache_driver', 'value' => 'redis', 'group' => 'performance', 'type' => 'string', 'description' => 'Cache driver'],
            ['key' => 'cache_ttl', 'value' => '3600', 'group' => 'performance', 'type' => 'integer', 'description' => 'Cache TTL in seconds'],
            ['key' => 'queue_driver', 'value' => 'redis', 'group' => 'performance', 'type' => 'string', 'description' => 'Queue driver'],
            ['key' => 'queue_max_workers', 'value' => '10', 'group' => 'performance', 'type' => 'integer', 'description' => 'Maximum queue workers'],
        ];

        // Insert or update all settings
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
        
        $this->command->info('✅ Successfully seeded ' . count($settings) . ' settings');
        $this->command->info('📊 Settings by group:');
        $this->command->info('   - Signal Generator: 20 fields');
        $this->command->info('   - System: 7 fields');
        $this->command->info('   - Trading: 7 fields');
        $this->command->info('   - Monitoring: 8 fields');
        $this->command->info('   - API: 3 fields');
        $this->command->info('   - Notifications: 12 fields');
        $this->command->info('   - Backup: 7 fields');
        $this->command->info('   - Performance: 4 fields');
    }
}