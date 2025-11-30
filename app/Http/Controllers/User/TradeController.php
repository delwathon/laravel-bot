<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    /**
     * Display user's trade history
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->trades()->with(['signal', 'exchangeAccount']);
        
        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('pair')) {
            $query->where('symbol', $request->pair);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $trades = $query->paginate(20)->withQueryString();
        
        // Trade statistics - REAL DATA
        $closedTrades = $user->closedTrades();
        
        $stats = [
            'total_trades' => $user->total_trades_count,
            'winning_trades' => $closedTrades->where('realized_pnl', '>', 0)->count(),
            'losing_trades' => $closedTrades->where('realized_pnl', '<', 0)->count(),
            'total_profit' => $closedTrades->where('realized_pnl', '>', 0)->sum('realized_pnl'),
            'total_loss' => abs($closedTrades->where('realized_pnl', '<', 0)->sum('realized_pnl')),
            'win_rate' => $user->win_rate,
            'avg_profit' => 0,
            'avg_loss' => 0,
        ];
        
        // Calculate averages
        if ($stats['winning_trades'] > 0) {
            $stats['avg_profit'] = $stats['total_profit'] / $stats['winning_trades'];
        }
        
        if ($stats['losing_trades'] > 0) {
            $stats['avg_loss'] = $stats['total_loss'] / $stats['losing_trades'];
        }
        
        // Filters for view
        $filters = [
            'status' => $request->get('status', 'all'),
            'pair' => $request->get('pair', 'all'),
            'type' => $request->get('type', 'all'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];
        
        // Get unique symbols for filter
        $availableSymbols = $user->trades()
            ->select('symbol')
            ->distinct()
            ->pluck('symbol');
        
        return view('user.trades.index', compact(
            'stats',
            'filters',
            'trades',
            'availableSymbols'
        ));
    }
}