@extends('layouts.app')

@section('title', 'Manage Exchanges - CryptoBot Pro')

@section('page-title', 'Manage Exchanges')

@section('content')
<!-- Status Banner -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-bank fs-2 text-primary"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Exchange Connections</h4>
                        <p class="text-muted mb-0">Manage your Bybit and Binance API connections</p>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <div>
                        <div class="text-muted text-uppercase small">Connected</div>
                        <div class="fw-bold fs-5">2 Exchanges</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="text-muted text-uppercase small">Total Balance</div>
                        <div class="fw-bold fs-5">$21,170</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="text-muted text-uppercase small">Status</div>
                        <div class="fw-bold fs-5 text-success">
                            <i class="bi bi-circle-fill" style="font-size: 8px;"></i> All Active
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <a href="{{ route('user.exchanges.connect') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Add Exchange
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Exchange Connections -->
<div class="row g-4 mb-4">
    <!-- Bybit Card -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-coin text-primary fs-1"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">Bybit</h4>
                            <p class="text-muted mb-0">Main Trading Account</p>
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Connected
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Connection Info -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Connection Details</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">API Key</span>
                        <span class="font-monospace">****...XY2Z</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Connected On</span>
                        <span class="fw-semibold">Jan 15, 2024</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Used</span>
                        <span class="fw-semibold">2 minutes ago</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Permissions</span>
                        <div>
                            <span class="badge bg-success bg-opacity-10 text-success me-1">Read</span>
                            <span class="badge bg-info bg-opacity-10 text-info">Trade</span>
                        </div>
                    </div>
                </div>

                <!-- Account Balance -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Account Balance</h6>
                    <div class="card border-0 bg-body-secondary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Balance</span>
                                <span class="fw-bold fs-5">$12,450</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Available</span>
                                <span class="text-success fw-semibold">$8,230</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">In Positions</span>
                                <span class="text-info fw-semibold">$4,220</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trading Activity -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Trading Activity (24h)</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">Active Positions</div>
                            <div class="fw-bold">5</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Trades Executed</div>
                            <div class="fw-bold">18</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Total Volume</div>
                            <div class="fw-bold">$42,350</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">P&L</div>
                            <div class="fw-bold text-success">+$890</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary flex-grow-1" onclick="testConnection('bybit')">
                        <i class="bi bi-arrow-clockwise me-2"></i>Test Connection
                    </button>
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editBybitModal">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#disconnectModal" data-exchange="Bybit">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Binance Card -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-currency-bitcoin text-warning fs-1"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">Binance</h4>
                            <p class="text-muted mb-0">Trading Account</p>
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Connected
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Connection Info -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Connection Details</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">API Key</span>
                        <span class="font-monospace">****...AB9C</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Connected On</span>
                        <span class="fw-semibold">Jan 15, 2024</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Used</span>
                        <span class="fw-semibold">5 minutes ago</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Permissions</span>
                        <div>
                            <span class="badge bg-success bg-opacity-10 text-success me-1">Read</span>
                            <span class="badge bg-info bg-opacity-10 text-info">Trade</span>
                        </div>
                    </div>
                </div>

                <!-- Account Balance -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Account Balance</h6>
                    <div class="card border-0 bg-body-secondary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Balance</span>
                                <span class="fw-bold fs-5">$8,720</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Available</span>
                                <span class="text-success fw-semibold">$5,850</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">In Positions</span>
                                <span class="text-info fw-semibold">$2,870</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trading Activity -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Trading Activity (24h)</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">Active Positions</div>
                            <div class="fw-bold">3</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Trades Executed</div>
                            <div class="fw-bold">12</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Total Volume</div>
                            <div class="fw-bold">$28,900</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">P&L</div>
                            <div class="fw-bold text-success">+$344</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-warning flex-grow-1" onclick="testConnection('binance')">
                        <i class="bi bi-arrow-clockwise me-2"></i>Test Connection
                    </button>
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editBinanceModal">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#disconnectModal" data-exchange="Binance">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connection History -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-clock-history me-2"></i>Connection History
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                    <tr>
                        <th class="border-0 px-4 py-3 fw-semibold">Time</th>
                        <th class="border-0 py-3 fw-semibold">Exchange</th>
                        <th class="border-0 py-3 fw-semibold">Action</th>
                        <th class="border-0 py-3 fw-semibold">Status</th>
                        <th class="border-0 py-3 fw-semibold">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">14:23:15</div>
                            <small class="text-muted">Today</small>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>Connection Test</td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle"></i> Success
                            </span>
                        </td>
                        <td><small class="text-muted">API connection verified</small></td>
                    </tr>
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">13:45:22</div>
                            <small class="text-muted">Today</small>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-currency-bitcoin me-1"></i>Binance
                            </span>
                        </td>
                        <td>Balance Sync</td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle"></i> Success
                            </span>
                        </td>
                        <td><small class="text-muted">Balance updated: $8,720</small></td>
                    </tr>
                    <tr>
                        <td class="px-4">
                            <div class="small fw-semibold">10:15:33</div>
                            <small class="text-muted">Today</small>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-coin me-1"></i>Bybit
                            </span>
                        </td>
                        <td>Trade Execution</td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle"></i> Success
                            </span>
                        </td>
                        <td><small class="text-muted">BTC/USDT LONG opened</small></td>
                    </tr>
                    <tr class="table-danger bg-opacity-10">
                        <td class="px-4">
                            <div class="small fw-semibold">08:42:11</div>
                            <small class="text-muted">Today</small>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-currency-bitcoin me-1"></i>Binance
                            </span>
                        </td>
                        <td>Connection Test</td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-x-circle"></i> Failed
                            </span>
                        </td>
                        <td><small class="text-danger">Rate limit exceeded</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Security Tips -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 p-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-shield-check me-2"></i>Security Best Practices
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="d-flex align-items-start">
                    <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Regular Key Rotation</h6>
                        <p class="small text-muted mb-0">Rotate your API keys every 3-6 months for enhanced security</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-start">
                    <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">IP Whitelisting</h6>
                        <p class="small text-muted mb-0">Enable IP restrictions on your exchange for added protection</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-start">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Never Share Your Keys</h6>
                        <p class="small text-muted mb-0">Your API secret should never be shared with anyone</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-start">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Disable Withdrawal</h6>
                        <p class="small text-muted mb-0">Never enable withdrawal permissions on trading API keys</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Bybit Modal -->
