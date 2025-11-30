<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trade;
use App\Models\Position;
use App\Models\Signal;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Real user statistics
        $totalUsers = User::where('is_admin', false)->count();
        $adminCount = User::where('is_admin', true)->count();
        $totalAccounts = User::count();
        
        // Calculate user growth
        $lastMonthUsers = User::where('is_admin', false)
            ->where('created_at', '>=', now()->subMonth())
            ->count();
        $userGrowthPercent = $totalUsers > 0 
            ? round(($lastMonthUsers / $totalUsers) * 100, 1) 
            : 0;
        
        // Recent users
        $recentUsers = User::where('is_admin', false)
            ->latest()
            ->take(10)
            ->get();
        
        // Real trading statistics
        $activeTrades = Trade::where('status', 'open')->count();
        $totalProfit = Trade::where('status', 'closed')->sum('realized_pnl');
        $todaySignals = Signal::whereDate('created_at', today())->count();
        
        // Calculate changes from yesterday
        $yesterdayTrades = Trade::where('status', 'open')
            ->whereDate('created_at', today()->subDay())
            ->count();
        $tradesChange = $yesterdayTrades > 0 
            ? round((($activeTrades - $yesterdayTrades) / $yesterdayTrades) * 100, 1)
            : 0;
        
        $yesterdayProfit = Trade::where('status', 'closed')
            ->whereDate('closed_at', today()->subDay())
            ->sum('realized_pnl');
        $profitChange = $yesterdayProfit > 0
            ? round((($totalProfit - $yesterdayProfit) / abs($yesterdayProfit)) * 100, 1)
            : 0;
        
        // Top performing trading pairs
        $topPairs = Trade::selectRaw('symbol, 
            COUNT(*) as trades_count,
            AVG(CASE WHEN realized_pnl > 0 THEN 1 ELSE 0 END) * 100 as win_rate,
            SUM(realized_pnl) as total_pnl,
            SUM(realized_pnl_percent) / COUNT(*) as avg_pnl_percent
        ')
            ->where('status', 'closed')
            ->groupBy('symbol')
            ->orderByDesc('total_pnl')
            ->limit(5)
            ->get()
            ->map(function($pair) {
                // Get latest price (from most recent trade)
                $latestTrade = Trade::where('symbol', $pair->symbol)
                    ->latest()
                    ->first();
                
                return (object) [
                    'symbol' => $pair->symbol,
                    'name' => str_replace('/USDT', '', $pair->symbol),
                    'icon' => $this->getIconForSymbol($pair->symbol),
                    'color' => $this->getColorForSymbol($pair->symbol),
                    'exchange' => 'Bybit',
                    'exchange_color' => 'primary',
                    'price' => $latestTrade ? $latestTrade->entry_price : 0,
                    'volume' => '-',
                    'change' => 0,
                    'trades_count' => $pair->trades_count,
                    'win_rate' => round($pair->win_rate, 0),
                    'pnl' => $pair->total_pnl,
                    'pnl_percent' => round($pair->avg_pnl_percent, 1),
                ];
            });
        
        return view('admin.dashboard', compact(
            'totalUsers',
            'adminCount',
            'totalAccounts',
            'activeTrades',
            'totalProfit',
            'todaySignals',
            'profitChange',
            'tradesChange',
            'userGrowthPercent',
            'recentUsers',
            'topPairs'
        ));
    }
    
    /**
     * Get icon class for symbol
     */
    private function getIconForSymbol($symbol)
    {
        $icons = [
            'BTC/USDT' => 'currency-bitcoin',
            'ETH/USDT' => 'currency-exchange',
            'SOL/USDT' => 'coin',
            'BNB/USDT' => 'currency-dollar',
            'XRP/USDT' => 'graph-up',
        ];
        
        return $icons[$symbol] ?? 'currency-exchange';
    }
    
    /**
     * Get color for symbol
     */
    private function getColorForSymbol($symbol)
    {
        $colors = [
            'BTC/USDT' => 'warning',
            'ETH/USDT' => 'info',
            'SOL/USDT' => 'purple',
            'BNB/USDT' => 'warning',
            'XRP/USDT' => 'primary',
        ];
        
        return $colors[$symbol] ?? 'primary';
    }
    
    public function history()
    {
        return view('admin.history.index');
    }
}