@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Categories</h1>
    </div>
    <a class="primary" href="{{ route('categories.create') }}">+ New Main Category</a>
</div>

<div class="search">
    <input id="categorySearch" placeholder="Search categories..." oninput="filterCategories()">
    <button class="filter-btn" onclick="openFilterModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
    </button>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Filter Categories</h2>
            <button class="modal-close" onclick="closeFilterModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="filter-section">
                <label>Category Type</label>
                <select id="typeFilter" onchange="applyFilters()">
                    <option value="">All Types</option>
                    <option value="main">Main Categories</option>
                    <option value="sub">Subcategories</option>
                </select>
            </div>
            <div class="filter-section">
                <label>Subcategory Count</label>
                <select id="countFilter" onchange="applyFilters()">
                    <option value="">All Counts</option>
                    <option value="none">No Subcategories</option>
                    <option value="has">Has Subcategories</option>
                    <option value="multiple">Multiple (2+)</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="secondary" onclick="clearFilters()">Clear Filters</button>
            <button class="primary" onclick="closeFilterModal()">Apply</button>
        </div>
    </div>
</div>

<div class="panel">
    <!-- Desktop List -->
    <div class="categories-list">
        @forelse($categories as $category)
            <div class="listrow">
                <div style="flex: 1;">
                    <b>{{ $category->name }}</b>
                    <span>{{ $category->children->count() }} subcategories</span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('categories.create') }}?parent_id={{ $category->id }}" class="secondary">+ Add Subcategory</a>
                    <a href="{{ route('categories.edit', $category) }}" class="secondary">Edit</a>
                </div>
            </div>
            @if($category->children->count() > 0)
                @foreach($category->children as $child)
                    <div class="listrow" style="padding-left: 40px;">
                        <div style="flex: 1;">
                            <b>↳ {{ $child->name }}</b>
                            <span>Subcategory</span>
                        </div>
                        <a href="{{ route('categories.edit', $child) }}" class="secondary">Edit</a>
                    </div>
                @endforeach
            @endif
        @empty
            <p class="empty">No categories found. Create main categories first.</p>
        @endforelse
    </div>

    <!-- Mobile Cards -->
    <div class="categories-cards">
        @forelse($categories as $category)
            <div class="category-card">
                <div class="card-top">
                    <div class="card-name">
                        <strong>{{ $category->name }}</strong>
                        <small>{{ $category->children->count() }} subcategories</small>
                    </div>
                </div>

                <div class="card-actions">
                    <a href="{{ route('categories.create') }}?parent_id={{ $category->id }}" class="btn-secondary">+ Add Sub</a>
                    <a href="{{ route('categories.edit', $category) }}" class="btn-secondary">Edit</a>
                </div>

                @if($category->children->count() > 0)
                    <div class="subcategories">
                        @foreach($category->children as $child)
                            <div class="subcategory-row">
                                <span class="sub-name">↳ {{ $child->name }}</span>
                                <a href="{{ route('categories.edit', $child) }}" class="sub-edit">Edit</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="empty-state">No categories found. Create main categories first.</div>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $categories->links() }}
    </div>
</div>

<style>
/* Desktop list stays normal */
.categories-list {
    display: block;
}

.categories-cards {
    display: none;
}

/* Search bar styling */
.search {
    display: flex;
    gap: 8px;
    position: relative;
    align-items: center;
}

.search input {
    flex: 1;
    height: 42px;
    box-sizing: border-box;
    padding: 0 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
}

.filter-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 14px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    height: 38px;
    box-sizing: border-box;
}

.filter-btn:hover {
    background: #d1d5db;
    border-color: #6b7280;
    color: #111827;
}

.filter-btn:active {
    background: #0a1f33;
    border-color: #0a1f33;
    color: white;
    transform: translateY(1px);
}

.filter-btn svg {
    width: 16px;
    height: 16px;
}

/* Modal styling */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5000;
    padding: 20px;
}

