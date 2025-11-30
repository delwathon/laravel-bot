@extends('layouts.app')

@section('title', 'System Settings - CryptoBot Pro')

@section('page-title', 'System Configuration')

@section('content')
<!-- Warning Banner -->
<div class="alert alert-danger border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3 flex-shrink-0">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-2">Critical System Configuration</h5>
            <p class="mb-0">These settings control core system behavior. Incorrect configuration may affect all users and trading operations. Make changes carefully and test thoroughly.</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.system.update') }}">
    @csrf
    @method('PUT')

    <!-- General System Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-gear-fill me-2"></i>General System Settings
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">System Name</label>
                    <input type="text" class="form-control form-control-lg" name="system_name" value="CryptoBot Pro">
                    <div class="form-text">Application display name</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">System Timezone</label>
                    <select class="form-select form-select-lg" name="timezone">
                        <option value="UTC" selected>UTC</option>
                        <option value="America/New_York">America/New York (EST)</option>
                        <option value="Europe/London">Europe/London (GMT)</option>
                        <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                        <option value="Asia/Singapore">Asia/Singapore (SGT)</option>
                    </select>
                    <div class="form-text">Server timezone for all operations</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Maintenance Mode</label>
                    <select class="form-select form-select-lg" name="maintenance_mode">
                        <option value="0" selected>Disabled</option>
                        <option value="1">Enabled</option>
                    </select>
                    <div class="form-text">Put system in maintenance mode</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Debug Mode</label>
                    <select class="form-select form-select-lg" name="debug_mode">
                        <option value="0" selected>Disabled</option>
                        <option value="1">Enabled</option>
                    </select>
                    <div class="form-text">Enable detailed error logging (production: disable)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trading System Configuration -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-graph-up-arrow me-2"></i>Trading System Configuration
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Global Trading Status</label>
                    <select class="form-select form-select-lg" name="trading_enabled">
                        <option value="1" selected>Enabled</option>
                        <option value="0">Disabled</option>
                    </select>
                    <div class="form-text">Master switch for all trading operations</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Concurrent Trades per User</label>
                    <input type="number" class="form-control form-control-lg" name="max_trades_per_user" value="20" min="1" max="100">
                    <div class="form-text">Maximum simultaneous open positions</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Default Leverage</label>
                    <select class="form-select form-select-lg" name="default_leverage">
                        <option value="1">1x (No Leverage)</option>
                        <option value="2">2x</option>
                        <option value="3" selected>3x</option>
                        <option value="5">5x</option>
                        <option value="10">10x</option>
                    </select>
                    <div class="form-text">Default leverage for new trades</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Leverage Allowed</label>
                    <select class="form-select form-select-lg" name="max_leverage">
                        <option value="5">5x</option>
                        <option value="10" selected>10x</option>
                        <option value="20">20x</option>
                        <option value="50">50x</option>
                        <option value="100">100x</option>
                    </select>
                    <div class="form-text">Maximum leverage users can select</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Min Position Size (USD)</label>
                    <input type="number" class="form-control form-control-lg" name="min_position_size" value="10" min="1">
                    <div class="form-text">Minimum trade size</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Position Size (USD)</label>
                    <input type="number" class="form-control form-control-lg" name="max_position_size" value="50000" min="100">
                    <div class="form-text">Maximum trade size</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trade Execution Timeout (sec)</label>
                    <input type="number" class="form-control form-control-lg" name="trade_timeout" value="30" min="5" max="120">
                    <div class="form-text">Max time for trade execution</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitoring & Safety -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-shield-check me-2"></i>Monitoring & Safety Configuration
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Monitor Refresh Interval (seconds)</label>
                    <input type="number" class="form-control form-control-lg" name="monitor_interval" value="10" min="5" max="60">
                    <div class="form-text">How often to check position status</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Emergency Stop Loss (%)</label>
                    <input type="number" class="form-control form-control-lg" name="emergency_sl" value="10" min="5" max="50" step="0.5">
                    <div class="form-text">Force close if loss exceeds this percentage</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Daily Loss Limit per User (%)</label>
                    <input type="number" class="form-control form-control-lg" name="daily_loss_limit" value="15" min="5" max="50">
                    <div class="form-text">Halt trading if daily loss exceeds</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Drawdown Alert (%)</label>
                    <input type="number" class="form-control form-control-lg" name="max_drawdown_alert" value="20" min="10" max="50">
                    <div class="form-text">Alert when drawdown reaches this level</div>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label fw-semibold">Safety Features</label>
                <hr class="mt-0 mb-3 bg-secondary">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_stop_loss" name="auto_stop_loss" checked>
                            <label class="form-check-label fw-semibold" for="auto_stop_loss">
                                Automatic Stop Loss
                            </label>
                        </div>
                        <small class="text-muted">Automatically set SL for all trades</small>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_take_profit" name="auto_take_profit" checked>
                            <label class="form-check-label fw-semibold" for="auto_take_profit">
                                Automatic Take Profit
                            </label>
                        </div>
                        <small class="text-muted">Automatically set TP for all trades</small>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="trailing_stop" name="trailing_stop">
                            <label class="form-check-label fw-semibold" for="trailing_stop">
                                Trailing Stop Loss
                            </label>
                        </div>
                        <small class="text-muted">Enable dynamic trailing stop loss</small>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="circuit_breaker" name="circuit_breaker" checked>
                            <label class="form-check-label fw-semibold" for="circuit_breaker">
                                Circuit Breaker
                            </label>
                        </div>
                        <small class="text-muted">Halt trading during extreme volatility</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API & Rate Limits -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-cloud me-2"></i>API & Rate Limits
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">API Request Timeout (seconds)</label>
                    <input type="number" class="form-control form-control-lg" name="api_timeout" value="15" min="5" max="60">
                    <div class="form-text">Max wait time for exchange API responses</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max API Retries</label>
                    <input type="number" class="form-control form-control-lg" name="max_retries" value="3" min="1" max="10">
                    <div class="form-text">Retry failed API calls this many times</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Rate Limit</label>
                    <input type="number" class="form-control form-control-lg" name="bybit_rate_limit" value="100" min="10" max="200">
                    <div class="form-text">Maximum requests per minute to Bybit</div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-people-fill me-2"></i>User Management
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Allow New Registrations</label>
                    <select class="form-select form-select-lg" name="allow_registration">
                        <option value="1" selected>Enabled</option>
                        <option value="0">Disabled</option>
                    </select>
                    <div class="form-text">Allow new users to register</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email Verification Required</label>
                    <select class="form-select form-select-lg" name="email_verification">
                        <option value="1" selected>Required</option>
                        <option value="0">Optional</option>
                    </select>
                    <div class="form-text">Force email verification before trading</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Session Timeout (minutes)</label>
                    <input type="number" class="form-control form-control-lg" name="session_timeout" value="120" min="30" max="1440">
                    <div class="form-text">Auto-logout inactive users after</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bell-fill me-2"></i>Notification Settings
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_trade_execution" name="notify_trade_execution" checked>
                        <label class="form-check-label fw-semibold" for="notify_trade_execution">
                            Trade Execution
                        </label>
                    </div>
                    <small class="text-muted">Notify on trade execution</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_tp_sl" name="notify_tp_sl" checked>
                        <label class="form-check-label fw-semibold" for="notify_tp_sl">
                            TP/SL Triggers
                        </label>
                    </div>
                    <small class="text-muted">Notify when TP or SL hit</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_signals" name="notify_signals" checked>
                        <label class="form-check-label fw-semibold" for="notify_signals">
                            New Signals
                        </label>
                    </div>
                    <small class="text-muted">Notify on signal generation</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_errors" name="notify_errors" checked>
                        <label class="form-check-label fw-semibold" for="notify_errors">
                            System Errors
                        </label>
                    </div>
                    <small class="text-muted">Alert on system errors</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_high_risk" name="notify_high_risk" checked>
                        <label class="form-check-label fw-semibold" for="notify_high_risk">
                            High Risk Alerts
                        </label>
                    </div>
                    <small class="text-muted">Alert on high-risk positions</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="daily_summary" name="daily_summary" checked>
                        <label class="form-check-label fw-semibold" for="daily_summary">
                            Daily Summary
                        </label>
                    </div>
                    <small class="text-muted">Send daily performance summary</small>
                </div>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Admin Email</label>
                    <input type="email" class="form-control form-control-lg" name="admin_email" value="admin@cryptobot.com">
                    <div class="form-text">Critical alerts sent to this email</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">SMTP Server</label>
                    <input type="text" class="form-control form-control-lg" name="smtp_server" value="smtp.gmail.com">
                    <div class="form-text">Email server for notifications</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Database & Backup -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-database me-2"></i>Database & Backup
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Automatic Backups</label>
                    <select class="form-select form-select-lg" name="auto_backup">
                        <option value="1" selected>Enabled</option>
                        <option value="0">Disabled</option>
                    </select>
                    <div class="form-text">Automatically backup database</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Backup Frequency</label>
                    <select class="form-select form-select-lg" name="backup_frequency">
                        <option value="hourly">Every Hour</option>
                        <option value="daily" selected>Daily</option>
                        <option value="weekly">Weekly</option>
                    </select>
                    <div class="form-text">How often to backup</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data Retention (days)</label>
                    <input type="number" class="form-control form-control-lg" name="data_retention" value="365" min="30" max="3650">
                    <div class="form-text">Keep historical data for this many days</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Log Retention (days)</label>
                    <input type="number" class="form-control form-control-lg" name="log_retention" value="90" min="7" max="365">
                    <div class="form-text">Keep system logs for this many days</div>
                </div>
            </div>

            <div class="mt-4">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#backupNowModal">
                    <i class="bi bi-download me-2"></i>Backup Database Now
                </button>
                <button type="button" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-clock-history me-2"></i>View Backup History
                </button>
            </div>
        </div>
    </div>

    <!-- Performance & Optimization -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-speedometer2 me-2"></i>Performance & Optimization
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cache Driver</label>
                    <select class="form-select form-select-lg" name="cache_driver">
                        <option value="redis" selected>Redis</option>
                        <option value="memcached">Memcached</option>
                        <option value="file">File</option>
                    </select>
                    <div class="form-text">Cache system for faster performance</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cache TTL (seconds)</label>
                    <input type="number" class="form-control form-control-lg" name="cache_ttl" value="3600" min="60" max="86400">
                    <div class="form-text">How long to cache data</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Queue Driver</label>
                    <select class="form-select form-select-lg" name="queue_driver">
                        <option value="redis" selected>Redis</option>
                        <option value="database">Database</option>
                        <option value="sync">Sync</option>
                    </select>
                    <div class="form-text">Queue system for background jobs</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Queue Workers</label>
                    <input type="number" class="form-control form-control-lg" name="max_workers" value="10" min="1" max="50">
                    <div class="form-text">Maximum concurrent queue workers</div>
                </div>
            </div>

            <div class="mt-4">
                <button type="button" class="btn btn-outline-warning">
                    <i class="bi bi-trash me-2"></i>Clear All Caches
                </button>
                <button type="button" class="btn btn-outline-info ms-2">
                    <i class="bi bi-arrow-clockwise me-2"></i>Restart Queue Workers
                </button>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex gap-3 flex-wrap">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Save All Settings
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="window.location.reload()">
                    <i class="bi bi-x-circle me-2"></i>Cancel Changes
                </button>
                <button type="button" class="btn btn-outline-danger btn-lg ms-auto" data-bs-toggle="modal" data-bs-target="#resetDefaultsModal">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset to Defaults
                </button>
                <button type="button" class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#exportConfigModal">
                    <i class="bi bi-download me-2"></i>Export Config
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Backup Now Modal -->
<div class="modal fade" id="backupNowModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-primary bg-opacity-10">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-download me-2"></i>Create Backup
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Create a backup of the database now?</p>
                <div class="alert alert-info border-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>This will create a full database backup. Depending on size, this may take several minutes.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-download me-2"></i>Create Backup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Defaults Modal -->
<div class="modal fade" id="resetDefaultsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Reset to Defaults
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to reset all settings to factory defaults?</p>
                <div class="alert alert-danger border-0">
                    <strong>Warning:</strong> This will overwrite all current configuration. This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export Config Modal -->
<div class="modal fade" id="exportConfigModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-download me-2"></i>Export Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Export current system configuration to file</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Export Format</label>
                    <select class="form-select">
                        <option value="json">JSON</option>
                        <option value="yaml">YAML</option>
                        <option value="env">.ENV</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-download me-2"></i>Export
                </button>
            </div>
        </div>
    </div>
</div>
@endsection