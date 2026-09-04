@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Operations Dashboard</h1>
        <p>Everything happening in your workshop, at a glance.</p>
    </div>
    <a class="primary" href="{{ route('jobs.create') }}">+ New Job Card</a>
</div>

<section class="panel quick-actions-panel">
    <h2>Quick Actions</h2>
    <div class="actions">
        <a href="{{ route('customers.create') }}" class="action-card action-blue">
            <span class="icon">👤</span>
            <span>Register Customer</span>
        </a>
        <a href="{{ route('vehicles.create') }}" class="action-card action-green">
            <span class="icon">🚗</span>
            <span>Register Vehicle</span>
        </a>
        <a href="{{ route('appointments.create') }}" class="action-card action-purple">
            <span class="icon">📅</span>
            <span>Book Appointment</span>
        </a>
        <a href="{{ route('inventory.create') }}" class="action-card action-orange">
            <span class="icon">📦</span>
            <span>Add Product</span>
        </a>
        <a href="{{ route('jobs.board') }}" class="action-card action-cyan">
            <span class="icon">📋</span>
            <span>Open Job Board</span>
        </a>
        <a href="{{ route('reports') }}" class="action-card action-pink">
            <span class="icon">📊</span>
            <span>View Reports</span>
        </a>
    </div>
</section>

<div class="stats">
    <div class="stat-card stat-blue">
        <small>Vehicles Today</small>
        <b>{{ $vehiclesToday }}</b>
        <span class="trend">+{{ $vehiclesToday > 0 ? '12%' : '0%' }}</span>
    </div>
    <div class="stat-card stat-green">
        <small>Active Jobs</small>
        <b>{{ $activeJobs }}</b>
        <span class="trend">In Progress</span>
    </div>
    <div class="stat-card stat-purple">
        <small>Completed This Month</small>
        <b>{{ $completedJobs }}</b>
        <span class="trend">{{ $completedJobs > 0 ? '+8%' : '0%' }}</span>
    </div>
    <div class="stat-card stat-cyan">
        <small>Today's Revenue</small>
        <b>{{ number_format($revenue, 2) }}</b>
        <span class="trend">{{ $revenue > 0 ? '+15%' : '0%' }}</span>
    </div>
    <div class="stat-card stat-orange">
        <small>Pending Payments</small>
        <b>{{ number_format($pendingPayments, 2) }}</b>
        <span class="trend">Outstanding</span>
    </div>
    <div class="stat-card stat-red">
        <small>Low Stock Alerts</small>
        <b>{{ $lowStock }}</b>
        <span class="trend">{{ $lowStock > 0 ? 'Action Needed' : 'OK' }}</span>
    </div>
</div>

<div class="stats-secondary">
    <div class="stat-card-secondary">
        <small>Monthly Revenue</small>
        <b>{{ number_format($monthlyRevenue ?? 0, 2) }}</b>
    </div>
    <div class="stat-card-secondary">
        <small>Monthly Jobs</small>
        <b>{{ $monthlyJobs ?? 0 }}</b>
    </div>
    <div class="stat-card-secondary">
        <small>Avg Job Value</small>
        <b>{{ $monthlyJobs > 0 ? number_format($monthlyRevenue / $monthlyJobs, 2) : '0.00' }}</b>
    </div>
    <div class="stat-card-secondary">
        <small>Payment Rate</small>
        <b>{{ $paymentRate ?? 0 }}%</b>
    </div>
</div>

