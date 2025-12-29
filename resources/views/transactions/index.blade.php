@extends('layouts.app')

@section('content')
<div class="dashboard-content">
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 class="page-title">My Transactions</h1>
        <p class="page-subtitle">Manage your borrowed books and view history</p>
    </div>
    
    <!-- Filter Tabs -->
    <div class="tab-container">
        <button onclick="filterTransactions('all')" class="tab-btn active" id="tab-all">All</button>
        <button onclick="filterTransactions('borrowed')" class="tab-btn" id="tab-borrowed">Active Loans</button>
        <button onclick="filterTransactions('returned')" class="tab-btn" id="tab-returned">Returned</button>
    </div>
    
    <!-- Transactions List -->
    <div id="transactions-list">
        <div class="dashboard-card loading-card">
            <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--text-perpuz);"></i>
            <p>Loading transactions...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .tab-btn.active {
        color: var(--text-perpuz) !important;
        border-bottom-color: var(--text-perpuz) !important;
    }
</style>
<script>
    let currentFilter = 'all';
    
    document.addEventListener('DOMContentLoaded', function() {
        loadTransactions();
    });
    
    function filterTransactions(status) {
        currentFilter = status;
        
        // Update tabs
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(`tab-${status}`).classList.add('active');
        
        loadTransactions();
    }
    
    async function loadTransactions() {
        const container = document.getElementById('transactions-list');
        container.innerHTML = '<div class="dashboard-card loading-card"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--text-perpuz);"></i><p>Loading...</p></div>';
        
        try {
            let url = '/api/transactions';
            if (currentFilter !== 'all') {
                url += `?status=${currentFilter}`;
            }
            
            const response = await fetch(url + (url.includes('?') ? '&' : '?') + 'limit=50', {
                 headers: { 'Authorization': 'Bearer ' + localStorage.getItem('auth_token') }
            });
            const data = await response.json();
            
            if (data.data.data.length === 0) {
                container.innerHTML = `
                    <div class="dashboard-card empty-state">
                        <i class="fas fa-history" style="font-size: 3rem; color: var(--text-perpuz); margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3 style="color: var(--text-new);">No transactions found</h3>
                        <p style="color: #6b7280;">You haven't borrowed any books yet.</p>
                        <a href="{{ route('books.index') }}" class="dashboard-btn-primary" style="margin-top: 1rem;">Browse Books</a>
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.data.data.forEach(trx => {
                const statusColors = {
                    'borrowed': 'var(--warning)',
                    'returned': 'var(--success)',
                    'overdue': 'var(--danger)'
                };
                
                const statusLabels = {
                    'borrowed': 'Active Loan',
                    'returned': 'Returned',
                    'overdue': 'Overdue'
                };
                
                const returnButton = trx.status === 'borrowed' || trx.status === 'overdue' 
                    ? `<button onclick="returnBook(${trx.id})" class="return-btn">Return Book</button>`
                    : '';
                
                const borrowDate = new Date(trx.borrow_date).toLocaleDateString();
                const dueDate = new Date(trx.due_date).toLocaleDateString();
                const returnDate = trx.return_date ? new Date(trx.return_date).toLocaleDateString() : '-';
                
                html += `
                    <div class="transaction-card">
                        <div class="transaction-cover">
                            <i class="fas fa-book"></i>
                        </div>
                        
                        <div class="transaction-info">
                            <div class="transaction-header">
                                <div>
                                    <span class="badge" style="background: rgba(255,255,255,0.1); color: ${statusColors[trx.status]}; margin-bottom: 0.5rem; display: inline-block;">
                                        ${statusLabels[trx.status]}
                                    </span>
                                    <h3 class="transaction-title">${trx.book.title}</h3>
                                </div>
                                ${returnButton}
                            </div>
                            
                            <div class="transaction-dates">
                                <div class="transaction-date-item">
                                    <span class="transaction-date-label">Borrowed Date</span>
                                    <span class="transaction-date-value">${borrowDate}</span>
                                </div>
                                <div class="transaction-date-item">
                                    <span class="transaction-date-label">Due Date</span>
                                    <span class="transaction-date-value">${dueDate}</span>
                                </div>
                                <div class="transaction-date-item">
                                    <span class="transaction-date-label">Return Date</span>
                                    <span class="transaction-date-value">${returnDate}</span>
                                </div>
                            </div>
                            
                            ${trx.fine_amount > 0 ? `
                                <div class="transaction-fine">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Fine: Rp ${trx.fine_amount}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
        } catch (error) {
            console.error(error);
            container.innerHTML = '<div class="dashboard-card error-state">Failed to load transactions.</div>';
        }
    }
    
    async function returnBook(id) {
        if (!confirm('Are you sure you want to return this book?')) return;
        
        try {
            const response = await fetch(`/api/transactions/${id}/return`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (response.ok) {
                alert('Book returned successfully');
                loadTransactions();
            } else {
                alert(data.message || 'Failed to return book');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred');
        }
    }
</script>
@endpush
