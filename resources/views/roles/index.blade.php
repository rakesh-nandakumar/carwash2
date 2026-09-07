@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Roles</h1>
        <p>Manage roles and their permissions</p>
    </div>

    @if(auth()->user()->hasPermissionTo('roles.create'))
        <a href="{{ route('roles.create') }}" class="primary">
            + Add Role
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert error">
        {{ session('error') }}
    </div>
@endif

<div class="panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>Role</th>
                <th>Users</th>
                <th>Status</th>
                <th>Type</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @forelse($roles as $role)
                <tr>
                    <td>
                        <strong>{{ $role->name }}</strong>

                        @if($role->description)
                            <div class="muted">
                                {{ $role->description }}
                            </div>
                        @endif
                    </td>

                    <td>
                        {{ $role->users_count }}
                    </td>

                    <td>
                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                    </td>

                    <td>
                        @if($role->is_full_admin)
                            Full Administrator
                        @elseif($role->is_system)
                            System
                        @else
                            Custom
                        @endif
                    </td>

                    <td class="actions">
                        @if(auth()->user()->hasPermissionTo('roles.edit'))
                            <a href="{{ route('roles.edit', $role) }}">
                                Edit
                            </a>
                        @endif

                        @if(
                            auth()->user()->hasPermissionTo('roles.delete')
                            && !$role->is_system
                            && !$role->is_full_admin
                            && $role->users_count === 0
                        )
                            <form
                                method="POST"
                                action="{{ route('roles.destroy', $role) }}"
                                style="display:inline"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        No roles found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrap">
        {{ $roles->links() }}
    </div>
</div>

<style>
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
</style>
@endsection