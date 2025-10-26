@extends('layouts.app')

@section('title', 'Edit User - CryptoBot Pro')

@section('page-title', 'Edit User')

@section('content')
<!-- Back Button -->
<div class="mb-4">
    <a href="{{ route('admin.users.show', 1) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to User Profile
    </a>
</div>

<!-- Edit Notice -->
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3 flex-shrink-0">
            <i class="bi bi-info-circle-fill text-info fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-2">Editing User Account</h5>
            <p class="mb-0">You are editing John Doe's account. Changes will take effect immediately and may impact active trading.</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.update', 1) }}">
    @csrf
    @method('PUT')

    <!-- Basic Information -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-person-circle me-2"></i>Basic Information
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Full Name
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-lg" name="name" value="John Doe" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Email Address
                        <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control form-control-lg" name="email" value="john.doe@example.com" required>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="email_verified" name="email_verified" checked>
                        <label class="form-check-label" for="email_verified">
                            Email Verified
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="tel" class="form-control form-control-lg" name="phone" value="+1 (555) 123-4567">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Country</label>
                    <select class="form-select form-select-lg" name="country">
                        <option value="US" selected>United States</option>
                        <option value="UK">United Kingdom</option>
                        <option value="CA">Canada</option>
                        <option value="AU">Australia</option>
                        <option value="SG">Singapore</option>
                        <option value="JP">Japan</option>
                        <option value="DE">Germany</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Status -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-shield-check me-2"></i>Account Status & Permissions
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Account Status</label>
                    <select class="form-select form-select-lg" name="status">
                        <option value="active" selected>Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="form-text">User's current account status</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">User Role</label>
                    <select class="form-select form-select-lg" name="role">
                        <option value="user" selected>Regular User</option>
                        <option value="admin">Administrator</option>
                    </select>
                    <div class="form-text">Admin users have full system access</div>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label fw-semibold">Permissions</label>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="can_trade" name="can_trade" checked>
                            <label class="form-check-label" for="can_trade">
                                Can Trade
                            </label>
                        </div>
                        <small class="text-muted">Allow executing trades</small>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="can_connect_exchanges" name="can_connect_exchanges" checked>
                            <label class="form-check-label" for="can_connect_exchanges">
                                Can Connect Exchanges
                            </label>
                        </div>
                        <small class="text-muted">Allow API connections</small>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="receive_signals" name="receive_signals" checked>
                            <label class="form-check-label" for="receive_signals">
                                Receive Trading Signals
                            </label>
                        </div>
                        <small class="text-muted">Get automated signals</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trading Configuration -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-graph-up-arrow me-2"></i>Trading Configuration
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Concurrent Positions</label>
                    <input type="number" class="form-control form-control-lg" name="max_positions" value="20" min="1" max="100">
                    <div class="form-text">Maximum open trades allowed</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Leverage</label>
                    <select class="form-select form-select-lg" name="max_leverage">
                        <option value="3">3x</option>
                        <option value="5">5x</option>
                        <option value="10" selected>10x</option>
                        <option value="20">20x</option>
                        <option value="50">50x</option>
                        <option value="100">100x</option>
                    </select>
                    <div class="form-text">Maximum allowed leverage</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Position Size Limit (%)</label>
                    <input type="number" class="form-control form-control-lg" name="position_size_limit" value="10" min="1" max="100">
                    <div class="form-text">Max % of balance per trade</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Daily Loss Limit (%)</label>
                    <input type="number" class="form-control form-control-lg" name="daily_loss_limit" value="15" min="5" max="50" step="0.5">
                    <div class="form-text">Halt trading if daily loss exceeds</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Risk Level</label>
                    <select class="form-select form-select-lg" name="risk_level">
                        <option value="conservative">Conservative</option>
                        <option value="moderate" selected>Moderate</option>
                        <option value="aggressive">Aggressive</option>
                    </select>
                    <div class="form-text">User's risk tolerance</div>
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
                        <label class="form-check-label" for="notify_trades">
                            Trade Notifications
                        </label>
                    </div>
                    <small class="text-muted">Notify on trade execution</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_signals" name="notify_signals" checked>
                        <label class="form-check-label" for="notify_signals">
                            Signal Alerts
                        </label>
                    </div>
                    <small class="text-muted">New trading signals</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_tp_sl" name="notify_tp_sl" checked>
                        <label class="form-check-label" for="notify_tp_sl">
                            TP/SL Triggers
                        </label>
                    </div>
                    <small class="text-muted">When TP or SL hit</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_daily_summary" name="notify_daily_summary" checked>
                        <label class="form-check-label" for="notify_daily_summary">
                            Daily Summary
                        </label>
                    </div>
                    <small class="text-muted">Daily performance report</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_alerts" name="notify_alerts" checked>
                        <label class="form-check-label" for="notify_alerts">
                            Risk Alerts
                        </label>
                    </div>
                    <small class="text-muted">High risk warnings</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_system" name="notify_system">
                        <label class="form-check-label" for="notify_system">
                            System Updates
                        </label>
                    </div>
                    <small class="text-muted">Platform announcements</small>
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
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Two-Factor Authentication</label>
                    <select class="form-select form-select-lg" name="two_factor">
                        <option value="0">Disabled</option>
                        <option value="1" selected>Enabled</option>
                    </select>
                    <div class="form-text">Require 2FA for login</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Session Timeout (minutes)</label>
                    <input type="number" class="form-control form-control-lg" name="session_timeout" value="120" min="30" max="1440">
                    <div class="form-text">Auto-logout after inactivity</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Reset Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg" name="new_password" id="new_password" placeholder="Leave blank to keep current password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password', 'passwordIcon')">
                            <i class="bi bi-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    <div class="form-text">Only fill this if you want to reset the user's password</div>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="ip_whitelist" name="ip_whitelist">
                        <label class="form-check-label" for="ip_whitelist">
                            Enable IP Whitelist
                        </label>
                    </div>
                    <small class="text-muted">Restrict login to specific IPs</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="require_email_change_verification" name="require_email_change_verification" checked>
                        <label class="form-check-label" for="require_email_change_verification">
                            Verify Email Changes
                        </label>
                    </div>
                    <small class="text-muted">Require verification for email updates</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes & Comments -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-journal-text me-2"></i>Admin Notes
            </h5>
        </div>
        <div class="card-body p-4">
            <label class="form-label fw-semibold">Internal Notes (Private)</label>
            <textarea class="form-control" name="admin_notes" rows="4" placeholder="Add notes about this user (visible only to admins)...">User joined through referral program. High volume trader with consistent performance.</textarea>
            <div class="form-text">These notes are only visible to administrators</div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card border-0 shadow-sm border-danger mb-4">
        <div class="card-header bg-danger bg-opacity-10 border-0 p-4">
            <h5 class="fw-bold mb-0 text-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Danger Zone
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">Suspend Account</h6>
                    <p class="small text-muted mb-3">Temporarily disable this user's account and close all positions.</p>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#suspendModal">
                        <i class="bi bi-ban me-2"></i>Suspend User
                    </button>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">Delete Account</h6>
                    <p class="small text-muted mb-3">Permanently delete this user and all associated data. This cannot be undone.</p>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash me-2"></i>Delete User
                    </button>
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
                <a href="{{ route('admin.users.show', 1) }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
                <button type="button" class="btn btn-outline-primary btn-lg ms-auto" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-warning bg-opacity-10">
                <h5 class="modal-title fw-bold text-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Suspend Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to suspend this account?</p>
                <div class="alert alert-warning border-0 mb-3">
                    <strong>This will:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Log out the user immediately</li>
                        <li>Close all active positions</li>
                        <li>Stop all trading activities</li>
                        <li>Disable API connections</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Suspension Reason</label>
                    <textarea class="form-control" rows="3" placeholder="Enter reason for suspension..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning">
                    <i class="bi bi-ban me-2"></i>Suspend Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to permanently delete this account?</p>
                <div class="alert alert-danger border-0 mb-3">
                    <strong>WARNING:</strong> This action is irreversible!
                    <ul class="mb-0 mt-2 small">
                        <li>All user data will be permanently deleted</li>
                        <li>All trade history will be removed</li>
                        <li>All exchange connections will be terminated</li>
                        <li>This cannot be undone</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type "DELETE" to confirm</label>
                    <input type="text" class="form-control" id="deleteConfirmInput" placeholder="Type DELETE to confirm">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn" disabled>
                    <i class="bi bi-trash me-2"></i>Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
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
            deleteButton.disabled = this.value !== 'DELETE';
        });
    }
</script>
@endpush