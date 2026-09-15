@extends('layouts.app')

@section('content')

<style>
    .grn-report-page {
        max-width: 100%;
    }

    .page-head-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filters {
        margin-bottom: 28px;
    }

    .filter-form {
        display: flex;
        align-items: flex-end;
        gap: 14px;
        flex-wrap: wrap;
    }

    .filter-form > div {
        min-width: 145px;
    }

    .filter-form label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .filter-form input[type="date"],
    .filter-form select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        background: #fff;
        font-size: 14px;
        box-sizing: border-box;
    }

    .report-title {
        text-align: center;
        margin: 6px 0 28px;
    }

    .report-title h1 {
        margin: 0 0 7px;
        font-size: 24px;
        font-weight: 700;
        color: #111827;
    }

    .report-title p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 28px;
    }

    .report-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        background: #fff;
    }

    .report-table th {
        padding: 13px 12px;
        text-align: left;
        background: #f3f4f6;
        border-bottom: 1px solid #d1d5db;
        color: #374151;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        white-space: nowrap;
    }

    .report-table td {
        padding: 13px 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
        font-size: 13px;
        vertical-align: middle;
    }

    .report-table tbody tr:hover {
        background: #f9fafb;
    }

    .text-right {
        text-align: right !important;
    }

    .date-cell {
        white-space: nowrap;
        color: #4b5563;
        font-size: 12px !important;
    }

    .supplier-section {
        margin-bottom: 24px;
    }

    .supplier-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 12px;
    }

    .supplier-header h3 {
        margin: 0 0 4px;
        font-size: 18px;
        font-weight: 600;
    }

    .supplier-header p {
        margin: 0;
        font-size: 13px;
        opacity: 0.9;
    }

    .supplier-total {
        background: #f3f4f6;
        padding: 12px 20px;
        border-radius: 8px;
        margin-top: 12px;
        text-align: right;
        font-weight: 600;
        color: #111827;
    }

    .grand-total {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        text-align: right;
        font-size: 18px;
        font-weight: 700;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-draft {
        background: #f59e0b;
        color: white;
    }

    .status-confirmed {
        background: #10b981;
        color: white;
    }

    .status-deleted {
        background: #ef4444;
        color: white;
    }

    .empty {
        text-align: center !important;
        padding: 40px !important;
        color: #9ca3af !important;
    }

    .report-back {
        margin-top: 18px;
        display: flex;
        justify-content: flex-end;
    }

    .report-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0;
    }

    .report-tab {
        padding: 12px 24px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
    }

    .report-tab:hover {
        color: #374151;
    }

    .report-tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }

    .report-tab-content {
        display: none;
    }

    .report-tab-content.active {
        display: block;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        background: #e5e7eb;
        color: #374151;
        border-radius: 9px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .back-button:hover {
        background: #d1d5db;
    }

    @media (max-width: 768px) {
        .page-head {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .page-head-actions {
            width: 100%;
        }

        .page-head-actions button {
            flex: 1;
        }

        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-form > div {
            width: 100%;
        }

        .table-wrap {
            display: none;
        }

        .report-back {
            justify-content: center;
        }
    }
</style>

<div class="grn-report-page">
    <!-- PAGE HEADER -->
    <div class="page-head">
        <div>
            <h1 id="page-title">GRN / Receiving Report</h1>
            <p id="page-subtitle">Products received from suppliers by date range</p>
        </div>

        <div class="page-head-actions">
            <button type="button" onclick="window.print()" class="primary">
                📄 Print Report
            </button>
        </div>
    </div>

    <!-- SCREEN REPORT -->
    <div id="screen-report" class="panel">

        <!-- Report Tabs -->
        <div class="report-tabs">
            <button class="report-tab {{ $activeTab == 'grn' ? 'active' : '' }}" onclick="switchTab('grn', this)">GRN / Receiving</button>
            <button class="report-tab {{ $activeTab == 'return-grn' ? 'active' : '' }}" onclick="switchTab('return-grn', this)">Return GRN</button>
        </div>

        <!-- GRN Tab Content -->
        <div id="grn-tab" class="report-tab-content {{ $activeTab == 'grn' ? 'active' : '' }}">
            <!-- Filters -->
        <div class="filters">
            <form
                method="GET"
                action="{{ route('reports.grn') }}"
                class="filter-form"
            >
                <div>
                    <label for="start_date">Start Date</label>
                    <input
                        id="start_date"
                        type="date"
                        name="start_date"
                        value="{{ $startDate->format('Y-m-d') }}"
                    >
                </div>

                <div>
                    <label for="end_date">End Date</label>
                    <input
                        id="end_date"
                        type="date"
                        name="end_date"
                        value="{{ $endDate->format('Y-m-d') }}"
                    >
                </div>

                <div>
                    <label for="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="primary">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Report title -->
        <div class="report-title">
            <h1 id="grn-tab-title">GRN / Receiving Report</h1>
            <p>
                Period:
                {{ $startDate->format('Y-m-d') }}
                to
                {{ $endDate->format('Y-m-d') }}
            </p>
        </div>

        <!-- Grouped by Supplier -->
        @forelse($grnsBySupplier as $supplierName => $grns)
            <div class="supplier-section">
                <div class="supplier-header">
                    <h3>{{ $supplierName }}</h3>
                    <p>{{ count($grns) }} GRN(s) in this period</p>
                </div>

                <div class="table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>GRN Number</th>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th class="text-right">Items</th>
                                <th class="text-right">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grns as $grn)
                                <tr>
                                    <td>
                                        <strong>{{ $grn->grn_number }}</strong>
                                    </td>
                                    <td class="date-cell">
                                        {{ $grn->received_at ? $grn->received_at->format('Y-m-d H:i') : '-' }}
                                    </td>
                                    <td>{{ $grn->reference ?? '-' }}</td>
                                    <td>
                                        <span class="status-badge status-{{ strtolower($grn->status?->value ?? 'draft') }}">
                                            {{ $grn->status?->value ?? 'Draft' }}
                                        </span>
                                    </td>
                                    <td class="text-right">{{ $grn->items->count() }}</td>
                                    <td class="text-right">
                                        Rs. {{ number_format($grn->items->sum(function($item) {
                                            return ($item->unit_cost ?? 0) * $item->quantity;
                                        }), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="supplier-total">
                    Supplier Total: Rs. {{ number_format(collect($grns)->sum(function($grn) {
                        return $grn->items->sum(function($item) {
                            return ($item->unit_cost ?? 0) * $item->quantity;
                        });
                    }), 2) }}
                </div>
            </div>
        @empty
            <div class="table-wrap">
                <table class="report-table">
                    <tbody>
                        <tr>
                            <td colspan="6" class="empty">
                                No GRNs found for this period.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforelse

        <!-- Grand Total -->
        @if($grnsBySupplier->isNotEmpty())
            <div class="grand-total">
                Grand Total: Rs. {{ number_format($grandTotal, 2) }}
            </div>
        @endif

        <!-- Back button -->
        <div class="report-back">
            <a href="{{ route('reports') }}" class="back-button">
                ← Back to Reports
            </a>
        </div>
        </div>

        <!-- Return GRN Tab Content -->
        <div id="return-grn-tab" class="report-tab-content {{ $activeTab == 'return-grn' ? 'active' : '' }}">
            <!-- Filters -->
            <div class="filters">
                <form
                    method="GET"
                    action="{{ route('reports.grn') }}"
                    class="filter-form"
                    id="return-grn-form"
                >
                    <input type="hidden" name="tab" value="return-grn">
                    <div>
                        <label for="return_start_date">Start Date</label>
                        <input
                            id="return_start_date"
                            type="date"
                            name="start_date"
                            value="{{ $startDate->format('Y-m-d') }}"
                        >
                    </div>

                    <div>
                        <label for="return_end_date">End Date</label>
                        <input
                            id="return_end_date"
                            type="date"
                            name="end_date"
                            value="{{ $endDate->format('Y-m-d') }}"
                        >
                    </div>

                    <div>
                        <label for="return_supplier_id">Supplier</label>
                        <select id="return_supplier_id" name="supplier_id">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="primary">
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>

            <!-- Report title -->
            <div class="report-title">
                <h1 id="return-grn-tab-title">Return GRN Report</h1>
                <p>
                    Period:
                    {{ $startDate->format('Y-m-d') }}
                    to
                    {{ $endDate->format('Y-m-d') }}
                </p>
            </div>

            <!-- Grouped by Supplier -->
            @forelse($returnGrnsBySupplier as $supplierName => $returnGrns)
                <div class="supplier-section">
                    <div class="supplier-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        <h3>{{ $supplierName }}</h3>
                        <p>{{ count($returnGrns) }} Return GRN(s) in this period</p>
                    </div>

                    <div class="table-wrap">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Return GRN Number</th>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th class="text-right">Items</th>
                                    <th class="text-right">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returnGrns as $returnGrn)
                                    <tr>
                                        <td>
                                            <strong>{{ $returnGrn->return_grn_number }}</strong>
                                        </td>
                                        <td class="date-cell">
                                            {{ $returnGrn->returned_at ? $returnGrn->returned_at->format('Y-m-d H:i') : '-' }}
                                        </td>
                                        <td>{{ $returnGrn->reference ?? '-' }}</td>
                                        <td>{{ $returnGrn->reason ?? '-' }}</td>
                                        <td>
                                            <span class="status-badge status-{{ $returnGrn->status_id == 56 ? 'draft' : ($returnGrn->status_id == 57 ? 'confirmed' : 'deleted') }}">
                                                {{ $returnGrn->status?->value ?? 'Draft' }}
                                            </span>
                                        </td>
                                        <td class="text-right">{{ $returnGrn->items->count() }}</td>
                                        <td class="text-right">
                                            Rs. {{ number_format($returnGrn->items->sum(function($item) {
                                                return ($item->unit_cost ?? 0) * $item->quantity;
                                            }), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="supplier-total">
                        Supplier Total: Rs. {{ number_format(collect($returnGrns)->sum(function($returnGrn) {
                            return $returnGrn->items->sum(function($item) {
                                return ($item->unit_cost ?? 0) * $item->quantity;
                            });
                        }), 2) }}
                    </div>
                </div>
            @empty
                <div class="table-wrap">
                    <table class="report-table">
                        <tbody>
                            <tr>
                                <td colspan="7" class="empty">
                                    No Return GRNs found for this period.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforelse

            <!-- Grand Total -->
            @if($returnGrnsBySupplier->isNotEmpty())
                <div class="grand-total" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    Grand Total: Rs. {{ number_format($returnGrandTotal, 2) }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function switchTab(tabName, clickedElement = null) {
    // Hide all tab contents
    document.querySelectorAll('.report-tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.report-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked tab or the appropriate tab based on name
    if (clickedElement) {
        clickedElement.classList.add('active');
    } else {
        // Find the tab by onclick attribute
        const tabs = document.querySelectorAll('.report-tab');
        tabs.forEach(tab => {
            if (tab.getAttribute('onclick')?.includes(tabName)) {
                tab.classList.add('active');
            }
        });
    }
    
    // Update page title
    const pageTitle = document.getElementById('page-title');
    const pageSubtitle = document.getElementById('page-subtitle');
    
    if (tabName === 'return-grn') {
        pageTitle.textContent = 'Return GRN Report';
        pageSubtitle.textContent = 'Items returned to suppliers by date range';
    } else {
        pageTitle.textContent = 'GRN / Receiving Report';
        pageSubtitle.textContent = 'Products received from suppliers by date range';
    }
}

// Initialize tab state on page load
document.addEventListener('DOMContentLoaded', function() {
    const activeTab = '{{ $activeTab ?? 'grn' }}';
    if (activeTab === 'return-grn') {
        switchTab('return-grn');
    }
});
</script>
@endsection
