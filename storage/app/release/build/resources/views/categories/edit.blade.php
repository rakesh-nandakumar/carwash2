@extends('layouts.app')
@section('content')
<div class="page-head">
    <h1>Edit Category</h1>
</div>

<div class="panel form-panel">
    <form method="post" action="{{ route('categories.update', $category) }}">
        @method('PUT')
        @csrf
        <div class="form-grid">
            <label>Name*
                <input name="name" value="{{ $category->name }}" required>
            </label>
            <label>Parent Category
                <select name="parent_id">
                    <option value="">None (Main Category)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $category->parent_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <button class="primary">Update Category</button>
    </form>
</div>
@endsection
