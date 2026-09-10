@extends('layouts.app')
@section('content')
<div class="shift-container">
    <div class="shift-header">
        <h1>Shift History</h1>
        <p>View all past till closures and shift records</p>
    </div>

    <div class="shift-card">
        <div class="shift-info">
            <h2>{{ $till->name }}</h2>
            <p>Till Closure History</p>
        </div>

        @if($closures->count() > 0)
            <div class="shift-list">
                @foreach($closures as $closure)
                    <div class="shift-item" onclick="window.location.href='{{ route('cashier.shift-show', $closure) }}'">
                        <div class="shift-item-header">
                            <div class="shift-id">#{{ $closure->id }}</div>
                            <div class="shift-date">{{ $closure->opened_at->format('M j, Y') }}</div>
                        </div>

                        <div class="shift-item-details">
                            <div class="detail-row">
                                <span>Opened:</span>
                                <strong>{{ $closure->opened_at->format('g:i A') }}</strong>
                            </div>

                            <div class="detail-row">
                                <span>Closed:</span>
                                <strong>{{ $closure->closed_at ? $closure->closed_at->format('g:i A') : 'Open' }}</strong>
                            </div>

                            <div class="detail-row">
                                <span>By:</span>
                                <strong>{{ $closure->user ? $closure->user->name : 'Unknown' }}</strong>
                            </div>

                            <div class="detail-row highlight">
                                <span>Expected:</span>
                                <strong>Rs. {{ number_format($closure->expected_balance, 2) }}</strong>
                            </div>

                            <div class="detail-row highlight">
                                <span>Counted:</span>
                                <strong>Rs. {{ number_format($closure->counted_balance, 2) }}</strong>
                            </div>

                            <div class="detail-row">
                                <span>Total Sales:</span>
                                <strong>Rs. {{ number_format($closure->total_sales, 2) }}</strong>
                            </div>

                            <div class="detail-row {{ $closure->discrepancy == 0 ? 'match' : ($closure->discrepancy > 0 ? 'overage' : 'shortage') }}">
                                <span>Discrepancy:</span>
                                <strong>
                                    @if($closure->discrepancy == 0)
                                        None
                                    @elseif($closure->discrepancy > 0)
                                        +Rs. {{ number_format($closure->discrepancy, 2) }}
                                    @else
                                        -Rs. {{ number_format(abs($closure->discrepancy), 2) }}
                                    @endif
                                </strong>
                            </div>
                        </div>

                        <div class="shift-item-footer">
                            <div class="cash-sales">
                                <span>Cash Sales:</span>
                                <strong>Rs. {{ number_format($closure->cash_sales, 2) }}</strong>
                            </div>
                            <div class="action-arrow">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @php $paginationThreshold = request()->isMobile() ? 10 : 50; @endphp
            @if($closures->total() > $paginationThreshold)
            <div class="pagination-wrap">
                {{ $closures->links() }}
            </div>
            @endif
        @else
            <div class="empty-state">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3>No shift history found</h3>
                <p>Shift closures will appear here once you start closing shifts</p>
            </div>
        @endif

        <div class="back-link">
            <a href="{{ route('cashier.index') }}" class="btn-secondary">
                Back to Cashier
            </a>
        </div>
    </div>
</div>

<style>
.shift-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.shift-header {
    margin-bottom: 24px;
}

.shift-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.shift-header p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.shift-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 32px;
}

.shift-info {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.shift-info h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.shift-info p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.shift-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.shift-item {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.shift-item:hover {
    background: white;
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

.shift-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.shift-id {
    font-size: 14px;
    font-weight: 700;
    color: #3b82f6;
}

.shift-date {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.shift-item-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.detail-row {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.detail-row span {
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
}

.detail-row strong {
    font-size: 13px;
    color: #1e293b;
    font-weight: 600;
}

.detail-row.highlight {
    background: white;
    border-radius: 6px;
    padding: 8px;
}

.detail-row.highlight strong {
    color: #15803d;
    font-size: 14px;
}

.detail-row.match strong {
    color: #15803d;
}

.detail-row.overage strong {
    color: #f59e0b;
}

.detail-row.shortage strong {
    color: #ef4444;
}

.shift-item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
}

.cash-sales {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.cash-sales span {
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
}

.cash-sales strong {
    font-size: 14px;
    color: #10b981;
    font-weight: 600;
}

.action-arrow {
    color: #cbd5e1;
    transition: all 0.2s ease;
}

.shift-item:hover .action-arrow {
    color: #3b82f6;
    transform: translateX(4px);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 40px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px dashed #cbd5e1;
    margin-bottom: 24px;
}

.empty-state svg {
    color: #cbd5e1;
    margin-bottom: 16px;
}

.empty-state h3 {
    font-size: 18px;
    color: #64748b;
    margin: 0 0 8px 0;
}

.empty-state p {
    font-size: 14px;
    color: #94a3b8;
    margin: 0;
    text-align: center;
}

.pagination-wrap {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.pagination-wrap nav {
    display: flex;
    justify-content: center;
}

.pagination-wrap .pagination,
.pagination-wrap nav > div {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination-wrap a,
.pagination-wrap span {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none !important;
    color: #374151;
    background: #fff;
    border: 1px solid #e5e7eb;
    transition: all 0.15s ease;
    line-height: 1;
}

.pagination-wrap a:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
}

.pagination-wrap span[aria-current="page"],
.pagination-wrap .active span,
.pagination-wrap [aria-current="page"] span {
    background: #111827 !important;
    color: #fff !important;
    border-color: #111827 !important;
    font-weight: 600;
}

.pagination-wrap span[aria-disabled="true"],
.pagination-wrap .disabled span {
    color: #9ca3af !important;
    background: #f9fafb !important;
    border-color: #e5e7eb !important;
    cursor: not-allowed;
    opacity: 0.7;
}

.pagination-wrap svg,
.pagination-wrap .pagination svg,
nav[role="navigation"] svg {
    width: 16px !important;
    height: 16px !important;
    max-width: 16px !important;
    max-height: 16px !important;
}

.pagination-wrap a[rel="prev"],
.pagination-wrap a[rel="next"] {
    font-weight: 500;
    padding: 0 14px;
}

/* Responsive pagination for mobile */
@media (max-width: 768px) {
    .pagination-wrap {
        margin-top: 20px;
        gap: 8px;
    }

    .pagination-wrap .pagination,
    .pagination-wrap nav > div {
        gap: 4px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-wrap a,
    .pagination-wrap span {
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        font-size: 13px;
        border-radius: 8px;
    }

    .pagination-wrap a[rel="prev"],
    .pagination-wrap a[rel="next"] {
        padding: 0 10px;
        font-size: 12px;
    }

    .pagination-wrap svg,
    .pagination-wrap .pagination svg,
    nav[role="navigation"] svg {
        width: 14px !important;
        height: 14px !important;
        max-width: 14px !important;
        max-height: 14px !important;
    }

    /* Hide some page numbers on very small screens */
    @media (max-width: 480px) {
        .pagination-wrap .pagination {
            gap: 2px;
        }

        .pagination-wrap a,
        .pagination-wrap span {
            min-width: 28px;
            height: 28px;
            padding: 0 6px;
            font-size: 12px;
        }

        .pagination-wrap a[rel="prev"],
        .pagination-wrap a[rel="next"] {
            padding: 0 8px;
            font-size: 11px;
        }
    }
}

.back-link {
    display: flex;
    justify-content: flex-end;
}

.btn-secondary {
    padding: 10px 20px;
    background: white;
    color: #64748b;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}

.btn-secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
</style>
@endsection