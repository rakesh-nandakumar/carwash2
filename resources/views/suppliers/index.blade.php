@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Suppliers</h1>
        <p>Manage suppliers and vendor relationships.</p>
    </div>
    <a class="primary" href="#" onclick="showSupplierModal()">+ New Supplier</a>
</div>

<div class="search">
    <input id="supplierSearch" placeholder="Search suppliers by name or phone" oninput="filterSuppliers()">
</div>

<div class="panel">
    <!-- Desktop Table -->
    <table class="suppliers-table">
        <thead>
            <tr>
                <th>Supplier</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Balance</th>
                <th>Credit Limit</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="suppliersTableBody">
            <!-- Suppliers will be loaded here via AJAX -->
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="suppliers-cards" id="suppliersCards">
        <!-- Suppliers will be loaded here via AJAX -->
    </div>

    <div class="pagination-wrap" id="pagination"></div>
</div>

<!-- Supplier Modal -->
<div id="supplierModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="supplierModalTitle">New Supplier</h2>
            <button class="close-btn" onclick="closeSupplierModal()">&times;</button>
        </div>
        <form id="supplierForm" onsubmit="saveSupplier(event)">
            <input type="hidden" id="supplierId">
            <div class="form-group">
                <label>Supplier Name *</label>
                <input type="text" id="supplierName" required>
            </div>
            <div class="form-group">
                <label>Business Name</label>
                <input type="text" id="businessName">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" id="supplierPhone">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="supplierEmail">
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea id="supplierAddress" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Credit Limit</label>
                <input type="number" id="creditLimit" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea id="supplierNotes" rows="2"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="secondary" onclick="closeSupplierModal()">Cancel</button>
                <button type="submit" class="primary">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<style>
.suppliers-table {
    width: 100%;
    border-collapse: collapse;
    display: table;
}

.suppliers-table th,
.suppliers-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.suppliers-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.suppliers-table tr:hover {
    background: #f9fafb;
}

.suppliers-cards {
    display: none;
}

@media (max-width: 768px) {
    .suppliers-table {
        display: none;
    }
    .suppliers-cards {
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

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-blacklisted {
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
    max-width: 500px;
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
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
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

function loadSuppliers(page = 1) {
    currentPage = page;
    fetch(`{{ route('api.suppliers.index') }}?page=${page}&search=${searchQuery}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data.data)) {
                renderSuppliers(data.data.data);
                renderPagination(data.data);
            } else {
                console.error('Invalid data format:', data);
            }
        })
        .catch(error => {
            console.error('Error loading suppliers:', error);
        });
}

function renderSuppliers(suppliers) {
    const tbody = document.getElementById('suppliersTableBody');
    const cards = document.getElementById('suppliersCards');

    // Desktop table
    tbody.innerHTML = suppliers.map(s => `
        <tr>
            <td>
                <a href="/suppliers/${s.id}">
                    <b>${s.name}</b>
                </a>
                ${s.business_name ? `<small>${s.business_name}</small>` : ''}
            </td>
            <td>${s.phone || '-'}</td>
            <td>${s.email || '-'}</td>
            <td>Rs. ${parseFloat(s.outstanding_balance).toFixed(2)}</td>
            <td>Rs. ${parseFloat(s.credit_limit).toFixed(2)}</td>
            <td>
                <span class="status-badge ${s.is_blacklisted ? 'status-blacklisted' : 'status-active'}">
                    ${s.is_blacklisted ? 'Blacklisted' : 'Active'}
                </span>
            </td>
            <td>
                <a href="/suppliers/${s.id}">View →</a>
            </td>
        </tr>
    `).join('');

    // Mobile cards
    cards.innerHTML = suppliers.map(s => `
        <a href="/suppliers/${s.id}" class="supplier-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>${s.name}</strong>
                    ${s.business_name ? `<small>${s.business_name}</small>` : ''}
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Phone</span>
                    <span class="value">${s.phone || '-'}</span>
                </div>
                <div class="detail">
                    <span class="label">Balance</span>
                    <span class="value">Rs. ${parseFloat(s.outstanding_balance).toFixed(2)}</span>
                </div>
                <div class="detail">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge ${s.is_blacklisted ? 'status-blacklisted' : 'status-active'}">
                            ${s.is_blacklisted ? 'Blacklisted' : 'Active'}
                        </span>
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
        html += `<button onclick="loadSuppliers(${data.current_page - 1})">Previous</button>`;
    }
    html += `<span>Page ${data.current_page} of ${data.last_page}</span>`;
    if (data.current_page < data.last_page) {
        html += `<button onclick="loadSuppliers(${data.current_page + 1})">Next</button>`;
    }
    html += '</div>';
    pagination.innerHTML = html;
}

function filterSuppliers() {
    searchQuery = document.getElementById('supplierSearch').value;
    loadSuppliers(1);
}

function showSupplierModal() {
    document.getElementById('supplierModal').style.display = 'flex';
    document.getElementById('supplierForm').reset();
    document.getElementById('supplierId').value = '';
    document.getElementById('supplierModalTitle').textContent = 'New Supplier';
}

function closeSupplierModal() {
    document.getElementById('supplierModal').style.display = 'none';
}

function saveSupplier(event) {
    event.preventDefault();
    const formData = {
        name: document.getElementById('supplierName').value,
        business_name: document.getElementById('businessName').value,
        phone: document.getElementById('supplierPhone').value,
        email: document.getElementById('supplierEmail').value,
        address: document.getElementById('supplierAddress').value,
        credit_limit: document.getElementById('creditLimit').value,
        notes: document.getElementById('supplierNotes').value,
    };

    const supplierId = document.getElementById('supplierId').value;
    const baseUrl = '{{ route('api.suppliers.index') }}';
    const url = supplierId ? `${baseUrl}/${supplierId}` : baseUrl;
    const method = supplierId ? 'PUT' : 'POST';

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
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeSupplierModal();
            loadSuppliers(currentPage);
            showToast('Supplier saved successfully', 'success');
        } else {
            showToast(data.message || 'Error saving supplier', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving supplier:', error);
        showToast('Error saving supplier', 'error');
    });
}

// Load suppliers on page load
loadSuppliers();
</script>
@endsection
