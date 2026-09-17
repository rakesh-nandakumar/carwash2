@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Goods Receipt Notes</h1>
        <p>Manage inventory receipts from suppliers.</p>
    </div>
    <a class="primary" href="#" onclick="showGrnModal()">+ New GRN</a>
</div>

<div class="search">
    <input id="grnSearch" placeholder="Search GRN by number or reference" oninput="filterGrns()">
</div>

<div class="panel">
    <!-- Desktop Table -->
    <table class="grns-table">
        <thead>
            <tr>
                <th>GRN Number</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="grnsTableBody">
            <!-- GRNs will be loaded here via AJAX -->
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="grns-cards" id="grnsCards">
        <!-- GRNs will be loaded here via AJAX -->
    </div>

    <div class="pagination-wrap" id="pagination"></div>
</div>

<!-- GRN Modal -->
<div id="grnModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="grnModalTitle">New GRN</h2>
            <button class="close-btn" onclick="closeGrnModal()">&times;</button>
        </div>
        <form id="grnForm" onsubmit="saveGrn(event)">
            <input type="hidden" id="grnId">
            <div class="form-group">
                <label>Supplier</label>
                <select id="supplierId">
                    <option value="">Select Supplier</option>
                    <!-- Suppliers will be loaded here -->
                </select>
            </div>
            <div class="form-group">
                <label>Reference</label>
                <input type="text" id="grnReference">
            </div>
            <div class="form-group">
                <label>Note</label>
                <textarea id="grnNote" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Items *</label>
                <div id="grnItems">
                    <div class="grn-item">
                        <select class="item-product" required>
                            <option value="">Select Product</option>
                        </select>
                        <input type="number" class="item-quantity" placeholder="Quantity" step="0.01" required>
                        <input type="number" class="item-cost" placeholder="Unit Cost" step="0.01">
                        <input type="number" class="item-price" placeholder="Sale Price" step="0.01" required>
                        <button type="button" onclick="removeGrnItem(this)" class="danger">×</button>
                    </div>
                </div>
                <button type="button" onclick="addGrnItem()" class="secondary">+ Add Item</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="secondary" onclick="closeGrnModal()">Cancel</button>
                <button type="submit" class="primary">Save GRN</button>
            </div>
        </form>
    </div>
</div>

<style>
.grns-table {
    width: 100%;
    border-collapse: collapse;
    display: table;
}

.grns-table th,
.grns-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.grns-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.grns-table tr:hover {
    background: #f9fafb;
}

.grns-cards {
    display: none;
}

