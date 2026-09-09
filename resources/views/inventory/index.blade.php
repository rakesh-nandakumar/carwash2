@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Item Master</h1>
        <p>Stock, valuation and traceable movements.</p>
    </div>
    <a class="primary" href="{{ route('inventory.create') }}">+ New Product</a>
</div>

<div class="search">
    <input id="inventorySearch" placeholder="Search products..." oninput="filterInventory()">
    <button class="filter-btn" onclick="openFilterModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
    </button>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Filter Products</h2>
            <button class="modal-close" onclick="closeFilterModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="filter-section">
                <label>Product</label>
                <div class="searchable-dropdown" id="productDropdown">
                    <input type="hidden" id="productFilter" name="product" value="">
                    <input type="text" class="searchable-dropdown-input" id="productFilterInput" placeholder="Search or select product...">
                    <div class="searchable-dropdown-options"></div>
                </div>
            </div>
            <div class="filter-section">
                <label>Brand</label>
                <div class="searchable-dropdown" id="brandDropdown">
                    <input type="hidden" id="brandFilter" name="brand" value="">
                    <input type="text" class="searchable-dropdown-input" id="brandFilterInput" placeholder="Search or select brand...">
                    <div class="searchable-dropdown-options"></div>
                </div>
            </div>
            <div class="filter-section">
                <label>Stock Status</label>
                <select id="stockFilter" onchange="applyFilters()">
                    <option value="">All Stock</option>
                    <option value="in-stock">In Stock</option>
                    <option value="low-stock">Low Stock</option>
                    <option value="out-of-stock">Out of Stock</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="secondary" onclick="clearFilters()">Clear Filters</button>
            <button class="primary" onclick="applyAndCloseFilterModal()">Apply</button>
        </div>
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
    applyFilters();
}

function closeFilterModal() {
    document.getElementById('filterModal').style.display = 'none';
}

function applyAndCloseFilterModal() {
    applyFilters();
    closeFilterModal();
}

function applyFilters() {
    const query = document.getElementById('inventorySearch').value.toLowerCase().trim();
    const productFilter = document.getElementById('productFilter').value.toLowerCase().trim();
    const brandFilter = document.getElementById('brandFilter').value;
    const stockFilter = document.getElementById('stockFilter').value;
    const tableRows = document.querySelectorAll('.inventory-table tbody tr');
    const mobileCards = document.querySelectorAll('.inventory-card');

    // Filter desktop table rows
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const cells = row.querySelectorAll('td');
        const stockCell = row.querySelector('td:nth-child(5)');
        const productId = row.getAttribute('data-product-id');
        
        let matchesSearch = text.includes(query);
        let matchesProduct = true;
        let matchesBrand = true;
        let matchesStock = true;

        if (productFilter && productId) {
            matchesProduct = productId === productFilter;
        }

        if (brandFilter && cells.length >= 4) {
            const brandCell = cells[3];
            const brandText = brandCell.textContent.toLowerCase().trim();
            matchesBrand = brandText.includes(brandFilter.toLowerCase());
        }

        if (stockFilter && stockCell) {
            const stockText = stockCell.textContent.toLowerCase();
            if (stockFilter === 'out-of-stock') {
                matchesStock = stockText.includes('out of stock');
            } else if (stockFilter === 'low-stock') {
                matchesStock = !stockText.includes('out of stock') && (stockCell.querySelector('span[style*="#dc2626"]') !== null);
            } else if (stockFilter === 'in-stock') {
                matchesStock = !stockText.includes('out of stock') && stockCell.querySelector('span[style*="#dc2626"]') === null;
            }
        }

        row.style.display = (matchesSearch && matchesProduct && matchesBrand && matchesStock) ? '' : 'none';
    });

    // Filter mobile cards
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const stockValue = card.querySelector('.card-details .detail:nth-child(1) .value');
        const brandText = card.querySelector('.card-name small')?.textContent || '';
        const productId = card.getAttribute('data-product-id');
        
        // Extract brand from the small text (format: "SKU · Brand")
        let extractedBrand = '';
        if (brandText.includes('·')) {
            const parts = brandText.split('·');
            if (parts.length > 1) {
                extractedBrand = parts[1].trim().toLowerCase();
            }
        } else {
            extractedBrand = brandText.toLowerCase();
        }
        
        let matchesSearch = text.includes(query);
        let matchesProduct = true;
        let matchesBrand = true;
        let matchesStock = true;

        if (productFilter && productId) {
            matchesProduct = productId === productFilter;
        }

        if (brandFilter) {
            matchesBrand = extractedBrand.includes(brandFilter.toLowerCase());
        }

        if (stockFilter && stockValue) {
            const stockText = stockValue.textContent.toLowerCase();
            if (stockFilter === 'out-of-stock') {
                matchesStock = stockText.includes('out of stock');
            } else if (stockFilter === 'low-stock') {
                matchesStock = !stockText.includes('out of stock') && (stockValue.querySelector('span[style*="#dc2626"]') !== null);
            } else if (stockFilter === 'in-stock') {
                matchesStock = !stockText.includes('out of stock') && stockValue.querySelector('span[style*="#dc2626"]') === null;
            }
        }

        card.style.display = (matchesSearch && matchesProduct && matchesBrand && matchesStock) ? '' : 'none';
    });
}

let brandDropdown = null;
let productDropdown = null;

function openFilterModal() {
    document.getElementById('filterModal').style.display = 'flex';
    if (!brandDropdown) {
        loadBrandsForDropdown();
    }
    if (!productDropdown) {
        loadProductsForDropdown();
    }
}

