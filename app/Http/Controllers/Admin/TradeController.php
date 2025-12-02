<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Trade;
use App\Models\User;
use App\Models\ExchangeAccount;
use App\Services\TradePropagationService;
use App\Services\BybitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    protected $tradePropagation;

    public function __construct(TradePropagationService $tradePropagation)
    {
        $this->tradePropagation = $tradePropagation;
    }

    public function index()
    {
        // Get only ADMIN trades (not user trades)
        $trades = Trade::with(['signal.trades', 'position', 'user'])
            ->whereHas('user', function($query) {
                $query->where('is_admin', true);
            })
            ->latest()
            ->paginate(20);

        // Calculate comprehensive stats
        $stats = $this->calculateStats();
        
        // Get position size and leverage from settings
        $positionSize = Setting::get('signal_position_size', 5);
        $leverage = Setting::get('signal_leverage', 'Max');
        
        // Get real admin balance
        $adminBalance = 0;
        $leverageNumeric = 100; // Default for "Max"
        
        try {
            $adminAccount = ExchangeAccount::getBybitAccount();
            if ($adminAccount) {
                $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
                $adminBalance = $bybit->getBalance();
                
                // If leverage is "Max", get the actual max for a sample symbol (ETHUSDT)
                // if (strtolower($leverage) === 'max') {
                //     try {
                //         $leverageNumeric = $bybit->getMaxLeverage('ETHUSDT');
                //     } catch (\Exception $e) {
                //         $leverageNumeric = 100; // Default fallback
                //     }
                // } else {
                //     $leverageNumeric = (int) $leverage;
                // }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch admin balance: ' . $e->getMessage());
        }
        
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

            $results = $this->closeTradeAndPropagate($trade);

            $message = "Position closed. Admin P&L: $" . number_format($trade->realized_pnl, 2);
            
            if (isset($results['users_closed'])) {
                $message .= " | {$results['users_closed']} user positions closed";
            }
            
            if (!empty($results['user_errors'])) {
                $errorCount = count($results['user_errors']);
                $message .= " | {$errorCount} user positions failed";
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
            $openTrades = Trade::where('status', 'open')
                ->whereHas('user', function($query) {
                    $query->where('is_admin', true);
                })
                ->get();

            $closed = 0;
            $errors = 0;

            foreach ($openTrades as $trade) {
                try {
                    $this->closeTradeAndPropagate($trade);
                    $closed++;
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error("Failed to close trade {$trade->id}: " . $e->getMessage());
                }
            }

            $message = "Closed {$closed} admin positions";
            if ($errors > 0) {
                $message .= " ({$errors} errors)";
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

    protected function closeTradeAndPropagate(Trade $adminTrade)
    {
        if (!$adminTrade->signal) {
            throw new \Exception('No signal associated with this trade');
        }

        // Close admin trade
        $adminAccount = ExchangeAccount::getBybitAccount();
        if ($adminAccount) {
            $bybit = new BybitService($adminAccount->api_key, $adminAccount->api_secret);
            
            $side = $adminTrade->type === 'long' ? 'Buy' : 'Sell';
            $closeResult = $bybit->closePosition($adminTrade->symbol, $side);
            
            $currentPrice = $bybit->getCurrentPrice($adminTrade->symbol);
            
            // Get actual realized P&L from Bybit
            $realizedPnl = $closeResult['closedPnl'] ?? 0;
            
            // If closeResult doesn't have P&L, try fetching from closed positions
            if ($realizedPnl == 0) {
                $realizedPnl = $bybit->getClosedPnL($adminTrade->symbol);
            }
            
            $adminTrade->update([
                'exit_price' => $currentPrice,
                'status' => 'closed',
                'closed_at' => now(),
                'realized_pnl' => $realizedPnl, // Use actual Bybit P&L
            ]);
            
            // Calculate P&L percent
            $pnlPercent = 0;
            if ($adminTrade->entry_price && $adminTrade->quantity) {
                $pnlPercent = ($realizedPnl / ($adminTrade->entry_price * $adminTrade->quantity)) * 100;
            }
            $adminTrade->update(['realized_pnl_percent' => $pnlPercent]);
            
            if ($adminTrade->position) {
                $adminTrade->position->close($currentPrice);
            }
            
            \Log::info("Admin trade closed", [
                'trade_id' => $adminTrade->id,
                'symbol' => $adminTrade->symbol,
                'realized_pnl' => $realizedPnl,
                'exit_price' => $currentPrice,
            ]);
        }

        // Close all user trades for this signal
        $userTrades = $adminTrade->signal->trades()
            ->where('status', 'open')
            ->whereHas('user', function($query) {
                $query->where('is_admin', false);
            })
            ->get();

        $usersClosed = 0;
        $userErrors = [];
        
        foreach ($userTrades as $userTrade) {
            try {
                $exchangeAccount = $userTrade->exchangeAccount;
                if (!$exchangeAccount) {
                    $userErrors[] = "User {$userTrade->user_id}: No exchange account";
                    continue;
                }
                
                if (!$exchangeAccount->is_active) {
                    $userErrors[] = "User {$userTrade->user_id}: Account not active";
                    continue;
                }
                
                $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
                
                $side = $userTrade->type === 'long' ? 'Buy' : 'Sell';
                $closeResult = $bybit->closePosition($userTrade->symbol, $side);
                
                $currentPrice = $bybit->getCurrentPrice($userTrade->symbol);
                
                // Get actual realized P&L from Bybit for user
                $userRealizedPnl = $closeResult['closedPnl'] ?? 0;
                
                if ($userRealizedPnl == 0) {
                    $userRealizedPnl = $bybit->getClosedPnL($userTrade->symbol);
                }
                
                $userTrade->update([
                    'exit_price' => $currentPrice,
                    'status' => 'closed',
                    'closed_at' => now(),
                    'realized_pnl' => $userRealizedPnl,
                ]);
                
                // Calculate P&L percent
                $userPnlPercent = 0;
                if ($userTrade->entry_price && $userTrade->quantity) {
                    $userPnlPercent = ($userRealizedPnl / ($userTrade->entry_price * $userTrade->quantity)) * 100;
                }
                $userTrade->update(['realized_pnl_percent' => $userPnlPercent]);
                
                if ($userTrade->position) {
                    $userTrade->position->close($currentPrice);
                }
                
                $usersClosed++;
                
                \Log::info("User trade closed", [
                    'trade_id' => $userTrade->id,
                    'user_id' => $userTrade->user_id,
                    'symbol' => $userTrade->symbol,
                    'realized_pnl' => $userRealizedPnl,
                ]);
                
            } catch (\Exception $e) {
                $userErrors[] = "User {$userTrade->user_id}: " . $e->getMessage();
                \Log::error("Failed to close user trade {$userTrade->id}: " . $e->getMessage());
            }
        }

        return [
            'admin_closed' => true,
            'users_closed' => $usersClosed,
            'user_errors' => $userErrors,
        ];
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
            $positionSize = Setting::get('signal_position_size', 5);
            $leverageSetting = Setting::get('signal_leverage', 'Max');
            
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
        // Active trades (all users)
        $activeTrades = Trade::where('status', 'open')->count();

        // 24h volume
        $totalVolume = Trade::where('created_at', '>=', now()->subDay())
            ->sum(DB::raw('entry_price * quantity'));

        // Previous 24h volume for comparison
        $previousVolume = Trade::whereBetween('created_at', [
            now()->subDays(2),
            now()->subDay()
        ])->sum(DB::raw('entry_price * quantity'));

        $volumeChange = $previousVolume > 0 
            ? (($totalVolume - $previousVolume) / $previousVolume) * 100 
            : 0;

        // Total profit (all closed trades)
        $totalProfit = Trade::where('status', 'closed')
            ->sum('realized_pnl');

        // Affected users (users with active trades)
        $affectedUsers = User::where('is_admin', false)
            ->whereHas('trades', function($query) {
                $query->where('status', 'open');
            })
            ->count();

        // Admin trades stats
        $adminTrades24h = Trade::whereHas('user', function($query) {
                $query->where('is_admin', true);
            })
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $adminLongCount = Trade::whereHas('user', function($query) {
                $query->where('is_admin', true);
            })
            ->where('created_at', '>=', now()->subDay())
            ->where('type', 'long')
            ->count();

        $adminShortCount = $adminTrades24h - $adminLongCount;

        // User trades stats
        $userTrades24h = Trade::whereHas('user', function($query) {
                $query->where('is_admin', false);
            })
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $activeUsersCount = User::where('is_admin', false)
            ->whereHas('trades', function($query) {
                $query->where('created_at', '>=', now()->subDay());
            })
            ->count();

        // Success rate (last 30 days)
        $closedTrades = Trade::where('status', 'closed')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        
        $winningTrades = Trade::where('status', 'closed')
            ->where('created_at', '>=', now()->subDays(30))
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
            'admin_trades_today' => $adminTrades24h,  // Alias for blade compatibility
            'long_trades' => $adminLongCount,
            'short_trades' => $adminShortCount,
            'admin_long_count' => $adminLongCount,
            'admin_short_count' => $adminShortCount,
            'user_trades_24h' => $userTrades24h,
            'user_trades_today' => $userTrades24h,  // Alias for blade compatibility
            'active_users' => $activeUsersCount,
            'win_rate' => $winRate,
            'winning_trades' => $winningTrades,
            'closed_trades' => $closedTrades,
            'success_rate' => $winRate,  // Alias for blade compatibility
            'open_trades' => $openAdminPositions,
        ];
    }
}