@extends('layouts.app')

@push('styles')
<style>
    body {
        overflow: hidden !important;
    }
    .content {
        overflow: hidden !important;
        height: 100vh !important;
    }
    .pos-page-wrapper {
        overflow: hidden !important;
        height: calc(100vh - 64px) !important;
    }
</style>
@endpush

@section('content')
<div class="pos-page-wrapper" style="height: calc(100vh - 140px); overflow: hidden; padding: 16px;">
<div x-data="posApp()" x-init="initApp()" class="pos-container" style="display: flex; height: 100%; gap: 0; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: white;">
    <!-- Products Panel -->
    <div class="pos-products-panel">
        <!-- Search Bar -->
        <div class="pos-search-section">
            <div class="pos-search-wrapper">
                <svg class="pos-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" x-model="searchQuery" placeholder="Search products or scan barcode..." class="pos-search-input">
                <button type="button" @click="barcodeOnly = !barcodeOnly" 
                    :class="barcodeOnly ? 'pos-barcode-toggle active' : 'pos-barcode-toggle'"
                    class="pos-barcode-toggle"
                    title="Toggle barcode-only search">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    <span>Barcode</span>
                </button>
            </div>
        </div>

        <!-- Category Filters -->
        <div class="pos-categories-section">
            <div class="pos-category-tabs">
                <button @click="selectedMainCategory = null; filterProducts()" 
                        class="pos-category-tab" 
                        :class="selectedMainCategory === null ? 'active' : ''">
                    All
                </button>
                <template x-for="category in categories" :key="category.id">
                    <button @click="selectedMainCategory = category.id; filterProducts()" 
                            class="pos-category-tab" 
                            :class="selectedMainCategory === category.id ? 'active' : ''"
                            x-text="category.name">
                    </button>
                </template>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="pos-products-grid">
            <template x-if="visibleProducts.length === 0">
                <div class="pos-empty-state">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4m8 4l8-4m-8 4H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                    </svg>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or search</p>
                </div>
            </template>
            <template x-for="product in visibleProducts" :key="product.id">
                <div @click="addToCart(product)" class="pos-product-card" :class="product.stock <= 0 ? 'out-of-stock' : ''">
                    <template x-if="cartLineFor(product)">
                        <span class="pos-cart-badge" x-text="cartLineFor(product).quantity"></span>
                    </template>
                    <div class="pos-product-image-placeholder">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4m8 4l8-4m-8 4H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                        </svg>
                    </div>
                    <div class="pos-product-info">
                        <div class="pos-product-name" x-text="product.name"></div>
                        <div class="pos-product-sku" x-text="product.sku"></div>
                        <div class="pos-product-price">Rs. <span x-text="product.unit_price.toFixed(2)"></span></div>
                        <div class="pos-product-stock" :class="product.stock <= 5 ? 'low-stock' : ''">
                            Stock: <span x-text="product.stock"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="pos-cart-panel" :class="cartExpanded ? 'pos-cart-panel-expanded' : ''">
        <!-- Business Name Header -->
        <div class="pos-cart-header">
            <div class="pos-business-name">AutoCare POS</div>
        </div>

        <!-- Branch/Till Section -->
        <div class="pos-till-section">
            <div class="pos-section-header">
                <span class="pos-section-title">Till</span>
                <button @click="loadHeldSales(); showHeldSalesModal = true" class="pos-held-btn">
                    📋 Held <span x-show="heldSalesCount > 0" class="pos-held-badge" x-text="heldSalesCount"></span>
                </button>
                <button @click="cartExpanded = !cartExpanded" class="pos-expand-btn" :title="cartExpanded ? 'Compact view' : 'Full view'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Customer Section -->
        <div class="pos-customer-section">
            <div class="pos-section-header">
                <span class="pos-section-title">Customer</span>
            </div>
            <select x-model="selectedCustomerId" class="pos-select">
                <option value="">Walk-in Customer</option>
                <template x-for="customer in customers" :key="customer.id">
                    <option :value="customer.id" x-text="customer.full_name + ' - ' + customer.phone"></option>
                </template>
            </select>
        </div>

        <!-- Cart Items -->
        <div class="pos-cart-items">
            <template x-if="cart.length === 0">
                <div class="pos-cart-empty">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                    </svg>
                    <p>Cart is empty</p>
                </div>
            </template>
            <template x-for="(item, index) in cart" :key="index">
                <div class="pos-cart-item">
                    <div class="pos-cart-item-main">
                        <div class="pos-cart-item-info">
                            <div class="pos-cart-item-name" x-text="item.name"></div>
                            <div class="pos-cart-item-details">
                                <span>Rs. <span x-text="item.unit_price.toFixed(2)"></span></span>
                            </div>
                        </div>
                        <div class="pos-cart-item-controls">
                            <button @click="decreaseQuantity(index)" class="pos-qty-btn pos-qty-minus">-</button>
                            <span class="pos-qty-display" x-text="item.quantity"></span>
                            <button @click="increaseQuantity(index)" class="pos-qty-btn pos-qty-plus">+</button>
                        </div>
                        <div class="pos-cart-item-price">
                            Rs. <span x-text="(item.unit_price * item.quantity).toFixed(2)"></span>
                        </div>
                        <button @click="removeFromCart(index)" class="pos-cart-item-remove">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Cart Total -->
        <div class="pos-cart-total">
            <div class="pos-cart-total-label">Total</div>
            <div class="pos-cart-total-amount">Rs. <span x-text="cartTotal.toFixed(2)"></span></div>
        </div>

        <!-- Success Message -->
        <div x-show="checkoutSuccess" x-transition class="pos-success-message">
            POS invoice created successfully. Cashier will handle payment processing.
        </div>

        <!-- Hold Success Message -->
        <div x-show="holdSuccess" x-transition class="pos-hold-message">
            Sale held successfully. Click "Held Sales" to resume later.
        </div>

        <!-- Hold Button -->
        <button @click="holdSale()" :disabled="cart.length === 0" class="pos-hold-btn">
            Hold Sale
        </button>

        <!-- Checkout Button -->
        <button @click="checkout()" :disabled="cart.length === 0" class="pos-checkout-btn">
            Checkout
        </button>

        <!-- Held Sales Modal -->
        <div x-show="showHeldSalesModal" x-cloak style="display: none;" class="pos-modal">
            <div class="pos-modal-content">
                <div class="pos-modal-header">
                    <h3>Held Sales</h3>
                    <button @click="showHeldSalesModal = false" class="pos-modal-close">&times;</button>
                </div>
                <div class="pos-modal-body">
                    <template x-if="heldSales.length === 0">
                        <div class="pos-empty-state">No held sales</div>
                    </template>
                    <template x-for="heldSale in heldSales" :key="heldSale.id">
                        <div class="pos-held-sale-item">
                            <div class="pos-held-sale-info">
                                <div class="pos-held-sale-customer" x-text="heldSale.customer_name"></div>
                                <div class="pos-held-sale-details">
                                    <span x-text="heldSale.items_count + ' items'"></span>
                                    <span>•</span>
                                    <span x-text="heldSale.created_at"></span>
                                </div>
                            </div>
                            <div class="pos-held-sale-actions">
                                <button @click="resumeSale(heldSale)" class="pos-resume-btn">Resume</button>
                                <button @click="deleteHeldSale(heldSale.id)" class="pos-delete-btn">Delete</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Alert Modal -->
        <div x-show="showAlertModal" x-cloak style="display: none;" class="pos-modal">
            <div class="pos-modal-content" style="max-width: 450px;">
                <div class="pos-modal-header">
                    <h3 x-text="alertTitle">Alert</h3>
                    <button @click="showAlertModal = false" class="pos-modal-close">&times;</button>
                </div>
                <div class="pos-modal-body">
                    <template x-if="alertProduct">
                        <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 16px;">
                            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="40" height="40" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4m8 4l8-4m-8 4H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                                </svg>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1e293b; font-size: 14px; margin-bottom: 4px;" x-text="alertProduct.name"></div>
                                <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;" x-text="'SKU: ' + (alertProduct.sku || 'N/A')"></div>
                                <div style="font-size: 12px; color: #6b7280;" x-text="'Price: Rs. ' + (alertProduct.unit_price || 0).toFixed(2)"></div>
                            </div>
                        </div>
                    </template>
                    <p x-text="alertMessage" style="color: #6b7280; font-size: 14px;"></p>
                </div>
                <div class="pos-modal-footer">
                    <button @click="showAlertModal = false" class="pos-checkout-btn">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pos-container {
    display: flex;
    height: calc(100vh - 140px);
    gap: 0;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    background: white;
    max-height: calc(100vh - 140px);
}

