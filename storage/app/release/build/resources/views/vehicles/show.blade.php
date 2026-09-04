@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $vehicle->registration_number }}</h1>
        <p>{{ $vehicle->make }} {{ $vehicle->model }} · {{ $vehicle->category }}</p>
    </div>
    <div>
        <a class="secondary" href="#" onclick="openTransferModal()">Transfer Ownership</a>
        <a class="primary" href="{{ route('jobs.create', ['vehicle_id' => $vehicle->id]) }}">+ Create Job</a>
    </div>
</div>

<div class="grid2">
    <section class="panel">
        <h2>Vehicle details</h2>
        <div class="details">
            <span>Customer</span>
            <b>{{ $vehicle->customer->full_name }}</b>
            <span>Mileage</span>
            <b>{{ number_format($vehicle->mileage) }} km</b>
            <span>Fuel</span>
            <b>{{ $vehicle->fuel_level ?? '—' }}%</b>
            <span>VIN</span>
            <b>{{ $vehicle->vin ?? '—' }}</b>
        </div>
    </section>

    <section class="panel">
        <h2>Service timeline</h2>
        @forelse($vehicle->jobs->sortByDesc('created_at') as $j)
            <a class="listrow" href="{{ route('jobs.show', $j) }}">
                <b>{{ $j->job_number }}</b>
                <span>{{ $j->created_at->format('d M Y') }} · {{ $j->status->getLabel() }}</span>
            </a>
        @empty
            <p class="empty">No service history.</p>
        @endforelse
    </section>
</div>

{{-- Back button (centered) --}}
<div class="page-back">
    <a href="{{ route('vehicles.index') }}" class="back-button">← Back</a>
</div>

{{-- Transfer Ownership Modal --}}
<div id="transferModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Transfer Vehicle Ownership</h3>
            <button class="modal-close" onclick="closeTransferModal()">✕</button>
        </div>
        <div class="modal-body">
            <form id="transferForm" method="post" action="{{ route('vehicles.transfer_ownership', $vehicle->id) }}">
                @csrf
                <div class="form-group">
                    <label>New Customer*</label>
                    <select name="customer_id" id="customerSelect" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->full_name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Or Create New Customer</label>
                    <button type="button" class="secondary" onclick="openNewCustomerModal()">+ New Customer</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary" onclick="closeTransferModal()">Cancel</button>
            <button type="button" class="primary" onclick="submitTransfer()">Transfer</button>
        </div>
    </div>
</div>

{{-- New Customer Modal --}}
<div id="newCustomerModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Customer</h3>
            <button class="modal-close" onclick="closeNewCustomerModal()">✕</button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ route('customers.store') }}">
                <input type="hidden" name="redirect_to" value="{{ route('vehicles.show', $vehicle->id) }}">
                <input type="hidden" name="transfer_vehicle_id" value="{{ $vehicle->id }}">
                @csrf
                <div class="form-group">
                    <label>Full name*</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Phone*</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>WhatsApp</label>
                    <input type="text" name="whatsapp">
                </div>
                <div class="form-group">
                    <label>NIC / Passport</label>
                    <input type="text" name="nic">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary" onclick="closeNewCustomerModal()">Cancel</button>
            <button type="button" class="primary" onclick="document.querySelector('#newCustomerModal form').submit()">Create & Transfer</button>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
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

.modal-header h3 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #e5e7eb;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

/* Back button - centered */
.page-back {
    margin-top: 28px;
    display: flex;
    justify-content: center;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #e5e7eb;
    color: #374151;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s;
}

.back-button:hover {
    background: #d1d5db;
    color: #111827;
}
</style>

<script>
function openTransferModal() {
    document.getElementById('transferModal').style.display = 'flex';
}
function closeTransferModal() {
    document.getElementById('transferModal').style.display = 'none';
}
function openNewCustomerModal() {
    document.getElementById('newCustomerModal').style.display = 'flex';
}
function closeNewCustomerModal() {
    document.getElementById('newCustomerModal').style.display = 'none';
}
function submitTransfer() {
    document.getElementById('transferForm').submit();
}
</script>
@endsection