@extends('layouts.app')

@section('content')
<div class="page-head">
    <h1>Edit Vehicle</h1>
</div>

<div class="panel form-panel">
    <form method="post" action="{{ route('vehicles.update', $vehicle) }}">
        @method('PUT')
        @csrf
        <div class="form-grid">
            <label>
                Customer*
                <div class="searchable-dropdown" id="customerDropdown">
                    <input type="hidden" name="customer_id" id="customer_id" value="{{ $vehicle->customer_id }}">
                    <input type="text" class="searchable-dropdown-input" id="customerInput" placeholder="Search or select customer..." required value="{{ $vehicle->customer->full_name }}">
                    <div class="searchable-dropdown-options"></div>
                </div>
            </label>
            <label>
                Registration*
                <input name="registration_number" required value="{{ $vehicle->registration_number }}">
            </label>
            <label>
                Make
                <input name="make" value="{{ $vehicle->make }}">
            </label>
            <label>
                Model
                <input name="model" value="{{ $vehicle->model }}">
            </label>
            <label>
                Year
                <input name="year" type="number" value="{{ $vehicle->year }}">
            </label>
            <label>
                Variant
                <input name="variant" value="{{ $vehicle->variant }}">
            </label>
            <label>
                Category*
                <select name="category">
                    <option value="Bike" {{ $vehicle->category === 'Bike' ? 'selected' : '' }}>Bike</option>
                    <option value="Motorcycle" {{ $vehicle->category === 'Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                    <option value="Three-wheeler" {{ $vehicle->category === 'Three-wheeler' ? 'selected' : '' }}>Three-wheeler</option>
                    <option value="Small Car" {{ $vehicle->category === 'Small Car' ? 'selected' : '' }}>Small Car</option>
                    <option value="Sedan" {{ $vehicle->category === 'Sedan' ? 'selected' : '' }}>Sedan</option>
                    <option value="Minivan" {{ $vehicle->category === 'Minivan' ? 'selected' : '' }}>Minivan</option>
                    <option value="SUV" {{ $vehicle->category === 'SUV' ? 'selected' : '' }}>SUV</option>
                    <option value="Jeep" {{ $vehicle->category === 'Jeep' ? 'selected' : '' }}>Jeep</option>
                    <option value="Pickup" {{ $vehicle->category === 'Pickup' ? 'selected' : '' }}>Pickup</option>
                    <option value="Van" {{ $vehicle->category === 'Van' ? 'selected' : '' }}>Van</option>
                    <option value="Bus" {{ $vehicle->category === 'Bus' ? 'selected' : '' }}>Bus</option>
                    <option value="Lorry" {{ $vehicle->category === 'Lorry' ? 'selected' : '' }}>Lorry</option>
                    <option value="JCB Truck" {{ $vehicle->category === 'JCB Truck' ? 'selected' : '' }}>JCB Truck</option>
                    <option value="Boom Truck" {{ $vehicle->category === 'Boom Truck' ? 'selected' : '' }}>Boom Truck</option>
                </select>
            </label>
            <label>
                Fuel Type
                <input name="fuel_type" value="{{ $vehicle->fuel_type }}">
            </label>
            <label>
                Transmission
                <input name="transmission" value="{{ $vehicle->transmission }}">
            </label>
            <label>
                Mileage
                <input name="mileage" type="number" value="{{ $vehicle->mileage }}">
            </label>
            <label>
                Fuel %
                <input name="fuel_level" type="number" min="0" max="100" value="{{ $vehicle->fuel_level }}">
            </label>
            <label>
                VIN
                <input name="vin" value="{{ $vehicle->vin }}">
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Update Vehicle</button>
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn-cancel">
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
.searchable-dropdown {
    position: relative;
    width: 100%
}
.searchable-dropdown-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 200px;
    overflow-y: auto;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-top: 4px;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 4px 0
}
.searchable-dropdown-option {
    padding: 8px 12px;
    cursor: pointer;
    color: #374151;
    font-size: 14px;
    transition: background 0.15s ease
}
.searchable-dropdown-option:hover {
    background: #f3f4f6
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
    console.log('Vehicle edit page - loading customers via AJAX');
    
    // Load customers via AJAX like the reception page
    async function loadCustomers() {
        try {
            const response = await fetch('{{ route('customers.list') }}');
            const customers = await response.json();

            const dropdownContainer = document.getElementById('customerDropdown');
            const customerInput = document.getElementById('customerInput');
            const customerId = document.getElementById('customer_id');
            
            if (dropdownContainer && customerInput && customerId) {
                const customerData = Array.isArray(customers) ? customers.map(customer => ({
                    id: customer.id,
                    label: `${customer.full_name} — ${customer.whatsapp_number || customer.phone}`,
                    name: customer.full_name,
                    phone: customer.phone,
                    whatsapp: customer.whatsapp_number || ''
                })) : [];

                console.log('Customer data loaded:', customerData);
                console.log('Customer data length:', customerData.length);

                // Simple dropdown implementation
                const optionsDiv = dropdownContainer.querySelector('.searchable-dropdown-options');
                
                customerInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    optionsDiv.innerHTML = '';
                    
                    if (query.length === 0) {
                        optionsDiv.style.display = 'none';
                        return;
                    }
                    
                    const filtered = customerData.filter(c => 
                        c.label.toLowerCase().includes(query)
                    );
                    
                    filtered.forEach(customer => {
                        const div = document.createElement('div');
                        div.className = 'searchable-dropdown-option';
                        div.textContent = customer.label;
                        div.addEventListener('click', function() {
                            customerInput.value = customer.label;
                            customerId.value = customer.id;
                            optionsDiv.style.display = 'none';
                        });
                        optionsDiv.appendChild(div);
                    });
                    
                    if (filtered.length > 0) {
                        optionsDiv.style.display = 'block';
                    } else {
                        optionsDiv.style.display = 'none';
                    }
                });
                
                customerInput.addEventListener('blur', function() {
                    setTimeout(() => optionsDiv.style.display = 'none', 200);
                });
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }
    
    loadCustomers();
});
</script>
@endsection
