@extends('layouts.app')
@section('content')
@php
    $till = app(\App\Services\CashMovementService::class)->getSelectedTill();
    $currentClosure = app(\App\Services\CashMovementService::class)->lastClosure($till);
    $isShiftOpen = $currentClosure && !$currentClosure->closed_at;
@endphp

<div class="page-head">
    <div>
        <h1>Search Results</h1>
        <p>Search results for: "{{ $query }}"</p>
    </div>
    <a class="secondary" href="{{ route('cashier.index') }}">Back to Dashboard</a>
</div>

<div class="panel">
    @forelse($jobs as $job)
        <div class="listrow" style="cursor: pointer;" 
             @if($isShiftOpen)
             onclick="window.location.href='{{ route('cashier.payment', $job) }}'"
             @else
             onclick="showTillNotOpenToast()"
             @endif
        >
            <div>
                <b>{{ $job->vehicle->registration_number }}</b>
                <span>{{ $job->customer->full_name }}</span>
                <small>{{ $job->job_number }}</small>
            </div>
            <div>
                @if($job->invoice)
                    <span class="badge large">Rs. {{ number_format($job->invoice->total, 2) }}</span>
                @else
                    <span class="badge large">Calculating...</span>
                @endif
                <small>{{ $job->updated_at->diffForHumans() }}</small>
            </div>
        </div>
    @empty
        <p class="empty">No results found for "{{ $query }}"</p>
    @endforelse
</div>

<script>
function showTillNotOpenToast() {
    const toast = document.createElement('div');
    toast.className = 'toast error';
    toast.textContent = 'Please open the till before processing payments';
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '100001';
    toast.style.padding = '14px 20px';
    toast.style.borderRadius = '10px';
    toast.style.fontSize = '14px';
    toast.style.fontWeight = '500';
    toast.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.15)';
    toast.style.maxWidth = '360px';
    toast.style.background = '#fef2f2';
    toast.style.color = '#991b1b';
    toast.style.border = '1px solid #fecaca';
    toast.style.animation = 'toastIn 0.3s ease';
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'toastOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}
</script>

<style>
@keyframes toastIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes toastOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(20px); }
}
</style>
@endsection
