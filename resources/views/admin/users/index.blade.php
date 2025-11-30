@extends('layouts.app')

@section('title', 'User Management - CryptoBot Pro')

@section('page-title', 'User Management')

@section('content')
<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total Users</div>
                        <h3 class="fw-bold mb-0">{{ $totalUsers }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">All registered accounts</span>
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
                        <h3 class="fw-bold mb-0 text-success">{{ $activeUsers }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-person-check-fill text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">{{ $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0 }}% of total</span>
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
                        <h3 class="fw-bold mb-0 text-info">{{ $connectedExchanges }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-link-45deg text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Bybit accounts</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">New This Month</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $newThisMonth }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-person-plus-fill text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">{{ now()->format('F Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-body-secondary border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search users by name, email, or ID..." 
                               id="searchUsers">
                    </div>
                </div>
                <div class="col-lg-2">
                    <select class="form-select" name="status" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-select" name="exchange" id="filterExchange">
                        <option value="">All Exchanges</option>
                        <option value="connected" {{ request('exchange') == 'connected' ? 'selected' : '' }}>Connected</option>
                        <option value="not_connected" {{ request('exchange') == 'not_connected' ? 'selected' : '' }}>Not Connected</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-select" name="sort_by" id="filterSort">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Newest First</option>
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email (A-Z)</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
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
                        <th class="border-0 py-3 fw-semibold">Exchange</th>
                        <th class="border-0 py-3 fw-semibold">Trades (24h)</th>
                        <th class="border-0 py-3 fw-semibold">Active Positions</th>
                        <th class="border-0 py-3 fw-semibold">P&L (Total)</th>
                        <th class="border-0 py-3 fw-semibold">Joined</th>
                        <th class="border-0 py-3 fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $user->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                        <i class="bi bi-person-fill text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->email }}</small>
                                        <div class="small">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">ID: {{ $user->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->hasConnectedExchange())
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-coin me-1"></i>Bybit
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Not Connected
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info rounded-pill">{{ $user->today_trades_count ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning rounded-pill">{{ $user->active_positions_count ?? 0 }}</span>
                            </td>
                            <td>
                                @php
                                    $pnl = $user->total_pnl ?? 0;
                                    $pnlPercent = $user->total_pnl_percent ?? 0;
                                @endphp
                                <div class="{{ $pnl >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                    {{ $pnl >= 0 ? '+' : '' }}${{ number_format(abs($pnl), 2) }}
                                </div>
                                <small class="{{ $pnlPercent >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $pnlPercent >= 0 ? '+' : '' }}{{ number_format($pnlPercent, 2) }}%
                                </small>
                            </td>
                            <td>
                                <div class="small">{{ $user->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')" 
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <p class="mb-0">No users found</p>
                                    @if(request()->hasAny(['search', 'status', 'exchange', 'sort_by']))
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary mt-3">
                                            Clear Filters
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    @if($users->hasPages())
        <div class="card-footer bg-transparent border-0 p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="text-muted small">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                </div>
                {{ $users->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <div class="alert alert-warning border-0 bg-warning bg-opacity-10">
                    <i class="bi bi-info-circle me-2"></i>
                    This action cannot be undone. All user data will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteUserForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete User
                    </button>
                </form>
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

    function confirmDelete(userId, userName) {
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteUserForm').action = '/admin/users/' + userId;
        new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
    }
</script>
@endpush