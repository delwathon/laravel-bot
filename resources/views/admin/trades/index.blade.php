@extends('layouts.app')

@section('title', 'Admin Trades - CryptoBot Pro')

@section('page-title', 'Admin Trade Execution')

@section('content')
<!-- Admin Trade Notice -->
<div class="alert alert-primary border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 flex-shrink-0">
            <i class="bi bi-info-circle-fill text-primary fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-2">Admin Trade Execution Center</h5>
            <p class="mb-0">Trades executed here will be automatically propagated to all active users with connected exchanges. Only successfully executed admin trades will be mirrored.</p>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Admin Trades (24h)</div>
                        <h3 class="fw-bold mb-0">23</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-person-badge text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> 18 Long, 5 Short
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
                        <div class="text-muted text-uppercase small fw-bold mb-1">User Trades (24h)</div>
                        <h3 class="fw-bold mb-0 text-info">5,704</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-people-fill text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">248 users × avg 23 trades</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Success Rate</div>
                        <h3 class="fw-bold mb-0 text-success">95.7%</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">22 of 23 propagated</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Admin Positions</div>
                        <h3 class="fw-bold mb-0 text-warning">8</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Being monitored</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Execute New Trade -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-plus-circle me-2"></i>Execute New Admin Trade
        </h5>
    </div>
    <div class="card-body p-4">
        <form id="executeTradeForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Exchange</label>
                    <select class="form-select" required>
                        <option value="">Select Exchange</option>
                        <option value="bybit">Bybit</option>
                        <option value="binance">Binance</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trading Pair</label>
                    <select class="form-select" required>
                        <option value="">Select Pair</option>
                        <option value="BTCUSDT">BTC/USDT</option>
                        <option value="ETHUSDT">ETH/USDT</option>
                        <option value="SOLUSDT">SOL/USDT</option>
                        <option value="XRPUSDT">XRP/USDT</option>
                        <option value="AVAXUSDT">AVAX/USDT</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Direction</label>
                    <select class="form-select" required>
                        <option value="">Select</option>
                        <option value="long">LONG</option>
                        <option value="short">SHORT</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Entry Price</label>
                    <input type="number" class="form-control" placeholder="66450.00" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Position Size (%)</label>
                    <input type="number" class="form-control" placeholder="5" min="1" max="100" required>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Take Profit</label>
                    <input type="number" class="form-control" placeholder="67200.00" step="0.01" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Stop Loss</label>
                    <input type="number" class="form-control" placeholder="65900.00" step="0.01" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Leverage</label>
                    <select class="form-select" required>
                        <option value="1">1x (No Leverage)</option>
                        <option value="2">2x</option>
                        <option value="3">3x</option>
                        <option value="5">5x</option>
                        <option value="10">10x</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Order Type</label>
                    <select class="form-select" required>
                        <option value="market">Market</option>
                        <option value="limit">Limit</option>
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="propagateToUsers" checked>
                        <label class="form-check-label fw-semibold" for="propagateToUsers">
                            <i class="bi bi-broadcast text-primary me-1"></i>
                            Propagate to all active users after successful execution
                        </label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-play-circle-fill me-2"></i>Execute Admin Trade
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#previewTradeModal">
                    <i class="bi bi-eye me-2"></i>Preview Impact
                </button>
                <button type="reset" class="btn btn-outline-danger btn-lg">
                    <i class="bi bi-x-circle me-2"></i>Clear
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Active Admin Positions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">Active Admin Positions</h5>
                <p class="text-muted small mb-0">Positions currently open on admin account</p>
            </div>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closeAllModal">
                <i class="bi bi-x-octagon me-2"></i>Close All Positions
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Pair</th>
                        <th class="border-0 py-3 fw-semibold">Exchange</th>
                        <th class="border-0 py-3 fw-semibold">Direction</th>
                        <th class="border-0 py-3 fw-semibold">Entry Price</th>
                        <th class="border-0 py-3 fw-semibold">Current Price</th>
                        <th class="border-0 py-3 fw-semibold">Size</th>
                        <th class="border-0 py-3 fw-semibold">P&L</th>
                        <th class="border-0 py-3 fw-semibold">User Mirrors</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Position 1 -->
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
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$66,450</div>
                            <small class="text-muted">5x leverage</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$66,823</div>
                            <small class="text-success">+0.56%</small>
                        </td>
                        <td>
                            <div class="fw-semibold">0.15 BTC</div>
                            <small class="text-muted">~$10,023</small>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$279.45</div>
                            <small class="text-success">+2.79%</small>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">248 users</span>
                            <div class="small text-muted">All active</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Monitor">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="Modify">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Close" data-bs-toggle="modal" data-bs-target="#closePositionModal">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 2 -->
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
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-currency-bitcoin me-1"></i>Binance
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-up-right me-1"></i>LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$3,245</div>
                            <small class="text-muted">3x leverage</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$3,298</div>
                            <small class="text-success">+1.63%</small>
                        </td>
                        <td>
                            <div class="fw-semibold">3.0 ETH</div>
                            <small class="text-muted">~$9,894</small>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$485.22</div>
                            <small class="text-success">+4.90%</small>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">231 users</span>
                            <div class="small text-muted">17 excluded</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closePositionModal">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 3 - Loss -->
                    <tr class="table-danger bg-opacity-10">
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
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-arrow-down-right me-1"></i>SHORT
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$145.80</div>
                            <small class="text-muted">2x leverage</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-danger">$147.23</div>
                            <small class="text-danger">+0.98%</small>
                        </td>
                        <td>
                            <div class="fw-semibold">50 SOL</div>
                            <small class="text-muted">~$7,361</small>
                        </td>
                        <td>
                            <div class="text-danger fw-bold">-$144.15</div>
                            <small class="text-danger">-1.96%</small>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">248 users</span>
                            <div class="small text-muted">All active</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closePositionModal">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Admin Trades History -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">Recent Admin Trades</h5>
                <p class="text-muted small mb-0">Execution history for the last 24 hours</p>
            </div>
            <a href="{{ route('admin.history.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-2"></i>Full History
            </a>
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
                        <th class="border-0 py-3 fw-semibold">Price</th>
                        <th class="border-0 py-3 fw-semibold">Size</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 py-3 fw-semibold">Propagation</th>
                        <th class="border-0 py-3 fw-semibold text-end">Result</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Trade 1 - Success -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">14:23:15</div>
                            <small class="text-muted">2m ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold">BTC/USDT</div>
                            <small class="text-muted">Bybit</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-up-right me-1"></i>BUY/LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$66,450</div>
                        </td>
                        <td>
                            <div class="fw-semibold">0.15 BTC</div>
                            <small class="text-muted">$9,968</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-check-circle"></i> Executed
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success rounded-pill">248/248</span>
                            <div class="small text-success">100% success</div>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-semibold">Active</div>
                            <small class="text-muted">Position open</small>
                        </td>
                    </tr>

                    <!-- Trade 2 - Closed with profit -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">13:45:22</div>
                            <small class="text-muted">40m ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold">ETH/USDT</div>
                            <small class="text-muted">Binance</small>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-arrow-down-left me-1"></i>SELL/CLOSE
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$3,298</div>
                        </td>
                        <td>
                            <div class="fw-semibold">2.5 ETH</div>
                            <small class="text-muted">$8,245</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-check-circle"></i> Executed
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success rounded-pill">231/248</span>
                            <div class="small text-success">93.1% success</div>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-semibold">+$423.50</div>
                            <small class="text-success">+5.4%</small>
                        </td>
                    </tr>

                    <!-- Trade 3 - Partial propagation -->
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">12:18:05</div>
                            <small class="text-muted">2h ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold">XRP/USDT</div>
                            <small class="text-muted">Bybit</small>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-arrow-down-right me-1"></i>SELL/SHORT
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$0.5234</div>
                        </td>
                        <td>
                            <div class="fw-semibold">15,000 XRP</div>
                            <small class="text-muted">$7,851</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-check-circle"></i> Executed
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-warning rounded-pill">215/248</span>
                            <div class="small text-warning">86.7% success</div>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-semibold">Active</div>
                            <small class="text-muted">Position open</small>
                        </td>
                    </tr>

                    <!-- Trade 4 - Failed -->
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="small fw-semibold">11:32:41</div>
                            <small class="text-muted">3h ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold">AVAX/USDT</div>
                            <small class="text-muted">Binance</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-up-right me-1"></i>BUY/LONG
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">$28.45</div>
                        </td>
                        <td>
                            <div class="fw-semibold">200 AVAX</div>
                            <small class="text-muted">$5,690</small>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                <i class="bi bi-x-circle"></i> Failed
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary rounded-pill">0/248</span>
                            <div class="small text-danger">Not propagated</div>
                        </td>
                        <td class="text-end">
                            <div class="text-danger fw-semibold">Failed</div>
                            <small class="text-muted">Insufficient funds</small>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Close Position Modal -->
