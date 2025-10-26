@extends('layouts.app')

@section('title', 'User Management - CryptoBot Pro')

@section('page-title', 'User Management')

@section('content')
<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Users</div>
                        <h3 class="fw-bold mb-0">248</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-up"></i> 12 this month
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
                        <div class="text-muted text-uppercase small fw-bold mb-1">Active Users</div>
                        <h3 class="fw-bold mb-0 text-success">231</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-person-check-fill text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">93.1% active rate</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Connected Exchanges</div>
                        <h3 class="fw-bold mb-0 text-info">412</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-bank text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Avg 1.7 per user</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Trades (24h)</div>
                        <h3 class="fw-bold mb-0 text-warning">1,342</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-graph-up text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">5.4 per user</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-body-secondary border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Search users by name, email, or ID..." id="searchUsers">
                </div>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="filterExchange">
                    <option value="">All Exchanges</option>
                    <option value="bybit">Bybit</option>
                    <option value="binance">Binance</option>
                    <option value="both">Both</option>
                    <option value="none">Not Connected</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="filterSort">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name">Name (A-Z)</option>
                    <option value="trades">Most Trades</option>
                    <option value="pnl">Highest P&L</option>
                </select>
            </div>
            <div class="col-lg-2">
                <button class="btn btn-outline-secondary w-100">
                    <i class="bi bi-funnel me-2"></i>More Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">All Users</h5>
                <p class="text-muted small mb-0">Manage user accounts, API connections, and trading status</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Export
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                    <i class="bi bi-lightning-fill"></i> Bulk Actions
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th class="border-0 py-3 fw-semibold">User</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 py-3 fw-semibold">Exchanges</th>
                        <th class="border-0 py-3 fw-semibold">Trades (24h)</th>
                        <th class="border-0 py-3 fw-semibold">Active Positions</th>
                        <th class="border-0 py-3 fw-semibold">P&L (Total)</th>
                        <th class="border-0 py-3 fw-semibold">Joined</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- User Row 1 -->
                    <tr>
                        <td class="px-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-person-fill text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">John Doe</div>
                                    <small class="text-muted">john.doe@example.com</small>
                                    <div class="small">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">ID: 1043</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-coin me-1"></i>Bybit
                                </span>
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-currency-bitcoin me-1"></i>Binance
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">23</span>
                        </td>
                        <td>
                            <span class="badge bg-warning rounded-pill">8</span>
                        </td>
                        <td>
                            <div class="text-success fw-semibold">+$4,523</div>
                            <small class="text-muted">+12.3%</small>
                        </td>
                        <td>
                            <div class="small">Jan 15, 2024</div>
                            <small class="text-muted">9 months ago</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.show', 3) }}" class="btn btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', 3) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-danger" title="Suspend" data-bs-toggle="modal" data-bs-target="#suspendUserModal">
                                    <i class="bi bi-ban"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- User Row 2 -->
                    <tr>
                        <td class="px-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-person-fill text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Sarah Chen</div>
                                    <small class="text-muted">sarah.chen@example.com</small>
                                    <div class="small">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">ID: 1092</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-coin me-1"></i>Bybit
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">18</span>
                        </td>
                        <td>
                            <span class="badge bg-warning rounded-pill">5</span>
                        </td>
                        <td>
                            <div class="text-success fw-semibold">+$8,921</div>
                            <small class="text-muted">+23.7%</small>
                        </td>
                        <td>
                            <div class="small">Feb 03, 2024</div>
                            <small class="text-muted">8 months ago</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="#" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="#" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendUserModal">
                                    <i class="bi bi-ban"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- User Row 3 - Inactive -->
                    <tr class="table-secondary bg-opacity-25">
                        <td class="px-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-person-fill text-secondary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Michael Rodriguez</div>
                                    <small class="text-muted">michael.r@example.com</small>
                                    <div class="small">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">ID: 1128</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Inactive
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-x-circle me-1"></i>Not Connected
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary rounded-pill">0</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary rounded-pill">0</span>
                        </td>
                        <td>
                            <div class="text-muted fw-semibold">$0</div>
                            <small class="text-muted">0%</small>
                        </td>
                        <td>
                            <div class="small">Mar 21, 2024</div>
                            <small class="text-muted">7 months ago</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="#" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="#" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-success" title="Activate">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- User Row 4 -->
                    <tr>
                        <td class="px-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-person-fill text-info"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Emily Watson</div>
                                    <small class="text-muted">emily.w@example.com</small>
                                    <div class="small">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">ID: 1156</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-currency-bitcoin me-1"></i>Binance
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">31</span>
                        </td>
                        <td>
                            <span class="badge bg-warning rounded-pill">12</span>
                        </td>
                        <td>
                            <div class="text-success fw-semibold">+$6,134</div>
                            <small class="text-muted">+15.8%</small>
                        </td>
                        <td>
                            <div class="small">Apr 10, 2024</div>
                            <small class="text-muted">6 months ago</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="#" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="#" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendUserModal">
                                    <i class="bi bi-ban"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- User Row 5 - Loss -->
                    <tr>
                        <td class="px-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-person-fill text-warning"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">David Kim</div>
                                    <small class="text-muted">david.kim@example.com</small>
                                    <div class="small">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">ID: 1189</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-coin me-1"></i>Bybit
                                </span>
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-currency-bitcoin me-1"></i>Binance
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info rounded-pill">15</span>
                        </td>
                        <td>
                            <span class="badge bg-warning rounded-pill">3</span>
                        </td>
                        <td>
                            <div class="text-danger fw-semibold">-$1,245</div>
                            <small class="text-muted">-5.2%</small>
                        </td>
                        <td>
                            <div class="small">May 28, 2024</div>
                            <small class="text-muted">5 months ago</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.show', 3) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', 3) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendUserModal">
                                    <i class="bi bi-ban"></i>
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
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted small">
                Showing 1 to 5 of 248 users
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">4</a></li>
                    <li class="page-item"><a class="page-link" href="#">5</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Suspend User Modal -->
