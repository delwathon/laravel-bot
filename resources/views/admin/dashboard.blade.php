@extends('layouts.app')

@section('title', 'Dashboard - CryptoBot Pro')

@section('page-title', 'Command Center')

@section('content')
<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-white text-opacity-75 text-uppercase small fw-bold mb-1">Total Users</div>
                        <h2 class="fw-bold mb-0">{{ $totalUsers ?? 248 }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 p-3 rounded-3">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white bg-opacity-25 text-white">
                        <i class="bi bi-arrow-up"></i> 12%
                    </span>
                    <small class="text-white text-opacity-75">vs last month</small>
                </div>
                <div class="progress mt-3 bg-white bg-opacity-10" style="height: 4px;">
                    <div class="progress-bar bg-white" style="width: 75%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Trades</div>
                        <h2 class="fw-bold mb-0 text-info">{{ $activeTrades ?? 1342 }}</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up-arrow text-info fs-3"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info bg-opacity-10 text-info">
                        <i class="bi bi-activity"></i> Live
                    </span>
                    <small class="text-muted">Across all users</small>
                </div>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: 89%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total P&L (24h)</div>
                        <h2 class="fw-bold mb-0 text-success">${{ number_format($totalPnl ?? 45231, 0) }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-currency-dollar text-success fs-3"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> 8.5%
                    </span>
                    <small class="text-muted">Today's profit</small>
                </div>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 92%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Win Rate</div>
                        <h2 class="fw-bold mb-0 text-warning">{{ $winRate ?? 68.4 }}%</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-trophy-fill text-warning fs-3"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> 2.1%
                    </span>
                    <small class="text-muted">This week</small>
                </div>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: 68%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Real-Time Data -->
<div class="row g-3 mb-4">
    <!-- Main Performance Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Performance Analytics</h5>
                        <p class="text-muted small mb-0">Real-time P&L and trade execution data</p>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active">1H</button>
                        <button class="btn btn-outline-secondary">4H</button>
                        <button class="btn btn-outline-secondary">1D</button>
                        <button class="btn btn-outline-secondary">1W</button>
                        <button class="btn btn-outline-secondary">1M</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Chart Placeholder -->
                <div class="border rounded-3 p-5 text-center bg-body-secondary">
                    <i class="bi bi-bar-chart-line-fill text-muted mb-3" style="font-size: 4rem;"></i>
                    <p class="text-muted mb-2">Interactive Performance Chart</p>
                    <small class="text-muted">Chart.js / ApexCharts integration</small>
                    <div class="row g-3 mt-4">
                        <div class="col-4">
                            <div class="text-success fw-bold fs-5">+$12.4K</div>
                            <small class="text-muted">Realized P&L</small>
                        </div>
                        <div class="col-4">
                            <div class="text-info fw-bold fs-5">+$8.2K</div>
                            <small class="text-muted">Unrealized P&L</small>
                        </div>
                        <div class="col-4">
                            <div class="text-warning fw-bold fs-5">156</div>
                            <small class="text-muted">Total Trades</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Feed -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Live Activity</h5>
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Live
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentActivities ?? [] as $activity)
                        <div class="list-group-item border-0 p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-{{ $activity->type_color ?? 'success' }} bg-opacity-10 p-2 rounded-circle flex-shrink-0">
                                    <i class="bi bi-{{ $activity->icon ?? 'check-circle-fill' }} text-{{ $activity->type_color ?? 'success' }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <p class="mb-0 fw-semibold small">{{ $activity->title }}</p>
                                        <small class="text-muted">{{ $activity->time_ago }}</small>
                                    </div>
                                    <p class="mb-1 small text-muted">{{ $activity->description }}</p>
                                    @if($activity->amount)
                                        <span class="badge bg-{{ $activity->amount > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $activity->amount > 0 ? 'success' : 'danger' }} small">
                                            {{ $activity->amount > 0 ? '+' : '' }}${{ number_format(abs($activity->amount), 0) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item border-0 p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle flex-shrink-0">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <p class="mb-0 fw-semibold small">Trade Executed</p>
                                        <small class="text-muted">2m</small>
                                    </div>
                                    <p class="mb-1 small text-muted">BTC/USDT LONG • Bybit</p>
                                    <span class="badge bg-success bg-opacity-10 text-success small">+$1,234</span>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item border-0 p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle flex-shrink-0">
                                    <i class="bi bi-person-plus-fill text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <p class="mb-0 fw-semibold small">New User</p>
                                        <small class="text-muted">5m</small>
                                    </div>
                                    <p class="mb-0 small text-muted">john.doe@example.com</p>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item border-0 p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-warning bg-opacity-10 p-2 rounded-circle flex-shrink-0">
                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <p class="mb-0 fw-semibold small">SL Triggered</p>
                                        <small class="text-muted">12m</small>
                                    </div>
                                    <p class="mb-1 small text-muted">ETH/USDT • User #1043</p>
                                    <span class="badge bg-danger bg-opacity-10 text-danger small">-$432</span>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item border-0 p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-info bg-opacity-10 p-2 rounded-circle flex-shrink-0">
                                    <i class="bi bi-lightning-charge-fill text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <p class="mb-0 fw-semibold small">Signal Generated</p>
                                        <small class="text-muted">15m</small>
                                    </div>
                                    <p class="mb-1 small text-muted">SOL/USDT LONG • 85% confidence</p>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item border-0 p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle flex-shrink-0">
                                    <i class="bi bi-bullseye text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <p class="mb-0 fw-semibold small">TP Reached</p>
                                        <small class="text-muted">18m</small>
                                    </div>
                                    <p class="mb-1 small text-muted">XRP/USDT • 248 users</p>
                                    <span class="badge bg-success bg-opacity-10 text-success small">+$8,923</span>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trading Pairs Performance -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Top Performing Pairs</h5>
                        <p class="text-muted small mb-0">Last 24 hours performance metrics</p>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold">Pair</th>
                                <th class="border-0 py-3 fw-semibold">Exchange</th>
                                <th class="border-0 py-3 fw-semibold">Price</th>
                                <th class="border-0 py-3 fw-semibold">24h Change</th>
                                <th class="border-0 py-3 fw-semibold">Trades</th>
                                <th class="border-0 py-3 fw-semibold">Win Rate</th>
                                <th class="border-0 py-3 fw-semibold text-end">P&L</th>
                                <th class="border-0 py-3 fw-semibold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPairs ?? [] as $pair)
                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-{{ $pair->color ?? 'warning' }} bg-opacity-10 p-2 rounded-circle me-2">
                                                <i class="bi bi-{{ $pair->icon ?? 'currency-bitcoin' }} text-{{ $pair->color ?? 'warning' }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $pair->symbol }}</div>
                                                <small class="text-muted">{{ $pair->name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $pair->exchange_color ?? 'primary' }} bg-opacity-10 text-{{ $pair->exchange_color ?? 'primary' }} border border-{{ $pair->exchange_color ?? 'primary' }} border-opacity-25">
                                            <i class="bi bi-coin me-1"></i>{{ $pair->exchange }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">${{ number_format($pair->price, 2) }}</div>
                                        <small class="text-muted">Volume: ${{ $pair->volume }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $pair->change >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $pair->change >= 0 ? 'success' : 'danger' }}">
                                            <i class="bi bi-arrow-{{ $pair->change >= 0 ? 'up' : 'down' }}"></i> {{ $pair->change > 0 ? '+' : '' }}{{ $pair->change }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">{{ $pair->trades_count }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-success" style="width: {{ $pair->win_rate }}%"></div>
                                            </div>
                                            <small class="fw-bold text-success">{{ $pair->win_rate }}%</small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="text-{{ $pair->pnl >= 0 ? 'success' : 'danger' }} fw-bold">{{ $pair->pnl > 0 ? '+' : '' }}${{ number_format(abs($pair->pnl), 0) }}</div>
                                        <small class="text-muted">{{ $pair->pnl_percent > 0 ? '+' : '' }}{{ $pair->pnl_percent }}%</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <!-- Default rows -->
                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-2">
                                                <i class="bi bi-currency-bitcoin text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">BTC/USDT</div>
                                                <small class="text-muted">Bitcoin</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                            <i class="bi bi-coin me-1"></i>Bybit
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">$66,450</div>
                                        <small class="text-muted">Volume: $2.4B</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-arrow-up"></i> +3.45%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">145</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-success" style="width: 72%"></div>
                                            </div>
                                            <small class="fw-bold text-success">72%</small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="text-success fw-bold">+$12,456</div>
                                        <small class="text-muted">+18.3%</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 p-2 rounded-circle me-2">
                                                <i class="bi bi-currency-exchange text-info"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">ETH/USDT</div>
                                                <small class="text-muted">Ethereum</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                            <i class="bi bi-currency-bitcoin me-1"></i>Binance
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">$3,245</div>
                                        <small class="text-muted">Volume: $1.8B</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-arrow-up"></i> +2.18%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">98</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-success" style="width: 68%"></div>
                                            </div>
                                            <small class="fw-bold text-success">68%</small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="text-success fw-bold">+$8,923</div>
                                        <small class="text-muted">+14.2%</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-purple bg-opacity-10 p-2 rounded-circle me-2" style="background-color: rgba(139, 92, 246, 0.1) !important;">
                                                <i class="bi bi-coin" style="color: #8b5cf6;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">SOL/USDT</div>
                                                <small class="text-muted">Solana</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                            <i class="bi bi-coin me-1"></i>Bybit
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">$145.80</div>
                                        <small class="text-muted">Volume: $892M</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-arrow-up"></i> +5.23%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">76</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-success" style="width: 65%"></div>
                                            </div>
                                            <small class="fw-bold text-success">65%</small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="text-success fw-bold">+$6,734</div>
                                        <small class="text-muted">+12.1%</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                                        </span>
                                    </td>
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
    @if(auth()->user()->is_admin)
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" role="button" onclick="window.location.href='{{ route('admin.signals.index') }}'">
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
                    <p class="mb-0 text-muted small">View all {{ $totalUsers ?? 248 }} users</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('admin.trades.index') }}'">
                <div class="card-body p-4">
                    <i class="bi bi-graph-up-arrow fs-1 mb-3 d-block text-success"></i>
                    <h5 class="fw-bold mb-2">Active Trades</h5>
                    <p class="mb-0 text-muted small">Monitor {{ $activeTrades ?? 1342 }} positions</p>
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
    @else
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" role="button" onclick="window.location.href='{{ route('user.exchanges.manage') }}'">
                <div class="card-body p-4">
                    <i class="bi bi-bank fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold mb-2">My Exchanges</h5>
                    <p class="mb-0 text-white text-opacity-75 small">Manage API connections</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('user.trades.index') }}'">
                <div class="card-body p-4">
                    <i class="bi bi-arrow-left-right fs-1 mb-3 d-block text-primary"></i>
                    <h5 class="fw-bold mb-2">My Trades</h5>
                    <p class="mb-0 text-muted small">View trading history</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('user.positions.index') }}'">
                <div class="card-body p-4">
                    <i class="bi bi-graph-up fs-1 mb-3 d-block text-success"></i>
                    <h5 class="fw-bold mb-2">Active Positions</h5>
                    <p class="mb-0 text-muted small">Monitor open trades</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('user.account.settings') }}'">
                <div class="card-body p-4">
                    <i class="bi bi-gear-fill fs-1 mb-3 d-block text-warning"></i>
                    <h5 class="fw-bold mb-2">Settings</h5>
                    <p class="mb-0 text-muted small">Account preferences</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection