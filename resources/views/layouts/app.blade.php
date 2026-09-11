<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AutoCare Pro' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/searchable-dropdown.css') }}">
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
            margin-left: -380px !important;
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
            max-height: 120px;
            max-width: 200px;
            object-fit: contain;
            border-radius: 8px;
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
            --nav-link-padding-v: 14px;
            --nav-link-padding-h: 20px;
            --nav-link-font-size: 16px;
            --nav-link-gap: 14px;
            --nav-link-margin-bottom: 5px;
            --nav-icon-size: 24px;
            --nav-padding-v: 18px;
            --brand-padding-v: 38px;
            --brand-padding-b: 28px;
        }
        aside.sidebar nav {
            display: flex;
            flex-direction: column;
            padding: var(--nav-padding-v) 12px;
            overflow-y: auto; /* allow scrolling instead of auto-scaling */
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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            margin-bottom: var(--nav-link-margin-bottom);
            font-size: var(--nav-link-font-size);
            font-weight: 500;
            position: relative;
            border: 1px solid transparent;
        }
        aside.sidebar nav a svg {
            flex-shrink: 0;
            color: rgba(255, 255, 255, 0.7);
            width: var(--nav-icon-size);
            height: var(--nav-icon-size);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        aside.sidebar nav a span {
            color: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        /* Hover effect */
        aside.sidebar nav a:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.08));
            color: #ffffff;
            transform: translateX(4px) scale(1.02);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        aside.sidebar nav a:hover svg {
            color: #ffffff;
            transform: scale(1.1);
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.3));
        }
        aside.sidebar nav a:hover span {
            font-weight: 600;
        }
        /* Active state */
        aside.sidebar nav a.active {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.35), rgba(74, 144, 226, 0.25));
            color: #ffffff;
            box-shadow: inset 4px 0 0 #4a90e2, 0 4px 12px rgba(74, 144, 226, 0.3);
            border-color: rgba(74, 144, 226, 0.4);
            transform: translateX(2px);
        }
        aside.sidebar nav a.active svg {
            color: #4a90e2;
            transform: scale(1.05);
            filter: drop-shadow(0 0 6px rgba(74, 144, 226, 0.5));
        }
        aside.sidebar nav a.active span {
            font-weight: 600;
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
        aside.sidebar nav a.cheque-payments-link {
            background: rgba(245, 158, 11, 0.12);
            position: relative;
        }
        aside.sidebar nav a.cheque-payments-link:hover,
        aside.sidebar nav a.cheque-payments-link.active {
            background: rgba(245, 158, 11, 0.25);
        }
        aside.sidebar nav a.cheque-payments-link svg {
            color: #f59e0b;
        }
        aside.sidebar nav a.cheque-payments-link.active {
            box-shadow: inset 3px 0 0 #f59e0b;
        }
        aside.sidebar nav a.notifications-link {
            background: rgba(239, 68, 68, 0.12);
            position: relative;
        }
        aside.sidebar nav a.notifications-link:hover,
        aside.sidebar nav a.notifications-link.active {
            background: rgba(239, 68, 68, 0.25);
        }
        aside.sidebar nav a.notifications-link svg {
            color: #ef4444;
        }
        aside.sidebar nav a.notifications-link.active {
            box-shadow: inset 3px 0 0 #ef4444;
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
                width: 380px !important;
                margin-left: 0 !important;
                transition: margin-left 0.3s ease !important;
            }
            aside.sidebar.collapsed {
                margin-left: -380px !important;
            }
            .main {
                margin-left: 380px !important;
                transition: margin-left 0.3s ease !important;
                width: calc(100% - 380px) !important;
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
                left: -300px !important;
                top: 0 !important;
                width: 300px !important;
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
                left: -260px !important;
                top: 0 !important;
                width: 260px !important;
                z-index: 1000 !important;
                transition: left 0.3s ease !important;
                background: #0a1f33 !important;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;

                /* Smaller content on mobile */
                --nav-link-padding-v: 10px;
                --nav-link-padding-h: 16px;
                --nav-link-font-size: 14px;
                --nav-link-gap: 12px;
                --nav-link-margin-bottom: 3px;
                --nav-icon-size: 20px;
                --nav-padding-v: 14px;
                --brand-padding-v: 30px;
                --brand-padding-b: 22px;
            }
            .main {
                margin-left: 0 !important;
            }
            header {
                padding: 12px !important;
                padding-left: 62px !important;
            }
            .brand-logo {
                max-height: 80px;
                max-width: 140px;
            }
        }
        @media (max-width: 480px) {
            aside.sidebar {
                width: 240px !important;
                left: -240px !important;

                /* Even more compact on very small screens */
                --nav-link-padding-v: 9px;
                --nav-link-padding-h: 15px;
                --nav-link-font-size: 13px;
                --nav-link-gap: 11px;
                --nav-link-margin-bottom: 3px;
                --nav-icon-size: 18px;
                --nav-padding-v: 12px;
                --brand-padding-v: 28px;
                --brand-padding-b: 20px;
            }
            header {
                padding: 10px !important;
                padding-left: 62px !important;
            }
            .brand-logo {
                max-height: 60px;
                max-width: 100px;
            }
        }
        /* Open state for tablet/mobile */
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

        .notification-badge.alert-badge {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            border: 2px solid #fee2e2;
            animation: urgentPulse 1s infinite;
        }

        .cheque-payments-link .notification-badge:first-of-type {
            top: -6px;
            right: 6px;
        }

        .cheque-payments-link .notification-badge.alert-badge {
            top: 6px;
            right: -6px;
        }

        @keyframes urgentPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            50% {
                transform: scale(1.15);
                box-shadow: 0 0 0 4px rgba(239, 68, 68, 0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
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
    @if(session('impersonated_by_central'))
    <div style="position:fixed;top:0;left:0;right:0;z-index:1000;background:#7c3aed;color:#fff;text-align:center;padding:6px 12px;font-size:13px;font-weight:600;">
        You are impersonating a user of {{ app(\App\Services\CurrentContext::class)->tenant()?->name ?? 'this tenant' }} — sign out to return to operator mode.
    </div>
    @endif
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
                <img src="{{ \App\Support\Media::url($settings['logo_path']) }}" alt="{{ $settings['company_name'] }}" class="brand-logo">
            @else
                <span class="brand-text">AUTO<span>CARE</span><small>PRO</small></span>
            @endif
        </div>
        <nav id="sidebarNav">
            @if(auth()->user()->hasPermissionTo('reception.access'))
                <a href="{{ route('reception.index') }}" class="reception-link {{ request()->routeIs('reception.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                    <span>Reception</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('dashboard.access'))
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('live_job_board.access'))
                <a href="{{ route('jobs.board') }}" class="{{ request()->routeIs('jobs.board') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span>Live Job Board</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('job_cards.access'))
                <a href="{{ route('jobs.index') }}" class="{{ request()->routeIs('jobs.index') || request()->routeIs('jobs.show') || request()->routeIs('jobs.create') || request()->routeIs('jobs.edit') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Job Cards</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('customers.access'))
                <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Customers</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('vehicles.access'))
                <a href="{{ route('vehicles.index') }}" class="{{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>Vehicles</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('appointments.access'))
                <a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Appointments</span>
                </a>
            @endif

            {{-- ==================== INVENTORY SECTION ==================== --}}
            @if(auth()->user()->hasPermissionTo('inventory.access'))
                <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <span>Item Master</span>
                </a>
            @endif

            @if(auth()->user()->hasPermissionTo('stock_adjustments.access'))
                <a href="{{ route('stock-adjustments.index') }}"
                   class="{{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v18"/>
                        <path d="M3 12h18"/>
                        <path d="M5 5h14v14H5z"/>
                    </svg>
                    <span>Stock Adjustments</span>
                </a>
            @endif

            @if(auth()->user()->hasPermissionTo('categories.access'))
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                    <span>Categories</span>
                </a>
            @endif

            @if(auth()->user()->hasPermissionTo('services.access'))
                <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                    <span>Services</span>
                </a>
            @endif
            {{-- ==================== END INVENTORY SECTION ==================== --}}

            @if(auth()->user()->hasPermissionTo('invoices.access'))
                <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span>Invoices</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('cashier.access'))
                <a href="{{ route('cashier.index') }}" class="cashier-link {{ request()->routeIs('cashier.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span>Cashier</span>
                    @php
                        $readyForPaymentCount = \App\Models\Job::where('status', \App\Enums\JobStatus::READY_FOR_PAYMENT->value)
                            ->whereDoesntHave('invoice', function ($query) {
                                // Exclude jobs with partial payments (have some payments but still have balance)
                                $query->where('paid', '>', 0)
                                      ->where('balance', '>', 0.01);
                            })
                            ->count();
                    @endphp
                    @if($readyForPaymentCount > 0)
                        <span class="notification-badge">{{ $readyForPaymentCount }}</span>
                    @endif
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('cashier.access'))
                <a href="{{ route('cheque-payments.index') }}" class="cheque-payments-link {{ request()->routeIs('cheque-payments.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M12 15h.01"/><path d="M16 15h.01"/></svg>
                    <span>Cheque Payments</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('cashier.access'))
                <a href="{{ route('notifications.index') }}" class="notifications-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span>Notifications</span>
                    @php
                        // Get partial payments (invoices with balance > 0 AND paid > 0)
                        $partialPayments = \App\Models\Invoice::where('balance', '>', 0)->where('total', '>', 0)->where('paid', '>', 0)->get();
                        $partialPaymentsCount = $partialPayments->count();
                        $partialPaymentJobIds = $partialPayments->pluck('job_id')->toArray();
                        
                        // Get jobs ready for payment excluding those with partial payments
                        $readyForPaymentCount = \App\Models\Job::where('status', \App\Enums\JobStatus::READY_FOR_PAYMENT->value)
                            ->whereDoesntHave('invoice', function ($query) {
                                // Exclude jobs with partial payments (have some payments but still have balance)
                                $query->where('paid', '>', 0)
                                      ->where('balance', '>', 0.01);
                            })
                            ->count();
                        
                        $pendingChequesCount = \App\Models\Payment::where('method', 'cheque')->where('payment_received', false)->where('is_bounced', false)->count();
                        $bouncedChequesNeedingFollowUp = \App\Models\Payment::where('method', 'cheque')->where('is_bounced', true)->where('follow_up_required', true)->where('replacement_payment_received', false)->count();
                        $totalNotifications = $partialPaymentsCount + $pendingChequesCount + $bouncedChequesNeedingFollowUp + $readyForPaymentCount;
                    @endphp
                    @if($totalNotifications > 0)
                        <span class="notification-badge">{{ $totalNotifications }}</span>
                    @endif
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('reports.access'))
                <a href="{{ route('reports') }}" class="{{ request()->routeIs('reports*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span>Reports</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('audit_logs.access'))
                <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <span>Audit Logs</span>
                </a>
            @endif
            @if(auth()->user()->hasPermissionTo('users.access'))
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Users</span>
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
                    <span class="muted"> · {{ auth()->user()->roles->pluck('name')->join(', ') }}</span>
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
        const NAV_BASE = {
            paddingV: 14,
            paddingH: 20,
            fontSize: 16,
            gap: 14,
            marginBottom: 5,
            iconSize: 24,
            navPaddingV: 18
        };
        const BRAND_BASE = {
            paddingV: 38,
            paddingB: 28
        };
        const NAV_MIN_SCALE = 0.45;
        const NAV_MIN_FONT = 10;
        const NAV_MIN_ICON = 12;
        const MAX_ITERATIONS = 30;
        const STEP = 0.03;
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
            // On mobile we already force compact sizes via CSS media queries,
            // so we only run the auto-scale logic on larger screens.
            if (window.innerWidth <= 768) {
                return;
            }
            // Disable auto-fit for now to allow larger fonts and icons
            applyNavScale(1);
            applyBrandScale(1);
            return;
            // Original auto-fit logic commented out
            /*
            requestAnimationFrame(() => {
                if (fits()) return;
                let scale = 1;
                let iterations = 0;
                while (!fits() && scale > NAV_MIN_SCALE && iterations < MAX_ITERATIONS) {
                    scale -= STEP;
                    applyNavScale(scale);
                    iterations++;
                }
                let brandScale = 1;
                while (!fits() && brandScale > 0.4 && iterations < MAX_ITERATIONS * 2) {
                    brandScale -= STEP;
                    applyBrandScale(brandScale);
                    iterations++;
                }
                requestAnimationFrame(() => {
                    let guard = 0;
                    while (!fits() && scale > NAV_MIN_SCALE && guard < 10) {
                        scale -= 0.01;
                        applyNavScale(scale);
                        guard++;
                    }
                });
            });
            */
        }
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
    <script src="{{ asset('js/searchable-dropdown.js') }}"></script>
</body>
</html>