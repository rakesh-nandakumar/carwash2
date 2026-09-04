@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Categories</h1>
    </div>
    <a class="primary" href="{{ route('categories.create') }}">+ New Main Category</a>
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
</div>

{{ $categories->links() }}

<style>
/* Desktop list stays normal */
.categories-list {
    display: block;
}

.categories-cards {
    display: none;
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
@endsection