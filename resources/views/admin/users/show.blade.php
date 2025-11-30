@extends('layouts.app')

@section('title', 'View User - CryptoBot Pro')

@section('page-title', 'User Details')

@section('content')
<!-- Back Button -->
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Users
    </a>
</div>

<!-- User Profile Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-4">
                        <i class="bi bi-person-fill text-primary fs-1"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">{{ $user->name }}</h3>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($user->email_verified_at)
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-check-circle-fill me-1"></i>Verified Account
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>Not Verified
                                </span>
                            @endif
                            @if($user->hasConnectedExchange())
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-link-45deg me-1"></i>Bybit Connected
                                </span>
                            @endif
                            <span class="badge bg-info bg-opacity-10 text-info">
                                Member since {{ $user->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="btn-group">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Edit User
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Information -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-person-circle me-2"></i>Personal Information
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold mb-1">Full Name</label>
                    <div class="fw-semibold">{{ $user->name }}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold mb-1">Email Address</label>
                    <div class="fw-semibold">{{ $user->email }}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold mb-1">User ID</label>
                    <div class="fw-semibold">#{{ $user->id }}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold mb-1">Registration Date</label>
                    <div class="fw-semibold">{{ $user->created_at->format('M d, Y h:i A') }}</div>
                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                </div>
                <div>
                    <label class="small text-muted text-uppercase fw-bold mb-1">Last Updated</label>
                    <div class="fw-semibold">{{ $user->updated_at->format('M d, Y h:i A') }}</div>
                    <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-graph-up me-2"></i>Trading Statistics
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold mb-1">Total Trades</label>
                    <div class="fw-bold fs-4">{{ $userStats['total_trades'] }}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold mb-1">Active Positions</label>
                    <div class="fw-bold fs-4 text-info">{{ $userStats['active_positions'] }}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold mb-1">Total P&L</label>
                    <div class="fw-bold fs-4 {{ $userStats['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $userStats['total_profit'] >= 0 ? '+' : '' }}${{ number_format(abs($userStats['total_profit']), 2) }}
                    </div>
                </div>
                <div>
                    <label class="small text-muted text-uppercase fw-bold mb-1">Win Rate</label>
                    <div class="fw-bold fs-4">{{ $userStats['win_rate'] }}%</div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $userStats['win_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Exchange Connection -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-bank me-2"></i>Bybit Exchange Connection
        </h5>
    </div>
    <div class="card-body p-4">
        @if($user->hasConnectedExchange())
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-link-45deg text-primary fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Bybit</h5>
                            <p class="text-muted mb-0">
                                @if($user->exchangeAccount->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Inactive
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">API Key</label>
                            <div>
                                <code class="bg-body-secondary p-2 rounded">{{ $user->exchangeAccount->masked_api_key }}</code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Connected On</label>
                            <div class="fw-semibold">{{ $user->exchangeAccount->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $user->exchangeAccount->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Last Synced</label>
                            <div>
                                @if($user->exchangeAccount->last_synced_at)
                                    <div class="fw-semibold">{{ $user->exchangeAccount->last_synced_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Account Balance</label>
                            <div class="fw-bold fs-5">${{ number_format($user->exchangeAccount->balance, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <form action="{{ route('admin.users.sync-balance', $user->exchangeAccount->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-clockwise me-2"></i>Sync Balance
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-info border-0 bg-info bg-opacity-10">
                <i class="bi bi-info-circle me-2"></i>
                No Bybit account connected yet. User needs to connect their exchange account to start trading.
            </div>
        @endif
    </div>
</div>

<!-- Recent Activity -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-clock-history me-2"></i>Recent Activity
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-info border-0 bg-info bg-opacity-10">
            <i class="bi bi-info-circle me-2"></i>
            No recent activity. Trading functionality will be implemented in the next phase.
        </div>
    </div>
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
                    This action cannot be undone. All user data including exchange connections will be permanently deleted.
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
    function confirmDelete(userId, userName) {
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteUserForm').action = '/admin/users/' + userId;
        new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
    }
</script>
@endpush