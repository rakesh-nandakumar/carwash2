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

    {{-- Bounced Cheque Alert --}}
    @php
        $bouncedChequesNeedingFollowUp = \App\Models\Payment::with(['invoice.job.customer', 'invoice.job.vehicle'])
            ->where('method', 'cheque')
            ->where('is_bounced', true)
            ->where('follow_up_required', true)
            ->where('replacement_payment_received', false)
            ->orderBy('bounced_at', 'desc')
            ->get();
    @endphp

    @if($bouncedChequesNeedingFollowUp->count() > 0)
        <div class="alert-panel bounced-cheque-alert">
            <div class="alert-header">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3>⚠️ Bounced Cheques Require Action ({{ $bouncedChequesNeedingFollowUp->count() }})</h3>
            </div>
            <div class="alert-content">
                @foreach($bouncedChequesNeedingFollowUp->take(3) as $bouncedCheque)
                    <div class="bounced-cheque-item">
                        <div class="cheque-info">
                            <strong>{{ $bouncedCheque->invoice->job->customer->full_name }}</strong>
                            <span>{{ $bouncedCheque->invoice->job->vehicle->registration_number }}</span>
                        </div>
                        <div class="cheque-details">
                            <span class="amount">Rs. {{ number_format($bouncedCheque->amount, 2) }}</span>
                            <span class="status">
                                @if($bouncedCheque->isOverdueForFollowUp())
                                    <span class="alert-badge overdue">Overdue</span>
                                @else
                                    <span class="alert-badge pending">Follow-up: {{ $bouncedCheque->follow_up_date ? $bouncedCheque->follow_up_date->format('M d') : 'ASAP' }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="cheque-actions">
                            <a href="{{ route('cheque-payments.show', $bouncedCheque) }}" class="btn-small">View Details</a>
                            <a href="{{ route('cheque-payments.edit-bounce', $bouncedCheque) }}" class="btn-small primary">Manage</a>
                        </div>
                    </div>
                @endforeach
                @if($bouncedChequesNeedingFollowUp->count() > 3)
                    <div class="view-all-link">
                        <a href="{{ route('cheque-payments.index') }}">View all {{ $bouncedChequesNeedingFollowUp->count() }} bounced cheques →</a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Till Summary Panel --}}
    <div class="till-panel">
        <div class="till-header">
            <div class="till-info-section">
                <div>
                    <h2>{{ $till->name }}</h2>
                    <p>Current Till</p>
                </div>
            </div>

            <div class="header-badges">
                <span class="till-status {{ $till->is_active ? 'active' : 'inactive' }}">
                    {{ $till->is_active ? 'Active' : 'Inactive' }}
                </span>
                @if($till->isInUse())
                    <span class="in-use-badge">
                        In Use
                    </span>
                @endif
                @if($isShiftOpen)
                    <span class="shift-status open">
                        Shift Open
                    </span>
                @else
                    <span class="shift-status closed">
                        Shift Closed
                    </span>
                @endif
                @if($readyForPayment->count() > 0)
                    <span class="ready-count">
                        {{ $readyForPayment->count() }} Ready
                    </span>
                @endif
            </div>
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
            @if(auth()->user()->hasPermissionTo('cashier.open_shift'))
                @if($isShiftOpen)
                    <a href="{{ route('cashier.till-action') }}" class="btn-shift-close">
                        Close Till
                    </a>
                @else
                    <a href="{{ route('cashier.till-action') }}" class="btn-shift-open">
                        Open Till
                    </a>
                @endif
            @endif

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

            @if(auth()->user()->hasPermissionTo('cashier.access'))
                <a href="{{ route('cashier.shift-history') }}" class="btn-history">
                    History
                </a>
            @endif

            @if(auth()->user()->hasPermissionTo('settings.access'))
                <button type="button" onclick="openChangeTillModal()" class="btn-change-till">
                    Change Till
                </button>
            @endif
        </div>
    </div>

    <div class="vehicles-grid">
        @forelse($readyForPayment as $job)
            <div class="vehicle-card {{ $isShiftOpen ? '' : 'till-closed' }}" 
                 @if($isShiftOpen)
                 onclick="window.location.href='{{ route('cashier.payment', $job) }}'"
                 @else
                 onclick="showTillNotOpenToast()"
                 @endif
            >
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

{{-- Change Till Modal --}}
<div id="changeTillModal" class="cash-modal hidden">
    <div class="cash-modal-content">
        <div class="cash-modal-header">
            <h2>Change Till</h2>
            <button type="button" onclick="closeChangeTillModal()">×</button>
        </div>

        <form method="POST" action="{{ route('tills.select') }}" id="changeTillForm">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ route('cashier.index') }}">

            <div class="form-group">
                <label>Select Till</label>
                <select name="till_id" id="changeTillSelect" required>
                    <option value="">-- Select a Till --</option>
                </select>
                <small>Select a different till to switch to. This will release your current till.</small>
            </div>

            <div class="cash-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeChangeTillModal()">
                    Cancel
                </button>
                <button type="submit" class="btn-confirm">
                    Change Till
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

