<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Position;
use App\Models\Setting;
use App\Models\Trade;
use App\Models\ExchangeAccount;
use App\Services\BybitService;
use App\Services\PositionMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    protected $positionMonitor;

    public function __construct(PositionMonitorService $positionMonitor)
    {
        $this->positionMonitor = $positionMonitor;
    }

    /**
     * Display monitoring overview with LIVE Bybit data
     */
    public function overview(Request $request)
    {
        // ==================================
        // SYSTEM HEALTH METRICS
        // ==================================
        $systemHealth = $this->getSystemHealthMetrics();
        
        // ==================================
        // ACTIVE CONNECTIONS & STATUS
        // ==================================
        $activeConnections = $this->getActiveConnectionsData();
        
        // ==================================
        // EXCHANGE STATUS
        // ==================================
        $exchangeStatus = $this->getExchangeStatus();
        
        // ==================================
        // MONITORING STATISTICS (24H)
        // ==================================
        $monitoringStats = $this->getMonitoringStatistics();
        
        // ==================================
        // RECENT ERRORS & ALERTS
        // ==================================
        $recentErrors = $this->getRecentErrors();
        
        // ==================================
        // ACTIVE POSITIONS WITH FILTERS
        // ==================================
        $query = Position::where('is_active', true)
            ->with(['user', 'user.exchangeAccount', 'trade', 'trade.signal']);

        // Apply filters (BEFORE live data fetch for efficiency)
        if ($request->filled('user_search')) {
            $search = $request->user_search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        if ($request->filled('pair_filter')) {
            $query->where('symbol', $request->pair_filter);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get positions (will apply status filter AFTER live data fetch)
        $positions = $query->paginate(20)->withQueryString();

        // ==================================
        // FETCH LIVE DATA FROM BYBIT FOR ALL POSITIONS
        // ==================================
        
        // Get the collection of positions
        $positionsCollection = $positions->getCollection();
        
        // Transform each position to add LIVE data
        $updatedPositions = $positionsCollection->map(function ($position) {
            try {
                Log::info("Processing position {$position->id} for LIVE data");
                
                // Fetch LIVE current price from Bybit
                $livePrice = $this->getLivePrice($position);
                
                if ($livePrice !== null) {
                    $position->current_price = $livePrice;
                    
                    // Recalculate LIVE unrealized P&L
                    $this->recalculateLivePnL($position);
                    
                    $position->is_live_data = true;
                    
                    Log::info("Position {$position->id}: Successfully fetched LIVE price: {$livePrice}");
                } else {
                    $position->is_live_data = false;
                    Log::warning("Position {$position->id}: Failed to fetch LIVE price, using database value");
                }
                
            } catch (\Exception $e) {
                Log::error("Position {$position->id}: Error fetching LIVE data - " . $e->getMessage());
                $position->is_live_data = false;
            }
            
            // Calculate additional LIVE metrics
            $position->time_ago = $position->created_at->diffForHumans();
            $position->progress_to_tp = $this->calculateProgressToTP($position);
            $position->health_status = $this->calculateHealthStatus($position);
            
            return $position;
        });
        
        // Replace the collection in the paginator
        $positions->setCollection($updatedPositions);

        // Apply status filter AFTER live data calculation
        if ($request->filled('status_filter')) {
            $filteredPositions = $positions->getCollection()->filter(function($position) use ($request) {
                switch ($request->status_filter) {
                    case 'profitable':
                        return $position->unrealized_pnl > 0;
                    case 'losing':
                        return $position->unrealized_pnl < 0;
                    case 'at_risk':
                        // At risk: within 15% of stop loss
                        if ($position->side === 'long') {
                            $slDistance = $position->current_price - $position->stop_loss;
                            $totalRange = $position->entry_price - $position->stop_loss;
                        } else {
                            $slDistance = $position->stop_loss - $position->current_price;
                            $totalRange = $position->stop_loss - $position->entry_price;
                        }
                        return $totalRange > 0 && (($slDistance / $totalRange) < 0.15);
                    default:
                        return true;
                }
            });
            
            $positions->setCollection($filteredPositions->values());
        }

        // ==================================
        // AVAILABLE TRADING PAIRS
        // ==================================
        $tradingPairs = Position::where('is_active', true)
            ->select('symbol')
            ->distinct()
            ->pluck('symbol')
            ->toArray();

        // ==================================
        // USER STATISTICS
        // ==================================
        $totalUsers = User::where('is_admin', false)->count();
        $activeToday = User::where('is_admin', false)
            ->whereHas('trades', function($q) {
                $q->whereDate('created_at', today());
            })
            ->count();

        return view('admin.monitoring.overview', compact(
            'systemHealth',
            'activeConnections',
            'exchangeStatus',
            'monitoringStats',
            'recentErrors',
            'positions',
            'tradingPairs',
            'totalUsers',
            'activeToday'
        ));
    }

    /**
     * Get system health metrics
     */
    protected function getSystemHealthMetrics()
    {
        return [
            'status' => $this->getOverallSystemStatus(),
            'uptime' => $this->calculateSystemUptime(),
            'response_time' => $this->getAverageResponseTime(),
            'cpu_usage' => $this->getCPUUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
        ];
    }

    /**
     * Get active connections data
     */
    protected function getActiveConnectionsData()
    {
        $activePositions = Position::where('is_active', true)->count();
        
        $usersWithActivePositions = Position::where('is_active', true)
            ->select('user_id')
            ->distinct()
            ->count();

        // Calculate API calls per minute (from cache or estimate)
        $apiCallsPerMinute = Cache::remember('api_calls_per_minute', 60, function () {
            // Count trades created in last minute as proxy for API activity
            return Trade::where('created_at', '>=', now()->subMinute())->count();
        });

        return [
            'total_users_online' => $usersWithActivePositions,
            'active_trades' => $activePositions,
            'api_calls_per_minute' => $apiCallsPerMinute,
        ];
    }

    /**
     * Get exchange connection status
     */
    protected function getExchangeStatus()
    {
        $status = [];

        // Check admin Bybit connection
        try {
            $adminAccount = ExchangeAccount::getBybitAccount();
            
            if ($adminAccount) {
                $startTime = microtime(true);
                $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
                $balance = $bybit->getBalance();
                $latency = round((microtime(true) - $startTime) * 1000, 2);

                $status['bybit'] = [
                    'status' => 'connected',
                    'latency' => $latency . 'ms',
                    'last_sync' => $adminAccount->last_synced_at ?? now(),
                    'balance' => $balance,
                ];
            } else {
                $status['bybit'] = [
                    'status' => 'not_configured',
                    'latency' => 'N/A',
                    'last_sync' => null,
                    'balance' => 0,
                ];
            }
        } catch (\Exception $e) {
            $status['bybit'] = [
                'status' => 'error',
                'latency' => 'N/A',
                'last_sync' => null,
                'error' => $e->getMessage(),
            ];
        }

        return $status;
    }

    /**
     * Get monitoring statistics for last 24 hours
     */
    protected function getMonitoringStatistics()
    {
        $last24Hours = now()->subHours(24);

        // TP Triggered (24h)
        $tpTriggeredCount = Trade::where('status', 'closed')
            ->where('closed_at', '>=', $last24Hours)
            ->where('realized_pnl', '>', 0)
            ->where('exit_price', '>=', DB::raw('take_profit'))
            ->count();

        $tpTriggeredProfit = Trade::where('status', 'closed')
            ->where('closed_at', '>=', $last24Hours)
            ->where('realized_pnl', '>', 0)
            ->where('exit_price', '>=', DB::raw('take_profit'))
            ->sum('realized_pnl');

        // SL Triggered (24h)
        $slTriggeredCount = Trade::where('status', 'closed')
            ->where('closed_at', '>=', $last24Hours)
            ->where('realized_pnl', '<', 0)
            ->where('exit_price', '<=', DB::raw('stop_loss'))
            ->count();

        $slTriggeredLoss = Trade::where('status', 'closed')
            ->where('closed_at', '>=', $last24Hours)
            ->where('realized_pnl', '<', 0)
            ->where('exit_price', '<=', DB::raw('stop_loss'))
            ->sum('realized_pnl');

        // Alerts generated (last hour) - positions at risk
        $alertsGenerated = Position::where('is_active', true)
            ->where('last_updated_at', '>=', now()->subHour())
            ->where(function($q) {
                // At risk: within 15% of stop loss
                $q->whereRaw('ABS(current_price - stop_loss) / ABS(entry_price - stop_loss) < 0.15');
            })
            ->count();

        // Monitor health (successful updates vs failed)
        $totalActivePositions = Position::where('is_active', true)->count();
        $healthyPositions = Position::where('is_active', true)
            ->where('last_updated_at', '>=', now()->subMinutes(10))
            ->count();

        $monitorHealth = $totalActivePositions > 0 
            ? round(($healthyPositions / $totalActivePositions) * 100, 1)
            : 100;

        return [
            'tp_triggered_count' => $tpTriggeredCount,
            'tp_triggered_profit' => $tpTriggeredProfit,
            'sl_triggered_count' => $slTriggeredCount,
            'sl_triggered_loss' => abs($slTriggeredLoss),
            'alerts_generated' => $alertsGenerated,
            'monitor_health_percent' => $monitorHealth,
            'healthy_positions' => $healthyPositions,
            'total_positions' => $totalActivePositions,
        ];
    }

    /**
     * Get recent errors and issues
     */
    protected function getRecentErrors()
    {
        $errors = collect([]);

        // Check for stuck positions (not updated in 2+ hours)
        $stuckPositions = Position::where('is_active', true)
            ->where('last_updated_at', '<', now()->subHours(2))
            ->get();

        foreach ($stuckPositions as $position) {
            $errors->push([
                'type' => 'stuck_position',
                'severity' => 'warning',
                'message' => "Position {$position->symbol} for user {$position->user->name} stuck (2+ hours)",
                'timestamp' => $position->last_updated_at,
                'position_id' => $position->id,
            ]);
        }

        // Check for failed API connections (not synced in 1+ hour)
        $failedAccounts = ExchangeAccount::where('is_active', true)
            ->where('last_synced_at', '<', now()->subHour())
            ->get();

        foreach ($failedAccounts as $account) {
            $errors->push([
                'type' => 'api_connection',
                'severity' => 'error',
                'message' => "Exchange account for {$account->user->name} hasn't synced in 1+ hour",
                'timestamp' => $account->last_synced_at,
                'user_id' => $account->user_id,
            ]);
        }

        // Check for high loss positions (>5% unrealized loss)
        $highLossPositions = Position::where('is_active', true)
            ->where('unrealized_pnl_percent', '<', -5)
            ->get();

        foreach ($highLossPositions as $position) {
            $errors->push([
                'type' => 'high_loss',
                'severity' => 'warning',
                'message' => "High loss position: {$position->symbol} for {$position->user->name} (-{$position->unrealized_pnl_percent}%)",
                'timestamp' => $position->last_updated_at,
                'position_id' => $position->id,
            ]);
        }

        return $errors->sortByDesc('timestamp')->take(10);
    }

    /**
     * Get LIVE current price from Bybit for a position
     */
    protected function getLivePrice($position)
    {
        try {
            Log::info("Attempting to fetch LIVE price for position {$position->id}, symbol: {$position->symbol}, user: {$position->user_id}");
            
            // Get user's exchange account
            $exchangeAccount = $position->user->exchangeAccount;
            
            if (!$exchangeAccount) {
                Log::warning("Position {$position->id}: User {$position->user_id} has no exchange account");
                return null;
            }
            
            if (!$exchangeAccount->is_active) {
                Log::warning("Position {$position->id}: Exchange account {$exchangeAccount->id} is inactive");
                return null;
            }

            Log::info("Position {$position->id}: Using exchange account {$exchangeAccount->id}");

            // Initialize Bybit service with user's credentials
            $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
            
            // Fetch LIVE current price
            $livePrice = $bybit->getCurrentPrice($position->symbol);
            
            if ($livePrice && $livePrice > 0) {
                Log::info("Position {$position->id}: Successfully fetched LIVE price for {$position->symbol}: \${$livePrice}");
                return $livePrice;
            } else {
                Log::warning("Position {$position->id}: Bybit returned invalid price: " . var_export($livePrice, true));
                return null;
            }

        } catch (\Exception $e) {
            Log::error("Position {$position->id}: Exception while fetching LIVE price - " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Recalculate LIVE unrealized P&L based on current price
     */
    protected function recalculateLivePnL($position)
    {
        $currentPrice = $position->current_price;
        $entryPrice = $position->entry_price;
        $quantity = $position->quantity;

        // Calculate P&L based on position side
        if ($position->side === 'long') {
            // LONG: profit when price goes up
            $priceDifference = $currentPrice - $entryPrice;
        } else {
            // SHORT: profit when price goes down
            $priceDifference = $entryPrice - $currentPrice;
        }

        // Calculate unrealized P&L
        $unrealizedPnl = $priceDifference * $quantity;
        $position->unrealized_pnl = $unrealizedPnl;

        // Calculate P&L percentage
        if ($entryPrice > 0) {
            $position->unrealized_pnl_percent = ($priceDifference / $entryPrice) * 100;
        } else {
            $position->unrealized_pnl_percent = 0;
        }

        // Calculate margin used (for reference)
        $position->margin_used = ($quantity * $currentPrice) / ($position->leverage ?? 1);
    }

    /**
     * Calculate progress to TP
     */
    protected function calculateProgressToTP($position)
    {
        if ($position->side === 'long') {
            $totalDistance = $position->take_profit - $position->entry_price;
            $currentDistance = $position->current_price - $position->entry_price;
        } else {
            $totalDistance = $position->entry_price - $position->take_profit;
            $currentDistance = $position->entry_price - $position->current_price;
        }

        if ($totalDistance <= 0) return 0;

        $progress = ($currentDistance / $totalDistance) * 100;
        return max(0, min(100, round($progress, 1)));
    }

    /**
     * Calculate health status
     */
    protected function calculateHealthStatus($position)
    {
        // Check if position is at risk (close to SL)
        if ($position->side === 'long') {
            $slDistance = $position->current_price - $position->stop_loss;
            $totalRange = $position->entry_price - $position->stop_loss;
        } else {
            $slDistance = $position->stop_loss - $position->current_price;
            $totalRange = $position->stop_loss - $position->entry_price;
        }

        if ($totalRange <= 0) return ['status' => 'unknown', 'message' => 'Unknown'];

        $slProximity = ($slDistance / $totalRange) * 100;

        if ($slProximity < 10) {
            return ['status' => 'critical', 'message' => 'Critical - Near SL'];
        } elseif ($slProximity < 25) {
            return ['status' => 'warning', 'message' => 'At Risk'];
        } elseif ($position->unrealized_pnl > 0) {
            return ['status' => 'healthy', 'message' => 'Healthy'];
        } else {
            return ['status' => 'active', 'message' => 'Active'];
        }
    }

    /**
     * System health helper methods
     */
    protected function getOverallSystemStatus()
    {
        // Check critical services
        try {
            DB::connection()->getPdo();
            $dbStatus = true;
        } catch (\Exception $e) {
            return 'critical';
        }

        $tradingEnabled = Setting::get('trading_enabled', true);
        if (!$tradingEnabled) {
            return 'warning';
        }

        return 'healthy';
    }

    protected function calculateSystemUptime()
    {
        // This would require a persistent uptime tracker
        // For now, return a static high uptime
        return '99.9%';
    }

    protected function getAverageResponseTime()
    {
        // Cache the response time calculation
        return Cache::remember('avg_response_time', 60, function () {
            return rand(40, 60) . 'ms'; // Placeholder
        });
    }

    protected function getCPUUsage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 10, 1); // Rough approximation
        }
        return 0;
    }

    protected function getMemoryUsage()
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsed = memory_get_usage(true);
        
        // Convert memory_limit to bytes
        $limit = $this->convertToBytes($memoryLimit);
        
        if ($limit > 0) {
            return round(($memoryUsed / $limit) * 100, 1);
        }
        
        return 0;
    }

    protected function getDiskUsage()
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        
        if ($totalSpace > 0) {
            return round((($totalSpace - $freeSpace) / $totalSpace) * 100, 1);
        }
        
        return 0;
    }

    protected function convertToBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * AJAX endpoint: Refresh all monitors
     */
    public function refreshAllMonitors(Request $request)
    {
        try {
            $forceRefresh = $request->boolean('force_refresh', false);

            if ($forceRefresh) {
                // Clear all caches
                Cache::flush();
            }

            // Trigger position monitoring
            $results = $this->positionMonitor->monitorAllPositions();

            return response()->json([
                'success' => true,
                'message' => 'All monitors refreshed successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to refresh monitors: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh monitors: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX endpoint: Get position details with LIVE data
     */
    public function getPositionDetails($positionId)
    {
        try {
            $position = Position::with(['user', 'user.exchangeAccount', 'trade', 'trade.signal'])
                ->findOrFail($positionId);

            // Fetch LIVE current price from Bybit
            $livePrice = $this->getLivePrice($position);
            
            if ($livePrice !== null) {
                $position->current_price = $livePrice;
                $this->recalculateLivePnL($position);
                $position->is_live_data = true;
            } else {
                $position->is_live_data = false;
            }

            $position->progress_to_tp = $this->calculateProgressToTP($position);
            $position->health_status = $this->calculateHealthStatus($position);

            return response()->json([
                'success' => true,
                'position' => $position,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to get position details: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Position not found',
            ], 404);
        }
    }

    /**
     * AJAX endpoint: Force close position
     */
    public function forceClosePosition($positionId)
    {
        try {
            $position = Position::with(['user', 'trade'])->findOrFail($positionId);

            if (!$position->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Position is already closed',
                ], 400);
            }

            // Close position via BybitService
            $exchangeAccount = $position->user->exchangeAccount;
            if ($exchangeAccount) {
                $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
                $currentPrice = $bybit->getCurrentPrice($position->symbol);
                
                $side = $position->side === 'long' ? 'Buy' : 'Sell';
                $bybit->closePosition($position->symbol, $side);

                $position->close($currentPrice);

                return response()->json([
                    'success' => true,
                    'message' => 'Position closed successfully',
                    'pnl' => $position->trade->realized_pnl,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No exchange account found',
            ], 400);

        } catch (\Exception $e) {
            Log::error("Failed to close position {$positionId}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to close position: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX endpoint: Restart monitor for position
     */
    public function restartMonitor($positionId)
    {
        try {
            $position = Position::findOrFail($positionId);

            // Force update position
            $this->positionMonitor->monitorPosition($position);

            return response()->json([
                'success' => true,
                'message' => 'Monitor restarted successfully',
                'position' => $position->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to restart monitor for position {$positionId}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart monitor: ' . $e->getMessage(),
            ], 500);
        }
    }
}