@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1">
    <a href="{{ route('books.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); margin-bottom: 2rem;">
        <i class="fas fa-arrow-left"></i> Back to Catalog
    </a>

    <div id="book-detail-container">
        <div class="card" style="text-align: center; padding: 4rem;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
        </div>
    </div>
</div>

<!-- Borrow Modal -->
<div id="borrow-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 400px; margin: 0;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Borrow Book</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Select loan duration (days):</p>
        
        <form id="borrow-form">
            <input type="hidden" id="borrow-book-id">
            <div class="form-group">
                <select id="borrow-duration" class="form-control">
                    <option value="3">3 Days</option>
                    <option value="7" selected>7 Days (1 Week)</option>
                    <option value="14">14 Days (2 Weeks)</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn" style="background: var(--bg-primary); flex: 1;" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Confirm Borrow</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const bookId = {{ $id }};
    
    document.addEventListener('DOMContentLoaded', function() {
        loadBookDetails();
        
        document.getElementById('borrow-form').addEventListener('submit', handleBorrow);
    });
    
    async function loadBookDetails() {
        const container = document.getElementById('book-detail-container');
        try {
            const response = await fetch(`/api/books/${bookId}`);
            const data = await response.json();
            
            if (!response.ok) {
                container.innerHTML = '<div class="alert alert-danger">Book not found</div>';
                return;
            }
            
            const book = data.data;
            const userStatus = data.user_status;
            
            let actionButton = '';
            
            if (userStatus.has_active_loan) {
                actionButton = `
                    <div class="alert alert-warning" style="margin-top: 1.5rem; background: rgba(245, 158, 11, 0.1); padding: 1rem; border-radius: 8px; border: 1px solid var(--warning); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>You currently have this book borrowed.</span>
                        <a href="{{ route('transactions.index') }}" style="margin-left: auto; text-decoration: underline; font-weight: 600;">View Transaction</a>
                    </div>
                `;
            } else if (book.available_copies > 0) {
                actionButton = `
                    <button onclick="openModal(${book.id})" class="btn btn-primary" style="margin-top: 1.5rem; width: 100%; font-size: 1.1rem; padding: 1rem;">
                        Borrow This Book
                    </button>
                `;
            } else {
                actionButton = `
                    <button disabled class="btn" style="margin-top: 1.5rem; width: 100%; background: var(--bg-primary); color: var(--text-secondary); cursor: not-allowed;">
                        Out of Stock
                    </button>
                `;
            }
            
            container.innerHTML = `
                <div class="grid grid-cols-3" style="gap: 2rem; align-items: start;">
                    <!-- Cover -->
                    <div style="aspect-ratio: 2/3; background: #2d3748; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 4rem;">
                        <i class="fas fa-book"></i>
                    </div>
                    
                    <!-- Details -->
                    <div class="card" style="grid-column: span 2; margin-bottom: 0;">
                        <div style="margin-bottom: 1rem;">
                            <span class="badge badge-primary">${book.category.name}</span>
                        </div>
                        
                        <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">${book.title}</h1>
                        <p style="font-size: 1.1rem; color: var(--accent); margin-bottom: 1.5rem;">${book.author}</p>
                        
                        <div class="grid grid-cols-2" style="gap: 1rem; margin-bottom: 2rem; background: var(--bg-primary); padding: 1rem; border-radius: 8px;">
                            <div>
                                <span style="color: var(--text-secondary); font-size: 0.9rem; display: block;">Publisher</span>
                                <span style="font-weight: 600;">${book.publisher}</span>
                            </div>
                            <div>
                                <span style="color: var(--text-secondary); font-size: 0.9rem; display: block;">Year</span>
                                <span style="font-weight: 600;">${book.publication_year}</span>
                            </div>
                            <div>
                                <span style="color: var(--text-secondary); font-size: 0.9rem; display: block;">ISBN</span>
                                <span style="font-weight: 600;">${book.isbn}</span>
                            </div>
                            <div>
                                <span style="color: var(--text-secondary); font-size: 0.9rem; display: block;">Availability</span>
                                <span style="font-weight: 600; color: ${book.available_copies > 0 ? 'var(--success)' : 'var(--danger)'}">
                                    ${book.available_copies} / ${book.total_copies} Copies
                                </span>
                            </div>
                        </div>
                        
                        <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Description</h3>
                        <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 1rem;">
                            ${book.description}
                        </p>

                        <!-- Rating Section -->
                        <div style="margin-bottom: 1.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                            <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Rate this Book</h3>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="display: flex; gap: 0.25rem; font-size: 1.5rem;">
                                    ${[1,2,3,4,5].map(star => `
                                        <i class="fas fa-star" 
                                           style="cursor: pointer; color: ${userStatus.rating && star <= userStatus.rating ? '#f59e0b' : '#4a5568'}; transition: color 0.2s;"
                                           onclick="rateBook(${star})">
                                        </i>
                                    `).join('')}
                                </div>
                                ${userStatus.rating ? `
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">(You rated: ${userStatus.rating})</span>
                                    <button onclick="unrateBook()" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; padding: 0.25rem 0.75rem; font-size: 0.8rem;">
                                        Remove
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                        
                        ${actionButton}
                    </div>
                </div>
            `;
            
        } catch(e) { console.error(e); }
    }
    
    function openModal(id) {
        document.getElementById('borrow-modal').style.display = 'flex';
        document.getElementById('borrow-book-id').value = id;
    }
    
    function closeModal() {
        document.getElementById('borrow-modal').style.display = 'none';
    }
    
    async function handleBorrow(e) {
        e.preventDefault();
        
        const bookId = document.getElementById('borrow-book-id').value;
        const duration = document.getElementById('borrow-duration').value;
        const btn = e.target.querySelector('button[type="submit"]');
        
        btn.disabled = true;
        btn.innerHTML = 'Processing...';
        
        try {
            const response = await fetch('/api/transactions/borrow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                },
                body: JSON.stringify({ book_id: bookId, duration: duration })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert('Book borrowed successfully!');
                window.location.href = '/transactions';
            } else {
                alert(data.message || 'Failed to borrow book');
                btn.disabled = false;
                btn.innerHTML = 'Confirm Borrow';
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred');
            btn.disabled = false;
        }
    }

    async function rateBook(score) {
        try {
            const response = await fetch(`/api/books/${bookId}/rate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                },
                body: JSON.stringify({ score: score })
            });

            if (response.ok) {
                loadBookDetails(); // Reload to show updated stars
            } else {
                alert('Failed to submit rating');
            }
        } catch (e) { console.error(e); }
    }

    async function unrateBook() {
        if(!confirm('Delete your rating?')) return;
        
        try {
            const response = await fetch(`/api/books/${bookId}/rate`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                }
            });

            if (response.ok) {
                loadBookDetails();
            } else {
                alert('Failed to remove rating');
            }
        } catch (e) { console.error(e); }
    }
</script>
@endpush
