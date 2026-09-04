@extends('central.layouts.central')

@section('title', 'Audit — '.$tenant->name)

@section('content')
    <h2>{{ $tenant->name }} — Audit log</h2>
    <p class="sub">Everything that happened inside the tenant, plus central actions on it.</p>

    <div class="tabs">
        <a href="{{ route('central.tenants.show', $tenant) }}">Overview</a>
        <a href="{{ route('central.tenants.settings', $tenant) }}">Settings</a>
        <a href="{{ route('central.tenants.modules', $tenant) }}">Modules</a>
        <a class="active" href="{{ route('central.tenants.audit', $tenant) }}">Audit</a>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr><th>When</th><th>Action</th><th>Actor</th><th>Reason / data</th></tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td><code>{{ $log->action }}</code></td>
                    <td>{{ $log->user?->name ?? '—' }}
                        @if(($log->new_value['central_admin'] ?? null) || ($log->reason ?? null))
                            <small style="color:#64748b;">({{ $log->new_value['central_admin'] ?? $log->reason }})</small>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#64748b;">{{ Str::limit(json_encode($log->old_value), 120) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No audit rows yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px;">{{ $logs->links() }}</div>
    </div>
@endsection
