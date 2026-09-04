@extends('layouts.app')

@section('content')

<div class="page-head">
    <h1>New Inventory Product</h1>
</div>

<div class="panel form-panel">

    <form
        method="post"
        action="{{ route('inventory.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="form-grid">

            {{-- Product Name --}}
            <label class="{{ $errors->has('name') ? 'field-error' : '' }}">
                Product name*

                <input
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="{{ $errors->has('name') ? 'input-error' : '' }}"
                >

                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- SKU --}}
            <label>
                SKU

                <input
                    name="sku"
                    readonly
                    id="skuField"
                    value="{{ old('sku') }}"
                >
            </label>


            {{-- Barcode --}}
            <label class="{{ $errors->has('barcode') ? 'field-error' : '' }}">
                Barcode

                <input
                    name="barcode"
                    value="{{ old('barcode') }}"
                    class="{{ $errors->has('barcode') ? 'input-error' : '' }}"
                >

                @error('barcode')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Main Category --}}
            <label class="{{ $errors->has('main_category_id') ? 'field-error' : '' }}">
                Main Category*

                <select
                    name="main_category_id"
                    id="mainCategorySelect"
                    required
                    onchange="filterSubcategories()"
                    class="{{ $errors->has('main_category_id') ? 'input-error' : '' }}"
                >
                    <option value="">Select Main Category</option>

                    @foreach($mainCategories as $cat)
                        <option
                            value="{{ $cat->id }}"
                            {{ old('main_category_id') == $cat->id ? 'selected' : '' }}
                        >
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                @error('main_category_id')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Subcategory --}}
            <label class="{{ $errors->has('category_id') ? 'field-error' : '' }}">
                Subcategory*

                <select
                    name="category_id"
                    id="subcategorySelect"
                    required
                    class="{{ $errors->has('category_id') ? 'input-error' : '' }}"
                >
                    <option value="">Select Main Category First</option>
                </select>

                @error('category_id')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Brand --}}
            <label class="{{ $errors->has('brand') ? 'field-error' : '' }}">
                Brand

                <input
                    name="brand"
                    value="{{ old('brand') }}"
                    class="{{ $errors->has('brand') ? 'input-error' : '' }}"
                >

                @error('brand')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Part Number --}}
            <label class="{{ $errors->has('part_number') ? 'field-error' : '' }}">
                Part number

                <input
                    name="part_number"
                    value="{{ old('part_number') }}"
                    class="{{ $errors->has('part_number') ? 'input-error' : '' }}"
                >

                @error('part_number')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Cost Price --}}
            <label class="{{ $errors->has('cost_price') ? 'field-error' : '' }}">
                Cost price

                <input
                    name="cost_price"
                    type="number"
                    step=".01"
                    value="{{ old('cost_price') }}"
                    class="{{ $errors->has('cost_price') ? 'input-error' : '' }}"
                >

                @error('cost_price')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Selling Price --}}
            <label class="{{ $errors->has('selling_price') ? 'field-error' : '' }}">
                Selling price*

                <input
                    name="selling_price"
                    type="number"
                    step=".01"
                    value="{{ old('selling_price') }}"
                    required
                    class="{{ $errors->has('selling_price') ? 'input-error' : '' }}"
                >

                @error('selling_price')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Minimum Stock --}}
            <label class="{{ $errors->has('minimum_stock') ? 'field-error' : '' }}">
                Minimum stock*

                <input
                    name="minimum_stock"
                    type="number"
                    value="{{ old('minimum_stock', 0) }}"
                    required
                    class="{{ $errors->has('minimum_stock') ? 'input-error' : '' }}"
                    onfocus="if (this.value === '0') this.value = ''"
                >

                @error('minimum_stock')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Opening Stock --}}
            <label class="{{ $errors->has('opening_stock') ? 'field-error' : '' }}">
                Opening stock*

                <input
                    name="opening_stock"
                    type="number"
                    step=".001"
                    value="{{ old('opening_stock', 0) }}"
                    required
                    class="{{ $errors->has('opening_stock') ? 'input-error' : '' }}"
                    onfocus="if (this.value === '0') this.value = ''"
                >

                @error('opening_stock')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>


            {{-- Product Image --}}
            <label class="wide {{ $errors->has('image') ? 'field-error' : '' }}">
                Product Image

                <input
                    type="file"
                    name="image"
                    accept="image/*"
                    class="{{ $errors->has('image') ? 'input-error' : '' }}"
                >

                @error('image')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </label>

        </div>


        {{-- Form Actions --}}
        <div class="form-actions">

            <button type="submit" class="primary">
                Create Product
            </button>

            <a
                href="{{ url()->previous() }}"
                class="btn-cancel"
            >
                <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>

                Cancel
            </a>

        </div>

    </form>

</div>


<script>

const subcategoriesData = @json($subcategories);

@php
    $generatedSku = 'PRD-' . date('Y') . '-' . str_pad(
        \App\Models\Product::max('id') + 1,
        6,
        '0',
        STR_PAD_LEFT
    );
@endphp

document.getElementById('skuField').value = @json(old('sku', $generatedSku));


function filterSubcategories() {

    const mainCategoryId =
        document.getElementById('mainCategorySelect').value;

    const subcategorySelect =
        document.getElementById('subcategorySelect');

    const oldSubcategory =
        @json(old('category_id'));

    subcategorySelect.innerHTML =
        '<option value="">Select Subcategory</option>';

    if (mainCategoryId) {

        const categorySubcategories =
            subcategoriesData.filter(
                c => c.parent_id == mainCategoryId
            );

        categorySubcategories.forEach(c => {

            const option =
                document.createElement('option');

            option.value = c.id;
            option.textContent = c.name;

            if (
                oldSubcategory &&
                oldSubcategory == c.id
            ) {
                option.selected = true;
            }

            subcategorySelect.appendChild(option);
        });
    }
}


document.addEventListener(
    'DOMContentLoaded',
    function () {

        const mainCategory =
            document.getElementById(
                'mainCategorySelect'
            );

        if (mainCategory.value) {
            filterSubcategories();
        }

    }
);

</script>


<style>

/*
|--------------------------------------------------------------------------
| Form Actions
|--------------------------------------------------------------------------
*/

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    gap: 12px;
}

.btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s;
}

.btn-cancel:hover {
    background: #fecaca;
    color: #b91c1c;
}


/*
|--------------------------------------------------------------------------
| Validation Errors
|--------------------------------------------------------------------------
*/

.input-error {
    border-color: #ef4444 !important;
    background: #fff7f7 !important;
}

.field-error {
    color: #b91c1c;
}

.field-error input,
.field-error select {
    border-color: #ef4444 !important;
}

.error-message {
    display: block;
    margin-top: 5px;
    color: #dc2626;
    font-size: 12px;
    font-weight: 500;
}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 640px) {

    .form-actions {
        flex-direction: column;
        gap: 10px;
    }

    .form-actions .primary,
    .form-actions .btn-cancel {
        width: 100%;
        text-align: center;
        justify-content: center;
    }

}

</style>

@endsection