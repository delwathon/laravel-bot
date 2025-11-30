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
            <p class="mb-0">Manage admin exchange API keys for signal generation and market data. Also view all user API connections.</p>
        </div>
    </div>
</div>

<!-- Admin API Keys Section -->
<div class="card border-0 shadow-sm mb-4 bg-primary bg-opacity-10">
    <div class="card-body p-4">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-25 p-3 rounded-circle me-3">
                <i class="bi bi-shield-lock-fill fs-2 text-primary"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="fw-bold mb-1">Admin Exchange API Keys</h5>
                <p class="mb-0 text-muted">Configure admin Bybit API credentials for signal generation and market data</p>
            </div>
            @if(!$bybitAccount)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addApiModal">
                    <i class="bi bi-plus-lg me-2"></i>Add API Keys
                </button>
            @endif
        </div>
    </div>
</div>

@if($bybitAccount)
<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-link-45deg me-2"></i>Bybit API Connection
                    </h5>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.api-keys.toggle', 'bybit') }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $bybitAccount->is_active ? 'btn-warning' : 'btn-success' }}">
                                <i class="bi bi-{{ $bybitAccount->is_active ? 'pause' : 'play' }}-fill me-1"></i>
                                {{ $bybitAccount->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editApiModal">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <form action="{{ route('admin.api-keys.destroy', 'bybit') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove these API keys?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash me-1"></i>Remove
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold mb-1">API Key</label>
                        <div class="d-flex align-items-center">
                            <code class="bg-body-secondary p-2 rounded flex-grow-1">{{ $bybitAccount->masked_api_key }}</code>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Status</label>
                        <div>
                            @if($bybitAccount->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Admin Balance</label>
                        <div class="fw-bold fs-5">${{ number_format($adminBalance, 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Last Synced</label>
                        <div>
                            @if($bybitAccount->last_synced_at)
                                {{ $bybitAccount->last_synced_at->diffForHumans() }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <form action="{{ route('admin.api-keys.sync', 'bybit') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-arrow-clockwise me-1"></i>Sync Admin Balance
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editApiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Edit Admin Bybit API Keys</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.api-keys.store') }}" method="POST">
                @csrf
                <input type="hidden" name="exchange" value="bybit">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_api_key" class="form-label fw-semibold">API Key</label>
                        <input type="text" class="form-control" id="edit_api_key" name="api_key" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_api_secret" class="form-label fw-semibold">API Secret</label>
                        <input type="password" class="form-control" id="edit_api_secret" name="api_secret" required>
                    </div>
                    <div class="alert alert-info border-0 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>These credentials will be encrypted and used for signal generation and market data fetching.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@else

<div class="modal fade" id="addApiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Add Admin Bybit API Keys</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.api-keys.store') }}" method="POST">
                @csrf
                <input type="hidden" name="exchange" value="bybit">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label for="api_key" class="form-label fw-semibold">
                            API Key <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('api_key') is-invalid @enderror" 
                               id="api_key" 
                               name="api_key" 
                               value="{{ old('api_key') }}"
                               placeholder="Enter your Bybit API Key" 
                               required>
                        @error('api_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="api_secret" class="form-label fw-semibold">
                            API Secret <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg @error('api_secret') is-invalid @enderror" 
                               id="api_secret" 
                               name="api_secret"
                               placeholder="Enter your Bybit API Secret" 
                               required>
                        @error('api_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 mb-0">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle fs-5 me-3"></i>
                            <div>
                                <strong>Admin API Key Requirements:</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Must have Read permission for market data</li>
                                    <li>Used only for signal generation and analysis</li>
                                    <li>Does not require trading permissions</li>
                                    <li>Credentials are encrypted at rest</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-link-45deg me-2"></i>Connect API Keys
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif

<!-- Stats Overview -->
<div class="row g-3 mb-4 mt-4">
    <div class="col-12">
        <h5 class="fw-bold mb-3">User API Keys Overview</h5>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Total API Keys</div>
                        <h4 class="fw-bold mb-0">{{ $stats['total_keys'] }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-key text-primary fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Across {{ $stats['total_users'] }} users</small>
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
                        <h4 class="fw-bold mb-0 text-success">{{ $stats['active_keys'] }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">{{ $stats['active_percentage'] }}% active</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold mb-1">Inactive Keys</div>
                        <h4 class="fw-bold mb-0 text-warning">{{ $stats['inactive_keys'] }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-pause-circle-fill text-warning fs-5"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Not connected</small>
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
                        <h4 class="fw-bold mb-0 text-info">{{ $stats['last_check'] }}</h4>
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

<!-- API Keys Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">User API Keys</h5>
                <p class="text-muted small mb-0">View exchange API connections for all users</p>
            </div>
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
                        <th class="border-0 py-3 fw-semibold">Balance</th>
                        <th class="border-0 py-3 fw-semibold">Last Synced</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 py-3 fw-semibold text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userApiKeys as $apiKey)
                    <tr class="{{ !$apiKey->is_active ? 'table-secondary bg-opacity-25' : '' }}">
                        <td class="px-4">
                            <div class="fw-semibold">{{ $apiKey->user->name }}</div>
                            <small class="text-muted">{{ $apiKey->user->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-currency-bitcoin me-1"></i>{{ ucfirst($apiKey->exchange) }}
                            </span>
                        </td>
                        <td>
                            <div class="font-monospace small">{{ $apiKey->masked_api_key }}</div>
                            <small class="text-secondary">Created: {{ $apiKey->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">${{ number_format($apiKey->balance, 2) }}</div>
                        </td>
                        <td>
                            @if($apiKey->last_synced_at)
                                <small>{{ $apiKey->last_synced_at->diffForHumans() }}</small>
                            @else
                                <small class="text-muted">Never</small>
                            @endif
                        </td>
                        <td>
                            @if($apiKey->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                    <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-end px-4">
                            <a href="{{ route('admin.users.show', $apiKey->user_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View User
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            No user API keys found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($userApiKeys->hasPages())
    <div class="card-footer bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing {{ $userApiKeys->firstItem() }} to {{ $userApiKeys->lastItem() }} of {{ $userApiKeys->total() }} API keys
            </div>
            {{ $userApiKeys->links() }}
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
@if(!$bybitAccount)
    document.addEventListener('DOMContentLoaded', function() {
        const addModal = new bootstrap.Modal(document.getElementById('addApiModal'));
        @if($errors->any())
            addModal.show();
        @endif
    });
@else
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = new bootstrap.Modal(document.getElementById('editApiModal'));
        @if($errors->any())
            editModal.show();
        @endif
    });
@endif
</script>
@endpush