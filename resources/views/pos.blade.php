@extends('layouts.app')

@section('content')
<div x-data="posApp()" x-init="initApp()" class="pos-container">
    <!-- Products Panel -->
    <div class="pos-products-panel">
        <!-- Search Bar -->
        <div class="pos-search-section">
            <div class="pos-search-wrapper">
                <svg class="pos-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" x-model="searchQuery" placeholder="Search products or scan barcode..." class="pos-search-input">
            </div>
            <button @click="loadHeldSales(); showHeldSalesModal = true" class="pos-held-sales-btn">
                📋 Held Sales <span x-show="heldSalesCount > 0" x-text="'(' + heldSalesCount + ')'" style="background: #ef4444; padding: 2px 6px; border-radius: 10px; font-size: 12px; margin-left: 4px;"></span>
            </button>
        </div>

        <!-- Category Filters -->
        <div class="pos-categories-section">
            <button @click="selectMainCategory(null)" :class="selectedMainCategory === null ? 'pos-category-btn active' : 'pos-category-btn'" class="pos-category-btn">
                All
            </button>
            <template x-for="category in categories" :key="category.id">
                <button @click="selectMainCategory(category.id)" :class="selectedMainCategory === category.id ? 'pos-category-btn active' : 'pos-category-btn'" x-text="category.name"></button>
            </template>
        </div>

        <!-- Subcategory Filters -->
        <div x-show="selectedMainCategory !== null" class="pos-subcategories-section">
            <button @click="selectSubcategory(null)" :class="selectedSubcategory === null ? 'pos-subcategory-btn active' : 'pos-subcategory-btn'" class="pos-subcategory-btn">
                All
            </button>
            <template x-for="subcategory in getSubcategories()" :key="subcategory.id">
                <button @click="selectSubcategory(subcategory.id)" :class="selectedSubcategory === subcategory.id ? 'pos-subcategory-btn active' : 'pos-subcategory-btn'" x-text="subcategory.name"></button>
            </template>
        </div>

        <!-- Products Grid -->
        <div class="pos-products-grid" style="min-height: 300px;">
            <template x-if="filteredProducts.length === 0">
                <div class="pos-empty-state">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4m8 4l8-4m-8 4H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                    </svg>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or search</p>
                </div>
            </template>
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="addToCart(product)" class="pos-product-card" :class="product.stock <= 0 ? 'out-of-stock' : ''">
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
    <div class="pos-cart-panel">
        <!-- Customer Selection -->
        <div class="pos-customer-section">
            <label class="pos-label">Customer</label>
            <select x-model="selectedCustomerId" class="pos-select">
                <option value="">Walk-in Customer</option>
                <template x-for="customer in customers" :key="customer.id">
                    <option :value="customer.id" x-text="customer.full_name + ' - ' + customer.phone"></option>
                </template>
            </select>
        </div>

        <!-- Clear Cart Button -->
        <button @click="clearCart()" :disabled="cart.length === 0" class="pos-clear-cart-btn" :style="cart.length > 0 ? '' : 'opacity: 0.5; cursor: not-allowed;'">
            🗑️ Clear Cart
        </button>

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

        <!-- Discount Section -->
        <div class="pos-discount-section">
            <h4 class="pos-section-title">Discount</h4>
            
            <div class="pos-form-group">
                <label class="pos-label">Discount Type</label>
                <select x-model="discountType" @change="resetDiscountValues()" class="pos-select">
                    <option value="none">No Discount</option>
                    <option value="amount">Fixed Amount</option>
                    <option value="percentage">Percentage</option>
                </select>
            </div>

            <div x-show="discountType !== 'none'" class="pos-form-group">
                <label class="pos-label">Apply To</label>
                <select x-model="discountApplyTo" class="pos-select">
                    <option value="total">Total Amount</option>
                    <option value="individual">Individual Parts</option>
                </select>
            </div>

            <div x-show="discountType !== 'none' && discountApplyTo === 'total'" class="pos-form-group">
                <label class="pos-label">Discount Value</label>
                <div class="pos-input-wrapper">
                    <input type="number" x-model="discountValue" step="0.01" min="0" class="pos-input" placeholder="0.00">
                    <span x-text="discountType === 'percentage' ? '%' : 'Rs.'" class="pos-input-suffix"></span>
                </div>
            </div>

            <div x-show="discountType !== 'none' && discountApplyTo === 'individual'" class="pos-individual-discounts">
                <label class="pos-label">Individual Part Discounts</label>
                <template x-for="(item, index) in cart" :key="index">
                    <div class="pos-individual-discount-item">
                        <span class="pos-individual-discount-name" x-text="item.name"></span>
                        <div class="pos-input-wrapper">
                            <input type="number" x-model="item.individualDiscount" step="0.01" min="0" class="pos-input-small" placeholder="0.00">
                            <span x-text="discountType === 'percentage' ? '%' : 'Rs.'" class="pos-input-suffix"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Cart Total -->
        <div class="pos-cart-total">
            <div class="pos-cart-total-label">Total</div>
            <div class="pos-cart-total-amount">Rs. <span x-text="cartTotal.toFixed(2)"></span></div>
        </div>

        <!-- Success Message -->
        <div x-show="checkoutSuccess" x-transition style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #10b981; font-size: 14px; font-weight: 500;">
            POS invoice created successfully. Cashier will handle payment processing.
        </div>

        <!-- Hold Success Message -->
        <div x-show="holdSuccess" x-transition style="background: #fef3c7; color: #92400e; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #f59e0b; font-size: 14px; font-weight: 500;">
            Sale held successfully. Click "Held Sales" to resume later.
        </div>

        <!-- Hold Button -->
        <button @click="holdSale()" :disabled="cart.length === 0" class="pos-hold-btn" :style="cart.length > 0 ? '' : 'opacity: 0.5; cursor: not-allowed;'">
            Hold Sale
        </button>

        <!-- Checkout Button -->
        <button @click="checkout()" :disabled="cart.length === 0" class="pos-checkout-btn" :style="cart.length > 0 ? '' : 'opacity: 0.5; cursor: not-allowed;'">
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
    </div>
