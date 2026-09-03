@extends('layouts.app')

@section('content')
@php
    $business = auth()->user()->business;
    $settings = $business ? $business->getBillingSettings() : [
        'a4_enabled' => true,
        'thermal_enabled' => true
    ];
@endphp

<div class="page-head">
    <div>
        <h1>{{ $invoice->invoice_number }}</h1>
        <p>{{ $invoice->customer->full_name }} · {{ $invoice->job->vehicle->registration_number }}</p>
    </div>
    <div class="page-head-actions">
        @if($settings['a4_enabled'])
            <a href="{{ route('invoices.print',$invoice) }}" target="_blank" class="secondary">Print A4</a>
        @endif
        @if($settings['thermal_enabled'])
            <a href="{{ route('invoices.print',['invoice'=>$invoice,'format'=>'thermal']) }}" target="_blank" class="secondary">Print Receipt</a>
        @endif
    </div>
</div>

<div class="invoice-wrapper">
    <div class="panel invoice-panel">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                @if(!empty($settings['logo_path']))
                    @php
                        $logoPath = $settings['logo_path'];
                        if(!str_starts_with($logoPath, 'http')) {
                            if(str_starts_with($logoPath, '/storage/')) {
                                $logoPath = asset($logoPath);
                            } elseif(str_starts_with($logoPath, 'storage/')) {
                                $logoPath = asset('/' . $logoPath);
                            } else {
                                $logoPath = asset('storage/' . $logoPath);
                            }
                        }
                    @endphp
                    <img src="{{ $logoPath }}" alt="Logo" class="invoice-logo" onerror="this.style.display='none';">
                @else
                    <h2 class="company-name">{{ $settings['company_name'] ?? 'AutoCare Pro' }}</h2>
                @endif
                <p class="muted">{{ $settings['address'] ?? '' }}</p>
                <p class="muted">{{ $settings['phone'] ?? '' }}</p>
                <p class="muted">{{ $settings['email'] ?? '' }}</p>
            </div>
            <div class="invoice-meta">
                <h3>INVOICE</h3>
                <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->created_at->addDays(7)->format('d M Y') }}</p>
                <div class="status-badge {{ $invoice->balance > 0 ? 'unpaid' : 'paid' }}">
                    {{ $invoice->balance > 0 ? 'UNPAID' : 'PAID' }}
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="bill-to">
            <div>
                <h4>Bill To:</h4>
                <p class="strong">{{ $invoice->customer->full_name }}</p>
                <p class="muted">{{ $invoice->customer->phone ?? '' }}</p>
                <p class="muted">{{ $invoice->customer->email ?? '' }}</p>
            </div>
            <div class="text-right">
                <h4>Vehicle Details:</h4>
                <p class="strong">{{ $invoice->job->vehicle->registration_number }}</p>
                <p class="muted">{{ $invoice->job->vehicle->make }} {{ $invoice->job->vehicle->model }}</p>
                <p class="muted">Job #{{ $invoice->job->job_number }}</p>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td class="desc">{{ $item->description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 3) }}</td>
                    <td class="text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right strong">Rs. {{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rs. {{ number_format($invoice->subtotal ?? $invoice->total, 2) }}</span>
                </div>
                @if($invoice->tax ?? 0)
                <div class="total-row">
                    <span>Tax</span>
                    <span>Rs. {{ number_format($invoice->tax, 2) }}</span>
                </div>
                @endif
                @if($invoice->discount ?? 0)
                <div class="total-row">
                    <span>Discount</span>
                    <span class="text-green">-Rs. {{ number_format($invoice->discount, 2) }}</span>
                </div>
                @endif
                <div class="total-row total-final">
                    <span>Total</span>
                    <span class="text-blue">Rs. {{ number_format($invoice->total, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Paid</span>
                    <span class="text-green">Rs. {{ number_format($invoice->paid, 2) }}</span>
                </div>
                <div class="total-row balance">
                    <span>Balance Due</span>
                    <span class="text-red">Rs. {{ number_format($invoice->balance, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="payment-section">
            <h3>Record Payment</h3>
            @if($invoice->balance > 0)
                <form method="post" action="{{ route('invoices.pay', $invoice) }}" class="payment-form">
                    @csrf
                    <div>
                        <label>Amount</label>
                        <input name="amount" type="number" step=".01" max="{{ $invoice->balance }}"
                               value="{{ $invoice->balance }}" required>
                    </div>
                    <div>
                        <label>Payment Method</label>
                        <select name="method">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="primary">Receive Payment</button>
                </form>
                <div class="payment-back">
                    <a href="javascript:history.back()" class="btn-back">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Back
                    </a>
                </div>
            @else
                <div class="paid-full">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>Paid in full</span>
                </div>
                <div class="payment-back">
                    <a href="javascript:history.back()" class="btn-back">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Back
                    </a>
                </div>
            @endif
        </div>

        <!-- Payment History -->
        @if($invoice->payments->count() > 0)
        <div class="payment-history">
            <h3>Payment History</h3>
            @foreach($invoice->payments as $p)
            <div class="payment-item">
                <div class="payment-info">
                    <div class="payment-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                    <div>
                        <p class="strong">{{ ucfirst(str_replace('_', ' ', $p->method)) }}</p>
                        <p class="muted small">{{ $p->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                <span class="text-green strong">Rs. {{ number_format($p->amount, 2) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<style>
.invoice-wrapper {
    max-width: 900px;
    margin: 0 auto;
}
.invoice-panel {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}
/* Header */
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid #e5e7eb;
    gap: 20px;
}
.invoice-logo {
    max-height: 80px;
    margin-bottom: 15px;
}
.company-name {
    margin: 0 0 10px 0;
    color: #1a1a2e;
    font-size: 28px;
    font-weight: 800;
}
.invoice-meta {
    text-align: right;
}
.invoice-meta h3 {
    margin: 0 0 10px 0;
    color: #1a1a2e;
    font-size: 24px;
    font-weight: 700;
}
.status-badge {
    margin-top: 15px;
    padding: 8px 16px;
    border-radius: 6px;
    display: inline-block;
    font-weight: 600;
    font-size: 13px;
}
.status-badge.unpaid {
    background: #fee2e2;
    color: #dc2626;
}
.status-badge.paid {
    background: #dcfce7;
    color: #16a34a;
}
/* Bill To */
.bill-to {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    gap: 20px;
}
.bill-to h4 {
    margin: 0 0 15px 0;
    color: #1a1a2e;
    font-size: 16px;
    font-weight: 600;
}
/* ========== TABLE ========== */
.invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    table-layout: fixed;
}
.invoice-table th {
    padding: 12px 6px;
    text-align: left;
    color: #1a1a2e;
    font-weight: 600;
    font-size: 13px;
    background: #f8fafc;
    border-bottom: 2px solid #e5e7eb;
}
.invoice-table td {
    padding: 12px 6px;
    color: #374151;
    font-size: 13.5px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.invoice-table .text-center { text-align: center; }
.invoice-table .text-right  { text-align: right; }
.invoice-table th:nth-child(1),
.invoice-table td:nth-child(1) { 
    width: 38%; 
    padding-right: 8px;
}
.invoice-table th:nth-child(2),
.invoice-table td:nth-child(2) { 
    width: 14%; 
}
.invoice-table th:nth-child(3),
.invoice-table td:nth-child(3) { 
    width: 24%; 
    padding-left: 4px;
    padding-right: 4px;
}
.invoice-table th:nth-child(4),
.invoice-table td:nth-child(4) { 
    width: 24%; 
    padding-left: 4px;
}
.invoice-table td.text-right {
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}
/* Totals */
.totals {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 30px;
}
.totals-box {
    width: 100%;
    max-width: 320px;
}
.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
    color: #667085;
}
.total-row span:last-child {
    font-weight: 600;
    color: #374151;
}
.total-final {
    background: #f8fafc;
    border-radius: 8px;
    margin-top: 10px;
    padding: 15px;
    border-bottom: none;
}
.total-final span {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
}
.balance {
    border-top: 2px solid #e5e7eb;
    padding-top: 12px;
    margin-top: 4px;
}
.balance span {
    font-size: 16px;
    font-weight: 700;
}
/* Payment */
.payment-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 25px;
    margin-top: 30px;
}
.payment-section h3 {
    margin: 0 0 20px 0;
    color: #1a1a2e;
    font-size: 18px;
    font-weight: 700;
}
.payment-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.payment-form > div {
    flex: 1;
    min-width: 180px;
}
.payment-form label {
    display: block;
    margin-bottom: 8px;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
}
.payment-form input,
.payment-form select {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
}
.payment-back {
    margin-top: 16px;
    display: flex;
    justify-content: center;   /* ← Centers the Back button */
}
.btn-back {
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
.btn-back:hover {
    background: #d1d5db;
    color: #111827;
}
.paid-full {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    background: #dcfce7;
    border-radius: 8px;
    border: 1px solid #86efac;
    color: #16a34a;
    font-weight: 600;
    font-size: 14px;
}
/* Payment History */
.payment-history {
    margin-top: 30px;
}
.payment-history h3 {
    margin: 0 0 15px 0;
    color: #1a1a2e;
    font-size: 18px;
    font-weight: 700;
}
.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 10px;
}
.payment-info {
    display: flex;
    align-items: center;
    gap: 15px;
}
.payment-icon {
    width: 40px;
    height: 40px;
    background: #dbeafe;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
/* Helpers */
.muted { color: #667085; font-size: 14px; margin: 5px 0; }
.strong { font-weight: 600; color: #374151; }
.small { font-size: 12px; }
.text-right { text-align: right; }
.text-green { color: #10b981 !important; }
.text-blue { color: #2563eb !important; }
.text-red { color: #dc2626 !important; }
.page-head-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
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
    .page-head-actions a {
        flex: 1;
        text-align: center;
    }
    .invoice-panel {
        padding: 20px;
    }
    .invoice-header {
        flex-direction: column;
        gap: 24px;
    }
    .invoice-meta {
        text-align: left;
    }
    .bill-to {
        flex-direction: column;
        gap: 24px;
    }
    .bill-to .text-right {
        text-align: left;
    }
    .totals-box {
        max-width: 100%;
    }
    .payment-form {
        flex-direction: column;
    }
    .payment-form > div {
        min-width: 100%;
    }
    .btn-back {
        width: 100%;
        justify-content: center;
    }
    .invoice-table th,
    .invoice-table td {
        padding: 10px 4px;
        font-size: 12.5px;
    }
    .invoice-table td.desc {
        word-break: break-word;
        white-space: normal;
        line-height: 1.35;
    }
    .invoice-table th:nth-child(1),
    .invoice-table td:nth-child(1) { width: 36%; }
    .invoice-table th:nth-child(2),
    .invoice-table td:nth-child(2) { width: 14%; }
    .invoice-table th:nth-child(3),
    .invoice-table td:nth-child(3) { width: 25%; }
    .invoice-table th:nth-child(4),
    .invoice-table td:nth-child(4) { width: 25%; }
}
</style>
@endsection