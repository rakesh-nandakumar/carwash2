@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Users</h1>
        <p>Manage system users and their permissions</p>
    </div>
    <div style="display:flex;gap:12px;align-items:center;">
        <input type="text" id="userSearch" placeholder="Search users..." oninput="filterUsers()" style="padding:10px 14px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;width:250px;">
        <a href="{{ route('users.create') }}" class="primary">+ Add User</a>
    </div>
</div>

<div class="users-grid">
    @foreach($users as $user)
        <div class="user-card">
            <div class="user-card-header">
                <div class="user-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="user-status {{ $user->active ? 'active' : 'inactive' }}">
                    {{ $user->active ? 'Active' : 'Inactive' }}
                </div>
            </div>
            <div class="user-card-body">
                <h3>{{ $user->name }}</h3>
                <p class="user-email">{{ $user->email }}</p>
                <div class="user-roles">
                    @foreach($user->roles as $role)
                        <span class="role-badge">{{ $role->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="user-card-footer">
                <a href="{{ route('users.edit', $user) }}" class="btn-edit">Edit</a>
                @if($user->id !== auth()->id())
                    <form method="post" action="{{ route('users.destroy', $user) }}" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

@php $paginationThreshold = request()->isMobile() ? 10 : 50; @endphp
@if($users->total() > $paginationThreshold)
<div class="pagination-wrap">
    {{ $users->links() }}
</div>
@endif

<style>
.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.user-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.user-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.user-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
}

.user-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.user-status.active {
    background: #27ae60;
    color: white;
}

.user-status.inactive {
    background: #e74c3c;
    color: white;
}

.user-card-body {
    padding: 20px;
}

.user-card-body h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
    color: #2c3e50;
}

.user-email {
    color: #666;
    font-size: 14px;
    margin: 0 0 15px 0;
}

.user-roles {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 15px;
}

.role-badge {
    padding: 4px 12px;
    background: #f0f8ff;
    color: #4a90e2;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.user-card-footer {
    display: flex;
    gap: 10px;
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    background: #f8f9fa;
}

.btn-edit {
    flex: 1;
    padding: 10px 20px;
    background: #4a90e2;
    color: white;
    border: none;
    border-radius: 8px;
    text-align: center;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-edit:hover {
    background: #357abd;
}

.delete-form {
    display: inline;
}

.btn-delete {
    padding: 10px 20px;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-delete:hover {
    background: #c0392b;
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

/* Responsive pagination for mobile */
@media (max-width: 768px) {
    .pagination-wrap {
        margin-top: 20px;
        gap: 8px;
    }

    .pagination-wrap .pagination,
    .pagination-wrap nav > div {
        gap: 4px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-wrap a,
    .pagination-wrap span {
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        font-size: 13px;
        border-radius: 8px;
    }

    .pagination-wrap a[rel="prev"],
    .pagination-wrap a[rel="next"] {
        padding: 0 10px;
        font-size: 12px;
    }

    .pagination-wrap svg,
    .pagination-wrap .pagination svg,
    nav[role="navigation"] svg {
        width: 14px !important;
        height: 14px !important;
        max-width: 14px !important;
        max-height: 14px !important;
    }

    /* Hide some page numbers on very small screens */
    @media (max-width: 480px) {
        .pagination-wrap .pagination {
            gap: 2px;
        }

        .pagination-wrap a,
        .pagination-wrap span {
            min-width: 28px;
            height: 28px;
            padding: 0 6px;
            font-size: 12px;
        }

        .pagination-wrap a[rel="prev"],
        .pagination-wrap a[rel="next"] {
            padding: 0 8px;
            font-size: 11px;
        }
    }
}
</style>

<script>
function filterUsers() {
    const query = document.getElementById('userSearch').value.toLowerCase().trim();
    const userCards = document.querySelectorAll('.user-card');

    userCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(query) ? '' : 'none';
    });
}
</script>
@endsection