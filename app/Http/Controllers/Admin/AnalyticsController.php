<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Calculate overview metrics
        $metrics = $this->calculateMetrics();
        
        // Calculate performance metrics
        $performanceMetrics = $this->calculatePerformanceMetrics();
        
        // Calculate user distribution
        $distribution = $this->calculateUserDistribution();
        
        // Get top traders
        $topTraders = $this->getTopTraders(10);
        
        // Get P&L chart data
        $pnlChartData = $this->getPnlChartData('1m');
        
        // Get trading activity by hour
        $activityByHour = $this->getActivityByHour();
        
        // Get most traded pairs
        $tradedPairs = $this->getMostTradedPairs(4);
        
        return view('admin.analytics.index', compact(
            'metrics',
            'performanceMetrics',
            'distribution',
            'topTraders',
            'pnlChartData',
            'activityByHour',
            'tradedPairs'
        ));
    }
    
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '1m');
        $data = $this->getPnlChartData($period);
        
        return response()->json($data);
    }
    
    protected function calculateMetrics()
    {
        // Total volume last 30 days
        $totalVolume30d = Trade::where('created_at', '>=', now()->subDays(30))
            ->sum(DB::raw('entry_price * quantity'));
        
        // Total volume previous 30 days (for comparison)
        $previousVolume = Trade::whereBetween('created_at', [
            now()->subDays(60),
            now()->subDays(30)
        ])->sum(DB::raw('entry_price * quantity'));
        
        $volumeChange = $previousVolume > 0 
            ? (($totalVolume30d - $previousVolume) / $previousVolume) * 100 
            : 0;
        
        // Win rate
        $totalClosed = Trade::where('status', 'closed')->count();
        $totalWins = Trade::where('status', 'closed')
            ->where('realized_pnl', '>', 0)
            ->count();
        
        $winRate = $totalClosed > 0 ? ($totalWins / $totalClosed) * 100 : 0;
        
        // Total fees
        $totalFees = Trade::where('status', 'closed')->sum('fees');
        $feePercentage = $totalVolume30d > 0 ? ($totalFees / $totalVolume30d) * 100 : 0;
        
        // Sharpe Ratio (simplified calculation)
        $sharpeRatio = $this->calculateSharpeRatio();
        
        return [
            'total_volume_30d' => $totalVolume30d,
            'volume_change' => $volumeChange,
            'win_rate' => $winRate,
            'total_fees' => $totalFees,
            'fee_percentage' => $feePercentage,
            'sharpe_ratio' => $sharpeRatio,
        ];
    }
    
    protected function calculatePerformanceMetrics()
    {
        $totalTrades = Trade::count();
        $winningTrades = Trade::where('status', 'closed')
            ->where('realized_pnl', '>', 0)
            ->count();
        $losingTrades = Trade::where('status', 'closed')
            ->where('realized_pnl', '<', 0)
            ->count();
        
        // Average trade duration
        $avgDuration = Trade::where('status', 'closed')
            ->whereNotNull('closed_at')
            ->get()
            ->avg(function($trade) {
                return $trade->opened_at->diffInMinutes($trade->closed_at);
            });
        
        $avgDurationHours = $avgDuration ? round($avgDuration / 60, 1) : 0;
        
        // Best and worst trades
        $bestTrade = Trade::where('status', 'closed')
            ->max('realized_pnl') ?? 0;
        $worstTrade = Trade::where('status', 'closed')
            ->min('realized_pnl') ?? 0;
        
        return [
            'total_trades' => $totalTrades,
            'winning_trades' => $winningTrades,
            'losing_trades' => $losingTrades,
            'avg_duration' => $avgDurationHours . 'h',
            'best_trade' => $bestTrade,
            'worst_trade' => $worstTrade,
        ];
    }
    
    protected function calculateUserDistribution()
    {
        // Include ALL users (admin and regular users)
        $users = User::withCount(['trades as closed_trades' => function($query) {
                $query->where('status', 'closed');
            }])
            ->get();
        
        $profitable = 0;
        $breakeven = 0;
        $losing = 0;
        
        foreach ($users as $user) {
            if ($user->closed_trades == 0) {
                continue;
            }
            
            $totalPnl = Trade::where('user_id', $user->id)
                ->where('status', 'closed')
                ->sum('realized_pnl');
            
            if ($totalPnl > 100) {
                $profitable++;
            } elseif ($totalPnl < -100) {
                $losing++;
            } else {
                $breakeven++;
            }
        }
        
        $total = $profitable + $breakeven + $losing;
        
        return [
            'profitable_count' => $profitable,
            'breakeven_count' => $breakeven,
            'losing_count' => $losing,
            'profitable_percent' => $total > 0 ? round(($profitable / $total) * 100, 0) : 0,
            'breakeven_percent' => $total > 0 ? round(($breakeven / $total) * 100, 0) : 0,
            'losing_percent' => $total > 0 ? round(($losing / $total) * 100, 0) : 0,
        ];
    }
    
    protected function getTopTraders($limit = 10)
    {
        // Include ALL users (admin and regular users)
        return User::withCount('trades')
            ->get()
            ->filter(function($user) {
                return $user->trades_count > 0;
            })
            ->map(function($user) {
                $totalPnl = Trade::where('user_id', $user->id)
                    ->where('status', 'closed')
                    ->sum('realized_pnl');
                
                $closedTrades = Trade::where('user_id', $user->id)
                    ->where('status', 'closed')
                    ->count();
                
                $winningTrades = Trade::where('user_id', $user->id)
                    ->where('status', 'closed')
                    ->where('realized_pnl', '>', 0)
                    ->count();
                
                $winRate = $closedTrades > 0 ? ($winningTrades / $closedTrades) * 100 : 0;
                
                // Calculate ROI (simplified - assuming $10,000 starting balance)
                $roi = ($totalPnl / 10000) * 100;
                
                $user->total_trades = $user->trades_count;
                $user->total_pnl = $totalPnl;
                $user->win_rate = $winRate;
                $user->roi = $roi;
                
                return $user;
            })
            ->sortByDesc('total_pnl')
            ->take($limit)
            ->values();
    }
    
    protected function getPnlChartData($period)
    {
        $startDate = match($period) {
            '1m' => now()->subMonth(),
            '3m' => now()->subMonths(3),
            '6m' => now()->subMonths(6),
            '1y' => now()->subYear(),
            'all' => Trade::min('created_at') ?? now()->subYear(),
            default => now()->subMonth(),
        };
        
        // Get trades grouped by date
        $trades = Trade::where('status', 'closed')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get()
            ->groupBy(function($trade) {
                return $trade->created_at->format('Y-m-d');
            });
        
        $labels = [];
        $values = [];
        $cumulativePnl = 0;
        
        // Fill in all dates (even those without trades)
        $currentDate = \Carbon\Carbon::parse($startDate);
        $endDate = now();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');
            
            if (isset($trades[$dateKey])) {
                $dailyPnl = $trades[$dateKey]->sum('realized_pnl');
                $cumulativePnl += $dailyPnl;
            }
            
            $values[] = round($cumulativePnl, 2);
            $currentDate->addDay();
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
    
    protected function getActivityByHour()
    {
        $hourlyActivity = Trade::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
        
        $labels = [];
        $values = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            $values[] = $hourlyActivity[$hour] ?? 0;
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
    
    protected function getMostTradedPairs($limit = 4)
    {
        return Trade::selectRaw('symbol, COUNT(*) as trade_count')
            ->groupBy('symbol')
            ->orderByDesc('trade_count')
            ->limit($limit)
            ->get();
    }
    
    protected function calculateSharpeRatio()
    {
        // Simplified Sharpe Ratio calculation
        // Get daily returns
        $dailyReturns = Trade::where('status', 'closed')
            ->where('created_at', '>=', now()->subDays(30))
            ->get()
            ->groupBy(function($trade) {
                return $trade->created_at->format('Y-m-d');
            })
            ->map(function($trades) {
                return $trades->sum('realized_pnl');
            })
            ->values();
        
        if ($dailyReturns->count() < 2) {
            return 0;
        }
        
        $avgReturn = $dailyReturns->avg();
        $stdDev = $this->standardDeviation($dailyReturns->toArray());
        
        if ($stdDev == 0) {
            return 0;
        }
        
        // Sharpe = (Avg Return - Risk Free Rate) / Std Dev
        // Assuming 0% risk-free rate for simplicity
        $sharpe = $avgReturn / $stdDev;
        
        return round($sharpe, 2);
    }
    
    protected function standardDeviation($array)
    {
        $count = count($array);
        if ($count < 2) {
            return 0;
        }
        
        $mean = array_sum($array) / $count;
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $array)) / ($count - 1);
        
        return sqrt($variance);
    }
}