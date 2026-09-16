@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Create Barcode Label</h1>
        <p>Create a new barcode label for a product.</p>
    </div>
    <a class="secondary" href="{{ route('barcode-labels.index') }}">Back to Labels</a>
</div>

<div class="panel">
    <form method="POST" action="{{ route('barcode-labels.store') }}" class="barcode-form">
        @csrf
        
        <div class="form-section">
            <label for="product_id">Product *</label>
            <select name="product_id" id="product_id" required onchange="updateProductInfo()">
                <option value="">Select a product</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" data-price="{{ $product->unit_price }}" data-name="{{ $product->name }}">
                    {{ $product->name }}
                </option>
                @endforeach
            </select>
            <small class="input-hint">Choose the product you want to create a label for</small>
        </div>

        <div class="form-section">
            <label for="labelName">Label Name</label>
            <input type="text" name="name" id="labelName" placeholder="Leave blank to use product name">
            <small class="input-hint">Optional: Custom name to display on the label instead of product name</small>
        </div>

        <div class="form-section">
            <label for="labelPrice">Price *</label>
            <div class="amount-input-wrapper">
                <span class="currency-symbol">Rs.</span>
                <input type="number" step="0.01" name="price" id="labelPrice" required min="0.01">
            </div>
            <small class="input-hint">Price that will be printed on the label</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Create Label
            </button>
            <a href="{{ route('barcode-labels.index') }}" class="secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function updateProductInfo() {
    const select = document.querySelector('select[name="product_id"]');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        document.getElementById('labelName').value = selectedOption.dataset.name;
        document.getElementById('labelPrice').value = selectedOption.dataset.price;
    }
}
</script>

<style>
.barcode-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-section {
    margin-bottom: 24px;
}

.form-section label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.form-section select,
.form-section input[type="text"],
.form-section input[type="number"] {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    color: #374151;
    background: white;
    box-sizing: border-box;
    transition: all 0.2s ease;
    text-indent: 0;
}

.amount-input-wrapper input {
    border: none !important;
}

.form-section select:focus,
.form-section input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: none;
}

.form-section select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 20px;
    padding-right: 40px;
}

.input-hint {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #6b7280;
}

.amount-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 0;
}

.currency-symbol {
    position: absolute;
    left: 12px;
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
    pointer-events: none;
    user-select: none;
    z-index: 10;
}

.amount-input-wrapper input {
    padding-left: 16px;
    padding-right: 16px;
    padding-top: 12px;
    padding-bottom: 12px;
    border: none;
    background: transparent;
    width: 100%;
    font-size: 14px;
    color: #374151;
    box-sizing: border-box;
    position: relative;
    z-index: 5;
    margin-left: 35px;
}

.amount-input-wrapper input:focus {
    outline: none;
}

.amount-input-wrapper:focus-within {
    border-color: #3b82f6;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.form-actions button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.form-actions button.primary {
    background: #3b82f6;
    color: white;
}

.form-actions button.primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.form-actions a.secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    background: #f3f4f6;
    text-decoration: none;
    transition: all 0.2s ease;
}

.form-actions a.secondary:hover {
    background: #e5e7eb;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .form-actions button,
    .form-actions a {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endsection
