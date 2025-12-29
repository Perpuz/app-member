@extends('layouts.app')

@section('content')
<div class="dashboard-content">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Browse Books</h1>
            <p class="page-subtitle">Discover our vast collection of knowledge</p>
        </div>
        
        <div class="search-filter-bar">
            <select id="category-filter" class="filter-select-small">
                <option value="">All Categories</option>
                <!-- populated by JS -->
            </select>
            <div class="search-container-small">
                <input type="text" id="search-input" class="search-input-small" placeholder="Search by title, author, or ISBN...">
                <i class="fas fa-search search-icon-small"></i>
            </div>
        </div>
    </div>
    
    <!-- Books Grid -->
    <div id="books-grid" class="books-page-grid">
        <!-- Loading Skeleton -->
        @for($i = 0; $i < 8; $i++)
        <div class="books-page-card loading-state">
            <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--text-perpuz);"></i>
        </div>
        @endfor
    </div>
    
    <!-- Empty State (Hidden) -->
    <div id="empty-state" class="dashboard-card empty-state" style="display: none;">
        <i class="fas fa-search" style="font-size: 3rem; color: var(--text-perpuz); margin-bottom: 1rem; opacity: 0.5;"></i>
        <h3 style="color: var(--text-new);">No books found</h3>
        <p style="color: #6b7280;">Try adjusting your search or filters</p>
    </div>
    
    <!-- Pagination -->
    <div id="pagination" class="pagination-controls">
        <!-- Buttons injected by JS -->
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPage = 1;
    let currentSearch = '';
    let currentCategory = '';
    
    document.addEventListener('DOMContentLoaded', function() {
        loadCategories();
        loadBooks();
        
        // Search Debounce
        let timeout;
        document.getElementById('search-input').addEventListener('input', function(e) {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                currentSearch = e.target.value;
                currentPage = 1;
                loadBooks();
            }, 500);
        });
        
        // Category Filter
        document.getElementById('category-filter').addEventListener('change', function(e) {
            currentCategory = e.target.value;
            currentPage = 1;
            loadBooks();
        });
    });
    
    async function loadCategories() {
        try {
            const response = await fetch('/api/categories');
            const data = await response.json();
            
            const select = document.getElementById('category-filter');
            data.success && data.data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.name;
                select.appendChild(option);
            });
        } catch(e) { console.error(e); }
    }
    
    async function loadBooks() {
        const grid = document.getElementById('books-grid');
        const emptyState = document.getElementById('empty-state');
        const pagination = document.getElementById('pagination');
        
        grid.innerHTML = '<div class="dashboard-card loading-card" style="grid-column: 1/-1;"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--text-perpuz);"></i> <p>Loading books...</p></div>';
        emptyState.style.display = 'none';
        
        try {
            let url = `/api/books?page=${currentPage}`;
            if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;
            if (currentCategory) url += `&category_id=${currentCategory}`;
            
            const response = await fetch(url);
            const data = await response.json();
            const books = data.data.data;
            const meta = data.data; // pagination meta
            
            grid.innerHTML = '';
            
            if (books.length === 0) {
                emptyState.style.display = 'block';
                pagination.innerHTML = '';
                return;
            }
            
            books.forEach(book => {
                const available = book.available_copies > 0;
                
                grid.innerHTML += `
                    <div class="books-page-card">
                        <div class="book-page-cover">
                            ${
                                book.cover_image
                                ? `<img 
                                    src="${book.cover_image}" 
                                    alt="${book.title}" 
                                    style="width:100%; height:100%; object-fit:cover;"
                                >`
                                : `<div class="book-cover-placeholder">
                                        <i class="fas fa-book fa-3x"></i>
                                </div>`
                            }

                            <!-- Badge -->
                            <div class="book-badge">
                                ${available 
                                    ? '<span class="badge badge-success">Available</span>' 
                                    : '<span class="badge badge-danger">Out of Stock</span>'}
                            </div>
                        </div>
                        
                        <div class="book-page-info">
                            <h3 class="book-page-title">${book.title}</h3>
                            <p class="book-page-author">${book.author}</p>
                            
                            <a href="/books/${book.id}" class="book-detail-btn">
                                View Details
                            </a>
                        </div>
                    </div>
                `;
            });
            
            // Pagination
            renderPagination(meta);
            
        } catch (error) {
            console.error(error);
            grid.innerHTML = '<div class="dashboard-card error-state" style="grid-column: 1/-1;">Failed to load books.</div>';
        }
    }
    
    function renderPagination(meta) {
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';
        
        if (meta.last_page <= 1) return;
        
        // Prev
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.disabled = meta.current_page === 1;
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.onclick = () => { currentPage--; loadBooks(); };
        pagination.appendChild(prevBtn);
        
        // Page Info
        const info = document.createElement('span');
        info.className = 'pagination-info';
        info.innerHTML = `Page ${meta.current_page} of ${meta.last_page}`;
        pagination.appendChild(info);
        
        // Next
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.disabled = meta.current_page === meta.last_page;
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.onclick = () => { currentPage++; loadBooks(); };
        pagination.appendChild(nextBtn);
    }
</script>
@endpush
