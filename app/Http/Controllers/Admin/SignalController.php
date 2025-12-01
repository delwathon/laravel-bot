<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use App\Models\Setting;
use App\Services\SignalGeneratorService;
use App\Services\TradePropagationService;
use Illuminate\Http\Request;

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
        $topSignalsCount = (int) Setting::get('signal_top_count', 5);

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
            'avg_confidence' => Signal::whereDate('created_at', '>=', now()->subDays(7))
                ->avg('confidence') ?? 0,
            'success_rate' => $this->calculateSuccessRate(),
        ];
        
        return view('admin.signals.index', compact(
            'stats',
            'recentSignals',
            'signalInterval',
            'topSignalsCount'
        ));
    }

    public function show(Signal $signal)
    {
        $signal->load('trades');
        
        return response()->json([
            'signal' => $signal
        ]);
    }
    
    public function generate(Request $request)
    {
        try {
            // Get settings
            $symbols = Setting::get('signal_pairs', ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT', 'XRPUSDT']);
            $timeframe = str_replace('m', '', Setting::get('signal_primary_timeframe', '15m'));
            $minConfidence = (int) Setting::get('signal_min_confidence', 70);

            $signals = $this->signalGenerator->generateSignals($symbols, $timeframe, $minConfidence);

            if (empty($signals)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No signals generated. Market conditions do not meet criteria.'
                    ]);
                }

                return redirect()->route('admin.signals.index')
                    ->with('info', 'No signals generated. Market conditions do not meet criteria.');
            }

            $message = count($signals) . ' signal(s) generated successfully!';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count' => count($signals)
                ]);
            }

            return redirect()->route('admin.signals.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Signal generation failed: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Signal generation failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.signals.index')
                ->with('error', 'Signal generation failed: ' . $e->getMessage());
        }
    }

    public function execute(Request $request, Signal $signal)
    {
        try {
            if (!in_array($signal->status, ['active', 'pending'])) {
                return back()->with('error', 'Signal is not active and cannot be executed.');
            }

            $results = $this->tradePropagation->propagateSignalToAllUsers($signal);

            $signal->update([
                'status' => 'executed',
                'executed_at' => now(),
            ]);

            $message = "Signal executed: {$results['successful']} successful, {$results['failed']} failed out of {$results['total']} users.";

            return redirect()->route('admin.signals.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Signal execution failed: ' . $e->getMessage());
            return back()->with('error', 'Signal execution failed: ' . $e->getMessage());
        }
    }

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

    protected function exportToCsv($signals)
    {
        $filename = 'signals_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($signals) {
            $file = fopen('php://output', 'w');

            // CSV headers
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
                'Executed At'
            ]);

            // CSV rows
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
                    $signal->executed_at ? $signal->executed_at->toDateTimeString() : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}