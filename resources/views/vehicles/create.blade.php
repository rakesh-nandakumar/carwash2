@extends('layouts.app')

@section('content')
<div class="page-head">
    <h1>New Vehicle</h1>
</div>

<div class="panel form-panel">
    <form method="post" action="{{ route('vehicles.store') }}">
        @csrf
        <div class="form-grid">
            <label>
                Customer*
                <div class="searchable-dropdown" id="customerDropdown">
                    <input type="hidden" name="customer_id" id="customer_id" value="">
                    <input type="text" class="searchable-dropdown-input" id="customerInput" placeholder="Search or select customer..." required>
                    <div class="searchable-dropdown-options"></div>
                </div>
            </label>
            <label>
                Registration*
                <input name="registration_number" required>
            </label>
            <label>
                Make
                <input name="make">
            </label>
            <label>
                Model
                <input name="model">
            </label>
            <label>
                Year
                <input name="year" type="number">
            </label>
            <label>
                Variant
                <input name="variant">
            </label>
            <label>
                Category*
                <select name="category">
                    <option>Small Car</option>
                    <option>Sedan</option>
                    <option>SUV</option>
                    <option>Luxury</option>
                    <option>Van</option>
                    <option>Pickup</option>
                    <option>Jeep</option>
                    <option>Three-wheeler</option>
                    <option>Motorcycle</option>
                    <option>Commercial</option>
                    <option>Bus</option>
                    <option>JCB Truck</option>
                </select>
            </label>
            <label>
                Fuel Type
                <input name="fuel_type">
            </label>
            <label>
                Transmission
                <input name="transmission">
            </label>
            <label>
                Mileage
                <input name="mileage" type="number" value="0">
            </label>
            <label>
                Fuel %
                <input name="fuel_level" type="number" min="0" max="100">
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Create Vehicle</button>
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

<style>
.form-panel .form-grid label {
    display: block;
    margin-bottom: 14px;
    font-size: 13px;
    font-weight: 600;
}
.form-panel .form-grid input,
.form-panel .form-grid select {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    font-size: 14px;
    border-radius: 12px;
    margin-top: 6px;
}
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Vehicle create page - loading customers via AJAX');
    
    // Load customers via AJAX like the reception page
    async function loadCustomers() {
        try {
            const response = await fetch('{{ route('customers.list') }}');
            const customers = await response.json();

            const dropdownContainer = document.getElementById('customerDropdown');
            if (dropdownContainer) {
                const customerData = Array.isArray(customers) ? customers.map(customer => ({
                    id: customer.id,
                    label: `${customer.full_name} — ${customer.whatsapp_number || customer.phone}`,
                    name: customer.full_name,
                    phone: customer.phone,
                    whatsapp: customer.whatsapp_number || ''
                })) : [];

                console.log('Customer data loaded:', customerData);
                console.log('Customer data length:', customerData.length);

                const dropdown = new SearchableDropdown(dropdownContainer, {
                    data: customerData
                });
                console.log('SearchableDropdown initialized:', dropdown);
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }
    
    loadCustomers();
});
</script>
@endsection