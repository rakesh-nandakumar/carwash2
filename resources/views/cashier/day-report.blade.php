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
                padding: 2mm !important;
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
            font-size: 12px;
            color: #000;
            width: 72mm;
            margin: 0 auto;
            background: white;
            padding: 2mm 0;
            box-sizing: border-box;
            font-weight: normal;
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
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .company-details {
            font-size: 11px;
            margin-bottom: 8px;
            color: #000;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 12px;
        }

        .label {
            text-align: left;
        }

        .value {
            text-align: right;
            white-space: nowrap;
        }

        .section-header {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin: 8px 0;
            text-transform: uppercase;
        }

        .movement-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 11px;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 12px;
        }

        .calc-row.total {
            border-top: 1px solid #000;
            margin-top: 4px;
            padding-top: 4px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 10px;
            color: #000;
        }

        .no-print {
            margin: 16px 0;
            text-align: center;
        }

        .no-print button {
            padding: 8px 16px;
            margin: 0 4px;
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
            <div style="text-align: center; margin-bottom: 6px;">
                <img
                    src="{{ \App\Support\Media::url($settings['logo_path']) }}"
                    alt="Logo"
                    style="
                        max-width: 50mm;
                        max-height: {{ $settings['logo_size_thermal'] ?? 35 }}px;
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

        {{-- Report Header --}}
        <div class="section-header">TILL CLOSURE #{{ $closure->id }}</div>

        <div class="info-row">
            <span class="label">Date:</span>
            <span class="value">{{ $closure->closed_at ? $closure->closed_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Cashier:</span>
            <span class="value">{{ $closure->user->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Till:</span>
            <span class="value">{{ $till->name }}</span>
        </div>

        <div class="divider"></div>

        {{-- Sales Summary --}}
        <div class="section-header">SALES SUMMARY</div>

        <div class="info-row">
            <span class="label">Cash:</span>
            <span class="value">Rs. {{ number_format($closure->cash_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Card:</span>
            <span class="value">Rs. {{ number_format($closure->card_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="label">UPI:</span>
            <span class="value">Rs. {{ number_format($closure->mobile_money_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Bank:</span>
            <span class="value">Rs. {{ number_format($closure->bank_transfer_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Cheque:</span>
            <span class="value">Rs. {{ number_format($closure->cheque_sales, 0) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Other:</span>
            <span class="value">Rs. {{ number_format($closure->other_payment_sales, 0) }}</span>
        </div>

        <div class="divider"></div>

        {{-- Cash Movements --}}
        <div class="section-header">CASH MOVEMENTS</div>

        <div style="font-size: 11px; font-weight: bold; margin-bottom: 4px;">CASH IN ({{ $cashInCount }})</div>
        @if($cashInMovements->count() > 0)
            @foreach($cashInMovements as $movement)
                <div class="movement-row">
                    <span>{{ $movement->reason }}</span>
                    <span>Rs. {{ number_format($movement->amount, 0) }}</span>
                </div>
            @endforeach
        @else
            <div style="font-size: 10px; color: #666;">No cash in</div>
        @endif

        <div style="font-size: 11px; font-weight: bold; margin: 6px 0 4px 0;">WITHDRAWALS ({{ $cashOutCount }})</div>
        @if($cashOutMovements->count() > 0)
            @foreach($cashOutMovements as $movement)
                <div class="movement-row">
                    <span>{{ $movement->reason }}</span>
                    <span>Rs. {{ number_format($movement->amount, 0) }}</span>
                </div>
            @endforeach
        @else
            <div style="font-size: 10px; color: #666;">No withdrawals</div>
        @endif

        <div class="divider"></div>

        {{-- Cash Calculation --}}
        <div class="section-header">CASH CALCULATION</div>

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
        <div class="calc-row total">
            <span>Expected in Till</span>
            <span>Rs. {{ number_format($closure->expected_balance, 0) }}</span>
        </div>
        <div class="calc-row total">
            <span>Actually Counted</span>
            <span>Rs. {{ number_format($closure->counted_balance, 0) }}</span>
        </div>
        <div class="calc-row total">
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

        {{-- Denomination Breakdown --}}
        @if($closure->denomination_breakdown)
        <div class="divider"></div>

        <div class="section-header">DENOMINATIONS</div>

        @foreach($closure->denomination_breakdown as $denomination => $count)
            @if($count > 0)
                <div class="movement-row">
                    <span>Rs. {{ $denomination }} x {{ $count }}</span>
                    <span>Rs. {{ number_format($denomination * $count, 0) }}</span>
                </div>
            @endif
        @endforeach
        @endif

        {{-- Notes --}}
        @if($closure->notes)
        <div class="divider"></div>

        <div class="section-header">NOTES</div>

        <div style="font-size: 11px; white-space: pre-wrap;">{{ $closure->notes }}</div>
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