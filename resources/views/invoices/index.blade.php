@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Invoices & Payments</h1>
        <p>Final invoices remain immutable and payments are recorded separately.</p>
    </div>
</div>

<div class="search">
    <input id="invoiceSearch" placeholder="Search invoices..." oninput="filterInvoices()">
    <button class="filter-btn" onclick="openFilterModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
    </button>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Filter Invoices</h2>
            <button class="modal-close" onclick="closeFilterModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="filter-section">
                <label>Customer</label>
                <div class="searchable-dropdown" id="customerDropdown">
                    <input type="hidden" id="customerFilter" name="customer" value="">
                    <input type="text" class="searchable-dropdown-input" id="customerFilterInput" placeholder="Search or select customer...">
                    <div class="searchable-dropdown-options"></div>
                </div>
            </div>
            <div class="filter-section">
                <label>Paid Status</label>
                <select id="paidFilter" onchange="applyFilters()">
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="secondary" onclick="clearFilters()">Clear Filters</button>
            <button class="primary" onclick="applyAndCloseFilterModal()">Apply</button>
        </div>
    </div>
</div>

<div class="panel">
    <!-- Desktop Table -->
    <table class="invoices-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $i)
            <tr>
                <td>
                    <a href="{{ route('invoices.show',$i) }}">
                        <b>{{ $i->invoice_number }}</b>
                    </a>
                </td>
                <td>{{ $i->customer->full_name }}</td>
                <td>Rs. {{ number_format($i->total,2) }}</td>
                <td>Rs. {{ number_format($i->paid,2) }}</td>
                <td>Rs. {{ number_format($i->balance,2) }}</td>
                <td><span class="badge">{{ $i->status }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty">No invoices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="invoices-cards">
        @forelse($invoices as $i)
        <a href="{{ route('invoices.show',$i) }}" class="invoice-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $i->invoice_number }}</strong>
                    <small>{{ $i->customer->full_name }}</small>
                </div>
                <span class="card-arrow">→</span>
            </div>
            <div class="card-details">
                <div class="detail">
                    <span class="label">Total</span>
                    <span class="value">Rs. {{ number_format($i->total,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Paid</span>
                    <span class="value">Rs. {{ number_format($i->paid,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Balance</span>
                    <span class="value">Rs. {{ number_format($i->balance,2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Status</span>
                    <span class="value">{{ $i->status }}</span>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state">No invoices found.</div>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $invoices->links() }}
    </div>
</div>

<style>
/* Desktop table stays normal */
.invoices-table {
    width: 100%;
    border-collapse: collapse;
}

.invoices-cards {
    display: none;
}

/* Search bar styling */
.search {
    display: flex;
    gap: 8px;
    position: relative;
    align-items: center;
}

.search input {
    flex: 1;
    height: 42px;
    box-sizing: border-box;
    padding: 0 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
}

.filter-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 14px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    height: 38px;
    box-sizing: border-box;
}

.filter-btn:hover {
    background: #d1d5db;
    border-color: #6b7280;
    color: #111827;
}

.filter-btn:active {
    background: #0a1f33;
    border-color: #0a1f33;
    color: white;
    transform: translateY(1px);
}

.filter-btn svg {
    width: 16px;
    height: 16px;
}

/* Modal styling */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5000;
    padding: 20px;
}

.modal-box {
    background: rgba(10, 31, 51, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    width: 100%;
    max-width: 400px;
    max-height: 90vh;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 25px 60px rgba(0,0,0,.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
}

.modal-close {
    border: none;
    background: none;
    font-size: 28px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background 0.15s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.modal-body {
    margin-bottom: 20px;
    overflow: visible;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-footer button {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.modal-footer .secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
}

.modal-footer .secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.modal-footer .primary {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.9);
    color: #0a1f33;
}

.modal-footer .primary:hover {
    background: #ffffff;
    border-color: #ffffff;
}

.filter-section {
    margin-bottom: 12px;
}

.filter-section:last-child {
    margin-bottom: 0;
}

.filter-section label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-section select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
    cursor: pointer;
}

.filter-section select:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.filter-section select option {
    background: #0a1f33;
    color: #ffffff;
}

.clear-filters {
    width: 100%;
    padding: 8px 12px;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    margin-top: 12px;
    transition: all 0.15s ease;
}

.clear-filters:hover {
    background: #e5e7eb;
    color: #374151;
}

/* Pagination styling */
.pagination-wrap {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.pagination-wrap nav {
    display: flex;
    justify-content: center;
}

.pagination-wrap .pagination,
.pagination-wrap nav > div {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination-wrap a,
.pagination-wrap span {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none !important;
    color: #374151;
    background: #fff;
    border: 1px solid #e5e7eb;
    transition: all 0.15s ease;
    line-height: 1;
}

.pagination-wrap a:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
}

.pagination-wrap span[aria-current="page"],
.pagination-wrap .active span,
.pagination-wrap [aria-current="page"] span {
    background: #111827 !important;
    color: #fff !important;
    border-color: #111827 !important;
    font-weight: 600;
}

.pagination-wrap span[aria-disabled="true"],
.pagination-wrap .disabled span {
    color: #9ca3af !important;
    background: #f9fafb !important;
    border-color: #e5e7eb !important;
    cursor: not-allowed;
    opacity: 0.7;
}

.pagination-wrap svg,
.pagination-wrap .pagination svg,
nav[role="navigation"] svg {
    width: 16px !important;
    height: 16px !important;
    max-width: 16px !important;
    max-height: 16px !important;
}

.pagination-wrap a[rel="prev"],
.pagination-wrap a[rel="next"] {
    font-weight: 500;
    padding: 0 14px;
}

/* ========== MOBILE ONLY ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .search {
        position: relative;
    }

    .search input {
        height: 40px;
    }

    .filter-btn {
        padding: 0 12px;
        font-size: 13px;
        height: 40px;
    }

    .modal-box {
        max-width: 90%;
        padding: 20px;
    }

    /* Hide the normal table */
    .invoices-table {
        display: none;
    }

    /* Show cards – auto-fit makes 1 or 2 cards stretch full width */
    .invoices-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .invoice-card {
        display: block;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .invoice-card:active {
        transform: scale(0.98);
        background: #f9fafb;
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .card-name strong {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }

    .card-name small {
        font-size: 12px;
        color: #6b7280;
    }

    .card-arrow {
        font-size: 16px;
        color: #9ca3af;
        margin-top: 2px;
    }

    .card-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 12px;
    }

    .detail {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .detail .label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .detail .value {
        font-size: 13.5px;
        font-weight: 500;
        color: #1f2937;
    }

    .empty-state {
        text-align: center;
        padding: 30px 16px;
        color: #9ca3af;
        font-size: 14px;
        grid-column: 1 / -1;
    }
}

/* Searchable Dropdown Styles */
.searchable-dropdown {
    position: relative;
    width: 100%;
    z-index: 1;
}

.searchable-dropdown-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
}

.searchable-dropdown-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.searchable-dropdown-input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.searchable-dropdown.open .searchable-dropdown-input {
    border-color: rgba(255, 255, 255, 0.4);
}

.searchable-dropdown.open {
    z-index: 100;
}

.searchable-dropdown-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    background: rgba(10, 31, 51, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-top: 4px;
    z-index: 10000;
    display: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    padding: 4px 0;
}

.searchable-dropdown.open .searchable-dropdown-options {
    display: block;
}

.searchable-dropdown-options > div {
    padding: 8px 12px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    transition: background 0.15s ease;
    display: block;
    width: 100%;
    box-sizing: border-box;
    text-align: left;
    border: none !important;
    background: none !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border-radius: 0;
    margin: 0;
    line-height: 1.4;
    height: auto;
    min-height: auto;
}

.searchable-dropdown-option {
    cursor: pointer;
    transition: background 0.15s ease;
}

.searchable-dropdown-option:hover {
    background: rgba(255, 255, 255, 0.1);
}

.searchable-dropdown-option.selected {
    background: rgba(255, 255, 255, 0.15);
}

.searchable-dropdown-no-results {
    padding: 12px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 14px;
    text-align: center;
    display: block;
    width: 100%;
    box-sizing: border-box;
}

.searchable-dropdown-options::-webkit-scrollbar {
    width: 6px;
}

.searchable-dropdown-options::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.searchable-dropdown-options::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.searchable-dropdown-options::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Responsive adjustments for dropdown */
@media (max-width: 640px) {
    .searchable-dropdown-options {
        max-height: 160px;
    }
}
</style>

<script>
let customerDropdown = null;

async function loadCustomersForDropdown() {
    try {
        const response = await fetch('{{ route('customers.list') }}');
        const customers = await response.json();

        const dropdownContainer = document.getElementById('customerDropdown');
        if (dropdownContainer) {
            const customerData = Array.isArray(customers) ? customers.map(customer => ({
                id: customer.id,
                label: `${customer.full_name} — ${customer.whatsapp_number || customer.phone}`
            })) : [];

            customerDropdown = new SearchableDropdown(dropdownContainer, {
                data: customerData,
                onSelect: function(item) {
                    // Don't apply filters immediately - wait for Apply button
                }
            });
        }
    } catch (error) {
        console.error('Error loading customers:', error);
    }
}

function filterInvoices() {
    applyFilters();
}

function openFilterModal() {
    document.getElementById('filterModal').style.display = 'flex';
    if (!customerDropdown) {
        loadCustomersForDropdown();
    }
}

function closeFilterModal() {
    document.getElementById('filterModal').style.display = 'none';
}

function applyAndCloseFilterModal() {
    applyFilters();
    closeFilterModal();
}

function applyFilters() {
    const query = document.getElementById('invoiceSearch').value.toLowerCase().trim();
    const customerFilterInput = document.getElementById('customerFilterInput').value.toLowerCase().trim();
    const paidFilter = document.getElementById('paidFilter').value;
    const tableRows = document.querySelectorAll('.invoices-table tbody tr');
    const mobileCards = document.querySelectorAll('.invoice-card');

    // Extract customer name from dropdown text (format: "Name — Phone")
    const customerName = customerFilterInput ? customerFilterInput.split('—')[0].trim() : '';

    // Filter desktop table rows
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const cells = row.querySelectorAll('td');
        const badge = row.querySelector('.badge');
        
        let matchesSearch = text.includes(query);
        let matchesCustomer = true;
        let matchesPaid = true;

        if (customerName && cells.length >= 2) {
            const customerText = cells[1].textContent.toLowerCase();
            matchesCustomer = customerText.includes(customerName);
        }

        if (paidFilter && badge) {
            const status = badge.textContent.toLowerCase();
            if (paidFilter === 'paid') {
                matchesPaid = status === 'paid';
            } else if (paidFilter === 'partial') {
                matchesPaid = status === 'partial';
            } else if (paidFilter === 'unpaid') {
                matchesPaid = status === 'unpaid';
            } else if (paidFilter === 'overdue') {
                matchesPaid = status === 'overdue';
            }
        }

        row.style.display = (matchesSearch && matchesCustomer && matchesPaid) ? '' : 'none';
    });

    // Filter mobile cards
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const details = card.querySelectorAll('.card-details .detail');
        const statusDetail = details[3]?.querySelector('.value')?.textContent.toLowerCase() || '';
        const customerNameCard = card.querySelector('.card-name small')?.textContent.toLowerCase() || '';
        
        let matchesSearch = text.includes(query);
        let matchesCustomer = true;
        let matchesPaid = true;

        if (customerName) {
            matchesCustomer = customerNameCard.includes(customerName);
        }

        if (paidFilter) {
            if (paidFilter === 'paid') {
                matchesPaid = statusDetail === 'paid';
            } else if (paidFilter === 'partial') {
                matchesPaid = statusDetail === 'partial';
            } else if (paidFilter === 'unpaid') {
                matchesPaid = statusDetail === 'unpaid';
            } else if (paidFilter === 'overdue') {
                matchesPaid = statusDetail === 'overdue';
            }
        }

        card.style.display = (matchesSearch && matchesCustomer && matchesPaid) ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('customerFilter').value = '';
    document.getElementById('customerFilterInput').value = '';
    document.getElementById('paidFilter').value = '';
    
    // Reset dropdown instance
    if (customerDropdown) {
        customerDropdown.setValue('', '');
    }
    
    applyFilters();
}

// Close modal when clicking outside
document.getElementById('filterModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeFilterModal();
    }
});
</script>
@endsection