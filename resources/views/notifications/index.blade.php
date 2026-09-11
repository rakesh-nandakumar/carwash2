@extends('layouts.app')

@section('content')
<div class="notifications-page">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <div class="header-text">
                <h1>Notifications</h1>
                <p>Incomplete payments and pending actions requiring attention</p>
            </div>
        </div>
        <div class="header-stats">
            <div class="stat-card">
                <span class="stat-number">{{ $allNotifications->count() }}</span>
                <span class="stat-label">Total</span>
            </div>
            <div class="stat-card urgent">
                <span class="stat-number">{{ $pendingCheques->where('is_overdue', true)->count() + $bouncedCheques->where('is_overdue', true)->count() }}</span>
                <span class="stat-label">Urgent</span>
            </div>
        </div>
    </div>

    <div class="notifications-container">
    <!-- Partial Payments Section -->
    <div class="section-card partial-section">
        <div class="section-header">
            <div class="section-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
            </div>
            <h3>Partial Payments</h3>
            <span class="count-badge">{{ $partialPayments->count() }}</span>
        </div>

    @if($partialPayments->count() > 0)
        <!-- Desktop Table -->
        <div class="table-container">
            <table class="modern-table partial-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partialPayments as $payment)
                    <tr>
                        <td><span class="invoice-badge">#{{ $payment['invoice_number'] }}</span></td>
                        <td>{{ $payment['customer_name'] }}</td>
                        <td>{{ $payment['vehicle_registration'] }}</td>
                        <td>Rs. {{ number_format($payment['total_amount'], 2) }}</td>
                        <td>Rs. {{ number_format($payment['paid_amount'], 2) }}</td>
                        <td><span class="balance-amount">Rs. {{ number_format($payment['balance'], 2) }}</span></td>
                        <td>
                            <a href="{{ route('invoices.show', $payment['id']) }}?from=notifications" class="action-btn process-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                Complete
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="notifications-cards partial-cards">
            @foreach($partialPayments as $payment)
            <div class="notification-card partial-card">
                <div class="card-top">
                    <div class="card-name">
                        <strong>Invoice #{{ $payment['invoice_number'] }}</strong>
                        <small>{{ $payment['customer_name'] }}</small>
                    </div>
                    <span class="status-badge pending">Partial</span>
                </div>
                <div class="card-details">
                    <div class="detail">
                        <span class="label">Vehicle</span>
                        <span class="value">{{ $payment['vehicle_registration'] }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Total</span>
                        <span class="value">Rs. {{ number_format($payment['total_amount'], 2) }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Paid</span>
                        <span class="value">Rs. {{ number_format($payment['paid_amount'], 2) }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Balance</span>
                        <span class="value balance-amount">Rs. {{ number_format($payment['balance'], 2) }}</span>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="{{ route('invoices.show', $payment['id']) }}?from=notifications" class="btn-action process">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        Complete Payment
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">No partial payments.</div>
    @endif
    </div>

    <!-- Pending Cheques Section -->
    <div class="section-card pending-section">
        <div class="section-header">
            <div class="section-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                    <path d="M12 15h.01"/>
                    <path d="M16 15h.01"/>
                </svg>
            </div>
            <h3>Pending Cheques</h3>
            <span class="count-badge">{{ $pendingCheques->count() }}</span>
        </div>

    @if($pendingCheques->count() > 0)
        <!-- Desktop Table -->
        <div class="table-container">
            <table class="modern-table pending-table">
                <thead>
                    <tr>
                        <th>Cheque Number</th>
                        <th>Bank</th>
                        <th>Due Date</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingCheques as $cheque)
                    <tr class="{{ $cheque['is_overdue'] ? 'row-urgent' : '' }}">
                        <td><span class="cheque-badge">{{ $cheque['cheque_number'] }}</span></td>
                        <td>{{ $cheque['bank_name'] }}</td>
                        <td>
                            <div class="date-cell">
                                {{ $cheque['cheque_due_date'] ? $cheque['cheque_due_date']->format('Y-m-d') : 'N/A' }}
                                @if($cheque['is_overdue'])
                                    <span class="status-badge overdue">Overdue</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $cheque['customer_name'] }}</td>
                        <td>{{ $cheque['vehicle_registration'] }}</td>
                        <td>Rs. {{ number_format($cheque['amount'], 2) }}</td>
                        <td>
                            <a href="{{ route('cheque-payments.confirm', $cheque['payment_id']) }}?from=notifications" class="action-btn process-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                Process
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="notifications-cards pending-cards">
            @foreach($pendingCheques as $cheque)
            <div class="notification-card pending-card {{ $cheque['is_overdue'] ? 'card-urgent' : '' }}">
                <div class="card-top">
                    <div class="card-name">
                        <strong>{{ $cheque['cheque_number'] }}</strong>
                        <small>{{ $cheque['bank_name'] }}</small>
                    </div>
                    @if($cheque['is_overdue'])
                        <span class="status-badge overdue">Overdue</span>
                    @else
                        <span class="status-badge pending">Pending</span>
                    @endif
                </div>
                <div class="card-details">
                    <div class="detail">
                        <span class="label">Customer</span>
                        <span class="value">{{ $cheque['customer_name'] }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Vehicle</span>
                        <span class="value">{{ $cheque['vehicle_registration'] }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Amount</span>
                        <span class="value">Rs. {{ number_format($cheque['amount'], 2) }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Due Date</span>
                        <span class="value">{{ $cheque['cheque_due_date'] ? $cheque['cheque_due_date']->format('Y-m-d') : 'N/A' }}</span>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="{{ route('cheque-payments.confirm', $cheque['payment_id']) }}?from=notifications" class="btn-action process">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        Process
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">No pending cheques.</div>
    @endif
    </div>

    <!-- Bounced Cheques Section -->
    <div class="section-card bounced-section">
        <div class="section-header">
            <div class="section-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h3>Bounced Cheques</h3>
            <span class="count-badge">{{ $bouncedCheques->count() }}</span>
        </div>

    @if($bouncedCheques->count() > 0)
        <!-- Desktop Table -->
        <div class="table-container">
            <table class="modern-table bounced-table">
                <thead>
                    <tr>
                        <th>Cheque Number</th>
                        <th>Bank</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Follow-up Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bouncedCheques as $cheque)
                    <tr class="{{ $cheque['is_overdue'] ? 'row-urgent' : '' }}">
                        <td><span class="cheque-badge bounced">{{ $cheque['cheque_number'] }}</span></td>
                        <td>{{ $cheque['bank_name'] }}</td>
                        <td>{{ $cheque['customer_name'] }}</td>
                        <td>Rs. {{ number_format($cheque['amount'], 2) }}</td>
                        <td>
                            <div class="date-cell">
                                {{ $cheque['follow_up_date'] ? $cheque['follow_up_date']->format('Y-m-d') : 'ASAP' }}
                                @if($cheque['is_overdue'])
                                    <span class="status-badge overdue">Overdue</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('cheque-payments.edit-bounce', $cheque['payment_id']) }}" class="action-btn followup-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                Follow-up
                            </a>
                            @if(!$cheque['replacement_payment_received'])
                            <a href="{{ route('cheque-payments.replacement', $cheque['payment_id']) }}?from=notifications" class="action-btn replace-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                                Replace
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="notifications-cards bounced-cards">
            @foreach($bouncedCheques as $cheque)
            <div class="notification-card bounced-card {{ $cheque['is_overdue'] ? 'card-urgent' : '' }}">
                <div class="card-top">
                    <div class="card-name">
                        <strong>{{ $cheque['cheque_number'] }}</strong>
                        <small>{{ $cheque['bank_name'] }}</small>
                    </div>
                    @if($cheque['is_overdue'])
                        <span class="status-badge overdue">Overdue</span>
                    @else
                        <span class="status-badge pending">Follow-up</span>
                    @endif
                </div>
                <div class="card-details">
                    <div class="detail">
                        <span class="label">Customer</span>
                        <span class="value">{{ $cheque['customer_name'] }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Amount</span>
                        <span class="value">Rs. {{ number_format($cheque['amount'], 2) }}</span>
                    </div>
                    <div class="detail">
                        <span class="label">Follow-up Date</span>
                        <span class="value">{{ $cheque['follow_up_date'] ? $cheque['follow_up_date']->format('Y-m-d') : 'ASAP' }}</span>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="{{ route('cheque-payments.edit-bounce', $cheque['payment_id']) }}" class="btn-action followup">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Follow-up
                    </a>
                    @if(!$cheque['replacement_payment_received'])
                    <a href="{{ route('cheque-payments.replacement', $cheque['payment_id']) }}?from=notifications" class="btn-action replace">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                        Replace
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">No bounced cheques requiring follow-up.</div>
    @endif
    </div>

    <!-- Ready for Payment Section -->
    <div class="section-card ready-section">
        <div class="section-header">
            <div class="section-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <h3>Ready for Payment</h3>
            <span class="count-badge">{{ $readyForPayment->count() }}</span>
        </div>

    @if($readyForPayment->count() > 0)
        <!-- Desktop Table -->
        <div class="table-container">
            <table class="modern-table ready-table">
                <thead>
                    <tr>
                        <th>Job Number</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($readyForPayment as $job)
                    <tr>
                        <td><span class="job-badge">{{ $job['job_number'] }}</span></td>
                        <td>{{ $job['customer_name'] }}</td>
                        <td>{{ $job['vehicle_registration'] }}</td>
                        <td>
                            <a href="{{ route('cashier.payment', $job['job_id']) }}" class="action-btn process-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                Process
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="notifications-cards ready-cards">
            @foreach($readyForPayment as $job)
            <div class="notification-card ready-card">
                <div class="card-top">
                    <div class="card-name">
                        <strong>{{ $job['job_number'] }}</strong>
                        <small>{{ $job['customer_name'] }}</small>
                    </div>
                    <span class="status-badge resolved">Ready</span>
                </div>
                <div class="card-details">
                    <div class="detail">
                        <span class="label">Vehicle</span>
                        <span class="value">{{ $job['vehicle_registration'] }}</span>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="{{ route('cashier.payment', $job['job_id']) }}" class="btn-action process">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        Process Payment
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">No jobs ready for payment.</div>
    @endif
    </div>
</div>

<style>
.notifications-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 24px;
}

