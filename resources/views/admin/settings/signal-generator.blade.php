@extends('layouts.app')

@section('title', 'Signal Generator Settings - CryptoBot Pro')

@section('page-title', 'Signal Generator Configuration')

@section('content')

@php
    // Schedule Configuration
    $interval = $settings['signal_interval'] ?? 30;
    $topCount = $settings['signal_top_count'] ?? 10;
    $minConfidence = $settings['signal_min_confidence'] ?? 70;
    $expiry = $settings['signal_expiry'] ?? 30;
    $autoExecute = $settings['signal_auto_execute'] ?? true;
    $autoExecuteCount = $settings['signal_auto_execute_count'] ?? 3;
    
    // Trading Pairs Configuration
    $useDynamicPairs = $settings['signal_use_dynamic_pairs'] ?? true;
    $minVolume = $settings['signal_min_volume'] ?? 5000000;
    $maxPairs = $settings['signal_max_pairs'] ?? 50;
    $enabledPairs = $settings['signal_pairs'] ?? ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'XRPUSDT'];
    
    // Timeframes
    $primaryTimeframe = $settings['signal_primary_timeframe'] ?? '240';
    $higherTimeframe = $settings['signal_higher_timeframe'] ?? 'D';
    
    // SMC Pattern Detection
    $enabledPatterns = $settings['signal_patterns'] ?? ['order_block', 'fvg', 'bos', 'choch', 'liquidity_sweep', 'premium_discount'];
    
    // Analysis Parameters
    $lookbackPeriods = $settings['signal_lookback_periods'] ?? 50;
    $patternStrength = $settings['signal_pattern_strength'] ?? 3;
    
    // Risk Management
    $riskReward = $settings['signal_risk_reward'] ?? '1:2';
    $maxSl = $settings['signal_max_sl'] ?? 5;
    $positionSize = $settings['signal_position_size'] ?? 5;
    $leverage = $settings['signal_leverage'] ?? 'Max';
    $orderType = $settings['signal_order_type'] ?? 'Market';
    
    // Exchange Configuration
    $enabledExchanges = $settings['signal_exchanges'] ?? ['bybit'];
    
    // Advanced Options
    $notifyUsers = $settings['signal_notify_users'] ?? true;
    $logAnalysis = $settings['signal_log_analysis'] ?? true;
    $testMode = $settings['signal_test_mode'] ?? false;
    
    // Conflict Management Settings (NEW)
    $staleOrderHours = $settings['signal_stale_order_hours'] ?? 24;
    $skipDuplicatePositions = $settings['signal_skip_duplicate_positions'] ?? true;
    $cancelOppositePending = $settings['signal_cancel_opposite_pending'] ?? true;
    $cancelStalePending = $settings['signal_cancel_stale_pending'] ?? true;
    $closeOppositePositions = $settings['signal_close_opposite_positions'] ?? false;
@endphp

