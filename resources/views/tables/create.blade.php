@extends('layouts.app')

@section('content')
<div class="container">

    <h1>{{ __('Add New Table') }}</h1>

    <form action="{{ route('tables.store') }}" method="POST">
        @csrf

        <div>
            <label for="number">{{ __('Table Number') }}</label>
            <input
                type="text"
                id="number"
                name="number"
                value="{{ old('number') }}"
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
                value="{{ old('capacity', 4) }}"
            >

            @error('capacity')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        <div>
            <label for="status">{{ __('Status') }}</label>

            <select id="status" name="status">
                <option value="available">{{ __('Available') }}</option>
                <option value="occupied">{{ __('Occupied') }}</option>
                <option value="reserved">{{ __('Reserved') }}</option>
            </select>

            @error('status')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        <button type="submit">
            {{ __('Save Table') }}
        </button>

        <a href="{{ route('tables.index') }}">
            {{ __('Cancel') }}
        </a>

    </form>

</div>
@endsection