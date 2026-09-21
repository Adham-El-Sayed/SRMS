<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ __('Add Product') }}</title>
</head>

<body>

    <h1>{{ __('Add Product') }}</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('products.store') }}"
        method="POST"
    >
        @csrf

        <div>
            <label for="category_id">
                {{ __('Category') }}
            </label>

            <select
                id="category_id"
                name="category_id"
                required
            >
                <option value="">
                    {{ __('Select Category') }}
                </option>

                @foreach($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        {{ old('category_id') == $category->id ? 'selected' : '' }}
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label for="name">
                {{ __('Product Name') }}
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="description">
                {{ __('Description') }}
            </label>

            <textarea
                id="description"
                name="description"
            >{{ old('description') }}</textarea>
        </div>

        <br>

        <div>
            <label for="price">
                {{ __('Price') }}
            </label>

            <input
                type="number"
                id="price"
                name="price"
                value="{{ old('price') }}"
                step="0.01"
                min="0"
                required
            >
        </div>

        <br>

        <div>
            <label>
                <input type="hidden" name="is_active" value="0">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    checked
                >

                {{ __('Active') }}
            </label>
        </div>

        <br>

        <button type="submit">
            {{ __('Create Product') }}
        </button>

        <a href="{{ route('products.index') }}">
            {{ __('Cancel') }}
        </a>
    </form>

</body>
</html>