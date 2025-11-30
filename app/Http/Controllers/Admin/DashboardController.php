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
        $totalUsers = User::where('is_admin', false)->count();
        
        $lastMonthUsers = User::where('is_admin', false)
            ->where('created_at', '>=', now()->subMonth())
            ->count();
        $userGrowthPercent = $totalUsers > 0 
            ? round(($lastMonthUsers / $totalUsers) * 100, 1) 
            : 0;
        
        $recentUsers = User::where('is_admin', false)
            ->with('exchangeAccount')
            ->latest()
            ->take(10)
            ->get();
        
        $activeTrades = Trade::where('status', 'open')->count();
        $totalProfit = Trade::where('status', 'closed')->sum('realized_pnl');
        $todaySignals = Signal::whereDate('created_at', today())->count();
        
        $yesterdayTrades = Trade::where('status', 'open')
            ->whereDate('created_at', today()->subDay())
            ->count();
        $tradesChange = $yesterdayTrades > 0 
            ? round((($activeTrades - $yesterdayTrades) / $yesterdayTrades) * 100, 1)
            : ($activeTrades > 0 ? 100 : 0);
        
        $yesterdayProfit = Trade::where('status', 'closed')
            ->whereDate('closed_at', today()->subDay())
            ->sum('realized_pnl');
        $profitChange = $yesterdayProfit > 0
            ? round((($totalProfit - $yesterdayProfit) / $yesterdayProfit) * 100, 1)
            : ($totalProfit > 0 ? 100 : 0);
        
        $yesterdaySignals = Signal::whereDate('created_at', today()->subDay())->count();
        $signalsChange = $yesterdaySignals > 0
            ? round((($todaySignals - $yesterdaySignals) / $yesterdaySignals) * 100, 1)
            : ($todaySignals > 0 ? 100 : 0);
        
        $connectedUsers = User::where('is_admin', false)
            ->whereHas('exchangeAccount')
            ->count();
        $connectionRate = $totalUsers > 0 
            ? round(($connectedUsers / $totalUsers) * 100, 1)
            : 0;
        
        return view('admin.dashboard', compact(
            'totalUsers',
            'userGrowthPercent',
            'recentUsers',
            'activeTrades',
            'tradesChange',
            'totalProfit',
            'profitChange',
            'todaySignals',
            'signalsChange',
            'connectedUsers',
            'connectionRate'
        ));
    }

    public function history()
    {
        $trades = Trade::with(['user', 'signal'])
            ->where('status', 'closed')
            ->latest('closed_at')
            ->paginate(50);

        $stats = [
            'total_trades' => Trade::where('status', 'closed')->count(),
            'total_profit' => Trade::where('status', 'closed')->sum('realized_pnl'),
            'winning_trades' => Trade::where('status', 'closed')->where('realized_pnl', '>', 0)->count(),
            'losing_trades' => Trade::where('status', 'closed')->where('realized_pnl', '<', 0)->count(),
            'avg_profit' => Trade::where('status', 'closed')->avg('realized_pnl'),
            'win_rate' => 0,
        ];

        if ($stats['total_trades'] > 0) {
            $stats['win_rate'] = round(($stats['winning_trades'] / $stats['total_trades']) * 100, 2);
        }

        return view('admin.history.index', compact('trades', 'stats'));
    }
}