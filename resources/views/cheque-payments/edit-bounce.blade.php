@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Edit Bounced Cheque</h1>
        <p>Update bounce details or reverse the bounce status.</p>
    </div>
    <a href="{{ route('cheque-payments.show', $payment) }}" class="secondary">Back to Details</a>
</div>

<div class="panel">
    <div class="alert-box warning">
        <strong>Warning:</strong> This cheque has been marked as bounced. You can update the bounce reason or reverse the bounce status if it was marked incorrectly.
    </div>

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
                <span class="label">Amount</span>
                <span class="value">Rs. {{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="info-item">
                <span class="label">Bounced At</span>
                <span class="value">{{ $payment->bounced_at ? $payment->bounced_at->format('Y-m-d H:i:s') : 'N/A' }}</span>
            </div>
            <div class="info-item full-width">
                <span class="label">Current Bounce Reason</span>
                <span class="value">{{ $payment->bounce_reason ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('cheque-payments.update-bounce', $payment) }}" class="action-form">
        @csrf

        <div class="form-section">
            <h3>Action</h3>
            
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="action" value="update_reason" required>
                    <div class="radio-content">
                        <strong>Update Bounce Reason Only</strong>
                        <small>Keep the bounce status but update the reason.</small>
                    </div>
                </label>

                <label class="radio-option">
                    <input type="radio" name="action" value="reverse" required>
                    <div class="radio-content">
                        <strong>Reverse Bounce Status</strong>
                        <small>Mark this cheque as pending again (if it was incorrectly marked as bounced).</small>
                    </div>
                </label>

                <label class="radio-option">
                    <input type="radio" name="action" value="mark_followup_complete" required>
                    <div class="radio-content">
                        <strong>Mark Follow-up Complete</strong>
                        <small>Customer has been contacted and follow-up is complete.</small>
                    </div>
                </label>
            </div>
        </div>

        <div class="form-section reason-section">
            <label for="bounce_reason">Updated Bounce Reason</label>
            <textarea id="bounce_reason" name="bounce_reason" rows="3">{{ $payment->bounce_reason }}</textarea>
        </div>

        <div class="form-section followup-section" style="display: none;">
            <label for="follow_up_notes">Follow-up Notes</label>
            <textarea id="follow_up_notes" name="follow_up_notes" rows="3" placeholder="Describe how the customer was contacted and the outcome...">{{ $payment->follow_up_notes }}</textarea>
        </div>

        <div class="form-section reverse-section" style="display: none;">
            <div class="alert-box danger">
                <strong>Warning:</strong> Reversing the bounce status will:
                <ul>
                    <li>Mark the cheque as pending again</li>
                    <li>Restore the payment amount to the invoice</li>
                    <li>Update the invoice status accordingly</li>
                </ul>
                <p>This action should only be taken if the cheque was incorrectly marked as bounced.</p>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Update Cheque</button>
            <a href="{{ route('cheque-payments.show', $payment) }}" class="secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.alert-box {
    padding: 16px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
}

.alert-box.warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #f59e0b;
    color: #92400e;
}

.alert-box.danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border: 1px solid #ef4444;
    color: #991b1b;
}

.alert-box ul {
    margin: 12px 0;
    padding-left: 20px;
}

.alert-box p {
    margin: 12px 0 0 0;
}

.info-section {
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
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

.reason-section label,
.followup-section label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.reason-section textarea,
.followup-section textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    transition: border-color 0.2s ease;
}

.reason-section textarea:focus,
.followup-section textarea:focus {
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
    const reasonSection = document.querySelector('.reason-section');
    const followupSection = document.querySelector('.followup-section');
    const reverseSection = document.querySelector('.reverse-section');
    const bounceReason = document.getElementById('bounce_reason');
    const followUpNotes = document.getElementById('follow_up_notes');

    function toggleActionFields() {
        const selectedAction = document.querySelector('input[name="action"]:checked').value;

        if (selectedAction === 'update_reason') {
            reasonSection.style.display = 'block';
            bounceReason.required = true;
            followupSection.style.display = 'none';
            followUpNotes.required = false;
            reverseSection.style.display = 'none';
        } else if (selectedAction === 'reverse') {
            reasonSection.style.display = 'none';
            bounceReason.required = false;
            followupSection.style.display = 'none';
            followUpNotes.required = false;
            reverseSection.style.display = 'block';
        } else if (selectedAction === 'mark_followup_complete') {
            reasonSection.style.display = 'none';
            bounceReason.required = false;
            followupSection.style.display = 'block';
            followUpNotes.required = true;
            reverseSection.style.display = 'none';
        }
    }

    radioInputs.forEach(input => {
        input.addEventListener('change', toggleActionFields);
    });

    // Initialize with first option selected
    if (radioInputs.length > 0) {
        radioInputs[0].checked = true;
        toggleActionFields();
    }
});
</script>
@endsection
