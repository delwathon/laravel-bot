@extends('layouts.app')

@section('title', 'Monitoring Overview - CryptoBot Pro')

@section('page-title', 'Real-Time Monitoring')

@section('content')
<!-- Live Status Banner -->
<div class="card border-0 shadow-sm mb-4 bg-light text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3">
                        <i class="bi bi-display fs-2"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Real-Time Position Monitoring</h4>
                        <p class="mb-0 opacity-75">Automated monitoring of all active positions across {{ $totalUsers }} users</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-3">
                        <div class="text-white text-opacity-75 small">Total Positions</div>
                        <div class="fw-bold fs-4">{{ number_format($monitoringStats['total_positions']) }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-white text-opacity-75 small">Monitored Users</div>
                        <div class="fw-bold fs-4">{{ $activeConnections['total_users_online'] }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-white text-opacity-75 small">Active Monitors</div>
                        <div class="fw-bold fs-4">{{ $monitoringStats['healthy_positions'] }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-white text-opacity-75 small">Status</div>
                        <div class="fw-bold fs-5">
                            @if($systemHealth['status'] === 'healthy')
                                <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Online
                            @elseif($systemHealth['status'] === 'warning')
                                <i class="bi bi-exclamation-circle-fill" style="font-size: 12px;"></i> Warning
                            @else
                                <i class="bi bi-x-circle-fill" style="font-size: 12px;"></i> Critical
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                    <i class="bi bi-activity fs-1"></i>
                </div>
                <div>
                    <button class="btn btn-light" onclick="showRefreshConfirmation()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh All Monitors
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monitor Statistics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">TP Triggered (24h)</div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($monitoringStats['tp_triggered_count']) }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-bullseye text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> ${{ number_format($monitoringStats['tp_triggered_profit'], 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">SL Triggered (24h)</div>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($monitoringStats['sl_triggered_count']) }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-shield-exclamation text-danger fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-arrow-down"></i> ${{ number_format($monitoringStats['sl_triggered_loss'], 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Alerts Generated</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $monitoringStats['alerts_generated'] }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-bell-fill text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Last 1 hour</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Monitor Health</div>
                        <h3 class="fw-bold mb-0 text-success">{{ $monitoringStats['monitor_health_percent'] }}%</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-heart-pulse-fill text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">{{ $monitoringStats['healthy_positions'] }}/{{ $monitoringStats['total_positions'] }} healthy</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Health & Errors -->
@if($recentErrors->count() > 0)
<div class="card shadow-sm mb-4 border-start border-warning border-4">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                Recent Alerts & Issues
            </h5>
            <span class="badge bg-warning">{{ $recentErrors->count() }} Alert(s)</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @foreach($recentErrors->take(5) as $error)
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>{{ $error['type'] }}</strong>
                        <p class="mb-1 text-muted small">{{ $error['message'] }}</p>
                        <small class="text-muted">{{ $error['time'] }}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="showDismissAlert('{{ $error['type'] }}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Pending Orders (NEW) -->
@php
$pendingOrders = \App\Models\Trade::where('status', 'pending')
    ->with(['signal', 'user'])
    ->get()
    ->groupBy('signal_id')
    ->map(function($trades, $signalId) {
        $firstTrade = $trades->first();
        return [
            'signal_id' => $signalId,
            'symbol' => $firstTrade->symbol,
            'type' => $firstTrade->type,
            'order_type' => $firstTrade->order_type ?? 'Limit',
            'entry_price' => $firstTrade->entry_price,
            'stop_loss' => $firstTrade->stop_loss,
            'take_profit' => $firstTrade->take_profit,
            'users_count' => $trades->count(),
            'created_at' => $firstTrade->created_at,
            'leverage' => $firstTrade->leverage,
        ];
    });
@endphp

@if($pendingOrders->count() > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-hourglass-split text-warning me-2"></i>
                    Pending Limit Orders
                </h5>
                <p class="text-muted small mb-0">Limit orders awaiting fill (monitored every 2 minutes)</p>
            </div>
            <span class="badge bg-warning bg-opacity-10 text-warning">{{ $pendingOrders->count() }} Order(s)</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Symbol</th>
                        <th class="border-0 py-3 fw-semibold">Type</th>
                        <th class="border-0 py-3 fw-semibold">Entry Price</th>
                        <th class="border-0 py-3 fw-semibold">TP / SL</th>
                        <th class="border-0 py-3 fw-semibold">Leverage</th>
                        <th class="border-0 py-3 fw-semibold">Users</th>
                        <th class="border-0 py-3 fw-semibold">Placed</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 px-4 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingOrders as $order)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-2">
                                    <i class="bi bi-clock-history text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $order['symbol'] }}</div>
                                    <div class="small text-muted">{{ $order['order_type'] }} Order</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $order['type'] === 'long' ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $order['type'] === 'long' ? 'text-success' : 'text-danger' }} border border-{{ $order['type'] === 'long' ? 'success' : 'danger' }} px-3 py-2">
                                <i class="bi bi-arrow-{{ $order['type'] === 'long' ? 'up' : 'down' }}-right me-1"></i>
                                {{ strtoupper($order['type']) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">${{ number_format($order['entry_price'], 2) }}</div>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">
                                    <i class="bi bi-bullseye"></i> ${{ number_format($order['take_profit'], 2) }}
                                </div>
                                <div class="text-danger">
                                    <i class="bi bi-shield-exclamation"></i> ${{ number_format($order['stop_loss'], 2) }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info">{{ $order['leverage'] }}x</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <span class="fw-bold small text-primary">{{ $order['users_count'] }}</span>
                                </div>
                                <span class="small text-muted">user(s)</span>
                            </div>
                        </td>
                        <td>
                            <div class="small">{{ $order['created_at']->diffForHumans() }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $order['created_at']->format('M d, H:i') }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-warning me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="small text-warning fw-semibold">Awaiting Fill</span>
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-info" onclick="showPendingOrderDetails({{ $order['signal_id'] }})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="showCancelPendingOrder({{ $order['signal_id'] }})" title="Cancel Order">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 p-4">
        <div class="d-flex align-items-center text-muted small">
            <i class="bi bi-info-circle me-2"></i>
            Pending orders are checked every 2 minutes. Positions will be created automatically when orders are filled.
        </div>
    </div>
</div>
@endif

<!-- Active Positions -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">Active Positions</h5>
                <p class="text-muted small mb-0">Real-time monitoring of open positions</p>
            </div>
            <div class="d-flex gap-2">
                <!-- Filters and sorting remain the same -->
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">User</th>
                        <th class="border-0 py-3 fw-semibold">Symbol</th>
                        <th class="border-0 py-3 fw-semibold">Side</th>
                        <th class="border-0 py-3 fw-semibold">Entry</th>
                        <th class="border-0 py-3 fw-semibold">Current</th>
                        <th class="border-0 py-3 fw-semibold">Unrealized P&L</th>
                        <th class="border-0 py-3 fw-semibold">Progress</th>
                        <th class="border-0 py-3 fw-semibold">Health</th>
                        <th class="border-0 px-4 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positions as $position)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="bi bi-person-fill text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $position->user->name }}</div>
                                    <div class="text-muted small">{{ $position->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $position->symbol }}</div>
                            <div class="small text-muted">{{ $position->leverage }}x</div>
                        </td>
                        <td>
                            <span class="badge {{ $position->side === 'long' ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $position->side === 'long' ? 'text-success' : 'text-danger' }}">
                                {{ strtoupper($position->side) }}
                            </span>
                        </td>
                        <td>${{ number_format($position->entry_price, 2) }}</td>
                        <td>${{ number_format($position->live_current_price, 2) }}</td>
                        <td>
                            <div class="fw-semibold {{ $position->live_unrealized_pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $position->live_unrealized_pnl >= 0 ? '+' : '' }}${{ number_format($position->live_unrealized_pnl, 2) }}
                            </div>
                            <div class="small text-muted">
                                {{ $position->live_unrealized_pnl_percent >= 0 ? '+' : '' }}{{ number_format($position->live_unrealized_pnl_percent, 2) }}%
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 8px; width: 60px;">
                                    <div class="progress-bar {{ $position->live_unrealized_pnl >= 0 ? 'bg-success' : 'bg-danger' }}" 
                                         style="width: {{ min(abs($position->progress_to_tp ?? 0), 100) }}%"></div>
                                </div>
                                <span class="ms-2 small">{{ number_format($position->progress_to_tp ?? 0, 0) }}%</span>
                            </div>
                        </td>
                        <td>
                            @if($position->health_status['status'] === 'healthy')
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-check-circle"></i> Healthy
                                </span>
                            @elseif($position->health_status['status'] === 'warning')
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Warning
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-x-circle"></i> Critical
                                </span>
                            @endif
                        </td>
                        <td class="px-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="showPositionDetails({{ $position->id }})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="showForceCloseConfirmation({{ $position->id }})" title="Force Close">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted">No active positions to monitor.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($positions->hasPages())
    <div class="card-footer bg-transparent border-0 p-4">
        {{ $positions->links() }}
    </div>
    @endif
</div>

<!-- Bootstrap 5 Modals -->

<!-- Generic Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0" id="confirmModalHeader">
                <h5 class="modal-title fw-bold" id="confirmModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmModalAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5" id="successModalBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger bg-opacity-10 border-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Error
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="errorModalBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Position Details Modal -->
<div class="modal fade" id="positionDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-graph-up me-2"></i>Position Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="positionDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-3">Loading position details...</p>
                </div>
            </div>
            <div class="modal-footer border-0" id="positionDetailsFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Pending Order Details Modal -->
<div class="modal fade" id="pendingOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-hourglass-split me-2"></i>Pending Order Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pendingOrderContent">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-bell-fill me-2"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastBody">
            <!-- Dynamic content -->
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-refresh settings
    let autoRefreshInterval;
    let countdownInterval;
    const refreshTime = 30; // seconds
    let countdown = refreshTime;

    // Initialize Bootstrap toast
    const toastElement = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastElement);

    // Helper: Show toast notification
    function showToast(title, message, type = 'info') {
        const iconMap = {
            'success': 'bi-check-circle-fill text-success',
            'error': 'bi-x-circle-fill text-danger',
            'warning': 'bi-exclamation-triangle-fill text-warning',
            'info': 'bi-info-circle-fill text-primary'
        };
        
        const toastHeader = document.querySelector('#liveToast .toast-header');
        const icon = toastHeader.querySelector('i');
        icon.className = `bi ${iconMap[type] || iconMap['info']} me-2`;
        
        document.getElementById('toastTitle').textContent = title;
        document.getElementById('toastBody').textContent = message;
        toast.show();
    }

    // Helper: Show confirmation modal
    function showConfirmModal(title, message, actionText, actionClass, callback) {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        
        document.getElementById('confirmModalTitle').textContent = title;
        document.getElementById('confirmModalBody').innerHTML = message;
        
        const actionBtn = document.getElementById('confirmModalAction');
        actionBtn.textContent = actionText;
        actionBtn.className = `btn ${actionClass}`;
        
        // Remove old event listeners
        const newActionBtn = actionBtn.cloneNode(true);
        actionBtn.parentNode.replaceChild(newActionBtn, actionBtn);
        
        // Add new event listener
        newActionBtn.addEventListener('click', function() {
            modal.hide();
            if (callback) callback();
        });
        
        modal.show();
    }

    // Helper: Show success modal
    function showSuccessModal(content, callback) {
        const modal = new bootstrap.Modal(document.getElementById('successModal'));
        document.getElementById('successModalBody').innerHTML = content;
        
        modal.show();
        
        if (callback) {
            document.getElementById('successModal').addEventListener('hidden.bs.modal', callback, { once: true });
        }
    }

    // Helper: Show error modal
    function showErrorModal(message) {
        const modal = new bootstrap.Modal(document.getElementById('errorModal'));
        document.getElementById('errorModalBody').innerHTML = `
            <div class="alert alert-danger border-0 mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ${message}
            </div>
        `;
        modal.show();
    }

    // Refresh all monitors with confirmation
    function showRefreshConfirmation() {
        showConfirmModal(
            'Refresh All Monitors',
            '<p>This will refresh position data for all active users from Bybit.</p><p class="small text-muted mb-0">This may take a few moments to complete.</p>',
            'Refresh',
            'btn-primary',
            function() {
                refreshAllMonitors();
            }
        );
    }

    function refreshAllMonitors() {
        showToast('Processing', 'Refreshing all monitors...', 'info');
        
        fetch('/admin/monitoring/refresh-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessModal(
                    `<div class="text-center">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Monitors Refreshed!</h5>
                        <p class="text-muted">Updated ${data.updated} positions successfully.</p>
                    </div>`,
                    function() { location.reload(); }
                );
            } else {
                showErrorModal('Failed to refresh monitors: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorModal('An error occurred while refreshing monitors.');
        });
    }

    // Show position details
    function showPositionDetails(positionId) {
        const modal = new bootstrap.Modal(document.getElementById('positionDetailsModal'));
        modal.show();
        
        fetch(`/admin/monitoring/positions/${positionId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.position) {
                const position = data.position;
                const pnlClass = position.unrealized_pnl >= 0 ? 'success' : 'danger';
                
                const content = `
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-body-secondary">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Position Info</h6>
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td class="text-muted">Symbol:</td>
                                            <td class="fw-semibold">${position.symbol}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Side:</td>
                                            <td>
                                                <span class="badge bg-${position.side === 'long' ? 'success' : 'danger'}">
                                                    ${position.side.toUpperCase()}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Quantity:</td>
                                            <td>${position.quantity}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Leverage:</td>
                                            <td><span class="badge bg-info">${position.leverage}x</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-body-secondary">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Price Levels</h6>
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td class="text-muted">Entry:</td>
                                            <td class="fw-semibold">$${parseFloat(position.entry_price).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Current:</td>
                                            <td class="fw-semibold">$${parseFloat(position.current_price).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Take Profit:</td>
                                            <td class="text-success">$${parseFloat(position.take_profit).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Stop Loss:</td>
                                            <td class="text-danger">$${parseFloat(position.stop_loss).toFixed(2)}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border-0 bg-${pnlClass} bg-opacity-10">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Performance</h6>
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td class="text-muted">Unrealized P&L:</td>
                                            <td class="fw-bold text-${pnlClass}">
                                                ${position.unrealized_pnl >= 0 ? '+' : ''}$${parseFloat(position.unrealized_pnl).toFixed(2)}
                                                (${position.unrealized_pnl_percent >= 0 ? '+' : ''}${parseFloat(position.unrealized_pnl_percent).toFixed(2)}%)
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Progress to TP:</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-${pnlClass}" 
                                                             style="width: ${Math.abs(position.progress_to_tp)}%"></div>
                                                    </div>
                                                    <span class="small">${Math.round(position.progress_to_tp)}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Health Status:</td>
                                            <td>
                                                <span class="badge bg-${position.health_status.status === 'healthy' ? 'success' : 
                                                    position.health_status.status === 'warning' ? 'warning' : 'danger'}">
                                                    ${position.health_status.message}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Last Updated:</td>
                                            <td class="small">${new Date(position.last_updated_at).toLocaleString()}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('positionDetailsContent').innerHTML = content;
                document.getElementById('positionDetailsFooter').innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="showRestartMonitor(${positionId})">
                        <i class="bi bi-arrow-clockwise me-2"></i>Restart Monitor
                    </button>
                    <button type="button" class="btn btn-danger" onclick="showForceCloseConfirmation(${positionId})">
                        <i class="bi bi-x-circle me-2"></i>Force Close
                    </button>
                `;
            } else {
                document.getElementById('positionDetailsContent').innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Failed to load position details.
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('positionDetailsContent').innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    An error occurred while loading position details.
                </div>
            `;
        });
    }

    // Restart monitor
    function showRestartMonitor(positionId) {
        bootstrap.Modal.getInstance(document.getElementById('positionDetailsModal')).hide();
        
        showConfirmModal(
            'Restart Monitor',
            '<p>Are you sure you want to restart monitoring for this position?</p><p class="small text-muted mb-0">This will force a position data refresh from the exchange.</p>',
            'Restart',
            'btn-success',
            function() {
                showToast('Processing', 'Restarting monitor...', 'info');
                
                fetch(`/admin/monitoring/positions/${positionId}/restart`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessModal(
                            '<div class="text-center"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i><h5 class="mt-3">Monitor Restarted!</h5><p class="text-muted">The position monitor has been refreshed.</p></div>',
                            function() { location.reload(); }
                        );
                    } else {
                        showErrorModal('Failed to restart monitor: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('An error occurred while restarting the monitor.');
                });
            }
        );
    }

    // Force close position
    function showForceCloseConfirmation(positionId) {
        // Close position details modal if open
        const detailsModal = bootstrap.Modal.getInstance(document.getElementById('positionDetailsModal'));
        if (detailsModal) detailsModal.hide();
        
        showConfirmModal(
            'Force Close Position',
            `<div class="alert alert-danger border-0 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Warning:</strong> This action cannot be undone!
            </div>
            <p>Are you sure you want to force close this position?</p>
            <p class="small text-muted mb-0">The position will be closed at the current market price.</p>`,
            'Force Close',
            'btn-danger',
            function() {
                showToast('Processing', 'Closing position...', 'info');
                
                fetch(`/admin/monitoring/positions/${positionId}/force-close`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const pnlClass = data.pnl >= 0 ? 'success' : 'danger';
                        const pnlIcon = data.pnl >= 0 ? 'bi-arrow-up-circle' : 'bi-arrow-down-circle';
                        
                        showSuccessModal(
                            `<div class="text-center">
                                <i class="bi ${pnlIcon} text-${pnlClass}" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Position Closed!</h5>
                                <div class="alert alert-${pnlClass} bg-${pnlClass} bg-opacity-10 border-0 mt-3">
                                    <strong>Realized P&L:</strong> 
                                    <span class="fs-5">${data.pnl >= 0 ? '+' : ''}$${parseFloat(data.pnl).toFixed(2)}</span>
                                </div>
                            </div>`,
                            function() { location.reload(); }
                        );
                    } else {
                        showErrorModal('Failed to close position: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('An error occurred while closing the position.');
                });
            }
        );
    }

    // Show pending order details
    function showPendingOrderDetails(signalId) {
        const modal = new bootstrap.Modal(document.getElementById('pendingOrderModal'));
        modal.show();
        
        // Show loading state
        document.getElementById('pendingOrderContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3">Loading pending order details...</p>
            </div>
        `;
        
        fetch(`/admin/monitoring/pending-orders/${signalId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.order) {
                const order = data.order;
                const pnlDistance = order.order.type === 'long' 
                    ? ((order.order.take_profit - order.order.entry_price) / order.order.entry_price * 100).toFixed(2)
                    : ((order.order.entry_price - order.order.take_profit) / order.order.entry_price * 100).toFixed(2);
                
                const content = `
                    <div class="row g-4">
                        <!-- Order Information -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-body-secondary">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-info-circle me-2"></i>Order Information
                                    </h6>
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td class="text-muted">Symbol:</td>
                                            <td class="fw-semibold">${order.order.symbol}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Type:</td>
                                            <td>
                                                <span class="badge bg-${order.order.type === 'long' ? 'success' : 'danger'}">
                                                    ${order.order.type.toUpperCase()}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Order Type:</td>
                                            <td><span class="badge bg-warning">${order.order.order_type}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Entry Price:</td>
                                            <td class="fw-semibold">$${parseFloat(order.order.entry_price).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Take Profit:</td>
                                            <td class="text-success">$${parseFloat(order.order.take_profit).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Stop Loss:</td>
                                            <td class="text-danger">$${parseFloat(order.order.stop_loss).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Leverage:</td>
                                            <td><span class="badge bg-info">${order.order.leverage}x</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Potential Profit:</td>
                                            <td class="text-success fw-semibold">+${pnlDistance}%</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Signal Information -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-body-secondary">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-graph-up me-2"></i>Signal Information
                                    </h6>
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td class="text-muted">Signal ID:</td>
                                            <td class="fw-semibold">#${order.signal_id}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Pattern:</td>
                                            <td>${order.signal.pattern}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Timeframe:</td>
                                            <td>${order.signal.timeframe}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Confidence:</td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-success" style="width: ${order.signal.confidence}%"></div>
                                                </div>
                                                <small class="text-muted">${order.signal.confidence}%</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-hourglass-split"></i> ${order.signal.status}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Created:</td>
                                            <td class="small">${order.order.created_at}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Pending For:</td>
                                            <td class="small">${order.stats.pending_duration}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="col-12">
                            <div class="card border-0 bg-primary bg-opacity-10">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-bar-chart me-2"></i>Order Statistics
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="text-muted small">Total Users</div>
                                            <div class="h4 mb-0 fw-bold">${order.stats.total_users}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-muted small">Total Quantity</div>
                                            <div class="h4 mb-0 fw-bold">${parseFloat(order.stats.total_quantity).toFixed(4)}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-muted small">Admin Included</div>
                                            <div class="h4 mb-0">
                                                ${order.stats.admin_included 
                                                    ? '<i class="bi bi-check-circle-fill text-success"></i>' 
                                                    : '<i class="bi bi-x-circle-fill text-danger"></i>'}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-muted small">Order ID</div>
                                            <div class="small fw-semibold text-truncate">${order.order.exchange_order_id || 'N/A'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users List -->
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-people-fill me-2"></i>Users with Pending Orders (${order.users.length})
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-body-secondary">
                                        <tr>
                                            <th class="border-0 py-2">User</th>
                                            <th class="border-0 py-2">Email</th>
                                            <th class="border-0 py-2">Quantity</th>
                                            <th class="border-0 py-2">Order ID</th>
                                            <th class="border-0 py-2">Exchange</th>
                                            <th class="border-0 py-2">Placed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${order.users.map(user => `
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                            <i class="bi bi-person-fill text-primary"></i>
                                                        </div>
                                                        <div class="fw-semibold">${user.name}</div>
                                                    </div>
                                                </td>
                                                <td class="small text-muted">${user.email}</td>
                                                <td>${parseFloat(user.quantity).toFixed(4)}</td>
                                                <td class="small font-monospace">${user.exchange_order_id ? user.exchange_order_id.substring(0, 12) + '...' : 'N/A'}</td>
                                                <td>
                                                    ${user.exchange_connected 
                                                        ? '<span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i> Connected</span>'
                                                        : '<span class="badge bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle"></i> Not Connected</span>'}
                                                </td>
                                                <td class="small">${user.created_at}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-12">
                            <div class="alert alert-info border-0 mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                    <div>
                                        <strong>Monitoring Active:</strong> This order is being checked every 2 minutes. 
                                        Positions will be created automatically when the order is filled on Bybit.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('pendingOrderContent').innerHTML = content;
            } else {
                document.getElementById('pendingOrderContent').innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Failed to load pending order details.'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('pendingOrderContent').innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    An error occurred while loading pending order details.
                </div>
            `;
        });
    }

    // Cancel pending order
    function showCancelPendingOrder(signalId) {
        showConfirmModal(
            'Cancel Pending Orders',
            `<div class="alert alert-warning border-0 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Warning:</strong> This will cancel the orders on Bybit for ALL users!
            </div>
            <p>Are you sure you want to cancel all pending limit orders for this signal?</p>
            <p class="small text-muted mb-0">The orders will be cancelled on Bybit for all affected users. This action cannot be undone.</p>`,
            'Cancel Orders',
            'btn-danger',
            function() {
                showToast('Processing', 'Cancelling orders...', 'info');
                
                fetch(`/admin/monitoring/pending-orders/${signalId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let message = `<div class="text-center">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Orders Cancelled Successfully!</h5>
                            <div class="alert alert-success bg-success bg-opacity-10 border-0 mt-3">
                                <strong>Cancelled:</strong> ${data.cancelled} order(s)
                            </div>`;
                        
                        if (data.failed > 0) {
                            message += `
                                <div class="alert alert-warning bg-warning bg-opacity-10 border-0 mt-2">
                                    <strong>Failed:</strong> ${data.failed} order(s)
                                    <div class="small mt-2">
                                        ${data.errors.map(err => `<div>• ${err}</div>`).join('')}
                                    </div>
                                </div>`;
                        }
                        
                        message += `</div>`;
                        
                        showSuccessModal(message, function() { location.reload(); });
                    } else {
                        showErrorModal('Failed to cancel orders: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('An error occurred while cancelling the orders.');
                });
            }
        );
    }

    // Dismiss alert
    function showDismissAlert(alertType) {
        showConfirmModal(
            'Dismiss Alert',
            '<p>Are you sure you want to dismiss this alert?</p>',
            'Dismiss',
            'btn-secondary',
            function() {
                const listItem = event.target.closest('.list-group-item');
                if (listItem) {
                    listItem.style.transition = 'opacity 0.3s';
                    listItem.style.opacity = '0';
                    setTimeout(() => {
                        listItem.remove();
                        showToast('Alert Dismissed', 'The alert has been dismissed.', 'success');
                    }, 300);
                }
            }
        );
    }

    // Auto-refresh functionality
    function startAutoRefresh() {
        countdown = refreshTime;
        updateCountdown();
        
        autoRefreshInterval = setInterval(() => {
            location.reload();
        }, refreshTime * 1000);
        
        countdownInterval = setInterval(() => {
            countdown--;
            updateCountdown();
            if (countdown <= 0) countdown = refreshTime;
        }, 1000);
    }

    function updateCountdown() {
        const countdownEl = document.getElementById('refreshCountdown');
        if (countdownEl) {
            countdownEl.textContent = countdown;
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh every 30 seconds
        // startAutoRefresh();
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        if (countdownInterval) clearInterval(countdownInterval);
    });
</script>
@endpush