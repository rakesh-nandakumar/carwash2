<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            @page { margin: 0; size: A4; }
            body { margin: 1cm; }
            .no-print { display: none !important; }
        }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; }
        .invoice-container { max-width: 210mm; margin: 0 auto; background: white; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .company-info { flex: 1; }
        .company-info h1 { font-size: 24px; margin: 0 0 10px 0; color: #2c3e50; }
        .company-info p { margin: 5px 0; color: #666; }
        .invoice-details { text-align: right; }
        .invoice-details h2 { font-size: 20px; margin: 0 0 15px 0; color: #e74c3c; }
        .invoice-details p { margin: 5px 0; }
        .customer-info { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .bill-to, .ship-to { flex: 1; }
        .bill-to h3, .ship-to h3 { font-size: 14px; margin: 0 0 10px 0; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .bill-to p, .ship-to p { margin: 5px 0; color: #666; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #f8f9fa; border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: bold; color: #2c3e50; }
        .items-table td { border: 1px solid #ddd; padding: 10px; }
        .items-table tr:nth-child(even) { background: #f9f9f9; }
        .item-discount {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
        }
        .totals { width: 300px; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .totals-row.grand-total { font-size: 16px; font-weight: bold; color: #2c3e50; border-top: 2px solid #333; border-bottom: none; margin-top: 10px; padding-top: 15px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 11px; }
        .terms { margin-top: 20px; padding: 15px; background: #f8f9fa; border-left: 3px solid #e74c3c; font-size: 11px; }
        .payment-info { margin-top: 20px; padding: 15px; background: #f0f8ff; border-left: 3px solid #3498db; font-size: 11px; }
        .no-print { margin: 20px 0; text-align: center; }
        .no-print button { padding: 10px 20px; margin: 0 10px; cursor: pointer; border: none; border-radius: 4px; }
        .btn-primary { background: #3498db; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
    </style>
</head>
<body>
    <div class="invoice-container">
        @php
            $business = auth()->user()->business;
            $settings = $business ? $business->getBillingSettings() : [
                'company_name' => 'AutoCare Pro',
                'address' => '',
                'phone' => '',
                'email' => '',
                'website' => '',
                'tax_id' => '',
                'invoice_prefix' => 'INV-',
                'receipt_prefix' => 'REC-',
                'footer_text' => 'Thank you for your business!',
                'terms_conditions' => 'Payment due upon receipt. Valid for 30 days.',
                'logo_path' => '',
                'a4_enabled' => true,
                'thermal_enabled' => true,
                'default_format' => 'a4'
            ];
        @endphp

        <div class="header">
            <div class="company-info">
                @if($settings['logo_path'])
                    <img src="{{ asset($settings['logo_path']) }}" alt="Logo" style="max-height: {{ $settings['logo_size_a4'] ?? 60 }}px; margin-bottom: 10px;">
                @endif
                <h1>{{ $settings['company_name'] }}</h1>
                @if($settings['address'])<p>{{ $settings['address'] }}</p>@endif
                @if($settings['phone'])<p>Phone: {{ $settings['phone'] }}</p>@endif
                @if($settings['email'])<p>Email: {{ $settings['email'] }}</p>@endif
                @if($settings['website'])<p>Website: {{ $settings['website'] }}</p>@endif
                @if($settings['tax_id'])<p>Tax ID: {{ $settings['tax_id'] }}</p>@endif
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->created_at->addDays(30)->format('d M Y') }}</p>
                <p><strong>Status:</strong> <span style="color: {{ $invoice->status == 'paid' ? 'green' : 'orange' }}; font-weight: bold;">{{ ucfirst($invoice->status) }}</span></p>
            </div>
        </div>

        <div class="customer-info">
            <div class="bill-to">
                <h3>BILL TO</h3>
                <p><strong>{{ $invoice->customer->full_name }}</strong></p>
                @if($invoice->customer->phone)<p>Phone: {{ $invoice->customer->phone }}</p>@endif
                @if($invoice->customer->email)<p>Email: {{ $invoice->customer->email }}</p>@endif
            </div>
            <div class="ship-to">
                <h3>VEHICLE DETAILS</h3>
                @if($invoice->job && $invoice->job->vehicle)
                    <p><strong>{{ $invoice->job->vehicle->registration_number }}</strong></p>
                    @if($invoice->job->vehicle->make)<p>{{ $invoice->job->vehicle->make }} {{ $invoice->job->vehicle->model }}</p>@endif
                    @if($invoice->job->vehicle->color)<p>Color: {{ $invoice->job->vehicle->color }}</p>@endif
                @endif
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="50%">Description</th>
                    <th width="10%">Qty</th>
                    <th width="15%">Unit Price</th>
                    <th width="15%">Tax</th>
                    <th width="10%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            {{ $item->description }}

                            @if((float) $item->discount > 0)
                                <div class="item-discount">
                                    Discount:
                                    -Rs. {{ number_format($item->discount, 2) }}
                                </div>
                            @endif
                        </td>

                        <td>{{ $item->quantity }}</td>

                        <td>
                            Rs. {{ number_format($item->unit_price, 2) }}
                        </td>

                        <td>
                            Rs. {{ number_format($item->tax, 2) }}
                        </td>

                        <td>
                            Rs. {{ number_format($item->line_total, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>Rs. {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Discount:</span>
                <span>-Rs. {{ number_format($invoice->discount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Tax:</span>
                <span>Rs. {{ number_format($invoice->tax, 2) }}</span>
            </div>
            <div class="totals-row grand-total">
                <span>Total Due:</span>
                <span>Rs. {{ number_format($invoice->total, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Amount Received:</span>
                <span>Rs. {{ number_format($currentPaymentAmount ?: $invoice->paid, 2) }}</span>
            </div>
            @if(isset($currentBalance) && $currentBalance >= 0)
            <div class="totals-row" style="color: green; font-weight: bold;">
                <span>Balance to Return:</span>
                <span>Rs. {{ number_format($currentBalance, 2) }}</span>
            </div>
            @elseif(isset($currentBalance) && $currentBalance < 0)
            <div class="totals-row" style="color: red; font-weight: bold;">
                <span>Amount Still Due:</span>
                <span>Rs. {{ number_format(abs($currentBalance), 2) }}</span>
            </div>
            @else
            <div class="totals-row" style="color: {{ $invoice->balance >= 0 ? 'green' : 'red' }}; font-weight: bold;">
                <span>Balance:</span>
                <span>Rs. {{ number_format($invoice->balance, 2) }}</span>
            </div>
            @endif
        </div>

        @if($settings['terms_conditions'])
            <div class="terms">
                <strong>Terms & Conditions:</strong>
                <p>{{ $settings['terms_conditions'] }}</p>
            </div>
        @endif

        <div class="payment-info">
            <strong>Payment Information:</strong>
            <p>We accept Cash, Card, and Bank Transfer</p>
            @if($settings['phone'])<p>For inquiries: {{ $settings['phone'] }}</p>@endif
        </div>

        <div class="footer">
            <p>{{ $settings['footer_text'] }}</p>
            <p>Generated on {{ now()->format('d M Y H:i') }} · {{ $settings['company_name'] }}</p>
            <p style="font-weight: bold; margin-top: 15px;">Powered by Vellix Global - 0773208478</p>
        </div>

        <div class="no-print">
            <button onclick="window.print()" class="btn-primary">Print Invoice</button>
            <button onclick="window.close()" class="btn-secondary">Close</button>
        </div>
    </div>
</body>
</html>