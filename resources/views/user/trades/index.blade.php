@extends('layouts.app')

@section('title', 'My Trade History - CryptoBot Pro')

@section('page-title', 'My Trade History')

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Trades</div>
                        <h3 class="fw-bold mb-0">{{ $stats['total_trades'] }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-left-right text-primary fs-4"></i>
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
                        <div class="text-muted text-uppercase small fw-bold mb-1">Win Rate</div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['win_rate'], 1) }}%</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">{{ $stats['closed_trades'] - ($stats['closed_trades'] - ($stats['closed_trades'] * $stats['win_rate'] / 100)) }} wins</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Avg P&L</div>
                        <h3 class="fw-bold mb-0 text-info">${{ number_format(abs($stats['avg_profit']), 2) }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-cash-stack text-info fs-4"></i>
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
                        <h3 class="fw-bold mb-0 {{ $stats['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $stats['total_profit'] >= 0 ? '+' : '' }}${{ number_format($stats['total_profit'], 2) }}</h3>
                    </div>
                    <div class="bg-{{ $stats['total_profit'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-currency-dollar text-{{ $stats['total_profit'] >= 0 ? 'success' : 'danger' }} fs-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="{{ $stats['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $stats['closed_trades'] }} closed trades</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">Date Range</label>
                <select class="form-select">
                    <option value="today">Today</option>
                    <option value="7d">Last 7 Days</option>
                    <option value="30d" selected>Last 30 Days</option>
                    <option value="90d">Last 90 Days</option>
                    <option value="all">All Time</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">Pair</label>
                <select class="form-select">
                    <option value="">All Pairs</option>
                    <option value="BTCUSDT">BTCUSDT</option>
                    <option value="ETHUSDT">ETHUSDT</option>
                    <option value="SOLUSDT">SOLUSDT</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">Type</label>
                <select class="form-select">
                    <option value="">All Types</option>
                    <option value="long">Long</option>
                    <option value="short">Short</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">Result</label>
                <select class="form-select">
                    <option value="">All Results</option>
                    <option value="profit">Profitable</option>
                    <option value="loss">Loss</option>
                    <option value="breakeven">Break Even</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-primary">
                <i class="bi bi-funnel me-2"></i>Apply Filters
            </button>
            <button class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-2"></i>Clear
            </button>
        </div>
    </div>
</div>

<!-- Trade History Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">Trade History</h5>
                <p class="text-muted small mb-0">Your complete trading history</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Export CSV
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
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
                    <tr class="{{ $trade->status === 'closed' && $trade->realized_pnl < 0 ? 'table-danger bg-opacity-10' : '' }}">
                        <td class="px-4">
                            <div class="small fw-semibold">{{ $trade->created_at->format('H:i:s') }}</div>
                            <small class="text-secondary">{{ $trade->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-coin text-primary small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $trade->symbol }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $trade->type === 'long' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $trade->type === 'long' ? 'success' : 'danger' }}">
                                <i class="bi bi-arrow-{{ $trade->type === 'long' ? 'up' : 'down' }}-right me-1"></i>{{ strtoupper($trade->type) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">${{ number_format($trade->entry_price, 2) }}</div>
                        </td>
                        <td>
                            @if($trade->exit_price)
                                <div class="fw-semibold {{ $trade->realized_pnl >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($trade->exit_price, 2) }}</div>
                            @else
                                <div class="text-muted">-</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $trade->quantity }} {{ explode('USDT', $trade->symbol)[0] }}</div>
                            <small class="text-secondary">~${{ number_format($trade->entry_price * $trade->quantity, 0) }}</small>
                        </td>
                        <td>
                            @if($trade->closed_at && $trade->opened_at)
                                <small>{{ $trade->opened_at->diffForHumans($trade->closed_at, true) }}</small>
                            @else
                                <small>-</small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($trade->status === 'closed')
                                <div class="{{ $trade->realized_pnl >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ $trade->realized_pnl >= 0 ? '+' : '' }}${{ number_format($trade->realized_pnl, 2) }}</div>
                                <small class="{{ $trade->realized_pnl >= 0 ? 'text-success' : 'text-danger' }}">{{ $trade->realized_pnl >= 0 ? '+' : '' }}{{ number_format($trade->realized_pnl_percent, 1) }}%</small>
                            @else
                                <span class="badge bg-info">Open</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No trades yet
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="card-footer bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted small">
                Showing {{ $trades->firstItem() ?? 0 }} to {{ $trades->lastItem() ?? 0 }} of {{ $trades->total() }} trades
            </div>
            <nav>
                {{ $trades->links() }}
            </nav>
        </div>
    </div>
</div>

@endsection