@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Return GRNs</h1>
        <p>Items returned to suppliers.</p>
    </div>
    <div class="page-actions">
        <a class="primary" href="{{ route('return_grns.create') }}">+ New Return GRN</a>
    </div>
</div>

<div class="panel">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Return GRN Number</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Reason</th>
                    <th>Items</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returnGrns as $returnGrn)
                    <tr>
                        <td><strong>{{ $returnGrn->return_grn_number }}</strong></td>
                        <td>{{ $returnGrn->returned_at ? $returnGrn->returned_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $returnGrn->supplier ? $returnGrn->supplier->name : '-' }}</td>
                        <td>{{ $returnGrn->reason ?? '-' }}</td>
                        <td>{{ $returnGrn->items->count() }}</td>
                        <td>Rs. {{ number_format($returnGrn->items->sum(function($item) {
                            return ($item->unit_cost ?? 0) * $item->quantity;
                        }), 2) }}</td>
                        <td>
                            <span class="status-badge status-{{ $returnGrn->status_id == 56 ? 'draft' : ($returnGrn->status_id == 57 ? 'confirmed' : 'deleted') }}">
                                {{ $returnGrn->status?->value ?? 'Draft' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('return_grns.show', $returnGrn) }}">View →</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No return GRNs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.status-draft {
    background: #f59e0b;
    color: white;
}

.status-badge.status-confirmed {
    background: #10b981;
    color: white;
}

.status-badge.status-deleted {
    background: #ef4444;
    color: white;
}
</style>
@endsection
