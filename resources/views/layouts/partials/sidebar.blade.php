<!-- Desktop Sidebar -->
<div class="sidebar bg-body border-end d-none d-lg-block" style="width: 280px;">
    <div class="d-flex flex-column h-100">
        
        <!-- Logo -->
        <div class="p-4 border-bottom">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-2">
                    <i class="bi bi-robot text-primary fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">CryptoBot Pro</h5>
                    <small class="text-muted">v2.0</small>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-person-fill text-primary fs-5"></i>
                </div>
                <div class="flex-grow-1 text-truncate">
                    <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                    <small class="text-muted text-truncate d-block">{{ auth()->user()->email }}</small>
                </div>
            </div>
            @if(auth()->user()->is_admin)
                <div class="mt-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                        <i class="bi bi-shield-fill-check me-1"></i>Administrator
                    </span>
                </div>
            @else
                <div class="mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active Trading
                    </span>
                </div>
            @endif
        </div>

        <!-- Navigation -->
        <nav class="flex-grow-1 overflow-auto py-3">
            @if(auth()->user()->is_admin)
                <!-- Admin Navigation -->
                <div class="px-3 mb-2">
                    <small class="text-muted text-uppercase fw-bold">Admin Panel</small>
                </div>
                
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    User Management
                    <span class="badge bg-primary rounded-pill ms-auto">248</span>
                </a>
                
                <a href="{{ route('admin.signals.index') }}" class="nav-link {{ request()->routeIs('admin.signals.*') ? 'active' : '' }}">
                    <i class="bi bi-lightning-charge"></i>
                    Signal Generator
                </a>
                
                <a href="{{ route('admin.trades.index') }}" class="nav-link {{ request()->routeIs('admin.trades.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    Admin Trades
                    <span class="badge bg-success rounded-pill ms-auto">1.3K</span>
                </a>
                
                <a href="{{ route('admin.monitoring.overview') }}" class="nav-link {{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}">
                    <i class="bi bi-display"></i>
                    Monitoring
                </a>
                
                <div class="px-3 mb-2 mt-4">
                    <small class="text-muted text-uppercase fw-bold">Analytics & Reports</small>
                </div>
                
                <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i>
                    Analytics
                </a>
                
                <a href="{{ route('admin.trade-history.index') }}" class="nav-link {{ request()->routeIs('admin.trade-history.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    Trade History
                </a>
                
                <div class="px-3 mb-2 mt-4">
                    <small class="text-muted text-uppercase fw-bold">Configuration</small>
                </div>
                
                <a href="{{ route('admin.api-keys.index') }}" class="nav-link {{ request()->routeIs('admin.api-keys.*') ? 'active' : '' }}">
                    <i class="bi bi-key"></i>
                    API Keys
                </a>
                
                <a href="{{ route('admin.settings.signal-generator') }}" class="nav-link {{ request()->routeIs('admin.settings.signal-generator') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>
                    Signal Settings
                </a>
                
                <a href="{{ route('admin.settings.system') }}" class="nav-link {{ request()->routeIs('admin.settings.system') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>
                    System Settings
                </a>
                
            @else
                <!-- User Navigation -->
                <div class="px-3 mb-2">
                    <small class="text-muted text-uppercase fw-bold">Trading</small>
                </div>
                
                <a href="{{ route('user.dashboard') }}" class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('user.exchanges.manage') }}" class="nav-link {{ request()->routeIs('user.exchanges.*') ? 'active' : '' }}">
                    <i class="bi bi-bank"></i>
                    My Exchanges
                </a>
                
                <a href="{{ route('user.trades.index') }}" class="nav-link {{ request()->routeIs('user.trades.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i>
                    Trade History
                </a>
                
                <a href="{{ route('user.positions.index') }}" class="nav-link {{ request()->routeIs('user.positions.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i>
                    Active Positions
                    <span class="badge bg-success rounded-pill ms-auto">12</span>
                </a>
                
                <div class="px-3 mb-2 mt-4">
                    <small class="text-muted text-uppercase fw-bold">Account</small>
                </div>
                
                <a href="{{ route('user.account.settings') }}" class="nav-link {{ request()->routeIs('user.account.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i>
                    Account Settings
                </a>
            @endif
        </nav>

        <!-- System Status -->
        <div class="p-3 border-top">
            <div class="card border-0 bg-body-secondary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="fw-semibold">System Status</small>
                        <span class="badge bg-success bg-opacity-10 text-success">
                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Operational
                        </span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span><i class="bi bi-cpu me-1"></i>CPU</span>
                        <span>45%</span>
                    </div>
                    <div class="progress mb-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 45%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span><i class="bi bi-hdd me-1"></i>API Calls</span>
                        <span>67%</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-info" style="width: 67%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout -->
        <div class="p-3 border-top">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
            </form>
        </div>

    </div>
