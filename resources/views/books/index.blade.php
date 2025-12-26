@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem;">Browse Books</h1>
            <p style="color: var(--text-secondary);">Discover our vast collection of knowledge</p>
        </div>
        
        <div style="display: flex; gap: 1rem; flex: 1; max-width: 600px;">
            <select id="category-filter" class="form-control" style="width: 180px;">
                <option value="">All Categories</option>
                <!-- populated by JS -->
            </select>
            <div style="flex: 1; position: relative;">
                <input type="text" id="search-input" class="form-control" placeholder="Search by title, author, or ISBN...">
                <i class="fas fa-search" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
            </div>
        </div>
    </div>
    
    <!-- Books Grid -->
    <div id="books-grid" class="grid grid-cols-4" style="gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Loading Skeleton -->
        @for($i = 0; $i < 4; $i++)
        <div class="card" style="height: 300px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
        </div>
        @endfor
    </div>
    
    <!-- Empty State (Hidden) -->
    <div id="empty-state" class="card" style="text-align: center; padding: 3rem; display: none;">
        <i class="fas fa-search" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem; opacity: 0.5;"></i>
        <h3>No books found</h3>
        <p style="color: var(--text-secondary);">Try adjusting your search or filters</p>
    </div>
    
    <!-- Pagination -->
    <div id="pagination" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1rem;">
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
        
        grid.innerHTML = '<div class="card" style="grid-column: 1/-1; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
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
                    <div class="card" style="margin-bottom: 0; padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                        <div style="aspect-ratio: 2/3; background: #2d3748; position: relative;">
                            <!-- Placeholder -->
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 2rem;">
                                <i class="fas fa-book"></i>
                            </div>
                            
                            <!-- Badges -->
                            <div style="position: absolute; top: 0.5rem; right: 0.5rem;">
                                ${available 
                                    ? '<span class="badge badge-success">Available</span>' 
                                    : '<span class="badge badge-danger">Out of Stock</span>'}
                            </div>
                        </div>
                        
                        <div style="padding: 1rem; flex: 1; display: flex; flex-direction: column;">
                            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${book.title}</h3>
                            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem;">${book.author}</p>
                            
                            <div style="margin-top: auto; padding-top: 1rem;">
                                <a href="/books/${book.id}" class="btn btn-primary" style="width: 100%; font-size: 0.85rem; padding: 0.5rem;">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            // Pagination
            renderPagination(meta);
            
        } catch (error) {
            console.error(error);
            grid.innerHTML = '<div class="card text-center text-danger" style="grid-column: 1/-1;">Failed to load books.</div>';
        }
    }
    
    function renderPagination(meta) {
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';
        
        if (meta.last_page <= 1) return;
        
        // Prev
        const prevBtn = document.createElement('button');
        prevBtn.className = 'btn';
        prevBtn.style.background = 'var(--bg-secondary)';
        prevBtn.disabled = meta.current_page === 1;
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.onclick = () => { currentPage--; loadBooks(); };
        pagination.appendChild(prevBtn);
        
        // Page Info
        const info = document.createElement('span');
        info.style.padding = '0.75rem';
        info.innerHTML = `Page ${meta.current_page} of ${meta.last_page}`;
        pagination.appendChild(info);
        
        // Next
        const nextBtn = document.createElement('button');
        nextBtn.className = 'btn';
        nextBtn.style.background = 'var(--bg-secondary)';
        nextBtn.disabled = meta.current_page === meta.last_page;
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.onclick = () => { currentPage++; loadBooks(); };
        pagination.appendChild(nextBtn);
    }
</script>
@endpush
