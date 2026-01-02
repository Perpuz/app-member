@extends('layouts.app')

@section('content')
<div class="dashboard-content">
    <a href="{{ route('books.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Catalog
    </a>

    <div id="book-detail-container">
        <div class="dashboard-card loading-card" style="padding: 4rem;">
            <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--text-perpuz);"></i>
            <p>Loading book details...</p>
        </div>
    </div>
</div>

<!-- Borrow Modal -->
<div id="borrow-modal" class="modal-overlay">
    <div class="book-detail-modal">
        <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--text-new); margin-bottom: 1rem;">Borrow Book</h2>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">Select loan duration (days):</p>
        
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
                <button type="button" class="return-btn" style="flex: 1;" onclick="closeModal()">Cancel</button>
                <button type="submit" class="dashboard-btn-primary" style="flex: 1;">Confirm Borrow</button>
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
                container.innerHTML = '<div class="dashboard-card error-state">Book not found</div>';
                return;
            }
            
            const book = data.data;
            const userStatus = data.user_status;
            
            let actionButton = '';
            
            if (userStatus.has_active_loan) {
                actionButton = `
                    <div class="alert-info" style="margin-top: 1.5rem;">
                        <i class="fas fa-info-circle"></i>
                        <span>You currently have this book borrowed.</span>
                        <a href="{{ route('transactions.index') }}" style="margin-left: auto; text-decoration: underline; font-weight: 600; color: var(--text-perpuz);">View Transaction</a>
                    </div>
                `;
            } else if (book.available_copies > 0) {
                actionButton = `
                    <button onclick="openModal(${book.id})" class="dashboard-btn-primary" style="margin-top: 1.5rem; width: 100%; font-size: 1.1rem; padding: 1rem;">
                        Borrow This Book
                    </button>
                `;
            } else {
                actionButton = `
                    <button disabled class="book-btn-disabled">
                        Out of Stock
                    </button>
                `;
            }
            
            container.innerHTML = `
                <div class="book-detail-layout">
                    <!-- Cover -->
                    <div class="book-detail-cover" ${book.cover_image ? `style="background-image: url('${book.cover_image}'); background-size: cover; background-position: center;"` : ''}>
                        ${book.cover_image ? '' : '<i class="fas fa-book"></i>'}
                    </div>
                    
                    <!-- Details -->
                    <div class="book-detail-card">
                        <div style="margin-bottom: 1rem;">
                            <span class="badge badge-success">${book.category.name}</span>
                        </div>
                        
                        <h1 class="book-detail-title">${book.title}</h1>
                        <p class="book-detail-author">${book.author}</p>
                        
                        <div class="book-detail-meta-grid">
                            <div class="book-meta-item">
                                <span class="book-meta-label">Publisher</span>
                                <span class="book-meta-value">${book.publisher}</span>
                            </div>
                            <div class="book-meta-item">
                                <span class="book-meta-label">Year</span>
                                <span class="book-meta-value">${book.publication_year}</span>
                            </div>
                            <div class="book-meta-item">
                                <span class="book-meta-label">ISBN</span>
                                <span class="book-meta-value">${book.isbn}</span>
                            </div>
                            <div class="book-meta-item">
                                <span class="book-meta-label">Availability</span>
                                <span style="font-weight: 600; color: ${book.available_copies > 0 ? 'var(--success)' : 'var(--danger)'}">
                                    ${book.available_copies} / ${book.total_copies} Copies
                                </span>
                            </div>
                        </div>
                        
                        <h3 class="book-section-title">Description</h3>
                        <p class="book-description">
                            ${book.description}
                        </p>

                        <!-- Rating Section -->
                        <div class="book-rating-section">
                            <h3 class="book-section-title">Rate this Book</h3>
                            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                <div class="star-rating">
                                    ${[1,2,3,4,5].map(star => `
                                        <i class="fas fa-star" 
                                           style="cursor: pointer; color: ${userStatus.rating && star <= userStatus.rating ? '#f59e0b' : '#d1d5db'}; transition: color 0.2s;"
                                           onclick="rateBook(${star})">
                                        </i>
                                    `).join('')}
                                </div>
                                ${userStatus.rating ? `
                                    <span style="font-size: 0.9rem; color: #6b7280;">(You rated: ${userStatus.rating})</span>
                                    <button onclick="unrateBook()" class="remove-rating-btn">
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
