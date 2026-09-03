@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Services</h1>
        <p>Manage service types and pricing</p>
    </div>
    <a class="primary" href="{{ route('services.create') }}">+ New Service</a>
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
    fetch(`/services/${serviceId}/toggle`, {
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
</style>
@endsection