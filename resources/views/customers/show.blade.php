@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $customer->full_name }}</h1>
        <p>{{ $customer->customer_code }} · {{ $customer->phone }}</p>
    </div>
    <a class="primary" href="{{ route('vehicles.create', ['customer_id' => $customer->id]) }}">
        + Add Vehicle
    </a>
</div>

{{-- Stats: always 2 per row --}}
<div class="stats">
    <div>
        <small>Vehicles</small>
        <b>{{ $customer->vehicles->count() }}</b>
    </div>
    <div>
        <small>Visits</small>
        <b>{{ $customer->total_visits }}</b>
    </div>
    <div>
        <small>Loyalty</small>
        <b>{{ number_format($customer->loyalty_points, 0) }} pts</b>
    </div>
    <div>
        <small>Lifetime Value</small>
        <b>Rs. {{ number_format($customer->total_spending, 2) }}</b>
    </div>
</div>

{{-- Vehicles + Recent Jobs --}}
<div class="grid2">
    <section class="panel">
        <h2>Vehicles</h2>
        <div class="list-grid">
            @forelse($customer->vehicles as $v)
                <a class="listrow" href="{{ route('vehicles.show', $v) }}">
                    <b>{{ $v->registration_number }}</b>
                    <span class="listrow-meta">
                        {{ $v->make }} {{ $v->model }} · {{ $v->category }}
                    </span>
                </a>
            @empty
                <p class="empty">No vehicles.</p>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <h2>Recent Jobs</h2>
        <div class="list-grid">
            @forelse($customer->jobs->sortByDesc('created_at')->take(8) as $j)
                <a class="listrow" href="{{ route('jobs.show', $j) }}">
                    <b>{{ $j->job_number }}</b>
                    <span class="listrow-meta">
                        {{ $j->vehicle->registration_number }} ·
                        <span class="badge">{{ $j->status->getLabel() }}</span>
                    </span>
                </a>
            @empty
                <p class="empty">No service history.</p>
            @endforelse
        </div>
    </section>
</div>

{{-- Back button (centered) --}}
<div class="page-back">
    <a href="{{ route('customers.index') }}" class="back-button">← Back</a>
</div>

<style>
/* ========== STATS: always 2 per row ========== */
.stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.stats > div {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.stats small {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}

.stats b {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

/* ========== GRID2 ========== */
.grid2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* ========== LIST GRID ========== */
.list-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 10px;
}

.listrow {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px 14px;
    background: #f9fafb;
    border: 1px solid #f3f4f6;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s ease;
}

.listrow:hover {
    background: #f3f4f6;
}

.listrow b {
    font-size: 14px;
    font-weight: 600;
    color: #c2410c;
    word-break: break-all;
}

.listrow-meta {
    font-size: 12.5px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.empty {
    grid-column: 1 / -1;
    color: #9ca3af;
    font-size: 14px;
    padding: 12px 0;
}

/* Back button - centered */
.page-back {
    margin-top: 28px;
    display: flex;
    justify-content: center;
}

.back-button {
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

.back-button:hover {
    background: #d1d5db;
    color: #111827;
}

/* ========== MOBILE ========== */
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

    .stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .stats > div {
        padding: 12px 14px;
    }

    .stats b {
        font-size: 16px;
    }

    .grid2 {
        grid-template-columns: 1fr;
        gap: 14px;
    }
}
</style>
@endsection