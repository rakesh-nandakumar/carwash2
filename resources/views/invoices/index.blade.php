@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Invoices & Payments</h1>
        <p>Final invoices remain immutable and payments are recorded separately.</p>
    </div>
</div>

<div class="search">
    <input id="invoiceSearch" placeholder="Search invoices..." oninput="filterInvoices()">
    <button class="filter-btn" onclick="openFilterModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
    </button>
</div>
</div>

<div class="panel">
    <!-- Partial Payments Section -->
    @php
        $partialInvoices = $invoices->filter(function($i) { return $i->status === 'partially_paid'; });
        $paidInvoices = $invoices->filter(function($i) { return $i->status === 'paid'; });
        $issuedInvoices = $invoices->filter(function($i) { return $i->status === 'issued'; });
    @endphp

    @if($partialInvoices->count() > 0)
    <div class="payment-section-header partial" data-section="partial">
        <h2>⏳ Partial Payments ({{ $partialInvoices->count() }})</h2>
        <p>Invoices with remaining balance</p>
    </div>

    <!-- Desktop Table -->
    <table class="invoices-table partial-table" data-section="partial">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($partialInvoices as $i)
            <tr class="partial-row">
                <td>
                    <a href="{{ route('invoices.show',$i) }}">
                        <b>{{ $i->invoice_number }}</b>
                    </a>
                </td>
                <td>{{ $i->customer->full_name }}</td>
                <td>Rs. {{ number_format($i->total,2) }}</td>
                <td>Rs. {{ number_format($i->paid,2) }}</td>
                <td style="color: #dc2626; font-weight: bold;">Rs. {{ number_format($i->balance,2) }}</td>
                <td><span class="badge partial-badge">{{ $i->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="invoices-cards partial-cards" data-section="partial">
        @foreach($partialInvoices as $i)
        <a href="{{ route('invoices.show',$i) }}" class="invoice-card partial-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $i->invoice_number }}</strong>
                    <small>{{ $i->customer->full_name }}</small>
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Total</span>
                    <span class="value">Rs. {{ number_format($i->total,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Paid</span>
                    <span class="value">Rs. {{ number_format($i->paid,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Balance</span>
                    <span class="value" style="color: #dc2626; font-weight: bold;">Rs. {{ number_format($i->balance,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Status</span>
                    <span class="value partial-badge">{{ $i->status }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    <!-- Fully Paid Section -->
    @if($paidInvoices->count() > 0)
    <div class="payment-section-header paid" data-section="paid">
        <h2>✅ Fully Paid ({{ $paidInvoices->count() }})</h2>
        <p>Invoices completed with full payment</p>
    </div>

    <!-- Desktop Table -->
    <table class="invoices-table paid-table" data-section="paid">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paidInvoices as $i)
            <tr class="paid-row">
                <td>
                    <a href="{{ route('invoices.show',$i) }}">
                        <b>{{ $i->invoice_number }}</b>
                    </a>
                </td>
                <td>{{ $i->customer->full_name }}</td>
                <td>Rs. {{ number_format($i->total,2) }}</td>
                <td>Rs. {{ number_format($i->paid,2) }}</td>
                <td style="color: #10b981; font-weight: bold;">Rs. {{ number_format($i->balance,2) }}</td>
                <td><span class="badge paid-badge">{{ $i->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="invoices-cards paid-cards" data-section="paid">
        @foreach($paidInvoices as $i)
        <a href="{{ route('invoices.show',$i) }}" class="invoice-card paid-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $i->invoice_number }}</strong>
                    <small>{{ $i->customer->full_name }}</small>
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Total</span>
                    <span class="value">Rs. {{ number_format($i->total,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Paid</span>
                    <span class="value">Rs. {{ number_format($i->paid,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Balance</span>
                    <span class="value" style="color: #10b981; font-weight: bold;">Rs. {{ number_format($i->balance,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Status</span>
                    <span class="value paid-badge">{{ $i->status }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    <!-- Issued/Pending Section -->
    @if($issuedInvoices->count() > 0)
    <div class="payment-section-header issued" data-section="issued">
        <h2>📋 Pending Payment ({{ $issuedInvoices->count() }})</h2>
        <p>Invoices awaiting payment</p>
    </div>

    <!-- Desktop Table -->
    <table class="invoices-table issued-table" data-section="issued">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issuedInvoices as $i)
            <tr class="issued-row">
                <td>
                    <a href="{{ route('invoices.show',$i) }}">
                        <b>{{ $i->invoice_number }}</b>
                    </a>
                </td>
                <td>{{ $i->customer->full_name }}</td>
                <td>Rs. {{ number_format($i->total,2) }}</td>
                <td>Rs. {{ number_format($i->paid,2) }}</td>
                <td>Rs. {{ number_format($i->balance,2) }}</td>
                <td><span class="badge issued-badge">{{ $i->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="invoices-cards issued-cards" data-section="issued">
        @foreach($issuedInvoices as $i)
        <a href="{{ route('invoices.show',$i) }}" class="invoice-card issued-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $i->invoice_number }}</strong>
                    <small>{{ $i->customer->full_name }}</small>
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Total</span>
                    <span class="value">Rs. {{ number_format($i->total,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Paid</span>
                    <span class="value">Rs. {{ number_format($i->paid,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Balance</span>
                    <span class="value">Rs. {{ number_format($i->balance,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Status</span>
                    <span class="value issued-badge">{{ $i->status }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    @if($invoices->count() === 0)
    <div class="empty-state" style="text-align: center; padding: 40px; color: #9ca3af;">
        No invoices found.
    </div>
    @endif

    <div class="pagination-wrap">
        {{ $invoices->links() }}
    </div>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Filter Invoices</h2>
            <button class="modal-close" onclick="closeFilterModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="filter-section">
                <label>Customer</label>
                <div class="searchable-dropdown" id="customerDropdown">
                    <input type="hidden" id="customerFilter" name="customer_id" value="">
                    <input type="text" class="searchable-dropdown-input" id="customerFilterInput" placeholder="Search or select customer...">
                    <div class="searchable-dropdown-options"></div>
                </div>
            </div>
            <div class="filter-section">
                <label>Payment Status</label>
                <select id="paidFilter">
                    <option value="">All Statuses</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partially Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <button class="clear-filters" onclick="clearFilters()">Clear Filters</button>
        </div>
        <div class="modal-footer">
            <button onclick="closeFilterModal()" class="secondary">Cancel</button>
            <button onclick="applyAndCloseFilterModal()" class="primary">Apply Filters</button>
        </div>
    </div>
</div>

<style>
/* Desktop table stays normal */
.invoices-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.invoices-cards {
    display: none;
}

/* Search bar styling */
.search {
    display: flex;
    gap: 8px;
    position: relative;
    align-items: center;
}

.search input {
    flex: 1;
    height: 42px;
    box-sizing: border-box;
    padding: 0 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
}

.filter-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 14px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    height: 38px;
    box-sizing: border-box;
}

.filter-btn:hover {
    background: #d1d5db;
    border-color: #6b7280;
    color: #111827;
}

.filter-btn:active {
    background: #0a1f33;
    border-color: #0a1f33;
    color: white;
    transform: translateY(1px);
}

.filter-btn svg {
    width: 16px;
    height: 16px;
}

/* Modal styling */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5000;
    padding: 20px;
}

.modal-box {
    background: rgba(10, 31, 51, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    width: 100%;
    max-width: 400px;
    max-height: 90vh;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 25px 60px rgba(0,0,0,.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
}

.modal-close {
    border: none;
    background: none;
    font-size: 28px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background 0.15s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-footer button {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.modal-footer .secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
}

.modal-footer .secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.modal-footer .primary {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.9);
    color: #0a1f33;
}

.modal-footer .primary:hover {
    background: #ffffff;
    border-color: #ffffff;
}

.filter-section {
    margin-bottom: 12px;
}

.filter-section:last-child {
    margin-bottom: 0;
}

.filter-section label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-section select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
    cursor: pointer;
}

.filter-section select:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.filter-section select option {
    background: #0a1f33;
    color: #ffffff;
}

.filter-section input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
}

.filter-section input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.filter-section input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.clear-filters {
    width: 100%;
    padding: 8px 12px;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    margin-top: 12px;
    transition: all 0.15s ease;
}

.clear-filters:hover {
    background: #e5e7eb;
    color: #374151;
}

.modal-overlay {
    display: none;
}

.modal-overlay[style*="flex"] {
    display: flex;
}

/* ========== MOBILE ONLY ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .search {
        position: relative;
    }

    .search input {
        height: 40px;
    }

    .filter-btn {
        padding: 0 12px;
        font-size: 13px;
        height: 40px;
    }

    .modal-box {
        max-width: 90%;
        padding: 20px;
    }
}

/* Payment section headers */
.payment-section-header {
    margin: 30px 0 15px 0;
    padding: 15px 20px;
    border-radius: 12px;
    border-left: 4px solid;
}

.payment-section-header h2 {
    margin: 0 0 5px 0;
    font-size: 18px;
    font-weight: 700;
}

.payment-section-header p {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
}

.payment-section-header.partial {
    background: #fef2f2;
    border-left-color: #dc2626;
}

.payment-section-header.partial h2 {
    color: #dc2626;
}

.payment-section-header.paid {
    background: #f0fdf4;
    border-left-color: #10b981;
}

.payment-section-header.paid h2 {
    color: #10b981;
}

.payment-section-header.issued {
    background: #f3f4f6;
    border-left-color: #6b7280;
}

.payment-section-header.issued h2 {
    color: #6b7280;
}

/* Badge styling */
.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.partial-badge {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.paid-badge {
    background: #f0fdf4;
    color: #10b981;
    border: 1px solid #bbf7d0;
}

.issued-badge {
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

/* Row styling */
.partial-row {
    background: #fef2f2;
}

.partial-row:hover {
    background: #fee2e2;
}

.paid-row {
    background: #f0fdf4;
}

.paid-row:hover {
    background: #dcfce7;
}

.issued-row {
    background: #f9fafb;
}

.issued-row:hover {
    background: #f3f4f6;
}

/* Card styling */
.partial-card {
    border: 2px solid #fecaca;
    background: #fef2f2;
}

.partial-card:active {
    background: #fee2e2;
}

.paid-card {
    border: 2px solid #bbf7d0;
    background: #f0fdf4;
}

.paid-card:active {
    background: #dcfce7;
}

.issued-card {
    border: 1px solid #e5e7eb;
    background: #ffffff;
}

.issued-card:active {
    background: #f9fafb;
}

/* Pagination styling */
.pagination-wrap {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
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

/* ========== MOBILE ONLY ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    /* Hide the normal table */
    .invoices-table {
        display: none;
    }

    /* Show cards – auto-fit makes 1 or 2 cards stretch full width */
    .invoices-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .payment-section-header {
        margin: 20px 0 10px 0;
        padding: 12px 16px;
    }

    .payment-section-header h2 {
        font-size: 16px;
    }

    .payment-section-header p {
        font-size: 12px;
    }

    .invoice-card {
        display: block;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .invoice-card:active {
        transform: scale(0.98);
        background: #f9fafb;
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .card-name strong {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }

    .card-name small {
        font-size: 12px;
        color: #6b7280;
    }

    .card-arrow {
        font-size: 16px;
        color: #9ca3af;
        margin-top: 2px;
    }

    .card-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 12px;
    }

    .detail {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .detail .label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .detail .value {
        font-size: 13.5px;
        font-weight: 500;
        color: #1f2937;
    }

    .empty-state {
        text-align: center;
        padding: 30px 16px;
        color: #9ca3af;
        font-size: 14px;
        grid-column: 1 / -1;
    }
}

/* Searchable Dropdown Styling */
.searchable-dropdown {
    position: relative;
    width: 100%;
    z-index: 1;
}

.searchable-dropdown-input {
    width: 100%;
    padding: 8px 12px;
    font-size: 14px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    background: rgba(255, 255, 255, 0.05);
    color: rgba(255, 255, 255, 0.9);
    box-sizing: border-box;
}

.searchable-dropdown-input:focus {
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.searchable-dropdown.open .searchable-dropdown-input {
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.searchable-dropdown.open {
    z-index: 100;
}

.searchable-dropdown-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    background: rgba(10, 31, 51, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-top: 4px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
    z-index: 10000;
    display: none;
    scroll-behavior: smooth;
}

.searchable-dropdown.open .searchable-dropdown-options {
    display: block;
}

.searchable-dropdown-option {
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    position: relative;
    min-height: 35px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.searchable-dropdown-option:last-child {
    border-bottom: none;
}

.searchable-dropdown-option:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    padding-left: 16px;
}

.searchable-dropdown-option.selected {
    background-color: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    font-weight: 500;
    padding-left: 16px;
}

.searchable-dropdown-no-results {
    padding: 8px 12px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 14px;
    text-align: center;
}

/* Scrollbar styling for dropdown options */
.searchable-dropdown-options::-webkit-scrollbar {
    width: 6px;
}

.searchable-dropdown-options::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.searchable-dropdown-options::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.searchable-dropdown-options::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Responsive adjustments for dropdown */
@media (max-width: 640px) {
    .searchable-dropdown-options {
        max-height: 160px;
    }
}

/* Searchable Dropdown Styling */
.searchable-dropdown {
    position: relative;
    width: 100%;
    z-index: 1;
}

.searchable-dropdown-input {
    width: 100%;
    padding: 8px 12px;
    font-size: 14px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    background: rgba(255, 255, 255, 0.05);
    color: rgba(255, 255, 255, 0.9);
    box-sizing: border-box;
}

.searchable-dropdown-input:focus {
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.searchable-dropdown.open .searchable-dropdown-input {
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.searchable-dropdown.open {
    z-index: 100;
}

.searchable-dropdown-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    background: rgba(10, 31, 51, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-top: 4px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
    z-index: 10000;
    display: none;
    scroll-behavior: smooth;
}

.searchable-dropdown.open .searchable-dropdown-options {
    display: block;
}

.searchable-dropdown-option {
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    position: relative;
    min-height: 35px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.searchable-dropdown-option:last-child {
    border-bottom: none;
}

.searchable-dropdown-option:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    padding-left: 16px;
}

.searchable-dropdown-option.selected {
    background-color: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    font-weight: 500;
    padding-left: 16px;
}

.searchable-dropdown-no-results {
    padding: 8px 12px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 14px;
    text-align: center;
}

/* Scrollbar styling for dropdown options */
.searchable-dropdown-options::-webkit-scrollbar {
    width: 6px;
}

.searchable-dropdown-options::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.searchable-dropdown-options::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.searchable-dropdown-options::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

</style>

<script>
let customerDropdown = null;

async function loadCustomersForDropdown() {
    try {
        const response = await fetch('{{ route('customers.list') }}');
        const customers = await response.json();

        const dropdownContainer = document.getElementById('customerDropdown');
        if (dropdownContainer) {
            const customerData = Array.isArray(customers) ? customers.map(customer => ({
                id: customer.id,
                label: `${customer.full_name} — ${customer.whatsapp_number || customer.phone}`
            })) : [];

            customerDropdown = new SearchableDropdown(dropdownContainer, {
                data: customerData,
                onSelect: function(item) {
                    // Don't apply filters immediately - wait for Apply button
                }
            });
        }
    } catch (error) {
        console.error('Error loading customers:', error);
    }
}

function filterInvoices() {
    applyFilters();
}

function openFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.style.display = 'flex';
    if (!customerDropdown) {
        loadCustomersForDropdown();
    }
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.style.display = 'none';
}

function applyAndCloseFilterModal() {
    applyFilters();
    closeFilterModal();
}

function applyFilters() {
    const query = document.getElementById('invoiceSearch').value.toLowerCase().trim();
    const customerFilterInput = document.getElementById('customerFilterInput').value.toLowerCase().trim();
    const paidFilter = document.getElementById('paidFilter').value;

    // Extract customer name from dropdown text (format: "Name — Phone")
    const customerName = customerFilterInput ? customerFilterInput.split('—')[0].trim() : '';

    // Show all sections and items by default when search is empty
    if (!query && !customerName && !paidFilter) {
        document.querySelectorAll('.payment-section-header').forEach(section => {
            section.style.display = 'block';
        });
        document.querySelectorAll('.invoices-table').forEach(table => {
            table.style.display = 'table';
            table.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = '';
            });
        });
        document.querySelectorAll('.invoices-cards').forEach(cards => {
            cards.style.display = 'grid';
            cards.querySelectorAll('.invoice-card').forEach(card => {
                card.style.display = '';
            });
        });
        return;
    }

    // Filter all table rows across all tables
    const allTableRows = document.querySelectorAll('.invoices-table tbody tr');
    allTableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const cells = row.querySelectorAll('td');
        const badge = row.querySelector('.badge');

        let matchesSearch = text.includes(query);
        let matchesCustomer = true;
        let matchesPaid = true;

        if (customerName && cells.length >= 2) {
            const customerText = cells[1].textContent.toLowerCase();
            matchesCustomer = customerText.includes(customerName);
        }

        if (paidFilter && badge) {
            const status = badge.textContent.toLowerCase();
            if (paidFilter === 'paid') {
                matchesPaid = status === 'paid';
            } else if (paidFilter === 'partial') {
                matchesPaid = status === 'partial';
            } else if (paidFilter === 'unpaid') {
                matchesPaid = status === 'unpaid';
            } else if (paidFilter === 'overdue') {
                matchesPaid = status === 'overdue';
            }
        }

        row.style.display = (matchesSearch && matchesCustomer && matchesPaid) ? '' : 'none';
    });

    // Filter all mobile cards across all sections
    const allMobileCards = document.querySelectorAll('.invoice-card');
    allMobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const details = card.querySelectorAll('.card-details .detail');
        const statusDetail = details[3]?.querySelector('.value')?.textContent.toLowerCase() || '';
        const customerNameCard = card.querySelector('.card-name small')?.textContent.toLowerCase() || '';

        let matchesSearch = text.includes(query);
        let matchesCustomer = true;
        let matchesPaid = true;

        if (customerName) {
            matchesCustomer = customerNameCard.includes(customerName);
        }

        if (paidFilter) {
            if (paidFilter === 'paid') {
                matchesPaid = statusDetail === 'paid';
            } else if (paidFilter === 'partial') {
                matchesPaid = statusDetail === 'partial';
            } else if (paidFilter === 'unpaid') {
                matchesPaid = statusDetail === 'unpaid';
            } else if (paidFilter === 'overdue') {
                matchesPaid = statusDetail === 'overdue';
            }
        }

        card.style.display = (matchesSearch && matchesCustomer && matchesPaid) ? '' : 'none';
    });

    // Hide empty sections based on visible items
    const sections = document.querySelectorAll('.payment-section-header');
    sections.forEach(section => {
        const sectionName = section.dataset.section;
        if (!sectionName) return;

        const nextTable = section.nextElementSibling;
        const nextCards = nextTable ? nextTable.nextElementSibling : null;

        let hasVisibleItems = false;

        if (nextTable && nextTable.classList.contains('invoices-table') && nextTable.dataset.section === sectionName) {
            const allRows = nextTable.querySelectorAll('tbody tr');
            hasVisibleItems = Array.from(allRows).some(row => row.style.display !== 'none');
        }

        if (nextCards && nextCards.classList.contains('invoices-cards') && nextCards.dataset.section === sectionName) {
            const allCards = nextCards.querySelectorAll('.invoice-card');
            hasVisibleItems = Array.from(allCards).some(card => card.style.display !== 'none');
        }

        section.style.display = hasVisibleItems ? 'block' : 'none';
        if (nextTable && nextTable.dataset.section === sectionName) {
            nextTable.style.display = hasVisibleItems ? 'table' : 'none';
        }
        if (nextCards && nextCards.dataset.section === sectionName) {
            nextCards.style.display = hasVisibleItems ? 'grid' : 'none';
        }
    });
}

function clearFilters() {
    document.getElementById('customerFilter').value = '';
    document.getElementById('customerFilterInput').value = '';
    document.getElementById('paidFilter').value = '';

    // Reset dropdown instance
    if (customerDropdown) {
        customerDropdown.setValue('', '');
    }

    applyFilters();
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('filterModal');
    if (event.target === modal) {
        closeFilterModal();
    }
});
</script>
@endsection