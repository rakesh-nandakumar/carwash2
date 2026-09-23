@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Edit Service</h1>
        <p>Update service details</p>
    </div>
    <a class="secondary" href="{{ route('services.index') }}">← Back to Services</a>
</div>

<div class="panel form-panel">
    <form method="post" action="{{ route('services.update', $service) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            {{-- Service Name --}}
            <label>
                Service Name *
                <input type="text" name="name" value="{{ old('name', $service->name) }}" required autofocus>
            </label>

            {{-- Vehicle Category --}}
            <label>
                Vehicle Category
                <div class="custom-select" id="vehicleCategorySelect">
                    <input type="hidden" name="vehicle_category" id="vehicle_category" value="{{ old('vehicle_category', $service->vehicle_category) }}">
                    <div class="select-trigger" id="vehicleCategoryTrigger">
                        <input type="text" id="vehicleCategoryInput" placeholder="Search or select vehicle category..." autocomplete="off" value="{{ old('vehicle_category', $service->vehicle_category) }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="select-options" id="vehicleCategoryOptions">
                        <div class="select-option" data-value="">Select vehicle category...</div>
                        <div class="select-option" data-value="Bike">Bike</div>
                        <div class="select-option" data-value="Motorcycle">Motorcycle</div>
                        <div class="select-option" data-value="Three-wheeler">Three-wheeler</div>
                        <div class="select-option" data-value="Small Car">Small Car</div>
                        <div class="select-option" data-value="Sedan">Sedan</div>
                        <div class="select-option" data-value="Minivan">Minivan</div>
                        <div class="select-option" data-value="SUV">SUV</div>
                        <div class="select-option" data-value="Jeep">Jeep</div>
                        <div class="select-option" data-value="Pickup">Pickup</div>
                        <div class="select-option" data-value="Van">Van</div>
                        <div class="select-option" data-value="Bus">Bus</div>
                        <div class="select-option" data-value="Lorry">Lorry</div>
                        <div class="select-option" data-value="JCB Truck">JCB Truck</div>
                        <div class="select-option" data-value="Boom Truck">Boom Truck</div>
                        <div class="select-no-results" id="vehicleCategoryNoResults">No results found</div>
                    </div>
                </div>
            </label>

            {{-- Description --}}
            <label class="wide">
                Description
                <textarea name="description" rows="3">{{ old('description', $service->description) }}</textarea>
            </label>

            {{-- Pricing --}}
            <label>
                Base Price (Rs.) *
                <input type="number" name="base_price" step="0.01" min="0"
                       value="{{ old('base_price', $service->base_price) }}" required>
            </label>

            <label>
                Labor Cost (Rs.)
                <input type="number" name="labor_cost" step="0.01" min="0"
                       value="{{ old('labor_cost', $service->labor_cost) }}">
            </label>

            <label>
                Tax Rate (%)
                <input type="number" name="tax_rate" step="0.01" min="0" max="100"
                       value="{{ old('tax_rate', $service->tax_rate) }}">
            </label>

            {{-- Duration --}}
            <label>
                Duration (minutes)
                <input type="number" name="duration_minutes" min="1"
                       value="{{ old('duration_minutes', $service->duration_minutes) }}">
            </label>

            {{-- Status --}}
            <label>
                Status
                <div class="toggle-wrapper">
                    <label class="toggle">
                        <input type="checkbox" name="active" value="1"
                               {{ old('active', $service->active) ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">{{ old('active', $service->active) ? 'Active' : 'Inactive' }}</span>
                </div>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Update Service</button>
            <a href="{{ route('services.index') }}" class="btn-cancel">
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
/* Form grid */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-grid label.wide {
    grid-column: 1 / -1;
}

/* Toggle switch */
.toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
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

/* Form actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
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

/* Modal */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 28px;
    border-radius: 12px;
    width: 100%;
    max-width: 420px;
}

.modal-content h3 {
    margin: 0 0 20px 0;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 24px;
}

/* Responsive */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head a.secondary {
        width: 100%;
        text-align: center;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

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

<style>
.custom-select {
    position: relative;
    width: 100%;
}

.select-trigger {
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 46px;
}

.select-trigger input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
    color: #374151;
}

.select-trigger input::placeholder {
    color: #9ca3af;
}

.select-trigger input:focus {
    outline: none;
}

.select-trigger:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.select-no-results {
    padding: 12px;
    color: #9ca3af;
    text-align: center;
    display: none;
}

.select-trigger:hover {
    border-color: #d1d5db;
}

.select-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-top: 4px;
    max-height: 150px;
    overflow-y: auto;
    z-index: 10;
    display: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.select-options.open {
    display: block;
}

.select-option {
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
}

.select-option:last-child {
    border-bottom: none;
}

.select-option:hover {
    background: #f9fafb;
}

.select-option.selected {
    background: #eff6ff;
    color: #3b82f6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('vehicleCategorySelect');
    const trigger = document.getElementById('vehicleCategoryTrigger');
    const options = document.getElementById('vehicleCategoryOptions');
    const hiddenInput = document.getElementById('vehicle_category');
    const textInput = document.getElementById('vehicleCategoryInput');
    const optionElements = options.querySelectorAll('.select-option');
    const currentValue = hiddenInput.value;

    // Store all options for filtering (exclude no-results element)
    const allOptions = Array.from(optionElements).filter(el => !el.classList.contains('select-no-results')).map(el => ({
        element: el,
        value: el.getAttribute('data-value'),
        text: el.textContent
    }));

    // Open dropdown when input is focused or clicked
    textInput.addEventListener('focus', function() {
        options.classList.add('open');
    });

    textInput.addEventListener('click', function() {
        options.classList.add('open');
    });

    // Filter options based on input
    textInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;
        
        allOptions.forEach(option => {
            const matches = option.text.toLowerCase().includes(searchTerm);
            option.element.style.display = matches ? 'block' : 'none';
            if (matches) visibleCount++;
        });
        
        // Show/hide no results message
        const noResults = document.getElementById('vehicleCategoryNoResults');
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        options.classList.add('open');
    });

    // Select option when clicked
    optionElements.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.textContent;
            
            hiddenInput.value = value;
            textInput.value = text;
            options.classList.remove('open');
            
            // Update selected state
            optionElements.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Set initial value if editing
    if (currentValue) {
        const selectedOption = Array.from(optionElements).find(opt => opt.getAttribute('data-value') === currentValue);
        if (selectedOption) {
            selectedOption.classList.add('selected');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!select.contains(e.target)) {
            options.classList.remove('open');
        }
    });
});
</script>
@endsection