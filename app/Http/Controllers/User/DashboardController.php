<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display user dashboard
     */
    public function index()
    {
        $user = auth()->user();
        
        // User trading statistics - REAL DATA
        $stats = [
            'total_profit' => $user->total_pnl,
            'total_trades' => $user->total_trades_count,
            'active_trades' => $user->openTrades()->count(),
            'win_rate' => $user->win_rate,
            'connected_exchanges' => $user->hasConnectedExchange() ? 1 : 0,
            'today_profit' => $user->trades()
                ->whereDate('closed_at', today())
                ->sum('realized_pnl'),
        ];
        
        // Calculate total profit percentage
        $closedTrades = $user->closedTrades()->count();
        if ($closedTrades > 0) {
            $totalInvested = $user->closedTrades()
                ->sum(\DB::raw('entry_price * quantity'));
            $stats['total_profit_percent'] = $totalInvested > 0 
                ? round(($stats['total_profit'] / $totalInvested) * 100, 2)
                : 0;
        } else {
            $stats['total_profit_percent'] = 0;
        }
        
        // Recent trades (last 10)
        $recentTrades = $user->trades()
            ->with(['signal'])
            ->latest()
            ->take(10)
            ->get();
        
        // Active positions
        $activePositions = $user->activePositions()
            ->with(['trade'])
            ->get();
        
        // Calculate account balance from exchange
        $accountBalance = 0;
        if ($user->hasConnectedExchange()) {
            $accountBalance = $user->exchangeAccount->balance;
        }
        $stats['account_balance'] = $accountBalance;
        
        // Performance chart data (last 30 days)
        $performanceData = [
            'labels' => [],
            'values' => [],
        ];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $performanceData['labels'][] = $date->format('M d');
            
            $dailyPnl = $user->trades()
                ->whereDate('closed_at', $date->toDateString())
                ->sum('realized_pnl');
            
            $performanceData['values'][] = $dailyPnl;
        }
        
        return view('user.dashboard', compact(
            'user',
            'stats',
            'recentTrades',
            'activePositions',
            'performanceData'
        ));
    }
}