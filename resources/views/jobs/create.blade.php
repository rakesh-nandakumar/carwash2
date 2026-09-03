@extends('layouts.app')

@section('content')
<div class="page-head"><h1>New Job Card</h1></div>

<div class="panel form-panel">
    <form method="post" action="{{ route('jobs.store') }}">
        @csrf
        <div class="form-grid">
            <label>Customer*<select name="customer_id" id="customerSelect" required onchange="filterVehicles()">@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->full_name }} — {{ $c->phone }}</option>@endforeach</select></label>
            <label>Vehicle*<select name="vehicle_id" id="vehicleSelect" required><option value="">Select Customer First</option>@foreach($vehicles as $v)<option value="{{ $v->id }}" data-customer="{{ $v->customer_id }}" @selected(request('vehicle_id')==$v->id)>{{ $v->registration_number }} — {{ $v->make }} {{ $v->model }}</option>@endforeach</select></label>
            <label>Priority*<select name="priority"><option>normal</option><option>urgent</option><option>vip</option><option>waiting_customer</option><option>breakdown</option></select></label>
            <label class="wide">Customer complaint<textarea name="customer_complaint"></textarea></label>
            <fieldset class="wide"><legend>Requested services</legend><div class="checks">@foreach($services as $s)<label class="check"><input type="checkbox" name="service_ids[]" value="{{ $s->id }}"> {{ $s->name }} — Rs. {{ number_format($s->base_price,2) }}</label>@endforeach</div></fieldset>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Create Job Card</button>
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
const vehiclesData=@json($vehicles);
function filterVehicles(){
    const customerId=document.getElementById('customerSelect').value;
    const vehicleSelect=document.getElementById('vehicleSelect');
    const currentSelected=vehicleSelect.value;
    vehicleSelect.innerHTML='<option value="">Select Vehicle</option>';
    if(customerId){
        const customerVehicles=vehiclesData.filter(v=>v.customer_id==customerId);
        customerVehicles.forEach(v=>{
            const option=document.createElement('option');
            option.value=v.id;
            option.textContent=v.registration_number+' — '+v.make+' '+v.model;
            if(v.id==currentSelected)option.selected=true;
            vehicleSelect.appendChild(option);
        });
    }
}
</script>

<style>
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