<div class="grid2">
    <section class="panel">
        <h2>🚗 Top Vehicle Categories by Revenue</h2>
        <div class="insights-list">
            @if($vehicleRevenue->count() > 0)
                @foreach($vehicleRevenue as $index => $vehicle)
                <div class="insight-item">
                    <div class="insight-rank">{{ $index + 1 }}</div>
                    <div class="insight-content">
                        <strong>{{ $vehicle->category }}</strong>
                        <small>{{ $vehicle->count }} vehicles</small>
                    </div>
                    <div class="insight-value">{{ number_format($vehicle->total_revenue, 2) }}</div>
                </div>
                @endforeach
            @else
                <p class="no-data">No revenue data available</p>
            @endif
        </div>
    </section>
    
    <section class="panel">
        <h2>👥 Frequent Customers</h2>
        <div class="insights-list">
            @if($frequentCustomers->count() > 0)
                @foreach($frequentCustomers as $index => $customer)
                <div class="insight-item">
                    <div class="insight-rank">{{ $index + 1 }}</div>
                    <div class="insight-content">
                        <strong>{{ $customer->full_name }}</strong>
                        <small>{{ $customer->job_count }} visits • {{ number_format($customer->total_spent, 2) }} spent</small>
                    </div>
                </div>
                @endforeach
            @else
                <p class="no-data">No customer data available</p>
            @endif
        </div>
    </section>
    
    <section class="panel panel-full">
        <h2>🔧 Most Popular Services</h2>
        <div class="services-chart">
            @if($servicePopularity->count() > 0)
                @foreach($servicePopularity as $index => $service)
                <div class="service-bar">
                    <div class="service-info">
                        <span class="service-name">{{ $service->name }}</span>
                        <span class="service-count">{{ $service->count }}</span>
                    </div>
                    <div class="service-progress">
                        <div class="service-fill" style="width: {{ ($service->count / $servicePopularity->first()->count) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            @else
                <p class="no-data">No service data available</p>
            @endif
        </div>
    </section>
</div>

<div class="panel panel-full">
    <h2>📦 Low Stock Items</h2>
    <div class="stock-list">
        @if($lowStockItems->count() > 0)
            @foreach($lowStockItems as $item)
            <div class="stock-item">
                <div class="stock-info">
                    <strong>{{ $item->name }}</strong>
                    <small>{{ $item->category ?? 'Uncategorized' }}</small>
                </div>
                <div class="stock-quantity {{ $item->quantity <= 5 ? 'critical' : 'warning' }}">
                    {{ $item->quantity }} {{ $item->unit ?? 'pcs' }}
                </div>
            </div>
            @endforeach
        @else
            <p class="no-data">All items are well stocked</p>
        @endif
    </div>
</div>

<style>
/* ========== BASE STYLES (DESKTOP - UNTOUCHED) ========== */
.page-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.page-head h1 {
    font-size: 32px;
    margin: 0 0 6px 0;
    color: #1a1a2e;
    line-height: 1.2;
}

.page-head p {
    margin: 0;
    color: #666;
    font-size: 15px;
}

.page-head a.primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    background: #4a90e2;
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    white-space: nowrap;
    transition: all 0.25s ease;
}

.page-head a.primary:hover {
    background: #357abd;
    transform: translateY(-2px);
}

/* Quick Actions */
.quick-actions-panel {
    margin-bottom: 30px;
}

.quick-actions-panel h2 {
    font-size: 22px;
    margin-bottom: 18px;
    color: #1a1a2e;
}

.actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 14px;
}

.actions .action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 22px 16px;
    border-radius: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    text-align: center;
    min-height: 110px;
}

.actions .action-card .icon {
    font-size: 36px;
    margin-bottom: 10px;
    line-height: 1;
}

.actions .action-card span:not(.icon) {
    font-size: 13.5px;
    font-weight: 600;
    color: #1a1a2e;
    line-height: 1.3;
}

.actions .action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.15);
}

.actions .action-card.action-blue {
    background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%);
}
.actions .action-card.action-blue:hover {
    background: linear-gradient(135deg, #64b5f6 0%, #42a5f5 100%);
}

.actions .action-card.action-green {
    background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
}
.actions .action-card.action-green:hover {
    background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%);
}