.pos-products-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #e5e7eb;
    min-width: 0;
}

.pos-cart-panel {
    width: 280px;
    display: flex;
    flex-direction: column;
    background: #f9fafb;
    flex-shrink: 0;
}

.pos-cart-panel-expanded {
    width: 50%;
}

/* Search Section */
.pos-search-section {
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.pos-search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pos-search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    color: #9ca3af;
    pointer-events: none;
}

.pos-search-input {
    width: 100%;
    padding: 8px 10px 8px 36px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
}

.pos-search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.pos-barcode-toggle {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    color: #6b7280;
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.pos-barcode-toggle:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.pos-barcode-toggle.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

/* Category Section */
.pos-categories-section {
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.pos-category-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.pos-category-tab {
    padding: 6px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    color: #64748b;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.2s;
}

.pos-category-tab:hover {
    background: #f1f5f9;
    color: #3b82f6;
}

.pos-category-tab.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

/* Products Grid */
.pos-products-grid {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    min-height: 0;
}

.pos-product-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: relative;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.pos-product-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    transform: translateY(-2px);
}

.pos-product-card.out-of-stock {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f9fafb;
}

.pos-cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    border: 2px solid white;
}

.pos-product-image-placeholder {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    margin: 0 auto;
}

.pos-product-info {
    text-align: center;
}

