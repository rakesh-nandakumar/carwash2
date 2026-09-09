@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Services</h1>
        <p>Manage service types and pricing</p>
    </div>
    <a class="primary" href="{{ route('services.create') }}">+ New Service</a>
</div>

<div class="search">
    <input id="serviceSearch" placeholder="Search services..." oninput="filterServices()">
    <button class="filter-btn" onclick="openFilterModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
    </button>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Filter Services</h2>
            <button class="modal-close" onclick="closeFilterModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="filter-section">
                <label>Category</label>
                <div class="searchable-dropdown" id="categoryDropdown">
                    <input type="hidden" id="categoryFilter" name="category" value="">
                    <input type="text" class="searchable-dropdown-input" id="categoryFilterInput" placeholder="Search or select category...">
                    <div class="searchable-dropdown-options"></div>
                </div>
            </div>
            <div class="filter-section">
                <label>Price Range</label>
                <select id="priceFilter">
                    <option value="">All Prices</option>
                    <option value="low">Low (under Rs. 1,000)</option>
                    <option value="medium">Medium (Rs. 1,000 - 5,000)</option>
                    <option value="high">High (over Rs. 5,000)</option>
                </select>
            </div>
            <div class="filter-section">
                <label>Stock Status</label>
                <select id="stockFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
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
    <table class="services-table">
        <thead>
            <tr>
                <th>Service</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
            <tr>
                <td>
                    <strong>{{ $service->name }}</strong>
                </td>
                <td>{{ $service->category ? $service->category->name : '-' }}</td>
                <td>Rs. {{ number_format($service->base_price, 2) }}</td>
                <td>
                    <label style="position:relative;display:inline-block;width:44px;height:24px;">
                        <input type="checkbox" {{ $service->active ? 'checked' : '' }} onchange="toggleServiceStatus({{ $service->id }}, this)" style="opacity:0;width:0;height:0;">
                        <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:{{ $service->active ? '#10b981' : '#ccc' }};transition:.4s;border-radius:24px;"></span>
                        <span style="position:absolute;content:'';height:18px;width:18px;left:3px;bottom:3px;background-color:white;transition:.4s;border-radius:50%;{{ $service->active ? 'transform:translateX(20px);' : '' }}"></span>
                    </label>
                </td>
                <td>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <a href="{{ route('services.edit', $service) }}" style="background:#3b82f6;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:500;transition:all 0.2s;">Edit</a>
                        <form method="post" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Are you sure you want to delete this service?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:#ef4444;color:white;padding:6px 12px;border-radius:6px;border:none;font-size:12px;font-weight:500;cursor:pointer;transition:all 0.2s;">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="services-cards">
        @forelse($services as $service)
        <div class="service-card">
            <div class="card-top">
                <div class="card-name">
                    <strong>{{ $service->name }}</strong>
                    <small>{{ $service->category ? $service->category->name : 'No category' }}</small>
                </div>
                <label class="status-toggle">
                    <input type="checkbox" {{ $service->active ? 'checked' : '' }} onchange="toggleServiceStatus({{ $service->id }}, this)">
                    <span class="slider"></span>
                </label>
            </div>

            <div class="card-details">
                <div class="detail">
                    <span class="label">Price</span>
                    <span class="value">Rs. {{ number_format($service->base_price, 2) }}</span>
                </div>
                <div class="detail">
                    <span class="label">Status</span>
                    <span class="value">{{ $service->active ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>

            <div class="card-actions">
                <a href="{{ route('services.edit', $service) }}" class="btn-edit">Edit</a>
                <form method="post" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Are you sure you want to delete this service?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state">No services found.</div>
        @endforelse
    </div>
</div>

<script>
function toggleServiceStatus(serviceId, checkbox) {
    fetch(`{{ route('services.toggle', ['service' => '__ID__']) }}`.replace('__ID__', serviceId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            active: checkbox.checked
        })
    })
    .then(response => response.json())
    .then(data => {
        // Update desktop toggle visual if present
        const span = checkbox.nextElementSibling?.nextElementSibling;
        if (span) {
            if (checkbox.checked) {
                span.style.transform = 'translateX(20px)';
                checkbox.nextElementSibling.style.backgroundColor = '#10b981';
            } else {
                span.style.transform = 'translateX(0)';
                checkbox.nextElementSibling.style.backgroundColor = '#ccc';
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

let categoryDropdown = null;

async function loadCategoriesForDropdown() {
    try {
        const response = await fetch('{{ route('service-categories.list') }}');
        const categories = await response.json();

        const dropdownContainer = document.getElementById('categoryDropdown');
        if (dropdownContainer) {
            const categoryData = Array.isArray(categories) ? categories.map(category => ({
                id: category.name,
                label: category.name
            })) : [];

            categoryDropdown = new SearchableDropdown(dropdownContainer, {
                data: categoryData,
                onSelect: function(item) {
                    // Don't apply filters immediately - wait for Apply button
                }
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

function filterServices() {
    applyFilters();
}

function openFilterModal() {
    document.getElementById('filterModal').style.display = 'flex';
    if (!categoryDropdown) {
        loadCategoriesForDropdown();
    }
}

function applyFilters() {
    const query = document.getElementById('serviceSearch').value.toLowerCase().trim();
    const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase().trim();
    const priceFilter = document.getElementById('priceFilter')?.value || '';
    const stockFilter = document.getElementById('stockFilter')?.value || '';
    const tableRows = document.querySelectorAll('.services-table tbody tr');
    const mobileCards = document.querySelectorAll('.service-card');

    console.log('Applying filters:', { query, categoryFilter, priceFilter, stockFilter });

    // Filter desktop table rows
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const cells = row.querySelectorAll('td');
        const checkbox = row.querySelector('input[type="checkbox"]');
        
        let matchesSearch = text.includes(query);
        let matchesCategory = true;
        let matchesPrice = true;
        let matchesStock = true;

        if (categoryFilter && cells.length >= 2) {
            const categoryText = cells[1].textContent.trim().toLowerCase();
            matchesCategory = categoryText === categoryFilter || categoryText.includes(categoryFilter);
        }

        if (priceFilter && cells.length >= 3) {
            const originalText = cells[2].textContent;
            // Remove "Rs." prefix and commas, then parse
            const priceText = originalText.replace(/Rs\./g, '').replace(/,/g, '').trim();
            const price = parseFloat(priceText) || 0;

            console.log('Price filtering:', { originalText, priceText, price, priceFilter });

            if (priceFilter === 'low') {
                matchesPrice = price < 1000;
            } else if (priceFilter === 'medium') {
                matchesPrice = price >= 1000 && price <= 5000;
            } else if (priceFilter === 'high') {
                matchesPrice = price > 5000;
            }
        }

        if (stockFilter && checkbox) {
            const isActive = checkbox.checked;
            if (stockFilter === 'active') {
                matchesStock = isActive;
            } else if (stockFilter === 'inactive') {
                matchesStock = !isActive;
            }
        }

        row.style.display = (matchesSearch && matchesCategory && matchesPrice && matchesStock) ? '' : 'none';
    });

    // Filter mobile cards
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const checkbox = card.querySelector('input[type="checkbox"]');
        const priceDetail = card.querySelector('.card-details .detail:nth-child(1) .value');
        const categoryDetail = card.querySelector('.card-name small');
        
        let matchesSearch = text.includes(query);
        let matchesCategory = true;
        let matchesPrice = true;
        let matchesStock = true;

        if (categoryFilter && categoryDetail) {
            const categoryText = categoryDetail.textContent.trim().toLowerCase();
            matchesCategory = categoryText === categoryFilter || categoryText.includes(categoryFilter);
        }

        if (priceFilter && priceDetail) {
            const originalText = priceDetail.textContent;
            // Remove "Rs." prefix and commas, then parse
            const priceText = originalText.replace(/Rs\./g, '').replace(/,/g, '').trim();
            const price = parseFloat(priceText) || 0;

            console.log('Mobile price filtering:', { originalText, priceText, price, priceFilter });

            if (priceFilter === 'low') {
                matchesPrice = price < 1000;
            } else if (priceFilter === 'medium') {
                matchesPrice = price >= 1000 && price <= 5000;
            } else if (priceFilter === 'high') {
                matchesPrice = price > 5000;
            }
        }

        if (stockFilter && checkbox) {
            const isActive = checkbox.checked;
            if (stockFilter === 'active') {
                matchesStock = isActive;
            } else if (stockFilter === 'inactive') {
                matchesStock = !isActive;
            }
        }

        card.style.display = (matchesSearch && matchesCategory && matchesPrice && matchesStock) ? '' : 'none';
    });
}

function closeFilterModal() {
    document.getElementById('filterModal').style.display = 'none';
}

function applyAndCloseFilterModal() {
    applyFilters();
    closeFilterModal();
}

function clearFilters() {
    document.getElementById('categoryFilter').value = '';
    document.getElementById('categoryFilterInput').value = '';
    document.getElementById('priceFilter').value = '';
    document.getElementById('stockFilter').value = '';
    
    // Reset dropdown instance
    if (categoryDropdown) {
        categoryDropdown.setValue('', '');
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

<style>
/* Desktop table stays normal */
.services-table {
    width: 100%;
    border-collapse: collapse;
}

.services-cards {
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

.filter-section input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
}

.filter-section input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.filter-section input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.clear-filters {
    width: 100%;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    cursor: pointer;
    margin-top: 12px;
    transition: all 0.15s ease;
}

.clear-filters:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

/* ========== MOBILE ONLY ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head a.primary {
        width: 100%;
        text-align: center;
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
    .services-table {
        display: none;
    }

    /* Show cards */
    .services-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .service-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
        gap: 10px;
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

    /* Toggle switch */
    .status-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex-shrink: 0;
    }

    .status-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .status-toggle .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .status-toggle .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    .status-toggle input:checked + .slider {
        background-color: #10b981;
    }

    .status-toggle input:checked + .slider:before {
        transform: translateX(20px);
    }

    .card-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 12px;
        margin-bottom: 14px;
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

    .card-actions {
        display: flex;
        gap: 8px;
    }

    .card-actions .btn-edit {
        flex: 1;
        text-align: center;
        padding: 8px 12px;
        border-radius: 8px;
        background: #3b82f6;
        color: white;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }

    .card-actions form {
        flex: 1;
        margin: 0;
    }

    .card-actions .btn-delete {
        width: 100%;
        padding: 8px 12px;
        border-radius: 8px;
        background: #ef4444;
        color: white;
        border: none;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
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
</style>
@endsection