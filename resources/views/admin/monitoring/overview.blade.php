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
                        <p class="mb-0 opacity-75">Automated monitoring of all active positions across 248 users</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-3">
                        <div class="text-white text-opacity-75 small">Total Positions</div>
                        <div class="fw-bold fs-4">1,342</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-white text-opacity-75 small">Monitored Users</div>
                        <div class="fw-bold fs-4">248</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-white text-opacity-75 small">Active Monitors</div>
                        <div class="fw-bold fs-4">248</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-white text-opacity-75 small">Status</div>
                        <div class="fw-bold fs-5">
                            <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Online
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                    <i class="bi bi-activity fs-1"></i>
                </div>
                <div>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#refreshMonitorsModal">
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
                        <h3 class="fw-bold mb-0 text-success">156</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-bullseye text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> +$127,450
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
                        <h3 class="fw-bold mb-0 text-danger">42</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-shield-exclamation text-danger fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-arrow-down"></i> -$32,180
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
                        <h3 class="fw-bold mb-0 text-warning">23</h3>
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
                        <h3 class="fw-bold mb-0 text-success">99.2%</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-heart-pulse-fill text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">246/248 healthy</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3 align-items-end">
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">User Filter</label>
                <input type="text" class="form-control" placeholder="Search by user name or ID...">
            </div>
            <div class="col-lg-2">
                <label class="form-label fw-semibold small">Exchange</label>
                <select class="form-select">
                    <option value="">All Exchanges</option>
                    <option value="bybit">Bybit</option>
                    <option value="binance">Binance</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label fw-semibold small">Status</label>
                <select class="form-select">
                    <option value="">All Status</option>
                    <option value="profitable">Profitable</option>
                    <option value="losing">Losing</option>
                    <option value="at_risk">At Risk</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label fw-semibold small">Pair</label>
                <select class="form-select">
                    <option value="">All Pairs</option>
                    <option value="BTCUSDT">BTC/USDT</option>
                    <option value="ETHUSDT">ETH/USDT</option>
                    <option value="SOLUSDT">SOL/USDT</option>
                </select>
            </div>
            <div class="col-lg-3">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Positions Being Monitored -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">Live Position Monitoring</h5>
                <p class="text-muted small mb-0">Real-time tracking of all active positions with automated TP/SL execution</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                    <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Auto-refresh: ON
                </span>
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">User</th>
                        <th class="border-0 py-3 fw-semibold">Pair</th>
                        <th class="border-0 py-3 fw-semibold">Type</th>
                        <th class="border-0 py-3 fw-semibold">Entry</th>
                        <th class="border-0 py-3 fw-semibold">Current</th>
                        <th class="border-0 py-3 fw-semibold">TP/SL</th>
                        <th class="border-0 py-3 fw-semibold">P&L</th>
                        <th class="border-0 py-3 fw-semibold">Monitor Status</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Position 1 - Near TP -->
                    <tr class="table-success bg-opacity-10">
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2 flex-shrink-0">
                                    <i class="bi bi-person-fill text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">John Doe</div>
                                    <small class="text-muted">ID: 1043</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-currency-bitcoin text-warning"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">BTC/USDT</div>
                                    <small class="text-muted">Bybit</small>
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
                            <small class="text-muted">5 min ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$67,185</div>
                            <small class="text-success">+1.11%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">TP: $67,200</div>
                                <div class="text-danger">SL: $65,900</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$735</div>
                            <small class="text-success">+5.53%</small>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 97%"></div>
                            </div>
                            <small class="text-muted">97% to TP</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                            <div class="small text-muted">Near TP!</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="Modify">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Force Close">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 2 - Normal -->
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2 flex-shrink-0">
                                    <i class="bi bi-person-fill text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Sarah Chen</div>
                                    <small class="text-muted">ID: 1092</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-currency-exchange text-info"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">ETH/USDT</div>
                                    <small class="text-muted">Binance</small>
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
                            <small class="text-muted">12 min ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$3,276</div>
                            <small class="text-success">+0.95%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">TP: $3,310</div>
                                <div class="text-danger">SL: $3,180</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$186</div>
                            <small class="text-success">+2.86%</small>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 47%"></div>
                            </div>
                            <small class="text-muted">47% to TP</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                            <div class="small text-muted">Healthy</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 3 - At Risk (Near SL) -->
                    <tr class="table-warning bg-opacity-10">
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-2 flex-shrink-0">
                                    <i class="bi bi-person-fill text-warning"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Michael Rodriguez</div>
                                    <small class="text-muted">ID: 1128</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-purple bg-opacity-10 p-2 rounded-circle me-2" style="background-color: rgba(139, 92, 246, 0.1) !important;">
                                    <i class="bi bi-coin" style="color: #8b5cf6;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">SOL/USDT</div>
                                    <small class="text-muted">Bybit</small>
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
                            <small class="text-muted">28 min ago</small>
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
                            <div class="text-danger fw-bold">-$135</div>
                            <small class="text-danger">-1.85%</small>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-danger" style="width: 79%"></div>
                            </div>
                            <small class="text-danger">79% to SL!</small>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-exclamation-triangle-fill"></i> At Risk
                            </span>
                            <div class="small text-warning">Near SL!</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 4 - Monitor Error -->
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-2 flex-shrink-0">
                                    <i class="bi bi-person-fill text-danger"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Emily Watson</div>
                                    <small class="text-muted">ID: 1156</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-currency-dollar text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">XRP/USDT</div>
                                    <small class="text-muted">Bybit</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$0.5234</div>
                            <small class="text-muted">45 min ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-muted">—</div>
                            <small class="text-muted">No data</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">TP: $0.5450</div>
                                <div class="text-danger">SL: $0.5100</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-muted fw-bold">—</div>
                            <small class="text-muted">Unknown</small>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                <i class="bi bi-x-circle"></i> Error
                            </span>
                            <div class="small text-danger">API error</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" title="Restart Monitor">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 5 - Profitable -->
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2 flex-shrink-0">
                                    <i class="bi bi-person-fill text-info"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">David Kim</div>
                                    <small class="text-muted">ID: 1189</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-coin text-danger"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">AVAX/USDT</div>
                                    <small class="text-muted">Binance</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$28.45</div>
                            <small class="text-muted">1h ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$29.12</div>
                            <small class="text-success">+2.36%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success">TP: $29.80</div>
                                <div class="text-danger">SL: $27.50</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$402</div>
                            <small class="text-success">+7.08%</small>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 49%"></div>
                            </div>
                            <small class="text-muted">49% to TP</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                            <div class="small text-muted">Healthy</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="card-footer bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing 1 to 5 of 1,342 active positions
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">...</a></li>
                    <li class="page-item"><a class="page-link" href="#">269</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Refresh Monitors Modal -->
<div class="modal fade" id="refreshMonitorsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-primary bg-opacity-10">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh All Monitors
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">This will restart all position monitoring scripts.</p>
                <div class="alert alert-info border-0 mb-3">
                    <strong>This action will:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Restart monitoring for all 248 users</li>
                        <li>Re-sync position data from exchanges</li>
                        <li>Update all TP/SL tracking</li>
                        <li>Take approximately 30-60 seconds</li>
                    </ul>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="forceRefresh">
                    <label class="form-check-label" for="forceRefresh">
                        Force refresh (ignore cache)
                    </label>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Start Refresh
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-refresh positions every 10 seconds
    let autoRefreshInterval = setInterval(() => {
        console.log('Auto-refreshing positions...');
        // Add actual refresh logic here
        
        // Update timestamp
        const cells = document.querySelectorAll('td small.text-muted');
        // Update time display logic
    }, 10000);

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        clearInterval(autoRefreshInterval);
    });
</script>
@endpush