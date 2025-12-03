@extends('layouts.app')

@section('title', 'Admin Trades - CryptoBot Pro')

@section('page-title', 'Admin Trade Execution')

@section('content')
<!-- Admin Trade Notice -->
<div class="alert alert-primary border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 flex-shrink-0">
            <i class="bi bi-info-circle-fill text-primary fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-2">Admin Trade Execution Center</h5>
            <p class="mb-0">Trades executed here will be automatically propagated to all active users with connected exchanges. Only successfully executed admin trades will be mirrored.</p>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Positions</div>
                        <h3 class="fw-bold mb-0 text-info">{{ $stats['active_trades'] }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up-arrow text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Across all users</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Volume (24h)</div>
                        <h3 class="fw-bold mb-0">${{ number_format($stats['total_volume'], 0) }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-cash-stack text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-{{ $stats['volume_change'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $stats['volume_change'] >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $stats['volume_change'] >= 0 ? 'up' : 'down' }}"></i> {{ abs($stats['volume_change']) }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Profit</div>
                        <h3 class="fw-bold mb-0 {{ $stats['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($stats['total_profit'], 2) }}
                        </h3>
                    </div>
                    <div class="bg-{{ $stats['total_profit'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-trophy-fill text-{{ $stats['total_profit'] >= 0 ? 'success' : 'danger' }} fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">All users combined</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Affected Users</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $stats['affected_users'] }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-people-fill text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">With active trades</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Admin Trades (24h)</div>
                        <h3 class="fw-bold mb-0">{{ $stats['admin_trades_today'] }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-person-badge text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> {{ $stats['long_trades'] }} Long, {{ $stats['short_trades'] }} Short
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Admin Positions</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $stats['open_trades'] }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Being monitored</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Execute New Trade -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-plus-circle me-2"></i>Execute New Admin Trade
        </h5>
        <p class="text-muted small mb-0 mt-2">
            Trades will use position size ({{ $positionSize }}%) and leverage ({{ $leverage }}) from signal generator settings
        </p>
    </div>
    <div class="card-body p-4">
        <form id="executeTradeForm" method="POST" action="{{ route('admin.trades.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Trading Pair</label>
                    <input type="text" class="form-control" name="symbol" placeholder="BTCUSDT" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Direction</label>
                    <select class="form-select" name="type" required>
                        <option value="">Select</option>
                        <option value="long">LONG</option>
                        <option value="short">SHORT</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Order Type</label>
                    <select class="form-select" name="order_type" required>
                        <option value="Market">Market</option>
                        <option value="Limit">Limit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Entry Price</label>
                    <input type="number" class="form-control" name="entry_price" placeholder="66450.00" step="0.000001" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Take Profit</label>
                    <input type="number" class="form-control" name="take_profit" placeholder="67200.00" step="0.000001" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Stop Loss</label>
                    <input type="number" class="form-control" name="stop_loss" placeholder="65900.00" step="0.000001" required>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <button type="button" class="btn btn-outline-secondary" onclick="previewTrade()">
                        <i class="bi bi-eye me-2"></i>Preview Impact
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-play-circle-fill me-2"></i>Execute for All Users
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Recent Admin Trades -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">Recent Admin Trades</h5>
                <p class="text-muted small mb-0">Manually executed trades and their propagation status</p>
            </div>
            @if($trades->where('status', 'open')->count() > 0)
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closeAllModal">
                <i class="bi bi-x-octagon me-2"></i>Close All Positions
            </button>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Time</th>
                        <th class="border-0 py-3 fw-semibold">Pair</th>
                        <th class="border-0 py-3 fw-semibold">Direction</th>
                        <th class="border-0 py-3 fw-semibold">Entry</th>
                        <th class="border-0 py-3 fw-semibold">TP / SL</th>
                        <th class="border-0 py-3 fw-semibold">User Trades</th>
                        <th class="border-0 py-3 fw-semibold">P&L</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 px-4 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trades as $trade)
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">{{ $trade->created_at->format('H:i:s') }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $trade->created_at->format('M d') }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $trade->symbol }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $trade->type === 'long' ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $trade->type === 'long' ? 'text-success' : 'text-danger' }} border border-{{ $trade->type === 'long' ? 'success' : 'danger' }} border-opacity-25">
                                {{ strtoupper($trade->type) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">${{ number_format($trade->entry_price, 2) }}</div>
                        </td>
                        <td>
                            <div class="small">
                                <span class="text-success">${{ number_format($trade->take_profit, 2) }}</span>
                                <span class="text-muted">/</span>
                                <span class="text-danger">${{ number_format($trade->stop_loss, 2) }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $userTrades = $trade->signal ? $trade->signal->trades()->count() : 0;
                                $successfulTrades = $trade->signal ? $trade->signal->trades()->where('status', '!=', 'failed')->count() : 0;
                            @endphp
                            <div class="d-flex align-items-center">
                                <div class="progress me-2" style="width: 60px; height: 6px;">
                                    @if($userTrades > 0)
                                        <div class="progress-bar bg-success" style="width: {{ ($successfulTrades / $userTrades) * 100 }}%"></div>
                                    @endif
                                </div>
                                <span class="small fw-semibold">{{ $successfulTrades }}/{{ $userTrades }}</span>
                            </div>
                            @if($userTrades > 0)
                                <small class="text-muted">{{ number_format(($successfulTrades / $userTrades) * 100, 1) }}% success</small>
                            @endif
                        </td>
                        <td>
                            @if($trade->status === 'closed')
                                <div class="fw-bold {{ $trade->realized_pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $trade->realized_pnl >= 0 ? '+' : '' }}${{ number_format($trade->realized_pnl, 2) }}
                                </div>
                                <div class="small text-muted">
                                    {{ $trade->realized_pnl >= 0 ? '+' : '' }}{{ number_format($trade->realized_pnl_percent, 2) }}%
                                </div>
                            @else
                                @php
                                    $position = $trade->position;
                                    $unrealizedPnl = $position ? $position->unrealized_pnl : 0;
                                @endphp
                                <div class="fw-bold {{ $unrealizedPnl >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $unrealizedPnl >= 0 ? '+' : '' }}${{ number_format($unrealizedPnl, 2) }}
                                </div>
                                <div class="small text-muted">Unrealized</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusConfig = match($trade->status) {
                                    'open' => ['color' => 'success', 'icon' => 'circle-fill', 'text' => 'Active'],
                                    'closed' => ['color' => 'secondary', 'icon' => 'check-circle-fill', 'text' => 'Closed'],
                                    'failed' => ['color' => 'danger', 'icon' => 'x-circle-fill', 'text' => 'Failed'],
                                    default => ['color' => 'warning', 'icon' => 'clock-fill', 'text' => ucfirst($trade->status)]
                                };
                            @endphp
                            <span class="badge bg-{{ $statusConfig['color'] }} bg-opacity-10 text-{{ $statusConfig['color'] }} border border-{{ $statusConfig['color'] }} border-opacity-25">
                                <i class="bi bi-{{ $statusConfig['icon'] }}" style="font-size: 6px;"></i> {{ $statusConfig['text'] }}
                            </span>
                            @if($trade->status === 'open' && $trade->position)
                                <div class="text-muted small mt-1">
                                    {{ $trade->created_at->diffForHumans() }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewTradeDetails({{ $trade->id }})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if($trade->status === 'open')
                                    <button class="btn btn-outline-danger" onclick="closePosition({{ $trade->id }}, '{{ $trade->symbol }}')" title="Close Position">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted">No admin trades executed yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($trades->hasPages())
    <div class="card-footer bg-transparent border-0 p-4">
        {{ $trades->links() }}
    </div>
    @endif
</div>

<!-- Close Position Modal -->
<div class="modal fade" id="closePositionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-x-octagon me-2"></i>Close Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="closePositionForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to close this admin position?</p>
                    <div class="alert alert-warning border-0 mb-3">
                        <strong>This will:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Close your admin position immediately</li>
                            <li>Trigger close orders for all user positions</li>
                            <li>Lock in current P&L for all users</li>
                            <li>Cannot be undone</li>
                        </ul>
                    </div>
                    <div class="card border-0 bg-body-secondary mb-3" id="closePositionSummary">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-2"></i>Close Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close All Positions Modal -->
<div class="modal fade" id="closeAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Close All Positions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.trades.close-all') }}">
                @csrf
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to close ALL admin positions?</p>
                    <div class="alert alert-danger border-0 mb-3">
                        <strong>CRITICAL WARNING:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>This will close ALL {{ $stats['open_trades'] }} admin positions</li>
                            <li>Will trigger close orders for ALL user positions across all pairs</li>
                            <li>Total estimated affected trades: ~{{ $stats['active_trades'] }} positions</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type "CLOSE ALL" to confirm</label>
                        <input type="text" class="form-control" id="confirmCloseAll" placeholder="Type CLOSE ALL" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="closeAllBtn" disabled>
                        <i class="bi bi-x-octagon me-2"></i>Close All Positions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Trade Impact Modal -->
<div class="modal fade" id="previewTradeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-eye me-2"></i>Trade Impact Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Populated by JavaScript -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Trade Details Modal -->
<div class="modal fade" id="tradeDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-info-circle me-2"></i>Trade Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="tradeDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Execute trade form submission
document.getElementById('executeTradeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if(confirm('Execute this trade for admin account and propagate to all users?')) {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Executing...';
        this.submit();
    }
});

// Close position
function closePosition(tradeId, symbol) {
    const form = document.getElementById('closePositionForm');
    form.action = `/admin/trades/${tradeId}`;
    
    // Show loading state
    document.getElementById('closePositionSummary').innerHTML = `
        <div class="card-body">
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Fetching position details...</p>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('closePositionModal')).show();
    
    // Fetch actual trade details
    fetch(`/admin/trades/${tradeId}/details`)
        .then(response => response.json())
        .then(data => {
            const currentPnL = data.realized_pnl || 0;
            const statusBadge = data.status === 'open' 
                ? '<span class="badge bg-success">Open</span>' 
                : '<span class="badge bg-secondary">Closed</span>';
            
            document.getElementById('closePositionSummary').innerHTML = `
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Position Summary</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pair</span>
                        <span class="fw-bold">${data.symbol}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Type</span>
                        <span class="fw-bold text-uppercase">${data.type}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Entry Price</span>
                        <span class="fw-bold">$${parseFloat(data.entry_price).toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Quantity</span>
                        <span class="fw-bold">${data.quantity}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Leverage</span>
                        <span class="fw-bold">${data.leverage}x</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span>${statusBadge}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Current P&L</span>
                        <span class="fw-bold ${currentPnL >= 0 ? 'text-success' : 'text-danger'}">
                            ${currentPnL >= 0 ? '+' : ''}$${parseFloat(currentPnL).toFixed(2)}
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">User Trades</span>
                        <span class="fw-bold">${data.user_trades_total}</span>
                    </div>
                    <div class="alert alert-warning border-0 mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>Closing this position will also close all ${data.user_trades_total} associated user trades.</small>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('closePositionSummary').innerHTML = `
                <div class="card-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Failed to load position details. Please try again.
                    </div>
                </div>
            `;
        });
}

// Preview trade impact
function previewTrade() {
    const form = document.getElementById('executeTradeForm');
    const formData = new FormData(form);
    
    const symbol = formData.get('symbol');
    const type = formData.get('type');
    const entryPrice = parseFloat(formData.get('entry_price') || 0);
    const takeProfit = parseFloat(formData.get('take_profit') || 0);
    const stopLoss = parseFloat(formData.get('stop_loss') || 0);
    
    const positionSize = {{ $positionSize }};
    const leverageDisplay = '{{ $leverage }}';
    const adminBalance = {{ $adminBalance }};
    
    if (!symbol || !entryPrice || !takeProfit || !stopLoss || !type) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Show loading state
    document.getElementById('previewContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Calculating...</span>
            </div>
            <p class="text-muted mt-2">Fetching leverage information for ${symbol}...</p>
        </div>
    `;
    new bootstrap.Modal(document.getElementById('previewTradeModal')).show();
    
    // Fetch max leverage for this specific symbol
    fetch(`/admin/trades/preview-calculation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            symbol: symbol,
            entry_price: entryPrice,
            take_profit: takeProfit,
            stop_loss: stopLoss,
            type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        const leverageValue = data.leverage;
        const riskAmount = data.risk_amount;
        const stopLossDistance = data.stop_loss_distance;
        const stopLossPercent = data.stop_loss_percent;
        const quantity = data.quantity;
        const positionValue = data.position_value;
        const marginRequired = data.margin_required;
        const potentialProfit = data.potential_profit;
        const potentialLoss = data.potential_loss;
        
        const symbolBase = symbol.replace('USDT', '');
        
        document.getElementById('previewContent').innerHTML = `
            <div class="alert alert-info border-0 mb-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Using Admin Account:</strong> Balance: $${adminBalance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} | Position Size: ${positionSize}% | Leverage: ${leverageDisplay}
            </div>
            <div class="card border-0 bg-body-secondary">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Admin Trade Calculation</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Account Balance</span>
                        <span class="fw-bold">$${adminBalance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Risk Amount (${positionSize}%)</span>
                        <span class="fw-bold">$${riskAmount.toFixed(2)} USDT</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Leverage (${symbol})</span>
                        <span class="fw-bold">${leverageDisplay === 'Max' ? leverageValue + 'x (Max for ' + symbol + ')' : leverageValue + 'x'}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Stop Loss Distance</span>
                        <span class="fw-bold">$${stopLossDistance.toFixed(2)} (${stopLossPercent.toFixed(2)}%)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Calculated Quantity</span>
                        <span class="fw-bold">${quantity.toFixed(2)} ${symbolBase}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Position Value (Notional)</span>
                        <span class="fw-bold">$${positionValue.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Margin Required</span>
                        <span class="fw-bold">$${marginRequired.toFixed(2)}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Entry Price</span>
                        <span class="fw-bold">$${entryPrice.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Take Profit</span>
                        <span class="text-success fw-bold">$${takeProfit.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Stop Loss</span>
                        <span class="text-danger fw-bold">$${stopLoss.toFixed(2)}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Potential Profit (TP)</span>
                        <span class="text-success fw-bold">+$${potentialProfit.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Potential Loss (SL)</span>
                        <span class="text-danger fw-bold">-$${potentialLoss.toFixed(2)}</span>
                    </div>
                </div>
            </div>
            <div class="alert alert-warning border-0 mt-3 mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <small><strong>Risk Explanation:</strong> With ${leverageValue}x leverage and ${stopLossPercent.toFixed(2)}% stop loss, you can open a position worth $${positionValue.toFixed(2)} (${quantity.toFixed(2)} ${symbolBase}) using $${marginRequired.toFixed(2)} margin. Max risk: $${potentialLoss.toFixed(2)} | Max profit: $${potentialProfit.toFixed(2)}</small>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('previewContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Failed to calculate trade preview. Please check your inputs and try again.
            </div>
        `;
    });
}

// View trade details
function viewTradeDetails(tradeId) {
    const modal = new bootstrap.Modal(document.getElementById('tradeDetailsModal'));
    
    document.getElementById('tradeDetailsContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-3">Loading trade details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`/admin/trades/${tradeId}/details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('tradeDetailsContent').innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Trade Information</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Symbol</span>
                                    <span class="fw-bold">${data.symbol}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Type</span>
                                    <span class="badge bg-${data.type === 'long' ? 'success' : 'danger'}">
                                        ${data.type.toUpperCase()}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Entry Price</span>
                                    <span class="fw-bold">$${parseFloat(data.entry_price).toLocaleString()}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Take Profit</span>
                                    <span class="text-success fw-bold">$${parseFloat(data.take_profit).toLocaleString()}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Stop Loss</span>
                                    <span class="text-danger fw-bold">$${parseFloat(data.stop_loss).toLocaleString()}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Quantity</span>
                                    <span class="fw-bold">${data.quantity}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Leverage</span>
                                    <span class="fw-bold">${data.leverage}x</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Performance & Propagation</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Status</span>
                                    <span class="badge bg-${data.status === 'open' ? 'success' : data.status === 'pending' ? 'warning' : 'secondary'}">
                                        ${data.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">P&L</span>
                                    <span class="fw-bold ${data.realized_pnl >= 0 ? 'text-success' : 'text-danger'}">
                                        ${data.realized_pnl >= 0 ? '+' : ''}$${parseFloat(data.realized_pnl || 0).toFixed(2)}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">User Propagation</span>
                                    <span class="fw-bold">${data.user_trades_successful}/${data.user_trades_total}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Success Rate</span>
                                    <span class="fw-bold text-success">${data.success_rate}%</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Created</span>
                                    <span class="fw-bold">${data.created_at}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tradeDetailsContent').innerHTML = `
                <div class="alert alert-danger border-0 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Failed to load trade details. Please try again.
                </div>
            `;
        });
}

// Enable close all button when confirmation text is typed
document.getElementById('confirmCloseAll')?.addEventListener('input', function(e) {
    const btn = document.getElementById('closeAllBtn');
    btn.disabled = e.target.value !== 'CLOSE ALL';
});
</script>
@endpush