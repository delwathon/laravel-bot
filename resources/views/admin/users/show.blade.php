@extends('layouts.app')

@section('title', 'User Details - CryptoBot Pro')

@section('page-title', 'User Profile')

@section('content')
<!-- Back Button -->
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Users
    </a>
</div>

<!-- User Header -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-4">
                        <i class="bi bi-person-fill text-primary fs-1"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">John Doe</h3>
                        <p class="text-muted mb-2">john.doe@example.com</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                ID: 1043
                            </span>
                            <span class="badge bg-info bg-opacity-10 text-info">
                                Member since Jan 15, 2024
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="btn-group">
                    <a href="{{ route('admin.users.edit', 1) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Edit User
                    </a>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendUserModal">
                        <i class="bi bi-ban me-2"></i>Suspend
                    </button>
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
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total P&L</div>
                        <h4 class="fw-bold mb-0 text-success">$4,523</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-currency-dollar text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">+12.3% ROI</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Trades</div>
                        <h4 class="fw-bold mb-0">342</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-arrow-left-right text-primary fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">23 trades today</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Win Rate</div>
                        <h4 class="fw-bold mb-0 text-warning">68.4%</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-trophy-fill text-warning fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-warning" style="width: 68.4%"></div>
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
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Positions</div>
                        <h4 class="fw-bold mb-0 text-info">8</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up text-info fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Currently open</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- User Information -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-person-circle me-2"></i>User Information
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="text-muted small">Full Name</label>
                    <div class="fw-semibold">John Doe</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Email Address</label>
                    <div class="fw-semibold">john.doe@example.com</div>
                    <span class="badge bg-success bg-opacity-10 text-success mt-1">
                        <i class="bi bi-check-circle"></i> Verified
                    </span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Phone Number</label>
                    <div class="fw-semibold">+1 (555) 123-4567</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Country</label>
                    <div class="fw-semibold">United States</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Registration Date</label>
                    <div class="fw-semibold">January 15, 2024</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Last Login</label>
                    <div class="fw-semibold">2 hours ago</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Account Status</label>
                    <div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                        </span>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="text-muted small">2FA Status</label>
                    <div>
                        <span class="badge bg-success bg-opacity-10 text-success">
                            <i class="bi bi-shield-check"></i> Enabled
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exchange Connections -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bank me-2"></i>Connected Exchanges
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Bybit Connection -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary h-100">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-2">
                                            <i class="bi bi-coin text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Bybit</div>
                                            <small class="text-muted">Main Account</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Connected
                                    </span>
                                </div>
                                <div class="small mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">API Key</span>
                                        <span class="fw-semibold">****...XY2Z</span>
                                    </div>
                                </div>
                                <div class="small mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Balance</span>
                                        <span class="fw-semibold">$12,450</span>
                                    </div>
                                </div>
                                <div class="small mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Active Trades</span>
                                        <span class="fw-semibold">5</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary flex-grow-1">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Binance Connection -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-body-secondary h-100">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-2">
                                            <i class="bi bi-currency-bitcoin text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Binance</div>
                                            <small class="text-muted">Trading Account</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Connected
                                    </span>
                                </div>
                                <div class="small mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">API Key</span>
                                        <span class="fw-semibold">****...AB9C</span>
                                    </div>
                                </div>
                                <div class="small mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Balance</span>
                                        <span class="fw-semibold">$8,720</span>
                                    </div>
                                </div>
                                <div class="small mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Active Trades</span>
                                        <span class="fw-semibold">3</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary flex-grow-1">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 mt-3 mb-0">
                    <small>
                        <i class="bi bi-info-circle me-2"></i>
                        Total Balance: <strong>$21,170</strong> across 2 exchanges
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Positions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-graph-up-arrow me-2"></i>Active Positions
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Pair</th>
                        <th class="border-0 py-3 fw-semibold">Exchange</th>
                        <th class="border-0 py-3 fw-semibold">Type</th>
                        <th class="border-0 py-3 fw-semibold">Entry</th>
                        <th class="border-0 py-3 fw-semibold">Current</th>
                        <th class="border-0 py-3 fw-semibold">Size</th>
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
                                <div class="fw-bold">BTC/USDT</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">Bybit</span>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">LONG</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$66,450</div>
                            <small class="text-muted">5m ago</small>
                        </td>
                        <td>
                            <div class="text-success fw-semibold">$66,823</div>
                            <small class="text-success">+0.56%</small>
                        </td>
                        <td>
                            <div>0.15 BTC</div>
                            <small class="text-muted">~$10k</small>
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
                                <div class="fw-bold">ETH/USDT</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">Binance</span>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">LONG</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$3,245</div>
                            <small class="text-muted">12m ago</small>
                        </td>
                        <td>
                            <div class="text-success fw-semibold">$3,298</div>
                            <small class="text-success">+1.63%</small>
                        </td>
                        <td>
                            <div>3.0 ETH</div>
                            <small class="text-muted">~$9.9k</small>
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
                                <div class="fw-bold">SOL/USDT</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">Bybit</span>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">SHORT</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$145.80</div>
                            <small class="text-muted">28m ago</small>
                        </td>
                        <td>
                            <div class="text-danger fw-semibold">$147.15</div>
                            <small class="text-danger">+0.93%</small>
                        </td>
                        <td>
                            <div>50 SOL</div>
                            <small class="text-muted">~$7.4k</small>
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

