@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Create Service</h1>
        <p>Add a new service type</p>
    </div>
</div>

<div class="panel">
    <form method="post" action="{{ route('services.store') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="form-group">
                <label>Service Name *</label>
                <input type="text" name="name" required autofocus style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Vehicle Category</label>
                <div class="searchable-dropdown" id="vehicleCategoryDropdown">
                    <input type="hidden" name="vehicle_category" id="vehicle_category" value="">
                    <input type="text" class="searchable-dropdown-input" id="vehicleCategoryInput" placeholder="Select vehicle category...">
                    <div class="searchable-dropdown-options"></div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;"></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
            <div class="form-group">
                <label>Base Price (Rs.) *</label>
                <input type="number" name="base_price" step="0.01" min="0" required style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Labor Cost (Rs.)</label>
                <input type="number" name="labor_cost" step="0.01" min="0" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Tax Rate (%)</label>
                <input type="number" name="tax_rate" step="0.01" min="0" max="100" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="form-group">
                <label>Duration (minutes)</label>
                <input type="number" name="duration_minutes" min="1" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Status</label>
                <div class="toggle-wrapper">
                    <label class="toggle">
                        <input
                            type="checkbox"
                            id="active"
                            name="active"
                            value="1"
                            checked
                        >
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Active</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary" style="background:#3b82f6;padding:12px 24px;border-radius:8px;border:none;color:white;font-weight:500;cursor:pointer;">Create Service</button>
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
document.addEventListener('DOMContentLoaded', function() {
    console.log('Service create page - loading vehicle categories');

    // Vehicle categories data (same as in vehicle creation)
    const vehicleCategories = [
        { id: 'Bike', label: 'Bike' },
        { id: 'Motorcycle', label: 'Motorcycle' },
        { id: 'Three-wheeler', label: 'Three-wheeler' },
        { id: 'Small Car', label: 'Small Car' },
        { id: 'Sedan', label: 'Sedan' },
        { id: 'Minivan', label: 'Minivan' },
        { id: 'SUV', label: 'SUV' },
        { id: 'Jeep', label: 'Jeep' },
        { id: 'Pickup', label: 'Pickup' },
        { id: 'Van', label: 'Van' },
        { id: 'Bus', label: 'Bus' },
        { id: 'Lorry', label: 'Lorry' },
        { id: 'JCB Truck', label: 'JCB Truck' },
        { id: 'Boom Truck', label: 'Boom Truck' }
    ];

    // Initialize vehicle category dropdown
    const vehicleCategoryContainer = document.getElementById('vehicleCategoryDropdown');
    if (vehicleCategoryContainer) {
        new SearchableDropdown(vehicleCategoryContainer, {
            data: vehicleCategories
        });
    }
});
</script>

<style>
/* Toggle switch */
.toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
}

.toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

.toggle input:checked + .slider {
    background-color: #10b981;
}

.toggle input:checked + .slider:before {
    transform: translateX(20px);
}

.toggle-label {
    font-size: 14px;
    color: #6b7280;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
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