.till-info-section {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-badges {
    display: flex;
    gap: 8px;
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

.shift-status {
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
}

.shift-status.open {
    background: #dbeafe;
    color: #1e40af;
}

.shift-status.closed {
    background: #f1f5f9;
    color: #64748b;
}

.in-use-badge {
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
    background: #dbeafe;
    color: #1e40af;
}

.ready-count {
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
    background: #10b981;
    color: white;
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

.till-actions a {
    padding: 10px 18px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
}

.till-actions a:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.btn-shift-open {
    background: #10b981 !important;
    color: white !important;
    border-color: #10b981 !important;
}

.btn-shift-open:hover {
    background: #059669 !important;
    border-color: #059669 !important;
}

.btn-shift-close {
    background: #f59e0b !important;
    color: white !important;
    border-color: #f59e0b !important;
}

.btn-shift-close:hover {
    background: #d97706 !important;
    border-color: #d97706 !important;
}

/* Bounced Cheque Alert Styles */
.alert-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin: 20px 40px;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bounced-cheque-alert {
    border: 3px solid #ef4444;
    background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
}

.alert-header {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 2px solid #ef4444;
}

.alert-header svg {
    color: #dc2626;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.alert-header h3 {
    color: #dc2626;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
}

.alert-content {
    padding: 16px 20px;
}

.bounced-cheque-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    margin-bottom: 10px;
    gap: 16px;
}

.bounced-cheque-item:last-child {
    margin-bottom: 0;
}

.cheque-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.cheque-info strong {
    color: #1e293b;
    font-size: 14px;
}

.cheque-info span {
    color: #64748b;
    font-size: 12px;
}

.cheque-details {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cheque-details .amount {
    color: #dc2626;
    font-weight: 700;
    font-size: 14px;
}

.cheque-details .alert-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.cheque-details .alert-badge.overdue {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
    border: 1px solid #ef4444;
}

.cheque-details .alert-badge.pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
    border: 1px solid #f59e0b;
}

.cheque-actions {
    display: flex;
    gap: 8px;
}

.btn-small {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-small:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.btn-small.primary {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.btn-small.primary:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.view-all-link {
    text-align: center;
    padding-top: 12px;
    border-top: 1px solid #fecaca;
}

.view-all-link a {
    color: #dc2626;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
}

.view-all-link a:hover {
    text-decoration: underline;
}

.btn-history {
    background: #6366f1 !important;
    color: white !important;
    border-color: #6366f1 !important;
}

.btn-history:hover {
    background: #4f46e5 !important;
    border-color: #4f46e5 !important;
}

.btn-change-till {
    background: #8b5cf6 !important;
    color: white !important;
    border-color: #8b5cf6 !important;
}

.btn-change-till:hover {
    background: #7c3aed !important;
    border-color: #7c3aed !important;
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
.form-group textarea,
.form-group select {
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
.form-group textarea:focus,
.form-group select:focus {
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

.till-in-use {
    color: #94a3b8;
    background-color: #f1f5f9;
}

/* Toast animations */
@keyframes toastIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes toastOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(20px); }
}

/* Disabled state when till is closed */
.vehicle-card.till-closed {
    opacity: 0.6;
    cursor: not-allowed;
}

.vehicle-card.till-closed:hover {
    transform: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border-color: #e5e7eb;
}

.vehicle-card.till-closed .action-arrow {
    color: #cbd5e1;
    transform: none;
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

    .till-header {
        flex-direction: column;
        gap: 12px;
    }

    .till-info-section {
        width: 100%;
    }

    .header-badges {
        width: 100%;
        justify-content: center;
    }

    .vehicles-grid {
        padding: 24px 16px;
        grid-template-columns: 1fr;
    }

    .till-stats {
        grid-template-columns: 1fr 1fr;
    }

    .till-actions {
        flex-wrap: wrap;
        gap: 8px;
    }

    .till-actions button,
    .till-actions a {
        flex: 1 1 auto;
        min-width: calc(50% - 4px);
        padding: 10px 12px;
        font-size: 12px;
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

// Make the function available globally for onclick handlers
window.openCashModal = openCashModal;
window.closeCashModal = closeCashModal;

// Change Till Modal Functions
function openChangeTillModal() {
    const modal = document.getElementById('changeTillModal');
    const select = document.getElementById('changeTillSelect');
    
    // Fetch available tills
    fetch('{{ route('tills.status') }}')
        .then(response => response.json())
        .then(data => {
            // Clear existing options
            select.innerHTML = '<option value="">-- Select a Till --</option>';
            
            // Add tills to select
            data.tills.forEach(till => {
                const option = document.createElement('option');
                option.value = till.id;
                option.textContent = `${till.name} (${till.code})${till.location ? ' - ' + till.location : ''}`;
                
                // Don't show current till as an option
                if (till.id !== {{ $till->id }}) {
                    select.appendChild(option);
                }
            });
            
            modal.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error fetching tills:', error);
            alert('Failed to load available tills. Please try again.');
        });
}

function closeChangeTillModal() {
    document.getElementById('changeTillModal').classList.add('hidden');
}

// Make the function available globally
window.openChangeTillModal = openChangeTillModal;
window.closeChangeTillModal = closeChangeTillModal;

// Show toast when till is not open
function showTillNotOpenToast() {
    const toast = document.createElement('div');
    toast.className = 'toast error';
    toast.textContent = 'Please open the till before processing payments';
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '100001';
    toast.style.padding = '14px 20px';
    toast.style.borderRadius = '10px';
    toast.style.fontSize = '14px';
    toast.style.fontWeight = '500';
    toast.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.15)';
    toast.style.maxWidth = '360px';
    toast.style.background = '#fef2f2';
    toast.style.color = '#991b1b';
    toast.style.border = '1px solid #fecaca';
    toast.style.animation = 'toastIn 0.3s ease';
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'toastOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Make the function available globally
window.showTillNotOpenToast = showTillNotOpenToast;
</script>
@endsection