<div class="modal fade" id="suspendUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Suspend User Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to suspend this user account?</p>
                <div class="alert alert-warning border-0 mb-3">
                    <strong>Effects of suspension:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>User will be logged out immediately</li>
                        <li>All active positions will be closed</li>
                        <li>Trading signals will stop</li>
                        <li>API connections will be disabled</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason for suspension</label>
                    <textarea class="form-control" rows="3" placeholder="Enter reason (optional)"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">
                    <i class="bi bi-ban me-2"></i>Suspend Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-lightning-fill me-2"></i>Bulk Actions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Select an action to apply to all selected users:</p>
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong>Activate Selected Users</strong>
                        <small class="d-block text-muted">Enable trading for selected accounts</small>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action">
                        <i class="bi bi-pause-circle text-warning me-2"></i>
                        <strong>Pause Trading</strong>
                        <small class="d-block text-muted">Temporarily stop all trading activities</small>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action">
                        <i class="bi bi-ban text-danger me-2"></i>
                        <strong>Suspend Accounts</strong>
                        <small class="d-block text-muted">Disable accounts and close positions</small>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action">
                        <i class="bi bi-envelope text-primary me-2"></i>
                        <strong>Send Notification</strong>
                        <small class="d-block text-muted">Send email to selected users</small>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action">
                        <i class="bi bi-download text-info me-2"></i>
                        <strong>Export Data</strong>
                        <small class="d-block text-muted">Export selected user data to CSV</small>
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Select All Checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    // Search functionality
    document.getElementById('searchUsers').addEventListener('input', function(e) {
        // Add search logic here
        console.log('Searching for:', e.target.value);
    });

    // Filter functionality
    document.getElementById('filterStatus').addEventListener('change', function(e) {
        console.log('Filter by status:', e.target.value);
    });

    document.getElementById('filterExchange').addEventListener('change', function(e) {
        console.log('Filter by exchange:', e.target.value);
    });

    document.getElementById('filterSort').addEventListener('change', function(e) {
        console.log('Sort by:', e.target.value);
    });
</script>
@endpush