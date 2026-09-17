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
    <!-- Desktop Table -->
    <table class="grns-table">
        <thead>
            <tr>
                <th>Return GRN Number</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Reason</th>
                <th>Items</th>
                <th>Total Cost</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($returnGrns as $returnGrn)
                <tr>
                    <td>
                        <a href="{{ route('return_grns.show', $returnGrn) }}">
                            <b>{{ $returnGrn->return_grn_number }}</b>
                        </a>
                        @if($returnGrn->reference)
                            <small>{{ $returnGrn->reference }}</small>
                        @endif
                    </td>
                    <td>{{ $returnGrn->returned_at ? (is_string($returnGrn->returned_at) ? $returnGrn->returned_at : $returnGrn->returned_at->format('d/m/Y H:i')) : '-' }}</td>
                    <td>{{ $returnGrn->supplier ? $returnGrn->supplier->name : '-' }}</td>
                    <td>{{ $returnGrn->reason ?? '-' }}</td>
                    <td>{{ $returnGrn->items ? $returnGrn->items->count() : 0 }}</td>
                    <td>Rs. {{ number_format($returnGrn->items ? $returnGrn->items->sum(function($item) {
                        return ($item->unit_cost ?? 0) * $item->quantity;
                    }) : 0, 2) }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($returnGrn->status_key ?? 'draft') }}">
                            {{ $returnGrn->status_display ?? 'Draft' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('return_grns.show', $returnGrn) }}" class="view-link">View →</a>
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

<style>
.grns-table {
    width: 100%;
    border-collapse: collapse;
    display: table;
}

.grns-table th,
.grns-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.grns-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.grns-table tr:hover {
    background: #f9fafb;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-draft {
    background: #fef3c7;
    color: #92400e;
}

.status-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-deleted {
    background: #fee2e2;
    color: #991b1b;
}

.status-return_draft {
    background: #fef3c7;
    color: #92400e;
}

.status-return_confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-return_deleted {
    background: #fee2e2;
    color: #991b1b;
}
</style>
@endsection
