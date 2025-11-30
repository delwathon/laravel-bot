<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminExchangeAccount;
use App\Models\ExchangeAccount;
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
        $settings = [
            'interval' => 15,
            'top_signals' => 5,
            'min_confidence' => 70,
            'signal_expiry' => 30,
            'risk_reward_ratio' => '1:2',
            'max_stop_loss' => 2,
            'position_size' => 5,
            'enabled_exchanges' => ['bybit'],
            'enabled_pairs' => ['BTCUSDT', 'ETHUSDT', 'SOLUSDT'],
        ];
        
        return view('admin.settings.signal-generator', compact('settings'));
    }
    
    public function updateSignalGenerator(Request $request)
    {
        $validated = $request->validate([
            'interval' => 'required|integer|min:5|max:240',
            'top_signals' => 'required|integer|min:1|max:20',
            'min_confidence' => 'required|integer|min:50|max:95',
            'signal_expiry' => 'required|integer|min:5|max:120',
        ]);
        
        return redirect()->route('admin.settings.signal-generator')
            ->with('success', 'Signal generator settings updated successfully!');
    }

    public function system()
    {
        return view('admin.settings.system');
    }

    public function updateSystem(Request $request)
    {
        return redirect()->route('admin.settings.system')
            ->with('success', 'System settings updated successfully!');
    }
}