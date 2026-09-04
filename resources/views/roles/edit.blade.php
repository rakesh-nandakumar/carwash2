@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Edit Role</h1>
        <p>{{ $role->name }}</p>
    </div>
</div>

<div class="panel">

    @if($errors->any())
        <div class="alert error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')

        <label>
            Role Name

            <input
                type="text"
                name="name"
                value="{{ old('name', $role->name) }}"
                required
            >
        </label>

        <label>
            Description

            <textarea name="description">{{ old('description', $role->description) }}</textarea>
        </label>

        <h2>Permissions</h2>

        @foreach($permissions as $module => $modulePermissions)

            <div class="permission-module">
                <div class="module-title">
                    {{ ucfirst(str_replace('_', ' ', $module)) }}
                </div>

                <div class="permissions-list">

                    @foreach($modulePermissions as $permission)

                        <label class="permission-item">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                {{ in_array(
                                    $permission->id,
                                    old('permissions', $selected)
                                ) ? 'checked' : '' }}
                            >

                            <span>
                                {{ $permission->name }}
                            </span>
                        </label>

                    @endforeach

                </div>
            </div>

        @endforeach

        <div class="form-actions">
            <button type="submit" class="primary">
                Update Role
            </button>

            <a href="{{ route('roles.index') }}">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection