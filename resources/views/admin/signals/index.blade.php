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
                                <div class="fw-bold fs-5">in 12 minutes</div>
                            </div>
                            <div class="vr"></div>
                            <div>
                                <div class="text-white text-opacity-75 small">Interval</div>
                                <div class="fw-bold fs-5">Every 15 min</div>
                            </div>
                            <div class="vr"></div>
                            <div>
                                <div class="text-white text-opacity-75 small">Top Signals</div>
                                <div class="fw-bold fs-5">5 pairs</div>
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
                        <span class="fw-bold">96 signals</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Executed</span>
                        <span class="fw-bold text-success">87 trades</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Win Rate</span>
                        <span class="fw-bold text-success">68.4%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Avg Confidence</span>
                        <span class="fw-bold">82.3%</span>
                    </div>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: 68.4%"></div>
                </div>
                <small class="text-muted">Positive P&L: +$45,231</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-lg-3">
                <select class="form-select" id="filterExchange">
                    <option value="">All Exchanges</option>
                    <option value="bybit">Bybit</option>
                    <option value="binance">Binance</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="filterDirection">
                    <option value="">All Directions</option>
                    <option value="long">Long</option>
                    <option value="short">Short</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="filterConfidence">
                    <option value="">All Confidence</option>
                    <option value="high">High (&gt;80%)</option>
                    <option value="medium">Medium (60-80%)</option>
                    <option value="low">Low (&lt;60%)</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="executed">Executed</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div class="col-lg-3">
                <select class="form-select" id="filterTimeframe">
                    <option value="today">Today</option>
                    <option value="24h">Last 24 Hours</option>
                    <option value="7d">Last 7 Days</option>
                    <option value="30d">Last 30 Days</option>
                </select>
            </div>
        </div>
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
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button class="btn btn-outline-secondary">
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
                        <th class="border-0 px-4 py-3 fw-semibold">Time</th>
                        <th class="border-0 py-3 fw-semibold">Pair</th>
                        <th class="border-0 py-3 fw-semibold">Exchange</th>
                        <th class="border-0 py-3 fw-semibold">Direction</th>
                        <th class="border-0 py-3 fw-semibold">Entry Price</th>
                        <th class="border-0 py-3 fw-semibold">Confidence</th>
                        <th class="border-0 py-3 fw-semibold">SMC Pattern</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Signal Row 1 - High Confidence -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">14:23:15</div>
                            <small class="text-muted">2 min ago</small>
                        </td>
                        <td>
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
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$66,450</div>
                            <small class="text-muted">TP: $67,200</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-success" style="width: 87%"></div>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success">87%</span>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div class="badge bg-info bg-opacity-10 text-info mb-1">Order Block</div>
                                <div class="badge bg-warning bg-opacity-10 text-warning">FVG</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#signalDetailsModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" title="Execute Now">
                                    <i class="bi bi-play-circle"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Dismiss">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Signal Row 2 - Executed -->
                    <tr class="table-success bg-opacity-10">
                        <td class="px-4">
                            <div class="small fw-semibold">14:08:42</div>
                            <small class="text-muted">17 min ago</small>
                        </td>
                        <td>
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
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-currency-bitcoin me-1"></i>Binance
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$3,245</div>
                            <small class="text-muted">TP: $3,310</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-success" style="width: 82%"></div>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success">82%</span>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div class="badge bg-info bg-opacity-10 text-info mb-1">Break of Structure</div>
                                <div class="badge bg-success bg-opacity-10 text-success">Liquidity Sweep</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-check-circle"></i> Executed
                                <div class="small">248 users</div>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#signalDetailsModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-info" title="View Trades">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Signal Row 3 - Short Position -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">13:53:20</div>
                            <small class="text-muted">32 min ago</small>
                        </td>
                        <td>
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
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                <i class="bi bi-arrow-down-right me-1"></i>SHORT
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$145.80</div>
                            <small class="text-muted">TP: $142.50</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-warning" style="width: 75%"></div>
                                </div>
                                <span class="badge bg-warning bg-opacity-10 text-warning">75%</span>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div class="badge bg-danger bg-opacity-10 text-danger mb-1">CHoCH</div>
                                <div class="badge bg-info bg-opacity-10 text-info">Order Block</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#signalDetailsModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-success">
                                    <i class="bi bi-play-circle"></i>
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Signal Row 4 - Medium Confidence -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">13:38:05</div>
                            <small class="text-muted">47 min ago</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-currency-dollar text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">XRP/USDT</div>
                                    <small class="text-muted">Ripple</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-currency-bitcoin me-1"></i>Binance
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$0.5234</div>
                            <small class="text-muted">TP: $0.5450</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-warning" style="width: 68%"></div>
                                </div>
                                <span class="badge bg-warning bg-opacity-10 text-warning">68%</span>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div class="badge bg-warning bg-opacity-10 text-warning">FVG</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                <i class="bi bi-x-circle"></i> Expired
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#signalDetailsModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="bi bi-archive"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Signal Row 5 - Executed -->
                    <tr class="table-success bg-opacity-10">
                        <td class="px-4">
                            <div class="small fw-semibold">13:23:18</div>
                            <small class="text-muted">1h ago</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-coin text-danger"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">AVAX/USDT</div>
                                    <small class="text-muted">Avalanche</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                <i class="bi bi-arrow-down-right me-1"></i>SHORT
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$28.45</div>
                            <small class="text-muted">TP: $27.80</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-success" style="width: 91%"></div>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success">91%</span>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div class="badge bg-danger bg-opacity-10 text-danger mb-1">Bearish OB</div>
                                <div class="badge bg-info bg-opacity-10 text-info">Premium Zone</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-check-circle"></i> Executed
                                <div class="small">231 users</div>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#signalDetailsModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-info">
                                    <i class="bi bi-list-ul"></i>
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
                Showing 1 to 5 of 96 signals today
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
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
            <div class="modal-body">
                <p class="mb-3">Start the SMC analysis engine to generate new trading signals.</p>
                <div class="alert alert-info border-0 mb-3">
                    <strong>What happens next:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Market data will be fetched from all exchanges</li>
                        <li>SMC patterns will be analyzed in real-time</li>
                        <li>Top signals will be ranked by confidence</li>
                        <li>Signals will be ready for execution in 30-60 seconds</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Exchanges</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="exchangeBybit" checked>
                        <label class="form-check-label" for="exchangeBybit">
                            <i class="bi bi-coin text-primary me-1"></i>Bybit
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="exchangeBinance" checked>
                        <label class="form-check-label" for="exchangeBinance">
                            <i class="bi bi-currency-bitcoin text-warning me-1"></i>Binance
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="startSignalGeneration()">
                    <i class="bi bi-play-circle-fill me-2"></i>Start Analysis
                </button>
            </div>
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
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Trade Information</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Pair</span>
                                    <span class="fw-bold">BTC/USDT</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Exchange</span>
                                    <span class="badge bg-primary">Bybit</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Direction</span>
                                    <span class="badge bg-success">LONG</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Entry Price</span>
                                    <span class="fw-bold">$66,450.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Take Profit</span>
                                    <span class="text-success fw-bold">$67,200.00</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Stop Loss</span>
                                    <span class="text-danger fw-bold">$65,900.00</span>
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
                                    <span class="badge bg-success">87%</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Risk/Reward</span>
                                    <span class="fw-bold">1:3.2</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Timeframe</span>
                                    <span class="fw-bold">15M</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Generated</span>
                                    <span class="fw-bold">14:23:15</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Expires</span>
                                    <span class="text-warning fw-bold">in 12 min</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">SMC Patterns Detected</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                            <i class="bi bi-graph-up me-1"></i>Order Block (Bullish)
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
                            <i class="bi bi-gap me-1"></i>Fair Value Gap
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                            <i class="bi bi-water me-1"></i>Liquidity Sweep
                        </span>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">
                            <i class="bi bi-arrow-up-circle me-1"></i>Break of Structure
                        </span>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Market Context</h6>
                    <p class="small text-muted mb-0">
                        Strong bullish momentum detected on the 15-minute timeframe. Price has swept liquidity below recent lows and is now reacting from a bullish order block. Fair value gap present above current price, suggesting potential upside. Break of structure confirmed at $66,200 level.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success">
                    <i class="bi bi-play-circle-fill me-2"></i>Execute for All Users
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function startSignalGeneration() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('generateSignalModal'));
        modal.hide();
        
        // Show loading state
        alert('Signal generation started! Analysis will complete in 30-60 seconds.');
        
        // In production, this would trigger the actual signal generation
        setTimeout(() => {
            location.reload();
        }, 2000);
    }

    // Auto-refresh signals every 60 seconds
    setInterval(() => {
        console.log('Auto-refreshing signals...');
        // Add actual refresh logic here
    }, 60000);
</script>
@endpush