@extends('layouts.guest')

@section('title', 'Login - CryptoBot Pro')

@section('content')
<div class="min-vh-100 d-flex position-relative" style="z-index: 1;">
    <!-- Left Side - Enhanced Branding -->
    <div class="d-none d-lg-flex col-lg-7 position-relative">
        <!-- Gradient Overlay -->
        <div class="position-absolute w-100 h-100 top-0 start-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);">
        </div>
        
        <div class="position-relative d-flex flex-column justify-content-between p-5 text-white w-100">
            <!-- Logo & Branding -->
            <div>
                <div class="d-flex align-items-center mb-5">
                    <div class="bg-white bg-opacity-10 backdrop-blur p-3 rounded-4 me-3 border border-white border-opacity-25">
                        <i class="bi bi-robot fs-1"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-0">CryptoBot Pro</h2>
                        <p class="mb-0 opacity-75">Enterprise Trading Platform</p>
                    </div>
                </div>

                <div class="mb-5">
                    <h1 class="display-3 fw-bold mb-4">Welcome to the Future of Trading</h1>
                    <p class="fs-4 mb-5 opacity-90">Advanced AI-powered trading system with Smart Money Concepts analysis across multiple exchanges</p>
                </div>

                <!-- Feature Cards -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card bg-white bg-opacity-10 backdrop-blur border-0 h-100">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <div class="bg-white bg-opacity-25 d-inline-flex p-3 rounded-3">
                                        <i class="bi bi-lightning-charge-fill fs-2"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Real-Time Execution</h5>
                                <p class="mb-0 opacity-75 small">Lightning-fast signal generation and instant trade execution across all connected accounts</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-white bg-opacity-10 backdrop-blur border-0 h-100">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <div class="bg-white bg-opacity-25 d-inline-flex p-3 rounded-3">
                                        <i class="bi bi-shield-check fs-2"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Advanced Risk Control</h5>
                                <p class="mb-0 opacity-75 small">Multi-layered risk management with dynamic position sizing and automated stop-loss</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-white bg-opacity-10 backdrop-blur border-0 h-100">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <div class="bg-white bg-opacity-25 d-inline-flex p-3 rounded-3">
                                        <i class="bi bi-graph-up-arrow fs-2"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Smart Analytics</h5>
                                <p class="mb-0 opacity-75 small">Advanced SMC analysis with order blocks, fair value gaps, and liquidity sweeps</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-white bg-opacity-10 backdrop-blur border-0 h-100">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <div class="bg-white bg-opacity-25 d-inline-flex p-3 rounded-3">
                                        <i class="bi bi-people-fill fs-2"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-2">Multi-Tenant Architecture</h5>
                                <p class="mb-0 opacity-75 small">Manage unlimited users with isolated monitoring and independent position tracking</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="row g-4">
                    <div class="col-4">
                        <div class="text-center">
                            <h2 class="display-4 fw-bold mb-2">99.9%</h2>
                            <p class="mb-0 opacity-75">Uptime</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <h2 class="display-4 fw-bold mb-2">248</h2>
                            <p class="mb-0 opacity-75">Active Users</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <h2 class="display-4 fw-bold mb-2">68%</h2>
                            <p class="mb-0 opacity-75">Win Rate</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <p class="small mb-0 opacity-75">© {{ date('Y') }} CryptoBot Pro. All rights reserved. | Enterprise Edition v2.0</p>
            </div>
        </div>
    </div>

    <!-- Right Side - Advanced Login -->
    <div class="col-lg-5 d-flex align-items-center justify-content-center p-4 p-lg-5 bg-body">
        <div class="w-100" style="max-width: 500px;">
            
            <!-- Theme & Language Switcher -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-globe me-1"></i> EN
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">English</a></li>
                        <li><a class="dropdown-item" href="#">中文</a></li>
                        <li><a class="dropdown-item" href="#">日本語</a></li>
                        <li><a class="dropdown-item" href="#">한국어</a></li>
                    </ul>
                </div>
                <button class="btn btn-outline-secondary btn-sm" id="themeToggle" onclick="toggleTheme()">
                    <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                </button>
            </div>

            <!-- Mobile Logo -->
            <div class="d-lg-none text-center mb-5">
                <div class="d-inline-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-2">
                        <i class="bi bi-robot text-primary fs-1"></i>
                    </div>
                    <div class="text-start">
                        <h2 class="fw-bold mb-0">CryptoBot Pro</h2>
                        <small class="text-muted">Enterprise Edition</small>
                    </div>
                </div>
            </div>

            <!-- Login Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <div class="mb-4">
                        <h3 class="fw-bold mb-2">Sign In</h3>
                        <p class="text-muted mb-0">Access your admin control center</p>
                    </div>

                    <!-- Quick Access Badges -->
                    <div class="d-flex gap-2 mb-4">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> System Online
                        </span>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                            <i class="bi bi-clock"></i> 24/7 Active
                        </span>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Error!</strong> Please check the following:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                Email Address
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-body-secondary border-end-0">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control form-control-lg border-start-0 ps-0" 
                                       id="email" name="email" placeholder="admin@cryptobot.com" 
                                       value="{{ old('email') }}" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="password" class="form-label fw-semibold mb-0">
                                    Password
                                    <span class="text-danger">*</span>
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-body-secondary border-end-0">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control form-control-lg border-start-0 border-end-0 ps-0" 
                                       id="password" name="password" placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Keep me signed in
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Dashboard
                            </button>
                        </div>

                        <!-- Two Factor Auth -->
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shield-check fs-4 me-3"></i>
                                <div class="small">
                                    <strong>Two-Factor Authentication Available</strong>
                                    <p class="mb-0 opacity-75">Secure your account with 2FA</p>
                                </div>
                            </div>
                        </div>

                        @if (Route::has('register'))
                            <div class="text-center border-top pt-4">
                                <p class="text-muted mb-2 small">New to CryptoBot Pro?</p>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary">
                                    Create Enterprise Account
                                </a>
                            </div>
                        @endif
                    </form>

                </div>
            </div>

            <!-- Security Badges -->
            <div class="d-flex justify-content-center gap-3 mt-4 text-muted small">
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-fill-check me-1"></i> SSL Secured
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-lock-fill me-1"></i> Encrypted
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-1"></i> GDPR Compliant
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    // Password Toggle
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.className = 'bi bi-eye-slash';
        } else {
            passwordInput.type = 'password';
            passwordIcon.className = 'bi bi-eye';
        }
    }

    // Login Form
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Authenticating...';
    });
</script>
@endpush
@endsection