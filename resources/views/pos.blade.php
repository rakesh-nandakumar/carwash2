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
        <div class="pos-products-grid">
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
                                <span>Rs. <span x-text="item.unit_price.toFixed(2)"></span> × <span x-text="item.quantity"></span></span>
                            </div>
                        </div>
                        <div class="pos-cart-item-price">
                            Rs. <span x-text="(item.unit_price * item.quantity).toFixed(2)"></span>
                        </div>
                    </div>
                    <button @click="removeFromCart(index)" class="pos-cart-item-remove">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
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

        <!-- Checkout Button -->
        <button @click="checkout()" :disabled="cart.length === 0" class="pos-checkout-btn" :style="cart.length > 0 ? '' : 'opacity: 0.5; cursor: not-allowed;'">
            Checkout
        </button>
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

.pos-search-section {
    flex-shrink: 0;
}

.pos-search-wrapper {
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
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pos-product-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    transform: translateY(-2px);
}

.pos-product-card.out-of-stock {
    opacity: 0.5;
    cursor: not-allowed;
}

.pos-product-image-placeholder {
    width: 48px;
    height: 48px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    margin: 0 auto;
}

.pos-product-info {
    text-align: center;
}

.pos-product-name {
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
    font-size: 14px;
}

.pos-product-sku {
    font-size: 12px;
    color: #9ca3af;
    margin-bottom: 8px;
}

.pos-product-price {
    font-size: 16px;
    font-weight: 700;
    color: #3b82f6;
    margin-bottom: 4px;
}

.pos-product-stock {
    font-size: 12px;
    color: #6b7280;
}

.pos-product-stock.low-stock {
    color: #ef4444;
    font-weight: 600;
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
    padding: 12px 40px 12px 12px;
    background: #f9fafb;
    border-radius: 8px;
    position: relative;
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

.pos-cart-item-remove {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s ease;
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

        initApp() {
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

        get cartTotal() {
            const subtotal = this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
            return subtotal - this.totalDiscount;
        },

        checkout() {
            if (this.cart.length === 0) return;

            const url = '{{ route("pos.create-invoice") }}';

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
                    window.location.href = '{{ route("cashier.index") }}';
                } else {
                    console.error('Error:', data.error);
                }
            });
        }
    };
}
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection