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

    {{-- Till Summary Panel --}}
    <div class="till-panel">
        <div class="till-header">
            <div>
                <h2>{{ $till->name }}</h2>
                <p>Current Till</p>
            </div>

            <span class="till-status {{ $till->is_active ? 'active' : 'inactive' }}">
                {{ $till->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="till-stats">
            <div>
                <span>Opening Balance</span>
                <strong>
                    Rs. {{ number_format($till->opening_balance, 2) }}
                </strong>
            </div>

            <div>
                <span>Cash Sales</span>
                <strong>
                    Rs. {{ number_format($cashSales, 2) }}
                </strong>
            </div>

            <div>
                <span>Cash In</span>
                <strong>
                    Rs. {{ number_format($cashIn, 2) }}
                </strong>
            </div>

            <div>
                <span>Cash Out</span>
                <strong>
                    Rs. {{ number_format($cashOut, 2) }}
                </strong>
            </div>

            <div>
                <span>Cash Refunds</span>
                <strong>
                    Rs. {{ number_format($cashRefunds, 2) }}
                </strong>
            </div>

            <div class="expected">
                <span>Expected Cash</span>
                <strong>
                    Rs. {{ number_format($expectedBalance, 2) }}
                </strong>
            </div>
        </div>

        <div class="till-actions">
            @if(auth()->user()->hasPermissionTo('cashier.cash_in'))
                <button type="button" onclick="openCashModal('cash-in')">
                    Cash In
                </button>
            @endif

            @if(auth()->user()->hasPermissionTo('cashier.cash_out'))
                <button type="button" onclick="openCashModal('cash-out')">
                    Cash Out
                </button>
            @endif

            @if(auth()->user()->hasPermissionTo('cashier.cash_drop'))
                <button type="button" onclick="openCashModal('cash-drop')">
                    Cash Drop
                </button>
            @endif
        </div>
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

{{-- Cash Movement Modal --}}
<div id="cashMovementModal" class="cash-modal hidden">
    <div class="cash-modal-content">
        <div class="cash-modal-header">
            <h2 id="cashModalTitle">Cash Movement</h2>
            <button type="button" onclick="closeCashModal()">×</button>
        </div>

        <form method="POST" id="cashMovementForm">
            @csrf

            <div class="form-group">
                <label>Amount</label>
                <input
                    type="number"
                    name="amount"
                    step="0.01"
                    min="0.01"
                    required
                >
            </div>

            <div class="form-group">
                <label>Reason</label>
                <input
                    type="text"
                    name="reason"
                    maxlength="255"
                    required
                >
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea
                    name="description"
                    rows="3"
                ></textarea>
            </div>

            <div class="cash-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeCashModal()">
                    Cancel
                </button>
                <button type="submit" class="btn-confirm">
                    Confirm
                </button>
            </div>
        </form>
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

/* Till Panel */
.till-panel {
    margin: 24px 40px 0;
    background: white;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.till-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.till-header h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 2px 0;
}

.till-header p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.till-status {
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
}

.till-status.active {
    background: #dcfce7;
    color: #166534;
}

.till-status.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.till-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
    padding: 20px 24px;
}

.till-stats > div {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.till-stats span {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.till-stats strong {
    font-size: 16px;
    color: #1e293b;
    font-weight: 700;
}

.till-stats .expected {
    background: #f0fdf4;
    border-radius: 10px;
    padding: 12px;
}

.till-stats .expected strong {
    color: #15803d;
    font-size: 18px;
}

.till-actions {
    display: flex;
    gap: 10px;
    padding: 16px 24px 20px;
    border-top: 1px solid #f1f5f9;
}

.till-actions button {
    padding: 10px 18px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.till-actions button:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

/* Vehicles Grid */
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

/* Cash Modal */
.cash-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 16px;
}

.cash-modal.hidden {
    display: none;
}

.cash-modal-content {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.cash-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.cash-modal-header h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.cash-modal-header button {
    background: none;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #94a3b8;
    cursor: pointer;
    padding: 0 4px;
}

.cash-modal-header button:hover {
    color: #1e293b;
}

.cash-modal-content form {
    padding: 24px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.cash-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
}

.btn-cancel {
    padding: 10px 18px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.btn-cancel:hover {
    background: #f8fafc;
}

.btn-confirm {
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    background: #3b82f6;
    color: white;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.btn-confirm:hover {
    background: #2563eb;
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

    .till-panel {
        margin: 16px 16px 0;
    }
    
    .vehicles-grid {
        padding: 24px 16px;
        grid-template-columns: 1fr;
    }

    .till-stats {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<script>
function openCashModal(type) {
    const modal = document.getElementById('cashMovementModal');
    const form = document.getElementById('cashMovementForm');
    const title = document.getElementById('cashModalTitle');

    if (type === 'cash-in') {
        title.textContent = 'Cash In';
        form.action = '{{ route('cashier.cash-in') }}';
    }

    if (type === 'cash-out') {
        title.textContent = 'Cash Out';
        form.action = '{{ route('cashier.cash-out') }}';
    }

    if (type === 'cash-drop') {
        title.textContent = 'Cash Drop';
        form.action = '{{ route('cashier.cash-drop') }}';
    }

    modal.classList.remove('hidden');
}

function closeCashModal() {
    document
        .getElementById('cashMovementModal')
        .classList.add('hidden');
}

// setInterval(function() {
//     location.reload();
// }, 5000);
</script>
@endsection