async function loadBrandsForDropdown() {
    try {
        const response = await fetch('{{ route('inventory.brands') }}');
        const brands = await response.json();

        const dropdownContainer = document.getElementById('brandDropdown');
        if (dropdownContainer) {
            const brandData = Array.isArray(brands) ? brands.map(brand => ({
                id: brand,
                label: brand
            })) : [];

            brandDropdown = new SearchableDropdown(dropdownContainer, {
                data: brandData,
                searchThreshold: 0, // Show all options immediately
                onSelect: function(item) {
                    // Don't apply filters immediately - wait for Apply button
                }
            });
        }
    } catch (error) {
        console.error('Error loading brands:', error);
    }
}

async function loadProductsForDropdown() {
    try {
        const response = await fetch('{{ route('inventory.products') }}');
        const products = await response.json();

        const dropdownContainer = document.getElementById('productDropdown');
        if (dropdownContainer) {
            const productData = Array.isArray(products) ? products.map(product => ({
                id: product.id,
                label: product.label // API returns 'label', not 'name'
            })) : [];

            productDropdown = new SearchableDropdown(dropdownContainer, {
                data: productData,
                searchThreshold: 0, // Show all options immediately
                onSelect: function(item) {
                    // Don't apply filters immediately - wait for Apply button
                }
            });
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

function clearFilters() {
    document.getElementById('productFilter').value = '';
    document.getElementById('productFilterInput').value = '';
    document.getElementById('brandFilter').value = '';
    document.getElementById('brandFilterInput').value = '';
    document.getElementById('stockFilter').value = '';
    
    // Reset dropdown instances
    if (productDropdown) {
        productDropdown.setValue('', '');
    }
    if (brandDropdown) {
        brandDropdown.setValue('', '');
    }
    
    applyFilters();
}

// Close modal when clicking outside
document.getElementById('filterModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeFilterModal();
    }
});
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

/* Search bar styling */
.search {
    display: flex;
    gap: 8px;
    position: relative;
    align-items: center;
}

.search input {
    flex: 1;
    height: 42px;
    box-sizing: border-box;
    padding: 0 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
}

.filter-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 14px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    height: 38px;
    box-sizing: border-box;
}

.filter-btn:hover {
    background: #d1d5db;
    border-color: #6b7280;
    color: #111827;
}

.filter-btn:active {
    background: #0a1f33;
    border-color: #0a1f33;
    color: white;
    transform: translateY(1px);
}

.filter-btn svg {
    width: 16px;
    height: 16px;
}

/* Modal styling */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5000;
    padding: 20px;
}

.modal-box {
    background: rgba(10, 31, 51, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    width: 100%;
    max-width: 400px;
    max-height: 90vh;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 25px 60px rgba(0,0,0,.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
}

.modal-close {
    border: none;
    background: none;
    font-size: 28px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background 0.15s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-footer button {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.modal-footer .secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
}

.modal-footer .secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.modal-footer .primary {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.9);
    color: #0a1f33;
}

.modal-footer .primary:hover {
    background: #ffffff;
    border-color: #ffffff;
}

.filter-section {
    margin-bottom: 12px;
}

.filter-section:last-child {
    margin-bottom: 0;
}

.filter-section label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-section select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
    cursor: pointer;
}

.filter-section select:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.filter-section select option {
    background: #0a1f33;
    color: #ffffff;
}

.filter-section input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
}

.filter-section input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.filter-section input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.clear-filters {
    width: 100%;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    cursor: pointer;
    margin-top: 12px;
    transition: all 0.15s ease;
}

.clear-filters:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

/* Searchable Dropdown Styles */
.searchable-dropdown {
    position: relative;
    width: 100%;
    z-index: 1;
}

.searchable-dropdown-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
}

.searchable-dropdown-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.searchable-dropdown-input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.searchable-dropdown.open .searchable-dropdown-input {
    border-color: rgba(255, 255, 255, 0.4);
}

.searchable-dropdown.open {
    z-index: 100;
}

.searchable-dropdown-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    background: rgba(10, 31, 51, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-top: 4px;
    z-index: 10000;
    display: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    padding: 4px 0;
}

.searchable-dropdown-options > div {
    padding: 8px 12px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    transition: background 0.15s ease;
    display: block;
    width: 100%;
    box-sizing: border-box;
    text-align: left;
    border: none !important;
    background: none !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border-radius: 0;
    margin: 0;
    line-height: 1.4;
    height: auto;
    min-height: auto;
}

.searchable-dropdown.open .searchable-dropdown-options {
    display: block;
}

.searchable-dropdown-option {
    cursor: pointer;
    transition: background 0.15s ease;
}

.searchable-dropdown-option:hover {
    background: rgba(255, 255, 255, 0.1);
}

.searchable-dropdown-option.selected {
    background: rgba(255, 255, 255, 0.15);
}

.searchable-dropdown-no-results {
    padding: 12px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 14px;
    text-align: center;
    display: block;
    width: 100%;
    box-sizing: border-box;
}

.searchable-dropdown-options::-webkit-scrollbar {
    width: 6px;
}

.searchable-dropdown-options::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.searchable-dropdown-options::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.searchable-dropdown-options::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Responsive adjustments for dropdown */
@media (max-width: 640px) {
    .searchable-dropdown-options {
        max-height: 160px;
    }
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

    .search {
        position: relative;
    }

    .search input {
        height: 40px;
    }

    .filter-btn {
        padding: 0 12px;
        font-size: 13px;
        height: 40px;
    }

    .modal-box {
        max-width: 90%;
        padding: 20px;
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