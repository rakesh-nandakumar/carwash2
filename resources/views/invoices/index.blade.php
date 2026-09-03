@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Invoices & Payments</h1>
        <p>Final invoices remain immutable and payments are recorded separately.</p>
    </div>
</div>

<div class="panel">
    <!-- Desktop Table -->
    <table class="invoices-table">
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
            @forelse($invoices as $i)
            <tr>
                <td>
                    <a href="{{ route('invoices.show',$i) }}">
                        <b>{{ $i->invoice_number }}</b>
                    </a>
                </td>
                <td>{{ $i->customer->full_name }}</td>
                <td>Rs. {{ number_format($i->total,2) }}</td>
                <td>Rs. {{ number_format($i->paid,2) }}</td>
                <td>Rs. {{ number_format($i->balance,2) }}</td>
                <td><span class="badge">{{ $i->status }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty">No invoices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="invoices-cards">
        @forelse($invoices as $i)
        <a href="{{ route('invoices.show',$i) }}" class="invoice-card">
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
                    <span class="value">{{ $i->status }}</span>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state">No invoices found.</div>
        @endforelse
    </div>

    {{ $invoices->links() }}
</div>

<style>
/* Desktop table stays normal */
.invoices-table {
    width: 100%;
    border-collapse: collapse;
}

.invoices-cards {
    display: none;
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
</style>
@endsection