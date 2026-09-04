@extends('central.layouts.central')

@section('title', 'Provision tenant — Master Control')

@section('content')
    <h2>Provision a tenant</h2>
    <p class="sub">Creates the tenant, its 7 system roles, all catalog settings, all modules enabled, and one super_admin user (impersonation-only access).</p>

    <div class="card" style="max-width:640px;">
        <form method="post" action="{{ route('central.tenants.store') }}">
            @csrf
            <div class="form-grid">
                <label>Company name
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </label>
                <label>URL prefix (slug)
                    <input type="text" name="slug" value="{{ old('slug') }}" required pattern="[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?">
                    <small style="color:#64748b;font-weight:400;">lowercase letters, digits and dashes; e.g. acme → /acme/…</small>
                </label>
            </div>
            <div class="form-grid">
                <label>Status
                    <select name="status">
                        <option value="active">Active</option>
                        <option value="trial">Trial</option>
                    </select>
                </label>
                <label>Trial ends at
                    <input type="date" name="trial_ends_at">
                </label>
            </div>
            <div class="form-grid">
                <label>Admin email
                    <input type="email" name="admin_email" required>
                </label>
                <label>Admin name
                    <input type="text" name="admin_name">
                </label>
            </div>
            <p style="font-size:12.5px;color:#64748b;margin-bottom:14px;">The admin account gets a random, never-communicated password — access it via impersonation from this panel.</p>
            <button class="btn btn-primary" type="submit">Provision</button>
        </form>
    </div>
@endsection
