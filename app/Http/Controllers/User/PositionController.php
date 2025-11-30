<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $positions = $user->activePositions()
            ->with(['trade', 'exchangeAccount'])
            ->get();
        
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
        
        if ($stats['total_value'] > 0) {
            $stats['unrealized_pnl_percent'] = ($stats['unrealized_pnl'] / $stats['total_value']) * 100;
        }
        
        if ($user->hasConnectedExchange()) {
            $accountBalance = $user->exchangeAccount->balance;
            $stats['margin_available'] = $accountBalance - $stats['margin_used'];
        }
        
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