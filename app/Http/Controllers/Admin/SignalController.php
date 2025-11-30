<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signal;
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

    public function index()
    {
        $recentSignals = Signal::with('trades')
            ->latest()
            ->take(20)
            ->get();

        $stats = [
            'total_signals_today' => Signal::whereDate('created_at', today())->count(),
            'pending_signals' => Signal::where('status', 'pending')->count(),
            'executed_signals' => Signal::where('status', 'executed')->count(),
            'expired_signals' => Signal::where('status', 'expired')->count(),
            'avg_confidence' => Signal::whereDate('created_at', '>=', now()->subDays(7))
                ->avg('confidence') ?? 0,
            'success_rate' => $this->calculateSuccessRate(),
        ];
        
        return view('admin.signals.index', compact('stats', 'recentSignals'));
    }
    
    public function generate(Request $request)
    {
        try {
            $symbols = $request->input('symbols', ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT', 'XRPUSDT']);
            $timeframe = $request->input('timeframe', '15');
            $minConfidence = $request->input('min_confidence', 70);

            $signals = $this->signalGenerator->generateSignals($symbols, $timeframe, $minConfidence);

            if (empty($signals)) {
                return redirect()->route('admin.signals.index')
                    ->with('info', 'No signals generated. Market conditions do not meet criteria.');
            }

            return redirect()->route('admin.signals.index')
                ->with('success', count($signals) . ' signal(s) generated successfully!');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.signals.index')
                ->with('error', 'Signal generation failed: ' . $e->getMessage());
        }
    }

    public function execute(Request $request, Signal $signal)
    {
        try {
            if ($signal->status !== 'active') {
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
            return back()->with('error', 'Signal execution failed: ' . $e->getMessage());
        }
    }

    public function cancel(Signal $signal)
    {
        try {
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
}