<div class="modal fade" id="editBybitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil me-2"></i>Edit Bybit Connection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('user.exchanges.update', 1) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bybit_label_edit" class="form-label fw-semibold">Account Label</label>
                        <input type="text" class="form-control" id="bybit_label_edit" name="label" value="Main Trading Account">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current API Key</label>
                        <div class="form-control bg-body-secondary">****...XY2Z</div>
                        <small class="text-muted">To change API key, disconnect and reconnect</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Binance Modal -->
<div class="modal fade" id="editBinanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil me-2"></i>Edit Binance Connection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('user.exchanges.update', 2) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="binance_label_edit" class="form-label fw-semibold">Account Label</label>
                        <input type="text" class="form-control" id="binance_label_edit" name="label" value="Trading Account">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current API Key</label>
                        <div class="form-control bg-body-secondary">****...AB9C</div>
                        <small class="text-muted">To change API key, disconnect and reconnect</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Disconnect Modal -->
<div class="modal fade" id="disconnectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Disconnect Exchange
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to disconnect this exchange?</p>
                <div class="alert alert-warning border-0 mb-3">
                    <strong>This will:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Close all active positions on this exchange</li>
                        <li>Stop automated trading</li>
                        <li>Remove the API connection</li>
                        <li>Require reconnection to resume trading</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type "DISCONNECT" to confirm</label>
                    <input type="text" class="form-control" id="disconnectConfirmInput" placeholder="Type DISCONNECT">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="disconnectConfirmBtn" disabled>
                    <i class="bi bi-x-circle me-2"></i>Disconnect
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Test connection function
    function testConnection(exchange) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
        
        // Simulate API call
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            alert(`${exchange} connection test successful!`);
        }, 2000);
    }

    // Disconnect confirmation
    const disconnectInput = document.getElementById('disconnectConfirmInput');
    const disconnectBtn = document.getElementById('disconnectConfirmBtn');
    
    if (disconnectInput && disconnectBtn) {
        disconnectInput.addEventListener('input', function() {
            disconnectBtn.disabled = this.value !== 'DISCONNECT';
        });
    }
</script>
@endpush