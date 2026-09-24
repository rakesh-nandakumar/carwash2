@extends('layouts.app')

@section('title', 'Payment Completed')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Payment Already Completed</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5>POS Sale #{{ $invoice->invoice_number }}</h5>
                        <p class="text-muted">This invoice has already been fully paid.</p>
                    </div>

                    <div class="alert alert-info">
                        <strong>Total:</strong> LKR {{ number_format($invoice->total, 2) }}<br>
                        <strong>Paid:</strong> LKR {{ number_format($invoice->paid, 2) }}<br>
                        <strong>Balance:</strong> LKR {{ number_format($invoice->balance, 2) }}
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('cashier.index') }}" class="btn btn-primary">
                            <i class="fas fa-cash-register me-2"></i>Back to Cashier
                        </a>
                        <a href="{{ route('pos.index') }}" class="btn btn-success">
                            <i class="fas fa-shopping-cart me-2"></i>New POS Sale
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
