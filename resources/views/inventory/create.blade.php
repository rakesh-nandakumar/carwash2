@extends('layouts.app')

@section('content')
<div class="page-head"><h1>New Inventory Product</h1></div>

<div class="panel form-panel">
    <form method="post" action="{{ route('inventory.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <label>Product name*<input name="name" required></label>
            <label>SKU<input name="sku" readonly id="skuField"></label>
            <label>Barcode<input name="barcode"></label>
            <label>Main Category*<select name="main_category_id" id="mainCategorySelect" required onchange="filterSubcategories()"><option value="">Select Main Category</option>@foreach($mainCategories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></label>
            <label>Subcategory*<select name="category_id" id="subcategorySelect" required><option value="">Select Main Category First</option></select></label>
            <label>Brand<input name="brand"></label>
            <label>Part number<input name="part_number"></label>
            <label>Cost price<input name="cost_price" type="number" step=".01"></label>
            <label>Selling price*<input name="selling_price" type="number" step=".01" required></label>
            <label>Minimum stock*<input name="minimum_stock" type="number" value="0" required></label>
            <label>Opening stock*<input name="opening_stock" type="number" step=".001" value="0" required></label>
            <label class="wide">Product Image<input type="file" name="image" accept="image/*"></label>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Create Product</button>
            <a href="{{ url()->previous() }}" class="btn-cancel">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
document.getElementById('skuField').value='PRD-{{ date('Y') }}-'+'{{ str_pad(\App\Models\Product::max('id')+1, 6, '0', STR_PAD_LEFT) }}';
function filterSubcategories(){
const mainCategoryId=document.getElementById('mainCategorySelect').value;
const subcategorySelect=document.getElementById('subcategorySelect');
subcategorySelect.innerHTML='<option value="">Select Subcategory</option>';
if(mainCategoryId){
const categorySubcategories=subcategoriesData.filter(c=>c.parent_id==mainCategoryId);
categorySubcategories.forEach(c=>{
const option=document.createElement('option');
option.value=c.id;
option.textContent=c.name;
subcategorySelect.appendChild(option);
});
}
}
</script>

<style>
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