.modal-box {
    background: rgba(10, 31, 51, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    width: 100%;
    max-width: 400px;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 25px 60px rgba(0,0,0,.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
}

.modal-close {
    border: none;
    background: none;
    font-size: 28px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background 0.15s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.modal-body {
    margin-bottom: 20px;
    overflow: visible;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-footer button {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.modal-footer .secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
}

.modal-footer .secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.modal-footer .primary {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.9);
    color: #0a1f33;
}

.modal-footer .primary:hover {
    background: #ffffff;
    border-color: #ffffff;
}

.filter-section {
    margin-bottom: 12px;
}

.filter-section:last-child {
    margin-bottom: 0;
}

.filter-section label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-section select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
    cursor: pointer;
}

.filter-section select:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.filter-section select option {
    background: #0a1f33;
    color: #ffffff;
}

.clear-filters {
    width: 100%;
    padding: 8px 12px;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    margin-top: 12px;
    transition: all 0.15s ease;
}

.clear-filters:hover {
    background: #e5e7eb;
    color: #374151;
}

/* Pagination styling */
.pagination-wrap {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.pagination-wrap nav {
    display: flex;
    justify-content: center;
}

.pagination-wrap .pagination,
.pagination-wrap nav > div {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination-wrap a,
.pagination-wrap span {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none !important;
    color: #374151;
    background: #fff;
    border: 1px solid #e5e7eb;
    transition: all 0.15s ease;
    line-height: 1;
}

.pagination-wrap a:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
}

.pagination-wrap span[aria-current="page"],
.pagination-wrap .active span,
.pagination-wrap [aria-current="page"] span {
    background: #111827 !important;
    color: #fff !important;
    border-color: #111827 !important;
    font-weight: 600;
}

.pagination-wrap span[aria-disabled="true"],
.pagination-wrap .disabled span {
    color: #9ca3af !important;
    background: #f9fafb !important;
    border-color: #e5e7eb !important;
    cursor: not-allowed;
    opacity: 0.7;
}

.pagination-wrap svg,
.pagination-wrap .pagination svg,
nav[role="navigation"] svg {
    width: 16px !important;
    height: 16px !important;
    max-width: 16px !important;
    max-height: 16px !important;
}

.pagination-wrap a[rel="prev"],
.pagination-wrap a[rel="next"] {
    font-weight: 500;
    padding: 0 14px;
}

/* ========== MOBILE ONLY ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head a.primary {
        width: 100%;
        text-align: center;
    }

    .search {
        position: relative;
    }

    .search input {
        height: 40px;
    }

    .filter-btn {
        padding: 0 12px;
        font-size: 13px;
        height: 40px;
    }

    .modal-box {
        max-width: 90%;
        padding: 20px;
    }

    /* Hide desktop list */
    .categories-list {
        display: none;
    }

    /* Show cards */
    .categories-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .category-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .card-top {
        margin-bottom: 12px;
    }

    .card-name strong {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }

    .card-name small {
        font-size: 12px;
        color: #6b7280;
    }

    .card-actions {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .card-actions .btn-secondary {
        flex: 1;
        text-align: center;
        padding: 8px 10px;
        border-radius: 8px;
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid #e5e7eb;
    }

    .subcategories {
        border-top: 1px solid #f3f4f6;
        padding-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .subcategory-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
    }

    .sub-name {
        font-size: 13.5px;
        color: #374151;
        font-weight: 500;
    }

    .sub-edit {
        font-size: 12px;
        color: #6b7280;
        text-decoration: none;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
    }

    .empty-state {
        text-align: center;
        padding: 30px 16px;
        color: #9ca3af;
        font-size: 14px;
        grid-column: 1 / -1;
    }
}
</style>

<script>
function filterCategories() {
    applyFilters();
}

function openFilterModal() {
    document.getElementById('filterModal').style.display = 'flex';
}

function closeFilterModal() {
    document.getElementById('filterModal').style.display = 'none';
}

function applyFilters() {
    const query = document.getElementById('categorySearch').value.toLowerCase().trim();
    const typeFilter = document.getElementById('typeFilter').value;
    const countFilter = document.getElementById('countFilter').value;
    const listRows = document.querySelectorAll('.categories-list .listrow');
    const mobileCards = document.querySelectorAll('.category-card');

    // Filter desktop list rows
    listRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const paddingLeft = row.style.paddingLeft || '';
        const isSubcategory = paddingLeft.includes('40px');
        const spanElement = row.querySelector('span');
        const subcategoryCount = spanElement ? parseInt(spanElement.textContent) || 0 : 0;
        
        let matchesSearch = text.includes(query);
        let matchesType = true;
        let matchesCount = true;

        if (typeFilter === 'main') {
            matchesType = !isSubcategory;
        } else if (typeFilter === 'sub') {
            matchesType = isSubcategory;
        }

        if (countFilter && !isSubcategory) {
            if (countFilter === 'none') {
                matchesCount = subcategoryCount === 0;
            } else if (countFilter === 'has') {
                matchesCount = subcategoryCount > 0;
            } else if (countFilter === 'multiple') {
                matchesCount = subcategoryCount >= 2;
            }
        } else if (countFilter && isSubcategory) {
            matchesCount = false; // Subcategories don't have subcategory counts
        }

        row.style.display = (matchesSearch && matchesType && matchesCount) ? '' : 'none';
    });

    // Filter mobile cards
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const cardName = card.querySelector('.card-name small')?.textContent || '';
        const subcategoryCount = parseInt(cardName) || 0;
        const hasSubcategories = card.querySelector('.subcategories');
        
        let matchesSearch = text.includes(query);
        let matchesType = true;
        let matchesCount = true;

        if (typeFilter === 'main') {
            matchesType = !hasSubcategories;
        } else if (typeFilter === 'sub') {
            matchesType = false; // Mobile cards only show main categories
        }

        if (countFilter) {
            if (countFilter === 'none') {
                matchesCount = subcategoryCount === 0;
            } else if (countFilter === 'has') {
                matchesCount = subcategoryCount > 0;
            } else if (countFilter === 'multiple') {
                matchesCount = subcategoryCount >= 2;
            }
        }

        card.style.display = (matchesSearch && matchesType && matchesCount) ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('typeFilter').value = '';
    document.getElementById('countFilter').value = '';
    applyFilters();
}

// Close modal when clicking outside
document.getElementById('filterModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeFilterModal();
    }
});
</script>
@endsection