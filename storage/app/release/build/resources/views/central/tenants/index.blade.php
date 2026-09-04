@extends('central.layouts.central')

@section('title', 'Tenants — Master Control')

@section('content')
    <h2>Tenants</h2>
    <p class="sub">Every car-wash company in the database, one tenant per company.</p>

    <div style="margin-bottom:16px;display:flex;gap:10px;">
        <a href="{{ route('central.tenants.create') }}" class="btn btn-primary">+ Provision Tenant</a>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>URL prefix</th>
                <th>Status</th>
                <th>Environment</th>
                <th>Users</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($tenants as $tenant)
                <tr>
                    <td><strong>{{ $tenant->name }}</strong></td>
                    <td><code>/{{ $tenant->slug }}/</code></td>
                    <td><span class="badge {{ $tenant->status }}">{{ $tenant->status }}</span></td>
                    <td>@if($tenant->isTestInstance())<span class="badge test">test</span>@else live @endif</td>
                    <td>{{ $tenant->users_count }}</td>
                    <td><a class="btn btn-secondary" href="{{ route('central.tenants.show', $tenant) }}">Manage</a></td>
                </tr>
            @empty
                <tr><td colspan="6">No tenants yet — provision the first one.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
