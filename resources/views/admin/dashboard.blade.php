@extends('layouts.app')

@section('title', 'Dashboard - CryptoBot Pro')

@section('page-title', 'Command Center')

@section('content')
<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Total Users -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-light" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-white text-opacity-75 text-uppercase small fw-bold mb-1">Total Users</div>
                        <h2 class="fw-bold mb-0">{{ number_format($totalUsers) }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 p-3 rounded-3">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white bg-opacity-25 text-white">
                        <i class="bi bi-arrow-{{ $userGrowthPercent >= 0 ? 'up' : 'down' }}"></i> 
                        {{ abs($userGrowthPercent) }}%
                    </span>
                    <small class="text-white text-opacity-75">vs last month</small>
                </div>
                <div class="progress mt-3 bg-white bg-opacity-10" style="height: 4px;">
                    <div class="progress-bar bg-white" style="width: {{ min($connectionRate, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Trades -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Trades</div>
                        <h2 class="fw-bold mb-0 text-info">{{ number_format($activeTrades) }}</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up-arrow text-info fs-3"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $tradesChange >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $tradesChange >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $tradesChange >= 0 ? 'up' : 'down' }}"></i> 
                        {{ abs($tradesChange) }}%
                    </span>
                    <small class="text-muted">vs yesterday</small>
                </div>
                <div class="mt-2">
                    <small class="text-muted">{{ $activePositions }} active positions</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Profit -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Profit</div>
                        <h2 class="fw-bold mb-0 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($totalProfit, 2) }}
                        </h2>
                    </div>
                    <div class="bg-{{ $totalProfit >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-currency-dollar {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }} fs-3"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $profitChange >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $profitChange >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $profitChange >= 0 ? 'up' : 'down' }}"></i> 
                        {{ abs($profitChange) }}%
                    </span>
                    <small class="text-muted">last 7 days</small>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Win Rate: {{ $winRate }}%</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Signals -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Today's Signals</div>
                        <h2 class="fw-bold mb-0 text-warning">{{ $todaySignals }}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-lightning-charge-fill text-warning fs-3"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $signalsChange >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $signalsChange >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $signalsChange >= 0 ? 'up' : 'down' }}"></i> 
                        {{ abs($signalsChange) }}%
                    </span>
                    <small class="text-muted">vs yesterday</small>
                </div>
                <div class="mt-2">
                    <small class="text-muted">{{ $systemHealth['active_signals'] }} active</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Health & Admin Balance -->