.page-header {
    background: white;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.header-text h1 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
}

.header-text p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

.header-stats {
    display: flex;
    gap: 16px;
}

.stat-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px 24px;
    text-align: center;
    min-width: 100px;
}

.stat-card.urgent {
    background: #fef2f2;
    border: 2px solid #fecaca;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.notifications-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.section-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}

.section-icon {
    width: 40px;
    height: 40px;
    background: #dbeafe;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
}

.section-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
}

.count-badge {
    margin-left: auto;
    background: #3b82f6;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.table-container {
    padding: 0;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table th {
    background: #f8fafc;
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: #475569;
    font-size: 13px;
    border-bottom: 2px solid #e2e8f0;
}

.modern-table td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 14px;
}

.modern-table tr:hover {
    background: #f8fafc;
}

.invoice-badge, .cheque-badge, .job-badge {
    background: #dbeafe;
    color: #1d4ed8;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.cheque-badge.bounced {
    background: #fee2e2;
    color: #dc2626;
}

.balance-amount {
    color: #dc2626;
    font-weight: 600;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #3b82f6;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.action-btn.replace-btn {
    background: #10b981;
}

.action-btn.replace-btn:hover {
    background: #059669;
}

.action-btn.followup-btn {
    background: #f59e0b;
}

.action-btn.followup-btn:hover {
    background: #d97706;
}

