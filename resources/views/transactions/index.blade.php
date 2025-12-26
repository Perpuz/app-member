@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem;">My Transactions</h1>
        <p style="color: var(--text-secondary);">Manage your borrowed books and view history</p>
    </div>
    
    <!-- Filter Tabs -->
    <div style="display: flex; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem;">
        <button onclick="filterTransactions('all')" class="tab-btn active" id="tab-all" style="padding: 1rem; color: var(--text-secondary); background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-weight: 500;">All</button>
        <button onclick="filterTransactions('borrowed')" class="tab-btn" id="tab-borrowed" style="padding: 1rem; color: var(--text-secondary); background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-weight: 500;">Active Loans</button>
        <button onclick="filterTransactions('returned')" class="tab-btn" id="tab-returned" style="padding: 1rem; color: var(--text-secondary); background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-weight: 500;">Returned</button>
    </div>
    
    <!-- Transactions List -->
    <div id="transactions-list" class="grid grid-cols-1">
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .tab-btn.active {
        color: var(--accent) !important;
        border-bottom-color: var(--accent) !important;
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
        container.innerHTML = '<div class="card" style="text-align: center; padding: 3rem;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        
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
                    <div class="card" style="text-align: center; padding: 3rem;">
                        <i class="fas fa-history" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No transactions found</h3>
                        <p style="color: var(--text-secondary);">You haven't borrowed any books yet.</p>
                        <a href="{{ route('books.index') }}" class="btn btn-primary" style="margin-top: 1rem;">Browse Books</a>
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
                    ? `<button onclick="returnBook(${trx.id})" class="btn" style="background: var(--bg-primary); border: 1px solid var(--border-color); font-size: 0.9rem;">Return Book</button>`
                    : '';
                
                const borrowDate = new Date(trx.borrow_date).toLocaleDateString();
                const dueDate = new Date(trx.due_date).toLocaleDateString();
                const returnDate = trx.return_date ? new Date(trx.return_date).toLocaleDateString() : '-';
                
                html += `
                    <div class="card" style="padding: 1.5rem; display: flex; gap: 1.5rem; flex-wrap: wrap;">
                        <div style="width: 80px; height: 100px; background: #334155; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0;">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                        
                        <div style="flex: 1; min-width: 250px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <div>
                                    <span class="badge" style="background: rgba(255,255,255,0.05); color: ${statusColors[trx.status]}; margin-bottom: 0.5rem; display: inline-block;">
                                        ${statusLabels[trx.status]}
                                    </span>
                                    <h3 style="font-size: 1.2rem; font-weight: 600;">${trx.book.title}</h3>
                                </div>
                                ${returnButton}
                            </div>
                            
                            <div class="grid grid-cols-3" style="gap: 1rem; margin-top: 1rem; background: var(--bg-primary); padding: 1rem; border-radius: 8px;">
                                <div>
                                    <span style="color: var(--text-secondary); font-size: 0.8rem; display: block;">Borrowed Date</span>
                                    <span style="font-weight: 600;">${borrowDate}</span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary); font-size: 0.8rem; display: block;">Due Date</span>
                                    <span style="font-weight: 600;">${dueDate}</span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary); font-size: 0.8rem; display: block;">Return Date</span>
                                    <span style="font-weight: 600;">${returnDate}</span>
                                </div>
                            </div>
                            
                            ${trx.fine_amount > 0 ? `
                                <div style="margin-top: 1rem; color: var(--danger); font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
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
            container.innerHTML = '<div class="card text-center text-danger">Failed to load transactions.</div>';
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
