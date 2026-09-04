@extends('layouts.app')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Appointment</h1>
        <p>
            {{ $appointment->customer->full_name }}
            —
            {{ $appointment->vehicle->registration_number }}
        </p>
    </div>
</div>

<div class="panel form-panel">

    <form method="POST" action="{{ route('appointments.update', $appointment) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">

            <label>
                Customer
                <input
                    type="text"
                    value="{{ $appointment->customer->full_name }}"
                    disabled
                >
            </label>

            <label>
                Vehicle
                <input
                    type="text"
                    value="{{ $appointment->vehicle->registration_number }}"
                    disabled
                >
            </label>

            <label>
                Date & time
                <input
                    type="datetime-local"
                    name="scheduled_at"
                    value="{{ old('scheduled_at', $appointment->scheduled_at?->format('Y-m-d\TH:i')) }}"
                    required
                >
            </label>

            <label>
                Status
                <select name="status" required>
                    <option
                        value="pending"
                        {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}
                    >
                        Pending
                    </option>

                    <option
                        value="confirmed"
                        {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}
                    >
                        Confirmed
                    </option>

                    <option
                        value="completed"
                        {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}
                    >
                        Completed
                    </option>

                    <option
                        value="cancelled"
                        {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}
                    >
                        Cancelled
                    </option>
                </select>
            </label>

            <label class="wide">
                Notes
                <textarea name="notes">{{ old('notes', $appointment->notes) }}</textarea>
            </label>

        </div>

        <div class="form-actions">

            <button type="submit" class="primary">
                Update Appointment
            </button>

            <a
                href="{{ route('appointments.index') }}"
                class="btn-cancel"
            >
                Cancel
            </a>

        </div>

    </form>

</div>

<style>
.form-panel .form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.form-panel .form-grid label {
    display: block;
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

.form-panel .form-grid input:disabled {
    background: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

.form-panel .form-grid textarea {
    min-height: 100px;
    resize: vertical;
}

.form-panel .wide {
    grid-column: 1 / -1;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    gap: 12px;
}

.btn-cancel {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 18px;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
}

.btn-cancel:hover {
    background: #fecaca;
    color: #b91c1c;
}

@media (max-width: 640px) {
    .form-panel .form-grid {
        grid-template-columns: 1fr;
    }

    .form-panel .wide {
        grid-column: auto;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .primary,
    .form-actions .btn-cancel {
        width: 100%;
    }
}
</style>

@endsection