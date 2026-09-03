@extends('layouts.app')

@section('content')
<style>
@media print {
    body * { visibility: hidden; }
    #report-content, #report-content * { visibility: visible; }
    #report-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print { display: none !important; }
}
</style>

<div class="page-head no-print">
    <div>
        <h1>Stock Movement Report</h1>
        <p>Inventory additions, adjustments, and consumption history.</p>
    </div>
    <div class="page-head-actions">
        <button onclick="window.print()" class="primary">📄 Print PDF</button>
        <button onclick="printThermal()" class="secondary" style="background:#f59e0b;color:white;">🖨️ Thermal</button>
    </div>
</div>

<div id="report-content" class="panel">
    <!-- Filters -->
    <div class="no-print filters">
        <form method="get" class="filter-form">
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}">
            </div>
            <div>
                <label>End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="filter-btn">
                <button type="submit" class="primary">Generate Report</button>
            </div>
        </form>
    </div>

    <!-- Report Title -->
    <div class="report-title">
        <h1>Stock Movement Report</h1>
        <p>Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <!-- Desktop Table -->
    <div class="table-wrap">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Type</th>
                    <th class="text-right">Quantity</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $movement->name }}</td>
                    <td>{{ $movement->sku ?? '-' }}</td>
                    <td>
                        @if($movement->type == 'add')
                            <span class="badge-add">Addition</span>
                        @elseif($movement->type == 'consume')
                            <span class="badge-consume">Consumption</span>
                        @elseif($movement->type == 'adjust')
                            <span class="badge-adjust">Adjustment</span>
                        @else
                            {{ $movement->type }}
                        @endif
                    </td>
                    <td class="text-right">{{ $movement->quantity }}</td>
                    <td>{{ $movement->reference ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty">No movements found for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards (2 per row) -->
    <div class="report-cards">
        @forelse($movements as $movement)
        <div class="report-card">
            <div class="card-top">
                <strong>{{ $movement->name }}</strong>
                <span class="date">{{ \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d H:i') }}</span>
            </div>

            <div class="card-body">
                <div class="row">
                    <span class="label">SKU</span>
                    <span>{{ $movement->sku ?? '-' }}</span>
                </div>
                <div class="row">
                    <span class="label">Type</span>
                    <span>
                        @if($movement->type == 'add')
                            <span class="badge-add">Addition</span>
                        @elseif($movement->type == 'consume')
                            <span class="badge-consume">Consumption</span>
                        @elseif($movement->type == 'adjust')
                            <span class="badge-adjust">Adjustment</span>
                        @else
                            {{ $movement->type }}
                        @endif
                    </span>
                </div>
                <div class="row">
                    <span class="label">Quantity</span>
                    <span>{{ $movement->quantity }}</span>
                </div>
                <div class="row">
                    <span class="label">Reference</span>
                    <span>{{ $movement->reference ?? '-' }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">No movements found for this period.</div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap no-print">
        {{ $movements->links() }}
    </div>

    <div class="report-footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <!-- Back button (one row below Generated text, right aligned) -->
    <div class="report-back no-print">
        <a href="{{ route('reports') }}" class="back-button">← Back</a>
    </div>
</div>

<style>
.page-head-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Filters */
.filters {
    margin-bottom: 24px;
}

.filter-form {
    display: flex;
    gap: 12px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-form label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
}

.filter-form input[type="date"] {
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
}

/* Report Title */
.report-title {
    text-align: center;
    margin-bottom: 28px;
}

.report-title h1 {
    margin: 0 0 8px 0;
    font-size: 24px;
}

.report-title p {
    margin: 0;
    color: #6b7280;
}

/* Desktop Table */
.table-wrap {
    overflow-x: auto;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}

.report-table th {
    padding: 12px;
    text-align: left;
    background: #f3f4f6;
    border-bottom: 2px solid #e5e7eb;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.report-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
    color: #374151;
}

.report-table .text-right {
    text-align: right;
}

.report-table .empty {
    text-align: center;
    color: #9ca3af;
    padding: 30px;
}

/* Badges */
.badge-add {
    color: #059669;
    font-weight: 500;
    background: #ecfdf5;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 12px;
}

.badge-consume {
    color: #dc2626;
    font-weight: 500;
    background: #fef2f2;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 12px;
}

.badge-adjust {
    color: #d97706;
    font-weight: 500;
    background: #fffbeb;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 12px;
}

/* Mobile Cards - 2 per row */
.report-cards {
    display: none;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.report-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.report-card .card-top {
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f3f4f6;
}

.report-card .card-top strong {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    line-height: 1.3;
    margin-bottom: 4px;
}

.report-card .date {
    font-size: 12px;
    color: #6b7280;
}

.report-card .card-body .row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    font-size: 13px;
}

.report-card .label {
    color: #6b7280;
    font-size: 12.5px;
    flex-shrink: 0;
    margin-right: 8px;
}

.report-card .card-body .row span:last-child {
    font-weight: 500;
    color: #111827;
    text-align: right;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 30px;
    color: #9ca3af;
    font-size: 14px;
}

.pagination-wrap {
    margin-top: 24px;
}

.report-footer {
    margin-top: 24px;
    text-align: center;
    color: #6b7280;
    font-size: 12px;
}

/* Back button - one row below Generated text, right aligned */
.report-back {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #e5e7eb;
    color: #374151;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s;
}

.back-button:hover {
    background: #d1d5db;
    color: #111827;
}

/* ========== MOBILE ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head-actions {
        width: 100%;
    }

    .page-head-actions a,
    .page-head-actions button {
        flex: 1;
        text-align: center;
    }

    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-form > div {
        width: 100%;
    }

    /* Hide table, show 2-column cards */
    .table-wrap {
        display: none;
    }

    .report-cards {
        display: grid;
    }

    .report-back {
        justify-content: center;
    }
}
</style>

<script>
function printThermal() {
    const content = `
STOCK MOVEMENT REPORT
Date: {{ $startDate }} to {{ $endDate }}
================================
@foreach($movements as $movement)
{{ \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d') }} | {{ $movement->name }} | {{ $movement->type }} | {{ $movement->quantity }}
@endforeach
================================
Generated: {{ now()->format('Y-m-d H:i') }}
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<pre style="font-family: monospace; font-size: 12px;">' + content + '</pre>');
    printWindow.document.close();
    printWindow.print();
}
</script>
@endsection