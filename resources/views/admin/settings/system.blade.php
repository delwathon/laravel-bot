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
    $executionTimeout = $settings['trading_execution_timeout'] ?? 30;
    
    // Monitoring Settings
    $monitorInterval = $settings['monitor_interval'] ?? 10;
    $emergencySl = $settings['monitor_emergency_sl'] ?? 10;
    $dailyLossLimit = $settings['monitor_daily_loss_limit'] ?? 15;
    $maxDrawdown = $settings['monitor_max_drawdown'] ?? 25;
    $autoStopLoss = $settings['monitor_auto_stop_loss'] ?? true;
    $autoTakeProfit = $settings['monitor_auto_take_profit'] ?? true;
    $trailingStop = $settings['monitor_trailing_stop'] ?? false;
    $circuitBreaker = $settings['monitor_circuit_breaker'] ?? true;
    $enableProfitMilestones = $settings['enable_profit_milestones'] ?? true;
    
    // API Settings
    $apiTimeout = $settings['api_timeout'] ?? 15;
    $maxRetries = $settings['api_max_retries'] ?? 3;
    $bybitRateLimit = $settings['api_bybit_rate_limit'] ?? 100;
    
    // Notification Settings
    $notificationsEnabled = $settings['notifications_enabled'] ?? true;
    $emailNotifications = $settings['notifications_email'] ?? true;
    $telegramNotifications = $settings['notifications_telegram'] ?? false;
    $smsNotifications = $settings['notifications_sms'] ?? false;
    $notifyTradeExecution = $settings['notifications_trade_execution'] ?? true;
    $notifyTpSl = $settings['notifications_tp_sl'] ?? true;
    $notifySignals = $settings['notifications_signals'] ?? true;
    $notifyErrors = $settings['notifications_errors'] ?? true;
    $notifyHighRisk = $settings['notifications_high_risk'] ?? true;
    $dailySummary = $settings['notifications_daily_summary'] ?? true;
    $adminEmail = $settings['notifications_admin_email'] ?? 'admin@cryptobot.com';
    $smtpServer = $settings['notifications_smtp_server'] ?? 'smtp.gmail.com';
    
    // Backup Settings
    $backupEnabled = $settings['backup_enabled'] ?? true;
    $autoBackup = $settings['backup_auto_backup'] ?? true;
    $backupFrequency = $settings['backup_frequency'] ?? 'daily';
    $backupRetentionDays = $settings['backup_retention_days'] ?? 30;
    $dataRetention = $settings['backup_data_retention'] ?? 365;
    $logRetention = $settings['backup_log_retention'] ?? 90;
    $includeLogsInBackup = $settings['backup_include_logs'] ?? false;
    
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
                <div class="col-md-3">
                    <label class="form-label fw-semibold">System Name</label>
                    <input type="text" class="form-control form-control-lg" name="system_name" value="{{ $systemName }}">
                    <div class="form-text">Application display name</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">System Timezone</label>
                    <select class="form-select form-select-lg" name="system_timezone">
                        <option value="UTC" {{ $timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="America/New_York" {{ $timezone == 'America/New_York' ? 'selected' : '' }}>America/New York (EST)</option>
                        <option value="Europe/London" {{ $timezone == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                        <option value="Asia/Tokyo" {{ $timezone == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)</option>
                        <option value="Asia/Singapore" {{ $timezone == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (SGT)</option>
                    </select>
                    <div class="form-text">Server timezone for all operations</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Maintenance Mode</label>
                    <select class="form-select form-select-lg" name="system_maintenance_mode">
                        <option value="0" {{ !$maintenanceMode ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ $maintenanceMode ? 'selected' : '' }}>Enabled</option>
                    </select>
                    <div class="form-text">Put system in maintenance mode</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Debug Mode</label>
                    <select class="form-select form-select-lg" name="system_debug_mode">
                        <option value="0" {{ !$debugMode ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ $debugMode ? 'selected' : '' }}>Enabled</option>
                    </select>
                    <div class="form-text">Enable detailed error logging (production: disable)</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Allow User Registration</label>
                    <select class="form-select form-select-lg" name="system_allow_registration">
                        <option value="1" {{ $allowRegistration ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !$allowRegistration ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <div class="form-text">Allow new users to register</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email Verification Required</label>
                    <select class="form-select form-select-lg" name="system_email_verification">
                        <option value="1" {{ $emailVerification ? 'selected' : '' }}>Required</option>
                        <option value="0" {{ !$emailVerification ? 'selected' : '' }}>Not Required</option>
                    </select>
                    <div class="form-text">Require email verification for new accounts</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Session Timeout (minutes)</label>
                    <input type="number" class="form-control form-control-lg" name="system_session_timeout" value="{{ $sessionTimeout }}" min="15" max="1440">
                    <div class="form-text">Auto-logout after inactivity</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trading Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-currency-exchange me-2"></i>Trading Settings
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Global Trading Status</label>
                    <select class="form-select form-select-lg" name="trading_enabled">
                        <option value="1" {{ $tradingEnabled ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !$tradingEnabled ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <div class="form-text">Master on/off switch for all trading</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Max Trades Per User</label>
                    <input type="number" class="form-control form-control-lg" name="trading_max_trades_per_user" value="{{ $maxTradesPerUser }}" min="1" max="100">
                    <div class="form-text">Maximum concurrent trades per user</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Default Leverage</label>
                    <input type="number" class="form-control form-control-lg" name="trading_default_leverage" value="{{ $defaultLeverage }}" min="1" max="100">
                    <div class="form-text">Default leverage for new users</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Leverage</label>
                    <input type="number" class="form-control form-control-lg" name="trading_max_leverage" value="{{ $maxLeverage }}" min="1" max="100">
                    <div class="form-text">Maximum allowed leverage</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Min Position Size (USD)</label>
                    <input type="number" class="form-control form-control-lg" name="trading_min_position_size" value="{{ $minPositionSize }}" min="1" max="1000">
                    <div class="form-text">Minimum trade size</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Position Size (USD)</label>
                    <input type="number" class="form-control form-control-lg" name="trading_max_position_size" value="{{ $maxPositionSize }}" min="100" max="1000000">
                    <div class="form-text">Maximum trade size</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Trade Execution Timeout (seconds)</label>
                    <input type="number" class="form-control form-control-lg" name="trading_execution_timeout" value="{{ $executionTimeout }}" min="5" max="120">
                    <div class="form-text">Timeout for trade execution</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitoring & Risk Management -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-shield-exclamation me-2"></i>Monitoring & Risk Management
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Monitor Interval (minutes)</label>
                    <input type="number" class="form-control form-control-lg" name="monitor_interval" value="{{ $monitorInterval }}" min="1" max="60">
                    <div class="form-text">Position check frequency</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Emergency SL (%)</label>
                    <input type="number" class="form-control form-control-lg" name="monitor_emergency_sl" value="{{ $emergencySl }}" min="1" max="50" step="0.1">
                    <div class="form-text">Force close threshold</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Daily Loss Limit (%)</label>
                    <input type="number" class="form-control form-control-lg" name="monitor_daily_loss_limit" value="{{ $dailyLossLimit }}" min="1" max="100" step="0.1">
                    <div class="form-text">Circuit breaker trigger</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Drawdown (%)</label>
                    <input type="number" class="form-control form-control-lg" name="monitor_max_drawdown" value="{{ $maxDrawdown }}" min="1" max="100" step="0.1">
                    <div class="form-text">Maximum drawdown alert</div>
                </div>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="monitor_auto_stop_loss" name="monitor_auto_stop_loss" {{ $autoStopLoss ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="monitor_auto_stop_loss">
                            Automatic Stop Loss
                        </label>
                    </div>
                    <small class="text-muted">Automatically set stop loss on all positions</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="monitor_auto_take_profit" name="monitor_auto_take_profit" {{ $autoTakeProfit ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="monitor_auto_take_profit">
                            Automatic Take Profit
                        </label>
                    </div>
                    <small class="text-muted">Automatically set take profit on all positions</small>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="monitor_trailing_stop" name="monitor_trailing_stop" {{ $trailingStop ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="monitor_trailing_stop">
                            Trailing Stop Loss
                        </label>
                    </div>
                    <small class="text-muted">Enable trailing stop loss (moves with price)</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="monitor_circuit_breaker" name="monitor_circuit_breaker" {{ $circuitBreaker ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="monitor_circuit_breaker">
                            Circuit Breaker Protection
                        </label>
                    </div>
                    <small class="text-muted">Auto-disable trading on excessive losses</small>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enable_profit_milestones" name="enable_profit_milestones" {{ $enableProfitMilestones ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="enable_profit_milestones">
                            Profit Milestone Management
                        </label>
                    </div>
                    <small class="text-muted">Enable automatic profit milestone tracking and management</small>
                </div>
            </div>
        </div>
    </div>

    <!-- API Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-plug me-2"></i>API Configuration
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">API Timeout (seconds)</label>
                    <input type="number" class="form-control form-control-lg" name="api_timeout" value="{{ $apiTimeout }}" min="5" max="60">
                    <div class="form-text">Request timeout duration</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Retries</label>
                    <input type="number" class="form-control form-control-lg" name="api_max_retries" value="{{ $maxRetries }}" min="0" max="10">
                    <div class="form-text">Retry attempts on failure</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Bybit Rate Limit (per min)</label>
                    <input type="number" class="form-control form-control-lg" name="api_bybit_rate_limit" value="{{ $bybitRateLimit }}" min="10" max="200">
                    <div class="form-text">Maximum API calls per minute</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bell me-2"></i>Notification Settings
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4 mb-3">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_enabled" name="notifications_enabled" {{ $notificationsEnabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_enabled">
                            Global Notifications
                        </label>
                    </div>
                    <small class="text-muted">Master switch for all notifications</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_email" name="notifications_email" {{ $emailNotifications ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_email">
                            Email Notifications
                        </label>
                    </div>
                    <small class="text-muted">Send email notifications</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_telegram" name="notifications_telegram" {{ $telegramNotifications ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_telegram">
                            Telegram Notifications
                        </label>
                    </div>
                    <small class="text-muted">Send Telegram notifications</small>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_sms" name="notifications_sms" {{ $smsNotifications ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_sms">
                            SMS Notifications
                        </label>
                    </div>
                    <small class="text-muted">Send SMS notifications</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_trade_execution" name="notifications_trade_execution" {{ $notifyTradeExecution ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_trade_execution">
                            Trade Execution Alerts
                        </label>
                    </div>
                    <small class="text-muted">Notify on trade execution</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_tp_sl" name="notifications_tp_sl" {{ $notifyTpSl ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_tp_sl">
                            TP/SL Trigger Alerts
                        </label>
                    </div>
                    <small class="text-muted">Notify when TP/SL is hit</small>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_signals" name="notifications_signals" {{ $notifySignals ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_signals">
                            Signal Generation Alerts
                        </label>
                    </div>
                    <small class="text-muted">Notify when signals are generated</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_errors" name="notifications_errors" {{ $notifyErrors ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_errors">
                            Error Notifications
                        </label>
                    </div>
                    <small class="text-muted">Notify on system errors</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_high_risk" name="notifications_high_risk" {{ $notifyHighRisk ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_high_risk">
                            High Risk Event Alerts
                        </label>
                    </div>
                    <small class="text-muted">Notify about high-risk situations</small>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifications_daily_summary" name="notifications_daily_summary" {{ $dailySummary ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notifications_daily_summary">
                            Daily Performance Summary
                        </label>
                    </div>
                    <small class="text-muted">Send daily trading summary reports</small>
                </div>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Admin Notification Email</label>
                    <input type="email" class="form-control form-control-lg" name="notifications_admin_email" value="{{ $adminEmail }}">
                    <div class="form-text">Email address for admin notifications</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">SMTP Server</label>
                    <input type="text" class="form-control form-control-lg" name="notifications_smtp_server" value="{{ $smtpServer }}">
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
                    <select class="form-select form-select-lg" name="backup_auto_backup">
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
                    <input type="number" class="form-control form-control-lg" name="backup_retention_days" value="{{ $backupRetentionDays }}" min="7" max="90">
                    <div class="form-text">How long to keep backups</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data Retention (days)</label>
                    <input type="number" class="form-control form-control-lg" name="backup_data_retention" value="{{ $dataRetention }}" min="30" max="3650">
                    <div class="form-text">Keep trading data for</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Log Retention (days)</label>
                    <input type="number" class="form-control form-control-lg" name="backup_log_retention" value="{{ $logRetention }}" min="7" max="365">
                    <div class="form-text">How long to keep system logs</div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="backup_include_logs" name="backup_include_logs" {{ $includeLogsInBackup ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="backup_include_logs">
                            Include Logs in Database Backup
                        </label>
                    </div>
                    <small class="text-muted">Backup log files with database</small>
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
                    <input type="number" class="form-control form-control-lg" name="queue_max_workers" value="{{ $maxWorkers }}" min="1" max="50">
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