</div>

<style>
.pos-container {
    display: flex;
    min-height: calc(100vh - 120px);
    gap: 24px;
    padding: 24px;
}

.pos-products-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
    overflow: hidden;
    min-height: 500px;
}

.pos-cart-panel {
    width: 400px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    background: white;
    border-radius: 16px;
    padding: 24px;
    padding-bottom: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.pos-clear-cart-btn {
    width: 100%;
    padding: 10px;
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-clear-cart-btn:hover {
    background: #4b5563;
}

.pos-clear-cart-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

.pos-search-section {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-shrink: 0;
}

.pos-search-wrapper {
    flex: 1;
    position: relative;
}

.pos-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: #9ca3af;
}

.pos-search-input {
    width: 100%;
    padding: 12px 12px 12px 44px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease;
}

.pos-search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.pos-categories-section {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex-shrink: 0;
}

.pos-category-btn {
    padding: 8px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-category-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.pos-category-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.pos-subcategories-section {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex-shrink: 0;
}

.pos-subcategory-btn {
    padding: 6px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-subcategory-btn:hover {
    border-color: #6b7280;
    color: #1f2937;
}

.pos-subcategory-btn.active {
    background: #6b7280;
    border-color: #6b7280;
    color: white;
}

.pos-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    overflow-y: auto;
    padding: 4px;
}

.pos-product-card {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 20px 14px 14px 14px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    justify-content: center;
}

.pos-product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.pos-product-card:hover::before {
    opacity: 1;
}

.pos-product-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
    transform: translateY(-4px) scale(1.02);
}

.pos-product-card.out-of-stock {
    opacity: 0.6;
    cursor: not-allowed;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

.pos-product-card.out-of-stock::before {
    background: linear-gradient(90deg, #ef4444, #f97316);
}

.pos-product-image-placeholder {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
    transition: all 0.3s ease;
}

.pos-product-card:hover .pos-product-image-placeholder {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
}

.pos-product-info {
    text-align: center;
    margin-bottom: 0;
}

.pos-product-name {
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 6px;
    font-size: 15px;
    line-height: 1.3;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pos-product-sku {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.pos-product-price {
    font-size: 18px;
    font-weight: 800;
    color: #3b82f6;
    margin-bottom: 6px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.pos-product-stock {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    background: #f1f5f9;
    display: inline-block;
    margin-bottom: 0;
}

.pos-product-stock.low-stock {
    color: #f59e0b;
    background: #fef3c7;
    animation: pulse-stock 2s infinite;
}

.pos-product-card.out-of-stock .pos-product-stock {
    color: #ef4444;
    background: #fee2e2;
}

@keyframes pulse-stock {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
    margin-bottom: 8px;
}

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
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
}

.pos-empty-state p {
    font-size: 14px;
}

.pos-customer-section {
    flex-shrink: 0;
}

.pos-label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.pos-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    outline: none;
    transition: border-color 0.2s ease;
}

.pos-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.pos-cart-items {
    flex: 1;
    min-height: 200px;
    max-height: 400px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 4px;
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
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
}

.pos-cart-item-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pos-cart-item-info {
    flex: 1;
    padding-right: 16px;
}

.pos-cart-item-name {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
    margin-bottom: 4px;
}

.pos-cart-item-details {
    font-size: 13px;
    color: #6b7280;
}

.pos-cart-item-price {
    font-weight: 700;
    color: #111827;
    font-size: 14px;
    padding-right: 8px;
}

.pos-cart-item-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-right: 12px;
}

.pos-qty-btn {
    width: 28px;
    height: 28px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 6px;
    font-size: 16px;
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
    border-color: #d1d5db;
}

.pos-qty-btn.pos-qty-plus {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.pos-qty-btn.pos-qty-plus:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.pos-qty-display {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
    min-width: 24px;
    text-align: center;
}

.pos-cart-item-remove {
    padding: 4px;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s ease;
    position: static;
}

.pos-cart-item-remove:hover {
    background: #fecaca;
}

.pos-discount-section {
    flex-shrink: 0;
    padding: 16px;
    background: #f9fafb;
    border-radius: 12px;
    max-height: 400px;
    overflow-y: auto;
}

.pos-section-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 12px;
}

.pos-form-group {
    margin-bottom: 12px;
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
}

.pos-cart-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f3f4f6;
    border-radius: 12px;
    margin-bottom: 16px;
}

.pos-cart-total-label {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.pos-cart-total-amount {
    font-size: 20px;
    font-weight: 700;
    color: #3b82f6;
}

.pos-checkout-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
}

.pos-checkout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px -2px rgba(59, 130, 246, 0.4);
}

.pos-checkout-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.pos-hold-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
    margin-bottom: 12px;
}

