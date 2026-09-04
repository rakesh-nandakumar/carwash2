@extends('central.layouts.central')

@section('title', 'Platform operators — Master Control')

@section('content')
    <h2>Platform operators</h2>
    <p class="sub">Who may sign in to master control. You can never deactivate or delete your own account, nor the last active operator.</p>

    <div class="card">
        <table>
            <thead>
            <tr><th>Name</th><th>Email</th><th>Active</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($admins as $admin)
                <tr>
                    <td><strong>{{ $admin->name }}</strong></td>
                    <td>{{ $admin->email }}</td>
                    <td>@if($admin->is_active)<span class="badge active">active</span>@else<span class="badge suspended">inactive</span>@endif</td>
                    <td>
                        @if($admin->id !== auth('central')->id())
                            <form method="post" action="{{ route('central.admins.update', $admin) }}" style="display:inline-block;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $admin->is_active ? '0' : '1' }}">
                                <button class="btn btn-secondary" type="submit">{{ $admin->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="post" action="{{ route('central.admins.destroy', $admin) }}" style="display:inline-block;" onsubmit="return confirm('Delete this operator?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        @else
                            <small style="color:#64748b;">(you)</small>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card" style="max-width:520px;">
        <h3 style="margin-bottom:12px;">Add operator</h3>
        <form method="post" action="{{ route('central.admins.store') }}">
            @csrf
            <label>Name <input type="text" name="name" required></label>
            <label>Email <input type="email" name="email" required></label>
            <label>Password <input type="password" name="password" required minlength="8"></label>
            <button class="btn btn-primary" type="submit">Create operator</button>
        </form>
    </div>
@endsection
