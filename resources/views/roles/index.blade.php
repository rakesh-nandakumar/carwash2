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

    {{ $roles->links() }}
</div>
@endsection