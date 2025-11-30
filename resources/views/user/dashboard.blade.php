@extends('layouts.app')

@section('title', 'My Dashboard - CryptoBot Pro')

@section('page-title', 'My Dashboard')

@section('content')
<!-- Welcome Banner -->
<div class="card border-0 shadow-sm mb-4 bg-light text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="fw-bold mb-2">Welcome back, {{ auth()->user()->name }}! 👋</h4>
                <p class="mb-3 opacity-90">Your automated trading system is active and monitoring the markets</p>
                <div class="d-flex gap-3">
                    <div>
                        <div class="text-white text-opacity-75 small">Account Status</div>
                        <div class="fw-bold">
                            <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Active
                        </div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="text-white text-opacity-75 small">Connected Exchanges</div>
                        <div class="fw-bold">2 Exchanges</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="text-white text-opacity-75 small">Active Since</div>
                        <div class="fw-bold">Jan 15, 2024</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex p-4 mb-2">
                    <i class="bi bi-graph-up-arrow fs-1"></i>
                </div>
                <div class="fw-bold">Auto-Trading Active</div>
            </div>
        </div>
    </div>
</div>

<!-- Performance KPIs -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total P&L</div>
                        <h3 class="fw-bold mb-0 text-success">$4,523</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-currency-dollar text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> +12.3%
                    </span>
                    <small class="text-muted ms-2">All time</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Win Rate</div>
                        <h3 class="fw-bold mb-0 text-warning">68.4%</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-trophy-fill text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 68.4%"></div>
                    </div>
                    <small class="text-muted">23 trades today</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Positions</div>
                        <h3 class="fw-bold mb-0 text-info">8</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-info rounded-pill">6 Long</span>
                    <span class="badge bg-danger rounded-pill">2 Short</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Today's P&L</div>
                        <h3 class="fw-bold mb-0 text-success">+$342</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> +3.2%
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Activity -->
<div class="row g-3 mb-4">
    <!-- Performance Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Performance Overview</h5>
                        <p class="text-muted small mb-0">Your P&L over time</p>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active">24H</button>
                        <button class="btn btn-outline-secondary">7D</button>
                        <button class="btn btn-outline-secondary">30D</button>
                        <button class="btn btn-outline-secondary">ALL</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="border rounded-3 p-5 text-center bg-body-secondary">
                    <i class="bi bi-bar-chart-line-fill text-muted mb-3" style="font-size: 4rem;"></i>
                    <p class="text-muted mb-2">Performance Chart</p>
                    <small class="text-muted">Chart.js / ApexCharts integration</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recent Activity</h5>
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Live
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
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
                                <p class="mb-1 small text-muted">BTCUSDT LONG • Bybit</p>
                                <span class="badge bg-success bg-opacity-10 text-success small">+$234</span>
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
                                    <p class="mb-0 fw-semibold small">New Signal</p>
                                    <small class="text-muted">5m</small>
                                </div>
                                <p class="mb-1 small text-muted">ETHUSDT • 85% confidence</p>
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
                                    <p class="mb-0 fw-semibold small">Take Profit Hit</p>
                                    <small class="text-muted">12m</small>
                                </div>
                                <p class="mb-1 small text-muted">SOLUSDT</p>
                                <span class="badge bg-success bg-opacity-10 text-success small">+$156</span>
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
                                    <p class="mb-0 fw-semibold small">Stop Loss Triggered</p>
                                    <small class="text-muted">28m</small>
                                </div>
                                <p class="mb-1 small text-muted">XRPUSDT</p>
                                <span class="badge bg-danger bg-opacity-10 text-danger small">-$45</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Positions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">My Active Positions</h5>
                <p class="text-muted small mb-0">Currently open trades being monitored</p>
            </div>
            <a href="{{ route('user.positions.index') }}" class="btn btn-outline-primary">
                View All
            </a>
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
                        <th class="border-0 py-3 fw-semibold">P&L</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-currency-bitcoin text-warning"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">BTCUSDT</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$66,450</div>
                            <small class="text-muted">5m ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$66,823</div>
                            <small class="text-success">+0.56%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">TP: $67,200</div>
                                <div class="text-danger">SL: $65,900</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$279</div>
                            <small class="text-success">+2.79%</small>
                        </td>
                        <td>
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
                                    <div class="fw-bold">ETHUSDT</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$3,245</div>
                            <small class="text-muted">12m ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$3,298</div>
                            <small class="text-success">+1.63%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">TP: $3,310</div>
                                <div class="text-danger">SL: $3,180</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$159</div>
                            <small class="text-success">+4.90%</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                    </tr>

                    <tr class="table-warning bg-opacity-10">
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-purple bg-opacity-10 p-2 rounded-circle me-2" style="background-color: rgba(139, 92, 246, 0.1) !important;">
                                    <i class="bi bi-coin" style="color: #8b5cf6;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">SOLUSDT</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-arrow-down-right me-1"></i>SHORT
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$145.80</div>
                            <small class="text-muted">28m ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-danger">$147.15</div>
                            <small class="text-danger">+0.93%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">TP: $142.50</div>
                                <div class="text-danger">SL: $147.50</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-danger fw-bold">-$67</div>
                            <small class="text-danger">-1.85%</small>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-exclamation-triangle"></i> At Risk
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('user.exchanges.manage') }}'">
            <div class="card-body p-4 text-center">
                <div class="bg-primary bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                    <i class="bi bi-bank text-primary fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Manage Exchanges</h5>
                <p class="text-muted small mb-0">View and configure API connections</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('user.trades.index') }}'">
            <div class="card-body p-4 text-center">
                <div class="bg-success bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                    <i class="bi bi-clock-history text-success fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Trade History</h5>
                <p class="text-muted small mb-0">Review past trades and performance</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('user.positions.index') }}'">
            <div class="card-body p-4 text-center">
                <div class="bg-info bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                    <i class="bi bi-graph-up text-info fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Active Positions</h5>
                <p class="text-muted small mb-0">Monitor open trades in real-time</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" role="button" onclick="window.location.href='{{ route('user.account.settings') }}'">
            <div class="card-body p-4 text-center">
                <div class="bg-warning bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                    <i class="bi bi-gear-fill text-warning fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Account Settings</h5>
                <p class="text-muted small mb-0">Configure your preferences</p>
            </div>
        </div>
    </div>
</div>

@endsection