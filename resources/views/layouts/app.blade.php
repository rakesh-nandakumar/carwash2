<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AutoCare Pro' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        // Runs before first paint: mark <html> with the saved sidebar state
        // so the sidebar renders already-collapsed on desktop with no flash/animation.
        (function () {
            try {
                var collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                var isDesktop = window.innerWidth > 1024;
                if (isDesktop && collapsed) {
                    document.documentElement.classList.add('sidebar-preload-collapsed');
                }
            } catch (e) {}
        })();
    </script>
    <style>
        html.sidebar-preload-collapsed aside.sidebar {
            margin-left: -280px !important;
        }

        html.sidebar-preload-collapsed .main {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .sidebar-toggle {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 30px !important;
            height: 30px !important;
            background: transparent !important;
            border: none !important;
            cursor: pointer !important;
            padding: 0 !important;
            margin-right: 15px !important;
        }

        #sidebarToggle {
            transition: all 0.3s ease !important;
        }

        #sidebarToggle svg {
            transition: transform 0.3s ease, stroke 0.3s ease !important;
        }

        .sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-right: 15px;
            transition: transform 0.3s ease;
        }

        .sidebar-toggle svg {
            stroke: #1a1a2e;
            transition: stroke 0.3s ease, transform 0.3s ease;
        }

        .sidebar-toggle:hover svg {
            stroke: #4a90e2;
        }

        .sidebar-toggle.collapsed svg {
            transform: rotate(180deg);
        }

        header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 15px 20px !important;
            background: white !important;
            border-bottom: 1px solid #e5e7eb !important;
        }

        header > div {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .brand-logo {
            max-height: 80px;
            max-width: 80px;
            object-fit: contain;
            border-radius: 50%;
            display: block;
            margin: 0 auto;
        }

        .brand-text {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .brand-text span {
            color: #4a90e2;
        }

        .brand-text small {
            font-size: 14px;
            color: #4a90e2;
        }

        .brand {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: var(--brand-padding-v, 25px) 10px var(--brand-padding-b, 20px) 10px;
            flex: 0 0 auto;
        }

        /* ===== Sidebar layout ===== */
        aside.sidebar {
            display: flex;
            flex-direction: column;
            height: 100vh;   /* fallback for older browsers */
            height: 100dvh;  /* real visible viewport height on mobile */

            /* Default sizing "tokens" — JS scales these down only if the
               nav content would otherwise overflow and need to scroll. */
            --nav-link-padding-v: 8px;
            --nav-link-padding-h: 14px;
            --nav-link-font-size: 13.5px;
            --nav-link-gap: 10px;
            --nav-link-margin-bottom: 2px;
            --nav-icon-size: 17px;
            --nav-padding-v: 12px;
            --brand-padding-v: 25px;
            --brand-padding-b: 20px;
        }

        aside.sidebar nav {
            display: flex;
            flex-direction: column;
            padding: var(--nav-padding-v) 12px;
            overflow-y: auto; /* safety-net only; JS aims to make this unnecessary */
            flex: 1 1 auto;
            min-height: 0;
        }

        aside.sidebar nav::-webkit-scrollbar {
            width: 5px;
        }

        aside.sidebar nav::-webkit-scrollbar-track {
            background: transparent;
        }

        aside.sidebar nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        aside.sidebar nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* ===== Compact Menu Links (sizes driven by CSS variables, adjusted by JS) ===== */
        aside.sidebar nav a {
            display: flex;
            align-items: center;
            gap: var(--nav-link-gap);
            color: rgba(255, 255, 255, 0.75);
            padding: var(--nav-link-padding-v) var(--nav-link-padding-h);
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
            border-radius: 8px;
            margin-bottom: var(--nav-link-margin-bottom);
            font-size: var(--nav-link-font-size);
            font-weight: 500;
            position: relative;
        }

        aside.sidebar nav a svg {
            flex-shrink: 0;
            color: rgba(255, 255, 255, 0.7);
            width: var(--nav-icon-size);
            height: var(--nav-icon-size);
            transition: color 0.2s ease;
        }

        aside.sidebar nav a span {
            color: inherit;
        }

        /* Hover effect */
        aside.sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transform: translateX(2px);
        }

        aside.sidebar nav a:hover svg {
            color: #ffffff;
        }

        /* Active state */
        aside.sidebar nav a.active {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.25), rgba(74, 144, 226, 0.15));
            color: #ffffff;
            box-shadow: inset 3px 0 0 #4a90e2;
        }

        aside.sidebar nav a.active svg {
            color: #4a90e2;
        }

        /* Special links */
        aside.sidebar nav a.reception-link {
            background: rgba(74, 144, 226, 0.12);
        }

        aside.sidebar nav a.reception-link:hover,
        aside.sidebar nav a.reception-link.active {
            background: rgba(74, 144, 226, 0.25);
        }

        aside.sidebar nav a.reception-link svg {
            color: #4a90e2;
        }

        aside.sidebar nav a.cashier-link {
            background: rgba(16, 185, 129, 0.12);
            position: relative;
        }

        aside.sidebar nav a.cashier-link:hover,
        aside.sidebar nav a.cashier-link.active {
            background: rgba(16, 185, 129, 0.25);
        }

        aside.sidebar nav a.cashier-link svg {
            color: #10b981;
        }

        aside.sidebar nav a.cashier-link.active {
            box-shadow: inset 3px 0 0 #10b981;
        }

        aside.sidebar a.logout {
            flex: 0 0 auto;
        }

        /* ===== Responsive ===== */
        @media (min-width: 1025px) {
            .sidebar-toggle {
                display: flex !important;
            }

            aside.sidebar {
                width: 280px !important;
                margin-left: 0 !important;
                transition: margin-left 0.3s ease !important;
            }

            aside.sidebar.collapsed {
                margin-left: -280px !important;
            }

            .main {
                margin-left: 280px !important;
                transition: margin-left 0.3s ease !important;
                width: calc(100% - 280px) !important;
            }

            .main.expanded {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar-toggle {
                display: flex;
            }

            aside.sidebar {
                position: fixed !important;
                left: -240px !important;
                top: 0 !important;
                width: 240px !important;
                z-index: 1000 !important;
                transition: left 0.3s ease !important;
                background: #0a1f33 !important;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;
            }

            .main {
                margin-left: 0 !important;
            }

            header {
                padding: 15px !important;
                padding-left: 65px !important;
            }
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: flex;
            }

            aside.sidebar {
                position: fixed !important;
                left: -230px !important;
                top: 0 !important;
                width: 230px !important;
                z-index: 1000 !important;
                transition: left 0.3s ease !important;
                background: #0a1f33 !important;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;
            }

            .main {
                margin-left: 0 !important;
            }

            header {
                padding: 12px !important;
                padding-left: 62px !important;
            }
        }

        @media (max-width: 480px) {
            aside.sidebar {
                width: 220px !important;
                left: -220px !important;
            }

            header {
                padding: 10px !important;
                padding-left: 62px !important;
            }
        }

        /* Open state for tablet/mobile — this was the missing rule
           that kept the sidebar from ever appearing when toggled. */
        @media (max-width: 1024px) {
            aside.sidebar.active {
                left: 0 !important;
            }
        }

        .cashier-link {
            position: relative !important;
        }

        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Toast */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100001;
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            max-width: 360px;
            animation: toastIn 0.3s ease;
        }

        .toast.toast-hide {
            animation: toastOut 0.3s ease forwards;
        }

        .toast.success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .toast.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(20px); }
        }

        @media (max-width: 640px) {
            .toast {
                left: 16px;
                right: 16px;
                max-width: none;
                top: 12px;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        @php
            $business = auth()->user()->business;
            $settings = $business ? $business->getBillingSettings() : [
                'company_name' => 'AutoCare Pro',
                'logo_path' => ''
            ];
        @endphp
        <div class="brand" id="sidebarBrand">
            @if($settings['logo_path'])
                <img src="{{ asset($settings['logo_path']) }}" alt="{{ $settings['company_name'] }}" class="brand-logo">
            @else
                <span class="brand-text">AUTO<span>CARE</span><small>PRO</small></span>
            @endif
        </div>
        <nav id="sidebarNav">
            @if(auth()->user()->hasPermission('view_reception'))
                <a href="{{ route('reception.index') }}" class="reception-link {{ request()->routeIs('reception.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                    <span>Reception</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_dashboard'))
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_live_job_board'))
                <a href="{{ route('jobs.board') }}" class="{{ request()->routeIs('jobs.board') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span>Live Job Board</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_job_cards'))
                <a href="{{ route('jobs.index') }}" class="{{ request()->routeIs('jobs.index') || request()->routeIs('jobs.show') || request()->routeIs('jobs.create') || request()->routeIs('jobs.edit') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Job Cards</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_customers'))
                <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Customers</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_vehicles'))
                <a href="{{ route('vehicles.index') }}" class="{{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>Vehicles</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_appointments'))
                <a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Appointments</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_item_master'))
                <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    <span>Item Master</span>
                </a>
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    <span>Categories</span>
                </a>
                @if(auth()->user()->hasPermission('view_services'))
                <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    <span>Services</span>
                </a>
                @endif
            @endif

            @if(auth()->user()->hasPermission('view_invoices'))
                <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span>Invoices</span>
                </a>
                <a href="{{ route('cashier.index') }}" class="cashier-link {{ request()->routeIs('cashier.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span>Cashier</span>
                    @php
                        $readyForPaymentCount = \App\Models\Job::where('status', \App\Enums\JobStatus::READY_FOR_PAYMENT->value)->count();
                    @endphp
                    @if($readyForPaymentCount > 0)
                        <span class="notification-badge">{{ $readyForPaymentCount }}</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_reports'))
                <a href="{{ route('reports') }}" class="{{ request()->routeIs('reports*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span>Reports</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_users'))
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Users</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('view_settings'))
                <a href="{{ route('settings.billing') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Settings</span>
                </a>
            @endif
        </nav>
        <a href="{{ route('logout') }}" class="logout">Sign out</a>
    </aside>

    <main class="main">
        <div style="position:fixed;top:15px;left:15px;z-index:100000;">
            <button
                id="sidebarToggle"
                aria-label="Toggle menu"
                style="
                    display:flex !important;
                    align-items:center !important;
                    justify-content:center !important;
                    width:30px !important;
                    height:30px !important;
                    background:white !important;
                    border:1px solid #e5e7eb !important;
                    border-radius:6px !important;
                    cursor:pointer !important;
                    padding:0 !important;
                    box-shadow:0 2px 6px rgba(0,0,0,0.12) !important;
                    z-index:100000 !important;
                    transition:all 0.3s ease !important;
                ">
                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="#1a1a2e"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    style="transition:transform 0.3s ease, stroke 0.3s ease !important;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
        </div>
        <header style="padding-left:70px;">
            <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                <div></div>
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <span class="muted"> · {{ str_replace('_',' ',ucfirst(auth()->user()->role)) }}</span>
                </div>
            </div>
        </header>
        @if(session('success'))
            <div class="toast success" id="appToast">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="toast error" id="appToast">{{ $errors->first() }}</div>
        @endif
        <div class="content">
            @yield('content')
        </div>
    </main>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarNav = document.getElementById('sidebarNav');
        const sidebarBrand = document.getElementById('sidebarBrand');
        const main = document.querySelector('.main');
        const toggleIcon = sidebarToggle.querySelector('svg');

        // Restore sidebar state on page load
        function restoreSidebarState() {
            const isMobile = window.innerWidth <= 1024;
            const savedState = localStorage.getItem('sidebarCollapsed');

            if (!isMobile) {
                // Desktop
                if (savedState === 'true') {
                    sidebar.classList.add('collapsed');
                    main.classList.add('expanded');
                } else {
                    sidebar.classList.remove('collapsed');
                    main.classList.remove('expanded');
                }
            } else {
                // Mobile - always start closed
                sidebar.classList.remove('active');
            }

            updateSidebarToggle();
        }

        function updateSidebarToggle() {
            const isCollapsed = sidebar.classList.contains('collapsed');
            const isMobile = window.innerWidth <= 1024;
            const isMobileActive = sidebar.classList.contains('active');

            if (!isMobile) {
                if (isCollapsed) {
                    toggleIcon.style.transform = 'rotate(180deg)';
                } else {
                    toggleIcon.style.transform = 'rotate(0deg)';
                }
            } else {
                if (isMobileActive) {
                    toggleIcon.style.transform = 'rotate(0deg)';
                } else {
                    toggleIcon.style.transform = 'rotate(180deg)';
                }
            }
        }

        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                // Mobile / tablet
                sidebar.classList.toggle('active');
            } else {
                // Desktop
                sidebar.classList.toggle('collapsed');
                main.classList.toggle('expanded');

                // Save state
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }

            updateSidebarToggle();
        });

        // Close sidebar when clicking outside (mobile only)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                    updateSidebarToggle();
                }
            }
        });

        // Run on page load
        restoreSidebarState();

        // Auto-dismiss toast
        const appToast = document.getElementById('appToast');
        if (appToast) {
            setTimeout(() => {
                appToast.classList.add('toast-hide');
                setTimeout(() => appToast.remove(), 300);
            }, 3500);
        }

        // ===== Auto-fit sidebar nav so it never needs to scroll =====
        // Iteratively shrinks padding / font-size / icon size / gaps /
        // nav padding (and, as a last resort, the brand block) until the
        // nav's content height fits its available space exactly, or a
        // readable floor is hit. Re-measures the DOM after every change
        // instead of relying on a single calculated ratio, so it corrects
        // for font metrics, sub-pixel rounding, and mobile viewport quirks.
        const NAV_BASE = {
            paddingV: 8,
            paddingH: 14,
            fontSize: 13.5,
            gap: 10,
            marginBottom: 2,
            iconSize: 17,
            navPaddingV: 12
        };
        const BRAND_BASE = {
            paddingV: 25,
            paddingB: 20
        };
        const NAV_MIN_SCALE = 0.45;   // don't shrink link metrics below ~45% of base
        const NAV_MIN_FONT = 10;      // px floor for readability
        const NAV_MIN_ICON = 12;      // px floor for readability
        const MAX_ITERATIONS = 30;
        const STEP = 0.03;            // how much to shrink per iteration

        function applyNavScale(scale) {
            sidebar.style.setProperty('--nav-link-padding-v', (NAV_BASE.paddingV * scale).toFixed(2) + 'px');
            sidebar.style.setProperty('--nav-link-padding-h', (NAV_BASE.paddingH * scale).toFixed(2) + 'px');
            sidebar.style.setProperty('--nav-link-font-size', Math.max(NAV_BASE.fontSize * scale, NAV_MIN_FONT).toFixed(2) + 'px');
            sidebar.style.setProperty('--nav-link-gap', (NAV_BASE.gap * scale).toFixed(2) + 'px');
            sidebar.style.setProperty('--nav-link-margin-bottom', (NAV_BASE.marginBottom * scale).toFixed(2) + 'px');
            sidebar.style.setProperty('--nav-icon-size', Math.max(NAV_BASE.iconSize * scale, NAV_MIN_ICON).toFixed(2) + 'px');
            sidebar.style.setProperty('--nav-padding-v', Math.max(NAV_BASE.navPaddingV * scale, 4).toFixed(2) + 'px');
        }

        function applyBrandScale(scale) {
            sidebar.style.setProperty('--brand-padding-v', (BRAND_BASE.paddingV * scale).toFixed(2) + 'px');
            sidebar.style.setProperty('--brand-padding-b', (BRAND_BASE.paddingB * scale).toFixed(2) + 'px');
        }

        function fits() {
            return sidebarNav.scrollHeight <= sidebarNav.clientHeight;
        }

        function fitSidebarNav() {
            if (!sidebarNav) return;

            // Reset to full size first so measurement reflects natural content.
            applyNavScale(1);
            applyBrandScale(1);

            requestAnimationFrame(() => {
                if (fits()) return; // already fits, keep base sizes

                let scale = 1;
                let iterations = 0;

                // Phase 1: shrink link/nav metrics down to the readability floor.
                while (!fits() && scale > NAV_MIN_SCALE && iterations < MAX_ITERATIONS) {
                    scale -= STEP;
                    applyNavScale(scale);
                    iterations++;
                }

                // Phase 2: if still overflowing (very short viewport / many
                // permissions), shrink the brand/logo block a bit too rather
                // than leaving a scrollbar.
                let brandScale = 1;
                while (!fits() && brandScale > 0.4 && iterations < MAX_ITERATIONS * 2) {
                    brandScale -= STEP;
                    applyBrandScale(brandScale);
                    iterations++;
                }

                // Final safety pass in case of any leftover 1px rounding.
                requestAnimationFrame(() => {
                    let guard = 0;
                    while (!fits() && scale > NAV_MIN_SCALE && guard < 10) {
                        scale -= 0.01;
                        applyNavScale(scale);
                        guard++;
                    }
                });
            });
        }

        // Debounce resize-triggered refits slightly for smoother behavior.
        let navFitTimeout;
        function scheduleFitSidebarNav() {
            clearTimeout(navFitTimeout);
            navFitTimeout = setTimeout(fitSidebarNav, 100);
        }
        window.addEventListener('resize', () => {
            restoreSidebarState();
            scheduleFitSidebarNav();
        });
        window.addEventListener('orientationchange', scheduleFitSidebarNav);
        window.addEventListener('load', fitSidebarNav);

        // Re-fit once fonts have actually loaded — icon/text metrics before
        // that point can be inaccurate and lead to an under-shrink.
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(fitSidebarNav);
        }

        fitSidebarNav();
    </script>
</body>
</html>