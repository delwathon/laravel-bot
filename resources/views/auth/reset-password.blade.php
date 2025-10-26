@extends('layouts.guest')

@section('title', 'Reset Password - CryptoBot Pro')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center position-relative" style="z-index: 1;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                
                <!-- Theme Switcher -->
                <div class="d-flex justify-content-end mb-4">
                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleTheme()">
                        <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                    </button>
                </div>

                <!-- Logo -->
                <div class="text-center mb-5">
                    <div class="d-inline-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-robot text-primary fs-1"></i>
                        </div>
                        <div class="text-start">
                            <h2 class="fw-bold mb-0">CryptoBot Pro</h2>
                            <small class="text-muted">Enterprise Edition</small>
                        </div>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-lg-5">
                        
                        <!-- Icon Header -->
                        <div class="text-center mb-4">
                            <div class="bg-success bg-opacity-10 d-inline-flex p-4 rounded-circle mb-3">
                                <i class="bi bi-shield-lock-fill text-success fs-1"></i>
                            </div>
                            <h3 class="fw-bold mb-2">Reset Password</h3>
                            <p class="text-muted mb-0">Create a new secure password for your account</p>
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

                        <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                            @csrf
                            
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    Email Address
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary border-end-0">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 ps-0" 
                                           id="email" name="email" placeholder="you@example.com" 
                                           value="{{ old('email', $email ?? '') }}" required autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    New Password
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary border-end-0">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" 
                                           id="password" name="password" placeholder="Minimum 8 characters" required>
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('password', 'passwordIcon')">
                                        <i class="bi bi-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <div id="passwordStrength" class="progress mt-2" style="height: 4px;">
                                    <div id="passwordStrengthBar" class="progress-bar" style="width: 0%"></div>
                                </div>
                                <div class="form-text small mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Use at least 8 characters with a mix of letters, numbers & symbols
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    Confirm New Password
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary border-end-0">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" 
                                           id="password_confirmation" name="password_confirmation" 
                                           placeholder="Re-enter your new password" required>
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('password_confirmation', 'passwordConfirmIcon')">
                                        <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-3"></i>
                                    <div class="small">
                                        <strong>Security Reminder</strong>
                                        <p class="mb-0">After resetting your password, you'll be logged out of all devices. You'll need to sign in again.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-shield-check me-2"></i>Reset Password
                                </button>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                                </a>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- Security Tips -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                            Password Security Tips
                        </h6>
                        <ul class="mb-0 small text-muted">
                            <li class="mb-2">Use a unique password you don't use anywhere else</li>
                            <li class="mb-2">Include uppercase and lowercase letters, numbers, and symbols</li>
                            <li class="mb-2">Avoid common words and personal information</li>
                            <li>Consider using a password manager for better security</li>
                        </ul>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-muted small mb-0">
                        © {{ date('Y') }} CryptoBot Pro. All rights reserved.
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Password Toggle
    function togglePassword(fieldId, iconId) {
        const passwordInput = document.getElementById(fieldId);
        const passwordIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.className = 'bi bi-eye-slash';
        } else {
            passwordInput.type = 'password';
            passwordIcon.className = 'bi bi-eye';
        }
    }

    // Password Strength Indicator
    document.getElementById('password').addEventListener('input', function(e) {
        const password = e.target.value;
        const strength = calculatePasswordStrength(password);
        const strengthBar = document.getElementById('passwordStrengthBar');
        
        let width = 0;
        let bgClass = '';
        
        if (strength === 1) {
            width = 25;
            bgClass = 'bg-danger';
        } else if (strength === 2) {
            width = 50;
            bgClass = 'bg-warning';
        } else if (strength === 3) {
            width = 75;
            bgClass = 'bg-info';
        } else if (strength === 4) {
            width = 100;
            bgClass = 'bg-success';
        }
        
        strengthBar.style.width = width + '%';
        strengthBar.className = 'progress-bar ' + bgClass;
    });

    function calculatePasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        return strength;
    }

    // Form Submit
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting...';
    });
</script>
@endpush
@endsection