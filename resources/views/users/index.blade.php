@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Users</h1>
        <p>Manage system users and their permissions</p>
    </div>
    <a href="{{ route('users.create') }}" class="primary">+ Add User</a>
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

{{ $users->links() }}

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
</style>
@endsection