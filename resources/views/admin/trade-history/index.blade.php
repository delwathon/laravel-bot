@extends('layouts.app')

@section('title', 'Trade History - CryptoBot Pro')

@section('page-title', 'Trade History')

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Trades</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_trades']) }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-left-right text-primary fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">All time</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Winning Trades</div>
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($summary['winning_trades']) }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">{{ number_format($summary['win_rate'], 1) }}% win rate</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Volume</div>
                        <h4 class="fw-bold mb-0 text-info">${{ number_format($summary['total_volume'], 0) }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-cash-stack text-info fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Last 30 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total P&L</div>
                        <h4 class="fw-bold mb-0 {{ $summary['total_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $summary['total_pnl'] >= 0 ? '+' : '' }}${{ number_format($summary['total_pnl'], 2) }}
                        </h4>
                    </div>
                    <div class="bg-{{ $summary['total_pnl'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-currency-dollar text-{{ $summary['total_pnl'] >= 0 ? 'success' : 'danger' }} fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="{{ $summary['total_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $summary['total_pnl'] >= 0 ? '+' : '' }}{{ number_format($summary['roi'], 1) }}% return
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.trade-history.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-lg-3">
                    <label class="form-label fw-semibold small">Date Range</label>
                    <select class="form-select" name="date_range" onchange="document.getElementById('filterForm').submit()">
                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="7d" {{ request('date_range') == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30d" {{ request('date_range', '30d') == '30d' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90d" {{ request('date_range') == '90d' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="all" {{ request('date_range') == 'all' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold small">Pair</label>
                    <select class="form-select" name="pair" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Pairs</option>
                        @foreach($availablePairs as $pair)
                            <option value="{{ $pair }}" {{ request('pair') == $pair ? 'selected' : '' }}>{{ $pair }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold small">Trade Type</label>
                    <select class="form-select" name="type" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Types</option>
                        <option value="long" {{ request('type') == 'long' ? 'selected' : '' }}>Long</option>
                        <option value="short" {{ request('type') == 'short' ? 'selected' : '' }}>Short</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold small">Status</label>
                    <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('admin.trade-history.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-2"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Trade History Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">All Trades</h5>
                <p class="text-muted small mb-0">Complete trade history across all users</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.trade-history.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Export CSV
                </a>
                <a href="{{ route('admin.trade-history.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Time</th>
                        <th class="border-0 py-3 fw-semibold">User</th>
                        <th class="border-0 py-3 fw-semibold">Pair</th>
                        <th class="border-0 py-3 fw-semibold">Type</th>
                        <th class="border-0 py-3 fw-semibold">Entry</th>
                        <th class="border-0 py-3 fw-semibold">Exit</th>
                        <th class="border-0 py-3 fw-semibold">Size</th>
                        <th class="border-0 py-3 fw-semibold">Duration</th>
                        <th class="border-0 py-3 fw-semibold text-end">P&L</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trades as $trade)
                    <tr class="{{ $trade->status == 'closed' && $trade->realized_pnl < 0 ? 'table-danger bg-opacity-10' : '' }}">
                        <td class="px-4">
                            <div class="small fw-semibold">{{ $trade->created_at->format('H:i:s') }}</div>
                            <small class="text-secondary">{{ $trade->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $trade->user->name }}</div>
                            <small class="text-secondary">ID: {{ $trade->user->id }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @php
                                    $iconMap = [
                                        'BTCUSDT' => ['icon' => 'currency-bitcoin', 'color' => 'warning'],
                                        'ETHUSDT' => ['icon' => 'currency-exchange', 'color' => 'info'],
                                        'SOLUSDT' => ['icon' => 'coin', 'color' => 'purple', 'custom' => '#8b5cf6'],
                                        'XRPUSDT' => ['icon' => 'currency-dollar', 'color' => 'success'],
                                        'BNBUSDT' => ['icon' => 'coin', 'color' => 'warning'],
                                        'AVAXUSDT' => ['icon' => 'coin', 'color' => 'danger'],
                                    ];
                                    $symbolData = $iconMap[$trade->symbol] ?? ['icon' => 'coin', 'color' => 'secondary'];
                                @endphp
                                <div class="bg-{{ $symbolData['color'] }} bg-opacity-10 p-1 rounded-circle me-2" 
                                     style="{{ isset($symbolData['custom']) ? 'background-color: rgba(139, 92, 246, 0.1) !important;' : '' }}">
                                    <i class="bi bi-{{ $symbolData['icon'] }} text-{{ $symbolData['color'] }} small" 
                                       style="{{ isset($symbolData['custom']) ? 'color: ' . $symbolData['custom'] . ' !important;' : '' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $trade->symbol }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $trade->type == 'long' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $trade->type == 'long' ? 'success' : 'danger' }}">
                                {{ strtoupper($trade->type) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">${{ number_format($trade->entry_price, 2) }}</div>
                        </td>
                        <td>
                            @if($trade->status == 'closed' && $trade->exit_price)
                                <div class="fw-semibold {{ $trade->realized_pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($trade->exit_price, 2) }}
                                </div>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            <div>{{ number_format($trade->quantity, 4) }} {{ str_replace('USDT', '', $trade->symbol) }}</div>
                            <small class="text-secondary">~${{ number_format($trade->entry_price * $trade->quantity, 0) }}</small>
                        </td>
                        <td>
                            @if($trade->status == 'closed' && $trade->closed_at)
                                @php
                                    $duration = $trade->opened_at->diff($trade->closed_at);
                                    $hours = $duration->h + ($duration->days * 24);
                                    $minutes = $duration->i;
                                @endphp
                                <small>{{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes }}m</small>
                            @else
                                <small class="text-muted">Active</small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($trade->status == 'closed' && $trade->realized_pnl !== null)
                                <div class="fw-bold {{ $trade->realized_pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $trade->realized_pnl >= 0 ? '+' : '' }}${{ number_format($trade->realized_pnl, 2) }}
                                </div>
                                @php
                                    $roi = $trade->entry_price > 0 ? (($trade->realized_pnl / ($trade->entry_price * $trade->quantity)) * 100) : 0;
                                @endphp
                                <small class="{{ $trade->realized_pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $roi >= 0 ? '+' : '' }}{{ number_format($roi, 1) }}%
                                </small>
                            @elseif($trade->status == 'open')
                                <span class="badge bg-info bg-opacity-10 text-info">Open</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($trade->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted mb-0">No trades found matching your filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($trades->hasPages())
    <div class="card-footer bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted small">
                Showing {{ $trades->firstItem() ?? 0 }} to {{ $trades->lastItem() ?? 0 }} of {{ $trades->total() }} trades
            </div>
            {{ $trades->links() }}
        </div>
    </div>
    @endif
</div>

@endsection