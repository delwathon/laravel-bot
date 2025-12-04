@extends('layouts.app')

@section('title', 'Signal Generator Settings - CryptoBot Pro')

@section('page-title', 'Signal Generator Configuration')

@section('content')

@php
    // Extract all settings with proper defaults
    $interval = $settings['signal_interval'] ?? 15;
    $topCount = $settings['signal_top_count'] ?? 5;
    $minConfidence = $settings['signal_min_confidence'] ?? 70;
    $expiry = $settings['signal_expiry'] ?? 30;
    
    $enabledPairs = $settings['signal_pairs'] ?? ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'XRPUSDT'];
    
    $primaryTimeframe = $settings['signal_primary_timeframe'] ?? '15';
    $higherTimeframe = $settings['signal_higher_timeframe'] ?? $settings['signal_secondary_timeframe'] ?? '60';
    
    $enabledPatterns = $settings['signal_patterns'] ?? ['order_block', 'fvg', 'bos', 'choch', 'liquidity_sweep', 'premium_discount'];
    
    $lookbackPeriods = $settings['signal_lookback_periods'] ?? 50;
    $patternStrength = $settings['signal_pattern_strength'] ?? 3;
    
    $riskReward = $settings['signal_risk_reward'] ?? '1:2';
    $maxSl = $settings['signal_max_sl'] ?? 2;
    $positionSize = $settings['signal_position_size'] ?? 5;
    $leverage = $settings['signal_leverage'] ?? 'Max';
    
    $enabledExchanges = $settings['signal_exchanges'] ?? ['bybit'];
    
    $autoExecute = $settings['signal_auto_execute'] ?? true;
    $notifyUsers = $settings['signal_notify_users'] ?? true;
    $logAnalysis = $settings['signal_log_analysis'] ?? true;
    $testMode = $settings['signal_test_mode'] ?? false;
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
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Signal Generation Interval</label>
                    <select class="form-select form-select-lg" name="interval">
                        <option value="5" {{ $interval == 5 ? 'selected' : '' }}>Every 5 minutes</option>
                        <option value="15" {{ $interval == 15 ? 'selected' : '' }}>Every 15 minutes</option>
                        <option value="30" {{ $interval == 30 ? 'selected' : '' }}>Every 30 minutes</option>
                        <option value="60" {{ $interval == 60 ? 'selected' : '' }}>Every 1 hour</option>
                        <option value="240" {{ $interval == 240 ? 'selected' : '' }}>Every 4 hours</option>
                    </select>
                    <div class="form-text">How often the SMC analysis should run</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Top Signals to Execute</label>
                    <input type="number" class="form-control form-control-lg" name="top_signals" value="{{ $topCount }}" min="1" max="20">
                    <div class="form-text">Number of highest-confidence signals to execute</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Minimum Confidence Threshold (%)</label>
                    <input type="number" class="form-control form-control-lg" name="min_confidence" value="{{ $minConfidence }}" min="50" max="95">
                    <div class="form-text">Only signals above this confidence will be considered</div>
                </div>
                <div class="col-md-6">
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
                <i class="bi bi-currency-exchange me-2"></i>Trading Pairs
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="mb-4">
                <label class="form-label fw-semibold">Select Trading Pairs to Monitor</label>
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
                                    name="pairs[]" 
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
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Primary Timeframe</label>
                    <select class="form-select form-select-lg" name="primary_timeframe">
                        <option value="5" {{ $primaryTimeframe == '5' ? 'selected' : '' }}>5 Minutes</option>
                        <option value="15" {{ $primaryTimeframe == '15' ? 'selected' : '' }}>15 Minutes</option>
                        <option value="30" {{ $primaryTimeframe == '30' ? 'selected' : '' }}>30 Minutes</option>
                        <option value="60" {{ $primaryTimeframe == '60' ? 'selected' : '' }}>1 Hour</option>
                        <option value="240" {{ $primaryTimeframe == '240' ? 'selected' : '' }}>4 Hours</option>
                    </select>
                    <div class="form-text">Main timeframe for signal generation</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Higher Timeframe (HTF)</label>
                    <select class="form-select form-select-lg" name="higher_timeframe">
                        <option value="15" {{ $higherTimeframe == '15' ? 'selected' : '' }}>15 Minutes</option>
                        <option value="30" {{ $higherTimeframe == '30' ? 'selected' : '' }}>30 Minutes</option>
                        <option value="60" {{ $higherTimeframe == '60' ? 'selected' : '' }}>1 Hour</option>
                        <option value="240" {{ $higherTimeframe == '240' ? 'selected' : '' }}>4 Hours</option>
                        <option value="D" {{ $higherTimeframe == 'D' ? 'selected' : '' }}>1 Day</option>
                    </select>
                    <div class="form-text">Higher timeframe for trend confirmation</div>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label fw-semibold">SMC Patterns to Detect</label>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_ob" name="patterns[]" value="order_block" {{ in_array('order_block', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_ob">
                                Order Blocks
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_fvg" name="patterns[]" value="fvg" {{ in_array('fvg', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_fvg">
                                Fair Value Gaps
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_bos" name="patterns[]" value="bos" {{ in_array('bos', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_bos">
                                Break of Structure
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_choch" name="patterns[]" value="choch" {{ in_array('choch', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_choch">
                                Change of Character
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_liq" name="patterns[]" value="liquidity_sweep" {{ in_array('liquidity_sweep', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_liq">
                                Liquidity Sweeps
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_premium" name="patterns[]" value="premium_discount" {{ in_array('premium_discount', $enabledPatterns) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pattern_premium">
                                Premium/Discount Zones
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Order Block Lookback Period</label>
                    <input type="number" class="form-control form-control-lg" name="lookback_periods" value="{{ $lookbackPeriods }}" min="10" max="200">
                    <div class="form-text">Number of candles to analyze for order blocks</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Minimum Pattern Strength</label>
                    <input type="number" class="form-control form-control-lg" name="pattern_strength" value="{{ $patternStrength }}" min="1" max="5">
                    <div class="form-text">Pattern strength threshold (1-5, higher = stronger)</div>
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
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Default Risk/Reward Ratio</label>
                    <input type="text" class="form-control form-control-lg" name="risk_reward" value="{{ $riskReward }}" placeholder="1:2">
                    <div class="form-text">Minimum R:R for signal execution</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Stop Loss (%)</label>
                    <input type="number" class="form-control form-control-lg" name="max_sl" value="{{ $maxSl }}" min="0.5" max="10" step="0.1">
                    <div class="form-text">Maximum stop loss percentage</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Position Size (% of Balance)</label>
                    <input type="number" class="form-control form-control-lg" name="position_size" value="{{ $positionSize }}" min="1" max="10">
                    <div class="form-text">Default position size per trade</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Leverage</label>
                    <input type="text" class="form-control form-control-lg" name="leverage" value="{{ $leverage }}">
                    <div class="form-text">Default leverage size per trade</div>
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
                        <input class="form-check-input" type="checkbox" id="exchange_bybit" name="exchanges[]" value="bybit" {{ in_array('bybit', $enabledExchanges) ? 'checked' : '' }}>
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
                        <input class="form-check-input" type="checkbox" id="auto_execute" name="auto_execute" {{ $autoExecute ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="auto_execute">
                            Auto-execute signals
                        </label>
                    </div>
                    <small class="text-muted">Automatically execute top signals when generated</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_users" name="notify_users" {{ $notifyUsers ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notify_users">
                            Notify users
                        </label>
                    </div>
                    <small class="text-muted">Send notifications when signals are generated</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="log_analysis" name="log_analysis" {{ $logAnalysis ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="log_analysis">
                            Log detailed analysis
                        </label>
                    </div>
                    <small class="text-muted">Store detailed analysis data for review</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="test_mode" name="test_mode" {{ $testMode ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="test_mode">
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