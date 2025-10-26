<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('user.dashboard') }}">
            <i class="bi bi-currency-bitcoin"></i> Crypto Trading Bot
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                        @if(auth()->user()->is_admin)
                            <span class="badge bg-danger">Admin</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if(!auth()->user()->is_admin)
                            <li>
                                <a class="dropdown-item" href="{{ route('user.account.settings') }}">
                                    <i class="bi bi-gear"></i> Account Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>