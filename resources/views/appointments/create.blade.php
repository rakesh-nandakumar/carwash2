@extends('layouts.app')

@section('content')
<div class="page-head">
    <h1>New Appointment</h1>
</div>

<div class="panel form-panel">
    <form method="post" action="{{ route('appointments.store') }}">
        @csrf
        <div class="form-grid">
            <label>
                Customer
                <select name="customer_id" id="customerSelect" onchange="filterVehicles()">
                    <option value="">Select Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->full_name }} — {{ $c->phone }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Vehicle
                <select name="vehicle_id" id="vehicleSelect">
                    <option value="">Select Customer First</option>
                </select>
            </label>
            <label>
                Date & time
                <input type="datetime-local" name="scheduled_at" required>
            </label>
            <label class="wide">
                Notes
                <textarea name="notes"></textarea>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Create Appointment</button>
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
    const vehiclesData = @json($vehicles);
    function filterVehicles() {
        const customerId = document.getElementById('customerSelect').value;
        const vehicleSelect = document.getElementById('vehicleSelect');
        vehicleSelect.innerHTML = '<option value="">Select Vehicle</option>';
        if (customerId) {
            const customerVehicles = vehiclesData.filter(v => v.customer_id == customerId);
            customerVehicles.forEach(v => {
                const option = document.createElement('option');
                option.value = v.id;
                option.textContent = v.registration_number + ' — ' + v.make + ' ' + v.model;
                vehicleSelect.appendChild(option);
            });
        }
    }
</script>

<style>
.form-panel .form-grid label {
    display: block;
    margin-bottom: 14px;
    font-size: 13px;
    font-weight: 600;
}
.form-panel .form-grid input,
.form-panel .form-grid select,
.form-panel .form-grid textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    font-size: 14px;
    border-radius: 12px;
    margin-top: 6px;
}
.form-panel .form-grid textarea {
    min-height: 90px;
    resize: vertical;
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
@endsection