@media (max-width: 768px) {
    .grns-table {
        display: none;
    }
    .grns-cards {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-draft {
    background: #fef3c7;
    color: #92400e;
}

.status-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-deleted {
    background: #fee2e2;
    color: #991b1b;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
}

.form-group {
    padding: 16px 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.grn-item {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 8px;
    margin-bottom: 8px;
}

.grn-item input,
.grn-item select {
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px;
    border-top: 1px solid #e5e7eb;
}
</style>

<script>
let currentPage = 1;
let searchQuery = '';

function getGrnUrl(id) {
    const pathParts = window.location.pathname.split('/');
    const tenant = pathParts[1];
    return `/${tenant}/grns/${id}`;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function loadGrns(page = 1) {
    currentPage = page;
    fetch(`{{ route('api.grns.index') }}?page=${page}&search=${searchQuery}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data.data)) {
                renderGrns(data.data.data);
                renderPagination(data.data);
            } else {
                console.error('Invalid data format:', data);
            }
        })
        .catch(error => {
            console.error('Error loading GRNs:', error);
        });
}

function renderGrns(grns) {
    const tbody = document.getElementById('grnsTableBody');
    const cards = document.getElementById('grnsCards');

    // Desktop table
    tbody.innerHTML = grns.map(g => {
        const url = getGrnUrl(g.id);
        console.log('Generated URL for GRN', g.id, ':', url);
        return `
        <tr>
            <td>
                <a href="${url}">
                    <b>${g.grn_number}</b>
                </a>
                ${g.reference ? `<small>${g.reference}</small>` : ''}
            </td>
            <td>${new Date(g.received_at).toLocaleString()}</td>
            <td>${g.supplier ? g.supplier.name : '-'}</td>
            <td>${g.items_count || 0}</td>
            <td>Rs. ${parseFloat(g.total_amount || 0).toFixed(2)}</td>
            <td>
                <span class="status-badge status-${g.status_key}">${g.status}</span>
            </td>
            <td>
                <a href="${url}" class="view-link">View →</a>
            </td>
        </tr>
    `;
    }).join('');

    // Mobile cards
    cards.innerHTML = grns.map(g => `
        <a href="${getGrnUrl(g.id)}" class="grn-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>${g.grn_number}</strong>
                    ${g.reference ? `<small>${g.reference}</small>` : ''}
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Date</span>
                    <span class="value">${new Date(g.received_at).toLocaleString()}</span>
                </div>
                <div class="detail">
                    <span class="label">Supplier</span>
                    <span class="value">${g.supplier ? g.supplier.name : '-'}</span>
                </div>
                <div class="detail">
                    <span class="label">Items</span>
                    <span class="value">${g.items_count || 0}</span>
                </div>
                <div class="detail">
                    <span class="label">Total</span>
                    <span class="value">Rs. ${parseFloat(g.total_amount || 0).toFixed(2)}</span>
                </div>
                <div class="detail">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-${g.status_key}">${g.status}</span>
                    </span>
                </div>
            </div>
        </a>
    `).join('');
}

function renderPagination(data) {
    const pagination = document.getElementById('pagination');
    if (data.last_page <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let html = '<div class="pagination">';
    if (data.current_page > 1) {
        html += `<button onclick="loadGrns(${data.current_page - 1})">Previous</button>`;
    }
    html += `<span>Page ${data.current_page} of ${data.last_page}</span>`;
    if (data.current_page < data.last_page) {
        html += `<button onclick="loadGrns(${data.current_page + 1})">Next</button>`;
    }
    html += '</div>';
    pagination.innerHTML = html;
}

function filterGrns() {
    searchQuery = document.getElementById('grnSearch').value;
    loadGrns(1);
}

function showGrnModal() {
    document.getElementById('grnModal').style.display = 'flex';
    document.getElementById('grnForm').reset();
    document.getElementById('grnId').value = '';
    document.getElementById('grnModalTitle').textContent = 'New GRN';
    loadSuppliers();
    loadProducts();
}

function closeGrnModal() {
    document.getElementById('grnModal').style.display = 'none';
}

function loadSuppliers() {
    fetch(`{{ route('api.suppliers.index') }}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('supplierId');
            const suppliers = data.data && data.data.data ? data.data.data : (Array.isArray(data.data) ? data.data : []);
            select.innerHTML = '<option value="">Select Supplier</option>' +
                suppliers.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        })
        .catch(error => {
            console.error('Error loading suppliers:', error);
        });
}

function loadProducts() {
    fetch(`{{ route('inventory.products') }}`)
        .then(response => response.json())
        .then(data => {
            const selects = document.querySelectorAll('.item-product');
            const products = Array.isArray(data) ? data : (data.data ? data.data : []);
            selects.forEach(select => {
                select.innerHTML = '<option value="">Select Product</option>' +
                    products.map(p => `<option value="${p.id}">${p.label || p.name || 'Product ' + p.id}</option>`).join('');
            });
        })
        .catch(error => {
            console.error('Error loading products:', error);
        });
}

function addGrnItem() {
    const container = document.getElementById('grnItems');
    const newItem = document.createElement('div');
    newItem.className = 'grn-item';
    newItem.innerHTML = `
        <select class="item-product" required>
            <option value="">Select Product</option>
        </select>
        <input type="number" class="item-quantity" placeholder="Quantity" step="0.01" required>
        <input type="number" class="item-cost" placeholder="Unit Cost" step="0.01">
        <input type="number" class="item-price" placeholder="Sale Price" step="0.01" required>
        <button type="button" onclick="removeGrnItem(this)" class="danger">×</button>
    `;
    container.appendChild(newItem);
    loadProducts();
}

function removeGrnItem(button) {
    const items = document.querySelectorAll('.grn-item');
    if (items.length > 1) {
        button.parentElement.remove();
    }
}

function saveGrn(event) {
    event.preventDefault();

    const items = [];
    document.querySelectorAll('.grn-item').forEach(item => {
        const productId = item.querySelector('.item-product').value;
        const quantity = item.querySelector('.item-quantity').value;
        const unitCost = item.querySelector('.item-cost').value;
        const salePrice = item.querySelector('.item-price').value;

        items.push({
            product_id: productId,
            quantity: parseFloat(quantity) || 0,
            unit_cost: parseFloat(unitCost) || null,
            sale_price: parseFloat(salePrice) || null,
        });
    });

    const formData = {
        supplier_id: document.getElementById('supplierId').value,
        reference: document.getElementById('grnReference').value,
        note: document.getElementById('grnNote').value,
        items: items,
    };

    const grnId = document.getElementById('grnId').value;
    const baseUrl = '{{ route('api.grns.index') }}';
    const url = grnId ? `${baseUrl}/${grnId}` : baseUrl;
    const method = grnId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || err.errors ? JSON.stringify(err.errors) : `HTTP error! status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeGrnModal();
            loadGrns(currentPage);
            showToast('GRN saved successfully', 'success');
        } else {
            showToast(data.message || 'Error saving GRN', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving GRN:', error);
        showToast(error.message || 'Error saving GRN', 'error');
    });
}

// Load GRNs on page load
loadGrns();
</script>
@endsection
