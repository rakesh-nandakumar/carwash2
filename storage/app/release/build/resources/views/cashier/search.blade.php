@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Search Results</h1>
        <p>Search results for: "{{ $query }}"</p>
    </div>
    <a class="secondary" href="{{ route('cashier.index') }}">Back to Dashboard</a>
</div>

<div class="panel">
    @forelse($jobs as $job)
        <div class="listrow" style="cursor: pointer;" onclick="window.location.href='{{ route('cashier.payment', $job) }}'">
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
@endsection
