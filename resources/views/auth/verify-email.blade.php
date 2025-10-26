@extends('layouts.guest')

@section('title', 'Verify Email - CryptoBot Pro')

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
                            <div class="bg-warning bg-opacity-10 d-inline-flex p-4 rounded-circle mb-3">
                                <i class="bi bi-envelope-check-fill text-warning fs-1"></i>
                            </div>
                            <h3 class="fw-bold mb-2">Verify Your Email</h3>
                            <p class="text-muted mb-0">We've sent a verification link to your email address</p>
                        </div>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                A new verification link has been sent to your email address!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                                <div class="small">
                                    <strong>Check Your Inbox</strong>
                                    <p class="mb-2">We sent a verification email to:</p>
                                    <p class="mb-0 fw-bold">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="small text-muted mb-3">
                                <i class="bi bi-1-circle-fill me-2"></i>
                                Click the verification link in the email
                            </p>
                            <p class="small text-muted mb-3">
                                <i class="bi bi-2-circle-fill me-2"></i>
                                You'll be redirected to your dashboard
                            </p>
                            <p class="small text-muted mb-0">
                                <i class="bi bi-3-circle-fill me-2"></i>
                                Start trading with full access
                            </p>
                        </div>

                        <div class="d-grid gap-2 mb-4">
                            <form method="POST" action="{{ route('verification.send') }}" id="resendForm">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Resend Verification Email
                                </button>
                            </form>
                        </div>

                        <div class="text-center mb-4">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-decoration-none">
                                    <i class="bi bi-box-arrow-right me-1"></i>Sign Out
                                </button>
                            </form>
                        </div>

                        <hr class="my-4">

                        <!-- Help Section -->
                        <div class="text-center">
                            <p class="small text-muted mb-2">Didn't receive the email?</p>
                            <div class="d-flex flex-column gap-2 small">
                                <div class="text-muted">
                                    <i class="bi bi-check2 me-1"></i>
                                    Check your spam/junk folder
                                </div>
                                <div class="text-muted">
                                    <i class="bi bi-check2 me-1"></i>
                                    Make sure {{ auth()->user()->email }} is correct
                                </div>
                                <div class="text-muted">
                                    <i class="bi bi-check2 me-1"></i>
                                    Wait a few minutes and try resending
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Support Card -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                                <i class="bi bi-headset text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Still Having Issues?</h6>
                                <p class="mb-2 small text-muted">Our support team is here to help you get started.</p>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-chat-dots me-1"></i>Contact Support
                                </a>
                            </div>
                        </div>
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
    document.getElementById('resendForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
    });
</script>
@endpush
@endsection