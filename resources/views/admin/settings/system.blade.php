@extends('layouts.app')

@section('title', 'System Settings - CryptoBot Pro')

@section('page-title', 'System Configuration')

@section('content')

@php
    // System Settings
    $systemName = $settings['system_name'] ?? 'CryptoBot Pro';
    $timezone = $settings['system_timezone'] ?? 'UTC';
    $maintenanceMode = $settings['system_maintenance_mode'] ?? false;
    $debugMode = $settings['system_debug_mode'] ?? false;
    $allowRegistration = $settings['system_allow_registration'] ?? true;
    $emailVerification = $settings['system_email_verification'] ?? true;
    $sessionTimeout = $settings['system_session_timeout'] ?? 120;
    
    // Trading Settings
    $tradingEnabled = $settings['trading_enabled'] ?? true;
    $maxTradesPerUser = $settings['trading_max_trades_per_user'] ?? 20;
    $defaultLeverage = $settings['trading_default_leverage'] ?? 3;
    $maxLeverage = $settings['trading_max_leverage'] ?? 10;
    $minPositionSize = $settings['trading_min_position_size'] ?? 10;
    $maxPositionSize = $settings['trading_max_position_size'] ?? 50000;
    $tradeTimeout = $settings['trading_execution_timeout'] ?? 30;
    
    // Monitoring Settings
    $monitorInterval = $settings['monitor_interval'] ?? 10;
    $emergencySl = $settings['monitor_emergency_sl'] ?? 10;
    $dailyLossLimit = $settings['monitor_daily_loss_limit'] ?? 15;
    $maxDrawdown = $settings['monitor_max_drawdown'] ?? 25;
    $autoStopLoss = $settings['monitor_auto_stop_loss'] ?? true;
    $autoTakeProfit = $settings['monitor_auto_take_profit'] ?? true;
    $trailingStop = $settings['monitor_trailing_stop'] ?? false;
    $circuitBreaker = $settings['monitor_circuit_breaker'] ?? true;
    
    // API Settings
    $apiTimeout = $settings['api_timeout'] ?? 15;
    $maxRetries = $settings['api_max_retries'] ?? 3;
    $bybitRateLimit = $settings['api_bybit_rate_limit'] ?? 100;
    
    // Notification Settings
    $notifyTradeExecution = $settings['notifications_trade_execution'] ?? true;
    $notifyTpSl = $settings['notifications_tp_sl'] ?? true;
    $notifySignals = $settings['notifications_signals'] ?? true;
    $notifyErrors = $settings['notifications_errors'] ?? true;
    $notifyHighRisk = $settings['notifications_high_risk'] ?? true;
    $dailySummary = $settings['notifications_daily_summary'] ?? true;
    $adminEmail = $settings['notifications_admin_email'] ?? 'admin@cryptobot.com';
    $smtpServer = $settings['notifications_smtp_server'] ?? 'smtp.gmail.com';
    
    // Backup Settings
    $autoBackup = $settings['backup_auto_backup'] ?? true;
    $backupFrequency = $settings['backup_frequency'] ?? 'daily';
    $backupRetention = $settings['backup_retention_days'] ?? 30;
    $dataRetention = $settings['backup_data_retention'] ?? 365;
    $logRetention = $settings['backup_log_retention'] ?? 90;
    
    // Performance Settings
    $cacheDriver = $settings['cache_driver'] ?? 'redis';
    $cacheTtl = $settings['cache_ttl'] ?? 3600;
    $queueDriver = $settings['queue_driver'] ?? 'redis';
    $maxWorkers = $settings['queue_max_workers'] ?? 10;
