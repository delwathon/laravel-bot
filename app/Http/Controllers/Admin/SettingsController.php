<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminExchangeAccount;
use App\Models\ExchangeAccount;
use App\Models\Setting;
use App\Models\User;
use App\Services\BybitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function apiKeys()
    {
        $bybitAccount = AdminExchangeAccount::where('exchange', 'bybit')->first();
        
        $adminBalance = 0;
        if ($bybitAccount && $bybitAccount->is_active) {
            try {
                $bybit = new BybitService($bybitAccount->api_key, $bybitAccount->api_secret);
                $adminBalance = $bybit->getBalance();
                
                $bybitAccount->update([
                    'last_synced_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to fetch admin balance: ' . $e->getMessage());
            }
        }
        
        $userApiKeys = ExchangeAccount::with('user')
            ->latest()
            ->paginate(20);

        $totalKeys = ExchangeAccount::count();
        $activeKeys = ExchangeAccount::where('is_active', true)->count();
        $inactiveKeys = ExchangeAccount::where('is_active', false)->count();
        $totalUsers = User::where('is_admin', false)->count();

        $lastSyncedAccount = ExchangeAccount::where('is_active', true)
            ->whereNotNull('last_synced_at')
            ->orderBy('last_synced_at', 'desc')
            ->first();

        $stats = [
            'total_keys' => $totalKeys,
            'active_keys' => $activeKeys,
            'inactive_keys' => $inactiveKeys,
            'total_users' => $totalUsers,
            'active_percentage' => $totalKeys > 0 ? round(($activeKeys / $totalKeys) * 100, 1) : 0,
            'last_check' => $lastSyncedAccount ? $lastSyncedAccount->last_synced_at->diffForHumans() : 'Never',
        ];
        
        return view('admin.settings.api-keys', compact('bybitAccount', 'adminBalance', 'userApiKeys', 'stats'));
    }

    public function storeApiKey(Request $request)
    {
        $validated = $request->validate([
            'exchange' => 'required|in:bybit',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'required|string|max:255',
        ]);

        try {
            $bybit = new BybitService($validated['api_key'], $validated['api_secret']);
            
            if (!$bybit->testConnection()) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Failed to connect to Bybit. Please check your API credentials.']);
            }

            AdminExchangeAccount::updateOrCreate(
                ['exchange' => $validated['exchange']],
                [
                    'api_key' => $validated['api_key'],
                    'api_secret' => $validated['api_secret'],
                    'is_active' => true,
                    'last_synced_at' => now(),
                ]
            );

            return redirect()->route('admin.api-keys.index')
                ->with('success', 'Admin API keys saved and verified successfully!');

        } catch (\Exception $e) {
            Log::error('Admin API key setup failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save API keys: ' . $e->getMessage()]);
        }
    }

    public function deleteApiKey($exchange)
    {
        try {
            $account = AdminExchangeAccount::where('exchange', $exchange)->first();
            
            if ($account) {
                $account->delete();
            }

            return redirect()->route('admin.api-keys.index')
                ->with('success', 'API keys removed successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to remove API keys: ' . $e->getMessage()]);
        }
    }

    public function toggleApiKey($exchange)
    {
        try {
            $account = AdminExchangeAccount::where('exchange', $exchange)->firstOrFail();
            
            $account->update([
                'is_active' => !$account->is_active,
            ]);

            $status = $account->is_active ? 'activated' : 'deactivated';

            return back()->with('success', "API keys {$status} successfully.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to toggle API keys: ' . $e->getMessage()]);
        }
    }

    public function syncAdminBalance($exchange)
    {
        try {
            $account = AdminExchangeAccount::where('exchange', $exchange)->firstOrFail();
            
            $bybit = new BybitService($account->api_key, $account->api_secret);
            $balance = $bybit->getBalance();
            
            $account->update([
                'last_synced_at' => now(),
            ]);
            
            return back()->with('success', 'Admin balance synced successfully! Balance: $' . number_format($balance, 2));
            
        } catch (\Exception $e) {
            Log::error('Failed to sync admin balance: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to sync balance: ' . $e->getMessage()]);
        }
    }
    
    public function analytics()
    {
        $analytics = [
            'total_volume_30d' => 0,
            'avg_win_rate' => 0,
            'total_fees' => 0,
            'sharpe_ratio' => 0,
            'total_trades' => 0,
            'winning_trades' => 0,
            'losing_trades' => 0,
        ];
        
        return view('admin.analytics.index', compact('analytics'));
    }
    
    public function signalGenerator()
    {
        $settings = Setting::getGroup('signal_generator');
        
        // Set defaults if not exists
        if (empty($settings)) {
            $settings = [
                'signal_interval' => 15,
                'signal_top_count' => 5,
                'signal_min_confidence' => 70,
                'signal_expiry' => 30,
                'signal_pairs' => ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'XRPUSDT'],
                'signal_primary_timeframe' => '15m',
                'signal_higher_timeframe' => '1h',
                'signal_patterns' => ['order_block', 'fvg', 'bos', 'choch', 'liquidity_sweep', 'premium_discount'],
                'signal_lookback_periods' => 50,
                'signal_pattern_strength' => 3,
                'signal_risk_reward' => '1:2',
                'signal_max_sl' => 2,
                'signal_position_size' => 5,
                'signal_leverage' => 'Max',
                'signal_exchanges' => ['bybit'],
                'signal_auto_execute' => true,
                'signal_notify_users' => true,
                'signal_log_analysis' => true,
                'signal_test_mode' => false,
            ];
        }
        
        return view('admin.settings.signal-generator', compact('settings'));
    }
    
    public function updateSignalGenerator(Request $request)
    {
        // Log all incoming data for debugging
        Log::info('Signal Generator Update - Incoming Data', $request->all());
        
        $validated = $request->validate([
            // Schedule Configuration
            'interval' => 'required|integer|min:5|max:240',
            'top_signals' => 'required|integer|min:1|max:20',
            'min_confidence' => 'required|integer|min:50|max:95',
            'signal_expiry' => 'required|integer|min:5|max:120',
            
            // Trading Pairs
            'pairs' => 'nullable|array',
            'pairs.*' => 'nullable|string',
            
            // Timeframes
            'primary_timeframe' => 'required|string',
            'higher_timeframe' => 'required|string',
            
            // SMC Pattern Detection
            'patterns' => 'nullable|array',
            'patterns.*' => 'nullable|string',
            
            // Analysis Parameters
            'lookback_periods' => 'required|integer|min:10|max:200',
            'pattern_strength' => 'required|integer|min:1|max:5',
            
            // Risk Management
            'risk_reward' => 'required|string',
            'max_sl' => 'required|numeric|min:0.5|max:10',
            'position_size' => 'required|integer|min:1|max:10',
            'leverage' => 'required|string',
            
            // Exchange Configuration
            'exchanges' => 'nullable|array',
            'exchanges.*' => 'nullable|string',
            
            // Advanced Options (checkboxes)
            'auto_execute' => 'nullable|string',
            'notify_users' => 'nullable|string',
            'log_analysis' => 'nullable|string',
            'test_mode' => 'nullable|string',
        ]);

        // Log validated data
        Log::info('Signal Generator Update - Validated Data', $validated);

        // Store all settings
        Setting::set('signal_interval', $validated['interval'], 'signal_generator', 'integer');
        Setting::set('signal_top_count', $validated['top_signals'], 'signal_generator', 'integer');
        Setting::set('signal_min_confidence', $validated['min_confidence'], 'signal_generator', 'integer');
        Setting::set('signal_expiry', $validated['signal_expiry'], 'signal_generator', 'integer');
        
        // Trading Pairs
        Setting::set('signal_pairs', $validated['pairs'] ?? [], 'signal_generator', 'array');
        
        // Timeframes
        Setting::set('signal_primary_timeframe', $validated['primary_timeframe'], 'signal_generator', 'string');
        Setting::set('signal_higher_timeframe', $validated['higher_timeframe'], 'signal_generator', 'string');
        // Also save as secondary_timeframe for backward compatibility
        Setting::set('signal_secondary_timeframe', $validated['higher_timeframe'], 'signal_generator', 'string');
        
        // SMC Patterns
        Setting::set('signal_patterns', $validated['patterns'] ?? [], 'signal_generator', 'array');
        
        // Analysis Parameters
        Setting::set('signal_lookback_periods', $validated['lookback_periods'], 'signal_generator', 'integer');
        Setting::set('signal_pattern_strength', $validated['pattern_strength'], 'signal_generator', 'integer');
        
        // Risk Management
        Setting::set('signal_risk_reward', $validated['risk_reward'], 'signal_generator', 'string');
        Setting::set('signal_max_sl', $validated['max_sl'], 'signal_generator', 'float');
        Setting::set('signal_position_size', $validated['position_size'], 'signal_generator', 'integer');
        Setting::set('signal_leverage', $validated['leverage'], 'signal_generator', 'string');
        
        // Exchange Configuration
        Setting::set('signal_exchanges', $validated['exchanges'] ?? [], 'signal_generator', 'array');
        
        // Advanced Options (convert checkbox "on" to boolean true)
        Setting::set('signal_auto_execute', $request->has('auto_execute'), 'signal_generator', 'boolean');
        Setting::set('signal_notify_users', $request->has('notify_users'), 'signal_generator', 'boolean');
        Setting::set('signal_log_analysis', $request->has('log_analysis'), 'signal_generator', 'boolean');
        Setting::set('signal_test_mode', $request->has('test_mode'), 'signal_generator', 'boolean');

        Log::info('Signal Generator Settings Updated Successfully');

        return redirect()->route('admin.settings.signal-generator')
            ->with('success', 'Signal generator settings updated successfully!');
    }

    public function system()
    {
        $systemSettings = Setting::getGroup('system');
        $tradingSettings = Setting::getGroup('trading');
        $monitoringSettings = Setting::getGroup('monitoring');
        $notificationSettings = Setting::getGroup('notifications');
        $backupSettings = Setting::getGroup('backup');
        $performanceSettings = Setting::getGroup('performance');
        $apiSettings = Setting::getGroup('api');
        
        // Merge all settings
        $settings = array_merge(
            $systemSettings ?: [],
            $tradingSettings ?: [],
            $monitoringSettings ?: [],
            $notificationSettings ?: [],
            $backupSettings ?: [],
            $performanceSettings ?: [],
            $apiSettings ?: []
        );
        
        // Set defaults if empty
        if (empty($settings)) {
            $settings = [
                'system_name' => 'CryptoBot Pro',
                'system_timezone' => 'UTC',
                'system_maintenance_mode' => false,
                'system_debug_mode' => false,
                'system_allow_registration' => true,
                'system_email_verification' => true,
                'system_session_timeout' => 120,
                
                'trading_enabled' => true,
                'trading_max_trades_per_user' => 20,
                'trading_default_leverage' => 3,
                'trading_max_leverage' => 10,
                'trading_min_position_size' => 10,
                'trading_max_position_size' => 50000,
                'trading_execution_timeout' => 30,
                
                'monitor_interval' => 10,
                'monitor_emergency_sl' => 10,
                'monitor_daily_loss_limit' => 15,
                'monitor_max_drawdown' => 25,
                'monitor_auto_stop_loss' => true,
                'monitor_auto_take_profit' => true,
                'monitor_trailing_stop' => false,
                'monitor_circuit_breaker' => true,
                
                'api_timeout' => 15,
                'api_max_retries' => 3,
                'api_bybit_rate_limit' => 100,
                
                'notifications_enabled' => true,
                'notifications_email' => true,
                'notifications_telegram' => false,
                'notifications_sms' => false,
                'notifications_trade_execution' => true,
                'notifications_tp_sl' => true,
                'notifications_signals' => true,
                'notifications_errors' => true,
                'notifications_high_risk' => true,
                'notifications_daily_summary' => true,
                'notifications_admin_email' => 'admin@cryptobot.com',
                'notifications_smtp_server' => 'smtp.gmail.com',
                
                'backup_enabled' => true,
                'backup_auto_backup' => true,
                'backup_frequency' => 'daily',
                'backup_retention_days' => 30,
                'backup_data_retention' => 365,
                'backup_log_retention' => 90,
                'backup_include_logs' => false,
                
                'cache_driver' => 'redis',
                'cache_ttl' => 3600,
                'queue_driver' => 'redis',
                'queue_max_workers' => 10,
            ];
        }
        
        return view('admin.settings.system', compact('settings'));
    }

    public function updateSystem(Request $request)
    {
        // Log all incoming data for debugging
        Log::info('System Settings Update - Incoming Data', $request->all());
        
        $validated = $request->validate([
            // System Settings
            'system_name' => 'required|string|max:255',
            'timezone' => 'required|string',
            'maintenance_mode' => 'required|in:0,1',
            'debug_mode' => 'required|in:0,1',
            'allow_registration' => 'nullable|in:0,1',
            'email_verification' => 'nullable|in:0,1',
            'session_timeout' => 'nullable|integer|min:30|max:1440',
            
            // Trading Settings
            'trading_enabled' => 'required|in:0,1',
            'max_trades_per_user' => 'required|integer|min:1|max:100',
            'default_leverage' => 'required|integer|min:1|max:100',
            'max_leverage' => 'required|integer|min:1|max:100',
            'min_position_size' => 'required|numeric|min:1',
            'max_position_size' => 'required|numeric|min:100',
            'trade_timeout' => 'required|integer|min:5|max:120',
            
            // Monitoring Settings
            'monitor_interval' => 'required|integer|min:5|max:60',
            'emergency_sl' => 'required|numeric|min:5|max:50',
            'daily_loss_limit' => 'required|numeric|min:5|max:50',
            'max_drawdown' => 'nullable|numeric|min:10|max:50',
            'max_drawdown_alert' => 'nullable|numeric|min:10|max:50',
            
            // Risk Management Features (checkboxes)
            'auto_stop_loss' => 'nullable|string',
            'auto_take_profit' => 'nullable|string',
            'trailing_stop' => 'nullable|string',
            'circuit_breaker' => 'nullable|string',
            
            // API Settings
            'api_timeout' => 'nullable|integer|min:5|max:60',
            'max_retries' => 'nullable|integer|min:1|max:10',
            'bybit_rate_limit' => 'nullable|integer|min:10|max:200',
            
            // Notification Settings (checkboxes)
            'notify_trade_execution' => 'nullable|string',
            'notify_tp_sl' => 'nullable|string',
            'notify_signals' => 'nullable|string',
            'notify_errors' => 'nullable|string',
            'notify_high_risk' => 'nullable|string',
            'daily_summary' => 'nullable|string',
            
            // Email Settings
            'admin_email' => 'nullable|email',
            'smtp_server' => 'nullable|string',
            
            // Backup Settings
            'auto_backup' => 'nullable|in:0,1',
            'backup_frequency' => 'required|string',
            'backup_retention' => 'required|integer|min:7|max:90',
            'data_retention' => 'nullable|integer|min:30|max:3650',
            'log_retention' => 'nullable|integer|min:7|max:365',
            
            // Performance Settings
            'cache_driver' => 'required|string',
            'cache_ttl' => 'required|integer|min:60|max:86400',
            'queue_driver' => 'required|string',
            'max_workers' => 'required|integer|min:1|max:50',
        ]);

        // Log validated data
        Log::info('System Settings Update - Validated Data', $validated);

        // System settings
        Setting::set('system_name', $validated['system_name'], 'system', 'string');
        Setting::set('system_timezone', $validated['timezone'], 'system', 'string');
        Setting::set('system_maintenance_mode', (bool)$validated['maintenance_mode'], 'system', 'boolean');
        Setting::set('system_debug_mode', (bool)$validated['debug_mode'], 'system', 'boolean');
        Setting::set('system_allow_registration', (bool)($validated['allow_registration'] ?? 1), 'system', 'boolean');
        Setting::set('system_email_verification', (bool)($validated['email_verification'] ?? 1), 'system', 'boolean');
        Setting::set('system_session_timeout', $validated['session_timeout'] ?? 120, 'system', 'integer');
        
        // Trading settings
        Setting::set('trading_enabled', (bool)$validated['trading_enabled'], 'trading', 'boolean');
        Setting::set('trading_max_trades_per_user', $validated['max_trades_per_user'], 'trading', 'integer');
        Setting::set('trading_default_leverage', $validated['default_leverage'], 'trading', 'integer');
        Setting::set('trading_max_leverage', $validated['max_leverage'], 'trading', 'integer');
        Setting::set('trading_min_position_size', $validated['min_position_size'], 'trading', 'integer');
        Setting::set('trading_max_position_size', $validated['max_position_size'], 'trading', 'integer');
        Setting::set('trading_execution_timeout', $validated['trade_timeout'], 'trading', 'integer');
        
        // Monitoring settings
        Setting::set('monitor_interval', $validated['monitor_interval'], 'monitoring', 'integer');
        Setting::set('monitor_emergency_sl', $validated['emergency_sl'], 'monitoring', 'float');
        Setting::set('monitor_daily_loss_limit', $validated['daily_loss_limit'], 'monitoring', 'float');
        Setting::set('monitor_max_drawdown', $validated['max_drawdown'] ?? $validated['max_drawdown_alert'] ?? 25, 'monitoring', 'float');
        
        // Risk Management Features
        Setting::set('monitor_auto_stop_loss', $request->has('auto_stop_loss'), 'monitoring', 'boolean');
        Setting::set('monitor_auto_take_profit', $request->has('auto_take_profit'), 'monitoring', 'boolean');
        Setting::set('monitor_trailing_stop', $request->has('trailing_stop'), 'monitoring', 'boolean');
        Setting::set('monitor_circuit_breaker', $request->has('circuit_breaker'), 'monitoring', 'boolean');
        
        // API Settings
        Setting::set('api_timeout', $validated['api_timeout'] ?? 15, 'api', 'integer');
        Setting::set('api_max_retries', $validated['max_retries'] ?? 3, 'api', 'integer');
        Setting::set('api_bybit_rate_limit', $validated['bybit_rate_limit'] ?? 100, 'api', 'integer');
        
        // Notification Preferences
        Setting::set('notifications_trade_execution', $request->has('notify_trade_execution'), 'notifications', 'boolean');
        Setting::set('notifications_tp_sl', $request->has('notify_tp_sl'), 'notifications', 'boolean');
        Setting::set('notifications_signals', $request->has('notify_signals'), 'notifications', 'boolean');
        Setting::set('notifications_errors', $request->has('notify_errors'), 'notifications', 'boolean');
        Setting::set('notifications_high_risk', $request->has('notify_high_risk'), 'notifications', 'boolean');
        Setting::set('notifications_daily_summary', $request->has('daily_summary'), 'notifications', 'boolean');
        
        // Email Settings
        Setting::set('notifications_admin_email', $validated['admin_email'] ?? 'admin@cryptobot.com', 'notifications', 'string');
        Setting::set('notifications_smtp_server', $validated['smtp_server'] ?? 'smtp.gmail.com', 'notifications', 'string');
        
        // Backup settings
        Setting::set('backup_auto_backup', (bool)($validated['auto_backup'] ?? 1), 'backup', 'boolean');
        Setting::set('backup_frequency', $validated['backup_frequency'], 'backup', 'string');
        Setting::set('backup_retention_days', $validated['backup_retention'], 'backup', 'integer');
        Setting::set('backup_data_retention', $validated['data_retention'] ?? 365, 'backup', 'integer');
        Setting::set('backup_log_retention', $validated['log_retention'] ?? 90, 'backup', 'integer');
        
        // Performance settings
        Setting::set('cache_driver', $validated['cache_driver'], 'performance', 'string');
        Setting::set('cache_ttl', $validated['cache_ttl'], 'performance', 'integer');
        Setting::set('queue_driver', $validated['queue_driver'], 'performance', 'string');
        Setting::set('queue_max_workers', $validated['max_workers'], 'performance', 'integer');

        Log::info('System Settings Updated Successfully');

        return redirect()->route('admin.settings.system')
            ->with('success', 'System settings updated successfully!');
    }
}