@extends('layouts.app')

@section('title', 'Trade History - CryptoBot Pro')

@section('page-title', 'Trade History')

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Trades</div>
                        <h4 class="fw-bold mb-0">8,342</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-left-right text-primary fs-5"></i>
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
                        <h4 class="fw-bold mb-0 text-success">5,706</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
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
                        <h4 class="fw-bold mb-0 text-info">$2.4M</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-cash-stack text-info fs-5"></i>
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
                        <h4 class="fw-bold mb-0 text-success">+$45,231</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-currency-dollar text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">+18.8% return</small>
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
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">Pair</label>
                <select class="form-select">
                    <option value="">All Pairs</option>
                    <option value="BTCUSDT">BTCUSDT</option>
                    <option value="ETHUSDT">ETHUSDT</option>
                    <option value="SOLUSDT">SOLUSDT</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">Trade Type</label>
                <select class="form-select">
                    <option value="">All Types</option>
                    <option value="long">Long</option>
                    <option value="short">Short</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold small">User</label>
                <input type="text" class="form-control" placeholder="Search by user...">
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
                <h5 class="fw-bold mb-1">All Trades</h5>
                <p class="text-muted small mb-0">Complete trade history across all users</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Export CSV
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-printer"></i> Print
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
                        <th class="border-0 py-3 fw-semibold">User</th>
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
                    <!-- Trade 1 - Profit -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">14:23:15</div>
                            <small class="text-secondary">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold">John Doe</div>
                            <small class="text-secondary">ID: 1043</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-currency-bitcoin text-warning small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">BTCUSDT</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">LONG</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$66,450</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$67,200</div>
                        </td>
                        <td>
                            <div>0.15 BTC</div>
                            <small class="text-secondary">~$10k</small>
                        </td>
                        <td>
                            <small>2h 15m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-bold">+$750</div>
                            <small class="text-success">+7.5%</small>
                        </td>
                    </tr>

                    <!-- Trade 2 - Loss -->
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="small fw-semibold">13:45:22</div>
                            <small class="text-secondary">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold">Sarah Chen</div>
                            <small class="text-secondary">ID: 1092</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-currency-exchange text-info small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">ETHUSDT</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">SHORT</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$3,245</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-danger">$3,298</div>
                        </td>
                        <td>
                            <div>2.5 ETH</div>
                            <small class="text-secondary">~$8.2k</small>
                        </td>
                        <td>
                            <small>45m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-danger fw-bold">-$132</div>
                            <small class="text-danger">-1.6%</small>
                        </td>
                    </tr>

                    <!-- Trade 3 - Profit -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">12:18:05</div>
                            <small class="text-secondary">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold">Emily Watson</div>
                            <small class="text-secondary">ID: 1156</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-purple bg-opacity-10 p-1 rounded-circle me-2" style="background-color: rgba(139, 92, 246, 0.1) !important;">
                                    <i class="bi bi-coin small" style="color: #8b5cf6;"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">SOLUSDT</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">LONG</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$145.80</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$149.50</div>
                        </td>
                        <td>
                            <div>50 SOL</div>
                            <small class="text-secondary">~$7.3k</small>
                        </td>
                        <td>
                            <small>3h 42m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-bold">+$185</div>
                            <small class="text-success">+2.5%</small>
                        </td>
                    </tr>

                    <!-- Trade 4 - Profit -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">11:32:41</div>
                            <small class="text-secondary">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold">Michael Rodriguez</div>
                            <small class="text-secondary">ID: 1128</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-currency-dollar text-success small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">XRPUSDT</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">LONG</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$0.5234</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$0.5450</div>
                        </td>
                        <td>
                            <div>5,000 XRP</div>
                            <small class="text-secondary">~$2.6k</small>
                        </td>
                        <td>
                            <small>1h 18m</small>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-bold">+$108</div>
                            <small class="text-success">+4.1%</small>
                        </td>
                    </tr>

                    <!-- Trade 5 - Loss -->
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="small fw-semibold">10:15:33</div>
                            <small class="text-secondary">Oct 25, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold">David Kim</div>
                            <small class="text-secondary">ID: 1189</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 p-1 rounded-circle me-2">
                                    <i class="bi bi-coin text-danger small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">AVAXUSDT</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">SHORT</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$28.45</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-danger">$28.90</div>
                        </td>
                        <td>
                            <div>200 AVAX</div>
                            <small class="text-secondary">~$5.7k</small>
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
                Showing 1 to 5 of 8,342 trades
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
                    <li class="page-item"><a class="page-link" href="#">1669</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

@endsection