@extends('layouts.app')

@section('title', 'Payment - POS Sale')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Payment - POS Sale #{{ $invoice->invoice_number }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Customer</h5>
                            <p class="mb-1">
                                <strong>{{ $invoice->customer->full_name ?? 'Walk-in Customer' }}</strong>
                            </p>
                            @if($invoice->customer)
                            <p class="mb-0">{{ $invoice->customer->phone ?? '' }}</p>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <h5>Invoice Details</h5>
                            <p class="mb-1">Invoice #: {{ $invoice->invoice_number }}</p>
                            <p class="mb-0">Date: {{ $invoice->date->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Items</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">LKR {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">LKR {{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>LKR {{ number_format($invoice->subtotal, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                <td class="text-end"><strong>LKR {{ number_format($invoice->tax, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                <td class="text-end"><strong>LKR {{ number_format($invoice->discount, 2) }}</strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>LKR {{ number_format($invoice->total, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Paid:</strong></td>
                                <td class="text-end"><strong>LKR {{ number_format($invoice->paid, 2) }}</strong></td>
                            </tr>
                            <tr class="table-warning">
                                <td colspan="3" class="text-end"><strong>Balance Due:</strong></td>
                                <td class="text-end"><strong>LKR {{ number_format($invoice->balance, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <hr>

                    <form method="POST" action="{{ route('cashier.process-payment-invoice', $invoice) }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="upi">UPI</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Amount Received</label>
                                    <input type="number" name="amount_received" class="form-control" required min="0" step="0.01" value="{{ $invoice->balance }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference (Optional)</label>
                            <input type="text" name="reference" class="form-control" placeholder="Transaction reference number">
                        </div>

                        <!-- Cheque fields -->
                        <div id="cheque-fields" class="d-none">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Cheque Number</label>
                                        <input type="text" name="cheque_number" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Due Date</label>
                                        <input type="date" name="cheque_due_date" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('cashier.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Process Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.querySelector('select[name="payment_method"]');
    const chequeFields = document.getElementById('cheque-fields');

    paymentMethod.addEventListener('change', function() {
        if (this.value === 'cheque') {
            chequeFields.classList.remove('d-none');
        } else {
            chequeFields.classList.add('d-none');
        }
    });
});
</script>
@endsection
