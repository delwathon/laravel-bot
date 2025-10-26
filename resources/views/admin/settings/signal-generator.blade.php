@extends('layouts.app')

@section('title', 'Signal Generator Settings - CryptoBot Pro')

@section('page-title', 'Signal Generator Configuration')

@section('content')
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
                        <option value="5">Every 5 minutes</option>
                        <option value="15" selected>Every 15 minutes</option>
                        <option value="30">Every 30 minutes</option>
                        <option value="60">Every 1 hour</option>
                        <option value="240">Every 4 hours</option>
                    </select>
                    <div class="form-text">How often the SMC analysis should run</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Top Signals to Execute</label>
                    <input type="number" class="form-control form-control-lg" name="top_signals" value="5" min="1" max="20">
                    <div class="form-text">Number of highest-confidence signals to execute</div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Minimum Confidence Threshold (%)</label>
                    <input type="number" class="form-control form-control-lg" name="min_confidence" value="70" min="50" max="95">
                    <div class="form-text">Only signals above this confidence will be considered</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Signal Expiry Time (minutes)</label>
                    <input type="number" class="form-control form-control-lg" name="signal_expiry" value="30" min="5" max="120">
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
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pair_btcusdt" name="pairs[]" value="BTCUSDT" checked>
                            <label class="form-check-label" for="pair_btcusdt">
                                <i class="bi bi-currency-bitcoin text-warning me-1"></i>BTC/USDT
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pair_ethusdt" name="pairs[]" value="ETHUSDT" checked>
                            <label class="form-check-label" for="pair_ethusdt">
                                <i class="bi bi-currency-exchange text-info me-1"></i>ETH/USDT
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pair_solusdt" name="pairs[]" value="SOLUSDT" checked>
                            <label class="form-check-label" for="pair_solusdt">
                                <i class="bi bi-coin text-purple me-1"></i>SOL/USDT
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pair_xrpusdt" name="pairs[]" value="XRPUSDT" checked>
                            <label class="form-check-label" for="pair_xrpusdt">
                                <i class="bi bi-currency-dollar text-success me-1"></i>XRP/USDT
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pair_adausdt" name="pairs[]" value="ADAUSDT">
                            <label class="form-check-label" for="pair_adausdt">
                                <i class="bi bi-coin text-primary me-1"></i>ADA/USDT
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pair_avaxusdt" name="pairs[]" value="AVAXUSDT">
                            <label class="form-check-label" for="pair_avaxusdt">
                                <i class="bi bi-coin text-danger me-1"></i>AVAX/USDT
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pair_dotusdt" name="pairs[]" value="DOTUSDT">
                            <label class="form-check-label" for="pair_dotusdt">
                                <i class="bi bi-coin text-pink me-1"></i>DOT/USDT
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pair_maticusdt" name="pairs[]" value="MATICUSDT">
                            <label class="form-check-label" for="pair_maticusdt">
                                <i class="bi bi-coin text-purple me-1"></i>MATIC/USDT
                            </label>
                        </div>
                    </div>
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
                        <option value="5m">5 Minutes</option>
                        <option value="15m" selected>15 Minutes</option>
                        <option value="30m">30 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                    </select>
                    <div class="form-text">Main timeframe for signal generation</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Higher Timeframe (HTF)</label>
                    <select class="form-select form-select-lg" name="higher_timeframe">
                        <option value="15m">15 Minutes</option>
                        <option value="30m">30 Minutes</option>
                        <option value="1h" selected>1 Hour</option>
                        <option value="4h">4 Hours</option>
                        <option value="1d">1 Day</option>
                    </select>
                    <div class="form-text">Higher timeframe for trend confirmation</div>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label fw-semibold">SMC Patterns to Detect</label>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_ob" name="patterns[]" value="order_block" checked>
                            <label class="form-check-label" for="pattern_ob">
                                Order Blocks
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_fvg" name="patterns[]" value="fvg" checked>
                            <label class="form-check-label" for="pattern_fvg">
                                Fair Value Gaps
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_bos" name="patterns[]" value="bos" checked>
                            <label class="form-check-label" for="pattern_bos">
                                Break of Structure
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_choch" name="patterns[]" value="choch" checked>
                            <label class="form-check-label" for="pattern_choch">
                                Change of Character
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_liq" name="patterns[]" value="liquidity_sweep" checked>
                            <label class="form-check-label" for="pattern_liq">
                                Liquidity Sweeps
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pattern_premium" name="patterns[]" value="premium_discount" checked>
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
                    <input type="number" class="form-control form-control-lg" name="ob_lookback" value="50" min="10" max="200">
                    <div class="form-text">Number of candles to analyze for order blocks</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Minimum Pattern Strength</label>
                    <input type="number" class="form-control form-control-lg" name="pattern_strength" value="3" min="1" max="5">
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
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Default Risk/Reward Ratio</label>
                    <input type="text" class="form-control form-control-lg" name="risk_reward" value="1:2" placeholder="1:2">
                    <div class="form-text">Minimum R:R for signal execution</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Stop Loss (%)</label>
                    <input type="number" class="form-control form-control-lg" name="max_sl" value="2" min="0.5" max="10" step="0.1">
                    <div class="form-text">Maximum stop loss percentage</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Position Size (% of Balance)</label>
                    <input type="number" class="form-control form-control-lg" name="position_size" value="5" min="1" max="20">
                    <div class="form-text">Default position size per trade</div>
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
                        <input class="form-check-input" type="checkbox" id="exchange_bybit" name="exchanges[]" value="bybit" checked>
                        <label class="form-check-label fw-semibold" for="exchange_bybit">
                            <i class="bi bi-coin text-primary me-2"></i>Enable Bybit
                        </label>
                    </div>
                    <small class="text-muted">Generate signals for Bybit exchange</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="exchange_binance" name="exchanges[]" value="binance" checked>
                        <label class="form-check-label fw-semibold" for="exchange_binance">
                            <i class="bi bi-currency-bitcoin text-warning me-2"></i>Enable Binance
                        </label>
                    </div>
                    <small class="text-muted">Generate signals for Binance exchange</small>
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
                        <input class="form-check-input" type="checkbox" id="auto_execute" name="auto_execute" checked>
                        <label class="form-check-label fw-semibold" for="auto_execute">
                            Auto-execute signals
                        </label>
                    </div>
                    <small class="text-muted">Automatically execute top signals when generated</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notify_users" name="notify_users" checked>
                        <label class="form-check-label fw-semibold" for="notify_users">
                            Notify users
                        </label>
                    </div>
                    <small class="text-muted">Send notifications when signals are generated</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="log_analysis" name="log_analysis" checked>
                        <label class="form-check-label fw-semibold" for="log_analysis">
                            Log detailed analysis
                        </label>
                    </div>
                    <small class="text-muted">Store detailed analysis data for review</small>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="test_mode" name="test_mode">
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