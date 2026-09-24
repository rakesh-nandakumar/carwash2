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
            font-size: 20px;
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
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .company-details {
            font-size: 18px;
            margin-bottom: 12px;
            color: #000;
            font-weight: bold;
        }

        .divider {
            border-top: 2px dashed #000;
            margin: 10px 0;
        }

        .report-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .report-title {
            font-size: 24px;
            font-weight: 900;
        }

        .report-date {
            font-size: 18px;
            font-weight: bold;
        }

        .report-info {
            text-align: left;
            margin-bottom: 12px;
            font-size: 18px;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 18px;
        }

        .items-table td {
            padding: 3px 2px;
        }

        .item-name {
            text-align: left;
            font-weight: bold;
            font-size: 16px;
        }

        .item-qty {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }

        .item-price {
            text-align: right;
            font-weight: bold;
            font-size: 16px;
        }

        .item-total {
            text-align: right;
            font-weight: 900;
            font-size: 16px;
        }

        .totals {
            text-align: right;
            margin-bottom: 12px;
            font-size: 18px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .grand-total {
            font-size: 20px;
            font-weight: 900;
            border-top: 2px solid #000;
            margin-top: 8px;
            padding-top: 8px;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
            color: #000;
            font-weight: bold;
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
            <div style="text-align: center; margin-bottom: 12px;">
                <img
                    src="{{ \App\Support\Media::url($settings['logo_path']) }}"
                    alt="Logo"
                    style="
                        max-width: 60mm;
                        max-height: {{ $settings['logo_size_thermal'] ?? 50 }}px;
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

            @if($settings['tax_id'])
                Tax ID: {{ $settings['tax_id'] }}
            @endif
        </div>

        <div class="divider"></div>

        {{-- Report Header --}}
        <div class="report-header">
            <div class="report-title">
                DAY REPORT #{{ $closure->id }}
            </div>

            <div class="report-date">
                {{ $closure->closed_at ? $closure->closed_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- Cashier/Till Info --}}
        <div class="report-info">
            <strong>Cashier:</strong> {{ $closure->user->name }}<br>
            <strong>Till:</strong> {{ $till->name }}<br>
            <strong>Shift:</strong> {{ $closure->opened_at ? $closure->opened_at->format('d/m/Y H:i') : 'N/A' }} - {{ $closure->closed_at ? $closure->closed_at->format('H:i') : 'Open' }}
        </div>

        <div class="divider"></div>

        {{-- Sales Breakdown --}}
        <table class="items-table">
            <tr style="border-bottom: 1px solid #000;">
                <td class="item-name" style="font-size: 11px;">METHOD</td>
                <td class="item-total" style="font-size: 11px;">AMOUNT</td>
            </tr>
            <tr>
                <td class="item-name">Cash Sales</td>
                <td class="item-total">Rs. {{ number_format($closure->cash_sales, 0) }}</td>
            </tr>
            <tr>
                <td class="item-name">Card Sales</td>
                <td class="item-total">Rs. {{ number_format($closure->card_sales, 0) }}</td>
            </tr>
            <tr>
                <td class="item-name">UPI Sales</td>
                <td class="item-total">Rs. {{ number_format($closure->mobile_money_sales, 0) }}</td>
            </tr>
            <tr>
                <td class="item-name">Bank Transfer</td>
                <td class="item-total">Rs. {{ number_format($closure->bank_transfer_sales, 0) }}</td>
            </tr>
            <tr>
                <td class="item-name">Cheque</td>
                <td class="item-total">Rs. {{ number_format($closure->cheque_sales, 0) }}</td>
            </tr>
            <tr>
                <td class="item-name">Other</td>
                <td class="item-total">Rs. {{ number_format($closure->other_payment_sales, 0) }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        {{-- Totals --}}
        <div class="totals">
            <div class="total-row">
                <span>Total Sales:</span>
                <span>Rs. {{ number_format($closure->total_sales, 0) }}</span>
            </div>
            <div class="total-row">
                <span>Transactions:</span>
                <span>{{ $transactionCount }}</span>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Cash Movements --}}
        <div style="margin-bottom: 8px;">
            <div style="font-size: 14px; font-weight: bold;">CASH IN ({{ $cashInCount }})</div>
        </div>
        @if($cashInMovements->count() > 0)
            @foreach($cashInMovements as $movement)
                <div style="font-size: 13px; padding: 2px 0;">
                    {{ $movement->reason }} @if($movement->description) - {{ $movement->description }} @endif
                </div>
            @endforeach
        @else
            <div style="font-size: 12px; color: #666;">No cash in</div>
        @endif

        <div style="margin-bottom: 8px; margin-top: 12px;">
            <div style="font-size: 14px; font-weight: bold;">WITHDRAWALS ({{ $cashOutCount }})</div>
        </div>
        @if($cashOutMovements->count() > 0)
            @foreach($cashOutMovements as $movement)
                <div style="font-size: 13px; padding: 2px 0;">
                    {{ $movement->reason }} @if($movement->description) - {{ $movement->description }} @endif
                </div>
            @endforeach
        @else
            <div style="font-size: 12px; color: #666;">No withdrawals</div>
        @endif

        <div class="divider"></div>

        {{-- Balance Summary --}}
        <div class="totals">
            <div class="total-row">
                <span>Opening Balance:</span>
                <span>Rs. {{ number_format($closure->opening_balance, 0) }}</span>
            </div>
            <div class="total-row">
                <span>+ Cash Sales:</span>
                <span>Rs. {{ number_format($closure->cash_sales, 0) }}</span>
            </div>
            <div class="total-row">
                <span>+ Cash In:</span>
                <span>Rs. {{ number_format($closure->cash_in, 0) }}</span>
            </div>
            <div class="total-row">
                <span>- Withdrawals:</span>
                <span>Rs. {{ number_format($closure->cash_out, 0) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>EXPECTED:</span>
                <span>Rs. {{ number_format($closure->expected_balance, 0) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>COUNTED:</span>
                <span>Rs. {{ number_format($closure->counted_balance, 0) }}</span>
            </div>
            <div class="total-row">
                <span>DISCREPANCY:</span>
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

        <div style="margin-bottom: 8px;">
            <div style="font-size: 14px; font-weight: bold;">DENOMINATIONS</div>
        </div>

        <table class="items-table">
            <tr style="border-bottom: 1px solid #000;">
                <td class="item-name" style="font-size: 11px;">DENOM</td>
                <td class="item-qty" style="font-size: 11px;">COUNT</td>
                <td class="item-total" style="font-size: 11px;">TOTAL</td>
            </tr>
            @foreach($closure->denomination_breakdown as $denomination => $count)
                @if($count > 0)
                    <tr>
                        <td class="item-name">Rs. {{ $denomination }}</td>
                        <td class="item-qty">{{ $count }}</td>
                        <td class="item-total">Rs. {{ number_format($denomination * $count, 0) }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
        @endif

        {{-- Variance Information --}}
        @if($closure->opening_variance != 0 || $closure->discrepancy != 0 || $closure->opening_variance_reason || $closure->variance_reason)
        <div class="divider"></div>

        <div style="margin-bottom: 8px;">
            <div style="font-size: 14px; font-weight: bold;">VARIANCE DETAILS</div>
        </div>

        @if($closure->opening_variance != 0 || $closure->opening_variance_reason)
        <div style="margin-bottom: 8px;">
            <div style="font-size: 13px;">Opening:</div>
            @if($closure->opening_variance != 0)
            <div style="font-size: 14px; font-weight: 900;">
                @if($closure->opening_variance > 0)
                    +Rs. {{ number_format($closure->opening_variance, 0) }}
                @elseif($closure->opening_variance < 0)
                    Rs. {{ number_format(abs($closure->opening_variance), 0) }}
                @endif
            </div>
            @endif
            @if($closure->opening_variance_reason)
            <div style="font-size: 12px; white-space: pre-wrap;">{{ $closure->opening_variance_reason }}</div>
            @endif
        </div>
        @endif

        @if($closure->discrepancy != 0 || $closure->variance_reason)
        <div>
            <div style="font-size: 13px;">Closing:</div>
            @if($closure->discrepancy != 0)
            <div style="font-size: 14px; font-weight: 900;">
                @if($closure->discrepancy > 0)
                    +Rs. {{ number_format($closure->discrepancy, 0) }}
                @elseif($closure->discrepancy < 0)
                    Rs. {{ number_format(abs($closure->discrepancy), 0) }}
                @endif
            </div>
            @endif
            @if($closure->variance_reason)
            <div style="font-size: 12px; white-space: pre-wrap;">{{ $closure->variance_reason }}</div>
            @endif
        </div>
        @endif
        @endif

        {{-- Notes --}}
        @if($closure->notes)
        <div class="divider"></div>

        <div style="margin-bottom: 8px;">
            <div style="font-size: 14px; font-weight: bold;">NOTES</div>
        </div>

        <div style="font-size: 13px; white-space: pre-wrap;">{{ $closure->notes }}</div>
        @endif

        <div class="divider"></div>

        {{-- Footer --}}
        <div class="footer">
            {!! nl2br(e($settings['footer_text'])) !!}<br>

            {{ now()->format('d/m/Y H:i') }}<br>

            <span class="powered">
                Powered by Vellix Global - 0773208478
            </span>
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