<!-- Settings Notice -->
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3 flex-shrink-0">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-2">Important Configuration Notice</h5>
            <p class="mb-0">Changes to these settings will affect signal generation for all users. Test thoroughly before applying to production.</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.signal-generator.update') }}">
    @csrf
    @method('PUT')

    <!-- Schedule Configuration -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock me-2"></i>Schedule Configuration
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Signal Generation Interval</label>
                    <select class="form-select form-select-lg" name="signal_interval">
                        <option value="5" {{ $interval == 5 ? 'selected' : '' }}>Every 5 minutes</option>
                        <option value="15" {{ $interval == 15 ? 'selected' : '' }}>Every 15 minutes</option>
                        <option value="30" {{ $interval == 30 ? 'selected' : '' }}>Every 30 minutes</option>
                        <option value="60" {{ $interval == 60 ? 'selected' : '' }}>Every 1 hour</option>
                        <option value="240" {{ $interval == 240 ? 'selected' : '' }}>Every 4 hours</option>
                    </select>
                    <div class="form-text">How often the SMC analysis should run</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Top Signals to Generate</label>
                    <input type="number" class="form-control form-control-lg" name="signal_top_count" value="{{ $topCount }}" min="1" max="50">
                    <div class="form-text">Number of highest-confidence signals to generate</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Auto-Execute Count</label>
                    <input type="number" class="form-control form-control-lg" name="signal_auto_execute_count" value="{{ $autoExecuteCount }}" min="1" max="20">
                    <div class="form-text">Number of signals to auto-execute</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Minimum Confidence (%)</label>
                    <input type="number" class="form-control form-control-lg" name="signal_min_confidence" value="{{ $minConfidence }}" min="50" max="95">
                    <div class="form-text">Only signals above this confidence will be considered</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Signal Expiry Time (minutes)</label>
                    <input type="number" class="form-control form-control-lg" name="signal_expiry" value="{{ $expiry }}" min="5" max="1440">
                    <div class="form-text">Signals expire if not executed within this time</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trading Pairs Configuration -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-currency-exchange me-2"></i>Trading Pairs Configuration
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_use_dynamic_pairs" name="signal_use_dynamic_pairs" {{ $useDynamicPairs ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="signal_use_dynamic_pairs">
                            Use Dynamic Pair Selection
                        </label>
                    </div>
                    <small class="text-muted">Select pairs based on trading volume</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Minimum Volume (USDT)</label>
                    <input type="number" class="form-control form-control-lg" name="signal_min_volume" value="{{ $minVolume }}" min="1000000" step="1000000">
                    <div class="form-text">Minimum 24h volume for pair selection</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Maximum Pairs to Analyze</label>
                    <input type="number" class="form-control form-control-lg" name="signal_max_pairs" value="{{ $maxPairs }}" min="10" max="100">
                    <div class="form-text">Number of pairs to analyze</div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Fixed Trading Pairs (used when dynamic is disabled)</label>
                <div class="row g-3">

                    @php
                        $pairs = [
                            'BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'TRXUSDT',
                            'MATICUSDT', 'DOTUSDT', 'LTCUSDT', 'LINKUSDT', 'AVAXUSDT', 'UNIUSDT', 'ATOMUSDT', 'XLMUSDT',
                            'FILUSDT', 'ETCUSDT', 'NEARUSDT', 'APTUSDT', 'ICPUSDT', 'ARBUSDT', 'OPUSDT', 'LDOUSDT',
                            'INJUSDT', 'STXUSDT', 'TIAUSDT', 'SUIUSDT', 'SEIUSDT', 'RENDERUSDT', 'RNDRUSDT', 'ALGOUSDT',
                            'VETUSDT', 'AAVEUSDT', 'SUSHIUSDT', 'PEPEUSDT', 'WIFUSDT', 'BONKUSDT', 'FLOKIUSDT', 'SHIBUSDT',
                            'FTMUSDT', 'SANDUSDT', 'MANAUSDT', 'AXSUSDT', 'GALAUSDT', 'ENJUSDT', 'CHZUSDT', 'GMTUSDT',
                            'APEUSDT', 'BLURUSDT'
                        ];
                    @endphp

                    @foreach ($pairs as $pair)
                        <div class="col-md-2">
                            <div class="form-check form-switch">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="pair_{{ strtolower($pair) }}" 
                                    name="signal_pairs[]" 
                                    value="{{ $pair }}" 
                                    {{ in_array($pair, $enabledPairs) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="pair_{{ strtolower($pair) }}">
                                    <i class="bi bi-coin text-primary me-1"></i>{{ $pair }}
                                </label>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>

    <!-- SMC Analysis Configuration -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-graph-up-arrow me-2"></i>SMC Analysis Parameters
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Primary Timeframe</label>
                    <select class="form-select form-select-lg" name="signal_primary_timeframe">
                        <option value="5" {{ $primaryTimeframe == '5' ? 'selected' : '' }}>5 Minutes</option>
                        <option value="15" {{ $primaryTimeframe == '15' ? 'selected' : '' }}>15 Minutes</option>
                        <option value="30" {{ $primaryTimeframe == '30' ? 'selected' : '' }}>30 Minutes</option>
                        <option value="60" {{ $primaryTimeframe == '60' ? 'selected' : '' }}>1 Hour</option>
                        <option value="240" {{ $primaryTimeframe == '240' ? 'selected' : '' }}>4 Hours</option>
                    </select>
                    <div class="form-text">Main timeframe for signal generation</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Higher Timeframe (HTF)</label>
                    <select class="form-select form-select-lg" name="signal_higher_timeframe">
                        <option value="15" {{ $higherTimeframe == '15' ? 'selected' : '' }}>15 Minutes</option>
                        <option value="30" {{ $higherTimeframe == '30' ? 'selected' : '' }}>30 Minutes</option>
                        <option value="60" {{ $higherTimeframe == '60' ? 'selected' : '' }}>1 Hour</option>
                        <option value="240" {{ $higherTimeframe == '240' ? 'selected' : '' }}>4 Hours</option>
                        <option value="D" {{ $higherTimeframe == 'D' ? 'selected' : '' }}>1 Day</option>
                    </select>
                    <div class="form-text">Higher timeframe for trend confirmation</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Order Block Lookback Period</label>
                    <input type="number" class="form-control form-control-lg" name="signal_lookback_periods" value="{{ $lookbackPeriods }}" min="10" max="200">
                    <div class="form-text">Number of candles to analyze for order blocks</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Minimum Pattern Strength</label>
                    <input type="number" class="form-control form-control-lg" name="signal_pattern_strength" value="{{ $patternStrength }}" min="1" max="5">
                    <div class="form-text">Pattern strength threshold (1-5, higher = stronger)</div>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label fw-semibold">SMC Patterns to Detect</label>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_ob" name="signal_patterns[]" value="order_block" {{ in_array('order_block', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_ob">
                                Order Blocks
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_fvg" name="signal_patterns[]" value="fvg" {{ in_array('fvg', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_fvg">
                                Fair Value Gaps
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_bos" name="signal_patterns[]" value="bos" {{ in_array('bos', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_bos">
                                Break of Structure
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_choch" name="signal_patterns[]" value="choch" {{ in_array('choch', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_choch">
                                Change of Character
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_liq" name="signal_patterns[]" value="liquidity_sweep" {{ in_array('liquidity_sweep', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_liq">
                                Liquidity Sweeps
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_premium" name="signal_patterns[]" value="premium_discount" {{ in_array('premium_discount', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_premium">
                                Premium/Discount Zones
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Management -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-shield-check me-2"></i>Risk Management
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Default Risk/Reward Ratio</label>
                    <input type="text" class="form-control form-control-lg" name="signal_risk_reward" value="{{ $riskReward }}" placeholder="1:2">
                    <div class="form-text">Minimum R:R for signal execution</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Max Stop Loss (%)</label>
                    <input type="number" class="form-control form-control-lg" name="signal_max_sl" value="{{ $maxSl }}" min="0.5" max="10" step="0.1">
                    <div class="form-text">Maximum stop loss percentage</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Position Size (% of Balance)</label>
                    <input type="number" class="form-control form-control-lg" name="signal_position_size" value="{{ $positionSize }}" min="1" max="20" step="0.5">
                    <div class="form-text">Default position size per trade</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Leverage</label>
                    <input type="text" class="form-control form-control-lg" name="signal_leverage" value="{{ $leverage }}">
                    <div class="form-text">Default leverage (e.g., 10 or Max)</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Order Type</label>
                    <select class="form-select form-select-lg" name="signal_order_type">
                        <option value="Market" {{ $orderType == 'Market' ? 'selected' : '' }}>Market Order</option>
                        <option value="Limit" {{ $orderType == 'Limit' ? 'selected' : '' }}>Limit Order</option>
                    </select>
                    <div class="form-text">Default order type for signal execution</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conflict Management (NEW) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>Conflict Management
            </h5>
            <p class="text-muted small mb-0 mt-2">Handle duplicate positions, stale orders, and opposite direction signals</p>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-info border-0 mb-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle me-2 flex-shrink-0"></i>
                    <div>
                        <strong>Admin-Centric Model:</strong> Conflict checks are performed on admin's account. 
                        If admin has a conflict, the signal is skipped or resolved for ALL users uniformly.
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Stale Order Threshold (hours)</label>
                    <input type="number" class="form-control form-control-lg" name="signal_stale_order_hours" value="{{ $staleOrderHours }}" min="1" max="168">
                    <div class="form-text">Pending orders older than this are considered stale</div>
                </div>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_skip_duplicate_positions" name="signal_skip_duplicate_positions" {{ $skipDuplicatePositions ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="signal_skip_duplicate_positions">
                            Skip Duplicate Positions
                        </label>
                    </div>
                    <small class="text-muted">Skip signal if admin already has an open position on the same symbol</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_cancel_opposite_pending" name="signal_cancel_opposite_pending" {{ $cancelOppositePending ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="signal_cancel_opposite_pending">
                            Cancel Opposite Pending Orders
                        </label>
                    </div>
                    <small class="text-muted">Cancel pending orders if new signal is opposite direction</small>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_cancel_stale_pending" name="signal_cancel_stale_pending" {{ $cancelStalePending ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="signal_cancel_stale_pending">
                            Auto-Cancel Stale Pending Orders
                        </label>
                    </div>
                    <small class="text-muted">Automatically cancel pending orders that exceed the stale threshold</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_close_opposite_positions" name="signal_close_opposite_positions" {{ $closeOppositePositions ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-danger" for="signal_close_opposite_positions">
                            Close Opposite Positions (Risky)
                        </label>
                    </div>
                    <small class="text-danger">⚠️ Close open positions if new signal is opposite direction (use with caution)</small>
                </div>
            </div>

            <div class="alert alert-warning border-0 mt-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle me-2 flex-shrink-0"></i>
                    <div>
                        <strong>Recommended Settings (Conservative):</strong><br>
                        • Stale Order Threshold: 24 hours<br>
                        • Skip Duplicate Positions: Enabled<br>
                        • Cancel Opposite Pending: Enabled<br>
                        • Cancel Stale Pending: Enabled<br>
                        • Close Opposite Positions: Disabled (too risky)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exchange Configuration -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bank me-2"></i>Exchange Configuration
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="exchange_bybit" name="signal_exchanges[]" value="bybit" {{ in_array('bybit', $enabledExchanges) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="exchange_bybit">
                            <i class="bi bi-coin text-primary me-2"></i>Enable Bybit
                        </label>
                    </div>
                    <small class="text-muted">Generate signals for Bybit exchange</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Options -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-gear-fill me-2"></i>Advanced Options
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_auto_execute" name="signal_auto_execute" {{ $autoExecute ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="signal_auto_execute">
                            Auto-execute signals
                        </label>
                    </div>
                    <small class="text-muted">Automatically execute top signals when generated</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_notify_users" name="signal_notify_users" {{ $notifyUsers ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="signal_notify_users">
                            Notify users
                        </label>
                    </div>
                    <small class="text-muted">Send notifications when signals are generated</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_log_analysis" name="signal_log_analysis" {{ $logAnalysis ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="signal_log_analysis">
                            Log detailed analysis
                        </label>
                    </div>
                    <small class="text-muted">Store detailed analysis data for review</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="signal_test_mode" name="signal_test_mode" {{ $testMode ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="signal_test_mode">
                            Test mode
                        </label>
                    </div>
                    <small class="text-muted">Generate signals without executing trades</small>
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
                <button type="button" class="btn btn-outline-primary btn-lg ms-auto" data-bs-toggle="modal" data-bs-target="#testConfigModal">
                    <i class="bi bi-play-circle me-2"></i>Test Configuration
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Test Configuration Modal -->
<div class="modal fade" id="testConfigModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-primary bg-opacity-10">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-play-circle me-2"></i>Test Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Run a test with the current configuration to verify settings.</p>
                <div class="alert alert-info border-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>This will generate signals without executing trades.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-play-circle me-2"></i>Run Test
                </button>
            </div>
        </div>
    </div>
</div>

@endsection