.pos-hold-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px -2px rgba(245, 158, 11, 0.4);
}

.pos-hold-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.pos-held-sales-btn {
    padding: 8px 16px;
    background: #6366f1;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.pos-held-sales-btn:hover {
    background: #4f46e5;
}

/* Modal Styles */
[x-cloak] {
    display: none !important;
}

.pos-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.pos-modal-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    z-index: 10000;
}

.pos-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.pos-modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #111827;
}

.pos-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #6b7280;
    line-height: 1;
}

.pos-modal-close:hover {
    color: #111827;
}

.pos-modal-body {
    padding: 20px;
    overflow-y: auto;
}

.pos-held-sale-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 12px;
    border: 1px solid #e5e7eb;
}

.pos-held-sale-info {
    flex: 1;
}

.pos-held-sale-customer {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
}

.pos-held-sale-details {
    font-size: 14px;
    color: #6b7280;
}

.pos-held-sale-details span {
    margin: 0 4px;
}

.pos-held-sale-actions {
    display: flex;
    gap: 8px;
}

.pos-resume-btn {
    padding: 8px 16px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-resume-btn:hover {
    background: #059669;
}

.pos-delete-btn {
    padding: 8px 16px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pos-delete-btn:hover {
    background: #dc2626;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .pos-container {
        flex-direction: column;
        padding: 16px;
    }

    .pos-products-panel {
        min-height: 500px;
        flex: 1;
    }

    .pos-cart-panel {
        width: 100%;
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    .pos-container {
        padding: 12px;
        gap: 16px;
    }

    .pos-search-section {
        flex-direction: column;
        align-items: stretch;
    }

    .pos-held-sales-btn {
        width: 100%;
        margin-top: 8px;
    }

    .pos-cart-panel {
        width: 100%;
    }

    .pos-categories-section {
        flex-wrap: wrap;
    }

    .pos-products-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }

    .pos-products-panel {
        min-height: 400px;
    }
}
</style>

<script>
function posApp() {
    return {
        products: @json($products),
        categories: @json($categories),
        customers: @json($customers),
        cart: [],
        selectedMainCategory: null,
        selectedSubcategory: null,
        selectedCustomerId: '',
        searchQuery: '',
        discountType: 'none',
        discountValue: 0,
        discountApplyTo: 'total',
        checkoutSuccess: false,
        holdSuccess: false,
        heldSales: [],
        heldSalesCount: 0,
        showHeldSalesModal: false,

        initApp() {
            this.loadHeldSalesInternal();
        },

        resetDiscountValues() {
            this.discountValue = 0;
            this.cart.forEach(item => item.individualDiscount = 0);
        },

        get totalDiscount() {
            if (this.discountType === 'none') return 0;

            const subtotal = this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);

            if (this.discountApplyTo === 'total') {
                if (this.discountType === 'percentage') {
                    return subtotal * (this.discountValue / 100);
                } else {
                    return this.discountValue;
                }
            } else if (this.discountApplyTo === 'individual') {
                return this.cart.reduce((sum, item) => sum + (item.individualDiscount || 0), 0);
            }

            return 0;
        },

        selectMainCategory(categoryId) {
            this.selectedMainCategory = categoryId;
            this.selectedSubcategory = null;
        },

        selectSubcategory(subcategoryId) {
            this.selectedSubcategory = subcategoryId;
        },

        getSubcategories() {
            if (!this.selectedMainCategory) return [];
            const category = this.categories.find(c => c.id === this.selectedMainCategory);
            return category ? (category.children || []) : [];
        },

        get filteredProducts() {
            let products = this.products;

            // Apply search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                products = products.filter(p => 
                    p.name.toLowerCase().includes(query) || 
                    p.sku.toLowerCase().includes(query) ||
                    p.barcode?.toLowerCase().includes(query)
                );
            }

            // Apply category filter
            if (this.selectedSubcategory !== null) {
                return products.filter(p => p.category_id === this.selectedSubcategory);
            } else if (this.selectedMainCategory !== null) {
                const category = this.categories.find(c => c.id === this.selectedMainCategory);
                const subcategoryIds = category ? (category.children || []).map(c => c.id) : [];
                subcategoryIds.push(this.selectedMainCategory);
                return products.filter(p => subcategoryIds.includes(p.category_id));
            }

            return products;
        },

        addToCart(product) {
            // Check if product has stock
            if (product.stock <= 0) {
                alert('This product is out of stock');
                return;
            }

            const existing = this.cart.find(item => item.product_id === product.id);
            if (existing) {
                // Check if adding one more would exceed available stock
                if (existing.quantity >= product.stock) {
                    alert('Insufficient stock. Available: ' + product.stock);
                    return;
                }
                existing.quantity += 1;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    unit_price: product.unit_price,
                    quantity: 1,
                    individualDiscount: 0
                });
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        increaseQuantity(index) {
            const item = this.cart[index];
            const product = this.products.find(p => p.id === item.product_id);
            if (product && item.quantity < product.stock) {
                item.quantity++;
            } else {
                alert('Cannot exceed available stock');
            }
        },

        decreaseQuantity(index) {
            const item = this.cart[index];
            if (item.quantity > 1) {
                item.quantity--;
            } else {
                this.removeFromCart(index);
            }
        },

        clearCart() {
            if (this.cart.length === 0) return;
            if (!confirm('Are you sure you want to clear the cart?')) return;
            
            this.cart = [];
            this.selectedCustomerId = '';
            this.discountType = 'none';
            this.discountValue = 0;
            this.discountApplyTo = 'total';
        },

        get cartTotal() {
            const subtotal = this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
            return subtotal - this.totalDiscount;
        },

        checkout() {
            if (this.cart.length === 0) return;

            const url = window.location.pathname + '/create-invoice';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    customer_id: this.selectedCustomerId || null,
                    discount_type: this.discountType,
                    discount_value: this.discountValue,
                    discount_apply_to: this.discountApplyTo,
                    items: this.cart.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        individual_discount: item.individualDiscount || 0
                    }))
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    this.checkoutSuccess = true;
                    this.cart = [];
                    this.selectedCustomerId = '';
                    this.discountType = 'none';
                    this.discountValue = 0;
                    this.discountApplyTo = 'total';

                    // Hide message after 3 seconds
                    setTimeout(() => {
                        this.checkoutSuccess = false;
                    }, 3000);
                } else {
                    console.error('Error:', data.error);
                    alert('Error: ' + (data.error || 'Failed to create invoice'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: Failed to create invoice');
            });
        },

        holdSale() {
            if (this.cart.length === 0) return;

            const url = window.location.pathname + '/hold-sale';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    customer_id: this.selectedCustomerId || null,
                    discount_type: this.discountType,
                    discount_value: this.discountValue,
                    discount_apply_to: this.discountApplyTo,
                    individual_discounts: this.cart.map(item => ({
                        product_id: item.product_id,
                        individual_discount: item.individualDiscount || 0
                    })),
                    items: this.cart.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        individual_discount: item.individualDiscount || 0
                    }))
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    this.cart = [];
                    this.selectedCustomerId = '';
                    this.discountType = 'none';
                    this.discountValue = 0;
                    this.discountApplyTo = 'total';
                    this.holdSuccess = true;
                    this.heldSalesCount++;
                    setTimeout(() => {
                        this.holdSuccess = false;
                    }, 3000);
                } else {
                    console.error('Error:', data.error);
                    alert('Error: ' + (data.error || 'Failed to hold sale'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: Failed to hold sale');
            });
        },

        loadHeldSales() {
            console.log('Loading held sales...');
            const url = window.location.pathname + '/held-sales';

            fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Held sales loaded:', data);
                this.heldSales = data;
                this.heldSalesCount = data.length;
                this.showHeldSalesModal = true;
                console.log('Modal should show now, showHeldSalesModal:', this.showHeldSalesModal);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        },

        async resumeSale(heldSale) {
            // If there's already a cart with items, hold it first
            if (this.cart.length > 0) {
                await this.holdSaleInternal();
            }

            this.cart = heldSale.items;
            this.selectedCustomerId = heldSale.customer_id || '';
            this.discountType = heldSale.discount_type;
            this.discountValue = heldSale.discount_value;
            this.discountApplyTo = heldSale.discount_apply_to;

            if (heldSale.individual_discounts) {
                heldSale.individual_discounts.forEach(discount => {
                    const item = this.cart.find(i => i.product_id === discount.product_id);
                    if (item) {
                        item.individualDiscount = discount.individual_discount;
                    }
                });
            }

            // Delete the held sale after resuming (without confirmation)
            this.deleteHeldSale(heldSale.id, false);
            this.showHeldSalesModal = false;
            
            // Reload held sales to get accurate count
            await this.loadHeldSalesInternal();
        },

        async holdSaleInternal() {
            const url = window.location.pathname + '/hold-sale';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        customer_id: this.selectedCustomerId || null,
                        discount_type: this.discountType,
                        discount_value: this.discountValue,
                        discount_apply_to: this.discountApplyTo,
                        individual_discounts: this.cart.map(item => ({
                            product_id: item.product_id,
                            individual_discount: item.individualDiscount || 0
                        })),
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            individual_discount: item.individualDiscount || 0
                        }))
                    })
                });
                const data = await response.json();
                if (data.ok) {
                    // Don't increment here - will be reloaded from server
                }
            } catch (error) {
                console.error('Error holding current cart:', error);
            }
        },

        async loadHeldSalesInternal() {
            const url = window.location.pathname + '/held-sales';

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                this.heldSales = data;
                this.heldSalesCount = data.length;
            } catch (error) {
                console.error('Error:', error);
            }
        },

        deleteHeldSale(id, confirmDelete = true) {
            if (confirmDelete && !confirm('Are you sure you want to delete this held sale?')) return;

            const url = window.location.pathname + '/held-sales/' + id;

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    this.heldSales = this.heldSales.filter(hs => hs.id !== id);
                    this.heldSalesCount = this.heldSales.length;
                } else {
                    console.error('Error:', data.error);
                    alert('Error: ' + (data.error || 'Failed to delete held sale'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: Failed to delete held sale');
            });
        }
    };
}
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection