@extends('layouts.app')

@section('content')
<script>
    // Client-side protection
    if (!localStorage.getItem('auth_token')) {
        window.location.href = '/';
    }
</script>

<div class="grid grid-cols-1">
    <div class="card" style="background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-secondary) 100%);">
        <h1 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Welcome back, <span id="welcome-name">Student</span>! 👋</h1>
        <p style="color: var(--text-secondary);">Explore our digital collection of books and manage your library activities.</p>
    </div>
    
    <!-- Stats Row -->
    <div class="grid grid-cols-3" style="gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem;">Active Loans</h3>
                    <div id="stat-active" style="font-size: 2rem; font-weight: 700;">-</div>
                </div>
                <div style="background: rgba(59, 130, 246, 0.1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="fas fa-book-reader" style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem;">Total History</h3>
                    <div id="stat-history" style="font-size: 2rem; font-weight: 700;">-</div>
                </div>
                <div style="background: rgba(16, 185, 129, 0.1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--success);">
                    <i class="fas fa-history" style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem;">Fines</h3>
                    <div id="stat-fines" style="font-size: 2rem; font-weight: 700;">-</div>
                </div>
                <div style="background: rgba(239, 68, 68, 0.1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--danger);">
                    <i class="fas fa-exclamation-circle" style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-2" style="gap: 2rem;">
        <!-- Active Loans -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 600;">Active Loans</h2>
                <a href="{{ route('transactions.index') }}" style="color: var(--accent); font-size: 0.9rem;">View All</a>
            </div>
            
            <div id="active-loans-list">
                <div class="card" style="text-align: center; padding: 3rem 1rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--accent); margin-bottom: 1rem;"></i>
                    <p>Loading active loans...</p>
                </div>
            </div>
        </div>
        
        <!-- New Arrivals -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 600;">New Books</h2>
                <a href="{{ route('books.index') }}" style="color: var(--accent); font-size: 0.9rem;">Browse All</a>
            </div>
            
            <div id="new-books-list" class="grid grid-cols-2" style="gap: 1rem;">
                <!-- Books populated by JS -->
            </div>
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
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-book-open" style="font-size: 2rem; color: var(--text-secondary); margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p style="color: var(--text-secondary);">You don't have any active loans.</p>
                        <a href="{{ route('books.index') }}" class="btn btn-primary" style="margin-top: 1rem;">Borrow a Book</a>
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
                    <div class="card" style="margin-bottom: 1rem; display: flex; gap: 1rem;">
                        <div style="width: 60px; height: 80px; background: #334155; border-radius: 4px; flex-shrink: 0; overflow: hidden;">
                             <!-- Placeholder for cover -->
                             <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #2d3748; color: #64748b;">
                                <i class="fas fa-book"></i>
                             </div>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-weight: 600; margin-bottom: 0.25rem;">${loan.book.title}</h4>
                            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem;">Due: ${new Date(loan.due_date).toLocaleDateString()}</p>
                            
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
            container.innerHTML = '<div class="card text-center text-danger">Failed to load active loans.</div>';
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
                    <a href="/books/${book.id}" class="card" style="padding: 1rem; transition: transform 0.2s; display: block; margin-bottom: 0;">
                        <div style="aspect-ratio: 2/3; background: #2d3748; border-radius: 4px; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: center; color: #64748b;">
                            <i class="fas fa-book" style="font-size: 1.5rem;"></i>
                        </div>
                        <h4 style="font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${book.title}</h4>
                        <p style="color: var(--text-secondary); font-size: 0.8rem;">${book.author}</p>
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
