<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Day Report</title>

    <style>
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }

            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 80mm !important;
                height: auto !important;
                overflow: visible !important;
            }

            body {
                padding: 3mm !important;
            }

            .receipt-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            table, tr, td, div, p {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            color: #000;
            width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 3mm;
            box-sizing: border-box;
            font-weight: bold;
            height: auto;
            min-height: auto;
        }

        .receipt-container {
            text-align: center;
            margin: 0 auto;
            width: 100%;
            height: auto;
            min-height: auto;
        }

        .company-name {
            font-size: 22px;
            font-weight: 900;
            margin-bottom: 6px;
        }

        .company-details {
            font-size: 14px;
            margin-bottom: 10px;
            color: #000;
            font-weight: normal;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: 900;
            text-align: center;
            margin: 8px 0;
            text-transform: uppercase;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            font-size: 14px;
        }

        .info-label {
            font-weight: normal;
        }

        .info-value {
            font-weight: 900;
        }

        .calculation-box {
            border: 1px solid #000;
            padding: 8px;
            margin: 8px 0;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            font-size: 14px;
        }

        .calc-row.result {
            border-top: 1px solid #000;
            margin-top: 6px;
            padding-top: 6px;
            font-size: 16px;
        }

        .movement-item {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 13px;
        }

        .movement-item small {
            font-weight: normal;
            color: #666;
        }

        .denom-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 13px;
        }

        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 12px;
            color: #000;
            font-weight: normal;
        }

        .no-print {
            margin: 18px 0;
            text-align: center;
        }

        .no-print button {
            padding: 10px 18px;
            margin: 0 5px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            font-size: 11px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
    </style>
</head>

<body>
    <div class="receipt-container">

        @php
            $business = auth()->user()->business;

            $settings = $business
                ? $business->getBillingSettings()
                : [
                    'company_name' => 'AutoCare Pro',
                    'address' => '',
                    'phone' => '',
                    'tax_id' => '',
                    'footer_text' => "Thank you for your business!!\nFor any enquiries, Email us on prasadauticare@gmail.com or call us on 0115 66 88 88",
                    'logo_path' => ''
                ];
        @endphp

        {{-- Company Logo --}}
        @if($settings['logo_path'])
            <div style="text-align: center; margin-bottom: 8px;">
                <img
                    src="{{ \App\Support\Media::url($settings['logo_path']) }}"
                    alt="Logo"
                    style="
                        max-width: 50mm;
                        max-height: {{ $settings['logo_size_thermal'] ?? 40 }}px;
                        display: block;
                        margin: 0 auto;
                    "
                >
            </div>
        @endif

        {{-- Company Information --}}
        <div class="company-name">
            {{ $settings['company_name'] }}
        </div>

        <div class="company-details">
            @if($settings['address'])
                {{ $settings['address'] }}<br>
            @endif

            @if($settings['phone'])
                Tel: {{ $settings['phone'] }}<br>
            @endif
        </div>

        <div class="divider"></div>

        {{-- Report Info --}}
        <div class="section-title">TILL CLOSURE #{{ $closure->id }}</div>

        <div class="info-row">
            <span class="info-label">Date:</span>
            <span class="info-value">{{ $closure->closed_at ? $closure->closed_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cashier:</span>
            <span class="info-value">{{ $closure->user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Till:</span>
            <span class="info-value">{{ $till->name }}</span>
        </div>

        <div class="divider"></div>

        {{-- Sales Summary --}}
        <div class="section-title">SALES SUMMARY</div>

        <div class="info-row">
            <span class="info-label">Cash:</span>
            <span class="info-value">Rs. {{ number_format($closure->cash_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Card:</span>
            <span class="info-value">Rs. {{ number_format($closure->card_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">UPI:</span>
            <span class="info-value">Rs. {{ number_format($closure->mobile_money_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Bank:</span>
            <span class="info-value">Rs. {{ number_format($closure->bank_transfer_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cheque:</span>
            <span class="info-value">Rs. {{ number_format($closure->cheque_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Other:</span>
            <span class="info-value">Rs. {{ number_format($closure->other_payment_sales, 0) }}</span>
        </div>

        <div class="divider"></div>

        {{-- Cash Movements --}}
        <div class="section-title">CASH MOVEMENTS</div>

        <div style="margin-bottom: 6px;">
            <div style="font-size: 13px; font-weight: 900;">CASH IN ({{ $cashInCount }})</div>
        </div>
        @if($cashInMovements->count() > 0)
            @foreach($cashInMovements as $movement)
                <div class="movement-item">
                    <span>{{ $movement->reason }}</span>
                    <span>Rs. {{ number_format($movement->amount, 0) }}</span>
                </div>
            @endforeach
        @else
            <div style="font-size: 12px; color: #666;">No cash in</div>
        @endif

        <div style="margin-bottom: 6px; margin-top: 8px;">
            <div style="font-size: 13px; font-weight: 900;">WITHDRAWALS ({{ $cashOutCount }})</div>
        </div>
        @if($cashOutMovements->count() > 0)
            @foreach($cashOutMovements as $movement)
                <div class="movement-item">
                    <span>{{ $movement->reason }}</span>
                    <span>Rs. {{ number_format($movement->amount, 0) }}</span>
                </div>
            @endforeach
        @else
            <div style="font-size: 12px; color: #666;">No withdrawals</div>
        @endif

        <div class="divider"></div>

        {{-- Cash Calculation --}}
        <div class="section-title">CASH CALCULATION</div>

        <div class="calculation-box">
            <div class="calc-row">
                <span>Opening Balance</span>
                <span>Rs. {{ number_format($closure->opening_balance, 0) }}</span>
            </div>
            <div class="calc-row">
                <span>+ Cash Sales</span>
                <span>Rs. {{ number_format($closure->cash_sales, 0) }}</span>
            </div>
            <div class="calc-row">
                <span>+ Cash In</span>
                <span>Rs. {{ number_format($closure->cash_in, 0) }}</span>
            </div>
            <div class="calc-row">
                <span>- Withdrawals</span>
                <span>Rs. {{ number_format($closure->cash_out, 0) }}</span>
            </div>
            <div class="calc-row result">
                <span>Expected in Till</span>
                <span>Rs. {{ number_format($closure->expected_balance, 0) }}</span>
            </div>
            <div class="calc-row result">
                <span>Actually Counted</span>
                <span>Rs. {{ number_format($closure->counted_balance, 0) }}</span>
            </div>
            <div class="calc-row result">
                <span>Difference</span>
                <span>
                    @if($closure->discrepancy > 0)
                        +Rs. {{ number_format($closure->discrepancy, 0) }}
                    @elseif($closure->discrepancy < 0)
                        Rs. {{ number_format(abs($closure->discrepancy), 0) }}
                    @else
                        Rs. 0
                    @endif
                </span>
            </div>
        </div>

        {{-- Denomination Breakdown --}}
        @if($closure->denomination_breakdown)
        <div class="divider"></div>

        <div class="section-title">DENOMINATIONS</div>

        @foreach($closure->denomination_breakdown as $denomination => $count)
            @if($count > 0)
                <div class="denom-row">
                    <span>Rs. {{ $denomination }} x {{ $count }}</span>
                    <span>Rs. {{ number_format($denomination * $count, 0) }}</span>
                </div>
            @endif
        @endforeach
        @endif

        {{-- Notes --}}
        @if($closure->notes)
        <div class="divider"></div>

        <div class="section-title">NOTES</div>

        <div style="font-size: 13px; white-space: pre-wrap;">{{ $closure->notes }}</div>
        @endif

        <div class="divider"></div>

        {{-- Footer --}}
        <div class="footer">
            {!! nl2br(e($settings['footer_text'])) !!}<br>
            Printed: {{ now()->format('d/m/Y H:i') }}
        </div>

        {{-- Print Controls --}}
        <div class="no-print">
            <button
                onclick="window.print()"
                class="btn-primary"
            >
                Print Report
            </button>

            <button
                onclick="window.location.href='{{ route('cashier.index') }}'"
                class="btn-secondary"
            >
                Back to Cashier
            </button>
        </div>

    </div>
</body>
</html>