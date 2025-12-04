<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $bybitAccount = ExchangeAccount::getBybitAccount();
        
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
            ->where('is_admin', false)
            ->latest()
            ->paginate(20);

        $totalKeys = ExchangeAccount::where('is_admin', false)->count();
        $activeKeys = ExchangeAccount::where('is_active', true)->where('is_admin', false)->count();
        $inactiveKeys = ExchangeAccount::where('is_active', false)->where('is_admin', false)->count();
        $totalUsers = User::where('is_admin', false)->where('is_admin', false)->count();

        $lastSyncedAccount = ExchangeAccount::where('is_active', true)
            ->where('is_admin', false)
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

            $balance = $bybit->getBalance();
            
            $admin = User::where('is_admin', true)->first();
    
            ExchangeAccount::updateOrCreate(
                ['user_id' => $admin->id, 'exchange' => $validated['exchange']],
                [
                    'api_key' => $validated['api_key'],
                    'api_secret' => $validated['api_secret'],
                    'is_active' => true,
                    'is_admin' => true,
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
            $account = ExchangeAccount::where('exchange', $exchange)->where('is_admin', true)->first();
            
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
            $account = ExchangeAccount::where('exchange', $exchange)->where('is_admin', true)->firstOrFail();
            
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
            $account = ExchangeAccount::where('exchange', $exchange)->where('is_admin', true)->firstOrFail();
            
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
        
        // Load defaults if not set
        if (empty($settings)) {
            $settings = [
                // Schedule Configuration
                'signal_interval' => 30,
                'signal_top_count' => 10,
                'signal_min_confidence' => 70,
                'signal_expiry' => 30,
                'signal_auto_execute' => true,
                'signal_auto_execute_count' => 3,
                
                // Trading Pairs Configuration
                'signal_use_dynamic_pairs' => true,
                'signal_min_volume' => 5000000,
                'signal_max_pairs' => 50,
                'signal_pairs' => ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'TRXUSDT', 'MATICUSDT', 'DOTUSDT', 'LTCUSDT', 'LINKUSDT', 'AVAXUSDT', 'UNIUSDT', 'ATOMUSDT', 'XLMUSDT', 'FILUSDT', 'ETCUSDT', 'NEARUSDT', 'APTUSDT', 'ICPUSDT', 'ARBUSDT', 'OPUSDT', 'LDOUSDT', 'INJUSDT', 'STXUSDT', 'TIAUSDT', 'SUIUSDT', 'SEIUSDT', 'RENDERUSDT', 'RNDRUSDT', 'ALGOUSDT', 'VETUSDT', 'AAVEUSDT', 'SUSHIUSDT', 'PEPEUSDT', 'WIFUSDT', 'BONKUSDT', 'FLOKIUSDT', 'SHIBUSDT', 'FTMUSDT', 'SANDUSDT', 'MANAUSDT', 'AXSUSDT', 'GALAUSDT', 'ENJUSDT', 'CHZUSDT', 'GMTUSDT', 'APEUSDT', 'BLURUSDT'],
                
                // Timeframes
                'signal_primary_timeframe' => '240',
                'signal_higher_timeframe' => 'D',
                
                // SMC Pattern Detection
                'signal_patterns' => ['order_block', 'fvg', 'bos', 'choch', 'liquidity_sweep', 'premium_discount'],
                
                // Analysis Parameters
                'signal_lookback_periods' => 50,
                'signal_pattern_strength' => 3,
                
                // Risk Management
                'signal_risk_reward' => '1:2',
                'signal_max_sl' => 5.0,
                'signal_position_size' => 5.0,
                'signal_leverage' => 'Max',
                
                // Order Type
                'signal_order_type' => 'Market',
                
                // Exchange Configuration
                'signal_exchanges' => ['bybit'],
                
                // Advanced Options
                'signal_notify_users' => true,
                'signal_log_analysis' => true,
                'signal_test_mode' => false,
                
                // Conflict Management
                'signal_stale_order_hours' => 24,
                'signal_skip_duplicate_positions' => true,
                'signal_cancel_opposite_pending' => true,
                'signal_cancel_stale_pending' => true,
                'signal_close_opposite_positions' => false,
            ];
        }
        
        return view('admin.settings.signal-generator', compact('settings'));
    }

    public function updateSignalGenerator(Request $request)
    {
        $validated = $request->validate([
            // Schedule Configuration
            'signal_interval' => 'required|integer|min:5|max:1440',
            'signal_top_count' => 'required|integer|min:1|max:50',
            'signal_min_confidence' => 'required|integer|min:50|max:95',
            'signal_expiry' => 'required|integer|min:5|max:180',
            'signal_auto_execute' => 'nullable|string',
            'signal_auto_execute_count' => 'required|integer|min:1|max:20',
            
            // Trading Pairs Configuration
            'signal_use_dynamic_pairs' => 'nullable|string',
            'signal_min_volume' => 'required|integer|min:100000',
            'signal_max_pairs' => 'required|integer|min:5|max:200',
            'signal_pairs' => 'nullable|array',
            
            // Timeframes
            'signal_primary_timeframe' => 'required|string',
            'signal_higher_timeframe' => 'required|string',
            
            // SMC Pattern Detection
            'signal_patterns' => 'nullable|array',
            
            // Analysis Parameters
            'signal_lookback_periods' => 'required|integer|min:20|max:500',
            'signal_pattern_strength' => 'required|integer|min:1|max:5',
            
            // Risk Management
            'signal_risk_reward' => 'required|string',
            'signal_max_sl' => 'required|numeric|min:0.5|max:10',
            'signal_position_size' => 'required|numeric|min:1|max:100',
            'signal_leverage' => 'required|string',
            
            // Order Type
            'signal_order_type' => 'required|in:Market,Limit',
            
            // Exchange Configuration
            'signal_exchanges' => 'nullable|array',
            
            // Advanced Options
            'signal_notify_users' => 'nullable|string',
            'signal_log_analysis' => 'nullable|string',
            'signal_test_mode' => 'nullable|string',
            
            // Conflict Management
            'signal_stale_order_hours' => 'required|integer|min:1|max:168',
            'signal_skip_duplicate_positions' => 'nullable|string',
            'signal_cancel_opposite_pending' => 'nullable|string',
            'signal_cancel_stale_pending' => 'nullable|string',
            'signal_close_opposite_positions' => 'nullable|string',
        ]);

        // Schedule Configuration
        Setting::set('signal_interval', $validated['signal_interval'], 'signal_generator', 'integer');
        Setting::set('signal_top_count', $validated['signal_top_count'], 'signal_generator', 'integer');
        Setting::set('signal_min_confidence', $validated['signal_min_confidence'], 'signal_generator', 'integer');
        Setting::set('signal_expiry', $validated['signal_expiry'], 'signal_generator', 'integer');
        Setting::set('signal_auto_execute', $request->has('signal_auto_execute'), 'signal_generator', 'boolean');
        Setting::set('signal_auto_execute_count', $validated['signal_auto_execute_count'], 'signal_generator', 'integer');
        
        // Trading Pairs Configuration
        Setting::set('signal_use_dynamic_pairs', $request->has('signal_use_dynamic_pairs'), 'signal_generator', 'boolean');
        Setting::set('signal_min_volume', $validated['signal_min_volume'], 'signal_generator', 'integer');
        Setting::set('signal_max_pairs', $validated['signal_max_pairs'], 'signal_generator', 'integer');
        Setting::set('signal_pairs', $validated['signal_pairs'] ?? [], 'signal_generator', 'array');
        
        // Timeframes
        Setting::set('signal_primary_timeframe', $validated['signal_primary_timeframe'], 'signal_generator', 'string');
        Setting::set('signal_higher_timeframe', $validated['signal_higher_timeframe'], 'signal_generator', 'string');
        
        // SMC Pattern Detection
        Setting::set('signal_patterns', $validated['signal_patterns'] ?? [], 'signal_generator', 'array');
        
        // Analysis Parameters
        Setting::set('signal_lookback_periods', $validated['signal_lookback_periods'], 'signal_generator', 'integer');
        Setting::set('signal_pattern_strength', $validated['signal_pattern_strength'], 'signal_generator', 'integer');
        
        // Risk Management
        Setting::set('signal_risk_reward', $validated['signal_risk_reward'], 'signal_generator', 'string');
        Setting::set('signal_max_sl', $validated['signal_max_sl'], 'signal_generator', 'float');
        Setting::set('signal_position_size', $validated['signal_position_size'], 'signal_generator', 'float');
        Setting::set('signal_leverage', $validated['signal_leverage'], 'signal_generator', 'string');
        
        // Order Type
        Setting::set('signal_order_type', $validated['signal_order_type'], 'signal_generator', 'string');
        
        // Exchange Configuration
        Setting::set('signal_exchanges', $validated['signal_exchanges'] ?? ['bybit'], 'signal_generator', 'array');
        
        // Advanced Options
        Setting::set('signal_notify_users', $request->has('signal_notify_users'), 'signal_generator', 'boolean');
        Setting::set('signal_log_analysis', $request->has('signal_log_analysis'), 'signal_generator', 'boolean');
        Setting::set('signal_test_mode', $request->has('signal_test_mode'), 'signal_generator', 'boolean');
        
        // Conflict Management
        Setting::set('signal_stale_order_hours', $validated['signal_stale_order_hours'], 'signal_generator', 'integer');
        Setting::set('signal_skip_duplicate_positions', $request->has('signal_skip_duplicate_positions'), 'signal_generator', 'boolean');
        Setting::set('signal_cancel_opposite_pending', $request->has('signal_cancel_opposite_pending'), 'signal_generator', 'boolean');
        Setting::set('signal_cancel_stale_pending', $request->has('signal_cancel_stale_pending'), 'signal_generator', 'boolean');
        Setting::set('signal_close_opposite_positions', $request->has('signal_close_opposite_positions'), 'signal_generator', 'boolean');

        return redirect()->route('admin.settings.signal-generator')
            ->with('success', 'Signal generator settings updated successfully!');
    }

    public function system()
    {
        // Load all system settings and convert to key-value array
        $settingsCollection = Setting::all()->keyBy('key');
        
        // Convert collection of Setting models to simple key-value array
        $settings = [];
        foreach ($settingsCollection as $key => $setting) {
            $settings[$key] = $setting->value;
        }
        
        // If no settings exist, provide comprehensive defaults
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
                'trading_max_trades_per_user' => 10,
                'trading_default_leverage' => 10,
                'trading_max_leverage' => 100,
                'trading_min_position_size' => 10,
                'trading_max_position_size' => 10000,
                'trading_execution_timeout' => 30,
                
                'monitor_interval' => 5,
                'monitor_emergency_sl' => 10,
                'monitor_daily_loss_limit' => 15,
                'monitor_max_drawdown' => 25,
                'monitor_auto_stop_loss' => true,
                'monitor_auto_take_profit' => true,
                'monitor_trailing_stop' => false,
                'monitor_circuit_breaker' => true,
                'enable_profit_milestones' => true,
                
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
            'system_timezone' => 'required|string',
            'system_maintenance_mode' => 'required|in:0,1',
            'system_debug_mode' => 'required|in:0,1',
            'system_allow_registration' => 'nullable|in:0,1',
            'system_email_verification' => 'nullable|in:0,1',
            'system_session_timeout' => 'nullable|integer|min:30|max:1440',
            
            // Trading Settings
            'trading_enabled' => 'required|in:0,1',
            'trading_max_trades_per_user' => 'required|integer|min:1|max:100',
            'trading_default_leverage' => 'required|integer|min:1|max:100',
            'trading_max_leverage' => 'required|integer|min:1|max:100',
            'trading_min_position_size' => 'required|numeric|min:1',
            'trading_max_position_size' => 'required|numeric|min:100',
            'trading_execution_timeout' => 'required|integer|min:5|max:120',
            
            // Monitoring Settings
            'monitor_interval' => 'required|integer|min:5|max:60',
            'monitor_emergency_sl' => 'required|numeric|min:5|max:50',
            'monitor_daily_loss_limit' => 'required|numeric|min:5|max:50',
            'monitor_max_drawdown' => 'nullable|numeric|min:10|max:50',
            
            // Risk Management Features (checkboxes)
            'monitor_auto_stop_loss' => 'nullable|string',
            'monitor_auto_take_profit' => 'nullable|string',
            'monitor_trailing_stop' => 'nullable|string',
            'monitor_circuit_breaker' => 'nullable|string',
            'enable_profit_milestones' => 'nullable|string',
            
            // API Settings
            'api_timeout' => 'nullable|integer|min:5|max:60',
            'api_max_retries' => 'nullable|integer|min:1|max:10',
            'api_bybit_rate_limit' => 'nullable|integer|min:10|max:200',
            
            // Notification Settings (checkboxes)
            'notifications_enabled' => 'nullable|string',
            'notifications_email' => 'nullable|string',
            'notifications_telegram' => 'nullable|string',
            'notifications_sms' => 'nullable|string',
            'notifications_trade_execution' => 'nullable|string',
            'notifications_tp_sl' => 'nullable|string',
            'notifications_signals' => 'nullable|string',
            'notifications_errors' => 'nullable|string',
            'notifications_high_risk' => 'nullable|string',
            'notifications_daily_summary' => 'nullable|string',
            
            // Email Settings
            'notifications_admin_email' => 'nullable|email',
            'notifications_smtp_server' => 'nullable|string',
            
            // Backup Settings
            'backup_auto_backup' => 'nullable|in:0,1',
            'backup_frequency' => 'required|string',
            'backup_retention_days' => 'required|integer|min:7|max:90',
            'backup_data_retention' => 'nullable|integer|min:30|max:3650',
            'backup_log_retention' => 'nullable|integer|min:7|max:365',
            'backup_include_logs' => 'nullable|string',
            
            // Performance Settings
            'cache_driver' => 'required|string',
            'cache_ttl' => 'required|integer|min:60|max:86400',
            'queue_driver' => 'required|string',
            'queue_max_workers' => 'required|integer|min:1|max:50',
        ]);

        // Log validated data
        Log::info('System Settings Update - Validated Data', $validated);

        // System settings
        Setting::set('system_name', $validated['system_name'], 'system', 'string');
        Setting::set('system_timezone', $validated['system_timezone'], 'system', 'string');
        Setting::set('system_maintenance_mode', (bool)$validated['system_maintenance_mode'], 'system', 'boolean');
        Setting::set('system_debug_mode', (bool)$validated['system_debug_mode'], 'system', 'boolean');
        Setting::set('system_allow_registration', (bool)($validated['system_allow_registration'] ?? 1), 'system', 'boolean');
        Setting::set('system_email_verification', (bool)($validated['system_email_verification'] ?? 1), 'system', 'boolean');
        Setting::set('system_session_timeout', $validated['system_session_timeout'] ?? 120, 'system', 'integer');
        
        // Trading settings
        Setting::set('trading_enabled', (bool)$validated['trading_enabled'], 'trading', 'boolean');
        Setting::set('trading_max_trades_per_user', $validated['trading_max_trades_per_user'], 'trading', 'integer');
        Setting::set('trading_default_leverage', $validated['trading_default_leverage'], 'trading', 'integer');
        Setting::set('trading_max_leverage', $validated['trading_max_leverage'], 'trading', 'integer');
        Setting::set('trading_min_position_size', $validated['trading_min_position_size'], 'trading', 'integer');
        Setting::set('trading_max_position_size', $validated['trading_max_position_size'], 'trading', 'integer');
        Setting::set('trading_execution_timeout', $validated['trading_execution_timeout'], 'trading', 'integer');
        
        // Monitoring settings
        Setting::set('monitor_interval', $validated['monitor_interval'], 'monitoring', 'integer');
        Setting::set('monitor_emergency_sl', $validated['monitor_emergency_sl'], 'monitoring', 'float');
        Setting::set('monitor_daily_loss_limit', $validated['monitor_daily_loss_limit'], 'monitoring', 'float');
        Setting::set('monitor_max_drawdown', $validated['monitor_max_drawdown'] ?? 25, 'monitoring', 'float');
        
        // Risk Management Features
        Setting::set('monitor_auto_stop_loss', $request->has('monitor_auto_stop_loss'), 'monitoring', 'boolean');
        Setting::set('monitor_auto_take_profit', $request->has('monitor_auto_take_profit'), 'monitoring', 'boolean');
        Setting::set('monitor_trailing_stop', $request->has('monitor_trailing_stop'), 'monitoring', 'boolean');
        Setting::set('monitor_circuit_breaker', $request->has('monitor_circuit_breaker'), 'monitoring', 'boolean');
        Setting::set('enable_profit_milestones', $request->has('enable_profit_milestones'), 'monitoring', 'boolean');
        
        // API Settings
        Setting::set('api_timeout', $validated['api_timeout'] ?? 15, 'api', 'integer');
        Setting::set('api_max_retries', $validated['api_max_retries'] ?? 3, 'api', 'integer');
        Setting::set('api_bybit_rate_limit', $validated['api_bybit_rate_limit'] ?? 100, 'api', 'integer');
        
        // Notification Settings
        Setting::set('notifications_enabled', $request->has('notifications_enabled'), 'notifications', 'boolean');
        Setting::set('notifications_email', $request->has('notifications_email'), 'notifications', 'boolean');
        Setting::set('notifications_telegram', $request->has('notifications_telegram'), 'notifications', 'boolean');
        Setting::set('notifications_sms', $request->has('notifications_sms'), 'notifications', 'boolean');
        Setting::set('notifications_trade_execution', $request->has('notifications_trade_execution'), 'notifications', 'boolean');
        Setting::set('notifications_tp_sl', $request->has('notifications_tp_sl'), 'notifications', 'boolean');
        Setting::set('notifications_signals', $request->has('notifications_signals'), 'notifications', 'boolean');
        Setting::set('notifications_errors', $request->has('notifications_errors'), 'notifications', 'boolean');
        Setting::set('notifications_high_risk', $request->has('notifications_high_risk'), 'notifications', 'boolean');
        Setting::set('notifications_daily_summary', $request->has('notifications_daily_summary'), 'notifications', 'boolean');
        
        // Email Settings
        Setting::set('notifications_admin_email', $validated['notifications_admin_email'] ?? 'admin@cryptobot.com', 'notifications', 'string');
        Setting::set('notifications_smtp_server', $validated['notifications_smtp_server'] ?? 'smtp.gmail.com', 'notifications', 'string');
        
        // Backup settings
        Setting::set('backup_auto_backup', (bool)($validated['backup_auto_backup'] ?? 1), 'backup', 'boolean');
        Setting::set('backup_frequency', $validated['backup_frequency'], 'backup', 'string');
        Setting::set('backup_retention_days', $validated['backup_retention_days'], 'backup', 'integer');
        Setting::set('backup_data_retention', $validated['backup_data_retention'] ?? 365, 'backup', 'integer');
        Setting::set('backup_log_retention', $validated['backup_log_retention'] ?? 90, 'backup', 'integer');
        Setting::set('backup_include_logs', $request->has('backup_include_logs'), 'backup', 'boolean');
        
        // Performance settings
        Setting::set('cache_driver', $validated['cache_driver'], 'performance', 'string');
        Setting::set('cache_ttl', $validated['cache_ttl'], 'performance', 'integer');
        Setting::set('queue_driver', $validated['queue_driver'], 'performance', 'string');
        Setting::set('queue_max_workers', $validated['queue_max_workers'], 'performance', 'integer');

        Log::info('System Settings Updated Successfully');

        return redirect()->route('admin.settings.system')
            ->with('success', 'System settings updated successfully!');
    }
}