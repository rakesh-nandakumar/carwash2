@extends('layouts.app')
@section('content')
<div class="cashier-dashboard">
    <div class="cashier-header">
        <div class="header-content">
            <h1>Cashier Dashboard</h1>
            <p>Process payments for completed vehicles</p>
        </div>
        <form class="search-form" method="get" action="{{ route('cashier.search') }}">
            <input name="q" placeholder="Search by registration, customer, or job number..." required>
            <button type="submit">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </form>
    </div>

    <div class="vehicles-grid">
        @forelse($readyForPayment as $job)
            <div class="vehicle-card" onclick="window.location.href='{{ route('cashier.payment', $job) }}'">
                <div class="card-header">
                    <div class="vehicle-reg">{{ $job->vehicle->registration_number }}</div>
                    <div class="job-number">{{ $job->job_number }}</div>
                </div>
                <div class="card-body">
                    <div class="customer-name">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ $job->customer->full_name }}
                    </div>
                    <div class="time-ago">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $job->updated_at->diffForHumans() }}
                    </div>
                </div>
                <div class="card-footer">
                    @if($job->invoice)
                        <div class="amount">Rs. {{ number_format($job->invoice->total, 2) }}</div>
                    @else
                        <div class="amount calculating">Calculating...</div>
                    @endif
                    <div class="action-arrow">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3>No vehicles ready for payment</h3>
                <p>Completed vehicles will appear here automatically</p>
            </div>
        @endforelse
    </div>
</div>

<style>
.cashier-dashboard {
    padding: 0;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: calc(100vh - 80px);
}

.cashier-header {
    background: white;
    padding: 32px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border-bottom: 1px solid #e5e7eb;
}

.header-content h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.header-content p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.search-form {
    display: flex;
    gap: 8px;
    max-width: 400px;
}

.search-form input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.2s ease;
}

.search-form input:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-form button {
    padding: 12px 16px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-form button:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.vehicles-grid {
    padding: 32px 40px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.vehicle-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.vehicle-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    border-color: #3b82f6;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.vehicle-reg {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: 0.5px;
}

.job-number {
    font-size: 12px;
    color: #64748b;
    background: #f1f5f9;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 500;
}

.card-body {
    margin-bottom: 16px;
}

.customer-name {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #475569;
    font-size: 14px;
    margin-bottom: 8px;
}

.customer-name svg {
    color: #94a3b8;
}

.time-ago {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #94a3b8;
    font-size: 12px;
}

.time-ago svg {
    color: #cbd5e1;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
}

.amount {
    font-size: 20px;
    font-weight: 700;
    color: #10b981;
}

.amount.calculating {
    color: #f59e0b;
    font-size: 14px;
}

.action-arrow {
    color: #cbd5e1;
    transition: all 0.2s ease;
}

.vehicle-card:hover .action-arrow {
    color: #3b82f6;
    transform: translateX(4px);
}

.empty-state {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 40px;
    background: white;
    border-radius: 16px;
    border: 1px dashed #cbd5e1;
}

.empty-state svg {
    color: #cbd5e1;
    margin-bottom: 16px;
}

.empty-state h3 {
    font-size: 18px;
    color: #64748b;
    margin: 0 0 8px 0;
}

.empty-state p {
    font-size: 14px;
    color: #94a3b8;
    margin: 0;
}

@media (max-width: 768px) {
    .cashier-header {
        flex-direction: column;
        gap: 16px;
        padding: 24px;
    }
    
    .search-form {
        max-width: 100%;
    }
    
    .vehicles-grid {
        padding: 24px;
        grid-template-columns: 1fr;
    }
}
</style>

<script>
setInterval(function() {
    location.reload();
}, 5000);
</script>
@endsection
