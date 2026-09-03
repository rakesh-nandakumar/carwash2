@extends('layouts.app')

@section('content')
<style>
    /* ========== BASE STYLES ========== */
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 18px;
        padding: 10px 0 30px;
    }

    .kanban-column {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 18px;
        min-height: 420px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        border: 1px solid rgba(255, 255, 255, 0.3);
        display: flex;
        flex-direction: column;
    }

    .kanban-column[data-status="pending"] {
        border-top: 4px solid #f59e0b;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(245, 158, 11, 0.08));
    }
    .kanban-column[data-status="check_in"] {
        border-top: 4px solid #3b82f6;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(59, 130, 246, 0.08));
    }
    .kanban-column[data-status="inspection"] {
        border-top: 4px solid #8b5cf6;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(139, 92, 246, 0.08));
    }
    .kanban-column[data-status="in_progress"] {
        border-top: 4px solid #f97316;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(249, 115, 22, 0.08));
    }
    .kanban-column[data-status="painting"] {
        border-top: 4px solid #ec4899;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(236, 72, 153, 0.08));
    }
    .kanban-column[data-status="completed"] {
        border-top: 4px solid #10b981;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(16, 185, 129, 0.08));
    }
    .kanban-column[data-status="delivered"] {
        border-top: 4px solid #6366f1;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(99, 102, 241, 0.08));
    }

    .column-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
        flex-shrink: 0;
    }

    .column-title {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .column-count {
        background: #e5e7eb;
        color: #374151;
        padding: 3px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .job-card {
        display: block;
        background: white;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .job-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.1);
        border-color: #2563eb;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
        gap: 8px;
    }

    .vehicle-number {
        font-size: 15px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        word-break: break-all;
    }

    .priority-badge {
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .priority-high { background: #fee2e2; color: #dc2626; }
    .priority-medium { background: #fef3c7; color: #d97706; }
    .priority-low { background: #dcfce7; color: #16a34a; }

    .customer-name {
        font-size: 13.5px;
        color: #374151;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 8px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        color: #667085;
        background: #f8fafc;
        padding: 3px 8px;
        border-radius: 6px;
    }

    .technician-badge,
    .branch-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .technician-badge {
        background: #dbeafe;
        color: #2563eb;
    }

    .branch-badge {
        background: #f3e8ff;
        color: #9333ea;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 10px;
        border-top: 1px solid #f1f5f9;
        margin-top: 8px;
    }

    .time-badge {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11.5px;
        color: #667085;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-pending { background: #f59e0b; }
    .status-in-progress { background: #3b82f6; }
    .status-completed { background: #10b981; }
    .status-delivered { background: #8b5cf6; }

    /* ========== MOBILE TABS ========== */
    .mobile-tabs {
        display: none;
        margin-top: 12px;
        margin-bottom: 18px;
        width: 100%;
        box-sizing: border-box;
    }

    .mobile-tab {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 9px 11px;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
        min-height: 48px;
        text-align: left;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        box-sizing: border-box;
        width: 100%;
    }

    .mobile-tab .tab-title {
        flex: 1;
        font-size: 12.5px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-width: 0;
    }

    .mobile-tab .count {
        flex-shrink: 0;
        background: #f1f5f9;
        color: #475569;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        min-width: 24px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }

    .mobile-tab.active {
        background: #eff6ff;
        border-color: #2563eb;
        color: #1e40af;
        box-shadow: 0 0 0 1px #2563eb, 0 4px 12px rgba(37, 99, 235, 0.15);
    }

    .mobile-tab.active .count {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    .mobile-tab:active {
        transform: scale(0.98);
    }

    .mobile-column {
        display: none;
    }

    .mobile-column.active {
        display: block;
    }

    .mobile-jobs-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    /* TV Mode */
    body.tv-mode .sidebar,
    body.tv-mode .page-head,
    body.tv-mode header,
    body.tv-mode .mobile-tabs {
        display: none !important;
    }

    body.tv-mode #tvToggle {
        display: block !important;
    }

    body.tv-mode .main {
        margin-left: 0 !important;
        width: 100% !important;
        padding: 12px;
        height: 100vh;
        overflow: hidden;
    }

    body.tv-mode .kanban-board {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        height: calc(100vh - 24px);
        overflow: hidden;
    }

    body.tv-mode .kanban-column {
        display: flex !important;
        min-height: auto;
        height: 100%;
        padding: 12px;
        overflow-y: auto;
    }

    body.tv-mode .mobile-column {
        display: none !important;
    }

    body.tv-mode .tv-toggle-btn {
        background: #dc2626;
        position: fixed !important;
        top: 16px !important;
        right: 16px !important;
        z-index: 999999 !important;
    }

    .tv-toggle-btn {
        background: #2563eb;
        color: white;
        border: none;
        padding: 11px 22px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transition: all 0.25s ease;
        font-size: 14px;
        box-sizing: border-box;
    }

    .tv-toggle-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
        .page-head {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .page-head h1 {
            font-size: 22px;
        }

        .page-head p {
            font-size: 13px;
        }

        /* Make New Job + TV Mode sit on the same row */
        .page-head > div:last-child {
            width: 100%;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .page-head a.primary {
            /* Keep original width - do not stretch */
            flex-shrink: 0;
            width: auto;
            padding: 11px 18px;
            text-align: center;
            display: inline-block;
            box-sizing: border-box;
        }

        .tv-toggle-btn {
            flex: 1;                 /* fills remaining space */
            margin-top: 0;
            margin-bottom: 0;
            width: auto;
        }

        .kanban-board {
            display: none !important;
        }

        .mobile-tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        .mobile-column {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            width: 100%;
            box-sizing: border-box;
        }

        .mobile-jobs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .mobile-jobs-grid .job-card {
            margin-bottom: 0;
            padding: 12px;
        }

        .mobile-jobs-grid .vehicle-number {
            font-size: 14px;
        }

        .mobile-jobs-grid .customer-name {
            font-size: 13px;
        }

        .mobile-jobs-grid .meta-item {
            font-size: 11px;
            padding: 2px 6px;
        }
    }

    @media (max-width: 400px) {
        .mobile-tab {
            padding: 8px 9px;
            min-height: 46px;
        }

        .mobile-tab .tab-title {
            font-size: 12px;
        }

        .mobile-jobs-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-head">
    <div>
        <h1>Live Job Board</h1>
        <p>Operational state of every active vehicle.</p>
    </div>
    <div>
        <a class="primary" href="{{ route('jobs.create') }}">+ New Job</a>
        <button class="tv-toggle-btn" id="tvToggle">📺 TV Mode</button>
    </div>
</div>

{{-- ==================== DESKTOP BOARD ==================== --}}
<div class="kanban-board" id="jobBoard">
    @foreach($kanbanColumns as $column)
    <div class="kanban-column" data-status="{{ $column['id'] }}">
        <div class="column-header">
            <h3 class="column-title">{{ $column['title'] }}</h3>
            <span class="column-count">{{ $jobs->get($column['id'], collect())->count() }}</span>
        </div>

        @php $columnJobs = $jobs->get($column['id'], collect())->take(5); @endphp

        @foreach($columnJobs as $j)
        <a class="job-card" href="{{ route('jobs.show', $j) }}">
            <div class="card-header">
                <h4 class="vehicle-number">{{ $j->vehicle->registration_number }}</h4>
                <span class="priority-badge priority-{{ $j->priority }}">{{ ucfirst($j->priority) }}</span>
            </div>
            <p class="customer-name">{{ $j->customer->full_name }}</p>
            <div class="card-meta">
                <div class="meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    {{ $j->job_number }}
                </div>
                @if($j->vehicle->make)
                <div class="meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                    {{ $j->vehicle->make }}
                </div>
                @endif
            </div>
            @if($j->technician)
            <div class="technician-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                {{ $j->technician->name }}
            </div>
            @endif
            @if($j->branch && !auth()->user()->branch_id)
            <div class="branch-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                {{ $j->branch->name }}
            </div>
            @endif
            <div class="card-footer">
                <div class="time-badge">
                    <span class="status-dot status-{{ str_replace('_', '-', $column['id']) }}"></span>
                    {{ $j->created_at->diffForHumans() }}
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endforeach
</div>

{{-- ==================== MOBILE TABS ==================== --}}
<div class="mobile-tabs" id="mobileTabs">
    @foreach($kanbanColumns as $index => $column)
        <button class="mobile-tab {{ $index === 0 ? 'active' : '' }}" 
                data-status="{{ $column['id'] }}">
            <span class="tab-title">{{ $column['title'] }}</span>
            <span class="count">{{ $jobs->get($column['id'], collect())->count() }}</span>
        </button>
    @endforeach
</div>

{{-- ==================== MOBILE COLUMNS ==================== --}}
@foreach($kanbanColumns as $index => $column)
<div class="mobile-column {{ $index === 0 ? 'active' : '' }}" 
     id="mobile-{{ $column['id'] }}">
    
    <div class="column-header">
        <h3 class="column-title">{{ $column['title'] }}</h3>
        <span class="column-count">{{ $jobs->get($column['id'], collect())->count() }}</span>
    </div>

    <div class="mobile-jobs-grid">
        @php $columnJobs = $jobs->get($column['id'], collect())->take(8); @endphp

        @forelse($columnJobs as $j)
            <a class="job-card" href="{{ route('jobs.show', $j) }}">
                <div class="card-header">
                    <h4 class="vehicle-number">{{ $j->vehicle->registration_number }}</h4>
                    <span class="priority-badge priority-{{ $j->priority }}">{{ ucfirst($j->priority) }}</span>
                </div>
                <p class="customer-name">{{ $j->customer->full_name }}</p>
                <div class="card-meta">
                    <div class="meta-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        {{ $j->job_number }}
                    </div>
                    @if($j->vehicle->make)
                    <div class="meta-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                        {{ $j->vehicle->make }}
                    </div>
                    @endif
                </div>
                @if($j->technician)
                <div class="technician-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    {{ $j->technician->name }}
                </div>
                @endif
                @if($j->branch && !auth()->user()->branch_id)
                <div class="branch-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    {{ $j->branch->name }}
                </div>
                @endif
                <div class="card-footer">
                    <div class="time-badge">
                        <span class="status-dot status-{{ str_replace('_', '-', $column['id']) }}"></span>
                        {{ $j->created_at->diffForHumans() }}
                    </div>
                </div>
            </a>
        @empty
            <p style="text-align:center; color:#94a3b8; padding: 30px 0; font-size: 14px; grid-column: 1 / -1;">
                No jobs in this stage
            </p>
        @endforelse
    </div>
</div>
@endforeach

<script>
// Mobile Tabs
document.querySelectorAll('.mobile-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.mobile-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        document.querySelectorAll('.mobile-column').forEach(col => col.classList.remove('active'));
        const status = this.getAttribute('data-status');
        document.getElementById('mobile-' + status)?.classList.add('active');
    });
});

// TV Mode
let sidebarStateBeforeTV = null;
const tvToggle = document.getElementById('tvToggle');

tvToggle.addEventListener('click', () => {
    const sidebar = document.getElementById('sidebar');
    const main = document.querySelector('.main');

    if (!document.body.classList.contains('tv-mode')) {
        sidebarStateBeforeTV = {
            sidebarCollapsed: sidebar?.classList.contains('collapsed'),
            mainExpanded: main?.classList.contains('expanded')
        };
    }

    document.body.classList.toggle('tv-mode');

    if (document.body.classList.contains('tv-mode')) {
        tvToggle.textContent = '❌ Exit TV Mode';
        document.documentElement.requestFullscreen?.();
    } else {
        tvToggle.textContent = '📺 TV Mode';
        document.exitFullscreen?.();
        if (sidebarStateBeforeTV) {
            sidebar?.classList.toggle('collapsed', sidebarStateBeforeTV.sidebarCollapsed);
            main?.classList.toggle('expanded', sidebarStateBeforeTV.mainExpanded);
        }
    }
});

// Auto Refresh
setInterval(() => {
    fetch('{{ route('jobs.board') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.text())
    .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newBoard = doc.getElementById('jobBoard');
        const current = document.getElementById('jobBoard');
        if (newBoard && current) current.innerHTML = newBoard.innerHTML;
    });
}, 5000);
</script>
@endsection