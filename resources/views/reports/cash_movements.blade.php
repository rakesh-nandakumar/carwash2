@extends('layouts.app')

@section('content')

<style>

    /* =========================================================
       PAGE HEADER
       ========================================================= */

    .cash-report-page {
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
       SUMMARY CARDS
       ========================================================= */

    .report-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 14px;
        margin-bottom: 28px;
    }

    .report-summary > div {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .report-summary span {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .report-summary strong {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
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


    /* =========================================================
       BADGES
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

    .badge-in {
        color: #047857;
        background: #ecfdf5;
    }

    .badge-out {
        color: #dc2626;
        background: #fef2f2;
    }

    .badge-sale {
        color: #1d4ed8;
        background: #eff6ff;
    }

    .badge-refund {
        color: #b45309;
        background: #fffbeb;
    }

    .badge-manual {
        color: #6d28d9;
        background: #f5f3ff;
    }

    .badge-drop {
        color: #0f766e;
        background: #f0fdfa;
    }


    /* =========================================================
       AMOUNT
       ========================================================= */

    .amount-in {
        color: #047857 !important;
        font-weight: 700 !important;
    }

    .amount-out {
        color: #dc2626 !important;
        font-weight: 700 !important;
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
       PAGINATION
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

    /* Responsive pagination for mobile */
    @media (max-width: 768px) {
        .pagination-wrap {
            margin-top: 20px;
            gap: 8px;
        }

        .pagination-wrap .pagination,
        .pagination-wrap nav > div {
            gap: 4px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-wrap a,
        .pagination-wrap span {
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            font-size: 13px;
            border-radius: 8px;
        }

        .pagination-wrap a[rel="prev"],
        .pagination-wrap a[rel="next"] {
            padding: 0 10px;
            font-size: 12px;
        }

        .pagination-wrap svg,
        .pagination-wrap .pagination svg,
        nav[role="navigation"] svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
        }

        /* Hide some page numbers on very small screens */
        @media (max-width: 480px) {
            .pagination-wrap .pagination {
                gap: 2px;
            }

            .pagination-wrap a,
            .pagination-wrap span {
                min-width: 28px;
                height: 28px;
                padding: 0 6px;
                font-size: 12px;
            }

            .pagination-wrap a[rel="prev"],
            .pagination-wrap a[rel="next"] {
                padding: 0 8px;
                font-size: 11px;
            }
        }
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

        .report-summary {
            grid-template-columns: 1fr 1fr;
        }

    }


    @media (max-width: 500px) {

        .report-cards {
            grid-template-columns: 1fr;
        }

        .report-summary {
            grid-template-columns: 1fr;
        }

    }

</style>


<div class="cash-report-page">

    <!-- PAGE HEADER -->
    <div class="page-head no-print">
        <div>
            <h1>Cash Movements Report</h1>
            <p>
                Till cash activity and movement history.
            </p>
        </div>

        <div class="page-head-actions">
            <button type="button" onclick="printPdf()" class="primary">
                📄 Print PDF
            </button>
        </div>
    </div>


    <!-- SCREEN REPORT -->
    <div id="screen-report" class="panel no-print">

        <!-- Filters -->
        <div class="filters">
            <form
                method="GET"
                action="{{ route('reports.cash-movements') }}"
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
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="">All</option>
                        <option value="in" @selected(request('type') === 'in')>
                            Cash In
                        </option>
                        <option value="out" @selected(request('type') === 'out')>
                            Cash Out
                        </option>
                    </select>
                </div>

                <div>
                    <label for="source">Source</label>
                    <select id="source" name="source">
                        <option value="">All</option>
                        <option value="sale" @selected(request('source') === 'sale')>
                            Sale
                        </option>
                        <option value="refund" @selected(request('source') === 'refund')>
                            Refund
                        </option>
                        <option value="manual" @selected(request('source') === 'manual')>
                            Manual
                        </option>
                        <option value="drop" @selected(request('source') === 'drop')>
                            Cash Drop
                        </option>
                    </select>
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
            <h1>Cash Movements Report</h1>
            <p>
                Period:
                {{ $startDate->format('Y-m-d') }}
                to
                {{ $endDate->format('Y-m-d') }}
                @if($till)
                    · Till: {{ $till->name }}
                @endif
            </p>
        </div>


        <!-- Summary -->
        <div class="report-summary">
            <div>
                <span>Cash Sales</span>
                <strong>Rs. {{ number_format($sales, 2) }}</strong>
            </div>

            <div>
                <span>Cash In</span>
                <strong>Rs. {{ number_format($manualIn, 2) }}</strong>
            </div>

            <div>
                <span>Cash Refunds</span>
                <strong>Rs. {{ number_format($refunds, 2) }}</strong>
            </div>

            <div>
                <span>Cash Out</span>
                <strong>Rs. {{ number_format($manualOut, 2) }}</strong>
            </div>

            <div>
                <span>Cash Drops</span>
                <strong>Rs. {{ number_format($drops, 2) }}</strong>
            </div>
        </div>


        <!-- DESKTOP TABLE -->
        <div class="table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Reason</th>
                        <th>Reference</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($paginatedCombined as $item)
                        @if($item['type'] === 'movement')
                            @php
                                $movement = $item['data'];
                                $typeClass = $movement->type === 'in' ? 'badge-in' : 'badge-out';
                                $sourceClass = match ($movement->source) {
                                    'sale' => 'badge-sale',
                                    'refund' => 'badge-refund',
                                    'manual' => 'badge-manual',
                                    'drop' => 'badge-drop',
                                    default => 'badge-manual',
                                };
                                $amountClass = $movement->type === 'in' ? 'amount-in' : 'amount-out';
                            @endphp

                            <tr>
                                <td class="date-cell">
                                    {{ $movement->created_at->format('Y-m-d H:i') }}
                                </td>

                                <td>
                                    {{ $movement->user?->name ?? '-' }}
                                </td>

                                <td>
                                    <span class="movement-badge {{ $typeClass }}">
                                        {{ ucfirst($movement->type) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="movement-badge {{ $sourceClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $movement->source)) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $movement->reason ?? '-' }}
                                </td>

                                <td class="reference">
                                    @if($movement->reference)
                                        {{ class_basename($movement->reference_type) }}
                                        #{{ $movement->reference_id }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-right {{ $amountClass }}">
                                    {{ $movement->type === 'in' ? '+' : '-' }}
                                    Rs. {{ number_format($movement->amount, 2) }}
                                </td>
                            </tr>
                        @elseif($item['type'] === 'closure_open')
                            @php
                                $closure = $item['data'];
                            @endphp

                            <tr style="background: #f0fdf4;">
                                <td class="date-cell">
                                    {{ $closure->opened_at->format('Y-m-d H:i') }}
                                </td>

                                <td>
                                    {{ $closure->user?->name ?? '-' }}
                                </td>

                                <td>
                                    <span class="movement-badge badge-in">
                                        Opening
                                    </span>
                                </td>

                                <td>
                                    <span class="movement-badge badge-manual">
                                        Till Open
                                    </span>
                                </td>

                                <td>
                                    Till opening balance
                                </td>

                                <td class="reference">
                                    TillClosure #{{ $closure->id }}
                                </td>

                                <td class="text-right amount-in">
                                    + Rs. {{ number_format($closure->opening_balance, 2) }}
                                </td>
                            </tr>
                        @elseif($item['type'] === 'closure_close')
                            @php
                                $closure = $item['data'];
                            @endphp

                            <tr style="background: #fef2f2;">
                                <td class="date-cell">
                                    {{ $closure->closed_at->format('Y-m-d H:i') }}
                                </td>

                                <td>
                                    {{ $closure->user?->name ?? '-' }}
                                </td>

                                <td>
                                    <span class="movement-badge badge-out">
                                        Closing
                                    </span>
                                </td>

                                <td>
                                    <span class="movement-badge badge-manual">
                                        Till Close
                                    </span>
                                </td>

                                <td>
                                    Till closing balance
                                </td>

                                <td class="reference">
                                    TillClosure #{{ $closure->id }}
                                </td>

                                <td class="text-right amount-out">
                                    - Rs. {{ number_format($closure->counted_balance, 2) }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="empty">
                                No cash movements or closures found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <!-- MOBILE CARDS -->
        <div class="report-cards">
            @forelse($paginatedCombined as $item)
                @if($item['type'] === 'movement')
                    @php
                        $movement = $item['data'];
                        $typeClass = $movement->type === 'in' ? 'badge-in' : 'badge-out';
                        $sourceClass = match ($movement->source) {
                            'sale' => 'badge-sale',
                            'refund' => 'badge-refund',
                            'manual' => 'badge-manual',
                            'drop' => 'badge-drop',
                            default => 'badge-manual',
                        };
                        $amountClass = $movement->type === 'in' ? 'amount-in' : 'amount-out';
                    @endphp

                    <div class="report-card">
                        <div class="card-top">
                            <strong>
                                {{ $movement->type === 'in' ? '+' : '-' }}
                                Rs. {{ number_format($movement->amount, 2) }}
                            </strong>
                            <span class="date">
                                {{ $movement->created_at->format('Y-m-d H:i') }}
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Cashier</span>
                            <span class="value">{{ $movement->user?->name ?? '-' }}</span>
                        </div>

                        <div class="row">
                            <span class="label">Type</span>
                            <span class="value">
                                <span class="movement-badge {{ $typeClass }}">
                                    {{ ucfirst($movement->type) }}
                                </span>
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Source</span>
                            <span class="value">
                                <span class="movement-badge {{ $sourceClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $movement->source)) }}
                                </span>
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Reason</span>
                            <span class="value">{{ $movement->reason ?? '-' }}</span>
                        </div>

                        <div class="row">
                            <span class="label">Reference</span>
                            <span class="value">
                                @if($movement->reference)
                                    {{ class_basename($movement->reference_type) }}
                                    #{{ $movement->reference_id }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>
                @elseif($item['type'] === 'closure_open')
                    @php
                        $closure = $item['data'];
                    @endphp

                    <div class="report-card" style="background: #f0fdf4;">
                        <div class="card-top">
                            <strong>
                                + Rs. {{ number_format($closure->opening_balance, 2) }}
                            </strong>
                            <span class="date">
                                {{ $closure->opened_at->format('Y-m-d H:i') }}
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Cashier</span>
                            <span class="value">{{ $closure->user?->name ?? '-' }}</span>
                        </div>

                        <div class="row">
                            <span class="label">Type</span>
                            <span class="value">
                                <span class="movement-badge badge-in">
                                    Opening
                                </span>
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Source</span>
                            <span class="value">
                                <span class="movement-badge badge-manual">
                                    Till Open
                                </span>
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Reason</span>
                            <span class="value">Till opening balance</span>
                        </div>

                        <div class="row">
                            <span class="label">Reference</span>
                            <span class="value">TillClosure #{{ $closure->id }}</span>
                        </div>
                    </div>
                @elseif($item['type'] === 'closure_close')
                    @php
                        $closure = $item['data'];
                    @endphp

                    <div class="report-card" style="background: #fef2f2;">
                        <div class="card-top">
                            <strong>
                                - Rs. {{ number_format($closure->counted_balance, 2) }}
                            </strong>
                            <span class="date">
                                {{ $closure->closed_at->format('Y-m-d H:i') }}
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Cashier</span>
                            <span class="value">{{ $closure->user?->name ?? '-' }}</span>
                        </div>

                        <div class="row">
                            <span class="label">Type</span>
                            <span class="value">
                                <span class="movement-badge badge-out">
                                    Closing
                                </span>
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Source</span>
                            <span class="value">
                                <span class="movement-badge badge-manual">
                                    Till Close
                                </span>
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">Reason</span>
                            <span class="value">Till closing balance</span>
                        </div>

                        <div class="row">
                            <span class="label">Reference</span>
                            <span class="value">TillClosure #{{ $closure->id }}</span>
                        </div>
                    </div>
                @endif
            @empty
                <div class="empty">
                    No cash movements or closures found for this period.
                </div>
            @endforelse
        </div>


        <!-- Pagination -->
        @php $paginationThreshold = request()->isMobile() ? 10 : 50; @endphp
        @if($paginatedCombined->total() > $paginationThreshold)
        <div class="pagination-wrap">
            {{ $paginatedCombined->links() }}
        </div>
        @endif


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
            <h2>CASH MOVEMENTS REPORT</h2>
            <p>
                Period:
                {{ $startDate->format('d/m/Y') }}
                -
                {{ $endDate->format('d/m/Y') }}
                @if($till)
                    · Till: {{ $till->name }}
                @endif
            </p>
        </div>

        <table class="pdf-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Cashier</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th>Reason</th>
                    <th>Reference</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>

            <tbody>
                @foreach($combined as $item)
                    @if($item['type'] === 'movement')
                        @php
                            $movement = $item['data'];
                        @endphp
                        <tr>
                            <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $movement->user?->name ?? '-' }}</td>
                            <td>{{ ucfirst($movement->type) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $movement->source)) }}</td>
                            <td>{{ $movement->reason ?? '-' }}</td>
                            <td>
                                @if($movement->reference)
                                    {{ class_basename($movement->reference_type) }}
                                    #{{ $movement->reference_id }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="right">
                                {{ $movement->type === 'in' ? '+' : '-' }}
                                Rs. {{ number_format($movement->amount, 2) }}
                            </td>
                        </tr>
                    @elseif($item['type'] === 'closure_open')
                        @php
                            $closure = $item['data'];
                        @endphp
                        <tr style="background: #f0fdf4;">
                            <td>{{ $closure->opened_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $closure->user?->name ?? '-' }}</td>
                            <td>Opening</td>
                            <td>Till Open</td>
                            <td>Till opening balance</td>
                            <td>TillClosure #{{ $closure->id }}</td>
                            <td class="right">
                                + Rs. {{ number_format($closure->opening_balance, 2) }}
                            </td>
                        </tr>
                    @elseif($item['type'] === 'closure_close')
                        @php
                            $closure = $item['data'];
                        @endphp
                        <tr style="background: #fef2f2;">
                            <td>{{ $closure->closed_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $closure->user?->name ?? '-' }}</td>
                            <td>Closing</td>
                            <td>Till Close</td>
                            <td>Till closing balance</td>
                            <td>TillClosure #{{ $closure->id }}</td>
                            <td class="right">
                                - Rs. {{ number_format($closure->counted_balance, 2) }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="pdf-footer">
            Cash Sales: Rs. {{ number_format($sales, 2) }}
            · Cash In: Rs. {{ number_format($manualIn, 2) }}
            · Refunds: Rs. {{ number_format($refunds, 2) }}
            · Cash Out: Rs. {{ number_format($manualOut, 2) }}
            · Drops: Rs. {{ number_format($drops, 2) }}
            <br>
            Generated: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

</div>


<script>
    function printPdf() {
        window.print();
    }
</script>

@endsection