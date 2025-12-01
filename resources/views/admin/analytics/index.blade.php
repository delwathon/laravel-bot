@extends('layouts.app')

@section('title', 'Analytics Dashboard - CryptoBot Pro')

@section('page-title', 'Analytics & Insights')

@section('content')
<!-- Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Volume (30d)</div>
                        <h4 class="fw-bold mb-0">${{ number_format($metrics['total_volume_30d'], 0) }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up text-primary fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-{{ $metrics['volume_change'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $metrics['volume_change'] >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $metrics['volume_change'] >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($metrics['volume_change']), 1) }}%
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
                        <div class="text-muted text-uppercase small fw-bold mb-1">Avg Win Rate</div>
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($metrics['win_rate'], 1) }}%</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-trophy-fill text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ $metrics['win_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Fees Paid</div>
                        <h4 class="fw-bold mb-0 text-warning">${{ number_format($metrics['total_fees'], 0) }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-cash-coin text-warning fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">{{ number_format($metrics['fee_percentage'], 2) }}% of volume</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Sharpe Ratio</div>
                        <h4 class="fw-bold mb-0 text-info">{{ number_format($metrics['sharpe_ratio'], 2) }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-lightning-charge-fill text-info fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Risk-adjusted return</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- P&L Chart -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Cumulative P&L</h5>
                        <p class="text-muted small mb-0">All users combined performance</p>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="pnl-period" id="pnl-1m" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="pnl-1m" onclick="updateChart('1m')">1M</label>
                        
                        <input type="radio" class="btn-check" name="pnl-period" id="pnl-3m" autocomplete="off">
                        <label class="btn btn-outline-primary" for="pnl-3m" onclick="updateChart('3m')">3M</label>
                        
                        <input type="radio" class="btn-check" name="pnl-period" id="pnl-6m" autocomplete="off">
                        <label class="btn btn-outline-primary" for="pnl-6m" onclick="updateChart('6m')">6M</label>
                        
                        <input type="radio" class="btn-check" name="pnl-period" id="pnl-1y" autocomplete="off">
                        <label class="btn btn-outline-primary" for="pnl-1y" onclick="updateChart('1y')">1Y</label>
                        
                        <input type="radio" class="btn-check" name="pnl-period" id="pnl-all" autocomplete="off">
                        <label class="btn btn-outline-primary" for="pnl-all" onclick="updateChart('all')">ALL</label>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="pnlChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row g-3 mb-4">
    <!-- Distribution Chart -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">User Distribution</h5>
            </div>
            <div class="card-body p-4">
                <canvas id="distributionChart" height="200"></canvas>
                <div class="small mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle-fill text-success me-2"></i>Profitable</span>
                        <span class="fw-bold">{{ $distribution['profitable_percent'] }}%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle-fill text-warning me-2"></i>Break Even</span>
                        <span class="fw-bold">{{ $distribution['breakeven_percent'] }}%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="bi bi-circle-fill text-danger me-2"></i>Loss</span>
                        <span class="fw-bold">{{ $distribution['losing_percent'] }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>Key Performance Metrics
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-4">
                        <div class="border-start border-primary border-3 ps-3">
                            <div class="text-muted small mb-1">Total Trades</div>
                            <div class="fw-bold fs-5">{{ number_format($performanceMetrics['total_trades']) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-success border-3 ps-3">
                            <div class="text-muted small mb-1">Winning Trades</div>
                            <div class="fw-bold fs-5 text-success">{{ number_format($performanceMetrics['winning_trades']) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-danger border-3 ps-3">
                            <div class="text-muted small mb-1">Losing Trades</div>
                            <div class="fw-bold fs-5 text-danger">{{ number_format($performanceMetrics['losing_trades']) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-warning border-3 ps-3">
                            <div class="text-muted small mb-1">Avg Trade Duration</div>
                            <div class="fw-bold fs-5">{{ $performanceMetrics['avg_duration'] }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-info border-3 ps-3">
                            <div class="text-muted small mb-1">Best Trade</div>
                            <div class="fw-bold fs-5 text-success">+${{ number_format($performanceMetrics['best_trade'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-secondary border-3 ps-3">
                            <div class="text-muted small mb-1">Worst Trade</div>
                            <div class="fw-bold fs-5 text-danger">-${{ number_format(abs($performanceMetrics['worst_trade']), 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-success border-3 ps-3">
                            <div class="text-muted small mb-1">Profitable Traders</div>
                            <div class="fw-bold fs-5 text-success">{{ $distribution['profitable_percent'] }}%</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-info border-3 ps-3">
                            <div class="text-muted small mb-1">Even Traders</div>
                            <div class="fw-bold fs-5 text-info">{{ $distribution['breakeven_percent'] }}%</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-danger border-3 ps-3">
                            <div class="text-muted small mb-1">Losing Traders</div>
                            <div class="fw-bold fs-5 text-danger">{{ $distribution['losing_percent'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Traders -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-star-fill me-2"></i>Top Performing Traders
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Rank</th>
                        <th class="border-0 py-3 fw-semibold">User</th>
                        <th class="border-0 py-3 fw-semibold">Total Trades</th>
                        <th class="border-0 py-3 fw-semibold">Win Rate</th>
                        <th class="border-0 py-3 fw-semibold">Total P&L</th>
                        <th class="border-0 py-3 fw-semibold">ROI</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topTraders as $index => $trader)
                    <tr>
                        <td class="px-4">
                            @if($index === 0)
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="bi bi-trophy-fill"></i> {{ $index + 1 }}
                                </span>
                            @else
                                <span class="badge bg-secondary text-white fs-6">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $trader->name }}</div>
                            <small class="text-secondary">ID: {{ $trader->id }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">{{ $trader->total_trades }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-success" style="width: {{ $trader->win_rate }}%"></div>
                                </div>
                                <span class="fw-bold text-success">{{ number_format($trader->win_rate, 0) }}%</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold {{ $trader->total_pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $trader->total_pnl >= 0 ? '+' : '' }}${{ number_format($trader->total_pnl, 2) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $trader->roi >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $trader->roi >= 0 ? 'success' : 'danger' }}">
                                {{ $trader->roi >= 0 ? '+' : '' }}{{ number_format($trader->roi, 1) }}%
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.show', $trader->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <p class="text-muted mb-0">No trading data available yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Trading Patterns -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-clock-history me-2"></i>Trading Activity by Hour
                </h5>
            </div>
            <div class="card-body p-4">
                <canvas id="activityChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-coin me-2"></i>Most Traded Pairs
                </h5>
            </div>
            <div class="card-body p-4">
                @foreach($tradedPairs as $pair)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">{{ $pair->symbol }}</span>
                        <span class="badge bg-primary rounded-pill">{{ number_format($pair->trade_count) }} trades</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-{{ $loop->index === 0 ? 'warning' : ($loop->index === 1 ? 'info' : ($loop->index === 2 ? 'purple' : 'success')) }}" 
                             style="width: {{ ($pair->trade_count / $tradedPairs->first()->trade_count) * 100 }}%; {{ $loop->index === 2 ? 'background-color: #8b5cf6;' : '' }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// P&L Chart Data
const pnlData = @json($pnlChartData);

// P&L Chart
const pnlCtx = document.getElementById('pnlChart').getContext('2d');
const pnlChart = new Chart(pnlCtx, {
    type: 'line',
    data: {
        labels: pnlData.labels,
        datasets: [{
            label: 'Cumulative P&L',
            data: pnlData.values,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Distribution Pie Chart
const distributionCtx = document.getElementById('distributionChart').getContext('2d');
new Chart(distributionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Profitable', 'Break Even', 'Loss'],
        datasets: [{
            data: [{{ $distribution['profitable_percent'] }}, {{ $distribution['breakeven_percent'] }}, {{ $distribution['losing_percent'] }}],
            backgroundColor: [
                'rgba(25, 135, 84, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Activity by Hour Chart
const activityData = @json($activityByHour);
const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'bar',
    data: {
        labels: activityData.labels,
        datasets: [{
            label: 'Trades',
            data: activityData.values,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Update chart based on period
function updateChart(period) {
    fetch(`/admin/analytics/chart-data?period=${period}`)
        .then(response => response.json())
        .then(data => {
            pnlChart.data.labels = data.labels;
            pnlChart.data.datasets[0].data = data.values;
            pnlChart.update();
        });
}
</script>
@endpush