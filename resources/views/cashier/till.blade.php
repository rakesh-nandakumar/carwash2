@extends('layouts.app')

@section('content')

<div class="panel">

    <h2>Till Settings</h2>

    <p>
        Configure the Main Till.
    </p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('settings.till.update') }}">
        @csrf
        @method('PUT')

        <div>
            <label>Till Name</label>
            <input
                type="text"
                value="{{ $till->name }}"
                disabled
            >
        </div>

        <div>
            <label>Code</label>
            <input
                type="text"
                value="{{ $till->code }}"
                disabled
            >
        </div>

        <div>
            <label for="opening_balance">
                Opening Balance
            </label>

            <input
                id="opening_balance"
                type="number"
                name="opening_balance"
                step="0.01"
                min="0"
                value="{{ old('opening_balance', $till->opening_balance) }}"
                required
            >
        </div>

        <div>
            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                rows="4"
            >{{ old('description', $till->description) }}</textarea>
        </div>

        <div>
            <label>
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $till->is_active))
                >

                Active
            </label>
        </div>

        <button type="submit" class="primary">
            Save Till Settings
        </button>

    </form>

</div>

@endsection