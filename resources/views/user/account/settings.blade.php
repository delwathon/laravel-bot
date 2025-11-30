@extends('layouts.app')

@section('title', 'Account Settings - CryptoBot Pro')

@section('page-title', 'Account Settings')

@section('content')
<!-- Profile Header -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-4">
                        <i class="bi bi-person-fill text-primary fs-1"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">{{ auth()->user()->name }}</h3>
                        <p class="text-muted mb-2">{{ auth()->user()->email }}</p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active Account
                            </span>
                            <span class="badge bg-info bg-opacity-10 text-info">
                                Member since {{ auth()->user()->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('user.account.update') }}">
    @csrf
    @method('PUT')

    <!-- Personal Information -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-person-circle me-2"></i>Personal Information
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="first_name" class="form-label fw-semibold">
                        First Name
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-lg" id="first_name" name="first_name" 
                           value="{{ auth()->user()->first_name }}" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label fw-semibold">
                        Last Name
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-lg" id="last_name" name="last_name" 
                           value="{{ auth()->user()->last_name }}" required>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">
                        Email Address
                        <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" 
                           value="{{ auth()->user()->email }}" required>
                    <div class="form-text small">
                        @if(auth()->user()->email_verified_at)
                            <i class="bi bi-check-circle-fill text-success me-1"></i>Verified
                        @else
                            <i class="bi bi-x-circle-fill text-danger me-1"></i>Not verified
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                    <input type="tel" class="form-control form-control-lg" id="phone" name="phone" 
                           placeholder="+1 (555) 123-4567">
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-shield-lock me-2"></i>Security Settings
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label fw-semibold">Change Password</label>
                    <p class="text-muted small">Leave blank to keep your current password</p>
                </div>
                <div class="col-md-6">
                    <label for="current_password" class="form-label fw-semibold">Current Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password', 'currentIcon')">
                            <i class="bi bi-eye" id="currentIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <label for="new_password" class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password', 'newIcon')">
                            <i class="bi bi-eye" id="newIcon"></i>
                        </button>
                    </div>
                    <div class="form-text small">Minimum 8 characters</div>
                </div>
                <div class="col-md-6">
                    <label for="new_password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation', 'confirmIcon')">
                            <i class="bi bi-eye" id="confirmIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="two_factor_enabled" name="two_factor_enabled">
                        <label class="form-check-label fw-semibold" for="two_factor_enabled">
                            Enable Two-Factor Authentication
                        </label>
                    </div>
                    <small class="text-muted">Add an extra layer of security to your account</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Trading Preferences -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-graph-up-arrow me-2"></i>Trading Preferences
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <label for="default_leverage" class="form-label fw-semibold">Default Leverage</label>
                    <select class="form-select form-select-lg" id="default_leverage" name="default_leverage">
                        <option value="1">1x (No Leverage)</option>
                        <option value="2">2x</option>
                        <option value="3" selected>3x</option>
                        <option value="5">5x</option>
                        <option value="10">10x</option>
                    </select>
                    <div class="form-text small">Leverage used for new positions</div>
                </div>
                <div class="col-md-4">
                    <label for="position_size" class="form-label fw-semibold">Position Size (%)</label>
                    <input type="number" class="form-control form-control-lg" id="position_size" name="position_size" 
                           value="5" min="1" max="100">
                    <div class="form-text small">Percentage of balance per trade</div>
                </div>
                <div class="col-md-4">
                    <label for="risk_level" class="form-label fw-semibold">Risk Level</label>
                    <select class="form-select form-select-lg" id="risk_level" name="risk_level">
                        <option value="conservative">Conservative</option>
                        <option value="moderate" selected>Moderate</option>
                        <option value="aggressive">Aggressive</option>
                    </select>
                    <div class="form-text small">Your risk tolerance</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label for="stop_loss_percent" class="form-label fw-semibold">Default Stop Loss (%)</label>
                    <input type="number" class="form-control form-control-lg" id="stop_loss_percent" name="stop_loss_percent" 
                           value="2" min="0.5" max="10" step="0.5">
                    <div class="form-text small">Default stop loss percentage</div>
                </div>
                <div class="col-md-6">
                    <label for="take_profit_percent" class="form-label fw-semibold">Default Take Profit (%)</label>
                    <input type="number" class="form-control form-control-lg" id="take_profit_percent" name="take_profit_percent" 
                           value="4" min="1" max="50" step="0.5">
                    <div class="form-text small">Default take profit percentage</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bell-fill me-2"></i>Notification Preferences
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_trades" name="notify_trades" checked>
                        <label class="form-check-label fw-semibold" for="notify_trades">
                            Trade Execution
                        </label>
                    </div>
                    <small class="text-muted">When trades are executed</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_signals" name="notify_signals" checked>
                        <label class="form-check-label fw-semibold" for="notify_signals">
                            New Signals
                        </label>
                    </div>
                    <small class="text-muted">When new trading signals appear</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_tp_sl" name="notify_tp_sl" checked>
                        <label class="form-check-label fw-semibold" for="notify_tp_sl">
                            TP/SL Triggers
                        </label>
                    </div>
                    <small class="text-muted">When TP or SL is hit</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_daily_summary" name="notify_daily_summary" checked>
                        <label class="form-check-label fw-semibold" for="notify_daily_summary">
                            Daily Summary
                        </label>
                    </div>
                    <small class="text-muted">Daily performance report</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_risk_alerts" name="notify_risk_alerts" checked>
                        <label class="form-check-label fw-semibold" for="notify_risk_alerts">
                            Risk Alerts
                        </label>
                    </div>
                    <small class="text-muted">High risk warnings</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_system" name="notify_system">
                        <label class="form-check-label fw-semibold" for="notify_system">
                            System Updates
                        </label>
                    </div>
                    <small class="text-muted">Platform announcements</small>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Notification Method</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_email" name="notify_email" checked>
                        <label class="form-check-label" for="notify_email">
                            Email Notifications
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_push" name="notify_push">
                        <label class="form-check-label" for="notify_push">
                            Push Notifications
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Management -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-key me-2"></i>Connected Exchanges
                </h5>
                <a href="{{ route('user.exchanges.manage') }}" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Exchange
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <!-- Bybit Connection -->
                <div class="col-md-6">
                    <div class="card border-0 bg-body-secondary">
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
                            <div class="small mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Connected</span>
                                    <span class="fw-semibold">Jan 15, 2024</span>
                                </div>
                            </div>
                            <a href="{{ route('user.exchanges.manage') }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-gear me-1"></i> Manage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Statistics -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bar-chart-line me-2"></i>Account Statistics
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-muted text-uppercase small mb-1">Total Trades</div>
                    <div class="fw-bold fs-4">342</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted text-uppercase small mb-1">Win Rate</div>
                    <div class="fw-bold fs-4 text-success">68.4%</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted text-uppercase small mb-1">Total P&L</div>
                    <div class="fw-bold fs-4 text-success">+$4,523</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted text-uppercase small mb-1">Active Positions</div>
                    <div class="fw-bold fs-4 text-info">8</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex gap-3 flex-wrap">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Save Changes
                </button>
                <button type="reset" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                </button>
                <button type="button" class="btn btn-outline-danger btn-lg ms-auto" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                    <i class="bi bi-trash me-2"></i>Delete Account
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to permanently delete your account?</p>
                <div class="alert alert-danger border-0 mb-3">
                    <strong>WARNING:</strong> This action is irreversible!
                    <ul class="mb-0 mt-2 small">
                        <li>All your trading data will be deleted</li>
                        <li>All active positions will be closed</li>
                        <li>All exchange connections will be terminated</li>
                        <li>You will not be able to recover your account</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type "DELETE MY ACCOUNT" to confirm</label>
                    <input type="text" class="form-control" id="deleteConfirmInput" placeholder="Type DELETE MY ACCOUNT">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn" disabled>
                    <i class="bi bi-trash me-2"></i>Delete My Account
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Password Toggle
    function togglePassword(fieldId, iconId) {
        const passwordInput = document.getElementById(fieldId);
        const passwordIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.className = 'bi bi-eye-slash';
        } else {
            passwordInput.type = 'password';
            passwordIcon.className = 'bi bi-eye';
        }
    }

    // Delete confirmation
    const deleteInput = document.getElementById('deleteConfirmInput');
    const deleteButton = document.getElementById('deleteConfirmBtn');
    
    if (deleteInput && deleteButton) {
        deleteInput.addEventListener('input', function() {
            deleteButton.disabled = this.value !== 'DELETE MY ACCOUNT';
        });
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('new_password_confirmation').value;
        
        if (newPassword && newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match!');
            return false;
        }
    });
</script>
@endpush