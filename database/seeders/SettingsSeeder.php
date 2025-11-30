<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Signal Generator Settings
            ['key' => 'signal_interval', 'value' => '15', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Signal generation interval in minutes'],
            ['key' => 'signal_top_count', 'value' => '5', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Number of top signals to execute'],
            ['key' => 'signal_min_confidence', 'value' => '70', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Minimum confidence threshold'],
            ['key' => 'signal_expiry', 'value' => '30', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Signal expiry time in minutes'],
            ['key' => 'signal_pairs', 'value' => json_encode(['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'XRPUSDT']), 'group' => 'signal_generator', 'type' => 'array', 'description' => 'Trading pairs to monitor'],
            ['key' => 'signal_primary_timeframe', 'value' => '15', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Primary analysis timeframe'],
            ['key' => 'signal_secondary_timeframe', 'value' => '60', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Secondary analysis timeframe'],
            ['key' => 'signal_lookback_periods', 'value' => '200', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Lookback periods for analysis'],
            ['key' => 'signal_pattern_strength', 'value' => '3', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Pattern strength threshold'],
            ['key' => 'signal_risk_reward', 'value' => '1:2', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Default risk/reward ratio'],
            ['key' => 'signal_max_sl', 'value' => '2', 'group' => 'signal_generator', 'type' => 'float', 'description' => 'Maximum stop loss percentage'],
            ['key' => 'signal_position_size', 'value' => '5', 'group' => 'signal_generator', 'type' => 'integer', 'description' => 'Default position size percentage'],
            ['key' => 'signal_leverage', 'value' => 'Max', 'group' => 'signal_generator', 'type' => 'string', 'description' => 'Default leverage'],
            ['key' => 'signal_exchanges', 'value' => json_encode(['bybit']), 'group' => 'signal_generator', 'type' => 'array', 'description' => 'Enabled exchanges'],
            ['key' => 'signal_auto_execute', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Auto-execute signals'],
            ['key' => 'signal_notify_users', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Notify users on signal generation'],
            ['key' => 'signal_log_analysis', 'value' => '1', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Log detailed analysis'],
            ['key' => 'signal_test_mode', 'value' => '0', 'group' => 'signal_generator', 'type' => 'boolean', 'description' => 'Test mode (no execution)'],

            // System Settings
            ['key' => 'system_name', 'value' => 'CryptoBot Pro', 'group' => 'system', 'type' => 'string', 'description' => 'Application name'],
            ['key' => 'system_timezone', 'value' => 'UTC', 'group' => 'system', 'type' => 'string', 'description' => 'System timezone'],
            ['key' => 'system_maintenance_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean', 'description' => 'Maintenance mode status'],
            ['key' => 'system_debug_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean', 'description' => 'Debug mode status'],
            
            // Trading Settings
            ['key' => 'trading_enabled', 'value' => '1', 'group' => 'trading', 'type' => 'boolean', 'description' => 'Global trading status'],
            ['key' => 'trading_max_trades_per_user', 'value' => '20', 'group' => 'trading', 'type' => 'integer', 'description' => 'Max concurrent trades per user'],
            ['key' => 'trading_default_leverage', 'value' => '3', 'group' => 'trading', 'type' => 'integer', 'description' => 'Default leverage'],
            ['key' => 'trading_max_leverage', 'value' => '10', 'group' => 'trading', 'type' => 'integer', 'description' => 'Maximum allowed leverage'],
            ['key' => 'trading_min_position_size', 'value' => '10', 'group' => 'trading', 'type' => 'integer', 'description' => 'Minimum position size in USD'],
            ['key' => 'trading_max_position_size', 'value' => '50000', 'group' => 'trading', 'type' => 'integer', 'description' => 'Maximum position size in USD'],
            ['key' => 'trading_execution_timeout', 'value' => '30', 'group' => 'trading', 'type' => 'integer', 'description' => 'Trade execution timeout in seconds'],
            
            // Monitoring Settings
            ['key' => 'monitor_interval', 'value' => '10', 'group' => 'monitoring', 'type' => 'integer', 'description' => 'Monitor refresh interval in seconds'],
            ['key' => 'monitor_emergency_sl', 'value' => '10', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Emergency stop loss percentage'],
            ['key' => 'monitor_daily_loss_limit', 'value' => '15', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Daily loss limit per user'],
            ['key' => 'monitor_max_drawdown', 'value' => '25', 'group' => 'monitoring', 'type' => 'float', 'description' => 'Maximum drawdown percentage'],
            
            // Notification Settings
            ['key' => 'notifications_enabled', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Notifications enabled'],
            ['key' => 'notifications_email', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Email notifications'],
            ['key' => 'notifications_telegram', 'value' => '0', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Telegram notifications'],
            ['key' => 'notifications_sms', 'value' => '0', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'SMS notifications'],
            
            // Backup Settings
            ['key' => 'backup_enabled', 'value' => '1', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Automatic backups enabled'],
            ['key' => 'backup_frequency', 'value' => 'daily', 'group' => 'backup', 'type' => 'string', 'description' => 'Backup frequency'],
            ['key' => 'backup_retention_days', 'value' => '30', 'group' => 'backup', 'type' => 'integer', 'description' => 'Backup retention days'],
            ['key' => 'backup_include_logs', 'value' => '0', 'group' => 'backup', 'type' => 'boolean', 'description' => 'Include logs in backup'],
            
            // Performance Settings
            ['key' => 'cache_driver', 'value' => 'redis', 'group' => 'performance', 'type' => 'string', 'description' => 'Cache driver'],
            ['key' => 'cache_ttl', 'value' => '3600', 'group' => 'performance', 'type' => 'integer', 'description' => 'Cache TTL in seconds'],
            ['key' => 'queue_driver', 'value' => 'redis', 'group' => 'performance', 'type' => 'string', 'description' => 'Queue driver'],
            ['key' => 'queue_max_workers', 'value' => '10', 'group' => 'performance', 'type' => 'integer', 'description' => 'Maximum queue workers'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}