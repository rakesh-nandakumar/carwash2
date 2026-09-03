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
        <h1>Stock Report</h1>
        <p>Current inventory levels, costs, and retail values.</p>
    </div>
    <div class="page-head-actions">
        <button onclick="window.print()" class="primary">📄 Print PDF</button>
        <button onclick="printThermal()" class="secondary" style="background:#f59e0b;color:white;">🖨️ Thermal</button>
    </div>
</div>

<div id="report-content" class="panel">
    <!-- Report Title -->
    <div class="report-title">
        <h1>Stock Report</h1>
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <small>Total Stock Value (Cost)</small>
            <h2>Rs. {{ number_format($totalStockValue, 2) }}</h2>
        </div>
        <div class="stat-card green">
            <small>Total Retail Value</small>
            <h2>Rs. {{ number_format($totalRetailValue, 2) }}</h2>
        </div>
    </div>

    <!-- Desktop Table -->
    <div class="table-wrap">
        <table class="report-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Cost Price</th>
                    <th class="text-right">Selling Price</th>
                    <th class="text-right">Total Cost</th>
                    <th class="text-right">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stock as $item)
                <tr>
                    <td>{{ $item->sku ?? '-' }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 3) }}</td>
                    <td class="text-right">Rs. {{ number_format($item->cost_price, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($item->selling_price, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($item->total_cost, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($item->total_value, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty">No stock items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards (2 per row) -->
    <div class="report-cards">
        @forelse($stock as $item)
        <div class="report-card">
            <div class="card-top">
                <strong>{{ $item->name }}</strong>
                <span class="sku">{{ $item->sku ?? '-' }}</span>
            </div>

            <div class="card-body">
                <div class="row">
                    <span class="label">Qty</span>
                    <span>{{ number_format($item->quantity, 3) }}</span>
                </div>
                <div class="row">
                    <span class="label">Cost</span>
                    <span>Rs. {{ number_format($item->cost_price, 2) }}</span>
                </div>
                <div class="row">
                    <span class="label">Selling</span>
                    <span>Rs. {{ number_format($item->selling_price, 2) }}</span>
                </div>
                <div class="row">
                    <span class="label">Total Cost</span>
                    <span>Rs. {{ number_format($item->total_cost, 2) }}</span>
                </div>
                <div class="row highlight">
                    <span class="label">Total Value</span>
                    <span class="text-green">Rs. {{ number_format($item->total_value, 2) }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">No stock items found.</div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap no-print">
        {{ $stock->links() }}
    </div>

    <!-- Back button (one row below, right aligned) -->
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

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    padding: 18px 20px;
    border-radius: 10px;
    border: 1px solid;
}

.stat-card small {
    font-size: 13px;
    font-weight: 500;
}

.stat-card h2 {
    margin: 6px 0 0 0;
    font-size: 22px;
}

.stat-card.blue {
    background: #eff6ff;
    border-color: #bfdbfe;
}
.stat-card.blue small { color: #1e40af; }
.stat-card.blue h2 { color: #1e40af; }

.stat-card.green {
    background: #ecfdf5;
    border-color: #a7f3d0;
}
.stat-card.green small { color: #065f46; }
.stat-card.green h2 { color: #065f46; }

/* Desktop Table */
.table-wrap {
    overflow-x: auto;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
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

.report-card .sku {
    display: inline-block;
    font-size: 11px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 6px;
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

.report-card .highlight {
    margin-top: 6px;
    padding-top: 8px;
    border-top: 1px solid #f3f4f6;
}

.report-card .text-green {
    color: #059669 !important;
    font-weight: 600 !important;
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

/* Back button - one row below, right aligned */
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

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .stat-card h2 {
        font-size: 20px;
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
STOCK REPORT
================================
Total Stock Value: Rs. {{ number_format($totalStockValue, 2) }}
Total Retail Value: Rs. {{ number_format($totalRetailValue, 2) }}
================================
@foreach($stock as $item)
{{ $item->name }} | Qty: {{ $item->quantity }} | Rs.{{ number_format($item->total_cost, 2) }}
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