<div class="modal fade" id="closePositionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-x-octagon me-2"></i>Close Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to close this admin position?</p>
                <div class="alert alert-warning border-0 mb-3">
                    <strong>This will:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Close your admin position immediately</li>
                        <li>Trigger close orders for all 248 user positions</li>
                        <li>Lock in current P&L for all users</li>
                        <li>Cannot be undone</li>
                    </ul>
                </div>
                <div class="card border-0 bg-body-secondary mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Position Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pair</span>
                            <span class="fw-bold">BTC/USDT</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Current P&L</span>
                            <span class="text-success fw-bold">+$279.45</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">User Mirrors</span>
                            <span class="fw-bold">248 positions</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">
                    <i class="bi bi-x-circle me-2"></i>Close Position
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Close All Positions Modal -->
<div class="modal fade" id="closeAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Close All Positions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to close ALL admin positions?</p>
                <div class="alert alert-danger border-0 mb-3">
                    <strong>CRITICAL WARNING:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>This will close ALL 8 admin positions</li>
                        <li>Will trigger close orders for ALL user positions across all pairs</li>
                        <li>Total estimated affected trades: ~1,984 positions</li>
                        <li>This action cannot be undone</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type "CLOSE ALL" to confirm</label>
                    <input type="text" class="form-control" placeholder="Type CLOSE ALL">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" disabled>
                    <i class="bi bi-x-octagon me-2"></i>Close All Positions
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Trade Impact Modal -->
<div class="modal fade" id="previewTradeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-eye me-2"></i>Trade Impact Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Admin Trade</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Position Size</span>
                                    <span class="fw-bold">$10,000</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Potential Profit (TP)</span>
                                    <span class="text-success fw-bold">+$750</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Potential Loss (SL)</span>
                                    <span class="text-danger fw-bold">-$550</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">User Impact (248 users)</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Volume</span>
                                    <span class="fw-bold">~$2,480,000</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Est. User Profit (TP)</span>
                                    <span class="text-success fw-bold">+$186,000</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Est. User Loss (SL)</span>
                                    <span class="text-danger fw-bold">-$136,400</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info border-0 mt-3 mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Estimated values based on current user portfolio sizes. Actual results may vary based on available balance and risk settings.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('executeTradeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if(confirm('Execute this trade for admin account and propagate to all users?')) {
            // Add actual trade execution logic here
            alert('Trade executed successfully! Propagating to users...');
        }
    });
</script>
@endpush