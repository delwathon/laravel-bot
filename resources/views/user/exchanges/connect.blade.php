@extends('layouts.app')

@section('title', 'Connect Exchange - CryptoBot Pro')

@section('page-title', 'Connect Exchange')

@section('content')
<!-- Info Banner -->
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3 flex-shrink-0">
            <i class="bi bi-info-circle-fill text-info fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-2">Connect Your Exchange Account</h5>
            <p class="mb-2">To start automated trading, connect your Bybit or Binance account using API keys. Your keys are encrypted and never shared.</p>
            <ul class="mb-0 small">
                <li>API keys allow the bot to execute trades on your behalf</li>
                <li>Keys are stored securely with AES-256 encryption</li>
                <li>You maintain full control and can disconnect anytime</li>
            </ul>
        </div>
    </div>
</div>

<!-- Exchange Selection -->
<div class="row g-4 mb-4">
    <!-- Bybit Card -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-coin text-primary fs-1"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Bybit</h4>
                        <p class="text-muted mb-0">Global crypto derivatives exchange</p>
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Features:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            High liquidity perpetual contracts
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Up to 100x leverage
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Advanced trading tools
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Low trading fees
                        </li>
                    </ul>
                </div>
                <button class="btn btn-primary w-100 btn-lg" data-bs-toggle="modal" data-bs-target="#connectBybitModal">
                    <i class="bi bi-plug me-2"></i>Connect Bybit Account
                </button>
            </div>
        </div>
    </div>

    <!-- Binance Card -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-currency-bitcoin text-warning fs-1"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Binance</h4>
                        <p class="text-muted mb-0">World's largest crypto exchange</p>
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Features:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Largest trading volume globally
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Hundreds of trading pairs
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Spot and futures trading
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Competitive fee structure
                        </li>
                    </ul>
                </div>
                <button class="btn btn-warning w-100 btn-lg" data-bs-toggle="modal" data-bs-target="#connectBinanceModal">
                    <i class="bi bi-plug me-2"></i>Connect Binance Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Setup Guide -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-book me-2"></i>How to Get Your API Keys
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-md-6 mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-coin text-primary me-2"></i>Bybit API Setup
                </h6>
                <ol class="small">
                    <li class="mb-2">Log in to your Bybit account</li>
                    <li class="mb-2">Go to <strong>Account & Security → API</strong></li>
                    <li class="mb-2">Click <strong>Create New Key</strong></li>
                    <li class="mb-2">Choose <strong>System-generated API Keys</strong></li>
                    <li class="mb-2">Set permissions: Enable <strong>Read</strong> and <strong>Trade</strong></li>
                    <li class="mb-2">Add IP whitelist (optional but recommended)</li>
                    <li class="mb-2">Complete 2FA verification</li>
                    <li class="mb-2">Copy your API Key and Secret</li>
                </ol>
                <a href="https://www.bybit.com" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Go to Bybit
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-currency-bitcoin text-warning me-2"></i>Binance API Setup
                </h6>
                <ol class="small">
                    <li class="mb-2">Log in to your Binance account</li>
                    <li class="mb-2">Go to <strong>Profile → API Management</strong></li>
                    <li class="mb-2">Click <strong>Create API</strong></li>
                    <li class="mb-2">Choose <strong>System Generated</strong></li>
                    <li class="mb-2">Enable <strong>Enable Spot & Margin Trading</strong></li>
                    <li class="mb-2">Restrict access to trusted IPs (optional)</li>
                    <li class="mb-2">Complete security verification</li>
                    <li class="mb-2">Save your API Key and Secret Key</li>
                </ol>
                <a href="https://www.binance.com" target="_blank" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Go to Binance
                </a>
            </div>
        </div>

        <div class="alert alert-warning border-0 mt-3">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-3"></i>
                <div class="small">
                    <strong>Security Best Practices:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Never enable withdrawal permissions on API keys</li>
                        <li>Use IP whitelisting when possible</li>
                        <li>Enable 2FA on your exchange account</li>
                        <li>Keep your API secret secure and never share it</li>
                        <li>Regularly rotate your API keys</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connect Bybit Modal -->