<div class="row g-3 mb-4">
    <!-- Admin Balance -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-wallet2 me-2"></i>Admin Account Balance
                </h6>
                <h3 class="fw-bold mb-2">${{ number_format($adminBalance, 2) }}</h3>
                <div class="d-flex align-items-center gap-2 mb-3">
                    @if($adminBalanceChange != 0)
                    <span class="badge bg-{{ $adminBalanceChange >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $adminBalanceChange >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $adminBalanceChange >= 0 ? 'up' : 'down' }}"></i> 
                        {{ abs($adminBalanceChange) }}%
                    </span>
                    @endif
                    <small class="text-muted">Since last sync</small>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="small text-muted">Today's Volume</div>
                        <div class="fw-semibold">${{ number_format($todayVolume, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted">Avg Profit/Trade</div>
                        <div class="fw-semibold">${{ number_format($avgProfitPerTrade, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-shield-check me-2"></i>System Health
                </h6>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted">Bybit API</span>
                    <span class="badge bg-{{ $systemHealth['bybit_api_status'] === 'operational' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $systemHealth['bybit_api_status'] === 'operational' ? 'success' : 'danger' }}">
                        {{ ucfirst($systemHealth['bybit_api_status']) }}
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted">Pending Orders</span>
                    <span class="badge bg-info bg-opacity-10 text-info">
                        {{ $systemHealth['pending_orders'] }}
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted">Failed Trades Today</span>
                    <span class="badge bg-{{ $systemHealth['failed_trades_today'] > 0 ? 'warning' : 'success' }} bg-opacity-10 text-{{ $systemHealth['failed_trades_today'] > 0 ? 'warning' : 'success' }}">
                        {{ $systemHealth['failed_trades_today'] }}
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted">Connected Users</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        {{ $connectedUsers }}/{{ $totalUsers }} ({{ $connectionRate }}%)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Metrics -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>Risk Metrics
                </h6>
                <div class="mb-3">
                    <div class="small text-muted mb-1">Total Exposure</div>
                    <h5 class="fw-bold mb-0">${{ number_format($riskMetrics['total_exposure'], 2) }}</h5>
                </div>
                <div class="mb-3">
                    <div class="small text-muted mb-1">Margin Used</div>
                    <div class="fw-semibold">${{ number_format($riskMetrics['total_margin_used'], 2) }}</div>
                </div>
                <div>
                    <div class="small text-muted mb-1">At-Risk Positions</div>
                    <div class="fw-semibold {{ $riskMetrics['at_risk_positions'] > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $riskMetrics['at_risk_positions'] }} positions
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- Daily Performance Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-graph-up me-2"></i>Daily Performance (Last 7 Days)
                </h6>
            </div>
            <div class="card-body">
                <canvas id="dailyPerformanceChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Trading Pairs Performance -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-bar-chart me-2"></i>Top Trading Pairs
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            @foreach($tradingPairs as $pair)
                            <tr>
                                <td class="fw-semibold">{{ $pair->symbol }}</td>
                                <td class="text-end">
                                    <small class="text-muted">{{ $pair->trade_count }} trades</small>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-{{ $pair->total_pnl >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $pair->total_pnl >= 0 ? 'success' : 'danger' }}">
                                        ${{ number_format($pair->total_pnl, 2) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers & Recent Activity -->
<div class="row g-3 mb-4">
    <!-- Top Performing Users -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-trophy me-2"></i>Top Performers
                    </h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold">User</th>
                                <th class="border-0 py-3 fw-semibold">Trades</th>
                                <th class="border-0 py-3 fw-semibold text-end px-4">P&L</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPerformers as $index => $performer)
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-warning bg-opacity-10 text-warning me-2" style="width: 24px; height: 24px; line-height: 24px;">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $performer->name }}</div>
                                            <small class="text-muted">{{ $performer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        {{ $performer->trades_count }}
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <div class="fw-bold {{ $performer->total_pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($performer->total_pnl, 2) }}
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Trades -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Trades
                    </h6>
                    <a href="{{ route('admin.trades.index') }}" class="btn btn-sm btn-light">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold">User</th>
                                <th class="border-0 py-3 fw-semibold">Symbol</th>
                                <th class="border-0 py-3 fw-semibold">Type</th>
                                <th class="border-0 py-3 fw-semibold text-end px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTrades as $trade)
                            <tr>
                                <td class="px-4">
                                    <div>
                                        <div class="fw-semibold small">{{ $trade->user->name }}</div>
                                        <small class="text-muted">{{ $trade->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-dark bg-opacity-10 text-dark">
                                        {{ $trade->symbol }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $trade->type === 'long' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $trade->type === 'long' ? 'success' : 'danger' }}">
                                        {{ strtoupper($trade->type) }}
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    @if($trade->status === 'open')
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Open
                                        </span>
                                    @elseif($trade->status === 'closed')
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-check-circle-fill"></i> Closed
                                        </span>
                                    @elseif($trade->status === 'pending')
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-clock-fill"></i> Pending
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-x-circle-fill"></i> Failed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No recent trades</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-people me-2"></i>Recent Users
                    </h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light">Manage Users</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold">User</th>
                                <th class="border-0 py-3 fw-semibold">Exchange</th>
                                <th class="border-0 py-3 fw-semibold">Trades</th>
                                <th class="border-0 py-3 fw-semibold">Active Positions</th>
                                <th class="border-0 py-3 fw-semibold text-end">P&L</th>
                                <th class="border-0 py-3 fw-semibold text-center px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                            <tr>
                                <td class="px-4">
                                    <div>
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="fw-semibold text-decoration-none">
                                            {{ $user->name }}
                                        </a>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                        <small class="text-muted">Joined {{ $user->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($user->exchangeAccount)
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-check-circle-fill"></i> Connected
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            <i class="bi bi-x-circle"></i> Not Connected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $user->trades_count }}
                                    </span>
                                    @if($user->today_trades_count > 0)
                                        <small class="text-success">+{{ $user->today_trades_count }} today</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ $user->active_positions_count }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="{{ $user->total_pnl >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                        ${{ number_format($user->total_pnl, 2) }}
                                    </div>
                                    @if($user->win_rate > 0)
                                        <small class="text-muted">{{ $user->win_rate }}% win rate</small>
                                    @endif
                                </td>
                                <td class="text-center px-4">
                                    @if($user->exchangeAccount && $user->exchangeAccount->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No users found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-light text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" role="button" onclick="window.location.href='{{ route('admin.signals.index') }}'">
            <div class="card-body p-4">
                <i class="bi bi-lightning-charge-fill fs-1 mb-3 d-block"></i>
                <h5 class="fw-bold mb-2">Generate Signals</h5>
                <p class="mb-0 text-white text-opacity-75 small">Run SMC analysis now</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('admin.users.index') }}'">
            <div class="card-body p-4">
                <i class="bi bi-people-fill fs-1 mb-3 d-block text-primary"></i>
                <h5 class="fw-bold mb-2">Manage Users</h5>
                <p class="mb-0 text-muted small">View all {{ $totalUsers }} users</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('admin.trades.index') }}'">
            <div class="card-body p-4">
                <i class="bi bi-graph-up-arrow fs-1 mb-3 d-block text-success"></i>
                <h5 class="fw-bold mb-2">Active Trades</h5>
                <p class="mb-0 text-muted small">Monitor {{ $activeTrades }} positions</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('admin.settings.system') }}'">
            <div class="card-body p-4">
                <i class="bi bi-gear-fill fs-1 mb-3 d-block text-warning"></i>
                <h5 class="fw-bold mb-2">Bot Settings</h5>
                <p class="mb-0 text-muted small">Configure parameters</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Daily Performance Chart
const dailyCtx = document.getElementById('dailyPerformanceChart');
if (dailyCtx) {
    const dailyData = @json($dailyStats);
    
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [
                {
                    label: 'Profit/Loss',
                    data: dailyData.map(d => d.profit),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Trades',
                    data: dailyData.map(d => d.trades),
                    borderColor: 'rgb(153, 102, 255)',
                    backgroundColor: 'rgba(153, 102, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.dataset.label === 'Profit/Loss') {
                                    label += '$' + context.parsed.y.toFixed(2);
                                } else {
                                    label += context.parsed.y;
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Profit/Loss ($)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Number of Trades'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

// Auto-refresh dashboard every 30 seconds
setInterval(function() {
    // You can add AJAX call here to update real-time stats
    console.log('Dashboard refresh...');
}, 30000);
</script>
@endpush

@endsection