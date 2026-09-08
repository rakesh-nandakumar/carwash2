@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Appointments</h1>
        <p>Scheduled visits, reschedules and cancellations.</p>
    </div>
    <div style="display:flex;gap:12px;align-items:center;">
        <input type="text" id="appointmentSearch" placeholder="Search appointments..." oninput="filterAppointments()" style="padding:10px 14px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;width:250px;">
        <a class="primary" href="{{ route('appointments.create') }}">+ Appointment</a>
    </div>
</div>

<div class="panel">
    <!-- Desktop Table -->
    <table class="appointments-table">
        <thead>
            <tr>
                <th>Date/time</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $a)
            <tr>
                <td>{{ $a->scheduled_at->format('d M Y H:i') }}</td>
                <td>{{ $a->customer->full_name }}</td>
                <td>{{ $a->vehicle->registration_number }}</td>
                <td><span class="badge">{{ $a->status }}</span></td>
                <td><a href="{{ route('appointments.edit',$a) }}">Edit</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty">No appointments.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="appointments-cards">
        @forelse($appointments as $a)
        <a href="{{ route('appointments.edit',$a) }}" class="appointment-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $a->scheduled_at->format('d M Y H:i') }}</strong>
                    <small>{{ $a->status }}</small>
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Customer</span>
                    <span class="value">{{ $a->customer->full_name }}</span>
                </div>
                <div class="detail">
                    <span class="label">Vehicle</span>
                    <span class="value">{{ $a->vehicle->registration_number }}</span>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state">No appointments.</div>
        @endforelse
    </div>

    </div>

    <div class="pagination-wrap">
        {{ $appointments->links() }}
    </div>
</div>

<style>
/* Desktop table stays normal */
.appointments-table {
    width: 100%;
    border-collapse: collapse;
}

.appointments-cards {
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

    /* Hide the normal table */
    .appointments-table {
        display: none;
    }

    /* Show cards – auto-fit makes 1 or 2 cards stretch full width */
    .appointments-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .appointment-card {
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

    .appointment-card:active {
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
        text-transform: capitalize;
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
function filterAppointments() {
    const query = document.getElementById('appointmentSearch').value.toLowerCase().trim();
    const tableRows = document.querySelectorAll('.appointments-table tbody tr');
    const mobileCards = document.querySelectorAll('.appointment-card');

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