<div class="modal fade" id="connectBybitModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-primary bg-opacity-10">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-coin me-2"></i>Connect Bybit Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('user.exchanges.store') }}" id="bybitConnectForm">
                @csrf
                <input type="hidden" name="exchange" value="bybit">
                
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-4">
                        <i class="bi bi-shield-lock me-2"></i>
                        <small>Your API keys are encrypted with AES-256 encryption and stored securely.</small>
                    </div>

                    <div class="mb-4">
                        <label for="bybit_api_key" class="form-label fw-semibold">
                            API Key
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="text" class="form-control" id="bybit_api_key" name="api_key" 
                                   placeholder="Enter your Bybit API Key" required>
                        </div>
                        <div class="form-text small">
                            Your public API key from Bybit
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bybit_api_secret" class="form-label fw-semibold">
                            API Secret
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary">
                                <i class="bi bi-shield-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="bybit_api_secret" name="api_secret" 
                                   placeholder="Enter your Bybit API Secret" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleSecret('bybit_api_secret', 'bybitSecretIcon')">
                                <i class="bi bi-eye" id="bybitSecretIcon"></i>
                            </button>
                        </div>
                        <div class="form-text small">
                            Your private API secret (never shared)
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bybit_label" class="form-label fw-semibold">Account Label (Optional)</label>
                        <input type="text" class="form-control" id="bybit_label" name="label" 
                               placeholder="e.g., Main Trading Account">
                        <div class="form-text small">
                            Give this connection a name to identify it
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="bybit_testnet">
                        <label class="form-check-label" for="bybit_testnet">
                            Use Testnet (for testing only)
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="bybit_terms" required>
                        <label class="form-check-label small" for="bybit_terms">
                            I confirm that I have disabled withdrawal permissions on this API key and understand the risks
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plug me-2"></i>Connect & Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Connect Binance Modal -->
<div class="modal fade" id="connectBinanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-warning bg-opacity-10">
                <h5 class="modal-title fw-bold text-warning">
                    <i class="bi bi-currency-bitcoin me-2"></i>Connect Binance Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('user.exchanges.store') }}" id="binanceConnectForm">
                @csrf
                <input type="hidden" name="exchange" value="binance">
                
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-4">
                        <i class="bi bi-shield-lock me-2"></i>
                        <small>Your API keys are encrypted with AES-256 encryption and stored securely.</small>
                    </div>

                    <div class="mb-4">
                        <label for="binance_api_key" class="form-label fw-semibold">
                            API Key
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="text" class="form-control" id="binance_api_key" name="api_key" 
                                   placeholder="Enter your Binance API Key" required>
                        </div>
                        <div class="form-text small">
                            Your public API key from Binance
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="binance_api_secret" class="form-label fw-semibold">
                            API Secret
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary">
                                <i class="bi bi-shield-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="binance_api_secret" name="api_secret" 
                                   placeholder="Enter your Binance API Secret" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleSecret('binance_api_secret', 'binanceSecretIcon')">
                                <i class="bi bi-eye" id="binanceSecretIcon"></i>
                            </button>
                        </div>
                        <div class="form-text small">
                            Your private API secret (never shared)
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="binance_label" class="form-label fw-semibold">Account Label (Optional)</label>
                        <input type="text" class="form-control" id="binance_label" name="label" 
                               placeholder="e.g., Main Trading Account">
                        <div class="form-text small">
                            Give this connection a name to identify it
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="binance_testnet">
                        <label class="form-check-label" for="binance_testnet">
                            Use Testnet (for testing only)
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="binance_terms" required>
                        <label class="form-check-label small" for="binance_terms">
                            I confirm that I have disabled withdrawal permissions on this API key and understand the risks
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-plug me-2"></i>Connect & Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function toggleSecret(fieldId, iconId) {
        const secretInput = document.getElementById(fieldId);
        const secretIcon = document.getElementById(iconId);
        
        if (secretInput.type === 'password') {
            secretInput.type = 'text';
            secretIcon.className = 'bi bi-eye-slash';
        } else {
            secretInput.type = 'password';
            secretIcon.className = 'bi bi-eye';
        }
    }

    // Form submission handlers
    document.getElementById('bybitConnectForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing connection...';
    });

    document.getElementById('binanceConnectForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing connection...';
    });
</script>
@endpush