.pos-product-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 13px;
    line-height: 1.3;
    min-height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pos-product-sku {
    font-size: 10px;
    color: #94a3b8;
    font-weight: 500;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.pos-product-price {
    font-size: 16px;
    font-weight: 700;
    color: #3b82f6;
    margin-bottom: 4px;
}

.pos-product-stock {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    background: #f1f5f9;
    display: inline-block;
}

.pos-product-stock.low-stock {
    color: #f59e0b;
    background: #fef3c7;
}

.pos-product-card.out-of-stock .pos-product-stock {
    color: #ef4444;
    background: #fee2e2;
}

/* Cart Panel */
.pos-cart-header {
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
    background: white;
    flex-shrink: 0;
}

.pos-business-name {
    text-align: center;
    font-weight: 700;
    color: #1e293b;
    font-size: 12px;
}

.pos-till-section,
.pos-customer-section {
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
    background: #f3f4f6;
    flex-shrink: 0;
}

.pos-section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.pos-section-title {
    flex: 1;
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pos-held-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    color: #6b7280;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-held-btn:hover {
    border-color: #f59e0b;
    background: #fef3c7;
    color: #92400e;
}

.pos-held-badge {
    background: #f59e0b;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
}

.pos-expand-btn {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-expand-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.pos-till-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
}

.pos-till-name {
    font-weight: 600;
    color: #374151;
}

.pos-expected-cash {
    color: #3b82f6;
    font-weight: 600;
}

.pos-new-customer-btn {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #3b82f6;
    border-radius: 6px;
    background: #eff6ff;
    color: #3b82f6;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-new-customer-btn:hover {
    background: #dbeafe;
}

.pos-select {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 12px;
    background: white;
    outline: none;
    transition: border-color 0.2s ease;
}

.pos-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Cart Items */
.pos-cart-items {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 0;
}

.pos-cart-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px;
    color: #9ca3af;
    text-align: center;
}

.pos-cart-empty svg {
    margin-bottom: 12px;
}

.pos-cart-item {
    padding: 6px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.pos-cart-item-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}

.pos-cart-item-info {
    flex: 1;
    min-width: 0;
}

