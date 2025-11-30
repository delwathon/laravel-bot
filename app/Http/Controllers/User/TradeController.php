<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $trades = $user->trades()
            ->with(['signal', 'exchangeAccount'])
            ->latest()
            ->paginate(20);
        
        $stats = [
            'total_trades' => $user->total_trades_count,
            'open_trades' => $user->openTrades()->count(),
            'closed_trades' => $user->closedTrades()->count(),
            'total_profit' => $user->total_pnl,
            'win_rate' => $user->win_rate,
            'avg_profit' => 0,
            'best_trade' => 0,
            'worst_trade' => 0,
        ];
        
        $closedTrades = $user->closedTrades();
        
        if ($stats['closed_trades'] > 0) {
            $stats['avg_profit'] = $closedTrades->avg('realized_pnl');
            $stats['best_trade'] = $closedTrades->max('realized_pnl');
            $stats['worst_trade'] = $closedTrades->min('realized_pnl');
        }
        
        return view('user.trades.index', compact('trades', 'stats'));
    }
}