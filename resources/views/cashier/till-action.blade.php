@extends('layouts.app')
@section('content')
<div class="till-action-container">
    <div class="till-action-header">
        <h1>{{ $isOpen ? 'Close Till' : 'Open Till' }}</h1>
        <p>{{ $isOpen ? 'End your working day by closing the till' : 'Start your working day by opening the till' }}</p>
    </div>

    <div class="till-action-card">
        <div class="till-info">
            <h2>{{ $till->name }}</h2>
            <p>Current Till</p>
        </div>

        @if($isOpen)
            <div class="shift-status">
                <span class="status-badge active">Till Open</span>
                <p class="status-text">Till opened at {{ $currentClosure->opened_at->format('g:i A') }}</p>
            </div>

            <div class="balance-summary">
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
                    <span>Bank Transfer Sales</span>
                    <strong>Rs. {{ number_format($summary['bank_transfer_sales'], 2) }}</strong>
                </div>

                <div class="balance-item">
                    <span>Total Sales</span>
                    <strong>Rs. {{ number_format($summary['total_sales'], 2) }}</strong>
                </div>

                <div class="balance-item">
                    <span>Cash In</span>
                    <strong>Rs. {{ number_format($summary['cash_in'], 2) }}</strong>
                </div>

                <div class="balance-item">
                    <span>Cash Out</span>
                    <strong>Rs. {{ number_format($summary['cash_out'], 2) }}</strong>
                </div>

                <div class="balance-item highlight">
                    <span>Expected Cash Balance</span>
                    <strong>Rs. {{ number_format($expectedBalance, 2) }}</strong>
                </div>
            </div>

            <form method="POST" action="{{ route('cashier.till-action') }}" class="till-action-form">
                @csrf

                <div class="form-group">
                    <label>Balance Option</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="balance_option" value="expected" checked onchange="toggleManualBalance()">
                            <span>Use Expected Balance (Rs. {{ number_format($expectedBalance, 2) }})</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="balance_option" value="manual" onchange="toggleManualBalance()">
                            <span>Enter Manual Amount</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="manualBalanceGroup" style="display: none;">
                    <label>Manual Balance</label>
                    <input
                        type="number"
                        name="manual_balance"
                        step="0.01"
                        min="0"
                        placeholder="Enter the actual cash counted"
                    >
                    <small>Count the cash in the till and enter the amount</small>
                </div>

                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea
                        name="notes"
                        rows="3"
                        placeholder="Any notes about this till closure..."
                    ></textarea>
                </div>

                <div class="denomination-breakdown">
                    <label>Denomination Breakdown (Optional)</label>
                    <div class="denomination-grid">
                        <div class="denomination-item">
                            <label>2000 x</label>
                            <input type="number" name="denomination_breakdown[2000]" min="0" value="0">
                        </div>
                        <div class="denomination-item">
                            <label>500 x</label>
                            <input type="number" name="denomination_breakdown[500]" min="0" value="0">
                        </div>
                        <div class="denomination-item">
                            <label>200 x</label>
                            <input type="number" name="denomination_breakdown[200]" min="0" value="0">
                        </div>
                        <div class="denomination-item">
                            <label>100 x</label>
                            <input type="number" name="denomination_breakdown[100]" min="0" value="0">
                        </div>
                        <div class="denomination-item">
                            <label>50 x</label>
                            <input type="number" name="denomination_breakdown[50]" min="0" value="0">
                        </div>
                        <div class="denomination-item">
                            <label>20 x</label>
                            <input type="number" name="denomination_breakdown[20]" min="0" value="0">
                        </div>
                        <div class="denomination-item">
                            <label>10 x</label>
                            <input type="number" name="denomination_breakdown[10]" min="0" value="0">
                        </div>
                        <div class="denomination-item">
                            <label>5 x</label>
                            <input type="number" name="denomination_breakdown[5]" min="0" value="0">
                        </div>
                        <div class="denomination-item">
                            <label>Coins</label>
                            <input type="number" name="denomination_breakdown[coins]" step="0.01" min="0" value="0">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="expected_balance" value="{{ $expectedBalance }}">

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        Close Till
                    </button>
                    <a href="{{ route('cashier.index') }}" class="btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        @else
            <div class="shift-status">
                <span class="status-badge inactive">Till Closed</span>
                <p class="status-text">No till is currently open</p>
            </div>

            <form method="POST" action="{{ route('cashier.till-action.post') }}" class="till-action-form" onsubmit="console.log('Form submitting'); return true;">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($hasPermanentTill)
                    <div class="form-group">
                        <label>Your Assigned Till</label>
                        <div class="permanent-till-info">
                            <strong>{{ $availableTills->first()->name }} ({{ $availableTills->first()->code }})</strong>
                            <small>This is your permanently assigned till</small>
                        </div>
                        <input type="hidden" name="till_id" value="{{ $availableTills->first()->id }}">
                    </div>
                @elseif($availableTills->count() === 1 && $availableTills->first()->id === $till->id)
                    <div class="form-group">
                        <label>Your Current Till</label>
                        <div class="permanent-till-info">
                            <strong>{{ $availableTills->first()->name }} ({{ $availableTills->first()->code }})</strong>
                            <small>This till is currently assigned to you</small>
                        </div>
                        <input type="hidden" name="till_id" value="{{ $availableTills->first()->id }}">
                    </div>
                @else
                    <div class="form-group">
                        <label>Select Till</label>
                        <select name="till_id" id="tillSelect" required>
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

                <div class="form-group">
                    <label>Opening Balance Option</label>
                    <div class="radio-group">
                        @if($hasPreviousClosure)
                            <label class="radio-option">
                                <input type="radio" name="balance_option" value="previous" checked onchange="toggleManualBalance()">
                                <span>Use Previous Closing Balance (Rs. {{ number_format($userPreviousClosingBalance, 2) }})</span>
                            </label>
                        @endif
                        <label class="radio-option">
                            <input type="radio" name="balance_option" value="manual" @if(!$hasPreviousClosure) checked @endif onchange="toggleManualBalance()">
                            <span>Enter Manual Amount</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="manualBalanceGroup" style="display: {{ $hasPreviousClosure ? 'none' : 'block' }};">
                    <label>Manual Opening Balance</label>
                    <input
                        type="number"
                        name="manual_balance"
                        step="0.01"
                        min="0"
                        placeholder="Enter the opening balance"
                        value="{{ $hasPreviousClosure ? '' : '0.00' }}"
                    >
                    <small>Count the cash in the till and enter the opening amount</small>
                </div>

                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea
                        name="notes"
                        rows="3"
                        placeholder="Any notes about starting this till..."
                    ></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" id="openTillBtn">
                        Open Till
                    </button>
                    <a href="{{ route('cashier.index') }}" class="btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>

