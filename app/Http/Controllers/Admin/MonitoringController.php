<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Display monitoring overview
     */
    public function overview()
    {
        // System health metrics
        $systemHealth = [
            'status' => 'healthy',
            'uptime' => '99.9%',
            'response_time' => '45ms',
            'cpu_usage' => 23,
            'memory_usage' => 67,
            'disk_usage' => 45,
        ];
        
        // Active connections
        $activeConnections = [
            'total_users_online' => 0,
            'active_trades' => 0,
            'api_calls_per_minute' => 0,
        ];
        
        // Exchange status
        $exchangeStatus = [
            'bybit' => [
                'status' => 'connected',
                'latency' => '12ms',
                'last_sync' => now(),
            ],
        ];
        
        // Recent errors (placeholder)
        $recentErrors = collect([]);
        
        // Active users stats
        $totalUsers = User::where('is_admin', false)->count();
        $activeToday = User::where('is_admin', false)
            ->whereNotNull('email_verified_at')
            ->count();
        
        return view('admin.monitoring.overview', compact(
            'systemHealth',
            'activeConnections',
            'exchangeStatus',
            'recentErrors',
            'totalUsers',
            'activeToday'
        ));
    }
}