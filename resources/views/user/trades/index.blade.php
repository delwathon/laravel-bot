@extends('layouts.app')

@section('title', 'My Trade History - CryptoBot Pro')

@section('page-title', 'My Trade History')

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Trades</div>
                        <h3 class="fw-bold mb-0">342</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-left-right text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">All time</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Winning Trades</div>
                        <h3 class="fw-bold mb-0 text-success">234</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">68.4% win rate</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Volume</div>
                        <h3 class="fw-bold mb-0 text-info">$98,450</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-cash-stack text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Last 30 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total P&L</div>
                        <h3 class="fw-bold mb-0 text-success">+$4,523</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-currency-dollar text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">+12.3% ROI</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">Date Range</label>
                <select class="form-select">
                    <option value="today">Today</option>
                    <option value="7d">Last 7 Days</option>
                    <option value="30d" selected>Last 30 Days</option>
                    <option value="90d">Last 90 Days</option>
                    <option value="all">All Time</option>
                </select>
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
                <label class="form-label fw-semibold small">Pair</label>
                <select class="form-select">
                    <option value="">All Pairs</option>
                    <option value="BTCUSDT">BTC/USDT</option>
                    <option value="ETHUSDT">ETH/USDT</option>
                    <option value="SOLUSDT">SOL/USDT</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label fw-semibold small">Type</label>
                <select class="form-select">
                    <option value="">All Types</option>
                    <option value="long">Long</option>
                    <option value="short">Short</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">Result</label>
                <select class="form-select">
                    <option value="">All Results</option>
                    <option value="profit">Profitable</option>
                    <option value="loss">Loss</option>
                    <option value="breakeven">Break Even</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-primary">
                <i class="bi bi-funnel me-2"></i>Apply Filters
            </button>
            <button class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-2"></i>Clear
            </button>
        </div>
    </div>
</div>

<!-- Trade History Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">Trade History</h5>
                <p class="text-muted small mb-0">Your complete trading history</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Export CSV
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
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
                        <th class="border-0 py-3 fw-semibold">Type</th>
                        <th class="border-0 py-3 fw-semibold">Entry</th>
                        <th class="border-0 py-3 fw-semibold">Exit</th>
                        <th class="border-0 py-3 fw-semibold">Size</th>
                        <th class="border-0 py-3 fw-semibold">Duration</th>
                        <th class="border-0 py-3 fw-semibold text-end">P&L</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Profitable Trade -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">14:23:15</div>
                            <small class="text-muted">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-currency-bitcoin text-warning small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">BTC/USDT</div>
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
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$67,200</div>
                        </td>
                        <td>
                            <div>0.15 BTC</div>
                            <small class="text-muted">~$10k</small>
                        </td>
                        <td>
                            <small>2h 15m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-bold">+$750</div>
                            <small class="text-success">+7.5%</small>
                        </td>
                    </tr>

                    <!-- Loss Trade -->
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="small fw-semibold">13:45:22</div>
                            <small class="text-muted">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-currency-exchange text-info small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">ETH/USDT</div>
                                    <small class="text-muted">Binance</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-arrow-down-right me-1"></i>SHORT
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$3,245</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-danger">$3,298</div>
                        </td>
                        <td>
                            <div>2.5 ETH</div>
                            <small class="text-muted">~$8.2k</small>
                        </td>
                        <td>
                            <small>45m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-danger fw-bold">-$132</div>
                            <small class="text-danger">-1.6%</small>
                        </td>
                    </tr>

                    <!-- Profitable Trade -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">12:18:05</div>
                            <small class="text-muted">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-purple bg-opacity-10 p-1 rounded-circle me-2" style="background-color: rgba(139, 92, 246, 0.1) !important;">
                                    <i class="bi bi-coin small" style="color: #8b5cf6;"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">SOL/USDT</div>
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
                            <div class="fw-semibold">$145.80</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$149.50</div>
                        </td>
                        <td>
                            <div>50 SOL</div>
                            <small class="text-muted">~$7.3k</small>
                        </td>
                        <td>
                            <small>3h 42m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-bold">+$185</div>
                            <small class="text-success">+2.5%</small>
                        </td>
                    </tr>

                    <!-- Profitable Trade -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">11:32:41</div>
                            <small class="text-muted">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-currency-dollar text-success small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">XRP/USDT</div>
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
                            <div class="fw-semibold">$0.5234</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$0.5450</div>
                        </td>
                        <td>
                            <div>5,000 XRP</div>
                            <small class="text-muted">~$2.6k</small>
                        </td>
                        <td>
                            <small>1h 18m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-bold">+$108</div>
                            <small class="text-success">+4.1%</small>
                        </td>
                    </tr>

                    <!-- Loss Trade -->
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="small fw-semibold">10:15:33</div>
                            <small class="text-muted">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-coin text-danger small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">AVAX/USDT</div>
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
                            <div class="fw-semibold">$28.45</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-danger">$28.90</div>
                        </td>
                        <td>
                            <div>200 AVAX</div>
                            <small class="text-muted">~$5.7k</small>
                        </td>
                        <td>
                            <small>28m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-danger fw-bold">-$90</div>
                            <small class="text-danger">-1.6%</small>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="card-footer bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted small">
                Showing 1 to 5 of 342 trades
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
                    <li class="page-item"><a class="page-link" href="#">69</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

@endsection