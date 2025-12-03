<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\Setting;
use App\Models\ExchangeAccount;
use App\Models\User;
use App\Services\TradePropagationService;
use App\Services\BybitService;
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
        // Get admin trades (recent 50)
        $trades = Trade::whereHas('user', function($query) {
                $query->where('is_admin', true);
            })
            ->with(['signal'])
            ->latest()
            ->paginate(50);

        // Get position size and leverage from settings
        $positionSize = Setting::get('signal_position_size', 5);
        $leverage = Setting::get('signal_leverage', 'Max');
        
        // Convert "Max" to numeric for display if needed
        $leverageNumeric = strtolower($leverage) === 'max' ? 100 : (float) $leverage;

        // Get admin balance
        $adminBalance = 0;
        try {
            $adminAccount = ExchangeAccount::getBybitAccount();
            if ($adminAccount) {
                $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
                $adminBalance = $bybit->getBalance();
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch admin balance: ' . $e->getMessage());
        }

        // Calculate stats
        $stats = $this->calculateStats();

        return view('admin.trades.index', compact('trades', 'stats', 'positionSize', 'leverage', 'adminBalance', 'leverageNumeric'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string',
            'type' => 'required|in:long,short',
            'order_type' => 'required|in:Market,Limit',
            'entry_price' => 'required|numeric|min:0',
            'stop_loss' => 'required|numeric|min:0',
            'take_profit' => 'required|numeric|min:0',
        ]);

        try {
            // Get position size from settings (not from form)
            $positionSize = Setting::get('signal_position_size', 5);

            $results = $this->tradePropagation->propagateManualTrade(
                $validated['symbol'],
                $validated['type'],
                $validated['entry_price'],
                $validated['stop_loss'],
                $validated['take_profit'],
                $positionSize,
                $validated['order_type']
            );

            $message = "Trade propagated: {$results['successful']} successful, {$results['failed']} failed out of {$results['total']} users.";

            if ($results['failed'] > 0) {
                $errorDetails = collect($results['errors'])->pluck('error')->unique()->implode(', ');
                $message .= " Errors: {$errorDetails}";
            }

            return redirect()->route('admin.trades.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Trade propagation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create and propagate trade: ' . $e->getMessage());
        }
    }

    public function destroy(Trade $trade)
    {
        try {
            if ($trade->status !== 'open') {
                return back()->with('error', 'Can only close open positions');
            }

            if (!$trade->signal_id) {
                return back()->with('error', 'Cannot close: No signal associated with this trade');
            }

            // Use the new method to close all positions by signal_id
            $results = $this->tradePropagation->closeAllPositionsBySignal($trade->signal_id);

            $message = "Closed {$results['closed']} positions (Admin + Users)";
            
            if ($results['failed'] > 0) {
                $message .= " | {$results['failed']} positions failed to close";
            }

            return redirect()->route('admin.trades.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Failed to close position: ' . $e->getMessage());
            return back()->with('error', 'Failed to close position: ' . $e->getMessage());
        }
    }

    public function closeAll()
    {
        try {
            // Get all unique signal_ids from open admin trades
            $signalIds = Trade::where('status', 'open')
                ->whereHas('user', function($query) {
                    $query->where('is_admin', true);
                })
                ->whereNotNull('signal_id')
                ->distinct()
                ->pluck('signal_id');

            if ($signalIds->isEmpty()) {
                return back()->with('info', 'No open positions to close');
            }

            $totalClosed = 0;
            $totalFailed = 0;

            // Close all positions for each signal
            foreach ($signalIds as $signalId) {
                try {
                    $results = $this->tradePropagation->closeAllPositionsBySignal($signalId);
                    $totalClosed += $results['closed'];
                    $totalFailed += $results['failed'];
                } catch (\Exception $e) {
                    $totalFailed++;
                    \Log::error("Failed to close positions for signal {$signalId}: " . $e->getMessage());
                }
            }

            $message = "Closed {$totalClosed} positions across all users";
            if ($totalFailed > 0) {
                $message .= " ({$totalFailed} errors)";
            }

            return redirect()->route('admin.trades.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Close all failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to close all positions: ' . $e->getMessage());
        }
    }

    public function details(Trade $trade)
    {
        $trade->load(['signal', 'position', 'user']);
        
        $userTrades = $trade->signal ? $trade->signal->trades()->count() : 0;
        $successfulTrades = $trade->signal ? $trade->signal->trades()->where('status', '!=', 'failed')->count() : 0;
        $successRate = $userTrades > 0 ? ($successfulTrades / $userTrades) * 100 : 0;
        
        return response()->json([
            'id' => $trade->id,
            'symbol' => $trade->symbol,
            'type' => $trade->type,
            'entry_price' => $trade->entry_price,
            'stop_loss' => $trade->stop_loss,
            'take_profit' => $trade->take_profit,
            'quantity' => $trade->quantity,
            'leverage' => $trade->leverage,
            'status' => $trade->status,
            'realized_pnl' => $trade->realized_pnl ?? 0,
            'user_trades_total' => $userTrades,
            'user_trades_successful' => $successfulTrades,
            'success_rate' => number_format($successRate, 1),
            'created_at' => $trade->created_at->format('M d, Y H:i:s'),
        ]);
    }

    public function previewCalculation(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string',
            'type' => 'required|in:long,short',
            'entry_price' => 'required|numeric|min:0',
            'stop_loss' => 'required|numeric|min:0',
            'take_profit' => 'required|numeric|min:0',
        ]);

        try {
            // Get settings
            $positionSize = \App\Models\Setting::get('signal_position_size', 5);
            $leverageSetting = \App\Models\Setting::get('signal_leverage', 'Max');
            
            // Get admin account and balance
            $adminAccount = ExchangeAccount::getBybitAccount();
            if (!$adminAccount) {
                return response()->json(['error' => 'No admin account configured'], 400);
            }
            
            $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
            $balance = $bybit->getBalance();
            
            // Get actual leverage for this specific symbol
            if (strtolower($leverageSetting) === 'max') {
                $leverage = $bybit->getMaxLeverage($validated['symbol']);
            } else {
                $leverage = (float) $leverageSetting;
            }
            
            // Bybit's ACTUAL calculation formula:
            // 1. Risk Amount = Balance × Position Size %
            $riskAmount = ($balance * $positionSize) / 100;
            
            // 2. Position Value = Risk Amount × Leverage
            $positionValue = $riskAmount * $leverage;
            
            // 3. Quantity = Position Value / Entry Price
            $quantity = $positionValue / $validated['entry_price'];
            
            // 4. Margin Required = Position Value / Leverage (should equal risk amount)
            $marginRequired = $positionValue / $leverage;
            
            // Calculate distances and percentages for display
            $stopLossDistance = abs($validated['entry_price'] - $validated['stop_loss']);
            $stopLossPercent = ($stopLossDistance / $validated['entry_price']) * 100;
            
            // P&L calculations
            $takeProfitDistance = abs($validated['take_profit'] - $validated['entry_price']);
            $potentialProfit = $takeProfitDistance * $quantity;
            $potentialLoss = $stopLossDistance * $quantity;
            
            return response()->json([
                'leverage' => $leverage,
                'risk_amount' => $riskAmount,
                'stop_loss_distance' => $stopLossDistance,
                'stop_loss_percent' => $stopLossPercent,
                'position_value' => $positionValue,
                'quantity' => $quantity,
                'margin_required' => $marginRequired,
                'potential_profit' => $potentialProfit,
                'potential_loss' => $potentialLoss,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Preview calculation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Calculation failed: ' . $e->getMessage()], 500);
        }
    }

    protected function calculateStats()
    {
        // Total active positions (across all users)
        $activeTrades = Trade::where('status', 'open')->count();

        // 24h volume
        $totalVolume = Trade::where('created_at', '>=', now()->subDay())
            ->sum(\DB::raw('entry_price * quantity'));

        // Volume change (comparison to previous 24h)
        $previousVolume = Trade::whereBetween('created_at', [now()->subDays(2), now()->subDay()])
            ->sum(\DB::raw('entry_price * quantity'));
        $volumeChange = $previousVolume > 0 ? (($totalVolume - $previousVolume) / $previousVolume) * 100 : 0;

        // Total profit (last 24h)
        $totalProfit = Trade::where('status', 'closed')
            ->where('closed_at', '>=', now()->subDay())
            ->sum('realized_pnl');

        // Affected users
        $affectedUsers = User::where('is_admin', false)
            ->whereHas('exchangeAccount', function($query) {
                $query->where('is_active', true);
            })
            ->count();

        // Admin trades today
        $adminTrades24h = Trade::whereHas('user', function($query) {
                $query->where('is_admin', true);
            })
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $adminLongCount = Trade::whereHas('user', function($query) {
                $query->where('is_admin', true);
            })
            ->where('type', 'long')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $adminShortCount = Trade::whereHas('user', function($query) {
                $query->where('is_admin', true);
            })
            ->where('type', 'short')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // User trades today
        $userTrades24h = Trade::whereHas('user', function($query) {
                $query->where('is_admin', false);
            })
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Active users (with open positions)
        $activeUsersCount = Trade::where('status', 'open')
            ->whereHas('user', function($query) {
                $query->where('is_admin', false);
            })
            ->distinct('user_id')
            ->count('user_id');

        // Win rate calculation
        $closedTrades = Trade::where('status', 'closed')
            ->where('closed_at', '>=', now()->subDays(7))
            ->count();
        
        $winningTrades = Trade::where('status', 'closed')
            ->where('closed_at', '>=', now()->subDays(7))
            ->where('realized_pnl', '>', 0)
            ->count();

        $winRate = $closedTrades > 0 ? ($winningTrades / $closedTrades) * 100 : 0;

        // Open admin positions
        $openAdminPositions = Trade::whereHas('user', function($query) {
                $query->where('is_admin', true);
            })
            ->where('status', 'open')
            ->count();

        return [
            'active_trades' => $activeTrades,
            'total_volume' => $totalVolume,
            'volume_change' => $volumeChange,
            'total_profit' => $totalProfit,
            'affected_users' => $affectedUsers,
            'admin_trades_24h' => $adminTrades24h,
            'admin_trades_today' => $adminTrades24h,
            'long_trades' => $adminLongCount,
            'short_trades' => $adminShortCount,
            'admin_long_count' => $adminLongCount,
            'admin_short_count' => $adminShortCount,
            'user_trades_24h' => $userTrades24h,
            'user_trades_today' => $userTrades24h,
            'active_users' => $activeUsersCount,
            'win_rate' => $winRate,
            'winning_trades' => $winningTrades,
            'closed_trades' => $closedTrades,
            'success_rate' => $winRate,
            'open_trades' => $openAdminPositions,
        ];
    }
}