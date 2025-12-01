@extends('layouts.app')

@section('title', 'Active Positions - CryptoBot Pro')

@section('page-title', 'Active Positions')

@section('content')
<!-- Status Banner -->
<div class="card border-0 shadow-sm mb-4 bg-light text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3">
                        <i class="bi bi-graph-up-arrow fs-2"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Real-Time Position Monitoring</h4>
                        <p class="mb-0 opacity-75">Your active positions are being monitored 24/7</p>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <div>
                        <div class="text-white text-opacity-75 small">Active Positions</div>
                        <div class="fw-bold fs-5">{{ $stats['active_positions'] }}</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="text-white text-opacity-75 small">Total Value</div>
                        <div class="fw-bold fs-5">${{ number_format($stats['total_value'], 0) }}</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="text-white text-opacity-75 small">Unrealized P&L</div>
                        <div class="fw-bold fs-5 {{ $stats['unrealized_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $stats['unrealized_pnl'] >= 0 ? '+' : '' }}${{ number_format($stats['unrealized_pnl'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex p-4 mb-2">
                    <i class="bi bi-activity fs-1"></i>
                </div>
                <div>
                    <span class="badge bg-success bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-2">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Auto-Monitor Active
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Profitable</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $positions->where('unrealized_pnl', '>', 0)->count() }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-up text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success">+${{ number_format($positions->where('unrealized_pnl', '>', 0)->sum('unrealized_pnl'), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Losing</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ $positions->where('unrealized_pnl', '<', 0)->count() }}</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-down text-danger fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger">${{ number_format($positions->where('unrealized_pnl', '<', 0)->sum('unrealized_pnl'), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Margin Used</div>
                        <h4 class="fw-bold mb-0 text-warning">${{ number_format($stats['margin_used'], 0) }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-pie-chart text-warning fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Available: ${{ number_format($stats['margin_available'], 0) }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">P&L %</div>
                        <h4 class="fw-bold mb-0 {{ $stats['unrealized_pnl_percent'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $stats['unrealized_pnl_percent'] >= 0 ? '+' : '' }}{{ number_format($stats['unrealized_pnl_percent'], 2) }}%</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-percent text-info fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Overall return</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-lg-4">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="profitable">Profitable</option>
                    <option value="losing">Losing</option>
                    <option value="at_risk">At Risk</option>
                </select>
            </div>
            <div class="col-lg-3">
                <select class="form-select" id="filterType">
                    <option value="">All Types</option>
                    <option value="long">Long</option>
                    <option value="short">Short</option>
                </select>
            </div>
            <div class="col-lg-3">
                <input type="text" class="form-select" placeholder="Search pair..." id="searchPair">
            </div>
            <div class="col-lg-2">
                <button class="btn btn-outline-secondary w-100" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Active Positions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">My Active Positions</h5>
                <p class="text-muted small mb-0">Real-time monitoring with automatic TP/SL execution</p>
            </div>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closeAllModal">
                <i class="bi bi-x-octagon me-2"></i>Close All
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Pair</th>
                        <th class="border-0 py-3 fw-semibold">Type</th>
                        <th class="border-0 py-3 fw-semibold">Entry</th>
                        <th class="border-0 py-3 fw-semibold">Current</th>
                        <th class="border-0 py-3 fw-semibold">TP/SL</th>
                        <th class="border-0 py-3 fw-semibold">Size</th>
                        <th class="border-0 py-3 fw-semibold">P&L</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positions as $position)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-coin text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $position->symbol }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $position->side === 'long' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $position->side === 'long' ? 'success' : 'danger' }}">
                                <i class="bi bi-arrow-{{ $position->side === 'long' ? 'up' : 'down' }}-right me-1"></i>{{ strtoupper($position->side) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">${{ number_format($position->entry_price, 2) }}</div>
                            <small class="text-secondary">{{ $position->opened_at ? $position->opened_at->diffForHumans() : '-' }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold {{ $position->unrealized_pnl >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($position->current_price, 2) }}</div>
                            <small class="{{ $position->unrealized_pnl >= 0 ? 'text-success' : 'text-danger' }}">{{ $position->unrealized_pnl >= 0 ? '+' : '' }}{{ number_format((($position->current_price - $position->entry_price) / $position->entry_price) * 100, 2) }}%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success mb-1">TP: ${{ number_format($position->take_profit, 2) }}</div>
                                <div class="text-danger">SL: ${{ number_format($position->stop_loss, 2) }}</div>
                            </div>
                            @php
                                $totalRange = abs($position->take_profit - $position->stop_loss);
                                $currentProgress = abs($position->current_price - $position->stop_loss);
                                $progressPercent = $totalRange > 0 ? ($currentProgress / $totalRange) * 100 : 0;
                            @endphp
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar {{ $position->unrealized_pnl >= 0 ? 'bg-success' : 'bg-danger' }}" style="width: {{ min(100, max(0, $progressPercent)) }}%"></div>
                            </div>
                            <small class="text-secondary">{{ number_format($progressPercent, 0) }}% to TP</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $position->quantity }} {{ explode('USDT', $position->symbol)[0] }}</div>
                            <small class="text-secondary">~${{ number_format($position->entry_price * $position->quantity, 0) }}</small>
                        </td>
                        <td>
                            <div class="{{ $position->unrealized_pnl >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ $position->unrealized_pnl >= 0 ? '+' : '' }}${{ number_format($position->unrealized_pnl, 2) }}</div>
                            <small class="{{ $position->unrealized_pnl >= 0 ? 'text-success' : 'text-danger' }}">{{ $position->unrealized_pnl >= 0 ? '+' : '' }}{{ number_format($position->unrealized_pnl_percent, 2) }}%</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" title="Modify" data-bs-toggle="modal" data-bs-target="#modifyPositionModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Close" data-bs-toggle="modal" data-bs-target="#closePositionModal">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No active positions
                            </div>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>
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
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to close this position?</p>
                <div class="card border-0 bg-body-secondary mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Position Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pair</span>
                            <span class="fw-bold">BTCUSDT (Bybit)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Current P&L</span>
                            <span class="text-success fw-bold">+$279.45</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Size</span>
                            <span class="fw-bold">0.15 BTC</span>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning border-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <small>This action will immediately close your position at market price. This cannot be undone.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">
                    <i class="bi bi-x-circle me-2"></i>Close Position
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modify Position Modal -->
<div class="modal fade" id="modifyPositionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil me-2"></i>Modify Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Modify take profit and stop loss levels for this position</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Position Details</label>
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Pair</span>
                                    <span class="fw-bold">BTCUSDT</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Entry Price</span>
                                    <span class="fw-bold">$66,450</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Current Price</span>
                                    <span class="text-success fw-bold">$66,823</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_tp" class="form-label fw-semibold">
                            Take Profit Price
                        </label>
                        <input type="number" class="form-control" id="new_tp" value="67200" step="0.01">
                        <div class="form-text small">
                            Current: $67,200
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_sl" class="form-label fw-semibold">
                            Stop Loss Price
                        </label>
                        <input type="number" class="form-control" id="new_sl" value="65900" step="0.01">
                        <div class="form-text small">
                            Current: $65,900
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Position
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
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to close ALL active positions?</p>
                <div class="alert alert-danger border-0 mb-3">
                    <strong>WARNING:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>This will close all 8 active positions</li>
                        <li>All positions will be closed at current market price</li>
                        <li>Current unrealized P&L: <strong>+$1,234</strong></li>
                        <li>This action cannot be undone</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type "CLOSE ALL" to confirm</label>
                    <input type="text" class="form-control" id="closeAllConfirm" placeholder="Type CLOSE ALL">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="closeAllBtn" disabled>
                    <i class="bi bi-x-octagon me-2"></i>Close All Positions
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Close All confirmation
    const closeAllInput = document.getElementById('closeAllConfirm');
    const closeAllBtn = document.getElementById('closeAllBtn');
    
    if (closeAllInput && closeAllBtn) {
        closeAllInput.addEventListener('input', function() {
            closeAllBtn.disabled = this.value !== 'CLOSE ALL';
        });
    }

    // Auto-refresh every 10 seconds
    setInterval(() => {
        console.log('Auto-refreshing positions...');
        // Add actual refresh logic here
    }, 10000);
</script>
@endpush