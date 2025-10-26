<!-- Top Bar -->
<div class="bg-body border-bottom sticky-top" style="z-index: 999;">
    <div class="p-3 p-lg-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-link d-lg-none p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                    <i class="bi bi-list fs-2"></i>
                </button>
                <div>
                    <h4 class="mb-0 fw-bold">@yield('page-title', 'Command Center')</h4>
                    <p class="text-muted mb-0 small">
                        <i class="bi bi-clock me-1"></i>
                        <span id="currentTime"></span> | 
                        <span class="badge bg-success bg-opacity-10 text-success ms-2">
                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> All Systems Operational
                        </span>
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="toggleTheme()">
                    <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm position-relative">
                    <i class="bi bi-bell-fill"></i>
                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">3</span>
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                @if(auth()->user()->is_admin)
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#emergencyStopModal">
                        <i class="bi bi-stop-circle-fill me-1"></i>E-Stop
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Emergency Stop Modal -->
@if(auth()->user()->is_admin)
<div class="modal fade" id="emergencyStopModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Emergency Stop
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">This will immediately halt all trading activities across all users and exchanges.</p>
                <div class="alert alert-warning border-0 mb-0">
                    <strong>Warning:</strong> This action cannot be undone. All active positions will remain open but no new trades will be executed.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="executeEmergencyStop()">
                    <i class="bi bi-stop-circle-fill me-2"></i>Execute Emergency Stop
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Real-time Clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit',
            hour12: false 
        });
        const dateString = now.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        });
        const clockElement = document.getElementById('currentTime');
        if (clockElement) {
            clockElement.textContent = `${timeString} • ${dateString}`;
        }
    }

    // Start clock
    document.addEventListener('DOMContentLoaded', function() {
        updateClock();
        setInterval(updateClock, 1000);
    });

    // Emergency Stop
    function executeEmergencyStop() {
        const confirmation = prompt('Type "EMERGENCY STOP" to confirm:');
        if (confirmation === 'EMERGENCY STOP') {
            // Submit form or make AJAX call here
            alert('Emergency stop executed - All trading halted');
            bootstrap.Modal.getInstance(document.getElementById('emergencyStopModal')).hide();
        }
    }
</script>
@endif