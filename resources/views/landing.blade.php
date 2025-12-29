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
            <li><a href="#home" class="landing-nav-link">Home</a></li>
            <li><a href="#browse-books" class="landing-nav-link">Books</a></li>
            <li><a href="#contact" class="landing-nav-link">Contact</a></li>
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
            <li><a href="#home" class="landing-nav-link" onclick="closeMobileMenu()">Home</a></li>
            <li><a href="#browse-books" class="landing-nav-link" onclick="closeMobileMenu()">Books</a></li>
            <li><a href="#contact" class="landing-nav-link" onclick="closeMobileMenu()">Contact</a></li>
        </ul>
    </aside>

    <!-- Hero Section -->
    <main class="hero-section">
        <div class="hero-content" id="home">
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

    <!-- Browse Books Section -->
    <section class="browse-section" id="browse-books">
        <div class="browse-header">
            <h2 class="browse-title">Browse Books</h2>
            <p class="browse-subtitle">Discover our vast collection of knowledge</p>
        </div>

        <div class="filter-bar">
            <select id="landing-category-filter" class="filter-select">
                <option value="">All Categories</option>
                <!-- JS Populated -->
            </select>
            
            <div class="search-container">
                <input type="text" id="landing-search-input" class="search-input-landing" placeholder="Search by title, author, or ISBN...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>

        <div id="landing-book-grid" class="landing-book-grid">
            <!-- JS Populated -->
            <div class="book-card-landing" style="height: 300px; grid-column: 1/-1; align-items: center; justify-content: center;">
                <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--text-perpuz);"></i>
            </div>
        </div>

        <div class="view-more-container">
            <a href="{{ route('books.index') }}" id="view-more-books-btn" class="view-more-main-btn">View More</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer" id="contact">
        <div class="footer-content">
            <div class="footer-brand">
                <div class="logo">
                     <i class="fas fa-book-reader" style="color: var(--danger);"></i> Perpuz
                </div>
                <p>
                    Jelajahi ribuan buku dan sumber pengetahuan dengan mudah. Perpustakaan digital masa depan dalam genggaman Anda.
                </p>
                
            </div>

            <div class="footer-links">
                <h4 class="footer-heading">Navigasi</h4>
                <ul>
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#browse-books">Koleksi Buku</a></li>
                    <li><a href="#" onclick="openLoginModal()">Login Member</a></li>
                    <li><a href="#" onclick="openRegisterModal()">Daftar Sekarang</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h4 class="footer-heading">Hubungi Kami</h4>
                <ul class="contact-info">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Jl. Mawar, Surabaya</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>+62 812 3456 7890</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>info@perpuz.id</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2024 Perpuz Digital Library. Hak Cipta Dilindungi.
        </div>
    </footer>

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

            // View More Button Interceptor
            const viewMoreBtn = document.getElementById('view-more-books-btn');
            if (viewMoreBtn) {
                viewMoreBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (localStorage.getItem('auth_token')) {
                        window.location.href = this.href;
                    } else {
                        openLoginModal();
                    }
                });
            }
            
            // Load Browse Section Data
            loadLandingCategories();
            loadLandingBooks();
            
            // Search Debounce
            let timeout;
            const searchInput = document.getElementById('landing-search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        loadLandingBooks(e.target.value, document.getElementById('landing-category-filter').value);
                    }, 500);
                });
            }
            
            // Category Filter
            const catFilter = document.getElementById('landing-category-filter');
            if (catFilter) {
                catFilter.addEventListener('change', function(e) {
                    loadLandingBooks(document.getElementById('landing-search-input').value, e.target.value);
                });
            }
        });

        // Fetch Categories
        async function loadLandingCategories() {
            try {
                const response = await fetch('/api/categories');
                const data = await response.json();
                const select = document.getElementById('landing-category-filter');
                
                if (data.success && select) {
                    data.data.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.name;
                        select.appendChild(option);
                    });
                }
            } catch(e) {
                console.error('Error loading categories', e);
            }
        }

        // Fetch Books
        async function loadLandingBooks(search = '', category = '') {
            const grid = document.getElementById('landing-book-grid');
            if (!grid) return;
            
            grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 3rem;"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--text-perpuz);"></i></div>';
            
            try {
                let url = `/api/books?limit=8`; // Limit to 8 as per design (2 rows of 4)
                if (search) url += `&search=${encodeURIComponent(search)}`;
                if (category) url += `&category_id=${category}`;
                
                const response = await fetch(url);
                const data = await response.json();
                const books = data.data.data;
                
                grid.innerHTML = '';
                
                if (books.length === 0) {
                    grid.innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-secondary);">
                            <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No books found matching your criteria.</p>
                        </div>
                    `;
                    return;
                }
                
                books.forEach(book => {
                    grid.innerHTML += `
                        <div class="book-card-landing">
                            <div class="book-cover-landing">
                                ${book.cover_image 
                                    ? `<img src="${book.cover_image}" alt="${book.title}">` 
                                    : `<div class="book-cover-placeholder"><i class="fas fa-book fa-3x" style="color: white; opacity:0.8;"></i></div>`
                                }
                            </div>
                            <div class="book-info-landing">
                                <h3 class="book-title-landing" title="${book.title}">${book.title}</h3>
                                <p class="book-author-landing">${book.author}</p>
                                <a href="/books/${book.id}" class="view-details-btn" onclick="handleBookDetailClick(event, this.href)">View Details</a>
                            </div>
                        </div>
                    `;
                });
                
            } catch (error) {
                console.error('Error loading books', error);
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--danger);">Failed to load books. Please try again later.</div>';
            }
        }

        function handleBookDetailClick(e, url) {
            e.preventDefault();
            if (localStorage.getItem('auth_token')) {
                window.location.href = url;
            } else {
                openLoginModal();
            }
        }

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