</div>

<!-- Mobile Sidebar (Offcanvas) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header border-bottom">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-2">
                <i class="bi bi-robot text-primary fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0" id="mobileSidebarLabel">CryptoBot Pro</h5>
                <small class="text-muted">v2.0</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body p-0">
        <!-- User Info -->
        <div class="p-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-person-fill text-primary fs-5"></i>
                </div>
                <div class="flex-grow-1 text-truncate">
                    <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                    <small class="text-muted text-truncate d-block">{{ auth()->user()->email }}</small>
                </div>
            </div>
            @if(auth()->user()->is_admin)
                <div class="mt-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                        <i class="bi bi-shield-fill-check me-1"></i>Administrator
                    </span>
                </div>
            @else
                <div class="mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Active Trading
                    </span>
                </div>
            @endif
        </div>

        <!-- Mobile Navigation -->
        <nav class="py-3">
            @if(auth()->user()->is_admin)
                <!-- Admin Mobile Navigation -->
                <div class="px-3 mb-2">
                    <small class="text-muted text-uppercase fw-bold">Admin Panel</small>
                </div>
                
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-people"></i>
                    User Management
                </a>
                
                <a href="{{ route('admin.signals.index') }}" class="nav-link {{ request()->routeIs('admin.signals.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-lightning-charge"></i>
                    Signal Generator
                </a>
                
                <a href="{{ route('admin.trades.index') }}" class="nav-link {{ request()->routeIs('admin.trades.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-graph-up-arrow"></i>
                    Admin Trades
                </a>
                
                <a href="{{ route('admin.monitoring.overview') }}" class="nav-link {{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-display"></i>
                    Monitoring
                </a>
                
                <div class="px-3 mb-2 mt-4">
                    <small class="text-muted text-uppercase fw-bold">Analytics & Reports</small>
                </div>
                
                <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-bar-chart-line"></i>
                    Analytics
                </a>
                
                <a href="{{ route('admin.trade-history.index') }}" class="nav-link {{ request()->routeIs('admin.trade-history.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-clock-history"></i>
                    Trade History
                </a>
                
                <div class="px-3 mb-2 mt-4">
                    <small class="text-muted text-uppercase fw-bold">Configuration</small>
                </div>
                
                <a href="{{ route('admin.api-keys.index') }}" class="nav-link {{ request()->routeIs('admin.api-keys.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-key"></i>
                    API Keys
                </a>
                
                <a href="{{ route('admin.settings.signal-generator') }}" class="nav-link {{ request()->routeIs('admin.settings.signal-generator') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-sliders"></i>
                    Signal Settings
                </a>
                
                <a href="{{ route('admin.settings.system') }}" class="nav-link {{ request()->routeIs('admin.settings.system') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-gear"></i>
                    System Settings
                </a>
                
            @else
                <!-- User Mobile Navigation -->
                <div class="px-3 mb-2">
                    <small class="text-muted text-uppercase fw-bold">Trading</small>
                </div>
                
                <a href="{{ route('user.dashboard') }}" class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-house-door"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('user.exchanges.manage') }}" class="nav-link {{ request()->routeIs('user.exchanges.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-bank"></i>
                    My Exchanges
                </a>
                
                <a href="{{ route('user.trades.index') }}" class="nav-link {{ request()->routeIs('user.trades.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-arrow-left-right"></i>
                    Trade History
                </a>
                
                <a href="{{ route('user.positions.index') }}" class="nav-link {{ request()->routeIs('user.positions.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-graph-up"></i>
                    Active Positions
                </a>
                
                <div class="px-3 mb-2 mt-4">
                    <small class="text-muted text-uppercase fw-bold">Account</small>
                </div>
                
                <a href="{{ route('user.account.settings') }}" class="nav-link {{ request()->routeIs('user.account.*') ? 'active' : '' }}" data-bs-dismiss="offcanvas">
                    <i class="bi bi-person-gear"></i>
                    Account Settings
                </a>
            @endif
        </nav>

        <!-- Mobile Logout -->
        <div class="p-3 border-top mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
            </form>
        </div>
    </div>
</div>