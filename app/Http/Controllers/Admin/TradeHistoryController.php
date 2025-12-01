<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TradeHistoryController extends Controller
{
    public function index(Request $request)
    {
        // Build query with filters
        $query = Trade::with(['user', 'signal'])
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Paginate
        $trades = $query->paginate(50)->withQueryString();
        
        // Calculate summary statistics
        $summary = $this->calculateSummary($request);
        
        // Get available pairs for filter dropdown
        $availablePairs = Trade::select('symbol')
            ->distinct()
            ->orderBy('symbol')
            ->pluck('symbol')
            ->toArray();
        
        return view('admin.trade-history.index', compact('trades', 'summary', 'availablePairs'));
    }
    
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        // Build query with filters (no pagination for export)
        $query = Trade::with(['user', 'signal'])
            ->orderBy('created_at', 'desc');
        
        $this->applyFilters($query, $request);
        
        // Limit to prevent memory issues
        $trades = $query->limit(10000)->get();
        
        if ($format === 'csv') {
            return $this->exportCsv($trades);
        } elseif ($format === 'pdf') {
            return $this->exportPdf($trades);
        }
        
        return back()->with('error', 'Invalid export format');
    }
    
    protected function applyFilters($query, Request $request)
    {
        // Date range filter
        $dateRange = $request->get('date_range', '30d');
        switch ($dateRange) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case '7d':
                $query->where('created_at', '>=', now()->subDays(7));
                break;
            case '30d':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case '90d':
                $query->where('created_at', '>=', now()->subDays(90));
                break;
            case 'all':
                // No date filter
                break;
        }
        
        // Pair filter
        if ($request->filled('pair')) {
            $query->where('symbol', $request->pair);
        }
        
        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // User search
        if ($request->filled('user')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user . '%')
                  ->orWhere('email', 'like', '%' . $request->user . '%');
            });
        }
    }
    
    protected function calculateSummary(Request $request)
    {
        // Build query for summary with same filters
        $query = Trade::query();
        $this->applyFilters($query, $request);
        
        // Clone for different calculations
        $allTrades = clone $query;
        $closedTrades = clone $query;
        
        // Total trades
        $totalTrades = $allTrades->count();
        
        // Winning trades and win rate
        $closedQuery = $closedTrades->where('status', 'closed');
        $closedCount = $closedQuery->count();
        $winningTrades = (clone $closedQuery)->where('realized_pnl', '>', 0)->count();
        $winRate = $closedCount > 0 ? ($winningTrades / $closedCount) * 100 : 0;
        
        // Total volume (last 30 days)
        $totalVolume = Trade::where('created_at', '>=', now()->subDays(30))
            ->sum(DB::raw('entry_price * quantity'));
        
        // Total P&L
        $totalPnl = (clone $query)->where('status', 'closed')->sum('realized_pnl');
        
        // ROI calculation (simplified - based on total volume)
        $roi = $totalVolume > 0 ? ($totalPnl / $totalVolume) * 100 : 0;
        
        return [
            'total_trades' => $totalTrades,
            'winning_trades' => $winningTrades,
            'win_rate' => $winRate,
            'total_volume' => $totalVolume,
            'total_pnl' => $totalPnl,
            'roi' => $roi,
        ];
    }
    
    protected function exportCsv($trades)
    {
        $filename = 'trade_history_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($trades) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Trade ID',
                'User',
                'User Email',
                'Symbol',
                'Type',
                'Entry Price',
                'Exit Price',
                'Quantity',
                'Leverage',
                'Entry Time',
                'Exit Time',
                'Duration (minutes)',
                'Status',
                'Realized P&L',
                'ROI %',
                'Fees',
            ]);
            
            // Data rows
            foreach ($trades as $trade) {
                $duration = null;
                if ($trade->status == 'closed' && $trade->opened_at && $trade->closed_at) {
                    $duration = $trade->opened_at->diffInMinutes($trade->closed_at);
                }
                
                $roi = 0;
                if ($trade->status == 'closed' && $trade->entry_price > 0) {
                    $roi = (($trade->realized_pnl ?? 0) / ($trade->entry_price * $trade->quantity)) * 100;
                }
                
                fputcsv($file, [
                    $trade->id,
                    $trade->user->name ?? 'N/A',
                    $trade->user->email ?? 'N/A',
                    $trade->symbol,
                    strtoupper($trade->type),
                    $trade->entry_price,
                    $trade->exit_price ?? '',
                    $trade->quantity,
                    $trade->leverage,
                    $trade->opened_at ? $trade->opened_at->toDateTimeString() : $trade->created_at->toDateTimeString(),
                    $trade->closed_at ? $trade->closed_at->toDateTimeString() : '',
                    $duration ?? '',
                    ucfirst($trade->status),
                    $trade->realized_pnl ?? '',
                    $roi ? number_format($roi, 2) : '',
                    $trade->fees ?? 0,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    protected function exportPdf($trades)
    {
        $summary = [
            'total_trades' => $trades->count(),
            'total_volume' => $trades->sum(function($trade) {
                return $trade->entry_price * $trade->quantity;
            }),
            'total_pnl' => $trades->where('status', 'closed')->sum('realized_pnl'),
            'winning_trades' => $trades->where('status', 'closed')->where('realized_pnl', '>', 0)->count(),
        ];
        
        $pdf = Pdf::loadView('admin.trade-history.pdf', compact('trades', 'summary'));
        
        $filename = 'trade_history_' . now()->format('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }
}