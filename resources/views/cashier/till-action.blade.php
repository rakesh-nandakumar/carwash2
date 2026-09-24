@extends('layouts.app')
@section('content')
<div class="till-action-container">
    <div class="till-action-header">
        <h1>{{ $isOpen ? 'Close Till' : 'Open Till' }}</h1>
        <p>{{ $isOpen ? 'End your working day by closing the till' : 'Start your working day by opening the till' }}</p>
    </div>

    <div class="till-action-content">
        <div class="till-info-card">
            <div class="till-details">
                <h2>{{ $till->name }} ({{ $till->code }})</h2>
            </div>

            @if($isOpen)
                <div class="shift-status-card">
                    <span class="status-badge {{ $isOpen ? 'active' : 'inactive' }}">{{ $isOpen ? 'Till Open' : 'Till Closed' }}</span>
                    <p>{{ $isOpen && $currentClosure && $currentClosure->opened_at ? 'Opened at ' . $currentClosure->opened_at->format('g:i A') : 'No till is currently open' }}</p>
                </div>
            @endif
        </div>

        @if($isOpen)
            <div class="balance-summary-card">
                <h3>Today's Summary</h3>
                <div class="balance-grid">
                    <div class="balance-item">
                        <span>Opening Balance</span>
                        <strong>Rs. {{ number_format($currentClosure->opening_balance, 2) }}</strong>
                    </div>
                    <div class="balance-item">
                        <span>Cash Sales</span>
                        <strong>Rs. {{ number_format($summary['cash_sales'], 2) }}</strong>
                    </div>
                    <div class="balance-item">
                        <span>Card Sales</span>
                        <strong>Rs. {{ number_format($summary['card_sales'], 2) }}</strong>
                    </div>
                    <div class="balance-item">
                        <span>UPI Sales</span>
                        <strong>Rs. {{ number_format($summary['mobile_money_sales'], 2) }}</strong>
                    </div>
                    <div class="balance-item">
                        <span>Bank Transfer</span>
                        <strong>Rs. {{ number_format($summary['bank_transfer_sales'], 2) }}</strong>
                    </div>
                    <div class="balance-item highlight">
                        <span>Total Sales</span>
                        <strong>Rs. {{ number_format($summary['total_sales'], 2) }}</strong>
                    </div>
                    <div class="balance-item">
                        <span>Cash In</span>
                        <strong>Rs. {{ number_format($summary['cash_in'], 2) }}</strong>
                    </div>
                    <div class="balance-item">
                        <span>Withdrawals</span>
                        <strong>Rs. {{ number_format($summary['cash_out'], 2) }}</strong>
                    </div>
                    <div class="balance-item expected">
                        <span>Expected Cash Balance</span>
                        <strong>Rs. {{ number_format($expectedBalance, 2) }}</strong>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('cashier.till-action.post') }}" class="till-action-form">
                @csrf

                <div class="form-section">
                    <h3>Cash Counting</h3>
                    
                    <div class="balance-choice">
                        <label class="choice-box {{ $expectedBalance > 0 ? 'recommended' : '' }}">
                            <input type="radio" name="balance_option" value="expected" checked onchange="toggleManualBalance()">
                            <div class="choice-content">
                                <div class="choice-title">Expected Balance</div>
                                <div class="choice-value">Rs. {{ number_format($expectedBalance, 2) }}</div>
                            </div>
                        </label>
                        <label class="choice-box">
                            <input type="radio" name="balance_option" value="manual" onchange="toggleManualBalance()">
                            <div class="choice-content">
                                <div class="choice-title">Manual Count</div>
                                <div class="choice-subtitle">Count cash yourself</div>
                            </div>
                        </label>
                    </div>

                    <div class="form-group manual-balance-group" id="manualBalanceGroup">
                        <label>Enter the actual cash amount</label>
                        <input
                            type="number"
                            name="manual_balance"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="form-input"
                            id="manualBalanceInput"
                            autofocus
                            oninput="calculateVariance()"
                        >
                        <div id="varianceDisplay" class="variance-display hidden">
                            <div class="variance-label">Variance:</div>
                            <div class="variance-amount" id="varianceAmount">Rs. 0.00</div>
                        </div>
                    </div>
                </div>

                <div class="form-section manual-balance-group" id="varianceReasonGroup">
                    <h3>Reason for Variance <span class="required">*</span></h3>
                    <div class="form-group">
                        <label>Please explain why the counted amount differs from the expected amount</label>
                        <input
                            type="text"
                            name="variance_reason"
                            class="form-input"
                            id="varianceReasonInput"
                            placeholder="e.g., Cash counting error, Petty cash used, etc."
                        >
                    </div>
                </div>

                <div class="form-section">
                    <h3>Additional Notes</h3>
                    <textarea
                        name="notes"
                        rows="2"
                        placeholder="Add any notes about this till closure..."
                        class="form-textarea"
                    ></textarea>
                </div>

                <input type="hidden" name="expected_balance" value="{{ $expectedBalance }}">

                <div class="form-actions">
                    <a href="{{ route('cashier.index') }}" class="btn-cancel">
                        Cancel
                    </a>
                    <button type="submit" class="btn-submit">
                        Close Till
                    </button>
                </div>
            </form>
        @else
            <form method="POST" action="{{ route('cashier.till-action.post') }}" class="till-action-form">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-section">
                    <h3>Select Till</h3>

                    @if($hasPermanentTill)
                        <div class="till-selection-card selected">
                            <strong>{{ $availableTills->first()->name }} ({{ $availableTills->first()->code }})</strong>
                            <small>Your Assigned Till</small>
                            <input type="hidden" name="till_id" value="{{ $availableTills->first()->id }}">
                        </div>
                    @elseif($availableTills->count() === 1 && $availableTills->first()->id === $till->id)
                        <div class="till-selection-card selected">
                            <strong>{{ $availableTills->first()->name }} ({{ $availableTills->first()->code }})</strong>
                            <small>Currently Assigned</small>
                            <input type="hidden" name="till_id" value="{{ $availableTills->first()->id }}">
                        </div>
                    @else
                        <div class="form-group">
                            <label>Select Till</label>
                            <select name="till_id" id="tillSelect" required class="form-select">
                                <option value="">-- Select a Till --</option>
                                @foreach($availableTills as $tillOption)
                                    <option value="{{ $tillOption->id }}">
                                        {{ $tillOption->name }} ({{ $tillOption->code }})
                                        @if($tillOption->location)
                                            - {{ $tillOption->location }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->hasPermissionTo('settings.access'))
                                <small>You can access any till with your admin privileges. This selection is for this session only.</small>
                            @else
                                <small>This will be your permanently assigned till. Choose carefully as it cannot be changed without admin assistance.</small>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="form-section">
                    <h3>Opening Balance</h3>
                    
                    <div class="balance-choice">
                        @if($hasPreviousClosure)
                            <label class="choice-box recommended">
                                <input type="radio" name="balance_option" value="previous" checked onchange="toggleManualBalance()">
                                <div class="choice-content">
                                    <div class="choice-title">Previous Closing Balance</div>
                                    <div class="choice-value">Rs. {{ number_format($userPreviousClosingBalance, 2) }}</div>
                                </div>
                            </label>
                        @endif
                        <label class="choice-box">
                            <input type="radio" name="balance_option" value="manual" @if(!$hasPreviousClosure) checked @endif onchange="toggleManualBalance()">
                            <div class="choice-content">
                                <div class="choice-title">Manual Count</div>
                                <div class="choice-subtitle">Count cash yourself</div>
                            </div>
                        </label>
                    </div>

                    <div class="form-group manual-balance-group" id="manualBalanceGroup">
                        <label>Enter the opening cash amount</label>
                        <input
                            type="number"
                            name="manual_balance"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            value="{{ $hasPreviousClosure ? '' : '0.00' }}"
                            class="form-input"
                            id="manualBalanceInput"
                            oninput="calculateVariance()"
                        >
                        <div id="varianceDisplay" class="variance-display hidden">
                            <div class="variance-label">Variance:</div>
                            <div class="variance-amount" id="varianceAmount">Rs. 0.00</div>
                        </div>
                    </div>
                </div>

                <div class="form-section manual-balance-group" id="varianceReasonGroup">
                    <h3>Reason for Variance <span class="required">*</span></h3>
                    <div class="form-group">
                        <label>Please explain why the opening amount differs from the expected amount</label>
                        <input
                            type="text"
                            name="variance_reason"
                            class="form-input"
                            id="varianceReasonInput"
                            placeholder="e.g., Cash counting error, Petty cash used, etc."
                        >
                    </div>
                </div>

                <div class="form-section">
                    <h3>Additional Notes</h3>
                    <textarea
                        name="notes"
                        rows="2"
                        placeholder="Add any notes about starting this till..."
                        class="form-textarea"
                    ></textarea>
                </div>

                <div class="form-actions">
                    <a href="{{ route('cashier.index') }}" class="btn-cancel">
                        Cancel
                    </a>
                    <button type="submit" class="btn-submit" id="openTillBtn">
                        Open Till
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<style>
.till-action-container {
    max-width: 1000px;
    margin: 20px auto;
    padding: 0 20px;
}

