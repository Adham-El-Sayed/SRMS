<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Product</title>
</head>

<body>

    <h1>Add Product</h1>

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
                Category
            </label>

            <select
                id="category_id"
                name="category_id"
                required
            >
                <option value="">
                    Select Category
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
                Product Name
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
                Description
            </label>

            <textarea
                id="description"
                name="description"
            >{{ old('description') }}</textarea>
        </div>

        <br>

        <div>
            <label for="price">
                Price
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

                Active
            </label>
        </div>

        <br>

        <button type="submit">
            Create Product
        </button>

        <a href="{{ route('products.index') }}">
            Cancel
        </a>
    </form>

</body>
</html>