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
                        <h4 class="fw-bold mb-0">$2.4M</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up text-primary fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> +18.2%
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
                        <h4 class="fw-bold mb-0 text-success">68.4%</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-trophy-fill text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 68.4%"></div>
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
                        <h4 class="fw-bold mb-0 text-warning">$12,450</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-cash-coin text-warning fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">0.52% of volume</small>
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
                        <h4 class="fw-bold mb-0 text-info">2.34</h4>
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
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Cumulative P&L</h5>
                        <p class="text-muted small mb-0">All users combined performance</p>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active">1M</button>
                        <button class="btn btn-outline-secondary">3M</button>
                        <button class="btn btn-outline-secondary">6M</button>
                        <button class="btn btn-outline-secondary">1Y</button>
                        <button class="btn btn-outline-secondary">ALL</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="border rounded-3 p-5 text-center bg-body-secondary">
                    <i class="bi bi-bar-chart-line-fill text-muted mb-3" style="font-size: 4rem;"></i>
                    <p class="text-muted mb-2">P&L Chart</p>
                    <small class="text-muted">ApexCharts / Chart.js integration</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution Chart -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">User Distribution</h5>
            </div>
            <div class="card-body p-4">
                <div class="border rounded-3 p-4 text-center bg-body-secondary mb-3">
                    <i class="bi bi-pie-chart-fill text-muted mb-2" style="font-size: 3rem;"></i>
                    <p class="text-muted small mb-0">Pie Chart</p>
                </div>
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle-fill text-success me-2"></i>Profitable</span>
                        <span class="fw-bold">67%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle-fill text-warning me-2"></i>Break Even</span>
                        <span class="fw-bold">18%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="bi bi-circle-fill text-danger me-2"></i>Loss</span>
                        <span class="fw-bold">15%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>Key Performance Metrics
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="border-start border-primary border-3 ps-3">
                            <div class="text-muted small mb-1">Total Trades</div>
                            <div class="fw-bold fs-5">8,342</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-success border-3 ps-3">
                            <div class="text-muted small mb-1">Winning Trades</div>
                            <div class="fw-bold fs-5 text-success">5,706</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-danger border-3 ps-3">
                            <div class="text-muted small mb-1">Losing Trades</div>
                            <div class="fw-bold fs-5 text-danger">2,636</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-warning border-3 ps-3">
                            <div class="text-muted small mb-1">Avg Trade Duration</div>
                            <div class="fw-bold fs-5">4.2h</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-info border-3 ps-3">
                            <div class="text-muted small mb-1">Best Trade</div>
                            <div class="fw-bold fs-5 text-success">+$2,345</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-secondary border-3 ps-3">
                            <div class="text-muted small mb-1">Worst Trade</div>
                            <div class="fw-bold fs-5 text-danger">-$890</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bank me-2"></i>Exchange Performance
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary me-2">
                                <i class="bi bi-coin"></i> Bybit
                            </span>
                            <span class="fw-semibold">$1.4M</span>
                        </div>
                        <span class="text-success small">+14.2%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: 58%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge bg-warning bg-opacity-10 text-warning me-2">
                                <i class="bi bi-currency-bitcoin"></i> Binance
                            </span>
                            <span class="fw-semibold">$1.0M</span>
                        </div>
                        <span class="text-success small">+12.8%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: 42%"></div>
                    </div>
                </div>
                <div class="alert alert-info border-0 mb-0 mt-3">
                    <small>
                        <i class="bi bi-info-circle me-2"></i>
                        Bybit shows higher volume with 4,845 trades vs Binance's 3,497 trades
                    </small>
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
                    <tr>
                        <td class="px-4">
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="bi bi-trophy-fill"></i> 1
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">Sarah Chen</div>
                            <small class="text-muted">ID: 1092</small>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">287</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-success" style="width: 76%"></div>
                                </div>
                                <span class="fw-bold text-success">76%</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$12,450</div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">+34.2%</span>
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4">
                            <span class="badge bg-secondary text-white fs-6">2</span>
                        </td>
                        <td>
                            <div class="fw-semibold">John Doe</div>
                            <small class="text-muted">ID: 1043</small>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">342</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-success" style="width: 68%"></div>
                                </div>
                                <span class="fw-bold text-success">68%</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$8,923</div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">+24.5%</span>
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4">
                            <span class="badge bg-secondary text-white fs-6">3</span>
                        </td>
                        <td>
                            <div class="fw-semibold">Emily Watson</div>
                            <small class="text-muted">ID: 1156</small>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">198</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-success" style="width: 72%"></div>
                                </div>
                                <span class="fw-bold text-success">72%</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$7,654</div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">+28.1%</span>
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
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
                    <i class="bi bi-clock-history me-2"></i>Trading Activity by Time
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="border rounded-3 p-5 text-center bg-body-secondary">
                    <i class="bi bi-calendar3 text-muted mb-3" style="font-size: 3rem;"></i>
                    <p class="text-muted mb-0">Heatmap Chart</p>
                    <small class="text-muted">Peak hours: 9AM - 11AM, 2PM - 4PM UTC</small>
                </div>
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
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">BTC/USDT</span>
                        <span class="badge bg-primary rounded-pill">2,845 trades</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 100%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">ETH/USDT</span>
                        <span class="badge bg-primary rounded-pill">2,134 trades</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 75%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">SOL/USDT</span>
                        <span class="badge bg-primary rounded-pill">1,567 trades</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-purple" style="width: 55%; background-color: #8b5cf6;"></div>
                    </div>
                </div>
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">XRP/USDT</span>
                        <span class="badge bg-primary rounded-pill">1,234 trades</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 43%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection