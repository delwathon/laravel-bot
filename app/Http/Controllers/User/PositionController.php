<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display user's active positions
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get all active positions
        $positions = $user->activePositions()
            ->with(['trade', 'exchangeAccount'])
            ->get();
        
        // Position statistics - REAL DATA
        $stats = [
            'active_positions' => $positions->count(),
            'total_value' => $positions->sum(function($position) {
                return $position->entry_price * $position->quantity;
            }),
            'unrealized_pnl' => $positions->sum('unrealized_pnl'),
            'unrealized_pnl_percent' => 0,
            'margin_used' => $positions->sum('margin_used'),
            'margin_available' => 0,
        ];
        
        // Calculate unrealized P&L percentage
        if ($stats['total_value'] > 0) {
            $stats['unrealized_pnl_percent'] = ($stats['unrealized_pnl'] / $stats['total_value']) * 100;
        }
        
        // Calculate margin available
        if ($user->hasConnectedExchange()) {
            $accountBalance = $user->exchangeAccount->balance;
            $stats['margin_available'] = $accountBalance - $stats['margin_used'];
        }
        
        // Group positions by exchange (though we only have Bybit)
        $positionsByExchange = [
            'bybit' => $positions->where('exchange', 'bybit'),
        ];
        
        return view('user.positions.index', compact(
            'stats',
            'positions',
            'positionsByExchange'
        ));
    }
}