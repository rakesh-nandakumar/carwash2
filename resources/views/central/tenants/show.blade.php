@extends('central.layouts.central')

@section('title', 'Manage '.$tenant->name.' — Master Control')

@section('content')
    <h2>{{ $tenant->name }} <span class="badge {{ $tenant->status }}">{{ $tenant->status }}</span>
        @if($tenant->isTestInstance())<span class="badge test">TEST INSTANCE</span>@endif
    </h2>
    <p class="sub">Sign in at <code>/{{ $tenant->slug }}/login</code> · {{ $tenant->users_count }} users · {{ $settingsStats }} catalog settings.</p>

    <div class="tabs">
        <a class="active" href="{{ route('central.tenants.show', $tenant) }}">Overview</a>
        <a href="{{ route('central.tenants.settings', $tenant) }}">Settings</a>
        <a href="{{ route('central.tenants.modules', $tenant) }}">Modules</a>
        <a href="{{ route('central.tenants.tills', $tenant) }}">Tills</a>
        <a href="{{ route('central.tenants.audit', $tenant) }}">Audit</a>
    </div>

    <div class="card">
        <h3 style="margin-bottom:12px;">Owner admin</h3>
        @if($owner)
            <p>{{ $owner->name }} — {{ $owner->email }} (impersonation-only password, never shown).</p>
            <form method="post" action="{{ route('central.tenants.reset-admin-password', $tenant) }}" style="display:inline-block;margin-top:8px;">
                @csrf
                <input type="text" name="password" placeholder="new password (defaults to password)" style="padding:8px 10px;border:2px solid #e2e8f0;border-radius:8px;">
                <button class="btn btn-secondary" type="submit">Reset password</button>
            </form>
        @else
            <p>No administrator user yet.</p>
        @endif
    </div>

    <div class="card">
        <h3 style="margin-bottom:12px;">Actions</h3>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <form method="post" action="{{ route('central.tenants.impersonate', $tenant) }}">
                @csrf
                <button class="btn btn-primary" type="submit">Impersonate → /{{ $tenant->slug }}/</button>
            </form>
            @if($tenant->isSuspended())
                <form method="post" action="{{ route('central.tenants.resume', $tenant) }}">
                    @csrf
                    <button class="btn btn-success" type="submit">Resume tenant</button>
                </form>
            @else
                <form method="post" action="{{ route('central.tenants.suspend', $tenant) }}">
                    @csrf
                    <button class="btn btn-danger" type="submit">Suspend tenant</button>
                </form>
            @endif
            <form method="post" action="{{ route('central.tenants.update', $tenant) }}">
                @csrf
                <div style="display:flex;gap:8px;align-items:end;">
                    <label style="margin:0;">Name
                        <input type="text" name="name" value="{{ $tenant->name }}" style="min-width:220px;">
                    </label>
                    <label style="margin:0;">Status
                        <select name="status" style="min-width:130px;">
                            @foreach(['active','trial','suspended','cancelled'] as $s)
                                <option value="{{ $s }}" {{ $tenant->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="btn btn-secondary" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:12px;">Test instance</h3>
        @if($tenant->isTestInstance())
            <p style="margin-bottom:10px;">Test instance of <strong>{{ $tenant->parentTenant?->name }}</strong> — reachable at <code>/{{ $tenant->slug }}/</code>. Last synced: {{ $tenant->last_synced_at?->diffForHumans() ?? 'never' }}.</p>
            <div style="display:flex;gap:10px;">
                <form method="post" action="{{ route('central.tenants.test-instance.sync', $tenant) }}">
                    @csrf
                    <button class="btn btn-secondary" type="submit">Sync from live</button>
                </form>
                <form method="post" action="{{ route('central.tenants.test-instance.destroy', $tenant) }}" onsubmit="return confirm('Destroy this test instance and all its rows?');">
                    @csrf
                    <button class="btn btn-danger" type="submit">Destroy</button>
                </form>
            </div>
        @else
            <p style="margin-bottom:10px;">Copy this tenant (and every row it owns) into an isolated <code>-test</code> environment.</p>
            <form method="post" action="{{ route('central.tenants.test-instance.create', $tenant) }}" onsubmit="return confirm('Clone every row of this tenant into a test instance?');">
                @csrf
                <button class="btn btn-secondary" type="submit">Create test instance</button>
            </form>
        @endif
    </div>
@endsection
