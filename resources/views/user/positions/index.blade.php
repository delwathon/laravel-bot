@extends('layouts.app')

@section('title', 'Active Positions - CryptoBot Pro')

@section('page-title', 'Active Positions')

@section('content')
<!-- Status Banner -->
<div class="card border-0 shadow-sm mb-4 bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3">
                        <i class="bi bi-graph-up-arrow fs-2"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Real-Time Position Monitoring</h4>
                        <p class="mb-0 opacity-75">Your active positions are being monitored 24/7</p>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <div>
                        <div class="text-white text-opacity-75 small">Active Positions</div>
                        <div class="fw-bold fs-5">8</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="text-white text-opacity-75 small">Total Value</div>
                        <div class="fw-bold fs-5">$45,230</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="text-white text-opacity-75 small">Unrealized P&L</div>
                        <div class="fw-bold fs-5">+$1,234</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex p-4 mb-2">
                    <i class="bi bi-activity fs-1"></i>
                </div>
                <div>
                    <span class="badge bg-success bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-2">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Auto-Monitor Active
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Profitable</div>
                        <h4 class="fw-bold mb-0 text-success">6</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-up text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success">+$1,456</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Losing</div>
                        <h4 class="fw-bold mb-0 text-danger">2</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-down text-danger fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger">-$222</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">At Risk</div>
                        <h4 class="fw-bold mb-0 text-warning">1</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Near stop loss</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Last Check</div>
                        <h4 class="fw-bold mb-0 text-info">5s ago</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-clock-history text-info fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Auto-refresh: ON</small>
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
                <select class="form-select" id="filterExchange">
                    <option value="">All Exchanges</option>
                    <option value="bybit">Bybit</option>
                    <option value="binance">Binance</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="profitable">Profitable</option>
                    <option value="losing">Losing</option>
                    <option value="at_risk">At Risk</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="filterType">
                    <option value="">All Types</option>
                    <option value="long">Long</option>
                    <option value="short">Short</option>
                </select>
            </div>
            <div class="col-lg-3">
                <input type="text" class="form-select" placeholder="Search pair..." id="searchPair">
            </div>
            <div class="col-lg-2">
                <button class="btn btn-outline-secondary w-100" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Active Positions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">My Active Positions</h5>
                <p class="text-muted small mb-0">Real-time monitoring with automatic TP/SL execution</p>
            </div>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closeAllModal">
                <i class="bi bi-x-octagon me-2"></i>Close All
            </button>
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
                        <th class="border-0 py-3 fw-semibold">Size</th>
                        <th class="border-0 py-3 fw-semibold">P&L</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Position 1 - Profitable -->
                    <tr>
                        <td class="px-4">
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
                            <small class="text-muted">5m ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$66,823</div>
                            <small class="text-success">+0.56%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success mb-1">TP: $67,200</div>
                                <div class="text-danger">SL: $65,900</div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 68%"></div>
                            </div>
                            <small class="text-muted">68% to TP</small>
                        </td>
                        <td>
                            <div class="fw-semibold">0.15 BTC</div>
                            <small class="text-muted">~$10,023</small>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$279</div>
                            <small class="text-success">+2.79%</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" title="Modify" data-bs-toggle="modal" data-bs-target="#modifyPositionModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Close" data-bs-toggle="modal" data-bs-target="#closePositionModal">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 2 - Profitable -->
                    <tr>
                        <td class="px-4">
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
                            <small class="text-muted">12m ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$3,298</div>
                            <small class="text-success">+1.63%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success mb-1">TP: $3,310</div>
                                <div class="text-danger">SL: $3,180</div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 47%"></div>
                            </div>
                            <small class="text-muted">47% to TP</small>
                        </td>
                        <td>
                            <div class="fw-semibold">3.0 ETH</div>
                            <small class="text-muted">~$9,894</small>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$159</div>
                            <small class="text-success">+4.90%</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modifyPositionModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closePositionModal">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 3 - At Risk -->
                    <tr class="table-warning bg-opacity-10">
                        <td class="px-4">
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
                            <small class="text-muted">28m ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-danger">$147.15</div>
                            <small class="text-danger">+0.93%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success mb-1">TP: $142.50</div>
                                <div class="text-danger">SL: $147.50</div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-danger" style="width: 79%"></div>
                            </div>
                            <small class="text-danger">79% to SL!</small>
                        </td>
                        <td>
                            <div class="fw-semibold">50 SOL</div>
                            <small class="text-muted">~$7,361</small>
                        </td>
                        <td>
                            <div class="text-danger fw-bold">-$67</div>
                            <small class="text-danger">-1.85%</small>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-exclamation-triangle"></i> At Risk
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modifyPositionModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closePositionModal">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 4 - Profitable -->
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-currency-dollar text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">XRP/USDT</div>
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
                            <small class="text-muted">1h ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-success">$0.5450</div>
                            <small class="text-success">+4.13%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success mb-1">TP: $0.5550</div>
                                <div class="text-danger">SL: $0.5100</div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 68%"></div>
                            </div>
                            <small class="text-muted">68% to TP</small>
                        </td>
                        <td>
                            <div class="fw-semibold">5,000 XRP</div>
                            <small class="text-muted">~$2,725</small>
                        </td>
                        <td>
                            <div class="text-success fw-bold">+$108</div>
                            <small class="text-success">+4.1%</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modifyPositionModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#closePositionModal">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Position 5 - Losing -->
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="bi bi-coin text-danger"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">AVAX/USDT</div>
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
                            <div class="fw-semibold">$28.45</div>
                            <small class="text-muted">2h ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-danger">$28.12</div>
                            <small class="text-danger">-1.16%</small>
                        </td>
                        <td>
                            <div class="small">
                                <div class="text-success mb-1">TP: $29.80</div>
                                <div class="text-danger">SL: $27.50</div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: 35%"></div>
                            </div>
                            <small class="text-muted">35% away from SL</small>
                        </td>
                        <td>
                            <div class="fw-semibold">200 AVAX</div>
                            <small class="text-muted">~$5,624</small>
                        </td>
                        <td>
                            <div class="text-danger fw-bold">-$66</div>
                            <small class="text-danger">-1.16%</small>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                <i class="bi bi-arrow-down"></i> Losing
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modifyPositionModal">
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
                <p class="mb-3">Are you sure you want to close this position?</p>
                <div class="card border-0 bg-body-secondary mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Position Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pair</span>
                            <span class="fw-bold">BTC/USDT (Bybit)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Current P&L</span>
                            <span class="text-success fw-bold">+$279.45</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Size</span>
                            <span class="fw-bold">0.15 BTC</span>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning border-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <small>This action will immediately close your position at market price. This cannot be undone.</small>
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

