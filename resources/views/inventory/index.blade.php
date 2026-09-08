@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Item Master</h1>
        <p>Stock, valuation and traceable movements.</p>
    </div>
    <div style="display:flex;gap:12px;align-items:center;">
        <input type="text" id="inventorySearch" placeholder="Search products..." oninput="filterInventory()" style="padding:10px 14px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;width:250px;">
        <a class="primary" href="{{ route('inventory.create') }}">+ New Product</a>
    </div>
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
                    {{ $item['product']->name }} ({{ number_format($item['current'], 3) }} / {{ number_format($item['minimum'], 3) }}){{ !$loop->last ? ', ' : '' }}
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
                <th style="min-width:320px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i)
            @php
                $quantity = (float) $i->quantity;
                $reserved = (float) ($i->reserved_quantity ?? 0);
                $available = max(0, $quantity - $reserved);
            @endphp
            <tr data-product-id="{{ $i->product->id }}">
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
                    @if($available == 0)
                        <span style="color:#dc2626;font-weight:bold;">Out of Stock</span>
                    @elseif($available <= $i->product->minimum_stock)
                        <span style="color:#dc2626;font-weight:bold;">{{ number_format($available, 3) }}</span>
                    @else
                        {{ number_format($available, 3) }}
                    @endif
                </td>
                <td>{{ $i->product->minimum_stock }}</td>
                <td>Rs. {{ number_format($i->product->selling_price,2) }}</td>
                <td>
                    <div class="actions-row">
                        <form method="post" action="{{ route('inventory.adjust',$i->product) }}" class="add-stock-form">
                            @csrf
                            <input 
                                name="quantity" 
                                type="number" 
                                step="0.001" 
                                min="0" 
                                placeholder="Qty"
                                required
                            >
                            <input 
                                name="reason" 
                                type="text" 
                                placeholder="Reason"
                            >
                            <button type="submit" class="btn-add">+ Add</button>
                        </form>

                        <a href="{{ route('inventory.edit',$i->product) }}" class="btn-edit">Edit</a>
                        <button type="button" class="btn-delete" onclick="showDeleteModal('{{ $i->product->id }}')">Delete</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="inventory-cards">
        @forelse($items as $i)
        @php
            $quantity = (float) $i->quantity;
            $reserved = (float) ($i->reserved_quantity ?? 0);
            $available = max(0, $quantity - $reserved);
        @endphp
        <div class="inventory-card" data-product-id="{{ $i->product->id }}">
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
                            <span style="color:#dc2626;font-weight:600;">{{ number_format($available, 3) }}</span>
                        @else
                            {{ number_format($available, 3) }}
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

            {{-- Add Stock on mobile --}}
            <form method="post" action="{{ route('inventory.adjust',$i->product) }}" class="mobile-add-stock">
                @csrf
                <input name="quantity" type="number" step="0.001" min="0" placeholder="Qty to add" required>
                <input name="reason" type="text" placeholder="Reason (optional)">
                <button type="submit">+ Add Stock</button>
            </form>

            <div class="card-actions">
                <a href="{{ route('inventory.edit',$i->product) }}" class="btn-edit">Edit</a>
                <button class="btn-delete" onclick="showDeleteModal('{{ $i->product->id }}')">Delete</button>
            </div>
        </div>
        @empty
        <div class="empty-state">No products found.</div>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $items->links() }}
    </div>
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

// Real-time inventory status updates
let inventoryStatusInterval = null;

function startInventoryStatusPolling() {
    if (inventoryStatusInterval) {
        clearInterval(inventoryStatusInterval);
    }
    
    console.log('Starting inventory status polling...');
    // Poll every 5 seconds for inventory updates
    inventoryStatusInterval = setInterval(updateInventoryStatus, 5000);
}

function stopInventoryStatusPolling() {
    if (inventoryStatusInterval) {
        clearInterval(inventoryStatusInterval);
        inventoryStatusInterval = null;
        console.log('Stopped inventory status polling');
    }
}

async function updateInventoryStatus() {
    console.log('Fetching inventory status...');
    try {
        // Build the URL manually to ensure it's correct
        const currentPath = window.location.pathname;
        const tenant = currentPath.split('/')[1];
        const url = `/${tenant}/inventory/status`;
        
        console.log('Fetching from URL:', url);
        console.log('Current path:', currentPath);
        console.log('Tenant:', tenant);
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            console.error('Response not OK:', response.status, response.statusText);
            const errorText = await response.text();
            console.error('Error response:', errorText);
            
            // If we get a 500 error, stop polling to avoid spamming
            if (response.status === 500) {
                console.error('Server error, stopping polling');
                stopInventoryStatusPolling();
            }
            return;
        }
        
        const data = await response.json();
        console.log('Inventory status data:', data);
        
        if (data.error) {
            console.error('API returned error:', data.error);
            stopInventoryStatusPolling();
            return;
        }
        
        if (data.inventory_status) {
            updateInventoryDisplay(data.inventory_status, data.low_stock_count);
        }
    } catch (error) {
        console.error('Error fetching inventory status:', error);
        // Stop polling on network errors
        stopInventoryStatusPolling();
    }
}

