@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Create New Till</h1>
        <p>Add a new till for a workstation</p>
    </div>
    <a class="secondary" href="{{ route('tills.index') }}">← Back to Tills</a>
</div>

<div class="panel">
    <form method="POST" action="{{ route('tills.store') }}">
        @csrf

        <div class="form-group">
            <label>Till Name *</label>
            <input type="text" name="name" required placeholder="e.g., Front Desk Till" value="{{ old('name') }}">
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>Till Code *</label>
            <input type="text" name="code" required placeholder="e.g., FRONT_DESK" value="{{ old('code') }}">
            <small>Unique identifier for this till (uppercase, no spaces)</small>
            @error('code') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" placeholder="e.g., Front Desk, Wash Bay 1" value="{{ old('location') }}">
            <small>Physical location of this till</small>
            @error('location') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>IP Address</label>
            <input type="text" name="ip_address" placeholder="e.g., 192.168.1.10" value="{{ old('ip_address') }}">
            <small>PC IP address for this workstation</small>
            @error('ip_address') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>Opening Balance *</label>
            <input type="number" name="opening_balance" step="0.01" min="0" required placeholder="0.00" value="{{ old('opening_balance', 0) }}">
            <small>Initial cash amount in this till</small>
            @error('opening_balance') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Optional description of this till">{{ old('description') }}</textarea>
            @error('description') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                Active
            </label>
            <small>Enable this till for use</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="primary">Create Till</button>
            <a href="{{ route('tills.index') }}" class="secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection