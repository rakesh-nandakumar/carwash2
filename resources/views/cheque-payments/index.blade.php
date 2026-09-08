@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Cheque Payments</h1>
        <p>Manage pending, cleared, and bounced cheque payments.</p>
    </div>
</div>

<div class="panel">
    <!-- Pending Cheques Section -->
    <div class="section-header">
        <h3>Pending Cheques <span class="count">{{ $pendingCheques->count() }}</span></h3>
    </div>

    <!-- Desktop Table -->
    <table class="cheques-table pending-table">
        <thead>
            <tr>
                <th>Cheque Number</th>
                <th>Bank</th>
                <th>Due Date</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Amount</th>
                <th>Job</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingCheques as $payment)
            <tr>
                <td><strong>{{ $payment->cheque_number }}</strong></td>
                <td>{{ $payment->bank_name }}</td>
                <td>
                    {{ $payment->cheque_due_date ? $payment->cheque_due_date->format('Y-m-d') : 'N/A' }}
                    @if($payment->cheque_due_date && $payment->cheque_due_date->isPast())
                        <span class="status-badge overdue">Overdue</span>
                    @endif
                </td>
                <td>{{ $payment->invoice->job->customer->full_name }}</td>
                <td>{{ $payment->invoice->job->vehicle->registration_number }}</td>
                <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                <td>{{ $payment->invoice->job->job_number }}</td>
                <td>
                    <a href="{{ route('cheque-payments.confirm', $payment) }}" class="action-link process">Process</a>
                    <a href="{{ route('cheque-payments.show', $payment) }}" class="action-link">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty">No pending cheques.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="cheques-cards pending-cards">
        @forelse($pendingCheques as $payment)
        <div class="cheque-card pending-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $payment->cheque_number }}</strong>
                    <small>{{ $payment->bank_name }}</small>
                </div>
                @if($payment->cheque_due_date && $payment->cheque_due_date->isPast())
                    <span class="status-badge overdue">Overdue</span>
                @endif
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Customer</span>
                    <span class="value">{{ $payment->invoice->job->customer->full_name }}</span>
                </div>
                <div class="detail">
                    <span class="label">Vehicle</span>
                    <span class="value">{{ $payment->invoice->job->vehicle->registration_number }}</span>
                </div>
                <div class="detail">
                    <span class="label">Amount</span>
                    <span class="value">Rs. {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Due Date</span>
                    <span class="value">{{ $payment->cheque_due_date ? $payment->cheque_due_date->format('Y-m-d') : 'N/A' }}</span>
                </div>
            </div>
            <div class="card-actions">
                <a href="{{ route('cheque-payments.show', $payment) }}" class="btn-action">View</a>
                <a href="{{ route('cheque-payments.confirm', $payment) }}" class="btn-action process">Process</a>
            </div>
        </div>
        @empty
        <div class="empty-state">No pending cheques.</div>
        @endforelse
    </div>

    <!-- Cleared Cheques Section -->
    <div class="section-header mt-5">
        <h3>Cleared Cheques <span class="count">{{ $clearedCheques->count() }}</span></h3>
    </div>

    <!-- Desktop Table -->
    <table class="cheques-table cleared-table">
        <thead>
            <tr>
                <th>Cheque Number</th>
                <th>Bank</th>
                <th>Due Date</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Received Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($clearedCheques as $payment)
            <tr>
                <td><strong>{{ $payment->cheque_number }}</strong></td>
                <td>{{ $payment->bank_name }}</td>
                <td>{{ $payment->cheque_due_date ? $payment->cheque_due_date->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ $payment->invoice->job->customer->full_name }}</td>
                <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                <td>{{ $payment->payment_received_at ? $payment->payment_received_at->format('Y-m-d') : 'N/A' }}</td>
                <td>
                    <a href="{{ route('cheque-payments.show', $payment) }}" class="action-link">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty">No cleared cheques.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="cheques-cards cleared-cards">
        @forelse($clearedCheques as $payment)
        <div class="cheque-card cleared-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $payment->cheque_number }}</strong>
                    <small>{{ $payment->bank_name }}</small>
                </div>
                <span class="status-badge resolved">Cleared</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Customer</span>
                    <span class="value">{{ $payment->invoice->job->customer->full_name }}</span>
                </div>
                <div class="detail">
                    <span class="label">Amount</span>
                    <span class="value">Rs. {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Received</span>
                    <span class="value">{{ $payment->payment_received_at ? $payment->payment_received_at->format('Y-m-d') : 'N/A' }}</span>
                </div>
            </div>
            <div class="card-actions">
                <a href="{{ route('cheque-payments.show', $payment) }}" class="btn-action">View</a>
            </div>
        </div>
        @empty
        <div class="empty-state">No cleared cheques.</div>
        @endforelse
    </div>

    <!-- Bounced Cheques Section -->
    <div class="section-header mt-5">
        <h3>Bounced Cheques <span class="count">{{ $bouncedCheques->count() }}</span></h3>
    </div>

    <!-- Desktop Table -->
    <table class="cheques-table bounced-table">
        <thead>
            <tr>
                <th>Cheque Number</th>
                <th>Bank</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Bounce Date</th>
                <th>Reason</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bouncedCheques as $payment)
            <tr class="{{ $payment->needsFollowUp() ? 'row-urgent' : '' }}">
                <td><strong>{{ $payment->cheque_number }}</strong></td>
                <td>{{ $payment->bank_name }}</td>
                <td>{{ $payment->invoice->job->customer->full_name }}</td>
                <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                <td>{{ $payment->bounced_at ? $payment->bounced_at->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ $payment->bounce_reason ?? 'N/A' }}</td>
                <td>
                    @if($payment->needsFollowUp())
                        @if($payment->isOverdueForFollowUp())
                            <span class="status-badge overdue">Overdue</span>
                        @else
                            <span class="status-badge pending">Follow-up: {{ $payment->follow_up_date ? $payment->follow_up_date->format('M d') : 'ASAP' }}</span>
                        @endif
                    @elseif($payment->replacement_payment_received)
                        <span class="status-badge resolved">Replaced</span>
                    @else
                        <span class="status-badge completed">Resolved</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('cheque-payments.show', $payment) }}" class="action-link">View</a>
                    @if($payment->needsFollowUp())
                        <a href="{{ route('cheque-payments.edit-bounce', $payment) }}" class="action-link followup">Follow-up</a>
                    @endif
                    @if(!$payment->replacement_payment_received)
                        <a href="{{ route('cheque-payments.replacement', $payment) }}" class="action-link replace">Replace</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty">No bounced cheques.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="cheques-cards bounced-cards">
        @forelse($bouncedCheques as $payment)
        <div class="cheque-card bounced-card {{ $payment->needsFollowUp() ? 'card-urgent' : '' }}">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $payment->cheque_number }}</strong>
                    <small>{{ $payment->bank_name }}</small>
                </div>
                @if($payment->needsFollowUp())
                    @if($payment->isOverdueForFollowUp())
                        <span class="status-badge overdue">Overdue</span>
                    @else
                        <span class="status-badge pending">Follow-up</span>
                    @endif
                @elseif($payment->replacement_payment_received)
                    <span class="status-badge resolved">Replaced</span>
                @else
                    <span class="status-badge completed">Resolved</span>
                @endif
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Customer</span>
                    <span class="value">{{ $payment->invoice->job->customer->full_name }}</span>
                </div>
                <div class="detail">
                    <span class="label">Amount</span>
                    <span class="value">Rs. {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Bounce Date</span>
                    <span class="value">{{ $payment->bounced_at ? $payment->bounced_at->format('Y-m-d') : 'N/A' }}</span>
                </div>
                <div class="detail">
                    <span class="label">Reason</span>
                    <span class="value">{{ $payment->bounce_reason ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="card-actions">
                <a href="{{ route('cheque-payments.show', $payment) }}" class="btn-action">View</a>
                @if($payment->needsFollowUp())
                    <a href="{{ route('cheque-payments.edit-bounce', $payment) }}" class="btn-action followup">Follow-up</a>
                @endif
                @if(!$payment->replacement_payment_received)
                    <a href="{{ route('cheque-payments.replacement', $payment) }}" class="btn-action replace">Replace</a>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">No bounced cheques.</div>
        @endforelse
    </div>
</div>

<style>
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
    letter-spacing: 0.3px;
}

.status-badge.overdue {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
    border: 1px solid #ef4444;
}

.status-badge.pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
    border: 1px solid #f59e0b;
}

.status-badge.resolved {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #059669;
    border: 1px solid #10b981;
}

.status-badge.completed {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4f46e5;
    border: 1px solid #6366f1;
}

.section-header {
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
}

.section-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header .count {
    background: #f3f4f6;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
}

.cheques-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
}

.cheques-table thead {
    background: #f9fafb;
}

.cheques-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
}

.cheques-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}

.cheques-table tr:hover {
    background: #f9fafb;
}

.cheques-table .empty {
    text-align: center;
    padding: 40px 16px;
    color: #9ca3af;
    font-size: 14px;
}

.cheques-table .row-urgent {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

.cheques-table .row-urgent:hover {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
}

.action-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    margin-right: 8px;
    transition: all 0.2s ease;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    color: #374151;
}

.action-link:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.action-link.process {
    background: #3b82f6;
    color: #ffffff;
    border-color: #3b82f6;
}

.action-link.process:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.action-link.followup {
    background: #f59e0b;
    color: #ffffff;
    border-color: #f59e0b;
}

.action-link.followup:hover {
    background: #d97706;
    border-color: #d97706;
}

.action-link.replace {
    background: #10b981;
    color: #ffffff;
    border-color: #10b981;
}

.action-link.replace:hover {
    background: #059669;
    border-color: #059669;
}

.cheques-cards {
    display: none;
}

.cheque-card {
    display: block;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    margin-bottom: 12px;
}

.cheque-card.card-urgent {
    border-color: #ef4444;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
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

.card-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 12px;
    margin-bottom: 12px;
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

.card-actions {
    display: flex;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
}

.card-actions a {
    flex: 1;
    text-align: center;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    color: #374151;
}

.card-actions a:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.btn-action.process {
    background: #3b82f6;
    color: #ffffff;
    border-color: #3b82f6;
}

.btn-action.process:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.btn-action.followup {
    background: #f59e0b;
    color: #ffffff;
    border-color: #f59e0b;
}

.btn-action.followup:hover {
    background: #d97706;
    border-color: #d97706;
}

.btn-action.replace {
    background: #10b981;
    color: #ffffff;
    border-color: #10b981;
}

.btn-action.replace:hover {
    background: #059669;
    border-color: #059669;
}

.empty-state {
    text-align: center;
    padding: 30px 16px;
    color: #9ca3af;
    font-size: 14px;
}

.mt-5 {
    margin-top: 32px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .cheques-table {
        display: none;
    }

    .cheques-cards {
        display: block;
    }

    .section-header h3 {
        font-size: 16px;
    }

    .card-details {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
