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
            <tr><th>When</th><th>Event</th><th>Severity</th><th>Actor</th><th>Description</th></tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td><code>{{ $log->event_key }}</code></td>
                    <td>
                        @php
                            $severityClass = match($log->severity) {
                                'info' => 'background:#dcfce7;color:#166534;',
                                'warning' => 'background:#fef3c7;color:#92400e;',
                                'error' => 'background:#fee2e2;color:#991b1b;',
                                'critical' => 'background:#7f1d1d;color:white;',
                                default => 'background:#f3f4f6;color:#4b5563;'
                            };
                        @endphp
                        <span style="{{ $severityClass }} padding:4px 8px;border-radius:999px;font-size:12px;font-weight:600;">
                            {{ ucfirst($log->severity) }}
                        </span>
                    </td>
                    <td>{{ $log->actor_email ?? '—' }}
                        <small style="color:#64748b;">({{ ucfirst(str_replace('_', ' ', $log->actor_type ?? 'unknown')) }})</small>
                    </td>
                    <td style="font-size:12px;color:#64748b;">{{ Str::limit($log->description, 120) }}
                        @if($log->is_flagged)
                            <span style="color:#dc2626;font-weight:600;">⚠️ Flagged</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No audit rows yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px;">{{ $logs->links() }}</div>
    </div>
@endsection