.pos-cart-item-name {
    font-weight: 600;
    color: #111827;
    font-size: 11px;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pos-cart-item-details {
    font-size: 10px;
    color: #6b7280;
}

.pos-cart-item-price {
    font-weight: 700;
    color: #111827;
    font-size: 11px;
    flex-shrink: 0;
}

.pos-cart-item-controls {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

.pos-qty-btn {
    width: 20px;
    height: 20px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    color: #374151;
}

.pos-qty-btn:hover {
    background: #f3f4f6;
}

.pos-qty-btn.pos-qty-plus {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.pos-qty-btn.pos-qty-plus:hover {
    background: #2563eb;
}

.pos-qty-display {
    font-weight: 600;
    color: #111827;
    font-size: 12px;
    min-width: 20px;
    text-align: center;
}

.pos-cart-item-remove {
    padding: 2px;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s ease;
    flex-shrink: 0;
}

.pos-cart-item-remove:hover {
    background: #fecaca;
}

/* Form Group (for modal) */
.pos-form-group {
    margin-bottom: 10px;
}

.pos-label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.pos-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.pos-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease;
}

.pos-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.pos-input-small {
    width: 80px;
    padding: 6px 12px;
}

.pos-input-suffix {
    position: absolute;
    right: 12px;
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
}

.pos-individual-discounts {
    margin-top: 12px;
}

.pos-individual-discount-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    padding: 8px;
    background: white;
    border-radius: 6px;
}

.pos-individual-discount-name {
    flex: 1;
    font-size: 12px;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Cart Total */
.pos-cart-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    background: #f3f4f6;
    border-radius: 8px;
    margin: 8px;
    flex-shrink: 0;
}

.pos-cart-total-label {
    font-size: 12px;
    font-weight: 600;
    color: #111827;
}

.pos-cart-total-amount {
    font-size: 14px;
    font-weight: 700;
    color: #3b82f6;
}

/* Buttons */
.pos-checkout-btn,
.pos-hold-btn {
    width: calc(100% - 16px);
    margin: 0 8px 8px;
    padding: 8px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.pos-checkout-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.pos-checkout-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

.pos-checkout-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.pos-hold-btn {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.pos-hold-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(245, 158, 11, 0.4);
}

.pos-hold-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Messages */
.pos-success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 10px 12px;
    border-radius: 8px;
    margin: 0 12px 12px;
    border: 1px solid #10b981;
    font-size: 12px;
    font-weight: 500;
    flex-shrink: 0;
}

.pos-hold-message {
    background: #fef3c7;
    color: #92400e;
    padding: 10px 12px;
    border-radius: 8px;
    margin: 0 12px 12px;
    border: 1px solid #f59e0b;
    font-size: 12px;
    font-weight: 500;
    flex-shrink: 0;
}

/* Empty State */
.pos-empty-state {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px;
    color: #9ca3af;
    text-align: center;
}

.pos-empty-state svg {
    margin-bottom: 16px;
}

.pos-empty-state h3 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.pos-empty-state p {
    font-size: 12px;
}

/* Modal */
[x-cloak] {
    display: none !important;
}

.pos-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}

.pos-modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.pos-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.pos-modal-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.pos-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #9ca3af;
    cursor: pointer;
    line-height: 1;
}

.pos-modal-close:hover {
    color: #6b7280;
}

.pos-modal-body {
    padding: 16px;
    overflow-y: auto;
}

.pos-modal-footer {
    padding: 16px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.pos-held-sale-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 8px;
}

.pos-held-sale-info {
    flex: 1;
}

.pos-held-sale-customer {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
    margin-bottom: 4px;
}

.pos-held-sale-details {
    font-size: 12px;
    color: #6b7280;
}

.pos-held-sale-details span {
    margin: 0 4px;
}

.pos-held-sale-actions {
    display: flex;
    gap: 8px;
}

.pos-resume-btn,
.pos-delete-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-resume-btn {
    background: #3b82f6;
    color: white;
}

.pos-resume-btn:hover {
    background: #2563eb;
}

.pos-delete-btn {
    background: #ef4444;
    color: white;
}

.pos-delete-btn:hover {
    background: #dc2626;
}
</style>