function updateInventoryDisplay(inventoryStatus, lowStockCount) {
    console.log('Updating inventory display with', inventoryStatus.length, 'items');
    
    // Create a map for quick lookup
    const inventoryMap = {};
    inventoryStatus.forEach(item => {
        inventoryMap[item.product_id] = item;
    });
    
    // Update desktop table rows
    const tableRows = document.querySelectorAll('.inventory-table tbody tr');
    tableRows.forEach(row => {
        const productId = row.getAttribute('data-product-id');
        if (!productId) return;
        
        const inventoryItem = inventoryMap[parseInt(productId)];
        
        if (inventoryItem) {
            const stockCell = row.querySelector('td:nth-child(5)');
            if (stockCell) {
                const available = inventoryItem.available;
                const minimum = inventoryItem.minimum_stock;
                
                if (available == 0) {
                    stockCell.innerHTML = '<span style="color:#dc2626;font-weight:bold;">Out of Stock</span>';
                } else if (available <= minimum) {
                    stockCell.innerHTML = `<span style="color:#dc2626;font-weight:bold;">${number_format(available, 3)}</span>`;
                } else {
                    stockCell.textContent = number_format(available, 3);
                }
            }
        }
    });
    
    // Update mobile cards
    const mobileCards = document.querySelectorAll('.inventory-card');
    mobileCards.forEach(card => {
        const productId = card.getAttribute('data-product-id');
        if (!productId) return;
        
        const inventoryItem = inventoryMap[parseInt(productId)];
        
        if (inventoryItem) {
            const stockValue = card.querySelector('.card-details .detail:nth-child(1) .value');
            if (stockValue) {
                const available = inventoryItem.available;
                const minimum = inventoryItem.minimum_stock;
                
                if (available == 0) {
                    stockValue.innerHTML = '<span style="color:#dc2626;font-weight:600;">Out of Stock</span>';
                } else if (available <= minimum) {
                    stockValue.innerHTML = `<span style="color:#dc2626;font-weight:600;">${number_format(available, 3)}</span>`;
                } else {
                    stockValue.textContent = number_format(available, 3);
                }
            }
        }
    });
    
    // Update low stock alert
    const lowStockAlert = document.querySelector('.alert-danger');
    if (lowStockAlert) {
        if (lowStockCount > 0) {
            lowStockAlert.style.display = 'flex';
            const alertTitle = lowStockAlert.querySelector('strong');
            if (alertTitle) {
                alertTitle.textContent = `Low Stock Alert: ${lowStockCount} product(s) need attention`;
            }
            
            const alertDetails = lowStockAlert.querySelector('p');
            if (alertDetails) {
                const lowStockItems = inventoryStatus.filter(item => item.is_low_stock).slice(0, 3);
                alertDetails.innerHTML = lowStockItems.map(item => 
                    `${item.name} (${number_format(item.available, 3)} / ${number_format(item.minimum_stock, 3)})`
                ).join(', ') + (lowStockCount > 3 ? ` and ${lowStockCount - 3} more...` : '');
            }
        } else {
            lowStockAlert.style.display = 'none';
        }
    }
    
    console.log('Inventory display updated');
}

// Helper function for number formatting
function number_format(number, decimals) {
    return parseFloat(number).toFixed(decimals);
}

// Start polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    startInventoryStatusPolling();
    // Initial update
    updateInventoryStatus();
});

// Stop polling when page is hidden
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopInventoryStatusPolling();
    } else {
        startInventoryStatusPolling();
        updateInventoryStatus();
    }
});

// Filter inventory items by search
function filterInventory() {
    const query = document.getElementById('inventorySearch').value.toLowerCase().trim();
    const tableRows = document.querySelectorAll('.inventory-table tbody tr');
    const mobileCards = document.querySelectorAll('.inventory-card');

    // Filter desktop table rows
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });

    // Filter mobile cards
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(query) ? '' : 'none';
    });
}
</script>

<style>
/* ========== DESKTOP ========== */
.inventory-table {
    width: 100%;
    border-collapse: collapse;
}

.inventory-cards {
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

/* Single row for everything */
.actions-row {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
}

.add-stock-form {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
}

.add-stock-form input[name="quantity"] {
    width: 70px;
    padding: 7px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
}

.add-stock-form input[name="reason"] {
    width: 100px;
    padding: 7px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
}

.btn-add {
    padding: 7px 12px;
    background: #16a34a;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
}

.btn-add:hover {
    background: #15803d;
}

.btn-edit {
    padding: 7px 12px;
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
}

.btn-delete {
    padding: 7px 12px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
}

/* ========== MOBILE ========== */
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

    .inventory-table {
        display: none;
    }

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

    /* Mobile Add Stock */
    .mobile-add-stock {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    .mobile-add-stock input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .mobile-add-stock button {
        width: 100%;
        padding: 10px;
        background: #16a34a;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
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