.actions .action-card.action-purple {
    background: linear-gradient(135deg, #e1bee7 0%, #ce93d8 100%);
}
.actions .action-card.action-purple:hover {
    background: linear-gradient(135deg, #ba68c8 0%, #ab47bc 100%);
}

.actions .action-card.action-orange {
    background: linear-gradient(135deg, #ffe0b2 0%, #ffcc80 100%);
}
.actions .action-card.action-orange:hover {
    background: linear-gradient(135deg, #ffb74d 0%, #ffa726 100%);
}

.actions .action-card.action-cyan {
    background: linear-gradient(135deg, #b2dfdb 0%, #80cbc4 100%);
}
.actions .action-card.action-cyan:hover {
    background: linear-gradient(135deg, #4db6ac 0%, #26a69a 100%);
}

.actions .action-card.action-pink {
    background: linear-gradient(135deg, #f8bbd0 0%, #f48fb1 100%);
}
.actions .action-card.action-pink:hover {
    background: linear-gradient(135deg, #f06292 0%, #ec407a 100%);
}

/* Stats Cards */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    padding: 20px 16px;
    border-radius: 16px;
    color: #1a1a2e;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    text-align: center;
}

.stat-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.05) 100%);
    pointer-events: none;
    border-radius: 16px;
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 36px rgba(0, 0, 0, 0.12);
}

.stat-card small {
    display: block;
    font-size: 11px;
    margin-bottom: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    opacity: 0.8;
}

.stat-card b {
    display: block;
    font-size: 28px;
    margin-bottom: 10px;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.stat-card .trend {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.45);
    padding: 5px 14px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.stat-card.stat-blue {
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.12) 0%, rgba(53, 122, 189, 0.05) 100%);
    border-color: rgba(74, 144, 226, 0.3);
}
.stat-card.stat-green {
    background: linear-gradient(135deg, rgba(39, 174, 96, 0.12) 0%, rgba(30, 132, 73, 0.05) 100%);
    border-color: rgba(39, 174, 96, 0.3);
}
.stat-card.stat-purple {
    background: linear-gradient(135deg, rgba(155, 89, 182, 0.12) 0%, rgba(125, 60, 152, 0.05) 100%);
    border-color: rgba(155, 89, 182, 0.3);
}
.stat-card.stat-cyan {
    background: linear-gradient(135deg, rgba(26, 188, 156, 0.12) 0%, rgba(22, 160, 133, 0.05) 100%);
    border-color: rgba(26, 188, 156, 0.3);
}
.stat-card.stat-orange {
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.12) 0%, rgba(214, 137, 16, 0.05) 100%);
    border-color: rgba(243, 156, 18, 0.3);
}
.stat-card.stat-red {
    background: linear-gradient(135deg, rgba(231, 76, 60, 0.12) 0%, rgba(192, 57, 43, 0.05) 100%);
    border-color: rgba(231, 76, 60, 0.3);
}

/* Secondary Stats */
.stats-secondary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card-secondary {
    padding: 18px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #4a90e2;
}

.stat-card-secondary small {
    display: block;
    font-size: 13px;
    color: #666;
    margin-bottom: 6px;
}

.stat-card-secondary b {
    display: block;
    font-size: 22px;
    color: #1a1a2e;
}

/* Grid & Panels */
.grid2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.panel {
    background: white;
    padding: 22px;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
}

.panel h2 {
    font-size: 18px;
    margin: 0 0 18px 0;
    color: #1a1a2e;
}

.panel-full {
    grid-column: 1 / -1;
}

/* Insights */
.insights-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.insight-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.25s ease;
}

.insight-item:hover {
    background: #eef1f5;
    transform: translateX(4px);
}

.insight-rank {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    flex-shrink: 0;
}

.insight-content {
    flex: 1;
    min-width: 0;
}

.insight-content strong {
    display: block;
    font-size: 14px;
    color: #1a1a2e;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.insight-content small {
    display: block;
    font-size: 12px;
    color: #666;
}

.insight-value {
    font-weight: 700;
    font-size: 14px;
    color: #4a90e2;
    white-space: nowrap;
}

/* Services Chart - Improved Alignment */
.services-chart {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.service-bar {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 14px 16px;
}

.service-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    gap: 12px;
}

