@extends('layouts.app')

@section('content')

<style>

    /* =========================================================
       PAGE HEADER
       ========================================================= */

    .stock-report-page {
        max-width: 100%;
    }

    .page-head-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .thermal-button {
        background: #f59e0b !important;
        color: #fff !important;
    }


    /* =========================================================
       FILTERS
       ========================================================= */

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

    .filter-form input[type="date"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        background: #fff;
        font-size: 14px;
        box-sizing: border-box;
    }

    .filter-btn {
        min-width: auto !important;
    }


    /* =========================================================
       REPORT TITLE
       ========================================================= */

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


    /* =========================================================
       DESKTOP TABLE
       ========================================================= */

    .table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .report-table {
        width: 100%;
        min-width: 800px;
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

    .report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .text-right {
        text-align: right !important;
    }

    .date-cell {
        white-space: nowrap;
        color: #4b5563;
        font-size: 12px !important;
    }

    .product-name {
        font-weight: 600;
        color: #111827;
    }

    .sku {
        color: #6b7280;
        font-family: monospace;
        font-size: 12px !important;
    }


    /* =========================================================
       MOVEMENT BADGES
       ========================================================= */

    .movement-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-positive {
        color: #047857;
        background: #ecfdf5;
    }

    .badge-negative {
        color: #dc2626;
        background: #fef2f2;
    }

    .badge-adjustment {
        color: #b45309;
        background: #fffbeb;
    }

    .badge-neutral {
        color: #4b5563;
        background: #f3f4f6;
    }


    /* =========================================================
       QUANTITY
       ========================================================= */

    .quantity-positive {
        color: #047857 !important;
        font-weight: 700 !important;
    }

    .quantity-negative {
        color: #dc2626 !important;
        font-weight: 700 !important;
    }

    .quantity-zero {
        color: #6b7280 !important;
        font-weight: 600 !important;
    }


    /* =========================================================
       REFERENCE
       ========================================================= */

    .reference {
        font-family: monospace;
        font-size: 11px;
        color: #4b5563;
        white-space: nowrap;
    }


    /* =========================================================
       EMPTY
       ========================================================= */

    .empty {
        text-align: center !important;
        padding: 40px !important;
        color: #9ca3af !important;
    }


    /* =========================================================
       PAGINATION  (Beautiful modern style)
       ========================================================= */

    .pagination-wrap {
        margin-top: 28px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .pagination-wrap > p,
    .pagination-wrap .text-sm {
        color: #6b7280;
        font-size: 13px;
        margin: 0;
    }

    .pagination-wrap nav {
        display: flex;
        justify-content: center;
    }

    .pagination-wrap .pagination,
    .pagination-wrap nav > div {
        display: flex;
        align-items: center;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination-wrap a,
    .pagination-wrap span {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none !important;
        color: #374151;
        background: #fff;
        border: 1px solid #e5e7eb;
        transition: all 0.15s ease;
        line-height: 1;
    }

    .pagination-wrap a:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #111827;
    }

    .pagination-wrap span[aria-current="page"],
    .pagination-wrap .active span,
    .pagination-wrap [aria-current="page"] span {
        background: #111827 !important;
        color: #fff !important;
        border-color: #111827 !important;
        font-weight: 600;
    }

    .pagination-wrap span[aria-disabled="true"],
    .pagination-wrap .disabled span {
        color: #9ca3af !important;
        background: #f9fafb !important;
        border-color: #e5e7eb !important;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .pagination-wrap svg,
    .pagination-wrap .pagination svg,
    nav[role="navigation"] svg {
        width: 16px !important;
        height: 16px !important;
        max-width: 16px !important;
        max-height: 16px !important;
    }

    .pagination-wrap a[rel="prev"],
    .pagination-wrap a[rel="next"] {
        font-weight: 500;
        padding: 0 14px;
    }


    /* =========================================================
       FOOTER
       ========================================================= */

    .report-footer {
        margin-top: 22px;
        text-align: center;
        color: #9ca3af;
        font-size: 12px;
    }

    .report-back {
        margin-top: 18px;
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
        border-radius: 9px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .back-button:hover {
        background: #d1d5db;
    }


    /* =========================================================
       MOBILE CARDS
       ========================================================= */

    .report-cards {
        display: none;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .report-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }

    .report-card .card-top {
        padding-bottom: 11px;
        margin-bottom: 10px;
        border-bottom: 1px solid #f3f4f6;
    }

    .report-card .card-top strong {
        display: block;
        color: #111827;
        font-size: 14px;
        line-height: 1.35;
        margin-bottom: 4px;
    }

    .report-card .date {
        color: #6b7280;
        font-size: 11px;
    }

    .report-card .row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        padding: 6px 0;
        font-size: 12px;
    }

    .report-card .label {
        flex-shrink: 0;
        color: #6b7280;
    }

    .report-card .value {
        text-align: right;
        color: #111827;
        font-weight: 500;
        word-break: break-word;
    }


    /* =========================================================
       PRINT PDF
       ========================================================= */

    .print-only {
        display: none;
    }

    @media print {

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            background: #fff !important;
        }

        body * {
            visibility: hidden;
        }

        #pdf-report,
        #pdf-report * {
            visibility: visible;
        }

        #pdf-report {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: #fff;
        }

        .no-print {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }

        .pdf-header {
            text-align: center;
            margin-bottom: 18px;
        }

        .pdf-header h1 {
            margin: 0 0 5px;
            font-size: 20px;
        }

        .pdf-header h2 {
            margin: 0 0 5px;
            font-size: 14px;
        }

        .pdf-header p {
            margin: 0;
            font-size: 11px;
            color: #555;
        }

        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .pdf-table th {
            padding: 6px;
            text-align: left;
            background: #f0f0f0;
            border: 1px solid #ccc;
            font-weight: bold;
        }

        .pdf-table td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .pdf-table .right {
            text-align: right;
        }

        .pdf-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

    }


    /* =========================================================
       MOBILE
       ========================================================= */

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

        .report-cards {
            display: grid;
        }

        .report-back {
            justify-content: center;
        }

    }


    @media (max-width: 500px) {

        .report-cards {
            grid-template-columns: 1fr;
        }

    }

