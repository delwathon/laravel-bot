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
                    <button class="btn btn-light" onclick="refreshAllMonitors()">
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
<div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
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
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            @if($error['severity'] === 'critical')
                                <span class="badge bg-danger me-2">Critical</span>
                            @elseif($error['severity'] === 'error')
                                <span class="badge bg-danger me-2">Error</span>
                            @else
                                <span class="badge bg-warning me-2">Warning</span>
                            @endif
                            <small class="text-muted">{{ $error['timestamp']->diffForHumans() }}</small>
                        </div>
                        <div class="fw-semibold">{{ $error['message'] }}</div>
                        <div class="small text-muted mt-1">Type: {{ ucwords(str_replace('_', ' ', $error['type'])) }}</div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        @if(isset($error['position_id']))
                            <button class="btn btn-outline-primary" onclick="viewPositionDetails({{ $error['position_id'] }})">
                                <i class="bi bi-eye"></i>
                            </button>
                        @endif
                        <button class="btn btn-outline-secondary" onclick="dismissAlert('{{ $error['type'] }}')">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.monitoring.overview') }}" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label fw-semibold small">User Filter</label>
                    <input type="text" class="form-control" name="user_search" placeholder="Search by user name or ID..." value="{{ request('user_search') }}">
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold small">Status</label>
                    <select class="form-select" name="status_filter">
                        <option value="">All Status</option>
                        <option value="profitable" {{ request('status_filter') === 'profitable' ? 'selected' : '' }}>Profitable</option>
                        <option value="losing" {{ request('status_filter') === 'losing' ? 'selected' : '' }}>Losing</option>
                        <option value="at_risk" {{ request('status_filter') === 'at_risk' ? 'selected' : '' }}>At Risk</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold small">Pair</label>
                    <select class="form-select" name="pair_filter">
                        <option value="">All Pairs</option>
                        @foreach($tradingPairs as $pair)
                            <option value="{{ $pair }}" {{ request('pair_filter') === $pair ? 'selected' : '' }}>{{ $pair }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.monitoring.overview') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Active Positions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-activity me-2"></i>Active Positions Monitor
                <span class="badge bg-success bg-opacity-10 text-success ms-2">
                    <i class="bi bi-broadcast-pin"></i> LIVE DATA
                </span>
            </h5>
            <div class="d-flex gap-2">
                <span class="badge bg-primary" id="live-indicator">
                    <i class="bi bi-circle-fill" style="font-size: 6px;"></i> LIVE
                </span>
                <span class="text-muted small">Auto-refresh: <span id="countdown">10</span>s</span>
            </div>
        </div>
        <small class="text-muted">Prices updated directly from Bybit API in real-time</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="px-4 py-3 fw-semibold">User</th>
                        <th class="py-3 fw-semibold">Symbol</th>
                        <th class="py-3 fw-semibold">Side</th>
                        <th class="py-3 fw-semibold">Entry</th>
                        <th class="py-3 fw-semibold">Current</th>
                        <th class="py-3 fw-semibold">TP / SL</th>
                        <th class="py-3 fw-semibold">Unrealized P&L</th>
                        <th class="py-3 fw-semibold">Status</th>
                        <th class="py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="positions-table-body">
                    @forelse($positions as $position)
                    <tr data-position-id="{{ $position->id }}">
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2 flex-shrink-0">
                                    <i class="bi bi-person-fill text-info"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $position->user->name }}</div>
                                    <small class="text-muted">#{{ $position->user->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-{{ $position->side === 'long' ? 'success' : 'danger' }} bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-coin text-{{ $position->side === 'long' ? 'success' : 'danger' }}"></i>
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
                            <small class="text-muted">{{ $position->time_ago }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-{{ $position->unrealized_pnl >= 0 ? 'success' : 'danger' }}">
                                ${{ number_format($position->current_price, 2) }}
                                @if(isset($position->is_live_data) && $position->is_live_data)
                                    <i class="bi bi-broadcast-pin text-success ms-1" title="LIVE from Bybit" style="font-size: 10px;"></i>
                                @else
                                    <i class="bi bi-database text-muted ms-1" title="Cached data" style="font-size: 10px;"></i>
                                @endif
                            </div>
                            <small class="text-{{ $position->unrealized_pnl >= 0 ? 'success' : 'danger' }}">
                                {{ $position->unrealized_pnl >= 0 ? '+' : '' }}{{ number_format($position->unrealized_pnl_percent, 2) }}%
                            </small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">TP: ${{ number_format($position->take_profit, 2) }}</div>
                                <div class="text-danger">SL: ${{ number_format($position->stop_loss, 2) }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-{{ $position->unrealized_pnl >= 0 ? 'success' : 'danger' }} fw-bold">
                                {{ $position->unrealized_pnl >= 0 ? '+' : '' }}${{ number_format($position->unrealized_pnl, 2) }}
                                <small class="text-{{ $position->unrealized_pnl >= 0 ? 'success' : 'danger' }}">
                                    ({{ $position->unrealized_pnl >= 0 ? '+' : '' }}{{ number_format($position->unrealized_pnl_percent, 2) }}%)
                                </small>
                            </div>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-{{ $position->unrealized_pnl >= 0 ? 'success' : 'danger' }}" 
                                     style="width: {{ abs($position->progress_to_tp) }}%"></div>
                            </div>
                            <small class="text-muted">{{ round($position->progress_to_tp) }}% to TP</small>
                        </td>
                        <td>
                            @php
                                $health = $position->health_status;
                                $statusColors = [
                                    'healthy' => 'success',
                                    'active' => 'primary',
                                    'warning' => 'warning',
                                    'critical' => 'danger',
                                ];
                                $color = $statusColors[$health['status']] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> {{ ucfirst($health['status']) }}
                            </span>
                            <div class="small text-muted">{{ $health['message'] }}</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewPositionDetails({{ $position->id }})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="restartMonitor({{ $position->id }})" title="Restart Monitor">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="forceClosePosition({{ $position->id }})" title="Force Close">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">No active positions found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($positions->hasPages())
    <div class="card-footer bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing {{ $positions->firstItem() }} to {{ $positions->lastItem() }} of {{ $positions->total() }} active positions
            </div>
            {{ $positions->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Position Details Modal -->
<div class="modal fade" id="positionDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-primary bg-opacity-10">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-graph-up me-2"></i>Position Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="positionDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0" id="positionDetailsFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0" id="confirmModalHeader">
                <h5 class="modal-title fw-bold" id="confirmModalTitle">
                    <i class="bi bi-question-circle me-2"></i>Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                Are you sure you want to proceed?
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
        <div class="modal-content">
            <div class="modal-header border-0 bg-success bg-opacity-10">
                <h5 class="modal-title fw-bold text-success">
                    <i class="bi bi-check-circle me-2"></i>Success
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="successModalBody">
                Operation completed successfully!
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="errorModalBody">
                An error occurred. Please try again.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" id="toastHeader">
            <i class="bi bi-bell-fill me-2" id="toastIcon"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <small id="toastTime">Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastBody">
            This is a toast notification.
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Global variables
    let autoRefreshInterval;
    let countdownInterval;
    let countdown = 10;
    let confirmCallback = null;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Add pulse animation CSS
        addPulseAnimation();
        
        // Start auto-refresh
        startAutoRefresh();
    });

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================

    // Add pulse animation CSS
    function addPulseAnimation() {
        if (!document.getElementById('pulse-animation-style')) {
            const pulseStyle = document.createElement('style');
            pulseStyle.id = 'pulse-animation-style';
            pulseStyle.textContent = `
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
                .pulse {
                    animation: pulse 0.5s ease-in-out;
                }
            `;
            document.head.appendChild(pulseStyle);
        }
    }

    // Show toast notification
    function showToast(title, message, type = 'info') {
        const toastEl = document.getElementById('liveToast');
        const toastHeader = document.getElementById('toastHeader');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastBody = document.getElementById('toastBody');
        const toastTime = document.getElementById('toastTime');

        // Set icon and colors based on type
        const types = {
            'success': { icon: 'bi-check-circle-fill', class: 'bg-success text-white' },
            'error': { icon: 'bi-exclamation-triangle-fill', class: 'bg-danger text-white' },
            'warning': { icon: 'bi-exclamation-circle-fill', class: 'bg-warning text-dark' },
            'info': { icon: 'bi-info-circle-fill', class: 'bg-primary text-white' }
        };

        const config = types[type] || types.info;

        // Remove old classes
        toastHeader.className = 'toast-header ' + config.class;
        toastIcon.className = config.icon + ' me-2';
        
        // Set content
        toastTitle.textContent = title;
        toastBody.textContent = message;
        toastTime.textContent = 'Just now';

        // Show toast
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    // Show confirmation modal
    function showConfirmModal(title, message, confirmText, confirmClass, onConfirm) {
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        const modalHeader = document.getElementById('confirmModalHeader');
        const modalTitle = document.getElementById('confirmModalTitle');
        const modalBody = document.getElementById('confirmModalBody');
        const confirmBtn = document.getElementById('confirmModalAction');

        // Set content
        modalTitle.innerHTML = `<i class="bi bi-question-circle me-2"></i>${title}`;
        modalBody.innerHTML = message;
        confirmBtn.textContent = confirmText;
        confirmBtn.className = 'btn ' + confirmClass;

        // Set color based on type
        if (confirmClass.includes('danger')) {
            modalHeader.className = 'modal-header border-0 bg-danger bg-opacity-10';
            modalTitle.className = 'modal-title fw-bold text-danger';
        } else if (confirmClass.includes('warning')) {
            modalHeader.className = 'modal-header border-0 bg-warning bg-opacity-10';
            modalTitle.className = 'modal-title fw-bold text-warning';
        } else {
            modalHeader.className = 'modal-header border-0 bg-primary bg-opacity-10';
            modalTitle.className = 'modal-title fw-bold text-primary';
        }

        // Set callback
        confirmCallback = onConfirm;

        // Remove old event listeners and add new one
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', function() {
            modal.hide();
            if (confirmCallback) {
                confirmCallback();
                confirmCallback = null;
            }
        });

        modal.show();
    }

    // Show success modal
    function showSuccessModal(message, onClose) {
        const modal = new bootstrap.Modal(document.getElementById('successModal'));
        document.getElementById('successModalBody').innerHTML = message;
        
        if (onClose) {
            document.getElementById('successModal').addEventListener('hidden.bs.modal', function() {
                onClose();
            }, { once: true });
        }
        
        modal.show();
    }

    // Show error modal
    function showErrorModal(message) {
        const modal = new bootstrap.Modal(document.getElementById('errorModal'));
        document.getElementById('errorModalBody').innerHTML = message;
        modal.show();
    }

    // ============================================
    // AUTO-REFRESH FUNCTIONS
    // ============================================

    // Start auto-refresh
    function startAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        if (countdownInterval) clearInterval(countdownInterval);
        
        countdown = 10;
        
        // Countdown timer
        countdownInterval = setInterval(() => {
            countdown--;
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                countdown = 10;
            }
        }, 1000);
        
        // Auto-refresh every 10 seconds
        autoRefreshInterval = setInterval(() => {
            refreshPositionsTable();
        }, 10000);
    }

    // Refresh positions table
    function refreshPositionsTable() {
        console.log('Auto-refreshing positions...');
        
        const indicator = document.getElementById('live-indicator');
        if (indicator) {
            indicator.classList.add('pulse');
        }
        
        const url = new URL(window.location.href);
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableBody = doc.querySelector('#positions-table-body');
            
            if (newTableBody) {
                const currentTableBody = document.querySelector('#positions-table-body');
                if (currentTableBody) {
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                }
            }
            
            setTimeout(() => {
                if (indicator) {
                    indicator.classList.remove('pulse');
                }
            }, 500);
        })
        .catch(error => {
            console.error('Auto-refresh failed:', error);
        });
    }

    // ============================================
    // MONITORING ACTIONS
    // ============================================

    // Refresh all monitors
    function refreshAllMonitors() {
        showConfirmModal(
            'Refresh All Monitors',
            `<p class="mb-3">This will restart all position monitoring scripts.</p>
            <div class="alert alert-info border-0 mb-0">
                <strong>This action will:</strong>
                <ul class="mb-0 mt-2 small">
                    <li>Restart monitoring for all users</li>
                    <li>Re-sync position data from exchanges</li>
                    <li>Update all TP/SL tracking</li>
                    <li>Take approximately 30-60 seconds</li>
                </ul>
            </div>`,
            'Start Refresh',
            'btn-primary',
            function() {
                const btn = document.querySelector('button[onclick="refreshAllMonitors()"]');
                if (btn) {
                    const originalHTML = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Refreshing...';
                    
                    fetch('{{ route("admin.monitoring.refresh-all") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ force_refresh: false })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessModal(
                                `<div class="text-center">
                                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 mb-3">Monitors Refreshed Successfully!</h5>
                                    <table class="table table-sm">
                                        <tr><td class="text-muted">Total Positions:</td><td class="fw-bold">${data.results.total}</td></tr>
                                        <tr><td class="text-muted">Updated:</td><td class="fw-bold text-success">${data.results.updated}</td></tr>
                                        <tr><td class="text-muted">Closed:</td><td class="fw-bold text-info">${data.results.closed}</td></tr>
                                        <tr><td class="text-muted">Errors:</td><td class="fw-bold text-danger">${data.results.errors}</td></tr>
                                    </table>
                                </div>`,
                                function() { location.reload(); }
                            );
                        } else {
                            showErrorModal('Failed to refresh monitors: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorModal('An error occurred while refreshing monitors. Please check the console for details.');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    });
                }
            }
        );
    }

    // ============================================
    // POSITION ACTIONS
    // ============================================

    // View position details
    function viewPositionDetails(positionId) {
        const modal = new bootstrap.Modal(document.getElementById('positionDetailsModal'));
        modal.show();
        
        document.getElementById('positionDetailsContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3">Loading position details...</p>
            </div>
        `;
        
        fetch(`/admin/monitoring/positions/${positionId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const position = data.position;
                const content = `
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-info-circle me-2"></i>Position Information
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">User:</td>
                                    <td class="fw-semibold">${position.user.name}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Symbol:</td>
                                    <td class="fw-semibold">${position.symbol}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Side:</td>
                                    <td><span class="badge bg-${position.side === 'long' ? 'success' : 'danger'}">${position.side.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Entry Price:</td>
                                    <td>$${parseFloat(position.entry_price).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Current Price:</td>
                                    <td class="fw-bold text-${position.unrealized_pnl >= 0 ? 'success' : 'danger'}">$${parseFloat(position.current_price).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Quantity:</td>
                                    <td>${parseFloat(position.quantity).toFixed(3)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Leverage:</td>
                                    <td><span class="badge bg-primary">${position.leverage}x</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-shield-check me-2"></i>Risk Management
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Take Profit:</td>
                                    <td class="text-success fw-semibold">$${parseFloat(position.take_profit).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Stop Loss:</td>
                                    <td class="text-danger fw-semibold">$${parseFloat(position.stop_loss).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Unrealized P&L:</td>
                                    <td class="fw-bold text-${position.unrealized_pnl >= 0 ? 'success' : 'danger'}">
                                        ${position.unrealized_pnl >= 0 ? '+' : ''}$${parseFloat(position.unrealized_pnl).toFixed(2)}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">P&L %:</td>
                                    <td class="fw-bold text-${position.unrealized_pnl >= 0 ? 'success' : 'danger'}">
                                        ${position.unrealized_pnl >= 0 ? '+' : ''}${parseFloat(position.unrealized_pnl_percent).toFixed(2)}%
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Progress to TP:</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-${position.unrealized_pnl >= 0 ? 'success' : 'danger'}" 
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
                `;
                
                document.getElementById('positionDetailsContent').innerHTML = content;
                document.getElementById('positionDetailsFooter').innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="restartMonitor(${positionId}); bootstrap.Modal.getInstance(document.getElementById('positionDetailsModal')).hide();">
                        <i class="bi bi-arrow-clockwise me-2"></i>Restart Monitor
                    </button>
                    <button type="button" class="btn btn-danger" onclick="forceClosePosition(${positionId}); bootstrap.Modal.getInstance(document.getElementById('positionDetailsModal')).hide();">
                        <i class="bi bi-x-circle me-2"></i>Force Close
                    </button>
                `;
            } else {
                document.getElementById('positionDetailsContent').innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Failed to load position details. Please try again.
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

    // Restart monitor for position
    function restartMonitor(positionId) {
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
                            '<div class="text-center"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i><h5 class="mt-3">Monitor Restarted Successfully!</h5><p class="text-muted">The position monitor has been refreshed.</p></div>',
                            function() { location.reload(); }
                        );
                    } else {
                        showErrorModal('Failed to restart monitor: ' + data.message);
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
    function forceClosePosition(positionId) {
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
                                <h5 class="mt-3">Position Closed Successfully!</h5>
                                <div class="alert alert-${pnlClass} bg-${pnlClass} bg-opacity-10 border-0 mt-3">
                                    <strong>Realized P&L:</strong> 
                                    <span class="fs-5">${data.pnl >= 0 ? '+' : ''}$${parseFloat(data.pnl).toFixed(2)}</span>
                                </div>
                            </div>`,
                            function() { location.reload(); }
                        );
                    } else {
                        showErrorModal('Failed to close position: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('An error occurred while closing the position.');
                });
            }
        );
    }

    // Dismiss alert
    function dismissAlert(alertType) {
        const listItem = event.target.closest('.list-group-item');
        if (listItem) {
            listItem.style.transition = 'opacity 0.3s';
            listItem.style.opacity = '0';
            setTimeout(() => listItem.remove(), 300);
            showToast('Alert Dismissed', 'The alert has been dismissed.', 'info');
        }
    }

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        if (countdownInterval) clearInterval(countdownInterval);
    });
</script>
@endpush