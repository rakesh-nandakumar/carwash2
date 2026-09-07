@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Customers</h1>
        <p>CRM and complete customer history.</p>
    </div>
    <a class="primary" href="{{ route('customers.create') }}">+ New Customer</a>
</div>

<form class="search">
    <input name="q" placeholder="Search name or phone" value="{{ request('q') }}">
    <button>Search</button>
</form>

<div class="panel">
    <!-- Desktop Table -->
    <table class="customers-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Phone</th>
                <th>Vehicles</th>
                <th>Visits</th>
                <th>Lifetime Spend</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $c)
            <tr>
                <td>
                    <a href="{{ route('customers.show',$c) }}">
                        <b>{{ $c->full_name }}</b>
                    </a>
                    <small>{{ $c->customer_code }}</small>
                </td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->vehicles->count() }}</td>
                <td>{{ $c->jobs_count }}</td>
                <td>Rs. {{ number_format($c->invoices_sum_total ?? 0, 2) }}</td>
                <td>
                    <a href="{{ route('customers.show',$c) }}">View →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty">No customers found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="customers-cards">
        @forelse($customers as $c)
        <a href="{{ route('customers.show',$c) }}" class="customer-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $c->full_name }}</strong>
                    <small>{{ $c->customer_code }}</small>
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Phone</span>
                    <span class="value">{{ $c->phone }}</span>
                </div>
                <div class="detail">
                    <span class="label">Vehicles</span>
                    <span class="value">{{ $c->vehicles->count() }}</span>
                </div>
                <div class="detail">
                    <span class="label">Visits</span>
                    <span class="value">{{ $c->jobs_count }}</span>
                </div>
                <div class="detail">
                    <span class="label">Lifetime Spend</span>
                    <span class="value">Rs. {{ number_format($c->invoices_sum_total ?? 0, 2) }}</span>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state">No customers found.</div>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $customers->links() }}
    </div>
</div>

<style>
/* Desktop table stays normal */
.customers-table {
    width: 100%;
    border-collapse: collapse;
}

.customers-cards {
    display: none;
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

    .page-head a.primary {
        width: 100%;
        text-align: center;
    }

    .search {
        display: flex;
        gap: 8px;
    }

    .search input {
        flex: 1;
    }

    /* Hide the normal table */
    .customers-table {
        display: none;
    }

    /* Show cards – auto-fit makes 1 or 2 cards stretch full width */
    .customers-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .customer-card {
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

    .customer-card:active {
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