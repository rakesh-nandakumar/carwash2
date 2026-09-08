@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Cheque Payment Details</h1>
        <p>View complete cheque payment information and status.</p>
    </div>
    <a href="{{ route('cheque-payments.index') }}" class="secondary">Back to List</a>
</div>

<div class="panel">
    <!-- Status Banner -->
    <div class="status-banner">
        @if($payment->is_bounced)
            <span class="status-badge bounced">Bounced</span>
        @elseif($payment->payment_received)
            <span class="status-badge cleared">Cleared</span>
        @else
            <span class="status-badge pending">Pending</span>
        @endif
    </div>

    <!-- Cheque Information -->
    <div class="info-section">
        <h3>Cheque Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Cheque Number</span>
                <span class="value">{{ $payment->cheque_number }}</span>
            </div>
            <div class="info-item">
                <span class="label">Bank Name</span>
                <span class="value">{{ $payment->bank_name }}</span>
            </div>
            <div class="info-item">
                <span class="label">Due Date</span>
                <span class="value">{{ $payment->cheque_due_date ? $payment->cheque_due_date->format('Y-m-d') : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Amount</span>
                <span class="value">Rs. {{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Payment Status -->
    <div class="info-section">
        <h3>Payment Status</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Payment Received</span>
                <span class="value">{{ $payment->payment_received ? 'Yes' : 'No' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Payment Received At</span>
                <span class="value">{{ $payment->payment_received_at ? $payment->payment_received_at->format('Y-m-d H:i:s') : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Is Bounced</span>
                <span class="value">{{ $payment->is_bounced ? 'Yes' : 'No' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Bounced At</span>
                <span class="value">{{ $payment->bounced_at ? $payment->bounced_at->format('Y-m-d H:i:s') : 'N/A' }}</span>
            </div>
            <div class="info-item full-width">
                <span class="label">Bounce Reason</span>
                <span class="value">{{ $payment->bounce_reason ?? 'N/A' }}</span>
            </div>
            @if($payment->is_bounced)
            <div class="info-item">
                <span class="label">Follow-up Required</span>
                <span class="value">
                    {{ $payment->follow_up_required ? 'Yes' : 'No' }}
                    @if($payment->follow_up_date)
                        <span class="status-badge {{ $payment->isOverdueForFollowUp() ? 'overdue' : 'pending' }}">
                            {{ $payment->isOverdueForFollowUp() ? 'Overdue' : 'Due: ' . $payment->follow_up_date->format('M d') }}
                        </span>
                    @endif
                </span>
            </div>
            <div class="info-item full-width">
                <span class="label">Follow-up Notes</span>
                <span class="value">{{ $payment->follow_up_notes ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Replacement Payment Received</span>
                <span class="value">{{ $payment->replacement_payment_received ? 'Yes' : 'No' }}</span>
            </div>
            @endif
        </div>
    </div>

    @if($payment->replacement_payment_received && $payment->replacementPayment)
    <!-- Replacement Payment Details -->
    <div class="info-section">
        <h3>Replacement Payment Details</h3>
        <div class="success-box">
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Replacement Method</span>
                    <span class="value">{{ ucfirst($payment->replacementPayment->method) }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Replacement Amount</span>
                    <span class="value">Rs. {{ number_format($payment->replacementPayment->amount, 2) }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Replacement Reference</span>
                    <span class="value">{{ $payment->replacementPayment->reference ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Received At</span>
                    <span class="value">{{ $payment->replacementPayment->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Customer & Job Details -->
    <div class="info-section">
        <h3>Customer & Job Details</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Customer</span>
                <span class="value">{{ $payment->invoice->job->customer->full_name }}</span>
            </div>
            <div class="info-item">
                <span class="label">Vehicle</span>
                <span class="value">{{ $payment->invoice->job->vehicle->registration_number }}</span>
            </div>
            <div class="info-item">
                <span class="label">Job Number</span>
                <span class="value">{{ $payment->invoice->job->job_number }}</span>
            </div>
            <div class="info-item">
                <span class="label">Invoice Number</span>
                <span class="value">{{ $payment->invoice->invoice_number }}</span>
            </div>
            <div class="info-item">
                <span class="label">Invoice Total</span>
                <span class="value">Rs. {{ number_format($payment->invoice->total, 2) }}</span>
            </div>
            <div class="info-item">
                <span class="label">Invoice Status</span>
                <span class="value">{{ ucfirst($payment->invoice->status) }}</span>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="info-section">
        <h3>Additional Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Received By</span>
                <span class="value">{{ $payment->receivedBy ? $payment->receivedBy->name : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Created At</span>
                <span class="value">{{ $payment->created_at->format('Y-m-d H:i:s') }}</span>
            </div>
        </div>
    </div>

    <!-- Action Alerts -->
    @if(!$payment->payment_received && !$payment->is_bounced)
        <div class="action-alert warning">
            <strong>Action Required:</strong> This cheque is pending confirmation.
            <a href="{{ route('cheque-payments.confirm', $payment) }}" class="btn-action">Process Now</a>
        </div>
    @endif

    @if($payment->is_bounced && !$payment->replacement_payment_received)
        <div class="action-alert danger">
            <strong>⚠️ Action Required:</strong> This cheque has bounced and requires follow-up.
            <div class="alert-actions">
                @if($payment->needsFollowUp())
                    <a href="{{ route('cheque-payments.edit-bounce', $payment) }}" class="btn-action warning">Manage Follow-up</a>
                @endif
                <a href="{{ route('cheque-payments.replacement', $payment) }}" class="btn-action success">Record Replacement Payment</a>
            </div>
        </div>
    @endif
</div>

<style>
.status-banner {
    display: flex;
    justify-content: center;
    padding: 20px 0;
    margin-bottom: 24px;
}

.status-badge {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-block;
}

.status-badge.pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
    border: 1px solid #f59e0b;
}

.status-badge.cleared {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #059669;
    border: 1px solid #10b981;
}

.status-badge.bounced {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
    border: 1px solid #ef4444;
}

.status-badge.overdue {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
    border: 1px solid #ef4444;
    font-size: 11px;
    padding: 4px 12px;
}

.info-section {
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.info-section:last-child {
    border-bottom: none;
}

.info-section h3 {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item .label {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.info-item .value {
    font-size: 14px;
    font-weight: 500;
    color: #111827;
}

.success-box {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 1px solid #10b981;
    border-radius: 10px;
    padding: 20px;
}

.action-alert {
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
}

.action-alert.warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #f59e0b;
    color: #92400e;
}

.action-alert.danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border: 1px solid #ef4444;
    color: #991b1b;
}

.action-alert strong {
    display: block;
    margin-bottom: 12px;
}

.alert-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    margin-top: 8px;
}

.btn-action {
    background: #111827;
    color: #fff;
}

.btn-action:hover {
    background: #374151;
}

.btn-action.warning {
    background: #f59e0b;
    color: #fff;
}

.btn-action.warning:hover {
    background: #d97706;
}

.btn-action.success {
    background: #10b981;
    color: #fff;
}

.btn-action.success:hover {
    background: #059669;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }

    .alert-actions {
        flex-direction: column;
    }

    .btn-action {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection
