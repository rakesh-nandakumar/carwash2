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

            {{-- Category --}}
            <label>
                Category
                <div style="display:flex; gap:10px; align-items:flex-start;">
                    <select name="service_category_id" id="categorySelect" style="flex:1;">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('service_category_id', $service->service_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="secondary" onclick="openCategoryModal()" style="white-space:nowrap;">
                        + Add Category
                    </button>
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

{{-- Add Category Modal --}}
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <h3>Add New Category</h3>

        <form id="categoryForm">
            @csrf
            <label>
                Category Name *
                <input type="text" id="categoryName" name="name" required>
            </label>

            <label style="margin-top: 16px;">
                Status
                <div class="toggle-wrapper">
                    <label class="toggle">
                        <input type="checkbox" id="categoryActive" name="active" value="1" checked>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Active</span>
                </div>
            </label>

            <div class="modal-actions">
                <button type="button" class="secondary" onclick="closeCategoryModal()">Cancel</button>
                <button type="submit" class="primary">Add Category</button>
            </div>
        </form>
    </div>
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

<script>
function openCategoryModal() {
    document.getElementById('categoryModal').style.display = 'flex';
}

function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
    document.getElementById('categoryForm').reset();
}

document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const name = document.getElementById('categoryName').value;
    const active = document.getElementById('categoryActive').checked;

    fetch('{{ route('service-categories.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name, active })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('categorySelect');
            const option = document.createElement('option');
            option.value = data.category.id;
            option.textContent = data.category.name;
            option.selected = true;
            select.appendChild(option);
            closeCategoryModal();
        } else {
            alert(data.error || 'Error creating category');
        }
    })
    .catch(() => alert('Error creating category. Please try again.'));
});
</script>
@endsection