<script>
function posApp() {
    return {
        products: @js($products ?? []),
        categories: @js($categories ?? []),
        customers: @js($customers ?? []),
        cart: [],
        searchQuery: '',
        selectedMainCategory: null,
        selectedCustomerId: '',
        cartExpanded: false,
        barcodeOnly: false,
        heldSales: [],
        heldSalesCount: 0,
        showHeldSalesModal: false,
        checkoutSuccess: false,
        holdSuccess: false,
        expectedCash: 0,
        showAlertModal: false,
        alertTitle: '',
        alertMessage: '',
        alertProduct: null,

        initApp() {
            console.log('Products loaded:', this.products.length);
            console.log('Categories loaded:', this.categories.length);
            console.log('Customers loaded:', this.customers.length);
            this.loadHeldSales();
            this.loadExpectedCash();
        },

        get filteredProducts() {
            let products = this.products;

            // If there's a search query, search across all products regardless of category
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                if (this.barcodeOnly) {
                    // Barcode-only mode: search only in barcode/SKU
                    return products.filter(p =>
                        (p.sku && p.sku.toLowerCase().includes(query)) ||
                        (p.barcode && p.barcode.toLowerCase().includes(query))
                    );
                } else {
                    // Normal mode: search across all fields
                    return products.filter(p =>
                        p.name.toLowerCase().includes(query) ||
                        (p.sku && p.sku.toLowerCase().includes(query)) ||
                        (p.barcode && p.barcode.toLowerCase().includes(query))
                    );
                }
            }

            // Apply category filter only when not searching
            if (this.selectedMainCategory !== null) {
                return products.filter(p => p.category_id === this.selectedMainCategory);
            }

            return products;
        },

        get visibleProducts() {
            return this.filteredProducts;
        },

        cartLineFor(product) {
            return this.cart.find(item => item.product_id === product.id);
        },

        addToCart(product) {
            if (product.stock <= 0) {
                this.showAlert('Out of Stock', 'This product is out of stock', product);
                return;
            }

            const existing = this.cart.find(item => item.product_id === product.id);
            if (existing) {
                if (existing.quantity >= product.stock) {
                    this.showAlert('Stock Limit', `Cannot add more. Only ${product.stock} in stock.`, product);
                    return;
                }
                existing.quantity++;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    unit_price: product.unit_price,
                    quantity: 1
                });
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        increaseQuantity(index) {
            const cartItem = this.cart[index];
            const product = this.products.find(p => p.id === cartItem.product_id);
            if (product && cartItem.quantity >= product.stock) {
                this.showAlert('Stock Limit', `Cannot add more. Only ${product.stock} in stock.`, product);
                return;
            }
            this.cart[index].quantity++;
        },

        decreaseQuantity(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
            } else {
                this.removeFromCart(index);
            }
        },

        clearCart() {
            this.cart = [];
        },

        get cartTotal() {
            return this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
        },

        filterProducts() {
            // Category change handler
        },

        async checkout() {
            if (this.cart.length === 0) return;

            try {
                const response = await fetch(window.location.pathname + '/create-invoice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        items: this.cart,
                        customer_id: this.selectedCustomerId || null
                    })
                });

                const data = await response.json();

                console.log('Checkout response:', data);

                if (data.ok || data.success) {
                    this.checkoutSuccess = true;
                    this.clearCart();
                    this.loadExpectedCash();
                    setTimeout(() => this.checkoutSuccess = false, 3000);
                } else {
                    this.showAlert('Checkout Failed', data.message || data.error || 'Checkout failed', null);
                }
            } catch (error) {
                console.error('Checkout error:', error);
                this.showAlert('Error', 'An error occurred during checkout: ' + error.message, null);
            }
        },

        async holdSale() {
            if (this.cart.length === 0) return;

            try {
                const response = await fetch(window.location.pathname + '/hold-sale', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        items: this.cart,
                        customer_id: this.selectedCustomerId || null,
                        customer_name: this.selectedCustomerId ? 
                            this.customers.find(c => c.id == this.selectedCustomerId)?.full_name : 'Walk-in'
                    })
                });

                const data = await response.json();

                if (data.ok || data.success) {
                    this.holdSuccess = true;
                    this.clearCart();
                    this.loadHeldSales();
                    setTimeout(() => this.holdSuccess = false, 3000);
                } else {
                    this.showAlert('Hold Failed', data.message || data.error || 'Failed to hold sale', null);
                }
            } catch (error) {
                this.showAlert('Error', 'An error occurred while holding the sale', null);
            }
        },

        async loadHeldSales() {
            try {
                const response = await fetch(window.location.pathname + '/held-sales');
                const data = await response.json();
                this.heldSales = data.held_sales || data.data || [];
                this.heldSalesCount = this.heldSales.length;
            } catch (error) {
                // Silent fail for held sales loading
            }
        },

        async resumeSale(heldSale) {
            try {
                const response = await fetch(window.location.pathname + '/resume-sale/' + heldSale.id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.ok || data.success) {
                    this.cart = data.held_sale?.items || data.cart || [];
                    this.selectedCustomerId = data.held_sale?.customer_id || data.customer_id;
                    this.showHeldSalesModal = false;
                    this.loadHeldSales();
                } else {
                    this.showAlert('Resume Failed', data.message || data.error || 'Failed to resume sale', null);
                }
            } catch (error) {
                this.showAlert('Error', 'An error occurred while resuming the sale', null);
            }
        },

        async deleteHeldSale(id) {
            if (!confirm('Are you sure you want to delete this held sale?')) return;

            try {
                const response = await fetch(window.location.pathname + '/held-sales/' + id, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.ok || data.success) {
                    this.loadHeldSales();
                } else {
                    this.showAlert('Delete Failed', data.message || data.error || 'Failed to delete held sale', null);
                }
            } catch (error) {
                this.showAlert('Error', 'An error occurred while deleting the held sale', null);
            }
        },

        showAlert(title, message, product = null) {
            this.alertTitle = title;
            this.alertMessage = message;
            this.alertProduct = product;
            this.showAlertModal = true;
        },

        async loadExpectedCash() {
            try {
                const response = await fetch(window.location.pathname + '/expected-cash');
                const data = await response.json();
                this.expectedCash = data.expected_cash || 0;
            } catch (error) {
                this.expectedCash = 0;
            }
        }
    };
}
</script>
@endsection
