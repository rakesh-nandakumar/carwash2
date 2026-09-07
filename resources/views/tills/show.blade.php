@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $till->name }}</h1>
        <p>Till Details: {{ $till->code }}</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a class="secondary" href="{{ route('tills.edit', $till) }}">Edit</a>
        <a class="secondary" href="{{ route('tills.index') }}">← Back to Tills</a>
    </div>
</div>

<div class="panel">
    <div class="till-details">
        <div class="detail-section">
            <h3>Till Information</h3>
            <div class="detail-row">
                <span class="label">Name:</span>
                <span class="value">{{ $till->name }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Code:</span>
                <span class="value">{{ $till->code }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value">
                    @if($till->is_active)
                        <span style="background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Active</span>
                    @else
                        <span style="background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Inactive</span>
                    @endif
                </span>
            </div>
            @if($till->location)
                <div class="detail-row">
                    <span class="label">Location:</span>
                    <span class="value">{{ $till->location }}</span>
                </div>
            @endif
            @if($till->ip_address)
                <div class="detail-row">
                    <span class="label">IP Address:</span>
                    <span class="value">{{ $till->ip_address }}</span>
                </div>
            @endif
            <div class="detail-row">
                <span class="label">Opening Balance:</span>
                <span class="value">Rs. {{ number_format($till->opening_balance, 2) }}</span>
            </div>
            @if($till->description)
                <div class="detail-row">
                    <span class="label">Description:</span>
                    <span class="value">{{ $till->description }}</span>
                </div>
            @endif
            <div class="detail-row">
                <span class="label">Current Status:</span>
                <span class="value">
                    @if($till->isOpen())
                        <span style="background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Shift Open</span>
                    @else
                        <span style="background: #e5e7eb; color: #374151; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Shift Closed</span>
                    @endif
                </span>
            </div>
        </div>

        @if($till->closures && $till->closures->count() > 0)
            <div class="detail-section">
                <h3>Recent Shift Closures</h3>
                <div class="closures-list">
                    @foreach($till->closures as $closure)
                        <div class="closure-item">
                            <div class="closure-header">
                                <strong>{{ $closure->opened_at->format('M d, Y - g:i A') }}</strong>
                                @if($closure->closed_at)
                                    <span>to {{ $closure->closed_at->format('g:i A') }}</span>
                                @else
                                    <span style="color: #d97706;">(Open)</span>
                                @endif
                            </div>
                            <div class="closure-details">
                                <span>Opening: Rs. {{ number_format($closure->opening_balance, 2) }}</span>
                                <span>Sales: Rs. {{ number_format($closure->total_sales, 2) }}</span>
                                @if($closure->closed_at)
                                    <span>Counted: Rs. {{ number_format($closure->counted_balance, 2) }}</span>
                                    @if($closure->discrepancy != 0)
                                        <span style="color: {{ $closure->discrepancy > 0 ? '#16a34a' : '#dc2626' }};">
                                            {{ $closure->discrepancy > 0 ? '+' : '' }}Rs. {{ number_format($closure->discrepancy, 2) }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                            @if($closure->user)
                                <div class="closure-user">
                                    Staff: {{ $closure->user->name }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.till-details {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.detail-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.detail-section h3 {
    margin: 0 0 16px 0;
    font-size: 18px;
    color: #111827;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    font-weight: 500;
    color: #6b7280;
}

.detail-row .value {
    color: #374151;
}

.closures-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.closure-item {
    background: white;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.closure-header {
    font-weight: 500;
    margin-bottom: 8px;
    color: #111827;
}

.closure-details {
    display: flex;
    gap: 16px;
    font-size: 13px;
    color: #6b7280;
    flex-wrap: wrap;
}

.closure-user {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 4px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .detail-row {
        flex-direction: column;
        gap: 4px;
    }

    .detail-row .label {
        font-weight: 600;
    }

    .closure-details {
        flex-direction: column;
        gap: 4px;
    }
}
</style>
@endsection