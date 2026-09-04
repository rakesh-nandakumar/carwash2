@extends('layouts.app')
@section('content')
<div class="payment-page">
    <div class="payment-header">
        <div class="header-content">
            <h1>Payment Processing</h1>
            <p>{{ $job->vehicle->registration_number }} · {{ $job->customer->full_name }}</p>
        </div>
    </div>

    <div class="payment-content">
        {{-- Job Details --}}
        <div class="glass-card job-details-card">
            <div class="card-header">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h2>Job Details</h2>
            </div>
            <div class="detail-row">
                <span class="label">Job Number</span>
                <span class="value">{{ $job->job_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Vehicle</span>
                <span class="value">{{ $job->vehicle->registration_number }} · {{ $job->vehicle->make }} {{ $job->vehicle->model }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Customer</span>
                <span class="value">{{ $job->customer->full_name }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Priority</span>
                <span class="value priority-badge">{{ ucfirst($job->priority) }}</span>
            </div>
        </div>

        {{-- Services --}}
        <div class="glass-card services-card">
            <div class="card-header">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h2>Services</h2>
            </div>
            @forelse($job->services as $service)
                <div class="service-item">
                    <span class="service-name">{{ $service->name_snapshot }}</span>
                    <span class="service-price">Rs. {{ number_format($service->unit_price, 2) }} × {{ $service->quantity }}</span>
                </div>
            @empty
                <p class="empty-state">No services.</p>
            @endforelse
        </div>

        {{-- Parts Used --}}
        <div class="glass-card parts-card">
            <div class="card-header">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h2>Parts Used</h2>
            </div>
            @forelse($job->parts as $part)
                <div class="part-item">
                    <span class="part-name">{{ $part->product->name }}</span>
                    <span class="part-price">{{ $part->quantity }} × Rs. {{ number_format($part->unit_price, 2) }}</span>
                </div>
            @empty
                <p class="empty-state">No parts used.</p>
            @endforelse
        </div>

        {{-- Payment Summary --}}
        @if($job->invoice)
        <div class="glass-card payment-card">
            <div class="card-header">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <h2>Payment Summary</h2>
            </div>

            <div class="summary-row">
                <span>Services Total</span>
                <span>Rs. {{ number_format($calculation['services_total'], 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Parts Total</span>
                <span>Rs. {{ number_format($calculation['parts_total'], 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rs. {{ number_format($calculation['subtotal'], 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Tax</span>
                <span>Rs. {{ number_format($calculation['tax'], 2) }}</span>
            </div>
            <div class="summary-row total-row">
                <span>Total Due</span>
                <span class="total-amount">Rs. {{ number_format($calculation['total'], 2) }}</span>
            </div>

            <form method="post" action="{{ route('cashier.process-payment', $job) }}" id="paymentForm" onsubmit="updateHiddenFieldsBeforeSubmit()">
                @csrf

                <div class="form-section">
                    <div class="payment-method-header">
                        <label style="margin: 0; font-size: 16px; font-weight: 600; color: #1e293b;">Payment Method</label>
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
                                </select>
                                <input type="number" step=".01" class="split-amount" placeholder="Amount" oninput="calculateSplitTotal()">
                                <input type="text" class="split-reference" placeholder="Reference (if needed)" style="display: none;">
                                <button type="button" class="remove-row-btn" onclick="removePaymentRow(this)" style="display: none;">×</button>
                            </div>
                        </div>
                        <button type="button" class="add-row-btn" onclick="addPaymentRow()">+ Add Payment Method</button>
                        <div class="split-summary">
                            <span>Total Split: <strong id="splitTotal">Rs. 0.00</strong></span>
                            <span>Remaining: <strong id="splitRemaining">Rs. {{ number_format($calculation['total'], 2) }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="form-section" id="referenceField" style="display: none;">
                    <label id="referenceLabel">Reference Number</label>
                    <input type="text" name="reference_number" id="referenceNumber" placeholder="Enter reference number">
                </div>

                <div class="form-section">
                    <label>Coupon/Voucher Code</label>
                    <input type="text" name="coupon_code" id="couponCode" placeholder="Enter coupon code" oninput="applyCoupon()">
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
                            <option value="services">Services Only</option>
                            <option value="parts">Parts Only</option>
                            <option value="individual_services">Individual Services</option>
                            <option value="individual_parts">Individual Parts</option>
                        </select>
                    </div>
                </div>

                <div class="form-section" id="globalDiscountSection" style="display: none;">
                    <label id="discountValueLabel">Discount Amount</label>
                    <div style="position: relative;">
                        <input
                            type="number"
                            step=".01"
                            min="0"
                            id="discountValue"
                            name="discount_value"
                            placeholder="0.00"
                            oninput="calculateTotal()"
                        >
                        <span id="discountValueSuffix" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);color:#64748b;font-weight:600;display:none;">%</span>
                    </div>
                </div>

                <div class="form-section" id="individualServicesSection" style="display: none;">
                    <label>Individual Service Discounts</label>
                    @foreach($job->services as $service)
                        <div class="individual-discount-row">
                            <span class="item-name">
                                {{ $service->name_snapshot }}
                                (Rs. {{ number_format($service->unit_price * $service->quantity, 2) }})
                            </span>
                            <div class="discount-inputs">
                                <input type="number" step=".01" min="0" class="item-discount-value"
                                    data-item-type="service" data-item-id="{{ $service->id }}"
                                    data-item-price="{{ $service->unit_price * $service->quantity }}"
                                    name="individual_service_discounts[{{ $service->id }}]"
                                    placeholder="0.00" oninput="calculateIndividualDiscounts()">
                                <span class="individual-discount-suffix">Rs.</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-section" id="individualPartsSection" style="display: none;">
                    <label>Individual Part Discounts</label>
                    @foreach($job->parts as $part)
                        <div class="individual-discount-row">
                            <span class="item-name">
                                {{ $part->product->name }}
                                (Rs. {{ number_format($part->unit_price * $part->quantity, 2) }})
                            </span>
                            <div class="discount-inputs">
                                <input type="number" step=".01" min="0" class="item-discount-value"
                                    data-item-type="part" data-item-id="{{ $part->id }}"
                                    data-item-price="{{ $part->unit_price * $part->quantity }}"
                                    name="individual_part_discounts[{{ $part->id }}]"
                                    placeholder="0.00" oninput="calculateIndividualDiscounts()">
                                <span class="individual-discount-suffix">Rs.</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-section">
                    <label>Amount Received</label>
                    <input type="number" step=".01" name="amount_received" id="amountReceived" placeholder="Enter amount received" required oninput="calculateBalance()" onwheel="this.blur()">
                </div>

                <div class="balance-card" id="balanceDisplay" style="display: none;">
                    <div class="balance-content">
                        <span>Balance to Return</span>
                        <span id="balanceAmount">Rs. 0.00</span>
                    </div>
                </div>

                <div class="live-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>Rs. {{ number_format($calculation['subtotal'], 2) }}</strong>
                    </div>
                    <div class="summary-row" id="discountRow">
                        <span>Discount</span>
                        <strong id="displayDiscount">Rs. 0.00</strong>
                    </div>
                    <div class="summary-row">
                        <span>Total Due</span>
                        <strong id="displayTotal">Rs. {{ number_format($calculation['total'], 2) }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Amount Received</span>
                        <strong id="displayReceived">Rs. 0.00</strong>
                    </div>
                    <div class="summary-row final-row">
                        <span>Balance</span>
                        <strong id="displayBalance">Rs. {{ number_format($calculation['total'], 2) }}</strong>
                    </div>
                </div>

                <button type="submit" class="process-button">
                    <span>Process Payment</span>
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
        @else
        <div class="glass-card">
            <p class="empty-state">Invoice not yet generated.</p>
        </div>
        @endif
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
    grid-template-columns: repeat(3, minmax(0, 1fr));
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
}

.form-section input:focus,
.form-section select:focus {
    outline: none;
    border-color: #94a3b8;
    background: white;
    box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.1);
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
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.payment-method-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 12px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
}

.payment-method-option input[type="radio"] {
    display: none;
}

.payment-method-option:has(input:checked) {
    border-color: #94a3b8;
    background: #f1f5f9;
}

.method-icon {
    font-size: 28px;
    margin-bottom: 8px;
}

.method-label {
    font-size: 13px;
    font-weight: 600;
}

.split-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    padding: 8px 16px;
    background: #f8fafc;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
}

.split-toggle input[type="checkbox"] {
    display: none;
}

.toggle-slider {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    background: #cbd5e1;
    border-radius: 14px;
    flex-shrink: 0;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.split-toggle input[type="checkbox"]:checked + .toggle-slider {
    background: #1e293b;
}

.split-toggle input[type="checkbox"]:checked + .toggle-slider::before {
    transform: translateX(24px);
}

.payment-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 12px;
    margin-bottom: 12px;
    align-items: center;
}

.payment-row select,
.payment-row input {
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
    width: 100%;
    box-sizing: border-box;
}

.remove-row-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #ef4444;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
}

.add-row-btn {
    width: 100%;
    padding: 12px;
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    color: #64748b;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.split-summary {
    display: flex;
    justify-content: space-between;
    padding: 16px;
    background: #f1f5f9;
    border-radius: 8px;
    margin-top: 16px;
    font-size: 14px;
    flex-wrap: wrap;
    gap: 8px;
}

.individual-discount-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 8px;
    border: 1px solid #e2e8f0;
    gap: 12px;
}

.item-name {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
    color: #475569;
}

.discount-inputs {
    display: flex;
    gap: 8px;
    align-items: center;
}

.discount-inputs input {
    padding: 8px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    background: white;
    width: 100px;
}

.balance-card {
    background: #10b981;
    border-radius: 16px;
    padding: 20px;
    margin-top: 20px;
}

.balance-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.balance-content span:first-child {
    color: white;
    font-size: 16px;
    font-weight: 600;
}

.balance-content span:last-child {
    color: white;
    font-size: 28px;
    font-weight: 700;
}

.live-summary {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid #e2e8f0;
}

.final-row {
    padding-top: 16px;
    border-top: 2px solid #e2e8f0;
    margin-top: 8px;
}

.process-button {
    width: 100%;
    padding: 18px 24px;
    background: #1e293b;
    color: white;
    border: none;
    border-radius: 16px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 24px;
}

.payment-back {
    margin-top: 16px;
    display: flex;
    justify-content: center;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    padding: 12px 20px;
    border-radius: 10px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    transition: all 0.15s;
}

.back-button:hover {
    background: #e2e8f0;
    color: #334155;
}

/* ========== RESPONSIVE (UNCHANGED) ========== */
@media (max-width: 768px) {
    .payment-header {
        padding: 20px 16px;
    }

    .header-content h1 {
        font-size: 22px;
    }

    .header-content p {
        font-size: 14px;
    }

    .payment-content {
        padding: 16px;
        grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    }

    .glass-card {
        padding: 18px;
        border-radius: 16px;
    }

    .card-header h2 {
        font-size: 17px;
    }

    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .detail-row .value {
        text-align: left;
    }

    .service-item,
    .part-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .payment-methods {
        grid-template-columns: repeat(2, 1fr);
    }

    .payment-method-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .split-toggle {
        width: 100%;
        justify-content: space-between;
        box-sizing: border-box;
    }

    .payment-row {
        grid-template-columns: 1fr;
    }

    .payment-row .remove-row-btn {
        width: 100%;
        height: 40px;
    }

    .individual-discount-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .discount-inputs {
        width: 100%;
    }

    .discount-inputs input {
        flex: 1;
        width: auto;
    }

    .balance-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .process-button {
        padding: 16px 20px;
        font-size: 16px;
    }

    .back-button {
        width: 100%;
        justify-content: center;
    }
}

/* Only force single column on very small phones */
@media (max-width: 520px) {
    .payment-content {
        grid-template-columns: 1fr;
    }

    .payment-methods {
        grid-template-columns: 1fr 1fr;
    }

    .header-content h1 {
        font-size: 20px;
    }
}
</style>

<script>
const originalTotal = {{ $calculation['total'] }};
const subtotal = {{ $calculation['subtotal'] }};
const servicesTotal = {{ $calculation['services_total'] }};
const partsTotal = {{ $calculation['parts_total'] }};
const tax = {{ $calculation['tax'] }};
let currentTotal = originalTotal;
let rowCounter = 1;

function toggleReferenceField() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const referenceField = document.getElementById('referenceField');
    const referenceLabel = document.getElementById('referenceLabel');

    if (paymentMethod === 'cash') {
        referenceField.style.display = 'none';
    } else if (paymentMethod === 'card') {
        referenceField.style.display = 'block';
        referenceLabel.textContent = 'Card Reference Number';
        document.getElementById('referenceNumber').placeholder = 'Enter card transaction reference';
    } else if (paymentMethod === 'upi') {
        referenceField.style.display = 'block';
        referenceLabel.textContent = 'UPI Transaction ID';
        document.getElementById('referenceNumber').placeholder = 'Enter UPI transaction ID';
    } else if (paymentMethod === 'bank_transfer') {
        referenceField.style.display = 'block';
        referenceLabel.textContent = 'Bank Transfer Reference';
        document.getElementById('referenceNumber').placeholder = 'Enter bank transfer reference';
    }
}

function updateHiddenFieldsBeforeSubmit() {
    const discountType = document.getElementById('discountType').value;
    const discountApplyTo = document.getElementById('discountApplyTo').value;

    if (discountType === 'none') {
        document.getElementById('discountValue').value = '0';
        return;
    }

    if (
        discountApplyTo === 'individual_services' ||
        discountApplyTo === 'individual_parts'
    ) {
        calculateIndividualDiscounts();
    }
}

function toggleDiscountOptions() {
    const discountType = document.getElementById('discountType').value;
    const applyToSection = document.getElementById('applyToSection');
    const globalDiscountSection = document.getElementById('globalDiscountSection');
    const individualServicesSection = document.getElementById('individualServicesSection');
    const individualPartsSection = document.getElementById('individualPartsSection');

    applyToSection.style.display = 'none';
    globalDiscountSection.style.display = 'none';
    individualServicesSection.style.display = 'none';
    individualPartsSection.style.display = 'none';

    if (discountType === 'none') {
        document.getElementById('discountValue').value = '';
        document.getElementById('discountApplyTo').value = 'total';
        resetDiscountDisplay();
        return;
    }

    applyToSection.style.display = 'block';
    toggleDiscountSection();
}

function toggleDiscountSection() {
    const discountType = document.getElementById('discountType').value;
    const discountApplyTo = document.getElementById('discountApplyTo').value;
    const globalDiscountSection = document.getElementById('globalDiscountSection');
    const individualServicesSection = document.getElementById('individualServicesSection');
    const individualPartsSection = document.getElementById('individualPartsSection');

    globalDiscountSection.style.display = 'none';
    individualServicesSection.style.display = 'none';
    individualPartsSection.style.display = 'none';

    if (discountApplyTo === 'individual_services') {
        individualServicesSection.style.display = 'block';
        updateIndividualDiscountInputs();
        calculateIndividualDiscounts();
        return;
    }

    if (discountApplyTo === 'individual_parts') {
        individualPartsSection.style.display = 'block';
        updateIndividualDiscountInputs();
        calculateIndividualDiscounts();
        return;
    }

    globalDiscountSection.style.display = 'block';
    updateDiscountValueUI();
    calculateTotal();
}

function updateDiscountValueUI() {
    const discountType = document.getElementById('discountType').value;
    const label = document.getElementById('discountValueLabel');
    const suffix = document.getElementById('discountValueSuffix');
    const input = document.getElementById('discountValue');

    if (discountType === 'percentage') {
        label.textContent = 'Discount Percentage';
        suffix.style.display = 'block';
        input.placeholder = '0';
        input.max = '100';
    } else {
        label.textContent = 'Discount Amount';
        suffix.style.display = 'none';
        input.placeholder = '0.00';
        input.removeAttribute('max');
    }
}

function updateIndividualDiscountInputs() {
    const discountType = document.getElementById('discountType').value;

    document.querySelectorAll('.individual-discount-suffix').forEach(suffix => {
        suffix.textContent = discountType === 'percentage' ? '%' : 'Rs.';
    });

    document.querySelectorAll('.item-discount-value').forEach(input => {
        if (discountType === 'percentage') {
            input.placeholder = '0';
            input.max = '100';
        } else {
            input.placeholder = '0.00';
            input.removeAttribute('max');
        }
    });
}

function calculateTotal() {
    const discountType = document.getElementById('discountType').value;
    const discountApplyTo = document.getElementById('discountApplyTo').value;

    if (discountType === 'none') {
        resetDiscountDisplay();
        return;
    }

    if (discountApplyTo === 'individual_services' || discountApplyTo === 'individual_parts') {
        calculateIndividualDiscounts();
        return;
    }

    const discountValue = parseFloat(
        document.getElementById('discountValue').value
    ) || 0;

    let discountBase = subtotal;
    if (discountApplyTo === 'services') discountBase = servicesTotal;
    else if (discountApplyTo === 'parts') discountBase = partsTotal;

    let discountAmount = 0;
    if (discountType === 'amount') {
        discountAmount = Math.min(discountValue, discountBase);
    } else if (discountType === 'percentage') {
        discountAmount = (discountBase * Math.min(discountValue, 100)) / 100;
    }

    currentTotal = Math.max(0, (subtotal - discountAmount) + tax);

    document.getElementById('displayDiscount').textContent = 'Rs. ' + discountAmount.toFixed(2);
    document.getElementById('displayTotal').textContent = 'Rs. ' + currentTotal.toFixed(2);
    calculateBalance();
}

function calculateIndividualDiscounts() {
    const discountType = document.getElementById('discountType').value;
    const discountApplyTo = document.getElementById('discountApplyTo').value;

    let totalDiscount = 0;

    let selector = '';
    if (discountApplyTo === 'individual_services') selector = '.item-discount-value[data-item-type="service"]';
    else if (discountApplyTo === 'individual_parts') selector = '.item-discount-value[data-item-type="part"]';
    else {
        resetDiscountDisplay();
        return;
    }

    document.querySelectorAll(selector).forEach(input => {
        const value = parseFloat(input.value) || 0;
        const price = parseFloat(input.dataset.itemPrice) || 0;
        let itemDiscount = 0;

        if (discountType === 'amount') itemDiscount = Math.min(value, price);
        else if (discountType === 'percentage') itemDiscount = (price * Math.min(value, 100)) / 100;

        totalDiscount += itemDiscount;
    });

    currentTotal = Math.max(0, (subtotal - totalDiscount) + tax);

    document.getElementById('displayDiscount').textContent = 'Rs. ' + totalDiscount.toFixed(2);
    document.getElementById('displayTotal').textContent = 'Rs. ' + currentTotal.toFixed(2);
    calculateBalance();
}

function resetDiscountDisplay() {
    currentTotal = originalTotal;
    document.getElementById('displayDiscount').textContent = 'Rs. 0.00';
    document.getElementById('displayTotal').textContent =
        'Rs. ' + currentTotal.toFixed(2);
    calculateBalance();
}

function toggleSplitPayment() {
    const splitToggle = document.getElementById('splitPaymentToggle');
    const singleSection = document.getElementById('singlePaymentSection');
    const splitSection = document.getElementById('splitPaymentSection');
    const referenceField = document.getElementById('referenceField');
    const amountReceived = document.getElementById('amountReceived');

    if (splitToggle.checked) {
        singleSection.style.display = 'none';
        splitSection.style.display = 'block';
        referenceField.style.display = 'none';
        amountReceived.parentElement.style.display = 'none';
        calculateSplitTotal();
    } else {
        singleSection.style.display = 'block';
        splitSection.style.display = 'none';
        referenceField.style.display = 'none';
        amountReceived.parentElement.style.display = 'block';
        toggleReferenceField();
    }
}

function addPaymentRow() {
    const paymentRows = document.getElementById('paymentRows');
    const newRow = document.createElement('div');
    newRow.className = 'payment-row';
    newRow.dataset.row = rowCounter;
    newRow.innerHTML = `
        <select class="split-method" onchange="updateSplitReference(this)">
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="upi">UPI</option>
            <option value="bank_transfer">Bank Transfer</option>
        </select>
        <input type="number" step=".01" class="split-amount" placeholder="Amount" oninput="calculateSplitTotal()">
        <input type="text" class="split-reference" placeholder="Reference (if needed)" style="display: none;">
        <button type="button" class="remove-row-btn" onclick="removePaymentRow(this)">×</button>
    `;
    paymentRows.appendChild(newRow);
    rowCounter++;
    updateRemoveButtons();
}

function removePaymentRow(button) {
    button.closest('.payment-row').remove();
    updateRemoveButtons();
    calculateSplitTotal();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.payment-row');
    rows.forEach(row => {
        const removeBtn = row.querySelector('.remove-row-btn');
        removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
    });
}

function updateSplitReference(select) {
    const row = select.closest('.payment-row');
    const referenceInput = row.querySelector('.split-reference');
    const method = select.value;

    if (method === 'cash') {
        referenceInput.style.display = 'none';
    } else {
        referenceInput.style.display = 'block';
        if (method === 'card') referenceInput.placeholder = 'Card reference';
        else if (method === 'upi') referenceInput.placeholder = 'UPI transaction ID';
        else if (method === 'bank_transfer') referenceInput.placeholder = 'Bank transfer reference';
    }
}

function calculateSplitTotal() {
    let total = 0;
    document.querySelectorAll('.split-amount').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('splitTotal').textContent = 'Rs. ' + total.toFixed(2);
    document.getElementById('splitRemaining').textContent = 'Rs. ' + Math.max(0, currentTotal - total).toFixed(2);

    document.getElementById('amountReceived').value = total;
    calculateBalance();
}

function applyCoupon() {
    console.log('Applying coupon:', document.getElementById('couponCode').value);
}

function calculateBalance() {
    const amountReceived = parseFloat(document.getElementById('amountReceived').value) || 0;
    const balance = amountReceived - currentTotal;

    document.getElementById('displayReceived').textContent = 'Rs. ' + amountReceived.toFixed(2);
    document.getElementById('displayBalance').textContent = 'Rs. ' + Math.abs(balance).toFixed(2);

    const balanceDisplay = document.getElementById('balanceDisplay');
    const balanceAmount = document.getElementById('balanceAmount');

    if (amountReceived > 0) {
        balanceDisplay.style.display = 'block';
        if (balance >= 0) {
            balanceAmount.textContent = 'Rs. ' + balance.toFixed(2);
            balanceDisplay.style.background = '#10b981';
            document.getElementById('displayBalance').style.color = '#10b981';
        } else {
            balanceAmount.textContent = 'Rs. ' + Math.abs(balance).toFixed(2);
            balanceDisplay.style.background = '#ef4444';
            document.getElementById('displayBalance').style.color = '#ef4444';
        }
    } else {
        balanceDisplay.style.display = 'none';
    }
}
</script>
@endsection