<!-- Modify Position Modal -->
<div class="modal fade" id="modifyPositionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil me-2"></i>Modify Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Modify take profit and stop loss levels for this position</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Position Details</label>
                        <div class="card border-0 bg-body-secondary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Pair</span>
                                    <span class="fw-bold">BTC/USDT</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Entry Price</span>
                                    <span class="fw-bold">$66,450</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Current Price</span>
                                    <span class="text-success fw-bold">$66,823</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_tp" class="form-label fw-semibold">
                            Take Profit Price
                        </label>
                        <input type="number" class="form-control" id="new_tp" value="67200" step="0.01">
                        <div class="form-text small">
                            Current: $67,200
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_sl" class="form-label fw-semibold">
                            Stop Loss Price
                        </label>
                        <input type="number" class="form-control" id="new_sl" value="65900" step="0.01">
                        <div class="form-text small">
                            Current: $65,900
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Position
                    </button>
                </div>
            </form>
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
                <p class="mb-3">Are you sure you want to close ALL active positions?</p>
                <div class="alert alert-danger border-0 mb-3">
                    <strong>WARNING:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>This will close all 8 active positions</li>
                        <li>All positions will be closed at current market price</li>
                        <li>Current unrealized P&L: <strong>+$1,234</strong></li>
                        <li>This action cannot be undone</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type "CLOSE ALL" to confirm</label>
                    <input type="text" class="form-control" id="closeAllConfirm" placeholder="Type CLOSE ALL">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="closeAllBtn" disabled>
                    <i class="bi bi-x-octagon me-2"></i>Close All Positions
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Close All confirmation
    const closeAllInput = document.getElementById('closeAllConfirm');
    const closeAllBtn = document.getElementById('closeAllBtn');
    
    if (closeAllInput && closeAllBtn) {
        closeAllInput.addEventListener('input', function() {
            closeAllBtn.disabled = this.value !== 'CLOSE ALL';
        });
    }

    // Auto-refresh every 10 seconds
    setInterval(() => {
        console.log('Auto-refreshing positions...');
        // Add actual refresh logic here
    }, 10000);
</script>
@endpush