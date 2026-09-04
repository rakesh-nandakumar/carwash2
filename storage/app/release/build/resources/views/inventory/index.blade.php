@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Item Master</h1>
        <p>Stock, valuation and traceable movements.</p>
    </div>
    <a class="primary" href="{{ route('inventory.create') }}">+ New Product</a>
</div>

@if($lowStockItems->count() > 0)
<div class="alert alert-danger" style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div>
            <strong style="color:#dc2626;">Low Stock Alert: {{ $lowStockItems->count() }} product(s) need attention</strong>
            <p style="margin:4px 0 0 0;color:#991b1b;font-size:14px;">
                @foreach($lowStockItems->take(3) as $item)
                    {{ $item->product->name }} ({{ $item->quantity }} / {{ $item->product->minimum_stock }}){{ !$loop->last ? ', ' : '' }}
                @endforeach
                @if($lowStockItems->count() > 3)
                    and {{ $lowStockItems->count() - 3 }} more...
                @endif
            </p>
        </div>
    </div>
</div>
@endif

<div class="panel">
    <!-- Desktop Table -->
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>SKU</th>
                <th>Product</th>
                <th>Brand</th>
                <th>Stock</th>
                <th>Min</th>
                <th>Sell Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i)
            <tr>
                <td>
                    @if($i->product->image && file_exists(storage_path('app/public/'.$i->product->image)))
                        <img src="{{ asset('storage/'.$i->product->image) }}" alt="{{ $i->product->name }}" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                    @else
                        <span style="color:#9ca3af;">No image</span>
                    @endif
                </td>
                <td>{{ $i->product->sku }}</td>
                <td><b>{{ $i->product->name }}</b></td>
                <td>{{ $i->product->brand }}</td>
                <td>
                    @php
                        $available = max(0, $i->quantity - ($i->reserved_quantity ?? 0));
                    @endphp
                    @if($available == 0)
                        <span style="color:#dc2626;font-weight:bold;">Out of Stock</span>
                    @elseif($available <= $i->product->minimum_stock)
                        <span style="color:#dc2626;font-weight:bold;">{{ $available }}</span>
                    @else
                        {{ $available }}
                    @endif
                </td>
                <td>{{ $i->product->minimum_stock }}</td>
                <td>Rs. {{ number_format($i->product->selling_price,2) }}</td>
                <td>
                    <form class="inline-form" method="post" action="{{ route('inventory.adjust',$i->product) }}">
                        @csrf
                        <input name="quantity" type="number" step=".001" placeholder="+/- qty">
                        <input name="reason" placeholder="Reason">
                        <button>Adjust</button>
                    </form>
                    <a href="{{ route('inventory.edit',$i->product) }}" class="secondary">Edit</a>
                    <button class="danger" style="padding:8px 16px;border-radius:6px;border:none;background:#ef4444;color:white;cursor:pointer;font-size:14px;" onclick="showDeleteModal('{{ $i->product->id }}')">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="inventory-cards">
        @forelse($items as $i)
        @php
            $available = max(0, $i->quantity - ($i->reserved_quantity ?? 0));
        @endphp
        <div class="inventory-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $i->product->name }}</strong>
                    <small>{{ $i->product->sku }} @if($i->product->brand) · {{ $i->product->brand }} @endif</small>
                </div>
            </div>

            <div class="card-details">
                <div class="detail">
                    <span class="label">Stock</span>
                    <span class="value">
                        @if($available == 0)
                            <span style="color:#dc2626;font-weight:600;">Out of Stock</span>
                        @elseif($available <= $i->product->minimum_stock)
                            <span style="color:#dc2626;font-weight:600;">{{ $available }}</span>
                        @else
                            {{ $available }}
                        @endif
                    </span>
                </div>
                <div class="detail">
                    <span class="label">Min</span>
                    <span class="value">{{ $i->product->minimum_stock }}</span>
                </div>
                <div class="detail">
                    <span class="label">Sell Price</span>
                    <span class="value">Rs. {{ number_format($i->product->selling_price,2) }}</span>
                </div>
            </div>

            <div class="card-actions">
                <a href="{{ route('inventory.edit',$i->product) }}" class="btn-edit">Edit</a>
                <button class="btn-delete" onclick="showDeleteModal('{{ $i->product->id }}')">Delete</button>
            </div>
        </div>
        @empty
        <div class="empty-state">No products found.</div>
        @endforelse
    </div>

    {{ $items->links() }}
</div>

{{-- Delete Modal --}}
<div id="deleteModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:1000;">
    <div class="modal-content" style="background:white;border-radius:12px;width:90%;max-width:400px;padding:24px;">
        <div class="modal-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;font-size:18px;">Confirm Delete</h3>
            <button class="modal-close" onclick="closeDeleteModal()" style="background:none;border:none;font-size:24px;cursor:pointer;">✕</button>
        </div>
        <div class="modal-body" style="margin-bottom:20px;">
            <p style="margin:0;color:#374151;">Are you sure you want to delete this product? This action cannot be undone.</p>
        </div>
        <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:12px;">
            <button type="button" onclick="closeDeleteModal()" style="padding:8px 16px;border-radius:6px;border:1px solid #d1d5db;background:white;color:#374151;cursor:pointer;font-size:14px;">No</button>
            <button type="button" onclick="confirmDelete()" style="padding:8px 16px;border-radius:6px;border:none;background:#ef4444;color:white;cursor:pointer;font-size:14px;">Yes</button>
        </div>
    </div>
</div>

<form id="deleteForm" method="post" action="" style="display:none">
    @method('DELETE')
    @csrf
</form>

<script>
let deleteProductId = null;

function showDeleteModal(id) {
    deleteProductId = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    deleteProductId = null;
    document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete() {
    if (deleteProductId) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route('inventory.destroy', ':id') }}'.replace(':id', deleteProductId);
        form.submit();
    }
    closeDeleteModal();
}
</script>

<style>
/* Desktop table stays normal */
.inventory-table {
    width: 100%;
    border-collapse: collapse;
}

.inventory-cards {
    display: none;
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

    /* Hide the normal table */
    .inventory-table {
        display: none;
    }

    /* Show cards – auto-fit makes 1 or 2 cards stretch full width */
    .inventory-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .inventory-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .card-top {
        margin-bottom: 12px;
    }

    .card-name strong {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }

    .card-name small {
        font-size: 12px;
        color: #6b7280;
    }

    .card-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 12px;
        margin-bottom: 14px;
    }

    .detail {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .detail .label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .detail .value {
        font-size: 13.5px;
        font-weight: 500;
        color: #1f2937;
    }

    .card-actions {
        display: flex;
        gap: 8px;
    }

    .card-actions .btn-edit {
        flex: 1;
        text-align: center;
        padding: 8px 12px;
        border-radius: 8px;
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid #e5e7eb;
    }

    .card-actions .btn-delete {
        flex: 1;
        padding: 8px 12px;
        border-radius: 8px;
        background: #ef4444;
        color: white;
        border: none;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
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