</style>


<div class="stock-report-page">

    <!-- PAGE HEADER -->
    <div class="page-head no-print">
        <div>
            <h1>Stock Movement Report</h1>
            <p>
                Inventory additions, adjustments, consumption,
                transfers and stock history.
            </p>
        </div>

        <div class="page-head-actions">
            <button type="button" onclick="printPdf()" class="primary">
                📄 Print PDF
            </button>

            <button type="button" onclick="printThermal()" class="primary thermal-button">
                🖨️ Thermal
            </button>
        </div>
    </div>


    <!-- SCREEN REPORT -->
    <div id="screen-report" class="panel no-print">

        <!-- Filters -->
        <div class="filters">
            <form
                method="GET"
                action="{{ route('reports.stock-movement') }}"
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

                <div class="filter-btn">
                    <button type="submit" class="primary">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>


        <!-- Report title -->
        <div class="report-title">
            <h1>Stock Movement Report</h1>
            <p>
                Period:
                {{ $startDate->format('Y-m-d') }}
                to
                {{ $endDate->format('Y-m-d') }}
            </p>
        </div>


        <!-- DESKTOP TABLE -->
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
                        @php
                            $type = $movement->type->value;
                            $quantity = (float) $movement->quantity;

                            if (in_array($type, ['purchase', 'restock', 'return', 'customer_return'])) {
                                $badgeClass = 'badge-positive';
                            } elseif (in_array($type, ['service_usage', 'sale', 'damage', 'supplier_return'])) {
                                $badgeClass = 'badge-negative';
                            } elseif (in_array($type, ['adjustment', 'adjustment_reversal', 'transfer'])) {
                                $badgeClass = 'badge-adjustment';
                            } else {
                                $badgeClass = 'badge-neutral';
                            }
                        @endphp

                        <tr>
                            <td class="date-cell">
                                {{ \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d H:i') }}
                            </td>

                            <td>
                                <span class="product-name">
                                    {{ $movement->product?->name ?? '-' }}
                                </span>
                            </td>

                            <td class="sku">
                                {{ $movement->product?->sku ?? '-' }}
                            </td>

                            <td>
                                <span class="movement-badge {{ $badgeClass }}">
                                    {{ $movement->type->getLabel() }}
                                </span>
                            </td>

                            <td class="text-right
                                {{ $quantity > 0
                                    ? 'quantity-positive'
                                    : ($quantity < 0
                                        ? 'quantity-negative'
                                        : 'quantity-zero')
                                }}"
                            >
                                {{ $quantity > 0 ? '+' : '' }}
                                {{ number_format($quantity, 3) }}
                            </td>

                            <td class="reference">
                                @if($movement->job)
                                    {{ $movement->job->job_number }}
                                @elseif($movement->reference_type && $movement->reference_id)
                                    {{ class_basename($movement->reference_type) }}
                                    #{{ $movement->reference_id }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                No stock movements found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <!-- MOBILE CARDS -->
        <div class="report-cards">
            @forelse($movements as $movement)
                @php
                    $type = $movement->type->value;
                    $quantity = (float) $movement->quantity;

                    if (in_array($type, ['purchase', 'restock', 'return', 'customer_return'])) {
                        $badgeClass = 'badge-positive';
                    } elseif (in_array($type, ['service_usage', 'sale', 'damage', 'supplier_return'])) {
                        $badgeClass = 'badge-negative';
                    } elseif (in_array($type, ['adjustment', 'adjustment_reversal', 'transfer'])) {
                        $badgeClass = 'badge-adjustment';
                    } else {
                        $badgeClass = 'badge-neutral';
                    }
                @endphp

                <div class="report-card">
                    <div class="card-top">
                        <strong>{{ $movement->product?->name ?? '-' }}</strong>
                        <span class="date">
                            {{ \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d H:i') }}
                        </span>
                    </div>

                    <div class="row">
                        <span class="label">SKU</span>
                        <span class="value">{{ $movement->product?->sku ?? '-' }}</span>
                    </div>

                    <div class="row">
                        <span class="label">Type</span>
                        <span class="value">
                            <span class="movement-badge {{ $badgeClass }}">
                                {{ $movement->type->getLabel() }}
                            </span>
                        </span>
                    </div>

                    <div class="row">
                        <span class="label">Quantity</span>
                        <span class="value
                            {{ $quantity > 0
                                ? 'quantity-positive'
                                : ($quantity < 0
                                    ? 'quantity-negative'
                                    : 'quantity-zero')
                            }}"
                        >
                            {{ $quantity > 0 ? '+' : '' }}
                            {{ number_format($quantity, 3) }}
                        </span>
                    </div>

                    <div class="row">
                        <span class="label">Reference</span>
                        <span class="value">
                            @if($movement->job)
                                {{ $movement->job->job_number }}
                            @elseif($movement->reference_type && $movement->reference_id)
                                {{ class_basename($movement->reference_type) }}
                                #{{ $movement->reference_id }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <div class="empty">
                    No stock movements found for this period.
                </div>
            @endforelse
        </div>


        <!-- Pagination -->
        <div class="pagination-wrap">
            {{ $movements->links() }}
        </div>


        <!-- Footer -->
        <div class="report-footer">
            Generated on {{ now()->format('Y-m-d H:i:s') }}
        </div>


        <!-- Back -->
        <div class="report-back">
            <a href="{{ route('reports') }}" class="back-button">
                ← Back
            </a>
        </div>
    </div>


    <!-- PDF PRINT REPORT -->
    <div id="pdf-report" class="print-only">
        <div class="pdf-header">
            <h1>AUTOCARE PRO</h1>
            <h2>STOCK MOVEMENT REPORT</h2>
            <p>
                Period:
                {{ $startDate->format('d/m/Y') }}
                -
                {{ $endDate->format('d/m/Y') }}
            </p>
        </div>

        <table class="pdf-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Reference</th>
                </tr>
            </thead>

            <tbody>
                @forelse($printMovements as $movement)
                    @php
                        $quantity = (float) $movement->quantity;
                    @endphp

                    <tr>
                        <td>{{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $movement->product?->name ?? '-' }}</td>
                        <td>{{ $movement->product?->sku ?? '-' }}</td>
                        <td>{{ $movement->type->getLabel() }}</td>
                        <td class="right">
                            {{ $quantity > 0 ? '+' : '' }}
                            {{ number_format($quantity, 3) }}
                        </td>
                        <td>
                            @if($movement->job)
                                {{ $movement->job->job_number }}
                            @elseif($movement->reference_type && $movement->reference_id)
                                {{ class_basename($movement->reference_type) }}
                                #{{ $movement->reference_id }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:20px;">
                            No stock movements found for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pdf-footer">
            Total Movements: {{ $printMovements->count() }}
            <br>
            Generated: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

</div>


<script>
    function printPdf() {
        window.print();
    }

    function printThermal() {
        const printWindow = window.open('', '_blank', 'width=400,height=700');

        if (!printWindow) {
            alert('Please allow pop-ups in your browser to print the thermal report.');
            return;
        }

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Stock Movement Report</title>
                <style>
                    @page {
                        size: 80mm auto;
                        margin: 0;
                    }

                    * { box-sizing: border-box; }

                    html, body {
                        width: 80mm;
                        margin: 0;
                        padding: 0;
                        background: #fff;
                    }

                    body {
                        font-family: "Courier New", Courier, monospace;
                        color: #000;
                        font-size: 10px;
                        line-height: 1.35;
                    }

                    .thermal {
                        width: 72mm;
                        margin: 0 auto;
                        padding: 5mm 0 7mm;
                    }

                    .header {
                        text-align: center;
                        margin-bottom: 4mm;
                    }

                    .company {
                        font-size: 16px;
                        font-weight: bold;
                        letter-spacing: .5px;
                    }

                    .title {
                        font-size: 11px;
                        font-weight: bold;
                        margin-top: 2mm;
                    }

                    .period {
                        font-size: 9px;
                        margin-top: 2mm;
                    }

                    .separator {
                        border-top: 1px dashed #000;
                        margin: 3mm 0;
                    }

                    .movement {
                        padding: 1mm 0;
                    }

                    .movement-date {
                        font-size: 9px;
                        font-weight: bold;
                        margin-bottom: 1mm;
                    }

                    .product {
                        font-size: 11px;
                        font-weight: bold;
                        word-break: break-word;
                        margin-bottom: 1mm;
                    }

                    .detail {
                        font-size: 9px;
                        margin: .7mm 0;
                        word-break: break-word;
                    }

                    .type {
                        font-size: 9px;
                        font-weight: bold;
                        margin-top: 1mm;
                    }

                    .quantity {
                        font-size: 11px;
                        font-weight: bold;
                        margin-top: 1mm;
                    }

                    .reference {
                        font-size: 9px;
                        margin-top: 1mm;
                        word-break: break-word;
                    }

                    .summary {
                        text-align: center;
                        font-weight: bold;
                        font-size: 10px;
                        margin-top: 4mm;
                    }

                    .generated {
                        text-align: center;
                        font-size: 8px;
                        margin-top: 3mm;
                    }

                    .footer {
                        text-align: center;
                        font-size: 8px;
                        margin-top: 4mm;
                    }
                </style>
            </head>
            <body>
                <div class="thermal">
                    <div class="header">
                        <div class="company">AUTOCARE PRO</div>
                        <div class="title">STOCK MOVEMENT REPORT</div>
                        <div class="period">
                            {{ $startDate->format('d/m/Y') }}
                            -
                            {{ $endDate->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="separator"></div>

                    @forelse($printMovements as $movement)
                        @php
                            $quantity = (float) $movement->quantity;
                            $type = $movement->type->getLabel();
                            $reference = null;

                            if ($movement->job) {
                                $reference = $movement->job->job_number;
                            } elseif ($movement->reference_type && $movement->reference_id) {
                                $reference = class_basename($movement->reference_type)
                                    . ' #'
                                    . $movement->reference_id;
                            }
                        @endphp

                        <div class="movement">
                            <div class="movement-date">
                                {{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}
                            </div>

                            <div class="product">
                                {{ $movement->product?->name ?? '-' }}
                            </div>

                            <div class="detail">
                                SKU: {{ $movement->product?->sku ?? '-' }}
                            </div>

                            <div class="type">
                                Type: {{ $type }}
                            </div>

                            <div class="quantity">
                                Qty:
                                {{ $quantity > 0 ? '+' : '' }}
                                {{ number_format($quantity, 3) }}
                            </div>

                            @if($reference)
                                <div class="reference">
                                    Ref: {{ $reference }}
                                </div>
                            @endif
                        </div>

                        <div class="separator"></div>
                    @empty
                        <div class="detail" style="text-align:center;">
                            No stock movements found.
                        </div>
                        <div class="separator"></div>
                    @endforelse

                    <div class="summary">
                        Total Movements: {{ $printMovements->count() }}
                    </div>

                    <div class="generated">
                        Generated: {{ now()->format('d/m/Y H:i') }}
                    </div>

                    <div class="footer">
                        AUTOCARE PRO
                        <br>
                        Stock Movement Report
                    </div>
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(function () {
            printWindow.print();
        }, 500);
    }
</script>

@endsection