.notifications-cards {
    display: none;
}

.notification-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
}

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.card-name strong {
    display: block;
    color: #1e293b;
    font-size: 14px;
}

.card-name small {
    color: #64748b;
    font-size: 12px;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.pending {
    background: #fef3c7;
    color: #d97706;
}

.status-badge.overdue {
    background: #fee2e2;
    color: #dc2626;
}

.status-badge.resolved {
    background: #dcfce7;
    color: #16a34a;
}

.card-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 12px;
}

.detail {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail .label {
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
}

.detail .value {
    font-size: 13px;
    color: #334155;
    font-weight: 500;
}

.card-actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #3b82f6;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-action:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-action.replace {
    background: #10b981;
}

.btn-action.replace:hover {
    background: #059669;
}

.btn-action.followup {
    background: #f59e0b;
}

.btn-action.followup:hover {
    background: #d97706;
}

.empty-state {
    padding: 32px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
}

.row-urgent, .card-urgent {
    background: #fef2f2;
    border-color: #fecaca;
}

@media (max-width: 768px) {
    .table-container {
        display: none;
    }
    
    .notifications-cards {
        display: block;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .header-stats {
        width: 100%;
        justify-content: space-between;
    }
    
    .card-details {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

<script>
    // Auto-refresh if payment was just completed
    @if(session('payment_completed'))
        console.log('Payment completed flag detected');
        // Clear the session flag for next time
        @php
            session()->forget('payment_completed');
        @endphp
        setTimeout(function() {
            console.log('Refreshing page...');
            location.reload();
        }, 100);
    @endif

    // Auto-refresh if cheque was just processed
    @if(session('cheque_processed'))
        console.log('Cheque processed flag detected');
        // Clear the session flag for next time
        @php
            session()->forget('cheque_processed');
        @endphp
        setTimeout(function() {
            console.log('Refreshing page...');
            location.reload();
        }, 100);
    @endif

    // Also check localStorage as backup
    if (localStorage.getItem('paymentCompleted') === 'true') {
        console.log('Payment completed from localStorage, refreshing...');
        localStorage.removeItem('paymentCompleted');
        setTimeout(function() {
            location.reload();
        }, 100);
    }

    if (localStorage.getItem('chequeProcessed') === 'true') {
        console.log('Cheque processed from localStorage, refreshing...');
        localStorage.removeItem('chequeProcessed');
        setTimeout(function() {
            location.reload();
        }, 100);
    }
</script>