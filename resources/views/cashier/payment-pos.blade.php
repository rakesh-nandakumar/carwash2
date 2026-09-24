@extends('layouts.app')
@section('content')
<div class="payment-page">
    <div class="payment-header">
        <div class="header-content">
            <h1>Payment Processing</h1>
            <p>POS Sale #{{ $invoice->invoice_number }} · {{ $invoice->customer->full_name ?? 'Walk-in Customer' }}</p>
        </div>
    </div>

    <div class="payment-content">
        {{-- Invoice Details --}}
        <div class="glass-card job-details-card">
            <div class="card-header">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h2>Invoice Details</h2>
            </div>
            <div class="detail-row">
                <span class="label">Invoice Number</span>
                <span class="value">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Date</span>
                <span class="value">{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Customer</span>
                <span class="value">{{ $invoice->customer->full_name ?? 'Walk-in Customer' }}</span>
            </div>
            @if($invoice->customer)
            <div class="detail-row">
                <span class="label">Phone</span>
                <span class="value">{{ $invoice->customer->phone ?? 'N/A' }}</span>
            </div>
            @endif
        </div>

        {{-- Products --}}
        <div class="glass-card services-card">
            <div class="card-header">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h2>Products</h2>
            </div>
            @forelse($invoice->items as $item)
                <div class="service-item">
                    <span class="service-name">{{ $item->description }}</span>
                    <span class="service-price">Rs. {{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}</span>
                </div>
            @empty
                <p class="empty-state">No items.</p>
            @endforelse
        </div>

        {{-- Payment Summary --}}
        <div class="glass-card payment-card">
            <div class="card-header">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <h2>Payment Summary</h2>
            </div>

            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rs. {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Tax</span>
                <span>Rs. {{ number_format($invoice->tax, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Discount</span>
                <span>Rs. {{ number_format($invoice->discount, 2) }}</span>
            </div>
            <div class="summary-row total-row highlighted">
                <span>Total Due</span>
                <span class="total-amount">Rs. {{ number_format($invoice->total, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Paid</span>
                <span>Rs. {{ number_format($invoice->paid, 2) }}</span>
            </div>
            <div class="summary-row total-row highlighted">
                <span>Balance Due</span>
                <span class="total-amount">Rs. {{ number_format($invoice->balance, 2) }}</span>
            </div>

            <form method="post" action="{{ route('cashier.process-payment-invoice', $invoice) }}" id="paymentForm" onsubmit="handlePaymentSubmit(event)">
                @csrf

                <div class="form-section">
                    <div class="payment-method-header">
                        <label style="margin: 0; font-size: 15px; font-weight: 600; color: #475569;">Payment Method</label>
                        <label class="split-toggle">
                            <input type="checkbox" id="splitPaymentToggle" onchange="toggleSplitPayment()">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Enable Split Payment</span>
                        </label>
                    </div>

                    <div id="singlePaymentSection">
                        <div class="payment-methods">
                            <label class="payment-method-option">
                                <input type="radio" name="payment_method" value="cash" checked onchange="toggleReferenceField()">
                                <span class="method-icon">💵</span>
                                <span class="method-label">Cash</span>
                            </label>
                            <label class="payment-method-option">
                                <input type="radio" name="payment_method" value="card" onchange="toggleReferenceField()">
                                <span class="method-icon">💳</span>
                                <span class="method-label">Card</span>
                            </label>
                            <label class="payment-method-option">
                                <input type="radio" name="payment_method" value="upi" onchange="toggleReferenceField()">
                                <span class="method-icon">📱</span>
                                <span class="method-label">UPI</span>
                            </label>
                            <label class="payment-method-option">
                                <input type="radio" name="payment_method" value="bank_transfer" onchange="toggleReferenceField()">
                                <span class="method-icon">🏦</span>
                                <span class="method-label">Bank Transfer</span>
                            </label>
                            <label class="payment-method-option">
                                <input type="radio" name="payment_method" value="cheque" onchange="toggleReferenceField()">
                                <span class="method-icon">📄</span>
                                <span class="method-label">Cheque</span>
                            </label>
                        </div>
                    </div>

                    <div id="splitPaymentSection" style="display: none;">
                        <div id="paymentRows">
                            <div class="payment-row" data-row="0">
                                <select class="split-method" onchange="updateSplitReference(this)">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                                <input type="number" step=".01" class="split-amount" placeholder="0.00" oninput="calculateSplitTotal()">
                                <input type="text" class="split-reference" placeholder="Reference (if needed)" style="display: none;">
                                <button type="button" class="remove-row-btn" onclick="removePaymentRow(this)" style="display: none;">×</button>
                            </div>
                        </div>
                        <button type="button" class="add-row-btn" onclick="addPaymentRow()">+ Add Payment Method</button>
                        <div class="split-summary">
                            <span>Total Split: <strong id="splitTotal">Rs. 0.00</strong></span>
                            <span>Remaining: <strong id="splitRemaining">Rs. {{ number_format($invoice->balance, 2) }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="form-section" id="referenceField" style="display: none;">
                    <label id="referenceLabel">Reference Number</label>
                    <input type="text" name="reference_number" id="referenceNumber" placeholder="Enter reference number">
                </div>

                <div class="form-section" id="chequeFields" style="display: none;">
                    <label>Cheque Number</label>
                    <input type="text" name="cheque_number" id="chequeNumber" placeholder="Enter cheque number">
                </div>

                <div class="form-section" id="bankField" style="display: none;">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" id="bankName" placeholder="Enter bank name">
                </div>

                <div class="form-section" id="chequeDateField" style="display: none;">
                    <label>Cheque Due Date</label>
                    <input type="date" name="cheque_due_date" id="chequeDueDate">
                </div>

                <div class="form-section" id="paymentReceivedField" style="display: none;">
                    <label>Payment Received Confirmation</label>
                    <div class="payment-received-options">
                        <label class="radio-option">
                            <input type="radio" name="payment_received" value="yes" onchange="togglePaymentReceived()">
                            <span>Yes - Payment received and cleared</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="payment_received" value="no" checked onchange="togglePaymentReceived()">
                            <span>No - Payment pending (cheque not yet cleared)</span>
                        </label>
                    </div>
                </div>

                <div class="form-section coupon-section">
                    <label>Coupon/Voucher Code</label>
                    <div class="coupon-input-wrapper">
                        <input type="text" name="coupon_code" id="couponCode" placeholder="Enter coupon code">
                        <button type="button" class="apply-coupon-btn" onclick="applyCoupon()">Apply</button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-section">
                        <label>Discount Method</label>
                        <select
                            id="discountType"
                            name="discount_type"
                            onchange="toggleDiscountOptions()"
                        >
                            <option value="none">No Discount</option>
                            <option value="amount">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>

                    <div class="form-section" id="applyToSection" style="display: none;">
                        <label>Apply Discount To</label>
                        <select
                            id="discountApplyTo"
                            name="discount_apply_to"
                            onchange="toggleDiscountSection()"
                        >
                            <option value="total">Total Amount</option>
                            <option value="individual_items">Individual Items</option>
                        </select>
                    </div>
                </div>

                <div class="form-section" id="globalDiscountSection" style="display: none;">
                    <label id="discountValueLabel">Discount Amount</label>
                    <div class="discount-input-wrapper">
                        <input
                            type="number"
                            step=".01"
                            min="0"
                            id="discountValue"
                            name="discount_value"
                            placeholder="0.00"
                            oninput="calculateTotal()"
                        >
                        <span id="discountValueSuffix" class="input-suffix">%</span>
                    </div>
                </div>

                <div class="form-section" id="individualItemsSection" style="display: none;">
                    <label>Individual Item Discounts</label>
                    @foreach($invoice->items as $item)
                        <div class="individual-discount-row">
                            <span class="item-name">
                                {{ $item->description }}
                                (Rs. {{ number_format($item->unit_price * $item->quantity, 2) }})
                            </span>
                            <div class="discount-inputs">
                                <input type="number" step=".01" min="0" class="item-discount-value"
                                    data-item-type="item" data-item-id="{{ $item->id }}"
                                    data-item-price="{{ $item->unit_price * $item->quantity }}"
                                    name="individual_item_discounts[{{ $item->id }}]"
                                    placeholder="0.00" oninput="calculateIndividualDiscounts()">
                                <span class="individual-discount-suffix">Rs.</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-section amount-section">
                    <label>Amount Received</label>
                    <div class="amount-input-wrapper">
                        <span class="currency-symbol">Rs.</span>
                        <input type="number" step=".01" name="amount_received" id="amountReceived" placeholder="0.00" required oninput="calculateBalance()" onwheel="this.blur()">
                    </div>
                    <small class="input-hint">Enter the amount received from customer</small>
                </div>

                <div class="balance-card" id="balanceDisplay" style="display: none;">
                    <div class="balance-content">
                        <span id="balanceLabel">Balance to Return</span>
                        <span id="balanceAmount">Rs. 0.00</span>
                    </div>
                </div>

                <div class="live-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>Rs. {{ number_format($invoice->subtotal, 2) }}</strong>
                    </div>
                    <div class="summary-row" id="discountRow">
                        <span>Discount</span>
                        <strong id="displayDiscount">Rs. 0.00</strong>
                    </div>
                    <div class="summary-row">
                        <span>Total Due</span>
                        <strong id="displayTotal" class="highlighted-amount">Rs. {{ number_format($invoice->balance, 2) }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Amount Received</span>
                        <strong id="displayReceived" class="received-amount">Rs. 0.00</strong>
                    </div>
                    <div class="summary-row final-row">
                        <span>Balance</span>
                        <strong id="displayBalance">Rs. {{ number_format($invoice->balance, 2) }}</strong>
                    </div>
                </div>

                <button type="button" class="process-button" onclick="openPaymentConfirmation()">
                    <span>Process Payment + Send WhatsApp Message</span>
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>

                {{-- Back button below Process Payment --}}
                <div class="payment-back">
                    <a href="{{ route('cashier.index') }}" class="back-button">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Confirmation Modal -->
<div id="paymentConfirmationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Payment</h2>
            <button class="close-btn" onclick="closePaymentConfirmation()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="payment-summary">
                <div class="summary-row">
                    <span>Total Due</span>
                    <span class="amount" id="confirmTotalDue">Rs. 0.00</span>
                </div>
                <div class="summary-row">
                    <span>Payment Method</span>
                    <span id="confirmPaymentMethod">-</span>
                </div>
                <div class="summary-row editable">
                    <span>Amount Received</span>
                    <div class="amount-edit-wrapper">
                        <span class="currency-prefix">Rs.</span>
                        <input type="number" id="editAmountReceived" step="0.01" value="0.00" oninput="updateConfirmBalance()" onfocus="this.select()" placeholder="Enter amount">
                    </div>
                </div>
                <div class="summary-row balance-row">
                    <span>Balance</span>
                    <span id="confirmBalance">Rs. 0.00</span>
                </div>
            </div>
            <p class="confirmation-text">⚠️ Are you sure you want to process this payment?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary" onclick="closePaymentConfirmation()">Cancel</button>
            <button type="button" class="primary" onclick="submitPayment()">Confirm Payment — Rs. <span id="confirmButtonAmount">0.00</span></button>
        </div>
    </div>
</div>

<style>
.payment-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 0;
}

.payment-header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 32px 40px;
}

.header-content h1 {
    color: #1e293b;
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.header-content p {
    color: #64748b;
    font-size: 16px;
    margin: 0;
}

/* ===== MAIN GRID (DESKTOP) ===== */
.payment-content {
    padding: 40px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    max-width: 1400px;
    margin: 0 auto;
}

.payment-card {
    grid-column: 1 / -1;
}

.glass-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f1f5f9;
}

.card-header svg {
    color: #64748b;
}

.card-header h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
}

.detail-row .value {
    color: #1e293b;
    font-size: 15px;
    font-weight: 600;
    text-align: right;
}

.priority-badge {
    background: #f1f5f9;
    color: #475569;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.service-item, .part-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    margin-bottom: 10px;
    gap: 12px;
}

.service-name, .part-name {
    color: #475569;
    font-size: 15px;
    font-weight: 500;
}

.service-price, .part-price {
    color: #475569;
    font-size: 15px;
    font-weight: 600;
    white-space: nowrap;
}

.empty-state {
    text-align: center;
    color: #94a3b8;
    font-size: 15px;
    padding: 20px;
    margin: 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
}

.total-row {
    padding: 18px 0;
    border-bottom: 2px solid #e2e8f0;
}

.total-row.highlighted {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    margin: 16px 0 0 0;
    border: 2px solid #e2e8f0;
}

.total-amount {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}

.form-section {
    margin-bottom: 20px;
}

.form-section label {
    display: block;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-section input,
.form-section select {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    background: #f8fafc;
    color: #1e293b;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.form-section input:focus,
.form-section select:focus {
    outline: none;
    border-color: #94a3b8;
    background: white;
    box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.1);
}

.amount-section {
    position: relative;
}

.amount-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.currency-symbol {
    position: absolute;
    left: 16px;
    font-size: 16px;
    font-weight: 600;
    color: #64748b;
    pointer-events: none;
}

.amount-section input {
    padding-left: 50px;
    border: 2px solid #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
}

.amount-section input:focus {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.input-hint {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.discount-input-wrapper {
    position: relative;
}

.input-suffix {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-weight: 600;
    display: none;
}

#discountValue {
    padding-right: 40px;
}

#discountValue:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.payment-method-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    flex-wrap: nowrap;
}

.payment-method-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 12px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-method-option:hover {
    border-color: #cbd5e1;
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.payment-method-option input:checked + .method-icon,
.payment-method-option input:checked + .method-label {
    color: #3b82f6;
}

.payment-method-option input {
    display: none;
}

.method-icon {
    font-size: 28px;
    margin-bottom: 8px;
}

.method-label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.split-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.split-toggle input {
    display: none;
}

.toggle-slider {
    width: 44px;
    height: 24px;
    background: #cbd5e1;
    border-radius: 12px;
    position: relative;
    transition: background 0.3s;
}

.split-toggle input:checked + .toggle-slider {
    background: #3b82f6;
}

.toggle-slider::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    transition: transform 0.3s;
}

.split-toggle input:checked + .toggle-slider::after {
    transform: translateX(20px);
}

.toggle-label {
    font-size: 14px;
    color: #475569;
    font-weight: 500;
}

.payment-row {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}

.payment-row select, .payment-row input {
    flex: 1;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
}

.add-row-btn {
    width: 100%;
    padding: 12px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    margin-top: 8px;
}

.remove-row-btn {
    padding: 8px 12px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.split-summary {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
    margin-top: 12px;
    border: 2px solid #e2e8f0;
}

.balance-card {
    background: #fef3c7;
    border: 2px solid #fcd34d;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.balance-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.balance-content span:first-child {
    font-weight: 600;
    color: #92400e;
}

.balance-content span:last-child {
    font-size: 20px;
    font-weight: 700;
    color: #92400e;
}

.live-summary {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 2px solid #e2e8f0;
}

.live-summary .summary-row {
    border-bottom: 1px solid #e2e8f0;
}

.live-summary .summary-row:last-child {
    border-bottom: none;
}

.highlighted-amount {
    color: #3b82f6;
    font-size: 18px;
}

.received-amount {
    color: #10b981;
}

.final-row {
    background: #f1f5f9;
    padding: 16px;
    border-radius: 10px;
    margin: 16px -20px -20px -20px;
    border: 2px solid #e2e8f0;
}

.process-button {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.process-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.payment-back {
    margin-top: 16px;
}

.back-button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: white;
    color: #64748b;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.back-button:hover {
    background: #f8fafc;
    color: #1e293b;
    border-color: #cbd5e1;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(15px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    max-width: 480px;
    width: 90%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 28px;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: -0.025em;
}

.close-btn {
    background: #f1f5f9;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #64748b;
    padding: 0;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
    font-weight: 300;
}

.close-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
    transform: rotate(90deg);
}

.modal-body {
    padding: 28px;
    text-align: left;
}

.modal-body p {
    margin: 0;
    color: #64748b;
    font-size: 15px;
    line-height: 1.6;
}

.payment-summary {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
    font-size: 15px;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row span:first-child {
    color: #64748b;
    font-weight: 500;
    font-size: 14px;
}

.summary-row span:last-child {
    color: #1e293b;
    font-weight: 600;
    font-size: 15px;
}

.summary-row .amount {
    font-size: 20px;
    color: #059669;
    font-weight: 700;
}

.summary-row.balance-row span:last-child {
    color: #dc2626;
    font-weight: 700;
    font-size: 20px;
}

.summary-row.editable {
    padding: 18px 0;
}

.summary-row.editable input {
    padding: 10px 14px;
    border: 2px solid #3b82f6;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    text-align: right;
    width: 160px;
    background: white;
    transition: all 0.2s ease;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.summary-row.editable input:focus {
    outline: none;
    border-color: #2563eb;
    background: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.amount-edit-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
}

.amount-edit-wrapper .currency-prefix {
    color: #2563eb;
    font-weight: 700;
    font-size: 16px;
}

.confirmation-text {
    color: #64748b;
    font-size: 15px;
    margin-bottom: 20px;
    padding: 16px;
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
    font-weight: 500;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 24px 28px;
    border-top: 1px solid #f1f5f9;
    background: #f8fafc;
}

.modal-footer button {
    padding: 14px 28px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.modal-footer button.secondary {
    background: white;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.modal-footer button.secondary:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.modal-footer button.primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.modal-footer button.primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.coupon-section {
    margin-bottom: 20px;
}

.coupon-input-wrapper {
    display: flex;
    gap: 8px;
}

.coupon-input-wrapper input {
    flex: 1;
}

.apply-coupon-btn {
    padding: 14px 24px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.apply-coupon-btn:hover {
    background: #2563eb;
}

.individual-discount-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    padding: 14px 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
}

.item-name {
    flex: 1;
    font-weight: 500;
    color: #475569;
    font-size: 15px;
}

.discount-inputs {
    position: relative;
    display: flex;
    align-items: center;
}

.individual-discount-suffix {
    position: absolute;
    right: 16px;
    color: #64748b;
    font-size: 14px;
    font-weight: 600;
}

.item-discount-value {
    width: 100px;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
}

.payment-received-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
}

.radio-option input {
    width: 18px;
    height: 18px;
}

.radio-option span {
    font-size: 14px;
    color: #475569;
}
</style>

<script>
const totalDue = {{ $invoice->balance }};

function toggleReferenceField() {
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    const referenceField = document.getElementById('referenceField');
    const chequeFields = document.getElementById('chequeFields');
    const bankField = document.getElementById('bankField');
    const chequeDateField = document.getElementById('chequeDateField');
    const paymentReceivedField = document.getElementById('paymentReceivedField');

    referenceField.style.display = 'none';
    chequeFields.style.display = 'none';
    bankField.style.display = 'none';
    chequeDateField.style.display = 'none';
    paymentReceivedField.style.display = 'none';

    if (method === 'card' || method === 'upi' || method === 'bank_transfer') {
        referenceField.style.display = 'block';
    } else if (method === 'cheque') {
        chequeFields.style.display = 'block';
        bankField.style.display = 'block';
        chequeDateField.style.display = 'block';
        paymentReceivedField.style.display = 'block';
    }
}

function toggleDiscountOptions() {
    const discountType = document.getElementById('discountType').value;
    const applyToSection = document.getElementById('applyToSection');
    const globalDiscountSection = document.getElementById('globalDiscountSection');
    const individualItemsSection = document.getElementById('individualItemsSection');

    applyToSection.style.display = discountType === 'none' ? 'none' : 'block';
    globalDiscountSection.style.display = 'none';
    individualItemsSection.style.display = 'none';

    if (discountType !== 'none') {
        toggleDiscountSection();
    }
}

function toggleDiscountSection() {
    const discountApplyTo = document.getElementById('discountApplyTo').value;
    const globalDiscountSection = document.getElementById('globalDiscountSection');
    const individualItemsSection = document.getElementById('individualItemsSection');

    globalDiscountSection.style.display = discountApplyTo === 'total' ? 'block' : 'none';
    individualItemsSection.style.display = discountApplyTo === 'individual_items' ? 'block' : 'none';

    // Update suffix based on discount type
    const discountType = document.getElementById('discountType').value;
    const suffix = document.getElementById('discountValueSuffix');
    const label = document.getElementById('discountValueLabel');
    if (discountType === 'percentage') {
        suffix.style.display = 'block';
        suffix.textContent = '%';
        label.textContent = 'Discount Percentage';
    } else {
        suffix.style.display = 'block';
        suffix.textContent = 'Rs.';
        label.textContent = 'Discount Amount';
    }
}

function calculateTotal() {
    // Calculate and update total based on discount
    const discountType = document.getElementById('discountType').value;
    const discountValue = parseFloat(document.getElementById('discountValue').value) || 0;
    const subtotal = {{ $invoice->subtotal }};
    let discount = 0;

    if (discountType === 'percentage') {
        discount = subtotal * (discountValue / 100);
    } else {
        discount = discountValue;
    }

    const total = subtotal - discount;
    document.getElementById('displayTotal').textContent = 'Rs. ' + total.toFixed(2);
    document.getElementById('displayDiscount').textContent = 'Rs. ' + discount.toFixed(2);
    calculateBalance();
}

function calculateIndividualDiscounts() {
    let totalDiscount = 0;
    const inputs = document.querySelectorAll('.item-discount-value');
    inputs.forEach(input => {
        totalDiscount += parseFloat(input.value) || 0;
    });

    const subtotal = {{ $invoice->subtotal }};
    const total = subtotal - totalDiscount;
    document.getElementById('displayTotal').textContent = 'Rs. ' + total.toFixed(2);
    document.getElementById('displayDiscount').textContent = 'Rs. ' + totalDiscount.toFixed(2);
    calculateBalance();
}

function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value;
    if (couponCode) {
        alert('Coupon applied: ' + couponCode);
    } else {
        alert('Please enter a coupon code');
    }
}

function toggleSplitPayment() {
    const enabled = document.getElementById('splitPaymentToggle').checked;
    document.getElementById('singlePaymentSection').style.display = enabled ? 'none' : 'block';
    document.getElementById('splitPaymentSection').style.display = enabled ? 'block' : 'none';
}

function addPaymentRow() {
    const rows = document.querySelectorAll('.payment-row');
    const newRow = rows[0].cloneNode(true);
    newRow.setAttribute('data-row', rows.length);
    newRow.querySelector('.remove-row-btn').style.display = 'block';
    newRow.querySelector('.split-amount').value = '';
    document.getElementById('paymentRows').appendChild(newRow);
}

function removePaymentRow(btn) {
    btn.parentElement.remove();
    calculateSplitTotal();
}

function calculateSplitTotal() {
    const amounts = document.querySelectorAll('.split-amount');
    let total = 0;
    amounts.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('splitTotal').textContent = 'Rs. ' + total.toFixed(2);
    document.getElementById('splitRemaining').textContent = 'Rs. ' + Math.max(0, totalDue - total).toFixed(2);
}

function calculateBalance() {
    const received = parseFloat(document.getElementById('amountReceived').value) || 0;
    // Get the current total due from the display (which includes discounts)
    const currentTotalText = document.getElementById('displayTotal').textContent;
    const currentTotal = parseFloat(currentTotalText.replace('Rs. ', '')) || 0;
    const balance = received - currentTotal;
    const balanceDisplay = document.getElementById('balanceDisplay');
    const balanceLabel = document.getElementById('balanceLabel');
    const balanceAmount = document.getElementById('balanceAmount');

    if (received > 0) {
        balanceDisplay.style.display = 'block';
        if (balance >= 0) {
            balanceLabel.textContent = 'Balance to Return';
            balanceAmount.textContent = 'Rs. ' + balance.toFixed(2);
            balanceAmount.style.color = '#10b981';
        } else {
            balanceLabel.textContent = 'Remaining Balance';
            balanceAmount.textContent = 'Rs. ' + Math.abs(balance).toFixed(2);
            balanceAmount.style.color = '#dc2626';
        }
    } else {
        balanceDisplay.style.display = 'none';
    }

    document.getElementById('displayReceived').textContent = 'Rs. ' + received.toFixed(2);
    document.getElementById('displayBalance').textContent = 'Rs. ' + Math.abs(balance).toFixed(2);
}

function openPaymentConfirmation() {
    const modal = document.getElementById('paymentConfirmationModal');
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    let amountReceived = parseFloat(document.getElementById('amountReceived').value) || 0;
    const totalDue = parseFloat(document.getElementById('displayTotal').textContent.replace('Rs. ', '')) || 0;

    // If amount received is 0 or empty, default to total due
    if (amountReceived === 0) {
        amountReceived = totalDue;
    }

    document.getElementById('confirmTotalDue').textContent = 'Rs. ' + totalDue.toFixed(2);
    document.getElementById('confirmPaymentMethod').textContent = method.charAt(0).toUpperCase() + method.slice(1);
    document.getElementById('editAmountReceived').value = amountReceived.toFixed(2);
    document.getElementById('confirmButtonAmount').textContent = amountReceived.toFixed(2);

    updateConfirmBalance();
    modal.style.display = 'flex';
}

function updateConfirmBalance() {
    const totalDue = parseFloat(document.getElementById('confirmTotalDue').textContent.replace('Rs. ', '')) || 0;
    const received = parseFloat(document.getElementById('editAmountReceived').value) || 0;
    const balance = totalDue - received;

    const balanceElement = document.getElementById('confirmBalance');
    balanceElement.textContent = 'Rs. ' + Math.abs(balance).toFixed(2);

    if (balance >= 0) {
        balanceElement.style.color = '#dc2626';
    } else {
        balanceElement.style.color = '#10b981';
    }

    document.getElementById('confirmButtonAmount').textContent = received.toFixed(2);
}

function closePaymentConfirmation() {
    document.getElementById('paymentConfirmationModal').style.display = 'none';
}

function submitPayment() {
    document.getElementById('paymentForm').submit();
}

function handlePaymentSubmit(e) {
    e.preventDefault();
    openPaymentConfirmation();
}

document.addEventListener('DOMContentLoaded', function() {
    calculateBalance();
});
</script>
@endsection