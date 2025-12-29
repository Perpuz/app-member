<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Perpustakaan Digital') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="antialiased">
    <div id="app">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-book-reader"></i>
                    <span>Perpuz</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="{{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
                        <a href="{{ url('/dashboard') }}">
                            <i class="fas fa-home"></i>
                            <span>Overview</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('books*') ? 'active' : '' }}">
                        <a href="{{ url('/books') }}">
                            <i class="fas fa-book"></i>
                            <span>Browse Books</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('transactions*') ? 'active' : '' }}">
                        <a href="{{ url('/transactions') }}">
                            <i class="fas fa-exchange-alt"></i>
                            <span>My Transactions</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('profile*') ? 'active' : '' }}">
                        <a href="{{ url('/profile') }}">
                            <i class="fas fa-user-circle"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="#" id="logout-btn" class="logout-btn" style="display: none;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
                <a href="{{ route('login') }}" id="login-link" class="login-btn" style="display: none;">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <header class="topbar">
                <div class="topbar-left">
                    <div class="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
                
                <div class="topbar-right">
                    <div id="user-menu" class="user-menu" style="display: none;">
                        <span id="user-name" class="user-name">User</span>
                        <div id="user-avatar" class="avatar">
                            U
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        // CLIENT-SIDE AUTH CHECK
        const token = localStorage.getItem('auth_token');
        const userStr = localStorage.getItem('user');
        
        // Define public routes that don't need auth
        const publicRoutes = ['/login', '/register', '/'];
        const currentPath = window.location.pathname;
        
        // redirect to login if no token and not on public route
        if (!token && !publicRoutes.includes(currentPath)) {
            window.location.href = '/';
        }
        
        // Update UI based on auth state
        if (token && userStr) {
            try {
                const user = JSON.parse(userStr);
                
                // Show user menu
                document.getElementById('user-menu').style.display = 'flex';
                document.getElementById('user-name').textContent = user.name;
                document.getElementById('user-avatar').textContent = user.name.charAt(0).toUpperCase();
                
                // Show logout button
                document.getElementById('logout-btn').style.display = 'flex';
            } catch(e) {
                console.error('Error parsing user data', e);
            }
        } else {
            // Show login link
             document.getElementById('login-link').style.display = 'flex';
        }
        
        // Logout handler
        document.getElementById('logout-btn').addEventListener('click', async function(e) {
            e.preventDefault();
            
            try {
                await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });
            } catch (e) {
                console.log('Logout error', e);
            }
            
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/';
        });

        // Add Authorization header to all fetch requests
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            // Only add auth for API requests
            if (url.includes('/api/') && !url.includes('/auth/login') && !url.includes('/auth/register')) {
                const token = localStorage.getItem('auth_token');
                if (token) {
                    if (!options.headers) options.headers = {};
                    options.headers['Authorization'] = 'Bearer ' + token;
                }
            }
            
            return originalFetch(url, options).then(async response => {
                if (response.status === 401 && url.includes('/api/')) {
                    // Token expired or invalid
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                }
                return response;
            });
        };

    </script>
    @stack('scripts')
</body>
</html>
