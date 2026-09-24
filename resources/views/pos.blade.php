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
            <button @click="filterCategory(null)" :class="selectedCategory === null ? 'btn-primary' : 'btn-secondary'" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; background: white; cursor: pointer;">
                All
            </button>
            <template x-for="category in categories" :key="category.id">
                <button @click="filterCategory(category.id)" :class="selectedCategory === category.id ? 'btn-primary' : 'btn-secondary'" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; background: white; cursor: pointer;" x-text="category.name"></button>
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
        selectedCategory: null,
        selectedCustomerId: '',

        initApp() {
        },

        get filteredProducts() {
            if (this.selectedCategory === null) {
                return this.products;
            }
            return this.products.filter(p => p.category_id === this.selectedCategory);
        },

        filterCategory(categoryId) {
            this.selectedCategory = categoryId;
        },

        addToCart(product) {
            const existing = this.cart.find(item => item.id === product.id);
            if (existing) {
                existing.quantity += 1;
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    unit_price: product.unit_price,
                    quantity: 1
                });
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        get cartTotal() {
            return this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
        },

        checkout() {
            if (this.cart.length === 0) return;

            fetch('/pos/invoice', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    customer_id: this.selectedCustomerId || null,
                    items: this.cart
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    window.location.href = '/cashier/' + data.invoice_id;
                }
            });
        }
    };
}
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection