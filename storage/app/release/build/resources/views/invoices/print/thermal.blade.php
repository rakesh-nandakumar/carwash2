<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $invoice->invoice_number }}</title>

    <style>
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
                margin-left: auto;
                margin-right: auto;
            }

            body {
                margin: 0 auto !important;
                width: 76mm !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #000;
            width: 76mm;
            margin: 0 auto;
            background: white;
            padding: 2mm;
            box-sizing: border-box;
            font-weight: bold;
        }

        .receipt-container {
            text-align: center;
            margin: 0 auto;
            width: 100%;
        }

        .company-name {
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .company-details {
            font-size: 12px;
            margin-bottom: 12px;
            color: #000;
            font-weight: bold;
        }

        .divider {
            border-top: 2px dashed #000;
            margin: 10px 0;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .invoice-number {
            font-size: 16px;
            font-weight: 900;
        }

        .invoice-date {
            font-size: 12px;
            font-weight: bold;
        }

        .customer-info {
            text-align: left;
            margin-bottom: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 12px;
        }

        .items-table td {
            padding: 3px 0;
        }

        .item-name {
            text-align: left;
            font-weight: bold;
        }

        .item-qty {
            text-align: center;
            font-weight: bold;
        }

        .item-price {
            text-align: right;
            font-weight: bold;
        }

        .item-total {
            text-align: right;
            font-weight: 900;
        }

        .item-discount {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
        }

        .totals {
            text-align: right;
            margin-bottom: 12px;
            font-size: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .grand-total {
            font-size: 14px;
            font-weight: 900;
            border-top: 2px solid #000;
            margin-top: 8px;
            padding-top: 8px;
        }

        .payment-status {
            text-align: center;
            margin: 12px 0;
            font-size: 14px;
            font-weight: 900;
        }

        .paid {
            color: #000;
        }

        .partial {
            color: #000;
        }

        .unpaid {
            color: #000;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 11px;
            color: #000;
            font-weight: bold;
        }

        .barcode {
            text-align: center;
            margin: 12px 0;
            font-family: 'Libre Barcode 39', cursive;
            font-size: 28px;
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
                    'footer_text' => 'Thank you for your business!',
                    'logo_path' => ''
                ];
        @endphp

        {{-- Company Logo --}}
        @if($settings['logo_path'])
            <img
                src="{{ asset($settings['logo_path']) }}"
                alt="Logo"
                style="
                    max-width: 50mm;
                    max-height: {{ $settings['logo_size_thermal'] ?? 40 }}px;
                    margin-bottom: 10px;
                "
            >
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

        {{-- Invoice Header --}}
        <div class="invoice-header">
            <div class="invoice-number">
                {{ $invoice->invoice_number }}
            </div>

            <div class="invoice-date">
                {{ $invoice->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- Customer Information --}}
        <div class="customer-info">
            <strong>
                {{ $invoice->customer->full_name }}
            </strong>

            <br>

            @if($invoice->job && $invoice->job->vehicle)
                {{ $invoice->job->vehicle->registration_number }}
            @endif
        </div>

        <div class="divider"></div>

        {{-- Invoice Items --}}
        <table class="items-table">
            @foreach($invoice->items as $item)
                <tr>
                    <td class="item-name">
                        {{ substr($item->description, 0, 20) }}

                        @if((float) $item->discount > 0)
                            <div class="item-discount">
                                Disc:
                                -Rs. {{ number_format($item->discount, 0) }}
                            </div>
                        @endif
                    </td>

                    <td class="item-qty">
                        {{ $item->quantity }}
                    </td>

                    <td class="item-price">
                        {{ number_format($item->unit_price, 0) }}
                    </td>

                    <td class="item-total">
                        {{ number_format($item->line_total, 0) }}
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="divider"></div>

        {{-- Totals --}}
        <div class="totals">

            <div class="total-row">
                <span>Subtotal:</span>
                <span>
                    Rs. {{ number_format($invoice->subtotal, 0) }}
                </span>
            </div>

            <div class="total-row">
                <span>Discount:</span>
                <span>
                    -Rs. {{ number_format($invoice->discount, 0) }}
                </span>
            </div>

            <div class="total-row">
                <span>Tax:</span>
                <span>
                    Rs. {{ number_format($invoice->tax, 0) }}
                </span>
            </div>

            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>
                    Rs. {{ number_format($invoice->total, 0) }}
                </span>
            </div>

            <div class="total-row">
                <span>Received:</span>
                <span>
                    Rs.
                    {{ number_format($currentPaymentAmount ?: $invoice->paid, 0) }}
                </span>
            </div>

            {{-- 
                ============================================================
                BALANCE / RETURN / DUE
                ============================================================

                Return:
                    Customer paid more than the invoice total.

                Due:
                    Customer still has an outstanding amount.

                Balance:
                    Nothing to return and nothing due.
            --}}
            @if($returnAmount > 0)

                <div class="total-row">
                    <span>Return:</span>
                    <span>
                        Rs. {{ number_format($returnAmount, 0) }}
                    </span>
                </div>

            @elseif($dueAmount > 0)

                <div class="total-row">
                    <span>Due:</span>
                    <span>
                        Rs. {{ number_format($dueAmount, 0) }}
                    </span>
                </div>

            @else

                <div class="total-row">
                    <span>Balance:</span>
                    <span>
                        Rs. 0
                    </span>
                </div>

            @endif

        </div>

        {{-- Payment Status --}}
        <div class="payment-status
            @if($invoice->status == 'paid')
                paid
            @elseif($invoice->status == 'partially_paid')
                partial
            @else
                unpaid
            @endif
        ">
            {{ strtoupper($invoice->status) }}
        </div>

        <div class="divider"></div>

        {{-- Footer --}}
        <div class="footer">
            {{ $settings['footer_text'] }}<br>

            {{ now()->format('d/m/Y H:i') }}<br>

            Thank you for your business!<br>

            <span style="font-weight: bold;">
                Powered by Vellix Global - 0773208478
            </span>
        </div>

        {{-- Print Controls --}}
        <div class="no-print">
            <button
                onclick="window.print()"
                class="btn-primary"
            >
                Print Receipt
            </button>

            <button
                onclick="window.close()"
                class="btn-secondary"
            >
                Close
            </button>
        </div>

    </div>
</body>
</html>