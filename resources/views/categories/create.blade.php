@extends('layouts.app')
@section('content')
<div class="page-head">
    <h1>{{ $selectedParent ? 'New Subcategory' : 'New Main Category' }}</h1>
</div>

<div class="panel form-panel">
    <form method="post" action="{{ route('categories.store') }}">
        @csrf
        <div class="form-grid">
            <label>Name*
                <input name="name" required>
            </label>
            @if($selectedParent)
                <label>Parent Category
                    <select name="parent_id">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $selectedParent == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <input type="hidden" name="parent_id" value="">
            @endif
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Create {{ $selectedParent ? 'Subcategory' : 'Main Category' }}</button>
            <a href="{{ url()->previous() }}" class="btn-cancel">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Cancel
            </a>
        </div>
    </form>
</div>

<style>
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    gap: 12px;
}
.btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s;
}
.btn-cancel:hover {
    background: #fecaca;
    color: #b91c1c;
}
@media (max-width: 640px) {
    .form-actions {
        flex-direction: column;
        gap: 10px;
    }
    .form-actions .primary,
    .form-actions .btn-cancel {
        width: 100%;
        text-align: center;
        justify-content: center;
    }
}
</style>
@endsection