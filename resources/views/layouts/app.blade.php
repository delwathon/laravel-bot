<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CryptoBot Pro')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    @stack('styles')
    
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: var(--bs-body-color);
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--bs-body-bg);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        main {
            margin-left: 280px;
        }
        
        @media (max-width: 768px) {
            main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-body-tertiary">

    <div class="d-flex">
        @include('layouts.partials.sidebar')

        <!-- Main Content -->
        <main class="flex-grow-1">
            @include('layouts.partials.topbar')
            
            <!-- Content Area -->
            <div class="p-3 p-lg-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <h4>Validation Errors:</h4>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            
            const icon = document.getElementById('themeIcon');
            icon.className = newTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            
            localStorage.setItem('theme', newTheme);
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.className = savedTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            }
        });

        // Refresh Dashboard
        function refreshDashboard() {
            const btn = event.target.closest('button');
            const icon = btn.querySelector('i');
            icon.classList.add('spin');
            setTimeout(() => {
                icon.classList.remove('spin');
                location.reload();
            }, 1000);
        }

        // Add spin animation
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
    
    @stack('scripts')
</body>
</html>