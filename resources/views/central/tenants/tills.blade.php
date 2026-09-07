@extends('central.layouts.central')

@section('title', 'Tills — '.$tenant->name.' — Master Control')

@section('content')
    <h2>{{ $tenant->name }} — Tills</h2>
    <p class="sub">Manage tills for this tenant</p>

    <div class="tabs">
        <a href="{{ route('central.tenants.show', $tenant) }}">Overview</a>
        <a href="{{ route('central.tenants.settings', $tenant) }}">Settings</a>
        <a href="{{ route('central.tenants.modules', $tenant) }}">Modules</a>
        <a class="active" href="{{ route('central.tenants.tills', $tenant) }}">Tills</a>
        <a href="{{ route('central.tenants.audit', $tenant) }}">Audit</a>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="margin:0;">All Tills</h3>
            <button class="btn btn-primary" type="button" onclick="document.getElementById('newTillForm').style.display = document.getElementById('newTillForm').style.display === 'none' ? 'block' : 'none'">+ New Till</button>
        </div>

        <div id="newTillForm" style="display:none;margin-bottom:20px;padding:20px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
            <h4 style="margin:0 0 16px 0;">Create New Till</h4>
            <form method="post" action="{{ route('central.tenants.tills.create', $tenant) }}">
                @csrf
                <div class="form-grid">
                    <label>Till name
                        <input type="text" name="name" required>
                    </label>
                    <label>Till code
                        <input type="text" name="code" required>
                    </label>
                </div>
                <div class="form-grid">
                    <label>Opening balance
                        <input type="number" name="opening_balance" value="0" min="0" step="0.01" required>
                    </label>
                    <label>Location
                        <input type="text" name="location">
                    </label>
                </div>
                <div style="margin-top:12px;">
                    <label>Description
                        <input type="text" name="description" style="width:100%;">
                    </label>
                </div>
                <div style="margin-top:16px;display:flex;gap:8px;">
                    <button class="btn btn-primary" type="submit">Create Till</button>
                    <button class="btn btn-secondary" type="button" onclick="document.getElementById('newTillForm').style.display = 'none'">Cancel</button>
                </div>
            </form>
        </div>

        @forelse($tills as $till)
            <div style="border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <strong>{{ $till->name }}</strong>
                    <span style="color:#64748b;margin:0 8px;">{{ $till->code }}</span>
                    @if($till->location)
                        <span style="color:#64748b;">{{ $till->location }}</span>
                    @endif
                    @if($till->is_active)
                        <span style="background:#dcfce7;color:#16a34a;padding:2px 8px;border-radius:4px;font-size:12px;margin-left:8px;">Active</span>
                    @else
                        <span style="background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:4px;font-size:12px;margin-left:8px;">Inactive</span>
                    @endif
                    @if($till->isInUse())
                        @if($till->current_user_id)
                            <span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:4px;font-size:12px;margin-left:8px;">In Use by {{ $till->currentUser->name ?? 'Unknown' }}</span>
                        @else
                            <span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:4px;font-size:12px;margin-left:8px;">In Use</span>
                        @endif
                    @endif
                </div>
                <div style="display:flex;gap:8px;">
                    <span style="color:#64748b;font-size:14px;">Balance: Rs. {{ number_format($till->opening_balance, 2) }}</span>
                </div>
            </div>
        @empty
            <p style="color:#64748b;text-align:center;padding:40px;">No tills configured for this tenant.</p>
        @endforelse
    </div>

    <div style="margin-top:20px;">
        <a href="{{ route('central.tenants.show', $tenant) }}" class="btn btn-secondary">← Back to Overview</a>
    </div>
@endsection