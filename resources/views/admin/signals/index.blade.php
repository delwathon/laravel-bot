@extends('layouts.app')

@section('title', 'Signal Generator - CryptoBot Pro')

@section('page-title', 'Signal Generator')

@section('content')
<!-- Signal Status Overview -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm bg-light text-white h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3">
                                <i class="bi bi-lightning-charge-fill fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">SMC Signal Generator</h4>
                                <p class="mb-0 opacity-75">Smart Money Concepts Analysis Engine</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div>
                                <div class="text-white text-opacity-75 small">Next Run</div>
                                <div class="fw-bold fs-5" id="nextRunTime">Calculating...</div>
                            </div>
                            <div class="vr"></div>
                            <div>
                                <div class="text-white text-opacity-75 small">Interval</div>
                                <div class="fw-bold fs-5">Every {{ $signalInterval }} min</div>
                            </div>
                            <div class="vr"></div>
                            <div>
                                <div class="text-white text-opacity-75 small">Top Signals</div>
                                <div class="fw-bold fs-5">{{ $topSignalsCount }} pairs</div>
                            </div>
                        </div>
                        <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#generateSignalModal">
                            <i class="bi bi-play-circle-fill me-2"></i>Generate Now
                        </button>
                        <a href="{{ route('admin.settings.signal-generator') }}" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-gear me-2"></i>Configure
                        </a>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex p-5 mb-3">
                            <i class="bi bi-robot fs-1"></i>
                        </div>
                        <div>
                            <span class="badge bg-success bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-2">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> System Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-graph-up-arrow text-success me-2"></i>
                    Signal Performance (24h)
                </h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Generated</span>
                        <span class="fw-bold">{{ $stats['total_signals_today'] }} signals</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Executed</span>
                        <span class="fw-bold text-success">{{ $stats['executed_signals'] }} trades</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Win Rate</span>
                        <span class="fw-bold text-success">{{ number_format($stats['success_rate'], 1) }}%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Avg Confidence</span>
                        <span class="fw-bold">{{ number_format($stats['avg_confidence'], 1) }}%</span>
                    </div>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $stats['success_rate'] }}%"></div>
                </div>
                <small class="text-muted">Active Signals: {{ $stats['pending_signals'] }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form id="filterForm" method="GET" action="{{ route('admin.signals.index') }}">
            <div class="row g-3">
                <div class="col-lg-3">
                    <select class="form-select" name="direction" id="filterDirection" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Directions</option>
                        <option value="long" {{ request('direction') == 'long' ? 'selected' : '' }}>Long</option>
                        <option value="short" {{ request('direction') == 'short' ? 'selected' : '' }}>Short</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <select class="form-select" name="confidence" id="filterConfidence" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Confidence</option>
                        <option value="high" {{ request('confidence') == 'high' ? 'selected' : '' }}>High (&gt;80%)</option>
                        <option value="medium" {{ request('confidence') == 'medium' ? 'selected' : '' }}>Medium (60-80%)</option>
                        <option value="low" {{ request('confidence') == 'low' ? 'selected' : '' }}>Low (&lt;60%)</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <select class="form-select" name="status" id="filterStatus" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="executed" {{ request('status') == 'executed' ? 'selected' : '' }}>Executed</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <select class="form-select" name="timeframe" id="filterTimeframe" onchange="document.getElementById('filterForm').submit()">
                        <option value="today" {{ request('timeframe', 'today') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="24h" {{ request('timeframe') == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                        <option value="7d" {{ request('timeframe') == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30d" {{ request('timeframe') == '30d' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Latest Signals -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">Latest Signals</h5>
                <p class="text-muted small mb-0">Real-time SMC analysis results and trade signals</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-outline-secondary" onclick="refreshSignals()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <a href="{{ route('admin.signals.index', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Export
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="signalsTable">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Time</th>
                        <th class="border-0 py-3 fw-semibold">Pair</th>
                        <th class="border-0 py-3 fw-semibold">Direction</th>
                        <th class="border-0 py-3 fw-semibold">Pattern</th>
                        <th class="border-0 py-3 fw-semibold">Entry</th>
                        <th class="border-0 py-3 fw-semibold">TP / SL</th>
                        <th class="border-0 py-3 fw-semibold">Confidence</th>
                        <th class="border-0 py-3 fw-semibold">R:R</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 px-4 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSignals as $signal)
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">{{ $signal->created_at->format('H:i:s') }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $signal->created_at->format('M d') }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    @php
                                        $symbolColor = match(true) {
                                            str_contains($signal->symbol, 'BTC') => 'text-warning',
                                            str_contains($signal->symbol, 'ETH') => 'text-info',
                                            str_contains($signal->symbol, 'SOL') => 'text-purple',
                                            default => 'text-secondary'
                                        };
                                    @endphp
                                    <i class="bi bi-coin {{ $symbolColor }}"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $signal->symbol }}</div>
                                    <div class="text-muted small">{{ $signal->timeframe }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $signal->type === 'long' ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $signal->type === 'long' ? 'text-success' : 'text-danger' }} border border-{{ $signal->type === 'long' ? 'success' : 'danger' }} border-opacity-25">
                                {{ strtoupper($signal->type) }}
                            </span>
                        </td>
                        <td>
                            <div class="small">{{ $signal->pattern }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">${{ number_format($signal->entry_price, 2) }}</div>
                        </td>
                        <td>
                            <div class="small">
                                <span class="text-success">${{ number_format($signal->take_profit, 2) }}</span>
                                <span class="text-muted">/</span>
                                <span class="text-danger">${{ number_format($signal->stop_loss, 2) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress me-2" style="width: 60px; height: 6px;">
                                    <div class="progress-bar {{ $signal->confidence >= 80 ? 'bg-success' : ($signal->confidence >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                         style="width: {{ $signal->confidence }}%"></div>
                                </div>
                                <span class="small fw-semibold">{{ number_format($signal->confidence, 0) }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="fw-semibold">1:{{ number_format($signal->risk_reward_ratio, 1) }}</span>
                        </td>
                        <td>
                            @php
                                $statusConfig = match($signal->status) {
                                    'active' => ['color' => 'primary', 'icon' => 'circle-fill', 'text' => 'Active'],
                                    'pending' => ['color' => 'warning', 'icon' => 'clock-fill', 'text' => 'Pending'],
                                    'executed' => ['color' => 'success', 'icon' => 'check-circle-fill', 'text' => 'Executed'],
                                    'expired' => ['color' => 'secondary', 'icon' => 'x-circle-fill', 'text' => 'Expired'],
                                    default => ['color' => 'secondary', 'icon' => 'circle', 'text' => $signal->status]
                                };
                            @endphp
                            <span class="badge bg-{{ $statusConfig['color'] }} bg-opacity-10 text-{{ $statusConfig['color'] }} border border-{{ $statusConfig['color'] }} border-opacity-25">
                                <i class="bi bi-{{ $statusConfig['icon'] }}" style="font-size: 6px;"></i> {{ $statusConfig['text'] }}
                            </span>
                            @if($signal->expires_at && $signal->status === 'active')
                                <div class="text-muted small mt-1">
                                    Expires {{ $signal->expires_at->diffForHumans() }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewSignalDetails({{ $signal->id }})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if($signal->status === 'active')
                                    <form action="{{ route('admin.signals.execute', $signal) }}" method="POST" class="d-inline" onsubmit="return confirm('Execute this signal for all users?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Execute for All Users">
                                            <i class="bi bi-play-circle-fill"></i>
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($signal->status, ['pending', 'active']))
                                    <form action="{{ route('admin.signals.cancel', $signal) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this signal?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Cancel Signal">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted">No signals found for the selected filters.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateSignalModal">
                                <i class="bi bi-lightning-charge-fill me-2"></i>Generate Signals
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($recentSignals->hasPages())
    <div class="card-footer bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing {{ $recentSignals->firstItem() }} to {{ $recentSignals->lastItem() }} of {{ $recentSignals->total() }} signals
            </div>
            {{ $recentSignals->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Generate Signal Modal -->
<div class="modal fade" id="generateSignalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-primary bg-opacity-10">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-lightning-charge-fill me-2"></i>Generate Signals Now
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.signals.generate') }}" method="POST" id="generateSignalForm">
                @csrf
                <div class="modal-body">
                    <p class="mb-3">Start the SMC analysis engine to generate new trading signals.</p>
                    <div class="alert alert-info border-0 mb-3">
                        <strong>What happens next:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Market data will be fetched from Bybit</li>
                            <li>SMC patterns will be analyzed in real-time</li>
                            <li>Top signals will be ranked by confidence</li>
                            <li>Signals will be ready for execution in 30-60 seconds</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="startGenerationBtn">
                        <i class="bi bi-play-circle-fill me-2"></i>Start Analysis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Signal Details Modal -->
<div class="modal fade" id="signalDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-info-circle me-2"></i>Signal Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="signalDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0" id="signalDetailsFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Calculate next run time
function updateNextRunTime() {
    const intervalMinutes = {{ $signalInterval }};
    const now = new Date();
    const minutes = now.getMinutes();
    const nextRun = intervalMinutes - (minutes % intervalMinutes);
    
    document.getElementById('nextRunTime').textContent = `in ${nextRun} minute${nextRun > 1 ? 's' : ''}`;
}

// Update immediately and every minute
updateNextRunTime();
setInterval(updateNextRunTime, 60000);

// Handle signal generation form submission
document.getElementById('generateSignalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('startGenerationBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
    
    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('generateSignalModal'));
        modal.hide();
        
        if (data.success) {
            showToast('success', data.message || 'Signals generated successfully!');
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('error', data.message || 'Signal generation failed');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-2"></i>Start Analysis';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred during signal generation');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-circle-fill me-2"></i>Start Analysis';
    });
});

// View signal details
function viewSignalDetails(signalId) {
    const modal = new bootstrap.Modal(document.getElementById('signalDetailsModal'));
    modal.show();
    
    fetch(`/admin/signals/${signalId}`)
        .then(response => response.json())
        .then(data => {
            const signal = data.signal;
            
            const content = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Trade Information</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Pair</span>
                                    <span class="fw-bold">${signal.symbol}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Direction</span>
                                    <span class="badge bg-${signal.type === 'long' ? 'success' : 'danger'}">${signal.type.toUpperCase()}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Entry Price</span>
                                    <span class="fw-bold">$${parseFloat(signal.entry_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Take Profit</span>
                                    <span class="text-success fw-bold">$${parseFloat(signal.take_profit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Stop Loss</span>
                                    <span class="text-danger fw-bold">$${parseFloat(signal.stop_loss).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Analysis Details</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Confidence</span>
                                    <span class="badge bg-success">${Math.round(signal.confidence)}%</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Risk/Reward</span>
                                    <span class="fw-bold">1:${parseFloat(signal.risk_reward_ratio).toFixed(1)}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Timeframe</span>
                                    <span class="fw-bold">${signal.timeframe}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Generated</span>
                                    <span class="fw-bold">${new Date(signal.created_at).toLocaleTimeString()}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Status</span>
                                    <span class="badge bg-primary">${signal.status}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">SMC Pattern</h6>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                        <i class="bi bi-graph-up me-1"></i>${signal.pattern}
                    </span>
                </div>
                ${signal.notes ? `
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Analysis Notes</h6>
                    <p class="small text-muted mb-0">${signal.notes}</p>
                </div>
                ` : ''}
            `;
            
            document.getElementById('signalDetailsContent').innerHTML = content;
            
            // Update footer with execute button if signal is active
            const footer = document.getElementById('signalDetailsFooter');
            if (signal.status === 'active') {
                footer.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form action="/admin/signals/${signal.id}/execute" method="POST" class="d-inline">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-play-circle-fill me-2"></i>Execute for All Users
                        </button>
                    </form>
                `;
            } else {
                footer.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('signalDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    Failed to load signal details. Please try again.
                </div>
            `;
        });
}

// Refresh signals
function refreshSignals() {
    location.reload();
}

// Toast notification helper
function showToast(type, message) {
    // If using Bootstrap Toast
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Or use alert as fallback
    alert(message);
}

// Auto-refresh signals every 60 seconds
setInterval(() => {
    // Silent refresh without page reload
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTable = doc.querySelector('#signalsTable tbody');
        if (newTable) {
            document.querySelector('#signalsTable tbody').innerHTML = newTable.innerHTML;
        }
    })
    .catch(error => console.error('Auto-refresh failed:', error));
}, 60000);
</script>
@endpush