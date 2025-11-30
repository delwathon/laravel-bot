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
        
        $userApiKeys = ExchangeAccount::with('user')
            ->latest()
            ->paginate(20);

        $totalKeys = ExchangeAccount::count();
        $activeKeys = ExchangeAccount::where('is_active', true)->count();
        $inactiveKeys = ExchangeAccount::where('is_active', false)->count();
        $totalUsers = User::where('is_admin', false)->count();

        $stats = [
            'total_keys' => $totalKeys,
            'active_keys' => $activeKeys,
            'inactive_keys' => $inactiveKeys,
            'total_users' => $totalUsers,
            'active_percentage' => $totalKeys > 0 ? round(($activeKeys / $totalKeys) * 100, 1) : 0,
            'last_check' => $activeKeys > 0 ? ExchangeAccount::where('is_active', true)->max('last_synced_at')?->diffForHumans() ?? 'Never' : 'Never',
        ];
        
        return view('admin.settings.api-keys', compact('bybitAccount', 'userApiKeys', 'stats'));
    }

    public function exchangeConfig()
    {
        // Admin's own exchange configuration
        $bybitAccount = AdminExchangeAccount::where('exchange', 'bybit')->first();
        
        return view('admin.settings.exchange-config', compact('bybitAccount'));
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
                'signal_primary_timeframe' => '15',
                'signal_secondary_timeframe' => '60',
                'signal_lookback_periods' => 200,
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
        $validated = $request->validate([
            'interval' => 'required|integer|min:5|max:240',
            'top_signals' => 'required|integer|min:1|max:20',
            'min_confidence' => 'required|integer|min:50|max:95',
            'signal_expiry' => 'required|integer|min:5|max:120',
            'pairs' => 'nullable|array',
            'primary_timeframe' => 'required|string',
            'secondary_timeframe' => 'nullable|string',
            'lookback_periods' => 'required|integer|min:50|max:500',
            'pattern_strength' => 'required|integer|min:1|max:5',
            'risk_reward' => 'required|string',
            'max_sl' => 'required|numeric|min:0.5|max:10',
            'position_size' => 'required|integer|min:1|max:10',
            'leverage' => 'required|string',
            'exchanges' => 'nullable|array',
            'auto_execute' => 'nullable|boolean',
            'notify_users' => 'nullable|boolean',
            'log_analysis' => 'nullable|boolean',
            'test_mode' => 'nullable|boolean',
        ]);

        // Store all settings
        Setting::set('signal_interval', $validated['interval'], 'signal_generator', 'integer');
        Setting::set('signal_top_count', $validated['top_signals'], 'signal_generator', 'integer');
        Setting::set('signal_min_confidence', $validated['min_confidence'], 'signal_generator', 'integer');
        Setting::set('signal_expiry', $validated['signal_expiry'], 'signal_generator', 'integer');
        Setting::set('signal_pairs', $validated['pairs'] ?? [], 'signal_generator', 'array');
        Setting::set('signal_primary_timeframe', $validated['primary_timeframe'], 'signal_generator', 'string');
        Setting::set('signal_secondary_timeframe', $validated['secondary_timeframe'] ?? '60', 'signal_generator', 'string');
        Setting::set('signal_lookback_periods', $validated['lookback_periods'], 'signal_generator', 'integer');
        Setting::set('signal_pattern_strength', $validated['pattern_strength'], 'signal_generator', 'integer');
        Setting::set('signal_risk_reward', $validated['risk_reward'], 'signal_generator', 'string');
        Setting::set('signal_max_sl', $validated['max_sl'], 'signal_generator', 'float');
        Setting::set('signal_position_size', $validated['position_size'], 'signal_generator', 'integer');
        Setting::set('signal_leverage', $validated['leverage'], 'signal_generator', 'string');
        Setting::set('signal_exchanges', $validated['exchanges'] ?? [], 'signal_generator', 'array');
        Setting::set('signal_auto_execute', $request->has('auto_execute'), 'signal_generator', 'boolean');
        Setting::set('signal_notify_users', $request->has('notify_users'), 'signal_generator', 'boolean');
        Setting::set('signal_log_analysis', $request->has('log_analysis'), 'signal_generator', 'boolean');
        Setting::set('signal_test_mode', $request->has('test_mode'), 'signal_generator', 'boolean');

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
        
        // Merge all settings
        $settings = array_merge(
            $systemSettings ?: [],
            $tradingSettings ?: [],
            $monitoringSettings ?: [],
            $notificationSettings ?: [],
            $backupSettings ?: [],
            $performanceSettings ?: []
        );
        
        // Set defaults if empty
        if (empty($settings)) {
            $settings = [
                'system_name' => 'CryptoBot Pro',
                'system_timezone' => 'UTC',
                'system_maintenance_mode' => false,
                'system_debug_mode' => false,
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
                'notifications_enabled' => true,
                'notifications_email' => true,
                'notifications_telegram' => false,
                'notifications_sms' => false,
                'backup_enabled' => true,
                'backup_frequency' => 'daily',
                'backup_retention_days' => 30,
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
        $validated = $request->validate([
            'system_name' => 'required|string|max:255',
            'timezone' => 'required|string',
            'maintenance_mode' => 'required|boolean',
            'debug_mode' => 'required|boolean',
            'trading_enabled' => 'required|boolean',
            'max_trades_per_user' => 'required|integer|min:1|max:100',
            'default_leverage' => 'required|integer|min:1|max:100',
            'max_leverage' => 'required|integer|min:1|max:100',
            'min_position_size' => 'required|numeric|min:1',
            'max_position_size' => 'required|numeric|min:100',
            'trade_timeout' => 'required|integer|min:5|max:120',
            'monitor_interval' => 'required|integer|min:5|max:60',
            'emergency_sl' => 'required|numeric|min:5|max:50',
            'daily_loss_limit' => 'required|numeric|min:5|max:50',
            'max_drawdown' => 'required|numeric|min:10|max:50',
            'notifications_enabled' => 'nullable|boolean',
            'email_notifications' => 'nullable|boolean',
            'telegram_notifications' => 'nullable|boolean',
            'sms_notifications' => 'nullable|boolean',
            'backup_enabled' => 'nullable|boolean',
            'backup_frequency' => 'required|string',
            'backup_retention' => 'required|integer|min:7|max:90',
            'backup_include_logs' => 'nullable|boolean',
            'cache_driver' => 'required|string',
            'cache_ttl' => 'required|integer|min:60|max:86400',
            'queue_driver' => 'required|string',
            'max_workers' => 'required|integer|min:1|max:50',
        ]);

        // System settings
        Setting::set('system_name', $validated['system_name'], 'system', 'string');
        Setting::set('system_timezone', $validated['timezone'], 'system', 'string');
        Setting::set('system_maintenance_mode', $validated['maintenance_mode'], 'system', 'boolean');
        Setting::set('system_debug_mode', $validated['debug_mode'], 'system', 'boolean');
        
        // Trading settings
        Setting::set('trading_enabled', $validated['trading_enabled'], 'trading', 'boolean');
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
        Setting::set('monitor_max_drawdown', $validated['max_drawdown'], 'monitoring', 'float');
        
        // Notification settings
        Setting::set('notifications_enabled', $request->has('notifications_enabled'), 'notifications', 'boolean');
        Setting::set('notifications_email', $request->has('email_notifications'), 'notifications', 'boolean');
        Setting::set('notifications_telegram', $request->has('telegram_notifications'), 'notifications', 'boolean');
        Setting::set('notifications_sms', $request->has('sms_notifications'), 'notifications', 'boolean');
        
        // Backup settings
        Setting::set('backup_enabled', $request->has('backup_enabled'), 'backup', 'boolean');
        Setting::set('backup_frequency', $validated['backup_frequency'], 'backup', 'string');
        Setting::set('backup_retention_days', $validated['backup_retention'], 'backup', 'integer');
        Setting::set('backup_include_logs', $request->has('backup_include_logs'), 'backup', 'boolean');
        
        // Performance settings
        Setting::set('cache_driver', $validated['cache_driver'], 'performance', 'string');
        Setting::set('cache_ttl', $validated['cache_ttl'], 'performance', 'integer');
        Setting::set('queue_driver', $validated['queue_driver'], 'performance', 'string');
        Setting::set('queue_max_workers', $validated['max_workers'], 'performance', 'integer');

        return redirect()->route('admin.settings.system')
            ->with('success', 'System settings updated successfully!');
    }
}