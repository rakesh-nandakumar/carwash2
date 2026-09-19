@extends('layouts.app')
@section('content')
<div class="payment-completed-container">
    <div class="payment-completed-card">
        <div class="icon-wrapper">
            <svg width="64" height="64" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1>Payment Already Completed</h1>
        <p>This payment has already been processed for:</p>
        
        <div class="job-details">
            <div class="detail-row">
                <span>Job Number:</span>
                <strong>{{ $job->job_number }}</strong>
            </div>
            <div class="detail-row">
                <span>Vehicle:</span>
                <strong>{{ $job->vehicle->registration_number }}</strong>
            </div>
            <div class="detail-row">
                <span>Customer:</span>
                <strong>{{ $job->customer->full_name }}</strong>
            </div>
            @if($job->invoice)
            <div class="detail-row">
                <span>Invoice:</span>
                <strong>{{ $job->invoice->invoice_number }}</strong>
            </div>
            @endif
        </div>

        <div class="action-buttons">
            <a href="{{ route('cashier.index') }}" class="btn-primary">
                Go to Cashier Dashboard
            </a>
            @if($job->invoice)
            <a href="{{ route('invoices.show', $job->invoice) }}" class="btn-secondary">
                View Invoice
            </a>
            @endif
        </div>
    </div>
</div>

<style>
.payment-completed-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 80px);
    padding: 20px;
}

.payment-completed-card {
    background: white;
    border-radius: 16px;
    padding: 40px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.icon-wrapper {
    margin-bottom: 24px;
}

.payment-completed-card h1 {
    color: #1f2937;
    font-size: 28px;
    margin-bottom: 12px;
}

.payment-completed-card p {
    color: #6b7280;
    font-size: 16px;
    margin-bottom: 24px;
}

.job-details {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 32px;
    text-align: left;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row span {
    color: #6b7280;
    font-size: 14px;
}

.detail-row strong {
    color: #1f2937;
    font-size: 14px;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    padding: 14px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: white;
    color: #374151;
    padding: 14px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    border: 2px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}
</style>
@endsection