.till-action-header {
    margin-bottom: 20px;
}

.till-action-header h1 {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.till-action-header p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.till-action-content {
    display: grid;
    gap: 16px;
}

.till-info-card {
    background: white;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.till-details h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.shift-status-card {
    text-align: right;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
}

.status-badge.active {
    background: #dcfce7;
    color: #166534;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.shift-status-card p {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}

.balance-summary-card {
    background: white;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.balance-summary-card h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 12px 0;
}

.balance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}

.balance-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.balance-item.highlight {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.balance-item.expected {
    background: #fef3c7;
    border-color: #fcd34d;
}

.balance-item span {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.balance-item strong {
    font-size: 14px;
    color: #1e293b;
    font-weight: 700;
}

.balance-item.highlight strong,
.balance-item.expected strong {
    font-size: 16px;
    color: #166534;
}

.till-action-form {
    background: white;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-section {
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section h3 {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 12px 0;
}

.balance-choice {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 12px;
}

.choice-box {
    position: relative;
    cursor: pointer;
}

.choice-box input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.choice-content {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    text-align: center;
    transition: all 0.2s ease;
}

.choice-box input[type="radio"]:checked + .choice-content {
    border-color: #3b82f6;
    background: #eff6ff;
}

.choice-box.recommended input[type="radio"]:checked + .choice-content {
    border-color: #22c55e;
    background: #f0fdf4;
}

.choice-title {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.choice-value {
    font-size: 18px;
    font-weight: 700;
    color: #3b82f6;
}

.choice-box.recommended .choice-value {
    color: #22c55e;
}

.choice-subtitle {
    font-size: 12px;
    color: #64748b;
}

.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    background: #f8fafc;
    transition: all 0.2s ease;
    font-family: inherit;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.form-group small {
    display: block;
    font-size: 11px;
    color: #94a3b8;
    margin-top: 4px;
}

.manual-balance-group {
    display: none;
}

.manual-balance-group.show {
    display: block;
    animation: slideDown 0.3s ease;
}

.variance-display {
    margin-top: 12px;
    padding: 12px;
    border-radius: 8px;
    background: #fef3c7;
    border: 2px solid #f59e0b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.variance-display.hidden {
    display: none;
}

.variance-label {
    font-size: 13px;
    font-weight: 600;
    color: #92400e;
}

.variance-amount {
    font-size: 16px;
    font-weight: 900;
    color: #b45309;
}

.variance-amount.positive {
    color: #059669;
}

.variance-amount.negative {
    color: #dc2626;
}

.required {
    color: #dc2626;
    font-weight: 700;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 12px;
}

.btn-cancel {
    padding: 8px 16px;
    background: white;
    color: #64748b;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    text-decoration: none;
}

.btn-cancel:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.btn-submit {
    padding: 8px 16px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.btn-submit:hover {
    background: #2563eb;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    background: #fee2e2;
    border: 1px solid #fecaca;
}

.alert strong {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #991b1b;
    margin-bottom: 8px;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

.alert li {
    color: #dc2626;
    font-size: 12px;
    margin: 4px 0;
}

.till-selection-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 12px;
}

.till-selection-card.selected {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.till-selection-card strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
}

.till-selection-card small {
    font-size: 12px;
    color: #166534;
}

@media (max-width: 768px) {
    .till-action-container {
        padding: 0 16px;
    }

    .till-info-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .shift-status-card {
        text-align: left;
    }

    .balance-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-cancel,
    .btn-submit {
        width: 100%;
    }
}
</style>

<script>
function toggleManualBalance() {
    const balanceOption = document.querySelector('input[name="balance_option"]:checked')?.value;
    const manualBalanceGroup = document.getElementById('manualBalanceGroup');
    const manualBalanceInput = document.getElementById('manualBalanceInput');
    const varianceReasonGroup = document.getElementById('varianceReasonGroup');

    if (balanceOption === 'manual') {
        manualBalanceGroup.classList.add('show');
        varianceReasonGroup.classList.add('show');
        // Auto-focus the input after animation
        setTimeout(() => {
            manualBalanceInput?.focus();
        }, 300);
    } else {
        manualBalanceGroup.classList.remove('show');
        varianceReasonGroup.classList.remove('show');
        // Reset variance display
        document.getElementById('varianceDisplay').classList.add('hidden');
    }
}

function calculateVariance() {
    const manualBalanceInput = document.getElementById('manualBalanceInput');
    const varianceDisplay = document.getElementById('varianceDisplay');
    const varianceAmount = document.getElementById('varianceAmount');

    if (!manualBalanceInput) return;

    const expectedBalance = {{ $isOpen ? $expectedBalance : ($hasPreviousClosure ? $userPreviousClosingBalance : 0) }};

    const manualBalance = parseFloat(manualBalanceInput.value) || 0;
    const variance = manualBalance - expectedBalance;

    console.log('Variance calculation:', { manualBalance, expectedBalance, variance });

    if (manualBalance > 0 && Math.abs(variance) > 0.01) {
        varianceDisplay.classList.remove('hidden');
        varianceAmount.textContent = (variance > 0 ? '+' : '') + 'Rs. ' + Math.abs(variance).toFixed(2);
        varianceAmount.classList.remove('positive', 'negative');
        if (variance > 0) {
            varianceAmount.classList.add('positive');
        } else if (variance < 0) {
            varianceAmount.classList.add('negative');
        }
    } else {
        varianceDisplay.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleManualBalance();
});
</script>
@endsection
