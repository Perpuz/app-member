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
                    <h3 class="stat-label">Active Loans</h3>
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
                    <h3 class="stat-label">Fines</h3>
                    <div id="stat-fines" class="stat-value">-</div>
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
            <h2 class="section-title">Active Loans</h2>
            <a href="{{ route('transactions.index') }}" class="section-link">View All</a>
        </div>
        
        <div id="active-loans-list">
            <div class="dashboard-card loading-card">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--text-perpuz); margin-bottom: 1rem;"></i>
                <p>Loading active loans...</p>
            </div>
        </div>
    </div>
    
    <!-- New Books Section (Full Width) -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2 class="section-title">New Books</h2>
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
                        <p style="color: var(--text-new); margin-bottom: 1rem;">You don't have any active loans.</p>
                        <a href="{{ route('books.index') }}" class="dashboard-btn-primary">Borrow a Book</a>
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.data.data.forEach(loan => {
                const dueDate = new Date(loan.due_date);
                const isOverdue = new Date() > dueDate;
                const daysLeft = Math.ceil((dueDate - new Date()) / (1000 * 60 * 60 * 24));
                
                html += `
                    <div class="dashboard-loan-card">
                        <div class="loan-card-cover">
                            <i class="fas fa-book"></i>
                        </div>
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
            container.innerHTML = '<div class="dashboard-card error-state">Failed to load active loans.</div>';
        }
    }
    
    async function loadNewBooks() {
        const container = document.getElementById('new-books-list');
        try {
            const response = await fetch('/api/books?limit=4');
            const data = await response.json();
            
            let html = '';
            data.data.data.slice(0, 4).forEach(book => {
                html += `
                    <a href="/books/${book.id}" class="dashboard-book-card">
                        <div class="book-card-cover">
                            <i class="fas fa-book"></i>
                        </div>
                        <h4 class="book-card-title">${book.title}</h4>
                        <p class="book-card-author">${book.author}</p>
                    </a>
                `;
            });
            container.innerHTML = html;
        } catch (error) {
            console.log('Error loading books', error);
        }
    }
    
    async function loadStats() {
        // Since we don't have a dedicated stats endpoint yet, we fetch lists
        // In a real app we would add /api/member/stats
        try {
            // Get active loans count
            const loansRes = await fetch('/api/transactions?status=borrowed');
            const loansData = await loansRes.json();
            document.getElementById('stat-active').textContent = loansData.data.total || 0;
            
            // Get all history count (approximate)
            const allRes = await fetch('/api/transactions');
            const allData = await allRes.json();
            document.getElementById('stat-history').textContent = allData.data.total || 0;
            
            // Fines (placeholder)
            document.getElementById('stat-fines').textContent = 'Rp 0';
        } catch (e) {
            console.log(e);
        }
    }
</script>
@endpush
