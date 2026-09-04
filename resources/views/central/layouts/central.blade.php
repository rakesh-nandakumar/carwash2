<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Autocare Pro — Master Control')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9; color: #1e293b; min-height: 100vh;
        }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 230px; background: #0c182a; color: #cbd5e1;
            padding: 18px 12px; flex-shrink: 0;
        }
        .sidebar h1 { font-size: 15px; color: #fff; margin-bottom: 22px; padding: 0 10px; font-weight: 700; }
        .sidebar h1 small { display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 3px; }
        .sidebar a.nav {
            display: block; padding: 10px 12px; border-radius: 8px; color: #cbd5e1;
            text-decoration: none; font-size: 13px; font-weight: 500; margin-bottom: 3px;
        }
        .sidebar a.nav:hover, .sidebar a.nav.active { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar .logout { margin-top: 30px; display: block; padding: 10px 12px; color: #f87171; text-decoration: none; font-size: 13px; font-weight: 600; }
        .content { flex: 1; padding: 26px 30px; }
        .content h2 { font-size: 22px; margin-bottom: 4px; }
        .content p.sub { color: #64748b; font-size: 13px; margin-bottom: 20px; }
        .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.08); margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; padding: 9px 10px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e2e8f0; }
        td { padding: 10px; border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #f8fafc; }
        .btn { display: inline-block; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #4a90e2; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-success { background: #10b981; color: #fff; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 99px; font-size: 11px; font-weight: 700; }
        .badge.active { background: #dcfce7; color: #166534; }
        .badge.trial { background: #fef9c3; color: #854d0e; }
        .badge.suspended, .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .badge.test { background: #e0e7ff; color: #3730a3; }
        .flash { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 600; }
        .flash.success { background: #dcfce7; color: #166534; }
        .flash.error { background: #fee2e2; color: #991b1b; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 14px; }
        label input, label select, label textarea {
            width: 100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; margin-top: 5px; background: #fff;
        }
        .tabs { display: flex; gap: 6px; margin-bottom: 18px; flex-wrap: wrap; }
        .tabs a { padding: 9px 16px; background: #e2e8f0; border-radius: 8px; text-decoration: none; color: #334155; font-size: 13px; font-weight: 600; }
        .tabs a.active { background: #4a90e2; color: #fff; }
        .impl-strip { background: #7c3aed; }
    </style>
</head>
<body>
@if($errors->any())
    <div class="flash error" style="margin:0">{{ $errors->first() }}</div>
@endif
@if(session('error'))
    <div class="flash error" style="margin:0">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="flash success" style="margin:0">{{ session('success') }}</div>
@endif
<div class="wrapper">
    <aside class="sidebar">
        <h1>AUTOCARE<span style="color:#4a90e2">PRO</span><small>Master Control</small></h1>
        <a href="{{ route('central.dashboard') }}" class="nav {{ request()->routeIs('central.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('central.tenants.index') }}" class="nav {{ request()->routeIs('central.tenants.*') || request()->routeIs('central.tenants.settings*') ? 'active' : '' }}">Tenants</a>
        <a href="{{ route('central.admins.index') }}" class="nav {{ request()->routeIs('central.admins.*') ? 'active' : '' }}">Platform Operators</a>
        <a href="/" class="nav">← Tenant app</a>
        <form method="post" action="{{ route('central.logout') }}" style="margin-top:auto;">
            @csrf
            <button class="logout" style="background:none;border:none;cursor:pointer;text-align:left;width:100%;font-size:13px;font-weight:600;color:#f87171;">Sign out</button>
        </form>
    </aside>
    <main class="content">
        @yield('content')
    </main>
</div>
</body>
</html>
