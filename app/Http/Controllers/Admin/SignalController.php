<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use App\Models\Setting;
use App\Services\SignalGeneratorService;
use App\Services\TradePropagationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignalController extends Controller
{
    protected $signalGenerator;
    protected $tradePropagation;

    public function __construct(SignalGeneratorService $signalGenerator, TradePropagationService $tradePropagation)
    {
        $this->signalGenerator = $signalGenerator;
        $this->tradePropagation = $tradePropagation;
    }

    public function index(Request $request)
    {
        // Get signal generator settings
        $signalInterval = (int) Setting::get('signal_interval', 15);
        $topSignalsCount = (int) Setting::get('signal_top_count', 10);
        
        // Get additional settings for view
        $autoExecute = (bool) Setting::get('signal_auto_execute', true);
        $autoExecuteCount = (int) Setting::get('signal_auto_execute_count', 3);
        $useDynamicPairs = (bool) Setting::get('signal_use_dynamic_pairs', false);
        $minVolume = (int) Setting::get('signal_min_volume', 5000000);
        $minConfidence = (int) Setting::get('signal_min_confidence', 70);

        // Build query with filters
        $query = Signal::with('trades');

        // Direction filter
        if ($request->filled('direction')) {
            $query->where('type', $request->direction);
        }

        // Confidence filter
        if ($request->filled('confidence')) {
            switch ($request->confidence) {
                case 'high':
                    $query->where('confidence', '>=', 80);
                    break;
                case 'medium':
                    $query->whereBetween('confidence', [60, 79]);
                    break;
                case 'low':
                    $query->where('confidence', '<', 60);
                    break;
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Timeframe filter
        $timeframe = $request->get('timeframe', 'today');
        switch ($timeframe) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case '24h':
                $query->where('created_at', '>=', now()->subDay());
                break;
            case '7d':
                $query->where('created_at', '>=', now()->subDays(7));
                break;
            case '30d':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
        }

        // Export to CSV if requested
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportToCsv($query->get());
        }

        // Paginate results
        $recentSignals = $query->latest()->paginate(20)->withQueryString();

        // Calculate stats
        $stats = [
            'total_signals_today' => Signal::whereDate('created_at', today())->count(),
            'pending_signals' => Signal::where('status', 'pending')->count(),
            'executed_signals' => Signal::where('status', 'executed')->count(),
            'expired_signals' => Signal::where('status', 'expired')->count(),
            'skipped_signals' => Signal::where('status', 'skipped')->count(),
            'avg_confidence' => Signal::whereDate('created_at', '>=', now()->subDays(7))
                ->avg('confidence') ?? 0,
            'success_rate' => $this->calculateSuccessRate(),
        ];
        
        return view('admin.signals.index', compact(
            'stats',
            'recentSignals',
            'signalInterval',
            'topSignalsCount',
            'autoExecute',
            'autoExecuteCount',
            'useDynamicPairs',
            'minVolume',
            'minConfidence'
        ));
    }

    /**
     * Get signal details for modal display
     */
    public function details(Signal $signal)
    {
        try {
            $signal->load('trades');
            
            $riskReward = $signal->risk_reward_ratio 
                ? number_format($signal->risk_reward_ratio, 1) 
                : '1:' . number_format(abs(($signal->take_profit - $signal->entry_price) / ($signal->entry_price - $signal->stop_loss)), 1);
            
            return response()->json([
                'success' => true,
                'symbol' => $signal->symbol,
                'pattern' => $signal->pattern,
                'type' => $signal->type,
                'order_type' => $signal->order_type ?? 'Market',
                'confidence' => number_format($signal->confidence, 0),
                'entry_price' => number_format($signal->entry_price, 2),
                'take_profit' => number_format($signal->take_profit, 2),
                'stop_loss' => number_format($signal->stop_loss, 2),
                'risk_reward_ratio' => $riskReward,
                'status' => $signal->status,
                'timeframe' => $signal->timeframe,
                'created_at' => $signal->created_at->toIso8601String(),
                'trades_count' => $signal->trades->count(),
                'notes' => $signal->notes,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load signal details: ' . $e->getMessage(), [
                'signal_id' => $signal->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load signal details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate new signals
     * - Stores top N signals (configurable via signal_top_count)
     * - Auto-executes only top M signals (configurable via signal_auto_execute_count)
     */
    public function generate(Request $request)
    {
        try {
            Log::info('[SignalController] Generate signals request started');
            
            $useDynamicPairs = (bool) Setting::get('signal_use_dynamic_pairs', false);
            $symbols = null;
            
            if (!$useDynamicPairs) {
                $symbols = Setting::get('signal_pairs', ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT', 'XRPUSDT']);
            }
            
            $timeframe = Setting::get('signal_primary_timeframe', '15');
            $minConfidence = (int) Setting::get('signal_min_confidence', 70);
            $topSignalsCount = (int) Setting::get('signal_top_count', 10);
            $autoExecute = (bool) Setting::get('signal_auto_execute', true);
            $autoExecuteCount = (int) Setting::get('signal_auto_execute_count', 3);

            Log::info('[SignalController] Signal generation settings', [
                'use_dynamic_pairs' => $useDynamicPairs,
                'symbols' => $symbols,
                'timeframe' => $timeframe,
                'min_confidence' => $minConfidence,
                'top_signals_count' => $topSignalsCount,
                'auto_execute' => $autoExecute,
                'auto_execute_count' => $autoExecuteCount,
            ]);

            $signalsData = $this->signalGenerator->generateSignals($symbols, $timeframe, $minConfidence);

            if (empty($signalsData)) {
                Log::info('[SignalController] No signals generated - market conditions not met');
                
                return response()->json([
                    'success' => false,
                    'message' => 'No signals generated. Market conditions do not meet criteria.'
                ]);
            }

            // Sort by confidence (highest first)
            usort($signalsData, function($a, $b) {
                return $b['confidence'] <=> $a['confidence'];
            });
            
            // Store top N signals
            $signalsData = array_slice($signalsData, 0, $topSignalsCount);

            Log::info('[SignalController] Creating ' . count($signalsData) . ' signals in database');

            $createdSignals = [];
            foreach ($signalsData as $signalData) {
                $signal = $this->signalGenerator->createSignal($signalData, $timeframe);
                $createdSignals[] = $signal;
                
                Log::info('[SignalController] Signal created', [
                    'id' => $signal->id,
                    'symbol' => $signal->symbol,
                    'type' => $signal->type,
                    'confidence' => $signal->confidence,
                    'order_type' => $signal->order_type,
                ]);
            }

            $executedCount = 0;
            $skippedCount = 0;
            $cancelledOrdersTotal = 0;
            $closedPositionsTotal = 0;

            if ($autoExecute) {
                Log::info('[SignalController] Auto-execute enabled, processing top signals');
                
                $signalsToExecute = array_slice($createdSignals, 0, $autoExecuteCount);
                
                foreach ($signalsToExecute as $signal) {
                    try {
                        Log::info("[SignalController] Checking conflicts for signal {$signal->id}");
                        
                        // Check admin conflicts FIRST
                        $conflictCheck = $this->tradePropagation->checkAdminConflicts($signal);
                        
                        if ($conflictCheck['action'] === 'skip') {
                            Log::warning("[SignalController] Signal {$signal->id} skipped", [
                                'reason' => $conflictCheck['reason']
                            ]);
                            
                            $signal->update([
                                'status' => 'skipped',
                                'notes' => $conflictCheck['reason']
                            ]);
                            
                            $skippedCount++;
                            continue;
                        }
                        
                        if ($conflictCheck['action'] === 'cancel_and_execute') {
                            Log::info("[SignalController] Cancelling existing orders for signal {$signal->id}");
                            
                            $cancelResults = $this->tradePropagation->cancelAllPendingOrdersBySymbol($signal->symbol);
                            $cancelledOrdersTotal += $cancelResults['cancelled'];
                            
                            Log::info("[SignalController] Cancelled {$cancelResults['cancelled']} orders");
                            
                            $signal->update([
                                'notes' => "Cancelled {$cancelResults['cancelled']} existing orders. " . $conflictCheck['reason']
                            ]);
                        }
                        
                        if ($conflictCheck['action'] === 'close_and_execute') {
                            Log::info("[SignalController] Closing existing positions for signal {$signal->id}");
                            
                            $closeResults = $this->tradePropagation->closeAllPositionsBySymbol($signal->symbol);
                            $closedPositionsTotal += $closeResults['closed'];
                            
                            Log::info("[SignalController] Closed {$closeResults['closed']} positions");
                            
                            $signal->update([
                                'notes' => "Closed {$closeResults['closed']} existing positions. " . $conflictCheck['reason']
                            ]);
                        }
                        
                        Log::info("[SignalController] Attempting to execute signal {$signal->id}");
                        
                        // Execute admin trade FIRST
                        $adminResult = $this->tradePropagation->executeAdminTrade(
                            $signal, 
                            Setting::get('signal_position_size', 5),
                            $signal->order_type ?? Setting::get('signal_order_type', 'Market')
                        );
                        
                        if (!$adminResult['success']) {
                            Log::error("[SignalController] Admin execution failed for signal {$signal->id}", [
                                'error' => $adminResult['error']
                            ]);
                            
                            $signal->update(['status' => 'failed']);
                            continue;
                        }
                        
                        // Admin succeeded - propagate to users
                        Log::info("[SignalController] Admin execution successful, propagating to users");
                        
                        $userResults = $this->tradePropagation->propagateSignalToAllUsers(
                            $signal, 
                            $signal->order_type ?? Setting::get('signal_order_type', 'Market')
                        );
                        
                        Log::info("[SignalController] Signal {$signal->id} executed", [
                            'users_successful' => $userResults['successful'],
                            'users_failed' => $userResults['failed'],
                        ]);

                        $signal->update([
                            'status' => 'executed',
                            'executed_at' => now(),
                        ]);
                        
                        $executedCount++;
                        
                    } catch (\Exception $e) {
                        Log::error("[SignalController] Signal execution exception for signal {$signal->id}", [
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        
                        $signal->update([
                            'status' => 'failed',
                            'notes' => 'Execution error: ' . $e->getMessage()
                        ]);
                    }
                }
                
                // Mark remaining signals as active (not executed)
                $remainingSignals = array_slice($createdSignals, $autoExecuteCount);
                foreach ($remainingSignals as $signal) {
                    $signal->update(['status' => 'active']);
                }
            } else {
                // Mark all signals as active for manual execution
                foreach ($createdSignals as $signal) {
                    $signal->update(['status' => 'active']);
                }
            }

            $message = count($createdSignals) . ' signal(s) generated successfully!';
            
            if ($autoExecute) {
                $message .= " {$executedCount} executed";
                if ($skippedCount > 0) {
                    $message .= ", {$skippedCount} skipped";
                }
                if ($cancelledOrdersTotal > 0) {
                    $message .= " (cancelled {$cancelledOrdersTotal} stale orders)";
                }
                if ($closedPositionsTotal > 0) {
                    $message .= " (closed {$closedPositionsTotal} opposite positions)";
                }
            }

            Log::info('[SignalController] Signal generation completed', [
                'generated' => count($createdSignals),
                'executed' => $executedCount,
                'skipped' => $skippedCount,
                'cancelled_orders' => $cancelledOrdersTotal,
                'closed_positions' => $closedPositionsTotal,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => count($createdSignals),
                'executed' => $executedCount,
                'skipped' => $skippedCount,
                'cancelled_orders' => $cancelledOrdersTotal,
                'closed_positions' => $closedPositionsTotal,
            ]);
                
        } catch (\Exception $e) {
            Log::error('[SignalController] Signal generation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Signal generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute a signal manually
     */
    public function execute(Request $request, Signal $signal)
    {
        try {
            if (!in_array($signal->status, ['active', 'pending'])) {
                return back()->with('error', 'Signal is not active and cannot be executed.');
            }

            $orderType = $signal->order_type ?? Setting::get('signal_order_type', 'Market');
            
            Log::info('[SignalController] Manual signal execution started', [
                'signal_id' => $signal->id,
                'order_type' => $orderType,
            ]);
            
            // Check admin conflicts FIRST
            $conflictCheck = $this->tradePropagation->checkAdminConflicts($signal);
            
            if ($conflictCheck['action'] === 'skip') {
                Log::warning("[SignalController] Manual execution skipped", [
                    'signal_id' => $signal->id,
                    'reason' => $conflictCheck['reason']
                ]);
                
                $signal->update([
                    'status' => 'skipped',
                    'notes' => $conflictCheck['reason']
                ]);
                
                return back()->with('warning', $conflictCheck['reason']);
            }
            
            $cancelledOrders = 0;
            $closedPositions = 0;
            
            if ($conflictCheck['action'] === 'cancel_and_execute') {
                Log::info("[SignalController] Cancelling existing orders before execution");
                
                $cancelResults = $this->tradePropagation->cancelAllPendingOrdersBySymbol($signal->symbol);
                $cancelledOrders = $cancelResults['cancelled'];
                
                Log::info("[SignalController] Cancelled {$cancelledOrders} orders");
                
                $signal->update([
                    'notes' => "Cancelled {$cancelledOrders} existing orders. " . $conflictCheck['reason']
                ]);
            }
            
            if ($conflictCheck['action'] === 'close_and_execute') {
                Log::info("[SignalController] Closing existing positions before execution");
                
                $closeResults = $this->tradePropagation->closeAllPositionsBySymbol($signal->symbol);
                $closedPositions = $closeResults['closed'];
                
                Log::info("[SignalController] Closed {$closedPositions} positions");
                
                $signal->update([
                    'notes' => "Closed {$closedPositions} existing positions. " . $conflictCheck['reason']
                ]);
            }
            
            $adminResult = $this->tradePropagation->executeAdminTrade(
                $signal, 
                Setting::get('signal_position_size', 5),
                $orderType
            );
            
            if (!$adminResult['success']) {
                throw new \Exception('Failed to execute admin trade: ' . $adminResult['error']);
            }
            
            $userResults = $this->tradePropagation->propagateSignalToAllUsers($signal, $orderType);

            $signal->update([
                'status' => 'executed',
                'executed_at' => now(),
            ]);

            $message = "Signal executed: Admin + {$userResults['successful']} users successful, {$userResults['failed']} failed out of {$userResults['total']} users.";
            
            if ($cancelledOrders > 0) {
                $message .= " Cancelled {$cancelledOrders} stale orders.";
            }
            
            if ($closedPositions > 0) {
                $message .= " Closed {$closedPositions} opposite positions.";
            }

            Log::info('[SignalController] Signal execution completed', [
                'signal_id' => $signal->id,
                'users_successful' => $userResults['successful'],
                'users_failed' => $userResults['failed'],
                'cancelled_orders' => $cancelledOrders,
                'closed_positions' => $closedPositions,
            ]);

            return redirect()->route('admin.signals.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('[SignalController] Signal execution failed', [
                'signal_id' => $signal->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Signal execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a signal
     */
    public function cancel(Signal $signal)
    {
        try {
            if (!in_array($signal->status, ['active', 'pending'])) {
                return back()->with('error', 'Cannot cancel a signal that is ' . $signal->status);
            }

            $signal->update(['status' => 'cancelled']);

            return back()->with('success', 'Signal cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel signal: ' . $e->getMessage());
        }
    }

    /**
     * Calculate success rate
     */
    protected function calculateSuccessRate()
    {
        $executedSignals = Signal::where('status', 'executed')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->withCount(['trades' => function($query) {
                $query->where('status', 'closed')
                      ->where('realized_pnl', '>', 0);
            }])
            ->get();

        if ($executedSignals->isEmpty()) {
            return 0;
        }

        $totalTrades = 0;
        $winningTrades = 0;

        foreach ($executedSignals as $signal) {
            $totalTrades += $signal->trades()->where('status', 'closed')->count();
            $winningTrades += $signal->trades_count;
        }

        if ($totalTrades === 0) {
            return 0;
        }

        return round(($winningTrades / $totalTrades) * 100, 2);
    }

    /**
     * Export signals to CSV
     */
    protected function exportToCsv($signals)
    {
        $filename = 'signals_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($signals) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Created At',
                'Symbol',
                'Type',
                'Pattern',
                'Confidence',
                'Entry Price',
                'Stop Loss',
                'Take Profit',
                'Risk/Reward',
                'Timeframe',
                'Status',
                'Executed At',
                'Notes'
            ]);

            foreach ($signals as $signal) {
                fputcsv($file, [
                    $signal->id,
                    $signal->created_at->toDateTimeString(),
                    $signal->symbol,
                    $signal->type,
                    $signal->pattern,
                    $signal->confidence,
                    $signal->entry_price,
                    $signal->stop_loss,
                    $signal->take_profit,
                    '1:' . $signal->risk_reward_ratio,
                    $signal->timeframe,
                    $signal->status,
                    $signal->executed_at ? $signal->executed_at->toDateTimeString() : '',
                    $signal->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}