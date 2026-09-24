@extends('layouts.app')

@section('title', 'Print POS Invoice')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Print Options - POS Sale #{{ $invoice->invoice_number }}</h4>
                </div>
                <div class="card-body text-center">
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="mb-4">
                        <h5>Payment Completed Successfully</h5>
                        <p class="text-muted">Total: LKR {{ number_format($invoice->total, 2) }}</p>
                        <p class="text-muted">Paid: LKR {{ number_format($invoice->paid, 2) }}</p>
                        <p class="text-muted">Balance: LKR {{ number_format($invoice->balance, 2) }}</p>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('cashier.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-cash-register me-2"></i>Back to Cashier
                        </a>
                        <a href="{{ route('pos.index') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>New POS Sale
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
