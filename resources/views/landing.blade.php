<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Perpuz') }} - Welcome</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>

    <!-- Background Elements -->
    <div class="gradient-blur-left"></div>
    <div class="gradient-blur-right"></div>

    <!-- Navigation -->
    <nav class="landing-navbar">
        <div class="landing-logo" style="color: var(--text-new);">
            <i class="fas fa-book-reader"></i> Perpuz
        </div>
        <ul class="landing-nav-list">
            <li><a href="#" class="landing-nav-link">Home</a></li>
            <li><a href="#" class="landing-nav-link">Books</a></li>
            <li><a href="#" class="landing-nav-link">Contact</a></li>
        </ul>
        <button class="hamburger-btn" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>
    <aside class="mobile-menu" id="mobileMenu">
        <button class="mobile-menu-close" onclick="closeMobileMenu()" aria-label="Close Menu">
            <i class="fas fa-times"></i>
        </button>
        <ul class="mobile-nav-list">
            <li><a href="#" class="landing-nav-link" onclick="closeMobileMenu()">Home</a></li>
            <li><a href="#" class="landing-nav-link" onclick="closeMobileMenu()">Books</a></li>
            <li><a href="#" class="landing-nav-link" onclick="closeMobileMenu()">Contact</a></li>
        </ul>
    </aside>

    <!-- Hero Section -->
    <main class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Di Setiap Buku, Ada Dunia Baru</h1>
            <p class="hero-subtitle">
                Jelajahi pengetahuan dan kisah tak terbatas<br>
                melalui rak buku digital <span>Perpuz</span>.
            </p>
            
            <button class="hero-btn" id="start-reading-btn">
                Mulai Baca Sekarang!
            </button>
        </div>
        
        <div class="hero-image">
            <img src="{{ asset('images/image-book.png') }}" alt="Books Shelf">
        </div>
    </main>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeLoginModal()" aria-label="Close Modal"><i class="fas fa-times"></i></button>
            <div class="modal-body" style="width: 100%;">
                @include('auth.partials.login-form')
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal-overlay" id="registerModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeRegisterModal()" aria-label="Close Modal"><i class="fas fa-times"></i></button>
            <div class="modal-body" style="width: 100%;">
                @include('auth.partials.register-form')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hero Button Event Listener
            const startBtn = document.getElementById('start-reading-btn');
            if (startBtn) {
                startBtn.addEventListener('click', function() {
                    if (localStorage.getItem('auth_token')) {
                        window.location.href = '/dashboard';
                    } else {
                        openLoginModal();
                    }
                });
            }

            // Link Interceptors
            const goToRegisterBtn = document.getElementById('go-to-register');
            if (goToRegisterBtn) {
                goToRegisterBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    openRegisterModal();
                });
            }

            const goToLoginBtn = document.getElementById('go-to-login');
            if (goToLoginBtn) {
                goToLoginBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    openLoginModal();
                });
            }
        });

        // Mobile Menu Logic
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            if (menu && overlay) {
                menu.classList.toggle('active');
                overlay.classList.toggle('active');
            }
        }

        function closeMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            if (menu && overlay) {
                menu.classList.remove('active');
                overlay.classList.remove('active');
            }
        }

        // Modal Logic
        function openLoginModal() {
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.classList.add('active');
                closeRegisterModal();
            }
        }

        function closeLoginModal() {
            const loginModal = document.getElementById('loginModal');
            if (loginModal) loginModal.classList.remove('active');
        }

        function openRegisterModal() {
            const registerModal = document.getElementById('registerModal');
            if (registerModal) {
                registerModal.classList.add('active');
                closeLoginModal();
            }
        }

        function closeRegisterModal() {
            const registerModal = document.getElementById('registerModal');
            if (registerModal) registerModal.classList.remove('active');
        }

        // Close functions (global for onclick access from buttons)
        window.closeLoginModal = closeLoginModal;
        window.closeRegisterModal = closeRegisterModal;
        window.toggleMobileMenu = toggleMobileMenu;
        window.closeMobileMenu = closeMobileMenu;
        
        // Close modals if clicked outside
        const loginModal = document.getElementById('loginModal');
        if (loginModal) {
            loginModal.addEventListener('click', function(e) {
                if (e.target === this) closeLoginModal();
            });
        }
        
        const registerModal = document.getElementById('registerModal');
        if (registerModal) {
            registerModal.addEventListener('click', function(e) {
                if (e.target === this) closeRegisterModal();
            });
        }
    </script>
</body>
</html>
