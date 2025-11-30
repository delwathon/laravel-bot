@extends('layouts.app')

@section('title', 'Manage Exchange - CryptoBot Pro')

@section('page-title', 'Manage Exchange')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($exchangeAccount)
    <!-- Connected Account -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-link-45deg me-2"></i>Connected Exchange
                </h5>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                    <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active
                </span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-bank text-primary fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">Bybit</h4>
                            <p class="text-muted mb-0">Connected on {{ $exchangeAccount->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">API Key</label>
                            <div class="d-flex align-items-center">
                                <code class="bg-body-secondary p-2 rounded flex-grow-1">{{ $exchangeAccount->masked_api_key }}</code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Status</label>
                            <div>
                                @if($exchangeAccount->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Last Synced</label>
                            <div>
                                @if($exchangeAccount->last_synced_at)
                                    {{ $exchangeAccount->last_synced_at->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Balance</label>
                            <div class="fw-bold fs-5">${{ number_format($exchangeAccount->balance, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateApiModal">
                            <i class="bi bi-pencil me-2"></i>Update API Keys
                        </button>
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-2"></i>Sync Balance
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#disconnectModal">
                            <i class="bi bi-x-circle me-2"></i>Disconnect
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-muted text-uppercase small fw-bold mb-1">Account Balance</div>
                    <h4 class="fw-bold mb-0">${{ number_format($exchangeAccount->balance, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-muted text-uppercase small fw-bold mb-1">Active Positions</div>
                    <h4 class="fw-bold mb-0">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-muted text-uppercase small fw-bold mb-1">Total Trades</div>
                    <h4 class="fw-bold mb-0">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-muted text-uppercase small fw-bold mb-1">P&L (All Time)</div>
                    <h4 class="fw-bold mb-0 text-success">+$0.00</h4>
                </div>
            </div>
        </div>
    </div>

@else
    <!-- No Account Connected -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <i class="bi bi-link-45deg text-muted mb-3" style="font-size: 4rem;"></i>
            <h4 class="fw-bold mb-3">No Exchange Connected</h4>
            <p class="text-muted mb-4">Connect your Bybit account to start automated trading</p>
            <a href="{{ route('user.exchanges.connect') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle me-2"></i>Connect Bybit Account
            </a>
        </div>
    </div>
@endif

<!-- Update API Modal -->
@if($exchangeAccount)
<div class="modal fade" id="updateApiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil me-2"></i>Update API Keys
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('user.exchanges.update', $exchangeAccount->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="api_key" class="form-label fw-semibold">API Key</label>
                        <input type="text" class="form-control" id="api_key" name="api_key" 
                               value="{{ $exchangeAccount->api_key }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="api_secret" class="form-label fw-semibold">API Secret</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="api_secret_update" name="api_secret" 
                                   placeholder="Enter new API secret" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('api_secret_update', 'updateSecretIcon')">
                                <i class="bi bi-eye" id="updateSecretIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-info border-0 bg-info bg-opacity-10">
                        <i class="bi bi-info-circle me-2"></i>
                        Updating your API keys will disconnect your current session and reconnect with new credentials.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Update Keys
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Disconnect Modal -->
<div class="modal fade" id="disconnectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Disconnect Bybit Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to disconnect your Bybit account?</p>
                <div class="alert alert-warning border-0 bg-warning bg-opacity-10">
                    <strong>This will:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Stop all automated trading</li>
                        <li>Close any open positions (if applicable)</li>
                        <li>Remove your API credentials from our system</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('user.exchanges.destroy', $exchangeAccount->id) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-2"></i>Disconnect Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    function togglePassword(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>
@endpush