<!-- Recent Activity -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-clock-history me-2"></i>Recent Trade History
        </h5>
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
                        <th class="border-0 py-3 fw-semibold text-end">P&L</th>
                    </tr>
                </thead>
                <tbody>
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
                            <span class="badge bg-success bg-opacity-10 text-success">BUY</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$66,450</div>
                        </td>
                        <td>
                            <div>0.15 BTC</div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">Executed</span>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-semibold">Active</div>
                        </td>
                    </tr>
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
                            <span class="badge bg-danger bg-opacity-10 text-danger">SELL</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$3,298</div>
                        </td>
                        <td>
                            <div>2.5 ETH</div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">Closed</span>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-bold">+$423.50</div>
                            <small class="text-success">+5.4%</small>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">12:18:33</div>
                            <small class="text-muted">2h ago</small>
                        </td>
                        <td>
                            <div class="fw-semibold">XRP/USDT</div>
                            <small class="text-muted">Bybit</small>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">SELL</span>
                        </td>
                        <td>
                            <div class="fw-semibold">$0.5234</div>
                        </td>
                        <td>
                            <div>5,000 XRP</div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">Closed</span>
                        </td>
                        <td class="text-end">
                            <div class="text-success fw-bold">+$156.80</div>
                            <small class="text-success">+6.1%</small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 p-4">
        <button class="btn btn-outline-primary w-100">
            <i class="bi bi-clock-history me-2"></i>View Full Trade History
        </button>
    </div>
</div>

<!-- Activity Log -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-list-ul me-2"></i>Activity Log
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="timeline">
            <div class="d-flex gap-3 mb-4">
                <div class="bg-success bg-opacity-10 p-2 rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-check-circle-fill text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Trade Executed</div>
                    <small class="text-muted">BTC/USDT LONG position opened</small>
                    <div class="text-muted small mt-1">2 minutes ago</div>
                </div>
            </div>
            <div class="d-flex gap-3 mb-4">
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-box-arrow-in-right text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">User Logged In</div>
                    <small class="text-muted">IP: 192.168.1.1 • Chrome/Windows</small>
                    <div class="text-muted small mt-1">2 hours ago</div>
                </div>
            </div>
            <div class="d-flex gap-3 mb-4">
                <div class="bg-success bg-opacity-10 p-2 rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-bullseye text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Take Profit Hit</div>
                    <small class="text-muted">ETH/USDT closed with +$423.50 profit</small>
                    <div class="text-muted small mt-1">40 minutes ago</div>
                </div>
            </div>
            <div class="d-flex gap-3 mb-4">
                <div class="bg-info bg-opacity-10 p-2 rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-gear text-info"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Settings Updated</div>
                    <small class="text-muted">Modified risk management settings</small>
                    <div class="text-muted small mt-1">Yesterday at 3:45 PM</div>
                </div>
            </div>
            <div class="d-flex gap-3">
                <div class="bg-warning bg-opacity-10 p-2 rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-bank text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Exchange Connected</div>
                    <small class="text-muted">Binance API successfully connected</small>
                    <div class="text-muted small mt-1">Jan 15, 2024</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suspend User Modal -->
<div class="modal fade" id="suspendUserModal" tabindex="-1" aria-labelledby="suspendUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suspendUserModalLabel">Suspend User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to suspend this user? They will not be able to log in or access their account until reactivated.</p>
                <div class="mb-3">
                    <label for="suspensionReason" class="form-label">Reason for Suspension (optional):</label>
                    <textarea class="form-control" id="suspensionReason" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">Suspend User</button>
            </div>
        </div>
    </div>
</div>
@endsection