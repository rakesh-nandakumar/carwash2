@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Record Replacement Payment</h1>
        <p>Document the replacement payment for a bounced cheque.</p>
    </div>
    <a href="{{ route('cheque-payments.show', $payment) }}" class="secondary">Back to Details</a>
</div>

<div class="panel">
    <div class="info-section warning-box">
        <h3>Original Bounced Cheque</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Cheque Number</span>
                <span class="value">{{ $payment->cheque_number }}</span>
            </div>
            <div class="info-item">
                <span class="label">Bank</span>
                <span class="value">{{ $payment->bank_name }}</span>
            </div>
            <div class="info-item">
                <span class="label">Original Amount</span>
                <span class="value">Rs. {{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="info-item full-width">
                <span class="label">Bounce Reason</span>
                <span class="value">{{ $payment->bounce_reason ?? 'N/A' }}</span>
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
                <span class="label">Current Invoice Balance</span>
                <span class="value">Rs. {{ number_format($payment->invoice->balance, 2) }}</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('cheque-payments.process-replacement', $payment) }}" class="action-form">
        @csrf

        <div class="alert-box info">
            <strong>Important:</strong> Record the replacement payment method and amount. This will update the invoice and till accordingly.
        </div>

        <div class="form-section">
            <h3>Replacement Payment Details</h3>

            <div class="form-group">
                <label for="replacement_payment_method">Replacement Payment Method</label>
                <select id="replacement_payment_method" name="replacement_payment_method" required>
                    <option value="">Select payment method</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="upi">UPI</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">New Cheque</option>
                </select>
            </div>

            <div class="form-group">
                <label for="replacement_amount">Replacement Amount</label>
                <input type="number" id="replacement_amount" name="replacement_amount" step="0.01" min="0" value="{{ $payment->amount }}" required>
                <small>Original amount: Rs. {{ number_format($payment->amount, 2) }}</small>
            </div>

            <div class="form-group">
                <label for="replacement_reference">Reference Number (Optional)</label>
                <input type="text" id="replacement_reference" name="replacement_reference" placeholder="Enter reference number if applicable">
            </div>

            <!-- Cheque-specific fields - shown only when cheque is selected -->
            <div id="chequeFields" style="display: none;">
                <div class="form-group">
                    <label for="cheque_number">Cheque Number</label>
                    <input type="text" id="cheque_number" name="cheque_number" placeholder="Enter new cheque number">
                </div>

                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" placeholder="Enter bank name">
                </div>

                <div class="form-group">
                    <label for="cheque_due_date">Cheque Due Date</label>
                    <input type="date" id="cheque_due_date" name="cheque_due_date">
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about the replacement payment..."></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary success">Record Replacement Payment</button>
            <a href="{{ route('cheque-payments.show', $payment) }}" class="secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.info-section {
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.info-section.warning-box {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #f59e0b;
    border-radius: 10px;
    padding: 20px;
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
    margin: 0 0 20px 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #111827;
}

.form-group small {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #6b7280;
}

.form-group textarea {
    resize: vertical;
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

.form-actions .primary.success {
    background: #10b981;
}

.form-actions .primary.success:hover {
    background: #059669;
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
    const paymentMethodSelect = document.getElementById('replacement_payment_method');
    const chequeFields = document.getElementById('chequeFields');
    const chequeNumber = document.getElementById('cheque_number');
    const bankName = document.getElementById('bank_name');
    const chequeDueDate = document.getElementById('cheque_due_date');

    function toggleChequeFields() {
        if (paymentMethodSelect.value === 'cheque') {
            chequeFields.style.display = 'block';
            chequeNumber.required = true;
            bankName.required = true;
            chequeDueDate.required = true;
        } else {
            chequeFields.style.display = 'none';
            chequeNumber.required = false;
            bankName.required = false;
            chequeDueDate.required = false;
            chequeNumber.value = '';
            bankName.value = '';
            chequeDueDate.value = '';
        }
    }

    paymentMethodSelect.addEventListener('change', toggleChequeFields);
    toggleChequeFields(); // Initialize on page load
});
</script>
@endsection
