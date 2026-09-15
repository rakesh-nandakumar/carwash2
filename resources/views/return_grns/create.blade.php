@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Create Return GRN</h1>
        <p>Return items to supplier.</p>
    </div>
    <div class="page-actions">
        <a class="secondary" href="{{ route('return_grns.index') }}">← Back to Return GRNs</a>
    </div>
</div>

<div class="panel">
    <form id="returnGrnForm">
        <div class="form-group">
            <label>Supplier</label>
            <select name="supplier_id" required onchange="handleSupplierChange(this.value)">
                <option value="">Select Supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Reference</label>
            <select name="reference" onchange="handleReferenceChange(this)">
                <option value="">Select Reference</option>
            </select>
        </div>

        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" placeholder="Supplier address">
        </div>

        <div class="form-group">
            <label>Reason for Return</label>
            <input type="text" name="reason" placeholder="e.g., Defective items, Wrong items received">
        </div>

        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3" placeholder="Additional notes..."></textarea>
        </div>

        <div class="form-group">
            <label>Items</label>
            <div id="itemsContainer">
                <div class="item-row">
                    <div class="item-row-fields">
                        <div class="field">
                            <label>Product</label>
                            <select name="items[0][product_id]" class="product-select" required onchange="updateStockDisplay(this)">
                                <option value="">Select Supplier First</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Quantity</label>
                            <input type="number" name="items[0][quantity]" step="0.001" required oninput="validateQuantity(this)">
                            <span class="stock-display" id="stock-display-0">Available stock: -</span>
                        </div>
                        <div class="field">
                            <label>Unit Cost</label>
                            <input type="number" name="items[0][unit_cost]" step="0.01">
                        </div>
                        <div class="field">
                            <label>Notes</label>
                            <input type="text" name="items[0][notes]">
                        </div>
                    </div>
                    <button type="button" class="danger" onclick="removeItemRow(this)">Remove</button>
                </div>
            </div>
            <button type="button" class="secondary" onclick="addItemRow()">+ Add Item</button>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Create Return GRN</button>
        </div>
    </form>
</div>

<style>
.item-row {
    background: #f9fafb;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    border: 1px solid #e5e7eb;
}

.item-row-fields {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 12px;
}

.field {
    display: flex;
    flex-direction: column;
}

.field label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 4px;
}

.field input,
.field select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.field input.error {
    border-color: #ef4444;
    background-color: #fef2f2;
}

