@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Job Cards</h1>
        <p>Track every vehicle visit from check-in to delivery.</p>
    </div>
    <div class="page-head-actions">
        <a class="secondary" href="{{ route('jobs.board') }}">Live Board</a>
        <a class="primary" href="{{ route('jobs.create') }}">+ New Job</a>
    </div>
</div>

<div class="panel">
    {{-- Desktop Table --}}
    <table class="jobs-table">
        <thead>
            <tr>
                <th>Job</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Technician</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($jobs as $j)
            <tr>
                <td>
                    <a href="{{ route('jobs.show',$j) }}">
                        <b>{{ $j->job_number }}</b>
                    </a>
                </td>
                <td>{{ $j->vehicle->registration_number }}</td>
                <td>{{ $j->customer->full_name }}</td>
                <td><span class="badge">{{ $j->status->getLabel() }}</span></td>
                <td>{{ ucfirst($j->priority) }}</td>
                <td>{{ optional($j->technician)->name ?? 'Unassigned' }}</td>
                <td>
                    <a href="{{ route('jobs.show',$j) }}">Open →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty">No job cards found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Mobile Cards --}}
    <div class="jobs-cards">
        @forelse($jobs as $j)
        <a href="{{ route('jobs.show',$j) }}" class="job-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $j->job_number }}</strong>
                    <small>{{ $j->vehicle->registration_number }}</small>
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Customer</span>
                    <span class="value">{{ $j->customer->full_name }}</span>
                </div>
                <div class="detail">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="badge">{{ $j->status->getLabel() }}</span>
                    </span>
                </div>
                <div class="detail">
                    <span class="label">Priority</span>
                    <span class="value">{{ ucfirst($j->priority) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Technician</span>
                    <span class="value">{{ optional($j->technician)->name ?? 'Unassigned' }}</span>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state">No job cards found.</div>
        @endforelse
    </div>

    {{ $jobs->links() }}
</div>

<style>
/* Desktop table stays normal */
.jobs-table {
    width: 100%;
    border-collapse: collapse;
}

.jobs-cards {
    display: none;
}

.page-head-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* ========== MOBILE ONLY ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head-actions {
        width: 100%;
        display: flex;
        gap: 8px;
    }

    .page-head-actions a {
        flex: 1;
        text-align: center;
    }

    /* Hide the normal table */
    .jobs-table {
        display: none;
    }

    /* Show cards – auto-fit makes 1 or 2 cards stretch full width */
    .jobs-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .job-card {
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

    .job-card:active {
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