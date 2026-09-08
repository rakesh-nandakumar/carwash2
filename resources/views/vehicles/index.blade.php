@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Vehicles</h1>
        <p>Vehicle records and service history.</p>
    </div>
    <a class="primary" href="{{ route('vehicles.create') }}">+ New Vehicle</a>
</div>

<div class="search">
    <input id="vehicleSearch" placeholder="Search by customer name, registration, make, model, or category..." oninput="filterVehicles()">
</div>

<div class="panel">
    <!-- Desktop Table -->
    <table class="vehicles-table">
        <thead>
            <tr>
                <th>Registration</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Category</th>
                <th>Mileage</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicles as $v)
            <tr>
                <td>
                    <a href="{{ route('vehicles.show',$v) }}">
                        <b>{{ $v->registration_number }}</b>
                    </a>
                </td>
                <td>{{ $v->customer->full_name }}</td>
                <td>{{ $v->make }} {{ $v->model }}</td>
                <td>{{ $v->category }}</td>
                <td>{{ number_format($v->mileage) }} km</td>
                <td>
                    <a href="{{ route('vehicles.show',$v) }}">View →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty">No vehicles.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="vehicles-cards">
        @forelse($vehicles as $v)
        <a href="{{ route('vehicles.show',$v) }}" class="vehicle-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $v->registration_number }}</strong>
                    <small>{{ $v->make }} {{ $v->model }}</small>
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Customer</span>
                    <span class="value">{{ $v->customer->full_name }}</span>
                </div>
                <div class="detail">
                    <span class="label">Category</span>
                    <span class="value">{{ $v->category }}</span>
                </div>
                <div class="detail">
                    <span class="label">Mileage</span>
                    <span class="value">{{ number_format($v->mileage) }} km</span>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state">No vehicles.</div>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $vehicles->links() }}
    </div>
</div>

<style>
/* Desktop table stays normal */
.vehicles-table {
    width: 100%;
    border-collapse: collapse;
}

.vehicles-cards {
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
    .vehicles-table {
        display: none;
    }

    /* Show cards – auto-fit makes 1 or 2 cards stretch full width */
    .vehicles-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .vehicle-card {
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

    .vehicle-card:active {
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

<script>
function filterVehicles() {
    const query = document.getElementById('vehicleSearch').value.toLowerCase().trim();
    const tableRows = document.querySelectorAll('.vehicles-table tbody tr');
    const mobileCards = document.querySelectorAll('.vehicle-card');

    // Filter desktop table rows
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });

    // Filter mobile cards
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(query) ? '' : 'none';
    });
}
</script>
@endsection