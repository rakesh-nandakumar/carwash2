@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Payment Completed</h1>
        <p>Payment processed successfully</p>
    </div>
    <a class="secondary" href="{{ route('cashier.index') }}">Back to Dashboard</a>
</div>

<div class="panel" style="text-align: center; padding: 40px;">
    <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
    <h2 style="margin-bottom: 10px;">Payment Successful</h2>
    <p style="color: #6b7280; margin-bottom: 30px;">
        {{ $job->vehicle->registration_number }} · {{ $job->customer->full_name }}
    </p>
    
    <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <div style="font-size: 1.2em; font-weight: bold; color: #4a90e2;">
            Total: Rs. {{ number_format($job->invoice->total, 2) }}
        </div>
        @if($job->invoice->discount > 0)
        <div style="font-size: 1em; color: #10b981; margin-top: 10px;">
            Discount Applied: Rs. {{ number_format($job->invoice->discount, 2) }}
        </div>
        @endif
    </div>
    
    <h3 style="margin-bottom: 20px;">Print Invoice</h3>
    
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('invoices.print', $job->invoice) }}" target="_blank" class="primary" style="padding: 15px 30px; font-size: 16px;">
            📄 Print A4
        </a>
        <a href="{{ route('invoices.print', [$job->invoice, 'thermal']) }}" target="_blank" class="primary" style="padding: 15px 30px; font-size: 16px; background: #10b981;">
            🧾 Print Thermal
        </a>
    </div>
    
    <p style="margin-top: 30px; color: #6b7280;">
        <small>Click to open print dialog and select your printer</small>
    </p>
</div>
@endsection
