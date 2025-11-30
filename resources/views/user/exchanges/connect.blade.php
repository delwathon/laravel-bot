@extends('layouts.app')

@section('title', 'Connect Bybit - CryptoBot Pro')

@section('page-title', 'Connect Your Bybit Account')

@section('content')

<!-- Info Banner -->
<div class="card border-0 shadow-sm mb-4 bg-primary bg-opacity-10">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold text-primary mb-2">
                    <i class="bi bi-info-circle-fill me-2"></i>Connect Your Bybit Account
                </h5>
                <p class="mb-0 text-muted">
                    Connect your Bybit account to start automated trading. You can only connect one Bybit account per CryptoBot Pro account.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-primary bg-opacity-25 text-primary px-3 py-2 fs-6">
                    <i class="bi bi-shield-check me-1"></i>Secure Connection
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Connection Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-link-45deg me-2"></i>API Credentials
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('user.exchanges.store') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="api_key" class="form-label fw-semibold">
                            API Key
                            <span class="text-danger">*</span>
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
                        <div class="form-text small">
                            <i class="bi bi-info-circle me-1"></i>
                            Your API key from Bybit account settings
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="api_secret" class="form-label fw-semibold">
                            API Secret
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control form-control-lg @error('api_secret') is-invalid @enderror" 
                                   id="api_secret" 
                                   name="api_secret"
                                   placeholder="Enter your Bybit API Secret" 
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('api_secret', 'secretIcon')">
                                <i class="bi bi-eye" id="secretIcon"></i>
                            </button>
                        </div>
                        @error('api_secret')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text small">
                            <i class="bi bi-info-circle me-1"></i>
                            Your API secret will be encrypted and stored securely
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle fs-5 me-3"></i>
                            <div>
                                <strong>Important Security Notes:</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Never share your API credentials with anyone</li>
                                    <li>Enable IP whitelist on Bybit for added security</li>
                                    <li>We only need trading permissions (no withdrawal rights)</li>
                                    <li>You can disconnect your account at any time</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-link-45deg me-2"></i>Connect Bybit Account
                        </button>
                        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Instructions Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 p-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-question-circle me-2"></i>How to Get API Keys
                </h5>
            </div>
            <div class="card-body p-4">
                <ol class="mb-0 ps-3">
                    <li class="mb-3">
                        <strong>Login to Bybit</strong>
                        <p class="small text-muted mb-0">Go to <a href="https://www.bybit.com" target="_blank">bybit.com</a> and login to your account</p>
                    </li>
                    <li class="mb-3">
                        <strong>Navigate to API Settings</strong>
                        <p class="small text-muted mb-0">Go to Account → API Management</p>
                    </li>
                    <li class="mb-3">
                        <strong>Create New API Key</strong>
                        <p class="small text-muted mb-0">Click "Create New Key" and select "System-generated API Keys"</p>
                    </li>
                    <li class="mb-3">
                        <strong>Set Permissions</strong>
                        <p class="small text-muted mb-0">Enable: Read, Trading. Disable: Withdrawal</p>
                    </li>
                    <li class="mb-0">
                        <strong>Copy Credentials</strong>
                        <p class="small text-muted mb-0">Save your API Key and Secret securely</p>
                    </li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-shield-check me-2"></i>Security Features
                </h6>
                <div class="d-flex align-items-start mb-3">
                    <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                    <div class="small">
                        <strong>Encrypted Storage</strong>
                        <p class="text-muted mb-0">API secrets are encrypted using AES-256</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                    <div class="small">
                        <strong>No Withdrawal Access</strong>
                        <p class="text-muted mb-0">We only request trading permissions</p>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                    <div class="small">
                        <strong>Easy Disconnect</strong>
                        <p class="text-muted mb-0">Revoke access anytime from your dashboard</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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