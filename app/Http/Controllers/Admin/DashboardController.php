<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trade;
use App\Models\Position;
use App\Models\Signal;
use App\Models\ExchangeAccount;
use App\Services\BybitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with real-time data
     */
    public function index()
    {
        // ==================================
        // PRIMARY KPI METRICS
        // ==================================
        
        // Total Users (Non-Admin)
        $totalUsers = User::where('is_admin', false)->count();
        
        // User Growth (Last 30 days)
        $last30DaysUsers = User::where('is_admin', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $previous30DaysUsers = User::where('is_admin', false)
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->count();
        $userGrowthPercent = $previous30DaysUsers > 0 
            ? round((($last30DaysUsers - $previous30DaysUsers) / $previous30DaysUsers) * 100, 1)
            : ($last30DaysUsers > 0 ? 100 : 0);
        
        // Active Trades
        $activeTrades = Trade::where('status', 'open')->count();
        
        // Active Trades Change (Yesterday vs Today)
        $yesterdayTrades = Trade::where('status', 'open')
            ->whereDate('created_at', today()->subDay())
            ->count();
        $tradesChange = $yesterdayTrades > 0 
            ? round((($activeTrades - $yesterdayTrades) / $yesterdayTrades) * 100, 1)
            : ($activeTrades > 0 ? 100 : 0);
        
        // Total Profit (All Time)
        $totalProfit = Trade::where('status', 'closed')->sum('realized_pnl');
        
        // Profit Change (Last 7 days vs Previous 7 days)
        $last7DaysProfit = Trade::where('status', 'closed')
            ->where('closed_at', '>=', now()->subDays(7))
            ->sum('realized_pnl');
        $previous7DaysProfit = Trade::where('status', 'closed')
            ->whereBetween('closed_at', [now()->subDays(14), now()->subDays(7)])
            ->sum('realized_pnl');
        $profitChange = $previous7DaysProfit != 0
            ? round((($last7DaysProfit - $previous7DaysProfit) / abs($previous7DaysProfit)) * 100, 1)
            : ($last7DaysProfit > 0 ? 100 : ($last7DaysProfit < 0 ? -100 : 0));
        
        // Today's Signals
        $todaySignals = Signal::whereDate('created_at', today())->count();
        
        // Signals Change
        $yesterdaySignals = Signal::whereDate('created_at', today()->subDay())->count();
        $signalsChange = $yesterdaySignals > 0
            ? round((($todaySignals - $yesterdaySignals) / $yesterdaySignals) * 100, 1)
            : ($todaySignals > 0 ? 100 : 0);
        
        // ==================================
        // ADDITIONAL METRICS
        // ==================================
        
        // Connected Users (Users with active exchange accounts)
        $connectedUsers = User::where('is_admin', false)
            ->whereHas('exchangeAccount', function($query) {
                $query->where('is_active', true);
            })
            ->count();
        $connectionRate = $totalUsers > 0 
            ? round(($connectedUsers / $totalUsers) * 100, 1)
            : 0;
        
        // Active Positions
        $activePositions = Position::where('is_active', true)->count();
        
        // Today's Trading Volume
        $todayVolume = Trade::whereDate('created_at', today())
            ->sum(DB::raw('entry_price * quantity'));
        
        // Win Rate (All Time)
        $closedTrades = Trade::where('status', 'closed')->count();
        $winningTrades = Trade::where('status', 'closed')
            ->where('realized_pnl', '>', 0)
            ->count();
        $winRate = $closedTrades > 0 
            ? round(($winningTrades / $closedTrades) * 100, 1)
            : 0;
        
        // Average Profit Per Trade
        $avgProfitPerTrade = $closedTrades > 0 
            ? $totalProfit / $closedTrades
            : 0;
        
        // ==================================
        // ADMIN ACCOUNT BALANCE
        // ==================================
        
        $adminBalance = 0;
        $adminBalanceChange = 0;
        try {
            $adminAccount = ExchangeAccount::getBybitAccount();
            if ($adminAccount && $adminAccount->is_active) {
                $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
                $currentBalance = $bybit->getBalance();
                $adminBalance = $currentBalance;
                
                // Calculate balance change (if we have previous balance cached)
                $previousBalance = $adminAccount->balance ?? $currentBalance;
                if ($previousBalance > 0) {
                    $adminBalanceChange = round((($currentBalance - $previousBalance) / $previousBalance) * 100, 1);
                }
                
                // Update cached balance
                $adminAccount->update([
                    'balance' => $currentBalance,
                    'last_synced_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch admin balance: ' . $e->getMessage());
        }
        
        // ==================================
        // RECENT USERS (Top 10 with trading activity)
        // ==================================
        
        $recentUsers = User::where('is_admin', false)
            ->with(['exchangeAccount', 'trades' => function($query) {
                $query->where('status', 'closed')->latest()->limit(5);
            }])
            ->withCount([
                'trades',
                'trades as today_trades_count' => function($query) {
                    $query->whereDate('created_at', today());
                },
                'positions as active_positions_count' => function($query) {
                    $query->where('is_active', true);
                }
            ])
            ->latest()
            ->limit(10)
            ->get();
        
        // Calculate P&L for each recent user
        $recentUsers->each(function($user) {
            $user->total_pnl = $user->trades()
                ->where('status', 'closed')
                ->sum('realized_pnl');
            
            // Calculate win rate
            $userClosedTrades = $user->trades()->where('status', 'closed')->count();
            $userWinningTrades = $user->trades()
                ->where('status', 'closed')
                ->where('realized_pnl', '>', 0)
                ->count();
            $user->win_rate = $userClosedTrades > 0 
                ? round(($userWinningTrades / $userClosedTrades) * 100, 1)
                : 0;
        });
        
        // ==================================
        // TOP PERFORMING USERS (Top 5 by P&L)
        // ==================================
        
        $topPerformers = User::where('is_admin', false)
            ->withCount('trades')
            ->get()
            ->map(function($user) {
                $user->total_pnl = $user->trades()
                    ->where('status', 'closed')
                    ->sum('realized_pnl');
                return $user;
            })
            ->sortByDesc('total_pnl')
            ->take(5);
        
        // ==================================
        // RECENT TRADES (Last 10 across all users)
        // ==================================
        
        $recentTrades = Trade::with(['user', 'signal'])
            ->whereHas('user', function($query) {
                $query->where('is_admin', false);
            })
            ->latest()
            ->limit(10)
            ->get();
        
        // ==================================
        // TRADING PAIRS PERFORMANCE
        // ==================================
        
        $tradingPairs = Trade::select('symbol')
            ->selectRaw('COUNT(*) as trade_count')
            ->selectRaw('SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as active_count')
            ->selectRaw('SUM(CASE WHEN status = "closed" THEN realized_pnl ELSE 0 END) as total_pnl')
            ->selectRaw('AVG(CASE WHEN status = "closed" THEN realized_pnl ELSE NULL END) as avg_pnl')
            ->groupBy('symbol')
            ->orderByDesc('trade_count')
            ->limit(6)
            ->get();
        
        // ==================================
        // DAILY STATISTICS (Last 7 Days)
        // ==================================
        
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyStats[] = [
                'date' => $date->format('M d'),
                'trades' => Trade::whereDate('created_at', $date)->count(),
                'profit' => Trade::whereDate('closed_at', $date)
                    ->where('status', 'closed')
                    ->sum('realized_pnl'),
                'volume' => Trade::whereDate('created_at', $date)
                    ->sum(DB::raw('entry_price * quantity')),
                'new_users' => User::where('is_admin', false)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }
        
        // ==================================
        // SYSTEM HEALTH INDICATORS
        // ==================================
        
        $systemHealth = [
            'bybit_api_status' => $this->checkBybitApiStatus(),
            'total_errors_today' => $this->countTodayErrors(),
            'pending_orders' => Trade::where('status', 'pending')->count(),
            'failed_trades_today' => Trade::where('status', 'failed')
                ->whereDate('created_at', today())
                ->count(),
            'active_signals' => Signal::where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->count(),
        ];
        
        // ==================================
        // HOURLY ACTIVITY (Last 24 Hours)
        // ==================================
        
        $hourlyActivity = [];
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $hourlyActivity[] = [
                'hour' => $hour->format('H:00'),
                'trades' => Trade::whereBetween('created_at', [
                    $hour->startOfHour(),
                    $hour->copy()->endOfHour()
                ])->count(),
            ];
        }
        
        // ==================================
        // RISK METRICS
        // ==================================
        
        $riskMetrics = [
            'total_exposure' => Position::where('is_active', true)
                ->sum(DB::raw('quantity * current_price')),
            'total_margin_used' => Position::where('is_active', true)
                ->sum('margin_used'),
            'largest_position' => Position::where('is_active', true)
                ->orderByDesc(DB::raw('quantity * current_price'))
                ->first(),
            'at_risk_positions' => Position::where('is_active', true)
                ->where('unrealized_pnl_percent', '<', -5)
                ->count(),
        ];
        
        return view('admin.dashboard', compact(
            'totalUsers',
            'userGrowthPercent',
            'activeTrades',
            'tradesChange',
            'totalProfit',
            'profitChange',
            'todaySignals',
            'signalsChange',
            'connectedUsers',
            'connectionRate',
            'activePositions',
            'todayVolume',
            'winRate',
            'avgProfitPerTrade',
            'adminBalance',
            'adminBalanceChange',
            'recentUsers',
            'topPerformers',
            'recentTrades',
            'tradingPairs',
            'dailyStats',
            'systemHealth',
            'hourlyActivity',
            'riskMetrics'
        ));
    }
    
    /**
     * Check Bybit API status
     */
    protected function checkBybitApiStatus()
    {
        try {
            $adminAccount = ExchangeAccount::getBybitAccount();
            if (!$adminAccount) {
                return 'not_configured';
            }
            
            $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
            $balance = $bybit->getBalance();
            
            return $balance !== null ? 'operational' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }
    
    /**
     * Count today's errors from logs
     */
    protected function countTodayErrors()
    {
        return Trade::where('status', 'failed')
            ->whereDate('created_at', today())
            ->count();
    }
    
    /**
     * Get real-time dashboard stats (AJAX endpoint)
     */
    public function getRealtimeStats()
    {
        return response()->json([
            'active_trades' => Trade::where('status', 'open')->count(),
            'active_positions' => Position::where('is_active', true)->count(),
            'pending_orders' => Trade::where('status', 'pending')->count(),
            'total_users_online' => $this->getActiveUsersCount(),
            'admin_balance' => $this->getAdminBalance(),
            'system_status' => $this->checkBybitApiStatus(),
        ]);
    }
    
    /**
     * Get active users count (users with activity in last 30 minutes)
     */
    protected function getActiveUsersCount()
    {
        return User::where('is_admin', false)
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->count();
    }
    
    /**
     * Get admin balance (cached for 5 minutes)
     */
    protected function getAdminBalance()
    {
        return Cache::remember('admin_balance', 300, function() {
            try {
                $adminAccount = ExchangeAccount::getBybitAccount();
                if (!$adminAccount) {
                    return 0;
                }
                
                $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
                return $bybit->getBalance();
            } catch (\Exception $e) {
                Log::error('Failed to fetch admin balance: ' . $e->getMessage());
                return 0;
            }
        });
    }
}