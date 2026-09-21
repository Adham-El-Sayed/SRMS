@extends('layouts.app')

@section('content')
<div class="container">

    <h1>{{ __('Edit Table') }} {{ $table->number }}</h1>

    <form action="{{ route('tables.update', $table) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label for="number">{{ __('Table Number') }}</label>

            <input
                type="text"
                id="number"
                name="number"
                value="{{ old('number', $table->number) }}"
            >

            @error('number')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        <div>
            <label for="capacity">{{ __('Capacity') }}</label>

            <input
                type="number"
                id="capacity"
                name="capacity"
                value="{{ old('capacity', $table->capacity) }}"
            >

            @error('capacity')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        <div>
            <label for="status">{{ __('Status') }}</label>

            <select id="status" name="status">

                <option
                    value="available"
                    @selected(old('status', $table->status->value) === 'available')
                >
                    {{ __('Available') }}
                </option>

                <option
                    value="occupied"
                    @selected(old('status', $table->status->value) === 'occupied')
                >
                    {{ __('Occupied') }}
                </option>

                <option
                    value="reserved"
                    @selected(old('status', $table->status->value) === 'reserved')
                >
                    {{ __('Reserved') }}
                </option>

            </select>

            @error('status')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        <button type="submit">
            {{ __('Update Table') }}
        </button>

        <a href="{{ route('tables.index') }}">
            {{ __('Cancel') }}
        </a>

    </form>

</div>
@endsection