<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Services\TradePropagationService;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    protected $tradePropagation;

    public function __construct(TradePropagationService $tradePropagation)
    {
        $this->tradePropagation = $tradePropagation;
    }

    public function index()
    {
        $trades = Trade::with(['user', 'signal'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total_trades' => Trade::count(),
            'open_trades' => Trade::where('status', 'open')->count(),
            'closed_trades' => Trade::where('status', 'closed')->count(),
            'total_profit' => Trade::where('status', 'closed')->sum('realized_pnl'),
            'today_trades' => Trade::whereDate('created_at', today())->count(),
            'win_rate' => $this->calculateWinRate(),
        ];

        return view('admin.trades.index', compact('trades', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string',
            'type' => 'required|in:long,short',
            'entry_price' => 'required|numeric|min:0',
            'stop_loss' => 'required|numeric|min:0',
            'take_profit' => 'required|numeric|min:0',
            'position_size_percent' => 'nullable|numeric|min:1|max:100',
        ]);

        try {
            $positionSize = $validated['position_size_percent'] ?? 5;

            $results = $this->tradePropagation->propagateManualTrade(
                $validated['symbol'],
                $validated['type'],
                $validated['entry_price'],
                $validated['stop_loss'],
                $validated['take_profit'],
                $positionSize
            );

            $message = "Trade propagated: {$results['successful']} successful, {$results['failed']} failed out of {$results['total']} users.";

            if ($results['failed'] > 0) {
                $errorDetails = collect($results['errors'])->pluck('error')->unique()->implode(', ');
                $message .= " Errors: {$errorDetails}";
            }

            return redirect()->route('admin.trades.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create and propagate trade: ' . $e->getMessage());
        }
    }

    public function destroy(Trade $trade)
    {
        try {
            if ($trade->status === 'open') {
                return back()->with('error', 'Cannot delete an open trade. Please close it first.');
            }

            $trade->delete();

            return redirect()->route('admin.trades.index')
                ->with('success', 'Trade deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete trade: ' . $e->getMessage());
        }
    }

    protected function calculateWinRate()
    {
        $closedTrades = Trade::where('status', 'closed')->count();

        if ($closedTrades === 0) {
            return 0;
        }

        $winningTrades = Trade::where('status', 'closed')
            ->where('realized_pnl', '>', 0)
            ->count();

        return round(($winningTrades / $closedTrades) * 100, 2);
    }
}