@endphp

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
                    <input type="text" class="form-control form-control-lg" name="system_name" value="{{ $systemName }}">
                    <div class="form-text">Application display name</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">System Timezone</label>
                    <select class="form-select form-select-lg" name="timezone">
                        <option value="UTC" {{ $timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="America/New_York" {{ $timezone == 'America/New_York' ? 'selected' : '' }}>America/New York (EST)</option>
                        <option value="Europe/London" {{ $timezone == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                        <option value="Asia/Tokyo" {{ $timezone == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)</option>
                        <option value="Asia/Singapore" {{ $timezone == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (SGT)</option>
                    </select>
                    <div class="form-text">Server timezone for all operations</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Maintenance Mode</label>
                    <select class="form-select form-select-lg" name="maintenance_mode">
                        <option value="0" {{ !$maintenanceMode ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ $maintenanceMode ? 'selected' : '' }}>Enabled</option>
                    </select>
                    <div class="form-text">Put system in maintenance mode</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Debug Mode</label>
                    <select class="form-select form-select-lg" name="debug_mode">
                        <option value="0" {{ !$debugMode ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ $debugMode ? 'selected' : '' }}>Enabled</option>
                    </select>
                    <div class="form-text">Enable detailed error logging (production: disable)</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Allow User Registration</label>
                    <select class="form-select form-select-lg" name="allow_registration">
                        <option value="1" {{ $allowRegistration ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !$allowRegistration ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <div class="form-text">Allow new users to register</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Verification Required</label>
                    <select class="form-select form-select-lg" name="email_verification">
                        <option value="1" {{ $emailVerification ? 'selected' : '' }}>Required</option>
                        <option value="0" {{ !$emailVerification ? 'selected' : '' }}>Not Required</option>
                    </select>
                    <div class="form-text">Require email verification for new accounts</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Session Timeout (minutes)</label>
                    <input type="number" class="form-control form-control-lg" name="session_timeout" value="{{ $sessionTimeout }}" min="30" max="1440">
                    <div class="form-text">Auto-logout after inactivity</div>
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
                        <option value="1" {{ $tradingEnabled ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !$tradingEnabled ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <div class="form-text">Master switch for all trading operations</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Concurrent Trades per User</label>
                    <input type="number" class="form-control form-control-lg" name="max_trades_per_user" value="{{ $maxTradesPerUser }}" min="1" max="100">
                    <div class="form-text">Maximum simultaneous open positions</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Default Leverage</label>
                    <select class="form-select form-select-lg" name="default_leverage">
                        <option value="1" {{ $defaultLeverage == 1 ? 'selected' : '' }}>1x (No Leverage)</option>
                        <option value="2" {{ $defaultLeverage == 2 ? 'selected' : '' }}>2x</option>
                        <option value="3" {{ $defaultLeverage == 3 ? 'selected' : '' }}>3x</option>
                        <option value="5" {{ $defaultLeverage == 5 ? 'selected' : '' }}>5x</option>
                        <option value="10" {{ $defaultLeverage == 10 ? 'selected' : '' }}>10x</option>
                    </select>
                    <div class="form-text">Default leverage for new trades</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Leverage Allowed</label>
                    <select class="form-select form-select-lg" name="max_leverage">
                        <option value="5" {{ $maxLeverage == 5 ? 'selected' : '' }}>5x</option>
                        <option value="10" {{ $maxLeverage == 10 ? 'selected' : '' }}>10x</option>
                        <option value="20" {{ $maxLeverage == 20 ? 'selected' : '' }}>20x</option>
                        <option value="50" {{ $maxLeverage == 50 ? 'selected' : '' }}>50x</option>
                        <option value="100" {{ $maxLeverage == 100 ? 'selected' : '' }}>100x</option>
                    </select>
                    <div class="form-text">Maximum leverage users can select</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Min Position Size (USD)</label>
                    <input type="number" class="form-control form-control-lg" name="min_position_size" value="{{ $minPositionSize }}" min="1">
                    <div class="form-text">Minimum trade size</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Position Size (USD)</label>
                    <input type="number" class="form-control form-control-lg" name="max_position_size" value="{{ $maxPositionSize }}" min="100">
                    <div class="form-text">Maximum trade size</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trade Execution Timeout (sec)</label>
                    <input type="number" class="form-control form-control-lg" name="trade_timeout" value="{{ $tradeTimeout }}" min="5" max="120">
                    <div class="form-text">Max time to execute trade</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Monitoring & Management -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-shield-exclamation me-2"></i>Risk Monitoring & Management
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Monitor Refresh Interval (sec)</label>
                    <input type="number" class="form-control form-control-lg" name="monitor_interval" value="{{ $monitorInterval }}" min="5" max="60">
                    <div class="form-text">How often to check positions</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Emergency Stop Loss (%)</label>
                    <input type="number" class="form-control form-control-lg" name="emergency_sl" value="{{ $emergencySl }}" min="5" max="50" step="0.5">
                    <div class="form-text">Auto-close if loss exceeds</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Daily Loss Limit (%)</label>
                    <input type="number" class="form-control form-control-lg" name="daily_loss_limit" value="{{ $dailyLossLimit }}" min="5" max="50">
                    <div class="form-text">Max daily loss per user</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Drawdown Alert (%)</label>
                    <input type="number" class="form-control form-control-lg" name="max_drawdown_alert" value="{{ $maxDrawdown }}" min="10" max="50">
                    <div class="form-text">Alert when drawdown exceeds</div>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto_stop_loss" name="auto_stop_loss" {{ $autoStopLoss ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="auto_stop_loss">
                            Auto Stop Loss Protection
                        </label>
                    </div>
                    <small class="text-muted">Automatically set stop loss on all trades</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto_take_profit" name="auto_take_profit" {{ $autoTakeProfit ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="auto_take_profit">
                            Auto Take Profit
                        </label>
                    </div>
                    <small class="text-muted">Automatically set take profit targets</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="trailing_stop" name="trailing_stop" {{ $trailingStop ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="trailing_stop">
                            Trailing Stop Loss
                        </label>
                    </div>
                    <small class="text-muted">Enable trailing stop loss feature</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="circuit_breaker" name="circuit_breaker" {{ $circuitBreaker ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="circuit_breaker">
                            Circuit Breaker Protection
                        </label>
                    </div>
                    <small class="text-muted">Halt trading during extreme volatility</small>
                </div>
            </div>
        </div>
    </div>

    <!-- API & Exchange Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-plug me-2"></i>API & Exchange Settings
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">API Timeout (seconds)</label>
                    <input type="number" class="form-control form-control-lg" name="api_timeout" value="{{ $apiTimeout }}" min="5" max="60">
                    <div class="form-text">Maximum API request wait time</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Retry Attempts</label>
                    <input type="number" class="form-control form-control-lg" name="max_retries" value="{{ $maxRetries }}" min="1" max="10">
                    <div class="form-text">Retry failed API requests</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Bybit Rate Limit (requests/min)</label>
                    <input type="number" class="form-control form-control-lg" name="bybit_rate_limit" value="{{ $bybitRateLimit }}" min="10" max="200">
                    <div class="form-text">API rate limiting threshold</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Preferences -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bell me-2"></i>Notification Preferences
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_trade_execution" name="notify_trade_execution" {{ $notifyTradeExecution ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notify_trade_execution">
                            Trade Execution Notifications
                        </label>
                    </div>
                    <small class="text-muted">Notify when trades are executed</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_tp_sl" name="notify_tp_sl" {{ $notifyTpSl ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notify_tp_sl">
                            TP/SL Trigger Notifications
                        </label>
                    </div>
                    <small class="text-muted">Notify when take profit or stop loss is hit</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_signals" name="notify_signals" {{ $notifySignals ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notify_signals">
                            Signal Generation Notifications
                        </label>
                    </div>
                    <small class="text-muted">Notify when new signals are generated</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_errors" name="notify_errors" {{ $notifyErrors ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notify_errors">
                            Error & System Notifications
                        </label>
                    </div>
                    <small class="text-muted">Notify about system errors and issues</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_high_risk" name="notify_high_risk" {{ $notifyHighRisk ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notify_high_risk">
                            High Risk Event Notifications
                        </label>
                    </div>
                    <small class="text-muted">Notify about high-risk trading situations</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="daily_summary" name="daily_summary" {{ $dailySummary ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="daily_summary">
                            Daily Performance Summary
                        </label>
                    </div>
                    <small class="text-muted">Send daily trading summary reports</small>
                </div>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Admin Notification Email</label>
                    <input type="email" class="form-control form-control-lg" name="admin_email" value="{{ $adminEmail }}">
                    <div class="form-text">Email address for admin notifications</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">SMTP Server</label>
                    <input type="text" class="form-control form-control-lg" name="smtp_server" value="{{ $smtpServer }}">
                    <div class="form-text">SMTP server for email notifications</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup & Data Retention -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-archive me-2"></i>Backup & Data Retention
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Automatic Backups</label>
                    <select class="form-select form-select-lg" name="auto_backup">
                        <option value="1" {{ $autoBackup ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !$autoBackup ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <div class="form-text">Enable automated backups</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Backup Frequency</label>
                    <select class="form-select form-select-lg" name="backup_frequency">
                        <option value="hourly" {{ $backupFrequency == 'hourly' ? 'selected' : '' }}>Hourly</option>
                        <option value="daily" {{ $backupFrequency == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $backupFrequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $backupFrequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                    <div class="form-text">How often to backup</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Backup Retention (days)</label>
                    <input type="number" class="form-control form-control-lg" name="backup_retention" value="{{ $backupRetention }}" min="7" max="90">
                    <div class="form-text">How long to keep backups</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data Retention (days)</label>
                    <input type="number" class="form-control form-control-lg" name="data_retention" value="{{ $dataRetention }}" min="30" max="3650">
                    <div class="form-text">Keep trading data for</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Log Retention (days)</label>
                    <input type="number" class="form-control form-control-lg" name="log_retention" value="{{ $logRetention }}" min="7" max="365">
                    <div class="form-text">How long to keep system logs</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance & Optimization -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-speedometer me-2"></i>Performance & Optimization
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cache Driver</label>
                    <select class="form-select form-select-lg" name="cache_driver">
                        <option value="redis" {{ $cacheDriver == 'redis' ? 'selected' : '' }}>Redis</option>
                        <option value="memcached" {{ $cacheDriver == 'memcached' ? 'selected' : '' }}>Memcached</option>
                        <option value="file" {{ $cacheDriver == 'file' ? 'selected' : '' }}>File</option>
                        <option value="database" {{ $cacheDriver == 'database' ? 'selected' : '' }}>Database</option>
                    </select>
                    <div class="form-text">Caching backend</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cache TTL (seconds)</label>
                    <input type="number" class="form-control form-control-lg" name="cache_ttl" value="{{ $cacheTtl }}" min="60" max="86400">
                    <div class="form-text">Cache lifetime</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Queue Driver</label>
                    <select class="form-select form-select-lg" name="queue_driver">
                        <option value="redis" {{ $queueDriver == 'redis' ? 'selected' : '' }}>Redis</option>
                        <option value="database" {{ $queueDriver == 'database' ? 'selected' : '' }}>Database</option>
                        <option value="sync" {{ $queueDriver == 'sync' ? 'selected' : '' }}>Sync</option>
                    </select>
                    <div class="form-text">Job queue backend</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Queue Workers</label>
                    <input type="number" class="form-control form-control-lg" name="max_workers" value="{{ $maxWorkers }}" min="1" max="50">
                    <div class="form-text">Concurrent job processors</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Save Configuration
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="window.location.reload()">
                    <i class="bi bi-x-circle me-2"></i>Cancel Changes
                </button>
                <button type="button" class="btn btn-outline-warning btn-lg ms-auto">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset to Defaults
                </button>
            </div>
        </div>
    </div>
</form>

@endsection