<style>
.till-action-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.till-action-header {
    margin-bottom: 24px;
}

.till-action-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.till-action-header p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.till-action-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 32px;
}

.till-info {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.till-info h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.till-info p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.shift-status {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.status-badge {
    display: inline-block;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 999px;
    margin-bottom: 8px;
}

.status-badge.active {
    background: #dcfce7;
    color: #166534;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.status-text {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.balance-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.balance-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.balance-item.highlight {
    background: #f0fdf4;
    border-radius: 8px;
    padding: 12px;
}

.balance-item span {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.balance-item strong {
    font-size: 16px;
    color: #1e293b;
    font-weight: 700;
}

.balance-item.highlight strong {
    color: #15803d;
    font-size: 18px;
}

.till-action-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.radio-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.radio-option:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.radio-option input[type="radio"] {
    cursor: pointer;
}

.radio-option span {
    font-size: 14px;
    color: #334155;
}

.radio-option.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.radio-option.disabled:hover {
    background: #f8fafc;
    border-color: #e5e7eb;
}

.warning-text {
    color: #dc2626;
    font-size: 12px;
    font-weight: 500;
    display: block;
    margin-top: 4px;
}

.form-group input,
.form-group textarea {
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group small {
    font-size: 12px;
    color: #94a3b8;
}

.denomination-breakdown {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.denomination-breakdown > label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.denomination-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
}

.denomination-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.denomination-item label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.denomination-item input {
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.2s ease;
}

.denomination-item input:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 8px;
}

.btn-primary {
    padding: 12px 24px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    padding: 12px 24px;
    background: white;
    color: #64748b;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}

.btn-secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert-danger ul {
    margin: 0;
    padding-left: 20px;
}

.alert-danger li {
    margin: 4px 0;
}

.permanent-till-info {
    padding: 12px 16px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.permanent-till-info strong {
    font-size: 14px;
    color: #166534;
}

.permanent-till-info small {
    font-size: 12px;
    color: #15803d;
}
</style>

<script>
function toggleManualBalance() {
    const balanceOption = document.querySelector('input[name="balance_option"]:checked').value;
    const manualBalanceGroup = document.getElementById('manualBalanceGroup');

    if (balanceOption === 'manual') {
        manualBalanceGroup.style.display = 'block';
    } else {
        manualBalanceGroup.style.display = 'none';
    }
}

// Test button click
document.addEventListener('DOMContentLoaded', function() {
    const openTillBtn = document.getElementById('openTillBtn');
    if (openTillBtn) {
        openTillBtn.addEventListener('click', function(e) {
            console.log('Button clicked!');
            console.log('Event:', e);
        });
    }
});
</script>
@endsection