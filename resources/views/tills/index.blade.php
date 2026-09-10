@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Till Management</h1>
        <p>Manage multiple tills for different workstations</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a class="secondary" href="{{ route('tills.user-assignments') }}">User Assignments</a>
        <a class="primary" href="{{ route('tills.create') }}">+ New Till</a>
    </div>
</div>

<div class="panel">
    <!-- Desktop List -->
    <div class="tills-list">
        @forelse($tills as $till)
            <div class="listrow">
                <div style="flex: 1;">
                    <b>{{ $till->name }}</b>
                    <span>{{ $till->code }}</span>
                    @if($till->location)
                        <span style="color: #6b7280;">{{ $till->location }}</span>
                    @endif
                    @if($till->is_active)
                        <span style="background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Active</span>
                    @else
                        <span style="background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Inactive</span>
                    @endif
                    @if($till->isInUse())
                        @if($till->current_user_id == auth()->id())
                            <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Your Till</span>
                        @else
                            <span style="background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 4px; font-size: 12px;">In Use by {{ $till->currentUser->name ?? 'Unknown' }}</span>
                        @endif
                    @endif
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('tills.show', $till) }}" class="secondary">View</a>
                    <a href="{{ route('tills.edit', $till) }}" class="secondary">Edit</a>
                    @if(auth()->user()->hasPermissionTo('settings.access'))
                        <form method="POST" action="{{ route('tills.destroy', $till) }}" onsubmit="return confirm('Are you sure you want to delete this till? This action cannot be undone.');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="secondary" style="color: #dc2626; border-color: #dc2626;">Delete</button>
                        </form>
                    @else
                        @if(!$till->isOpen() && !$till->isInUse())
                            <form method="POST" action="{{ route('tills.destroy', $till) }}" onsubmit="return confirm('Are you sure you want to delete this till? This action cannot be undone.');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="secondary" style="color: #dc2626; border-color: #dc2626;">Delete</button>
                            </form>
                        @elseif($till->isOpen())
                            <span style="color: #f59e0b; font-size: 13px; padding: 4px 8px; background: #fef3c7; border-radius: 4px;">Shift Open</span>
                        @elseif($till->isInUse())
                            <span style="color: #6b7280; font-size: 13px; padding: 4px 8px; background: #f3f4f6; border-radius: 4px;">In Use</span>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <p class="empty">No tills found. Create your first till.</p>
        @endforelse
    </div>

    <!-- Mobile Cards -->
    <div class="tills-cards">
        @forelse($tills as $till)
            <div class="till-card">
                <div class="card-top">
                    <div class="card-name">
                        <strong>{{ $till->name }}</strong>
                        <small>{{ $till->code }}</small>
                    </div>
                    @if($till->is_active)
                        <span class="active-badge">Active</span>
                    @else
                        <span class="inactive-badge">Inactive</span>
                    @endif
                </div>

                @if($till->location)
                    <div class="card-info">
                        <span class="info-label">Location:</span>
                        <span class="info-value">{{ $till->location }}</span>
                    </div>
                @endif

                @if($till->ip_address)
                    <div class="card-info">
                        <span class="info-label">IP:</span>
                        <span class="info-value">{{ $till->ip_address }}</span>
                    </div>
                @endif

                <div class="card-info">
                    <span class="info-label">Balance:</span>
                    <span class="info-value">Rs. {{ number_format($till->opening_balance, 2) }}</span>
                </div>

                @if($till->isOpen())
                    <div class="shift-status">
                        <span style="color: #f59e0b;">⚠ Shift Open</span>
                    </div>
                @endif

                @if($till->isInUse())
                    <div class="in-use-status">
                        @if($till->current_user_id == auth()->id())
                            <span style="color: #1e40af;">✓ Your Till</span>
                        @else
                            <span style="color: #d97706;">⚠ In Use by {{ $till->currentUser->name ?? 'Unknown' }}</span>
                        @endif
                    </div>
                @endif

                <div class="card-actions">
                    <a href="{{ route('tills.show', $till) }}" class="btn-secondary">View</a>
                    <a href="{{ route('tills.edit', $till) }}" class="btn-secondary">Edit</a>
                    @if(auth()->user()->hasPermissionTo('settings.access'))
                        <form method="POST" action="{{ route('tills.destroy', $till) }}" onsubmit="return confirm('Are you sure you want to delete this till? This action cannot be undone.');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary" style="color: #dc2626; border-color: #dc2626;">Delete</button>
                        </form>
                    @else
                        @if(!$till->isOpen() && !$till->isInUse())
                            <form method="POST" action="{{ route('tills.destroy', $till) }}" onsubmit="return confirm('Are you sure you want to delete this till? This action cannot be undone.');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary" style="color: #dc2626; border-color: #dc2626;">Delete</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">No tills found. Create your first till.</div>
        @endforelse
    </div>

    @php $paginationThreshold = request()->isMobile() ? 10 : 50; @endphp
    @if($tills->total() > $paginationThreshold)
    <div class="pagination-wrap">
        {{ $tills->links() }}
    </div>
    @endif
</div>

<style>
/* Desktop list stays normal */
.tills-list {
    display: block;
}

.listrow button[type="submit"] {
    background: #fef2f2;
    color: #dc2626;
    border-color: #dc2626;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
}

.listrow button[type="submit"]:hover {
    background: #fee2e2;
}

.tills-cards {
    display: none;
}

/* Pagination styling */
.pagination-wrap {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
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

.till-selection {
    margin-bottom: 20px;
}

/* ========== MOBILE ONLY ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head a.primary {
        width: 100%;
        text-align: center;
    }

    /* Hide desktop list */
    .tills-list {
        display: none;
    }

    /* Show cards */
    .tills-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }

    .till-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .card-name strong {
        display: block;
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }

    .card-name small {
        font-size: 12px;
        color: #6b7280;
    }

    .active-badge {
        background: #dcfce7;
        color: #16a34a;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
    }

    .inactive-badge {
        background: #fee2e2;
        color: #dc2626;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
    }

    .card-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 13px;
    }

    .info-label {
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        color: #374151;
    }

    .shift-status {
        background: #fef3c7;
        padding: 8px;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 12px;
        font-size: 13px;
    }

    .in-use-status {
        background: #eff6ff;
        padding: 8px;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 12px;
        font-size: 13px;
    }

    .card-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .card-actions .btn-secondary,
    .card-actions button {
        flex: 1;
        text-align: center;
        padding: 8px 10px;
        border-radius: 8px;
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        min-width: 60px;
    }

    .card-actions button[type="submit"] {
        color: #dc2626;
        border-color: #dc2626;
        background: #fef2f2;
    }

    .empty-state {
        text-align: center;
        padding: 30px 16px;
        color: #9ca3af;
        font-size: 14px;
        grid-column: 1 / -1;
    }
}
</style>
@endsection