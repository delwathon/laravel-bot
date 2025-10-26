@extends('layouts.app')

@section('title', 'API Keys Management - CryptoBot Pro')

@section('page-title', 'API Keys Management')

@section('content')
<!-- Info Banner -->
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3 flex-shrink-0">
            <i class="bi bi-info-circle-fill text-info fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-2">Admin API Key Management</h5>
            <p class="mb-0">View and manage exchange API keys for all users. Keys are encrypted with AES-256. Only key prefixes and statuses are visible.</p>
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
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total API Keys</div>
                        <h4 class="fw-bold mb-0">412</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-key text-primary fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Across 248 users</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Keys</div>
                        <h4 class="fw-bold mb-0 text-success">398</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">96.6% active</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Failed Connections</div>
                        <h4 class="fw-bold mb-0 text-danger">14</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-danger">Need attention</small>
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
                        <h4 class="fw-bold mb-0 text-info">2m ago</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-clock-history text-info fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Auto-checking</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-lg-4">
                <input type="text" class="form-control" placeholder="Search by user name or email...">
            </div>
            <div class="col-lg-2">
                <select class="form-select">
                    <option value="">All Exchanges</option>
                    <option value="bybit">Bybit</option>
                    <option value="binance">Binance</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="col-lg-2">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-2"></i>Filter
                </button>
            </div>
            <div class="col-lg-2">
                <button class="btn btn-outline-success w-100">
                    <i class="bi bi-arrow-clockwise me-2"></i>Check All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- API Keys Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">All API Keys</h5>
                <p class="text-muted small mb-0">Manage exchange API connections for all users</p>
            </div>
            <button class="btn btn-outline-secondary">
                <i class="bi bi-download me-2"></i>Export Report
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">User</th>
                        <th class="border-0 py-3 fw-semibold">Exchange</th>
                        <th class="border-0 py-3 fw-semibold">API Key</th>
                        <th class="border-0 py-3 fw-semibold">Label</th>
                        <th class="border-0 py-3 fw-semibold">Permissions</th>
                        <th class="border-0 py-3 fw-semibold">Last Used</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- API Key 1 - Active -->
                    <tr>
                        <td class="px-4">
                            <div class="fw-semibold">John Doe</div>
                            <small class="text-muted">john.doe@example.com</small>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>
                            <div class="font-monospace small">****...XY2Z</div>
                            <small class="text-muted">Created: Jan 15, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold small">Main Account</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-success bg-opacity-10 text-success">Read</span>
                                <span class="badge bg-info bg-opacity-10 text-info">Trade</span>
                            </div>
                        </td>
                        <td>
                            <small>2 min ago</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Test Connection">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Revoke">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- API Key 2 - Active -->
                    <tr>
                        <td class="px-4">
                            <div class="fw-semibold">John Doe</div>
                            <small class="text-muted">john.doe@example.com</small>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-currency-bitcoin me-1"></i>Binance
                            </span>
                        </td>
                        <td>
                            <div class="font-monospace small">****...AB9C</div>
                            <small class="text-muted">Created: Jan 15, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold small">Trading Account</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-success bg-opacity-10 text-success">Read</span>
                                <span class="badge bg-info bg-opacity-10 text-info">Trade</span>
                            </div>
                        </td>
                        <td>
                            <small>5 min ago</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- API Key 3 - Failed -->
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="fw-semibold">Sarah Chen</div>
                            <small class="text-muted">sarah.chen@example.com</small>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>
                            <div class="font-monospace small">****...MN5P</div>
                            <small class="text-muted">Created: Feb 03, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold small">Main Account</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-success bg-opacity-10 text-success">Read</span>
                                <span class="badge bg-info bg-opacity-10 text-info">Trade</span>
                            </div>
                        </td>
                        <td>
                            <small>2 hours ago</small>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                <i class="bi bi-x-circle"></i> Failed
                            </span>
                            <div class="small text-danger">Invalid signature</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- API Key 4 - Inactive -->
                    <tr class="table-secondary bg-opacity-25">
                        <td class="px-4">
                            <div class="fw-semibold">Michael Rodriguez</div>
                            <small class="text-muted">michael.r@example.com</small>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-currency-bitcoin me-1"></i>Binance
                            </span>
                        </td>
                        <td>
                            <div class="font-monospace small">****...QR7T</div>
                            <small class="text-muted">Created: Mar 21, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold small">—</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-success bg-opacity-10 text-success">Read</span>
                            </div>
                        </td>
                        <td>
                            <small>Never</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Inactive
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- API Key 5 - Active -->
                    <tr>
                        <td class="px-4">
                            <div class="fw-semibold">Emily Watson</div>
                            <small class="text-muted">emily.w@example.com</small>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-currency-bitcoin me-1"></i>Binance
                            </span>
                        </td>
                        <td>
                            <div class="font-monospace small">****...UV8W</div>
                            <small class="text-muted">Created: Apr 10, 2024</small>
                        </td>
                        <td>
                            <div class="fw-semibold small">Primary</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-success bg-opacity-10 text-success">Read</span>
                                <span class="badge bg-info bg-opacity-10 text-info">Trade</span>
                            </div>
                        </td>
                        <td>
                            <small>1 min ago</small>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise"></i>
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
                Showing 1 to 5 of 412 API keys
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
                    <li class="page-item"><a class="page-link" href="#">83</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

@endsection