.service-name {
    font-weight: 600;
    font-size: 14px;
    color: #1a1a2e;
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.service-count {
    font-weight: 700;
    font-size: 14px;
    color: #4a90e2;
    flex-shrink: 0;
    min-width: 24px;
    text-align: right;
}

.service-progress {
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    width: 100%;
}

.service-fill {
    height: 100%;
    background: linear-gradient(90deg, #4a90e2 0%, #357abd 100%);
    border-radius: 4px;
    transition: width 0.5s ease;
}

/* Stock List */
.stock-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.stock-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.25s ease;
}

.stock-item:hover {
    background: #eef1f5;
    transform: translateX(4px);
}

.stock-info {
    flex: 1;
    min-width: 0;
}

.stock-info strong {
    display: block;
    font-size: 14px;
    color: #1a1a2e;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stock-info small {
    display: block;
    font-size: 12px;
    color: #666;
}

.stock-quantity {
    font-weight: 700;
    font-size: 13px;
    padding: 5px 12px;
    border-radius: 20px;
    background: #fff3cd;
    color: #856404;
    white-space: nowrap;
    flex-shrink: 0;
}

.stock-quantity.critical {
    background: #f8d7da;
    color: #721c24;
}

.stock-quantity.warning {
    background: #fff3cd;
    color: #856404;
}

.no-data {
    text-align: center;
    color: #999;
    padding: 24px 16px;
    font-style: italic;
    margin: 0;
}

/* ========== RESPONSIVE ONLY ========== */

/* Tablet & below */
@media (max-width: 1024px) {
    .page-head h1 {
        font-size: 28px;
    }

    .actions {
        grid-template-columns: repeat(3, 1fr);
    }

    .stats {
        grid-template-columns: repeat(3, 1fr);
    }

    .stats-secondary {
        grid-template-columns: repeat(2, 1fr);
    }

    .grid2 {
        grid-template-columns: 1fr 1fr;
    }

    .panel-full {
        grid-column: 1 / -1;
    }
}

/* Mobile (main changes you requested) */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 22px;
    }

    .page-head h1 {
        font-size: 24px;
    }

    .page-head p {
        font-size: 14px;
    }

    .page-head a.primary {
        width: 100%;
        padding: 12px 16px;
        font-size: 14px;
    }

    .quick-actions-panel h2 {
        font-size: 18px;
        margin-bottom: 14px;
    }

    /* 1. Quick Actions → 2 cards per row */
    .actions {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px;
    }

    .actions .action-card {
        padding: 18px 12px;
        min-height: 100px;
    }

    .actions .action-card .icon {
        font-size: 30px;
        margin-bottom: 8px;
    }

    .actions .action-card span:not(.icon) {
        font-size: 12.5px;
    }

    /* 2. Main Stats → 2 cards per row */
    .stats {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat-card {
        padding: 16px 12px;
    }

    .stat-card b {
        font-size: 24px;
    }

    /* 3. Secondary Stats → 2 cards per row */
    .stats-secondary {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat-card-secondary {
        padding: 14px 16px;
    }

    .stat-card-secondary b {
        font-size: 20px;
    }

    .grid2 {
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }

    .panel {
        padding: 18px;
    }

    .panel h2 {
        font-size: 16px;
        margin-bottom: 14px;
    }

    /* Better services alignment on mobile */
    .service-bar {
        padding: 12px 14px;
    }

    .service-info {
        margin-bottom: 8px;
    }
}

/* Smaller mobile */
@media (max-width: 480px) {
    .page-head h1 {
        font-size: 22px;
    }

    .page-head p {
        font-size: 13px;
    }

    /* Keep 2 cards per row */
    .actions {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }

    .actions .action-card {
        padding: 16px 10px;
        min-height: 95px;
    }

    .actions .action-card .icon {
        font-size: 26px;
        margin-bottom: 6px;
    }

    .actions .action-card span:not(.icon) {
        font-size: 11.5px;
    }

    .stats {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }

    .stat-card {
        padding: 14px 10px;
    }

    .stat-card small {
        font-size: 10px;
        letter-spacing: 0.8px;
    }

    .stat-card b {
        font-size: 22px;
        margin-bottom: 8px;
    }

    .stat-card .trend {
        font-size: 10px;
        padding: 4px 10px;
    }

    .stats-secondary {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }

    .stat-card-secondary {
        padding: 14px 14px;
    }

    .stat-card-secondary b {
        font-size: 18px;
    }

    .panel {
        padding: 16px;
    }

    .panel h2 {
        font-size: 15px;
    }

    .insight-item,
    .stock-item {
        padding: 10px 12px;
    }

    .insight-rank {
        width: 26px;
        height: 26px;
        font-size: 11px;
    }

    .insight-content strong,
    .stock-info strong,
    .service-name {
        font-size: 13px;
    }

    .insight-content small,
    .stock-info small {
        font-size: 11px;
    }

    .insight-value,
    .service-count,
    .stock-quantity {
        font-size: 13px;
    }

    .service-bar {
        padding: 12px;
    }
}

/* Very small phones - still keep 2 columns */
@media (max-width: 360px) {
    .actions,
    .stats,
    .stats-secondary {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px;
    }

    .actions .action-card {
        padding: 14px 8px;
        min-height: 90px;
    }

    .actions .action-card .icon {
        font-size: 24px;
    }

    .actions .action-card span:not(.icon) {
        font-size: 11px;
    }

    .stat-card b {
        font-size: 20px;
    }

    .stat-card-secondary b {
        font-size: 16px;
    }

    .page-head h1 {
        font-size: 20px;
    }
}
</style>
@endsection