@extends('layouts.app')

@section('content')
<script>
    // Client-side protection
    if (!localStorage.getItem('auth_token')) {
        window.location.href = '/';
    }
</script>

<div class="dashboard-content">
    <!-- Welcome Card -->
    <div class="dashboard-welcome-card">
        <h1 class="dashboard-welcome-title">Welcome back, <span id="welcome-name">Student</span>! 👋</h1>
        <p class="dashboard-welcome-text">Explore our digital collection of books and manage your library activities.</p>
    </div>
    
    <!-- Stats Row -->
    <div class="dashboard-stats-grid">
        <div class="dashboard-stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3 class="stat-label">Peminjaman Aktif</h3>
                    <div id="stat-active" class="stat-value">-</div>
                </div>
                <div class="stat-icon stat-icon-primary">
                    <i class="fas fa-book-reader"></i>
                </div>
            </div>
        </div>
        
        <div class="dashboard-stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3 class="stat-label">Total History</h3>
                    <div id="stat-history" class="stat-value">-</div>
                </div>
                <div class="stat-icon stat-icon-success">
                    <i class="fas fa-history"></i>
                </div>
            </div>
        </div>
        
        <div class="dashboard-stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3 class="stat-label">Denda</h3>
                    <div id="totalFines" class="stat-value">-</div>
                </div>
                <div class="stat-icon stat-icon-danger">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Loans Section (Full Width) -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2 class="section-title">Peminjaman Aktif</h2>
            <a href="{{ route('transactions.index') }}" class="section-link">View All</a>
        </div>
        
        <div id="active-loans-list">
            <div class="dashboard-card loading-card">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--text-perpuz); margin-bottom: 1rem;"></i>
                <p>Loading peminjaman aktif...</p>
            </div>
        </div>
    </div>
    
    <!-- New Books Section (Full Width) -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2 class="section-title">Recommendation</h2>
            <a href="{{ route('books.index') }}" class="section-link">Browse All</a>
        </div>
        
        <div id="new-books-list" class="dashboard-books-grid">
            <!-- Books populated by JS -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (user.name) {
            document.getElementById('welcome-name').textContent = user.name.split(' ')[0];
        }
        
        // Fetch Dashboard Data
        loadActiveLoans();
        loadNewBooks();
        loadStats(); // We'll implement a simple calculation from transaction list
    });
    
    async function loadActiveLoans() {
        const container = document.getElementById('active-loans-list');
        try {
            const response = await fetch('/api/transactions?status=borrowed');
            const data = await response.json();
            
            if (data.data.data.length === 0) {
                container.innerHTML = `
                    <div class="dashboard-card empty-state">
                        <i class="fas fa-book-open" style="font-size: 2.5rem; color: var(--text-perpuz); margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p style="color: var(--text-new); margin-bottom: 1rem;">Kamu belum memiliki peminjaman aktif.</p>>
                        <a href="{{ route('books.index') }}" class="dashboard-btn-primary">Pinjam Buku</a>
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.data.data.forEach(loan => {
                const dueDate = new Date(loan.due_date);
                const isOverdue = new Date() > dueDate;
                const daysLeft = Math.ceil((dueDate - new Date()) / (1000 * 60 * 60 * 24));
                
                let coverHtml = `<div class="loan-card-cover"><i class="fas fa-book"></i></div>`;
                if (loan.book.cover_image) {
                     coverHtml = `<div class="loan-card-cover" style="background-image: url('${loan.book.cover_image}'); background-size: cover; background-position: center;"></div>`;
                }

                html += `
                    <div class="dashboard-loan-card">
                        ${coverHtml}
                        <div class="loan-card-info">
                            <h4 class="loan-card-title">${loan.book.title}</h4>
                            <p class="loan-card-due">Due: ${new Date(loan.due_date).toLocaleDateString()}</p>
                            ${isOverdue 
                                ? `<span class="badge badge-danger">Overdue ${Math.abs(daysLeft)} days</span>` 
                                : `<span class="badge badge-success">${daysLeft} days left</span>`
                            }
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
            
        } catch (error) {
            console.error('Error loading loans', error);
            container.innerHTML = '<div class="dashboard-card error-state">Failed to load active peminjaman.</div>';
        }
    }
    
    async function loadNewBooks() {
        const container = document.getElementById('new-books-list');
        // Override style to Grid (Vertical Wrapper)
        container.style.display = 'grid';
        container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(140px, 1fr))';
        container.style.gap = '1.5rem';
        container.style.overflowX = 'visible';
        
        try {
            // Update Limit to 10
            const response = await fetch('/api/books?limit=10');
            const data = await response.json();
            
            let html = '';
            // Display up to 10 books
            const books = data.data.data.slice(0, 10);
            
            if (books.length === 0) {
                 container.innerHTML = '<p>No books available.</p>';
                 return;
            }

            books.forEach(book => {
                // Use Cover Image if available
                let coverHtml = `<div class="book-card-cover"><i class="fas fa-book"></i></div>`;
                if (book.cover_image && book.cover_image.startsWith('http')) {
                     coverHtml = `<div class="book-card-cover" style="background-image: url('${book.cover_image}'); background-size: cover; background-position: center;"></div>`;
                }

                html += `
                    <a href="/books/${book.id}" class="dashboard-book-card" style="text-decoration: none; color: inherit; display: flex; flex-direction: column;">
                        ${coverHtml}
                        <h4 class="book-card-title" style="margin-top: 0.5rem; font-size: 0.95rem; font-weight: 600; line-height: 1.3;">${book.title}</h4>
                        <p class="book-card-author" style="font-size: 0.85rem; color: #666; margin-bottom: 0;">${book.author}</p>
                    </a>
                `;
            });
            container.innerHTML = html;
        } catch (error) {
            console.log('Error loading books', error);
            container.innerHTML = '<p>Failed to load books.</p>';
        }
    }
    
    async function loadStats() {
        try {
            // Get active loans count
            const loansRes = await fetch('/api/transactions?status=borrowed&limit=100');
            const loansData = await loansRes.json();
            document.getElementById('stat-active').textContent = loansData.data.total || 0;
            
            // Get all history count and Calculate Fines
            const allRes = await fetch('/api/transactions?limit=100');
            const allData = await allRes.json();
            document.getElementById('stat-history').textContent = allData.data.total || 0;
            
            let totalFines = 0;
            
            // 1. Sum up finalized fines (returned books)
            if (allData.data.data) {
                allData.data.data.forEach(trx => {
                    totalFines += parseFloat(trx.fine_amount || 0);
                });
            }

            // 2. Sum up estimated fines (active overdue books)
            if (loansData.data.data) {
                const today = new Date();
                today.setHours(0,0,0,0);
                
                loansData.data.data.forEach(trx => {
                    if (trx.status === 'borrowed' || trx.status === 'overdue') {
                        const due = new Date(trx.due_date);
                        due.setHours(0,0,0,0);
                        
                        // If Overdue and NOT returned (fine_amount likely 0)
                        if (today > due && parseFloat(trx.fine_amount || 0) === 0) {
                            const diffTime = Math.abs(today - due);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                            totalFines += diffDays * 5000;
                        }
                    }
                });
            }
            
            document.getElementById('stat-fines').textContent = 'Rp ' + totalFines.toLocaleString('id-ID');
        } catch (e) {
            console.log(e);
            document.getElementById('stat-fines').textContent = 'Rp -';
        }
    }
</script>
@endpush