.stock-display {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.stock-display.error {
    color: #ef4444;
    font-weight: 600;
}

@media (max-width: 768px) {
    .item-row-fields {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let itemCount = 1;
const products = @json($products);
const productStocks = @json($productStocks);
const supplierProducts = @json($supplierProducts);
const supplierReferences = @json($supplierReferences);
const grnItemsMap = @json($grnItemsMap);

let currentSupplierId = null;
let currentGrnNumber = null;

// Initialize first row stock display
document.addEventListener('DOMContentLoaded', function() {
    const firstSelect = document.querySelector('select[name="items[0][product_id]"]');
    if (firstSelect) {
        firstSelect.addEventListener('change', function() {
            updateStockDisplay(this);
        });
    }
    
    // Handle supplier change
    const supplierSelect = document.querySelector('select[name="supplier_id"]');
    if (supplierSelect) {
        supplierSelect.addEventListener('change', function() {
            handleSupplierChange(this.value);
        });
    }
});

function handleSupplierChange(supplierId) {
    currentSupplierId = supplierId;
    
    // Update reference dropdown
    const referenceSelect = document.querySelector('select[name="reference"]');
    if (referenceSelect) {
        referenceSelect.innerHTML = '<option value="">Select Reference</option>';
        if (supplierId && supplierReferences[supplierId]) {
            supplierReferences[supplierId].forEach(ref => {
                referenceSelect.innerHTML += `<option value="${ref.reference}" data-address="${ref.address || ''}" data-notes="${ref.notes || ''}" data-grn-number="${ref.grn_number}">${ref.reference} (${ref.grn_number})</option>`;
            });
        }
    }
    
    // Update all product dropdowns to show only supplier's products
    const productSelects = document.querySelectorAll('.product-select');
    productSelects.forEach(select => {
        updateProductOptions(select, supplierId);
    });
}

function updateProductOptions(select, supplierId) {
    const currentValue = select.value;
    let optionsHtml = '<option value="">Select Product</option>';
    
    if (supplierId && supplierProducts[supplierId]) {
        supplierProducts[supplierId].forEach(productId => {
            const product = products.find(p => p.id === productId);
            if (product) {
                optionsHtml += `<option value="${product.id}">${product.name}</option>`;
            }
        });
    } else {
        // Show all products if no supplier selected
        products.forEach(p => {
            optionsHtml += `<option value="${p.id}">${p.name}</option>`;
        });
    }
    
    select.innerHTML = optionsHtml;
    
    // Try to restore current value if it's still valid
    if (currentValue) {
        const isValid = Array.from(select.options).some(opt => opt.value === currentValue);
        if (isValid) {
            select.value = currentValue;
        }
    }
}

function handleReferenceChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const address = selectedOption?.dataset?.address || '';
    const notes = selectedOption?.dataset?.notes || '';
    const grnNumber = selectedOption?.dataset?.grnNumber || '';
    
    const addressInput = document.querySelector('input[name="address"]');
    const notesInput = document.querySelector('textarea[name="notes"]');
    
    if (addressInput) addressInput.value = address;
    if (notesInput) notesInput.value = notes;
    
    currentGrnNumber = grnNumber;
    
    // Update product dropdowns to show only items from this GRN
    if (grnNumber && grnItemsMap[grnNumber]) {
        const productSelects = document.querySelectorAll('.product-select');
        productSelects.forEach(select => {
            updateProductOptionsForGrn(select, grnItemsMap[grnNumber]);
        });
    }
}

function updateProductOptionsForGrn(select, grnProductIds) {
    const currentValue = select.value;
    let optionsHtml = '<option value="">Select Product</option>';
    
    if (grnProductIds && grnProductIds.length > 0) {
        grnProductIds.forEach(productId => {
            const product = products.find(p => p.id === productId);
            if (product) {
                optionsHtml += `<option value="${product.id}">${product.name}</option>`;
            }
        });
    }
    
    select.innerHTML = optionsHtml;
    
    // Clear current value since products changed
    select.value = '';
    
    // Update stock display
    const row = select.closest('.item-row');
    const stockDisplay = row.querySelector('.stock-display');
    if (stockDisplay) {
        stockDisplay.textContent = 'Available stock: -';
    }
}

function addItemRow() {
    const container = document.getElementById('itemsContainer');
    
    let optionsHtml = '<option value="">Select Product</option>';
    
    // If a reference is selected, show only items from that GRN
    if (currentGrnNumber && grnItemsMap[currentGrnNumber]) {
        grnItemsMap[currentGrnNumber].forEach(productId => {
            const product = products.find(p => p.id === productId);
            if (product) {
                optionsHtml += `<option value="${product.id}">${product.name}</option>`;
            }
        });
    } else if (currentSupplierId && supplierProducts[currentSupplierId]) {
        // Otherwise show all supplier products
        supplierProducts[currentSupplierId].forEach(productId => {
            const product = products.find(p => p.id === productId);
            if (product) {
                optionsHtml += `<option value="${product.id}">${product.name}</option>`;
            }
        });
    } else {
        // Show all products if no supplier selected
        products.forEach(p => {
            optionsHtml += `<option value="${p.id}">${p.name}</option>`;
        });
    }

    const html = `
        <div class="item-row">
            <div class="item-row-fields">
                <div class="field">
                    <label>Product</label>
                    <select name="items[${itemCount}][product_id]" class="product-select" required onchange="updateStockDisplay(this)">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="field">
                    <label>Quantity</label>
                    <input type="number" name="items[${itemCount}][quantity]" step="0.001" required oninput="validateQuantity(this)">
                    <span class="stock-display" id="stock-display-${itemCount}">Available stock: -</span>
                </div>
                <div class="field">
                    <label>Unit Cost</label>
                    <input type="number" name="items[${itemCount}][unit_cost]" step="0.01">
                </div>
                <div class="field">
                    <label>Notes</label>
                    <input type="text" name="items[${itemCount}][notes]">
                </div>
            </div>
            <button type="button" class="danger" onclick="removeItemRow(this)">Remove</button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    itemCount++;
}

function updateStockDisplay(select) {
    const row = select.closest('.item-row');
    const productId = select.value;
    const stockDisplay = row.querySelector('.stock-display');
    
    if (productId && productStocks[productId] !== undefined) {
        stockDisplay.textContent = `Available stock: ${productStocks[productId]}`;
        stockDisplay.classList.remove('error');
    } else {
        stockDisplay.textContent = 'Available stock: -';
    }
    
    // Also validate quantity when product changes
    const quantityInput = row.querySelector('input[type="number"][name*="quantity"]');
    validateQuantity(quantityInput);
}

function validateQuantity(input) {
    const row = input.closest('.item-row');
    const productSelect = row.querySelector('select');
    const stockDisplay = row.querySelector('.stock-display');
    const productId = productSelect.value;
    const quantity = parseFloat(input.value);
    
    if (productId && productStocks[productId] !== undefined && quantity > productStocks[productId]) {
        stockDisplay.textContent = `Insufficient stock! Available: ${productStocks[productId]}, trying to return: ${quantity}`;
        stockDisplay.classList.add('error');
        input.classList.add('error');
    } else if (productId && productStocks[productId] !== undefined) {
        stockDisplay.textContent = `Available stock: ${productStocks[productId]}`;
        stockDisplay.classList.remove('error');
        input.classList.remove('error');
    }
}

function removeItemRow(button) {
    const row = button.closest('.item-row');
    row.remove();
}

document.getElementById('returnGrnForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        supplier_id: formData.get('supplier_id'),
        reference: formData.get('reference'),
        address: formData.get('address'),
        reason: formData.get('reason'),
        notes: formData.get('notes'),
        items: []
    };
    
    // Collect items
    const itemsContainer = document.getElementById('itemsContainer');
    const itemRows = itemsContainer.querySelectorAll('.item-row');
    
    // Check for validation errors first
    const errorInputs = document.querySelectorAll('input.error');
    if (errorInputs.length > 0) {
        alert('Please fix the insufficient stock errors before submitting.');
        return;
    }
    
    itemRows.forEach((row) => {
        const productSelect = row.querySelector('select');
        const quantityInput = row.querySelector('input[type="number"][name*="quantity"]');
        const unitCostInput = row.querySelector('input[type="number"][name*="unit_cost"]');
        const notesInput = row.querySelector('input[type="text"][name*="notes"]');
        
        const productId = productSelect?.value;
        const quantity = quantityInput?.value;
        const unitCost = unitCostInput?.value;
        const notes = notesInput?.value;
        
        if (productId && quantity) {
            data.items.push({
                product_id: parseInt(productId),
                quantity: parseFloat(quantity),
                unit_cost: unitCost ? parseFloat(unitCost) : null,
                notes: notes || null
            });
        }
    });
    
    const tenant = window.location.pathname.split('/')[1];
    
    fetch(`/${tenant}/api/return-grns`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.href = `/${tenant}/return-grns/${result.data.id}`;
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});
</script>
@endsection