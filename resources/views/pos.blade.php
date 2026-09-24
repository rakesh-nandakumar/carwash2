@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>POS Terminal</h1>
        <p>Point of sale for direct product sales.</p>
    </div>
</div>

<div class="search">
    <input id="productSearch" placeholder="Search products or scan barcode...">
</div>

<div class="panel">
    <div x-data="posApp()" x-init="initApp()">
        <!-- Category Filter -->
        <div style="margin-bottom: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
            <button @click="selectMainCategory(null)" :class="selectedMainCategory === null ? 'btn-primary' : 'btn-secondary'" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; background: white; cursor: pointer;">
                All
            </button>
            <template x-for="category in categories" :key="category.id">
                <button @click="selectMainCategory(category.id)" :class="selectedMainCategory === category.id ? 'btn-primary' : 'btn-secondary'" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; background: white; cursor: pointer;" x-text="category.name"></button>
            </template>
        </div>

        <!-- Subcategory Filter (shows when main category is selected) -->
        <div x-show="selectedMainCategory !== null" style="margin-bottom: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
            <button @click="selectSubcategory(null)" :class="selectedSubcategory === null ? 'btn-primary' : 'btn-secondary'" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #e5e7eb; background: white; cursor: pointer; font-size: 13px;">
                All
            </button>
            <template x-for="subcategory in getSubcategories()" :key="subcategory.id">
                <button @click="selectSubcategory(subcategory.id)" :class="selectedSubcategory === subcategory.id ? 'btn-primary' : 'btn-secondary'" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #e5e7eb; background: white; cursor: pointer; font-size: 13px;" x-text="subcategory.name"></button>
            </template>
        </div>

        <!-- Products Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <template x-if="filteredProducts.length === 0">
                <div style="grid-column: 1 / -1; padding: 24px; text-align: center; color: #9ca3af; background: #f9fafb; border-radius: 8px;">
                    No products found
                </div>
            </template>
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="addToCart(product)" style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; cursor: pointer; transition: all 0.2s ease; background: white;" class="product-card">
                    <div style="font-weight: 600; margin-bottom: 8px;" x-text="product.name"></div>
                    <div style="font-size: 13px; color: #6b7280; margin-bottom: 8px;" x-text="product.sku"></div>
                    <div style="font-size: 14px; font-weight: 500; color: #111827;">Rs. <span x-text="product.unit_price.toFixed(2)"></span></div>
                    <div style="font-size: 12px; color: #9ca3af; margin-top: 4px;">Stock: <span x-text="product.stock"></span></div>
                </div>
            </template>
        </div>

        <!-- Cart Section -->
        <div style="border-top: 1px solid #e5e7eb; padding-top: 16px;">
            <h3 style="margin-bottom: 12px;">Cart</h3>

            <!-- Customer Selection -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 4px; font-size: 14px; font-weight: 500;">Customer (Optional)</label>
                <select x-model="selectedCustomerId" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <option value="">Walk-in Customer</option>
                    <template x-for="customer in customers" :key="customer.id">
                        <option :value="customer.id" x-text="customer.full_name + ' - ' + customer.phone"></option>
                    </template>
                </select>
            </div>

            <!-- Cart Items -->
            <div style="margin-bottom: 16px;">
                <template x-if="cart.length === 0">
                    <div style="padding: 24px; text-align: center; color: #9ca3af; background: #f9fafb; border-radius: 8px;">
                        Cart is empty
                    </div>
                </template>
                <template x-for="(item, index) in cart" :key="index">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f3f4f6;">
                        <div>
                            <div style="font-weight: 500;" x-text="item.name"></div>
                            <div style="font-size: 13px; color: #6b7280;">Rs. <span x-text="item.unit_price.toFixed(2)"></span> × <span x-text="item.quantity"></span></div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="font-weight: 600;">Rs. <span x-text="(item.unit_price * item.quantity).toFixed(2)"></span></div>
                            <button @click="removeFromCart(index)" style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer;">×</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Discount Section -->
            <div style="margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                <h4 style="margin-bottom: 12px; font-size: 14px;">Discount</h4>
                
                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 4px; font-size: 12px; font-weight: 500;">Discount Type</label>
                    <select x-model="discountType" @change="resetDiscountValues()" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 4px;">
                        <option value="none">No Discount</option>
                        <option value="amount">Fixed Amount</option>
                        <option value="percentage">Percentage</option>
                    </select>
                </div>

                <div x-show="discountType !== 'none'" style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 4px; font-size: 12px; font-weight: 500;">Apply To</label>
                    <select x-model="discountApplyTo" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 4px;">
                        <option value="total">Total Amount</option>
                        <option value="individual">Individual Parts</option>
                    </select>
                </div>

                <div x-show="discountType !== 'none' && discountApplyTo === 'total'" style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 4px; font-size: 12px; font-weight: 500;">Discount Value</label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="number" x-model="discountValue" step="0.01" min="0" style="flex: 1; padding: 8px; border: 1px solid #e5e7eb; border-radius: 4px;" placeholder="0.00">
                        <span x-text="discountType === 'percentage' ? '%' : 'Rs.'" style="font-size: 12px; color: #6b7280;"></span>
                    </div>
                </div>

                <div x-show="discountType !== 'none' && discountApplyTo === 'individual'" style="margin-top: 12px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 500;">Individual Part Discounts</label>
                    <template x-for="(item, index) in cart" :key="index">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; padding: 8px; background: white; border-radius: 4px;">
                            <span style="flex: 1; font-size: 12px;" x-text="item.name"></span>
                            <input type="number" x-model="item.individualDiscount" step="0.01" min="0" style="width: 80px; padding: 4px 8px; border: 1px solid #e5e7eb; border-radius: 4px;" placeholder="0.00">
                            <span x-text="discountType === 'percentage' ? '%' : 'Rs.'" style="font-size: 12px; color: #6b7280;"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Cart Total -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #f9fafb; border-radius: 8px; margin-bottom: 16px;">
                <div style="font-weight: 600;">Total</div>
                <div style="font-size: 18px; font-weight: 700;">Rs. <span x-text="cartTotal.toFixed(2)"></span></div>
            </div>

            <!-- Checkout Button -->
            <button @click="checkout()" :disabled="cart.length === 0" style="width: 100%; padding: 12px; background: #111827; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; opacity: 0.5;" :style="cart.length > 0 ? 'opacity: 1;' : ''">
                Checkout
            </button>
        </div>
    </div>
</div>

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
            if (this.selectedSubcategory !== null) {
                return this.products.filter(p => p.category_id === this.selectedSubcategory);
            } else if (this.selectedMainCategory !== null) {
                // Get all subcategory IDs for this main category
                const category = this.categories.find(c => c.id === this.selectedMainCategory);
                const subcategoryIds = category ? (category.children || []).map(c => c.id) : [];
                subcategoryIds.push(this.selectedMainCategory);
                return this.products.filter(p => subcategoryIds.includes(p.category_id));
            }
            return this.products;
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