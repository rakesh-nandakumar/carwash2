@extends('layouts.app')

@section('content')
<div class="panel">
    <h2>Available Reports</h2>
    <p>Select a report type to generate with date range filtering and export options.</p>
    
    <div class="reports-grid">
        {{-- Sales Report --}}
        <div class="report-card report-card-blue" onclick="window.location.href='{{ route('reports.sales') }}'">
            <div class="report-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <h3>Sales Report</h3>
            <p>Revenue, payments, and outstanding balances by date range.</p>
        </div>
        
        {{-- Stock Report --}}
        <div class="report-card report-card-green" onclick="window.location.href='{{ route('reports.stock') }}'">
            <div class="report-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
            </div>
            <h3>Stock Report</h3>
            <p>Current inventory levels, costs, and retail values.</p>
        </div>
        
        {{-- Stock Movement --}}
        <div class="report-card report-card-orange" onclick="window.location.href='{{ route('reports.stock-movement') }}'">
            <div class="report-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 1l4 4-4 4"/>
                    <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                    <path d="M7 23l-4-4 4-4"/>
                    <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                </svg>
            </div>
            <h3>Stock Movement</h3>
            <p>Inventory additions, adjustments, and consumption history.</p>
        </div>
        
        {{-- Services Report --}}
        <div class="report-card report-card-purple" onclick="window.location.href='{{ route('reports.services') }}'">
            <div class="report-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h3>Services Report</h3>
            <p>Service usage statistics and revenue by service type.</p>
        </div>
        
        {{-- Customer Report --}}
        <div class="report-card report-card-pink" onclick="window.location.href='{{ route('reports.customers') }}'">
            <div class="report-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3>Customer Report</h3>
            <p>Customer activity and job history by date range.</p>
        </div>
    </div>
</div>

<style>
.reports-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 30px;
}

/* Desktop - 3 columns, last card stays normal size */
@media (min-width: 900px) {
    .reports-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
}

.report-card {
    border-radius: 16px;
    padding: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
    text-align: center;
}

.report-card:hover {
    transform: translateY(-5px);
}

.report-icon {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.report-card h3 {
    margin: 0 0 8px 0;
    color: white;
    font-size: 18px;
}

.report-card p {
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 13px;
    line-height: 1.4;
}

/* Colors */
.report-card-blue {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
}

.report-card-green {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
}

.report-card-orange {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);
}

.report-card-purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    box-shadow: 0 4px 6px rgba(139, 92, 246, 0.2);
}

.report-card-pink {
    background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    box-shadow: 0 4px 6px rgba(236, 72, 153, 0.2);
}

/* Only stretch the last card on mobile (2-column layout) */
@media (max-width: 899px) {
    .reports-grid .report-card:last-child:nth-child(odd) {
        grid-column: 1 / -1;
    }
}
</style>
@endsection