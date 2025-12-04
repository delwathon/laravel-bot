@extends('layouts.app')

@section('title', 'Signal Generator - CryptoBot Pro')

@section('page-title', 'Signal Generator')

@section('content')

@php
    $autoExecute = \App\Models\Setting::get('signal_auto_execute', true);
    $useDynamicPairs = \App\Models\Setting::get('signal_use_dynamic_pairs', false);
    $minVolume = \App\Models\Setting::get('signal_min_volume', 5000000);
    $topSignalsCount = \App\Models\Setting::get('signal_top_count', 5);
    $minConfidence = \App\Models\Setting::get('signal_min_confidence', 70);
@endphp

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
                <button class="btn btn-outline-secondary" onclick="filterSignals('all')">
                    <i class="bi bi-list-ul me-1"></i>All
                </button>
                <button class="btn btn-outline-success" onclick="filterSignals('executed')">
                    <i class="bi bi-check-circle me-1"></i>Executed
                </button>
                <button class="btn btn-outline-info" onclick="filterSignals('active')">
                    <i class="bi bi-lightning-charge me-1"></i>Active
                </button>
                <button class="btn btn-outline-danger" onclick="filterSignals('expired')">
                    <i class="bi bi-x-circle me-1"></i>Expired
                </button>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                    Generate Signals
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex">
                        <i class="bi bi-info-circle-fill me-3 fs-5"></i>
                        <div class="small">
                            This will analyze all configured trading pairs and generate signals based on Smart Money Concepts patterns.
                            @if($autoExecute)
                                <strong>Auto-execution is enabled</strong> - signals will be executed immediately.
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Configuration</label>
                    <div class="card bg-body-secondary border-0">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="small text-muted">Pair Selection</div>
                                    <div class="fw-bold">{{ $useDynamicPairs ? 'Dynamic' : 'Fixed' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Min Volume</div>
                                    <div class="fw-bold">${{ number_format($minVolume / 1000000, 1) }}M</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Top Signals</div>
                                    <div class="fw-bold">{{ $topSignalsCount }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Min Confidence</div>
                                    <div class="fw-bold">{{ $minConfidence }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="generateProgress" class="d-none">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Generating...</span>
                        </div>
                        <p class="text-muted mb-0">Analyzing markets and generating signals...</p>
                        <p class="small text-muted mt-2">This may take a few moments</p>
                    </div>
                </div>

                <div id="generateResult" class="d-none"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmGenerateBtn" onclick="generateSignals()">
                    <i class="bi bi-play-circle-fill me-2"></i>Generate Signals
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Signal Details Modal -->
<div class="modal fade" id="signalDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Signal Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="signalDetailsContent">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Execute Confirmation Modal -->
<div class="modal fade" id="executeConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-play-circle-fill text-success me-2"></i>
                    Execute Signal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Warning:</strong> This will execute the signal for admin account and propagate to all active users.
                </div>
                <p class="mb-0">Are you sure you want to execute this signal?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmExecuteBtn">
                    <i class="bi bi-play-fill me-2"></i>Execute
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alert/Notification Modal -->
<div class="modal fade" id="alertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="alertModalTitle">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="alertModalBody">
                <!-- Alert content -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
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

// Filter signals
function filterSignals(status) {
    window.location.href = `{{ route('admin.signals.index') }}?status=${status}`;
}

// Generate signals
function generateSignals() {
    const btn = document.getElementById('confirmGenerateBtn');
    const progress = document.getElementById('generateProgress');
    const result = document.getElementById('generateResult');
    
    btn.disabled = true;
    progress.classList.remove('d-none');
    result.classList.add('d-none');
    
    fetch('{{ route('admin.signals.generate') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        progress.classList.add('d-none');
        result.classList.remove('d-none');
        
        if (data.success) {
            result.innerHTML = `
                <div class="alert alert-success border-0 mb-0">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Success!</strong> Generated ${data.count} signals.
                    ${data.executed ? `${data.executed} signals executed.` : ''}
                </div>
            `;
            
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            result.innerHTML = `
                <div class="alert alert-danger border-0 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Error:</strong> ${data.message || 'Failed to generate signals'}
                </div>
            `;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        progress.classList.add('d-none');
        result.classList.remove('d-none');
        result.innerHTML = `
            <div class="alert alert-danger border-0 mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Error:</strong> Failed to generate signals. Please try again.
            </div>
        `;
        btn.disabled = false;
    });
}

// View signal details
function viewSignalDetails(signalId) {
    const modal = new bootstrap.Modal(document.getElementById('signalDetailsModal'));
    
    document.getElementById('signalDetailsContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-3">Loading signal details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`/admin/signals/${signalId}/details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('signalDetailsContent').innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Signal Information</h6>
                                <div class="mb-2">
                                    <span class="text-muted small">Symbol</span>
                                    <div class="fw-bold">${data.symbol}</div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Pattern</span>
                                    <div><span class="badge bg-info">${data.pattern}</span></div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Type</span>
                                    <div><span class="badge bg-${data.type === 'long' ? 'success' : 'danger'}">${data.type.toUpperCase()}</span></div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Order Type</span>
                                    <div><span class="badge bg-${data.order_type === 'Market' ? 'primary' : 'warning'}">${data.order_type}</span></div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Confidence</span>
                                    <div class="fw-bold">${data.confidence}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Price Levels</h6>
                                <div class="mb-2">
                                    <span class="text-muted small">Entry Price</span>
                                    <div class="fw-bold">$${parseFloat(data.entry_price).toLocaleString()}</div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Take Profit</span>
                                    <div class="text-success fw-bold">$${parseFloat(data.take_profit).toLocaleString()}</div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Stop Loss</span>
                                    <div class="text-danger fw-bold">$${parseFloat(data.stop_loss).toLocaleString()}</div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Risk:Reward</span>
                                    <div class="fw-bold">${data.risk_reward_ratio}</div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Status</span>
                                    <div><span class="badge bg-info">${data.status.toUpperCase()}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error', 'Failed to load signal details');
        });
}

// Execute signal
let executeSignalId = null;

function executeSignal(signalId) {
    executeSignalId = signalId;
    const modal = new bootstrap.Modal(document.getElementById('executeConfirmModal'));
    modal.show();
    
    document.getElementById('confirmExecuteBtn').onclick = function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Executing...';
        
        fetch(`/admin/signals/${executeSignalId}/execute`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('executeConfirmModal')).hide();
            
            if (data.success) {
                showAlert('Success', `Signal executed successfully! Propagated to ${data.propagated || 0} users.`, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('Error', data.message || 'Failed to execute signal', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            bootstrap.Modal.getInstance(document.getElementById('executeConfirmModal')).hide();
            showAlert('Error', 'Failed to execute signal', 'danger');
        });
    };
}

// Show alert modal (replacement for alert())
function showAlert(title, message, type = 'info') {
    const modal = new bootstrap.Modal(document.getElementById('alertModal'));
    document.getElementById('alertModalTitle').textContent = title;
    
    const iconClass = {
        'success': 'bi-check-circle-fill text-success',
        'danger': 'bi-exclamation-triangle-fill text-danger',
        'warning': 'bi-exclamation-triangle-fill text-warning',
        'info': 'bi-info-circle-fill text-primary'
    }[type] || 'bi-info-circle-fill text-primary';
    
    document.getElementById('alertModalBody').innerHTML = `
        <div class="d-flex align-items-start">
            <i class="bi ${iconClass} fs-4 me-3"></i>
            <div>${message}</div>
        </div>
    `;
    
    modal.show();
}
</script>
@endpush