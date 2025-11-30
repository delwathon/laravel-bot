@extends('layouts.guest')

@section('title', 'Register - CryptoBot Pro')

@section('content')
<div class="min-vh-100 d-flex position-relative" style="z-index: 1;">
    <!-- Left Side - Branding -->
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
                    <h1 class="display-3 fw-bold mb-4">Start Your Trading Journey</h1>
                    <p class="fs-4 mb-5 opacity-90">Join hundreds of traders using AI-powered automation for consistent profits</p>
                </div>

                <!-- Benefits List -->
                <div class="mb-5">
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3 flex-shrink-0">
                            <i class="bi bi-check-lg fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Multi-Exchange Support</h5>
                            <p class="mb-0 opacity-75">Connect Bybit and Binance accounts seamlessly</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3 flex-shrink-0">
                            <i class="bi bi-check-lg fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Smart Money Concepts Analysis</h5>
                            <p class="mb-0 opacity-75">Advanced order flow and market structure detection</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3 flex-shrink-0">
                            <i class="bi bi-check-lg fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Automated Risk Management</h5>
                            <p class="mb-0 opacity-75">Dynamic stop-loss and take-profit execution</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle me-3 flex-shrink-0">
                            <i class="bi bi-check-lg fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">24/7 Position Monitoring</h5>
                            <p class="mb-0 opacity-75">Real-time tracking and instant notifications</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial -->
                <div class="card bg-white bg-opacity-10 backdrop-blur border-0">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="mb-3 fst-italic">"The SMC analysis is incredibly accurate. My win rate improved from 52% to 68% in just two months. The automated execution saves me hours every day."</p>
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Sarah Chen</div>
                                <small class="opacity-75">Professional Trader</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <p class="small mb-0 opacity-75">© {{ date('Y') }} CryptoBot Pro. All rights reserved. | Enterprise Edition v2.0</p>
            </div>
        </div>
    </div>

    <!-- Right Side - Registration Form -->
    <div class="col-lg-5 d-flex align-items-center justify-content-center p-5">
        <div class="w-100" style="max-width: 450px;">
            
            <!-- Logo for Mobile -->
            <div class="d-lg-none text-center mb-5">
                <div class="d-inline-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-2">
                        <i class="bi bi-robot text-primary fs-1"></i>
                    </div>
                    <div class="text-start">
                        <h3 class="fw-bold mb-0">CryptoBot Pro</h3>
                        <small class="text-muted">Enterprise Trading</small>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    
                    <h2 class="fw-bold mb-2">Create Your Account</h2>
                    <p class="text-muted mb-4">Get started with automated crypto trading</p>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-exclamation-triangle me-2"></i>Please correct the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" id="registerForm">
                        @csrf
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label fw-semibold">
                                    First Name
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary border-end-0">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0 @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" placeholder="John" 
                                           value="{{ old('first_name') }}" required autofocus>
                                </div>
                                @error('first_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="last_name" class="form-label fw-semibold">
                                    Last Name
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary border-end-0">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0 @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" placeholder="Doe" 
                                           value="{{ old('last_name') }}" required>
                                </div>
                                @error('last_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                Email Address
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary border-end-0">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                       id="email" name="email" placeholder="you@example.com" 
                                       value="{{ old('email') }}" required>
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                Password
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary border-end-0">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Minimum 8 characters" required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('password', 'passwordIcon')">
                                    <i class="bi bi-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            <div class="form-text small">
                                <i class="bi bi-info-circle me-1"></i>
                                Use at least 8 characters with a mix of letters, numbers & symbols
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                Confirm Password
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary border-end-0">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 border-end-0 ps-0" 
                                       id="password_confirmation" name="password_confirmation" 
                                       placeholder="Re-enter your password" required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('password_confirmation', 'passwordConfirmIcon')">
                                    <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                                <label class="form-check-label small" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>
                            @error('terms')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </div>

                        <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-shield-check fs-4 me-3"></i>
                                <div class="small">
                                    <strong>Secure Registration</strong>
                                    <p class="mb-0 opacity-75">Your data is encrypted and protected. We'll never share your information.</p>
                                </div>
                            </div>
                        </div>

                        <div class="text-center border-top pt-4">
                            <p class="text-muted mb-2 small">Already have an account?</p>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </a>
                        </div>
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

    // Registration Form
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating account...';
    });

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function(e) {
        const password = e.target.value;
        const strength = calculatePasswordStrength(password);
        // You can add visual feedback here
    });

    function calculatePasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        return strength;
    }
</script>
@endpush
@endsection