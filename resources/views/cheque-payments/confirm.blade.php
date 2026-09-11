@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Process Cheque Payment</h1>
        <p>Confirm cheque status and update payment records.</p>
    </div>
    @if($from === 'notifications')
        <a href="{{ route('notifications.index') }}" class="secondary">Back to Notifications</a>
    @else
        <a href="{{ route('cheque-payments.index') }}" class="secondary">Back to List</a>
    @endif
</div>

<div class="panel">
    <div class="info-section">
        <h3>Cheque Details</h3>
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
                <span class="label">Invoice Total</span>
                <span class="value">Rs. {{ number_format($payment->invoice->total, 2) }}</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('cheque-payments.process', $payment) }}?from={{ $from ?? 'cheque-payments' }}" class="action-form">
        @csrf

        <div class="alert-box info">
            <strong>Important:</strong> Please verify the cheque status with your bank before proceeding.
        </div>

        <div class="form-section">
            <h3>Action Required</h3>
            
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="action" value="confirm" required>
                    <div class="radio-content">
                        <strong>Confirm Payment Received</strong>
                        <small>The cheque has cleared and payment has been received. This will record the amount in the till.</small>
                    </div>
                </label>

                <label class="radio-option">
                    <input type="radio" name="action" value="bounce" required>
                    <div class="radio-content">
                        <strong>Mark as Bounced</strong>
                        <small>The cheque has bounced. This will reverse the payment and update the invoice status.</small>
                    </div>
                </label>
            </div>
        </div>

        <div class="form-section bounce-section" style="display: none;">
            <label for="bounce_reason">Bounce Reason</label>
            <textarea id="bounce_reason" name="bounce_reason" rows="3" placeholder="Enter the reason for the cheque bounce..."></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Process Cheque</button>
            @if($from === 'notifications')
                <a href="{{ route('notifications.index') }}" class="secondary">Cancel</a>
            @else
                <a href="{{ route('cheque-payments.index') }}" class="secondary">Cancel</a>
            @endif
        </div>
    </form>
</div>

<style>
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

.action-form {
    margin-top: 32px;
}

.alert-box {
    padding: 16px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
}

.alert-box.info {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #3b82f6;
    color: #1e40af;
}

.form-section {
    margin-bottom: 24px;
}

.form-section h3 {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.radio-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.radio-option {
    display: flex;
    gap: 12px;
    padding: 16px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.radio-option:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

.radio-option input[type="radio"] {
    margin-top: 4px;
    width: 18px;
    height: 18px;
    accent-color: #111827;
}

.radio-option input[type="radio"]:checked + .radio-content {
    color: #111827;
}

.radio-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.radio-content strong {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.radio-content small {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.4;
}

.bounce-section {
    margin-bottom: 24px;
}

.bounce-section label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.bounce-section textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    transition: border-color 0.2s ease;
}

.bounce-section textarea:focus {
    outline: none;
    border-color: #111827;
}

.form-actions {
    display: flex;
    gap: 12px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.form-actions button,
.form-actions a {
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.form-actions .primary {
    background: #111827;
    color: #fff;
}

.form-actions .primary:hover {
    background: #374151;
}

.form-actions .secondary {
    background: #f3f4f6;
    color: #374151;
}

.form-actions .secondary:hover {
    background: #e5e7eb;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions button,
    .form-actions a {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioInputs = document.querySelectorAll('input[name="action"]');
    const bounceSection = document.querySelector('.bounce-section');
    const bounceReason = document.getElementById('bounce_reason');

    radioInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'bounce') {
                bounceSection.style.display = 'block';
                bounceReason.required = true;
            } else {
                bounceSection.style.display = 'none';
                bounceReason.required = false;
            }
        });
    });

    // Set localStorage flag when cheque is processed
    @if(session('cheque_processed'))
        localStorage.setItem('chequeProcessed', 'true');
        // Clear the session flag
        @php
            session()->forget('cheque_processed');
        @endphp
    @endif
});
</script>
@endsection
