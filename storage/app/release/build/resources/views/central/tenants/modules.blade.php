@extends('central.layouts.central')

@section('title', 'Modules — '.$tenant->name)

@section('content')
    <h2>{{ $tenant->name }} — Modules</h2>
    <p class="sub">Licensing is enforced server-side regardless of role: a disabled module 403s even the tenant's own super_admin.</p>

    <div class="tabs">
        <a href="{{ route('central.tenants.show', $tenant) }}">Overview</a>
        <a href="{{ route('central.tenants.settings', $tenant) }}">Settings</a>
        <a class="active" href="{{ route('central.tenants.modules', $tenant) }}">Modules</a>
        <a href="{{ route('central.tenants.audit', $tenant) }}">Audit</a>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr><th>Module</th><th>Description</th><th>Enabled</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($modules as $module)
                <tr>
                    <td><strong>{{ $module['name'] }}</strong></td>
                    <td style="color:#64748b;font-size:13px;">{{ $module['description'] }}</td>
                    <td>
                        @if($module['enabled']) <span class="badge active">on</span> @else <span class="badge suspended">off</span> @endif
                    </td>
                    <td>
                        <form method="post" action="{{ route('central.tenants.modules.update', [$tenant, $module['key']]) }}">
                            @csrf
                            <input type="hidden" name="enabled" value="{{ $module['enabled'] ? '0' : '1' }}">
                            <button class="btn {{ $module['enabled'] ? 'btn-danger' : 'btn-success' }}" type="submit">
                                {{ $module['enabled'] ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
