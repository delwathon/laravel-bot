<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Test data for dashboard
        $data = [
            'totalUsers' => 248,
            'activeTrades' => 1342,
            'totalPnl' => 45231,
            'winRate' => 68.4,
            
            // Recent activities
            'recentActivities' => collect([
                (object) [
                    'title' => 'Trade Executed',
                    'description' => 'BTC/USDT LONG • Bybit',
                    'time_ago' => '2m',
                    'amount' => 1234,
                    'type_color' => 'success',
                    'icon' => 'check-circle-fill'
                ],
                (object) [
                    'title' => 'New User',
                    'description' => 'john.doe@example.com',
                    'time_ago' => '5m',
                    'amount' => null,
                    'type_color' => 'primary',
                    'icon' => 'person-plus-fill'
                ],
                (object) [
                    'title' => 'SL Triggered',
                    'description' => 'ETH/USDT • User #1043',
                    'time_ago' => '12m',
                    'amount' => -432,
                    'type_color' => 'warning',
                    'icon' => 'exclamation-triangle-fill'
                ],
                (object) [
                    'title' => 'Signal Generated',
                    'description' => 'SOL/USDT LONG • 85% confidence',
                    'time_ago' => '15m',
                    'amount' => null,
                    'type_color' => 'info',
                    'icon' => 'lightning-charge-fill'
                ],
                (object) [
                    'title' => 'TP Reached',
                    'description' => 'XRP/USDT • 248 users',
                    'time_ago' => '18m',
                    'amount' => 8923,
                    'type_color' => 'success',
                    'icon' => 'bullseye'
                ],
            ]),
            
            // Top performing pairs
            'topPairs' => collect([
                (object) [
                    'symbol' => 'BTC/USDT',
                    'name' => 'Bitcoin',
                    'icon' => 'currency-bitcoin',
                    'color' => 'warning',
                    'exchange' => 'Bybit',
                    'exchange_color' => 'primary',
                    'price' => 66450,
                    'volume' => '2.4B',
                    'change' => 3.45,
                    'trades_count' => 145,
                    'win_rate' => 72,
                    'pnl' => 12456,
                    'pnl_percent' => 18.3
                ],
                (object) [
                    'symbol' => 'ETH/USDT',
                    'name' => 'Ethereum',
                    'icon' => 'currency-exchange',
                    'color' => 'info',
                    'exchange' => 'Binance',
                    'exchange_color' => 'warning',
                    'price' => 3245,
                    'volume' => '1.8B',
                    'change' => 2.18,
                    'trades_count' => 98,
                    'win_rate' => 68,
                    'pnl' => 8923,
                    'pnl_percent' => 14.2
                ],
                (object) [
                    'symbol' => 'SOL/USDT',
                    'name' => 'Solana',
                    'icon' => 'coin',
                    'color' => 'purple',
                    'exchange' => 'Bybit',
                    'exchange_color' => 'primary',
                    'price' => 145.80,
                    'volume' => '892M',
                    'change' => 5.23,
                    'trades_count' => 76,
                    'win_rate' => 65,
                    'pnl' => 6734,
                    'pnl_percent' => 12.1
                ],
            ])
        ];
        
        return view('admin.dashboard', $data);
    }

    public function history()
    {
        return view('admin.history.index');
    }   
}