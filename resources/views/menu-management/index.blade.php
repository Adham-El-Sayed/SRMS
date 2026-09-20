@extends('layouts.app')

@section('title', 'Menu Management')

@section('content')

<div class="menu-management">

    {{-- =========================
        Page Header
    ========================== --}}
    <div class="page-header">

        <div class="page-title">

            <span class="page-kicker">
                MENU
            </span>

            <h1>Menu Management</h1>

            <p>
                Manage categories and products from one place.
            </p>

        </div>

        <button
            type="button"
            class="primary-btn"
            id="create-category-btn"
        >
            <span class="btn-icon">+</span>
            Create Category
        </button>

    </div>


    {{-- =========================
        Success Message
    ========================== --}}
    @if(session('success'))

        <div class="alert success-message">
            <div class="alert-icon">✓</div>

            <div>
                <strong>Success</strong>
                <span>{{ session('success') }}</span>
            </div>
        </div>

    @endif


    {{-- =========================
        Error Message
    ========================== --}}
    @if(session('error'))

        <div class="alert error-message">
            <div class="alert-icon">!</div>

            <div>
                <strong>Something went wrong</strong>
                <span>{{ session('error') }}</span>
            </div>
        </div>

    @endif


    {{-- =========================
        Validation Errors
    ========================== --}}
    @if($errors->any())

        <div class="alert error-message">

            <div class="alert-icon">!</div>

            <div>

                <strong>Please check the following:</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        </div>

    @endif


    {{-- =========================
        Categories
    ========================== --}}
    <div class="categories-container">

        @forelse($categories as $category)

            <div class="category-card {{ !$category->is_active ? 'category-inactive' : '' }}">

                {{-- =========================
                    Category Header
                ========================== --}}
                <div class="category-header">

                    <div class="category-info">

                        <div class="category-image">

                            @if($category->image)

                                <img
                                    src="{{ asset('storage/' . $category->image) }}"
                                    alt="{{ $category->name }}"
                                >

                            @else

                                <div class="image-placeholder">
                                    <span>🍽️</span>
                                </div>

                            @endif

                        </div>


                        <div class="category-details">

                            <div class="category-name-row">

                                <h2>
                                    {{ $category->name }}
                                </h2>

                                <span
                                    class="status-badge {{ $category->is_active ? 'active' : 'inactive' }}"
                                >
                                    <span class="status-dot"></span>

                                    {{ $category->is_active ? 'Active' : 'Inactive' }}

                                </span>

                            </div>


                            @if($category->description)

                                <p>
                                    {{ $category->description }}
                                </p>

                            @else

                                <p class="muted-text">
                                    No description added.
                                </p>

                            @endif

                        </div>

                    </div>


                    {{-- =========================
                        Category Actions
                    ========================== --}}
                    <div class="category-actions">

                        <button
                            type="button"
                            class="edit-btn edit-category-btn"
                            data-id="{{ $category->id }}"
                            data-name="{{ $category->name }}"
                            data-description="{{ $category->description }}"
                            data-active="{{ $category->is_active ? '1' : '0' }}"
                            data-image="{{ $category->image ? asset('storage/' . $category->image) : '' }}"
                            data-action="{{ route('menu.management.categories.update', $category) }}"
                        >
                            Edit
                        </button>


                        <form
                            action="{{ route('menu.management.categories.toggle', $category) }}"
                            method="POST"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="toggle-btn {{ $category->is_active ? 'deactivate' : 'activate' }}"
                            >
                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                            </button>

                        </form>

                    </div>

                </div>


                {{-- =========================
                    Products Section
                ========================== --}}
                <div class="products-section">

                    <div class="products-title">

                        <div>

                            <h3>
                                Products
                            </h3>

                            <p>
                                Items inside this category
                            </p>

                        </div>

                        <span class="product-count">
                            {{ $category->products->count() }}

                            {{ Str::plural('product', $category->products->count()) }}
                        </span>

                    </div>


                    @if($category->products->count())

                        <div class="products-grid">

                            @foreach($category->products as $product)

                                <div
                                    class="product-card {{ !$product->is_active ? 'product-inactive' : '' }}"
                                >

                                    {{-- Product Image --}}
                                    <div class="product-image">

                                        @if($product->image)

                                            <img
                                                src="{{ asset('storage/' . $product->image) }}"
                                                alt="{{ $product->name }}"
                                            >

                                        @else

                                            <div class="product-placeholder">
                                                <span>🍔</span>
                                            </div>

                                        @endif

                                        @if(!$product->is_active)

                                            <div class="inactive-overlay">
                                                Inactive
                                            </div>

                                        @endif

                                    </div>


                                    {{-- Product Content --}}
                                    <div class="product-content">

                                        <div class="product-top">

                                            <div>

                                                <h4>
                                                    {{ $product->name }}
                                                </h4>

                                                @if($product->description)

                                                    <p class="product-description">
                                                        {{ $product->description }}
                                                    </p>

                                                @else

                                                    <p class="product-description muted-text">
                                                        No description.
                                                    </p>

                                                @endif

                                            </div>

                                            <span class="product-price">
                                                ${{ number_format($product->price, 2) }}
                                            </span>

                                        </div>


                                        <div class="product-bottom">

                                            <span
                                                class="status-badge small {{ $product->is_active ? 'active' : 'inactive' }}"
                                            >

                                                <span class="status-dot"></span>

                                                {{ $product->is_active ? 'Active' : 'Inactive' }}

                                            </span>


                                            <div class="product-actions">

                                                {{-- Edit --}}
                                                <button
                                                    type="button"
                                                    class="small-edit-btn edit-product-btn"
                                                    data-id="{{ $product->id }}"
                                                    data-category-id="{{ $category->id }}"
                                                    data-name="{{ $product->name }}"
                                                    data-description="{{ $product->description }}"
                                                    data-price="{{ $product->price }}"
                                                    data-active="{{ $product->is_active ? '1' : '0' }}"
                                                    data-image="{{ $product->image ? asset('storage/' . $product->image) : '' }}"
                                                    data-action="{{ route('menu.management.products.update', $product) }}"
                                                >
                                                    Edit
                                                </button>


                                                {{-- Activate / Deactivate --}}
                                                <form
                                                    action="{{ route('menu.management.products.toggle', $product) }}"
                                                    method="POST"
                                                >

                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="small-toggle-btn {{ $product->is_active ? 'deactivate' : 'activate' }}"
                                                    >
                                                        {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="no-products">

                            <div class="no-products-icon">
                                🍽️
                            </div>

                            <h4>
                                No products yet
                            </h4>

                            <p>
                                Add the first product to this category.
                            </p>

                        </div>

                    @endif

                </div>


                {{-- =========================
                    Category Footer
                ========================== --}}
                <div class="category-footer">

                    <button
                        type="button"
                        class="add-product-btn add-product-trigger"
                        data-category-id="{{ $category->id }}"
                        data-category-name="{{ $category->name }}"
                    >
                        <span>+</span>
                        Add Product
                    </button>

                </div>

            </div>

        @empty

            <div class="empty-categories">

                <div class="empty-icon">
                    🍽️
                </div>

                <h2>
                    No Categories Yet
                </h2>

                <p>
                    Create your first category to start building your menu.
                </p>

                <button
                    type="button"
                    class="primary-btn"
                    id="empty-create-category-btn"
                >
                    <span class="btn-icon">+</span>
                    Create Category
                </button>

            </div>

        @endforelse

    </div>

</div>


{{-- ============================================================
     CREATE CATEGORY MODAL
============================================================ --}}

<div id="category-modal" class="modal">

    <div class="modal-box">

        <div class="modal-header">

            <div>

                <span class="modal-kicker">
                    NEW CATEGORY
                </span>

                <h2>
                    Create Category
                </h2>

                <p>
                    Add a new category to your menu.
                </p>

            </div>

            <button
                type="button"
                class="close-btn"
                data-close-modal="category-modal"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            action="{{ route('menu.management.categories.store') }}"
            enctype="multipart/form-data"
        >

            @csrf

            <div class="form-group">

                <label for="category-name">
                    Category Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="category-name"
                    placeholder="e.g. Burgers"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    placeholder="Category description..."
                    rows="3"
                ></textarea>

            </div>


            <div class="form-group">

                <label>
                    Category Image
                </label>

                <input
                    type="file"
                    name="image"
                    id="category-image"
                    accept="image/jpeg,image/png,image/webp"
                >

                <div
                    id="category-preview"
                    class="image-preview hidden"
                ></div>

            </div>


            <label class="checkbox-label">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    checked
                >

                <span>
                    Make category active
                </span>

            </label>


            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    data-close-modal="category-modal"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="primary-btn"
                >
                    Create Category
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ============================================================
     EDIT CATEGORY MODAL
============================================================ --}}

<div id="edit-category-modal" class="modal">

    <div class="modal-box">

        <div class="modal-header">

            <div>

                <span class="modal-kicker">
                    CATEGORY
                </span>

                <h2>
                    Edit Category
                </h2>

                <p>
                    Update category information.
                </p>

            </div>

            <button
                type="button"
                class="close-btn"
                data-close-modal="edit-category-modal"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            id="edit-category-form"
            enctype="multipart/form-data"
        >

            @csrf
            @method('PUT')


            <div class="form-group">

                <label>
                    Category Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="edit-category-name"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    id="edit-category-description"
                    rows="3"
                ></textarea>

            </div>


            <div class="form-group">

                <label>
                    Current Image
                </label>

                <div
                    id="edit-category-current-image"
                    class="current-image-container"
                ></div>

            </div>


            <div class="form-group">

                <label>
                    Change Image
                </label>

                <input
                    type="file"
                    name="image"
                    id="edit-category-image"
                    accept="image/jpeg,image/png,image/webp"
                >

                <div
                    id="edit-category-preview"
                    class="image-preview hidden"
                ></div>

            </div>


            <label class="remove-image-label">

                <input
                    type="checkbox"
                    name="remove_image"
                    value="1"
                    id="edit-category-remove-image"
                >

                <span>
                    Remove current image
                </span>

            </label>


            <label class="checkbox-label">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    id="edit-category-active"
                >

                <span>
                    Category is active
                </span>

            </label>


            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    data-close-modal="edit-category-modal"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="primary-btn"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ============================================================
     CREATE PRODUCT MODAL
============================================================ --}}

<div id="product-modal" class="modal">

    <div class="modal-box">

        <div class="modal-header">

            <div>

                <span class="modal-kicker">
                    NEW PRODUCT
                </span>

                <h2>
                    Add Product
                </h2>

                <p>
                    Add a product to
                    <strong id="selected-category-name"></strong>
                </p>

            </div>

            <button
                type="button"
                class="close-btn"
                data-close-modal="product-modal"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            action="{{ route('menu.management.products.store') }}"
            enctype="multipart/form-data"
        >

            @csrf

            <input
                type="hidden"
                name="category_id"
                id="product-category-id"
            >


            <div class="selected-category">

                <span>
                    Category
                </span>

                <strong id="selected-category-display"></strong>

            </div>


            <div class="form-group">

                <label>
                    Product Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="product-name"
                    placeholder="e.g. Classic Burger"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    placeholder="Product description..."
                    rows="3"
                ></textarea>

            </div>


            <div class="form-group">

                <label>
                    Price
                </label>

                <div class="price-input">

                    <span>
                        $
                    </span>

                    <input
                        type="number"
                        name="price"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Product Image
                </label>

                <input
                    type="file"
                    name="image"
                    id="product-image"
                    accept="image/jpeg,image/png,image/webp"
                >

                <div
                    id="product-preview"
                    class="image-preview hidden"
                ></div>

            </div>


            <label class="checkbox-label">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    checked
                >

                <span>
                    Make product active
                </span>

            </label>


            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    data-close-modal="product-modal"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="primary-btn"
                >
                    Add Product
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ============================================================
     EDIT PRODUCT MODAL
============================================================ --}}

<div id="edit-product-modal" class="modal">

    <div class="modal-box">

        <div class="modal-header">

            <div>

                <span class="modal-kicker">
                    PRODUCT
                </span>

                <h2>
                    Edit Product
                </h2>

                <p>
                    Update product information.
                </p>

            </div>

            <button
                type="button"
                class="close-btn"
                data-close-modal="edit-product-modal"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            id="edit-product-form"
            enctype="multipart/form-data"
        >

            @csrf
            @method('PUT')


            <div class="form-group">

                <label>
                    Product Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="edit-product-name"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Category
                </label>

                <select
                    name="category_id"
                    id="edit-product-category"
                    required
                >

                    @foreach($categories as $category)

                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    id="edit-product-description"
                    rows="3"
                ></textarea>

            </div>


            <div class="form-group">

                <label>
                    Price
                </label>

                <div class="price-input">

                    <span>
                        $
                    </span>

                    <input
                        type="number"
                        name="price"
                        id="edit-product-price"
                        step="0.01"
                        min="0"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Current Image
                </label>

                <div
                    id="edit-product-current-image"
                    class="current-image-container"
                ></div>

            </div>


            <div class="form-group">

                <label>
                    Change Image
                </label>

                <input
                    type="file"
                    name="image"
                    id="edit-product-image"
                    accept="image/jpeg,image/png,image/webp"
                >

                <div
                    id="edit-product-preview"
                    class="image-preview hidden"
                ></div>

            </div>


            <label class="remove-image-label">

                <input
                    type="checkbox"
                    name="remove_image"
                    value="1"
                    id="edit-product-remove-image"
                >

                <span>
                    Remove current image
                </span>

            </label>


            <label class="checkbox-label">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    id="edit-product-active"
                >

                <span>
                    Product is active
                </span>

            </label>


            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    data-close-modal="edit-product-modal"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="primary-btn"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


@endsection


{{-- ============================================================
     CSS
============================================================ --}}

@push('styles')

<style>

* {
    box-sizing: border-box;
}

.menu-management {
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
}


/* =========================
   Header
========================= */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 25px;
    margin-bottom: 32px;
}

.page-kicker,
.modal-kicker {
    display: block;
    color: #2563eb;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.2px;
    margin-bottom: 7px;
}

.page-title h1 {
    margin: 0 0 7px;
    color: #172033;
    font-size: 32px;
    line-height: 1.15;
    font-weight: 700;
}

.page-title p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}


/* =========================
   Buttons
========================= */

button {
    font-family: inherit;
}

.primary-btn {
    border: none;
    background: #2563eb;
    color: white;
    padding: 12px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: 0.2s ease;
}

.primary-btn:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.btn-icon {
    font-size: 18px;
    line-height: 1;
}


/* =========================
   Alerts
========================= */

.alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    margin-bottom: 22px;
}

.alert-icon {
    width: 27px;
    height: 27px;
    flex-shrink: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
}

.alert strong {
    display: block;
    font-size: 13px;
    margin-bottom: 2px;
}

.alert span {
    font-size: 13px;
}

.success-message {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.success-message .alert-icon {
    background: #d1fae5;
}

.error-message {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.error-message .alert-icon {
    background: #fee2e2;
}

.error-message ul {
    margin: 7px 0 0;
    padding-left: 18px;
    font-size: 13px;
}


/* =========================
   Category Card
========================= */

.category-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    margin-bottom: 24px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(15, 23, 42, 0.05);
    transition: 0.2s ease;
}

.category-card:hover {
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
}

.category-inactive {
    border-color: #e5e7eb;
}

.category-header {
    padding: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    border-bottom: 1px solid #eef0f4;
}

.category-info {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 0;
}

.category-image {
    width: 76px;
    height: 76px;
    flex-shrink: 0;
    border-radius: 15px;
    overflow: hidden;
    background: #f3f4f6;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-placeholder,
.product-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f6fa;
}

.image-placeholder span,
.product-placeholder span {
    font-size: 28px;
}

.category-details {
    min-width: 0;
}

.category-name-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.category-info h2 {
    margin: 0;
    color: #172033;
    font-size: 20px;
    font-weight: 700;
}

.category-info p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}

.muted-text {
    color: #9ca3af !important;
}


/* =========================
   Status
========================= */

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 9px;
    border-radius: 999px;
    white-space: nowrap;
}

.status-badge.small {
    font-size: 10px;
    padding: 4px 8px;
}

.status-badge.active {
    background: #ecfdf5;
    color: #047857;
}

.status-badge.inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}


/* =========================
   Category Actions
========================= */

.category-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.category-actions form {
    margin: 0;
}

.edit-btn,
.toggle-btn,
.small-edit-btn,
.small-toggle-btn {
    border: none;
    cursor: pointer;
    border-radius: 8px;
    font-weight: 600;
    transition: 0.2s ease;
}

.edit-btn {
    padding: 9px 13px;
    background: #eff6ff;
    color: #2563eb;
}

.edit-btn:hover {
    background: #dbeafe;
}

.toggle-btn {
    padding: 9px 13px;
}

.toggle-btn.deactivate,
.small-toggle-btn.deactivate {
    background: #fff7ed;
    color: #c2410c;
}

.toggle-btn.deactivate:hover,
.small-toggle-btn.deactivate:hover {
    background: #ffedd5;
}

.toggle-btn.activate,
.small-toggle-btn.activate {
    background: #ecfdf5;
    color: #047857;
}

.toggle-btn.activate:hover,
.small-toggle-btn.activate:hover {
    background: #d1fae5;
}


/* =========================
   Products
========================= */

.products-section {
    padding: 22px;
}

.products-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 17px;
}

.products-title h3 {
    margin: 0 0 3px;
    color: #172033;
    font-size: 16px;
}

.products-title p {
    margin: 0;
    color: #9ca3af;
    font-size: 12px;
}

.product-count {
    padding: 6px 10px;
    border-radius: 8px;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 12px;
    font-weight: 600;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}


/* =========================
   Product Card
========================= */

.product-card {
    display: flex;
    gap: 14px;
    min-width: 0;
    border: 1px solid #e5e7eb;
    border-radius: 13px;
    padding: 12px;
    background: #fafbfc;
    transition: 0.2s ease;
}

.product-card:hover {
    border-color: #d1d5db;
    background: #ffffff;
}

.product-inactive {
    opacity: 0.62;
}

.product-image {
    width: 105px;
    height: 105px;
    flex-shrink: 0;
    position: relative;
    border-radius: 11px;
    overflow: hidden;
    background: #f3f4f6;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.inactive-overlay {
    position: absolute;
    left: 7px;
    bottom: 7px;
    padding: 4px 7px;
    border-radius: 6px;
    background: rgba(17, 24, 39, 0.78);
    color: white;
    font-size: 10px;
    font-weight: 700;
}

.product-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.product-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

.product-top h4 {
    margin: 0;
    color: #172033;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.3;
}

.product-price {
    color: #2563eb;
    font-size: 15px;
    font-weight: 800;
    white-space: nowrap;
}

.product-description {
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
    margin: 6px 0 0;
}

.product-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-top: auto;
    padding-top: 12px;
}

.product-actions {
    display: flex;
    align-items: center;
    gap: 5px;
}

.product-actions form {
    margin: 0;
}

.small-edit-btn {
    padding: 6px 9px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 11px;
}

.small-edit-btn:hover {
    background: #dbeafe;
}

.small-toggle-btn {
    padding: 6px 9px;
    font-size: 11px;
}


/* =========================
   Add Product
========================= */

.category-footer {
    padding: 14px 22px;
    border-top: 1px solid #eef0f4;
    background: #fafbfc;
}

.add-product-btn {
    width: 100%;
    padding: 10px;
    border: 1px dashed #93c5fd;
    border-radius: 9px;
    background: #eff6ff;
    color: #2563eb;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: 0.2s ease;
}

.add-product-btn span {
    font-size: 17px;
    margin-right: 4px;
}

.add-product-btn:hover {
    background: #dbeafe;
}


/* =========================
   Empty Products
========================= */

.no-products {
    text-align: center;
    padding: 32px 20px;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    background: #fafafa;
}

.no-products-icon {
    font-size: 28px;
    margin-bottom: 7px;
}

.no-products h4 {
    margin: 0 0 5px;
    color: #374151;
    font-size: 14px;
}

.no-products p {
    margin: 0;
    color: #9ca3af;
    font-size: 12px;
}


/* =========================
   Empty Categories
========================= */

.empty-categories {
    text-align: center;
    padding: 75px 20px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.empty-categories h2 {
    margin: 0 0 8px;
    color: #172033;
    font-size: 21px;
}

.empty-categories p {
    color: #6b7280;
    margin: 0 0 22px;
    font-size: 14px;
}


/* =========================
   Modal
========================= */

.modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(15, 23, 42, 0.58);
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(3px);
}

.modal.show {
    display: flex;
}

.modal-box {
    width: 100%;
    max-width: 540px;
    max-height: 90vh;
    overflow-y: auto;
    background: white;
    border-radius: 18px;
    padding: 27px;
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.22);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 25px;
}

.modal-header h2 {
    margin: 0 0 5px;
    color: #172033;
    font-size: 22px;
}

.modal-header p {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
}

.close-btn {
    border: none;
    background: #f3f4f6;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    font-size: 22px;
    color: #6b7280;
    cursor: pointer;
    line-height: 1;
    flex-shrink: 0;
}

.close-btn:hover {
    background: #e5e7eb;
}


/* =========================
   Form
========================= */

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="file"],
.form-group textarea,
.form-group select {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 9px;
    padding: 11px 12px;
    font-family: inherit;
    font-size: 13px;
    color: #172033;
    background: white;
    outline: none;
    transition: 0.2s ease;
}

.form-group textarea {
    resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.price-input {
    position: relative;
}

.price-input span {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 13px;
    z-index: 1;
}

.price-input input {
    padding-left: 28px !important;
}


/* =========================
   Checkbox
========================= */

.checkbox-label,
.remove-image-label {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 18px;
    cursor: pointer;
    color: #374151;
    font-size: 13px;
}

.remove-image-label {
    color: #dc2626;
}

.checkbox-label input,
.remove-image-label input {
    width: 16px;
    height: 16px;
}


/* =========================
   Selected Category
========================= */

.selected-category {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 11px 13px;
    border-radius: 9px;
    margin-bottom: 18px;
    font-size: 13px;
}

.selected-category span {
    color: #6b7280;
}

.selected-category strong {
    color: #1d4ed8;
}


/* =========================
   Modal Actions
========================= */

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    margin-top: 25px;
}

.cancel-btn {
    border: none;
    background: #f3f4f6;
    color: #374151;
    padding: 11px 17px;
    border-radius: 9px;
    cursor: pointer;
    font-weight: 600;
}

.cancel-btn:hover {
    background: #e5e7eb;
}


/* =========================
   Images
========================= */

.current-image-container {
    min-height: 20px;
}

.current-image-container img {
    width: 120px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.no-current-image {
    color: #9ca3af;
    font-size: 13px;
}

.image-preview {
    margin-top: 12px;
    padding: 10px;
    border: 1px dashed #93c5fd;
    border-radius: 10px;
    background: #f8fbff;
}

.image-preview img {
    display: block;
    width: 150px;
    height: 120px;
    object-fit: cover;
    border-radius: 9px;
    margin: auto;
}

.hidden {
    display: none;
}


/* =========================
   Responsive
========================= */

@media (max-width: 900px) {

    .products-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 700px) {

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .page-header .primary-btn {
        width: 100%;
    }

    .category-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .category-actions {
        width: 100%;
    }

    .category-actions button,
    .category-actions form {
        flex: 1;
    }

    .category-actions .toggle-btn {
        width: 100%;
    }

}


@media (max-width: 550px) {

    .page-title h1 {
        font-size: 26px;
    }

    .category-header,
    .products-section {
        padding: 17px;
    }

    .category-footer {
        padding: 13px 17px;
    }

    .category-info {
        align-items: flex-start;
    }

    .category-image {
        width: 62px;
        height: 62px;
    }

    .category-info h2 {
        font-size: 17px;
    }

    .product-card {
        flex-direction: column;
    }

    .product-image {
        width: 100%;
        height: 190px;
    }

    .product-top {
        flex-direction: column;
    }

    .product-bottom {
        align-items: flex-start;
        flex-direction: column;
    }

    .product-actions {
        width: 100%;
    }

    .product-actions button,
    .product-actions form {
        flex: 1;
    }

    .product-actions .small-toggle-btn,
    .product-actions .small-edit-btn {
        width: 100%;
    }

    .modal {
        padding: 12px;
    }

    .modal-box {
        padding: 20px;
        border-radius: 15px;
    }

    .modal-actions {
        flex-direction: column-reverse;
    }

    .modal-actions button {
        width: 100%;
    }

}

</style>

@endpush


{{-- ============================================================
     JAVASCRIPT
============================================================ --}}

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Modal Helpers
    |--------------------------------------------------------------------------
    */

    function openModal(id) {

        const modal = document.getElementById(id);

        if (modal) {
            modal.classList.add('show');
        }

    }


    function closeModal(id) {

        const modal = document.getElementById(id);

        if (modal) {
            modal.classList.remove('show');
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Create Category
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('#create-category-btn, #empty-create-category-btn')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                openModal('category-modal');

                setTimeout(function () {

                    const input =
                        document.getElementById('category-name');

                    if (input) {
                        input.focus();
                    }

                }, 100);

            });

        });


    /*
    |--------------------------------------------------------------------------
    | Edit Category
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.edit-category-btn')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const name =
                    button.dataset.name || '';

                const description =
                    button.dataset.description || '';

                const isActive =
                    button.dataset.active === '1';

                const image =
                    button.dataset.image || '';

                const action =
                    button.dataset.action;


                document.getElementById(
                    'edit-category-form'
                ).action = action;


                document.getElementById(
                    'edit-category-name'
                ).value = name;


                document.getElementById(
                    'edit-category-description'
                ).value = description;


                document.getElementById(
                    'edit-category-active'
                ).checked = isActive;


                document.getElementById(
                    'edit-category-remove-image'
                ).checked = false;


                const currentImage =
                    document.getElementById(
                        'edit-category-current-image'
                    );


                if (image) {

                    currentImage.innerHTML =
                        '<img src="' +
                        image +
                        '" alt="Category image">';

                } else {

                    currentImage.innerHTML =
                        '<span class="no-current-image">' +
                        'No image uploaded.' +
                        '</span>';

                }


                const preview =
                    document.getElementById(
                        'edit-category-preview'
                    );

                preview.innerHTML = '';
                preview.classList.add('hidden');


                document.getElementById(
                    'edit-category-image'
                ).value = '';


                openModal('edit-category-modal');

            });

        });


    /*
    |--------------------------------------------------------------------------
    | Create Product
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.add-product-trigger')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const categoryId =
                    button.dataset.categoryId;

                const categoryName =
                    button.dataset.categoryName;


                document.getElementById(
                    'product-category-id'
                ).value = categoryId;


                document.getElementById(
                    'selected-category-name'
                ).textContent = categoryName;


                document.getElementById(
                    'selected-category-display'
                ).textContent = categoryName;


                openModal('product-modal');


                setTimeout(function () {

                    const input =
                        document.getElementById('product-name');

                    if (input) {
                        input.focus();
                    }

                }, 100);

            });

        });


    /*
    |--------------------------------------------------------------------------
    | Edit Product
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.edit-product-btn')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const name =
                    button.dataset.name || '';

                const categoryId =
                    button.dataset.categoryId;

                const description =
                    button.dataset.description || '';

                const price =
                    button.dataset.price || '';

                const isActive =
                    button.dataset.active === '1';

                const image =
                    button.dataset.image || '';

                const action =
                    button.dataset.action;


                document.getElementById(
                    'edit-product-form'
                ).action = action;


                document.getElementById(
                    'edit-product-name'
                ).value = name;


                document.getElementById(
                    'edit-product-category'
                ).value = categoryId;


                document.getElementById(
                    'edit-product-description'
                ).value = description;


                document.getElementById(
                    'edit-product-price'
                ).value = price;


                document.getElementById(
                    'edit-product-active'
                ).checked = isActive;


                document.getElementById(
                    'edit-product-remove-image'
                ).checked = false;


                const currentImage =
                    document.getElementById(
                        'edit-product-current-image'
                    );


                if (image) {

                    currentImage.innerHTML =
                        '<img src="' +
                        image +
                        '" alt="Product image">';

                } else {

                    currentImage.innerHTML =
                        '<span class="no-current-image">' +
                        'No image uploaded.' +
                        '</span>';

                }


                const preview =
                    document.getElementById(
                        'edit-product-preview'
                    );

                preview.innerHTML = '';
                preview.classList.add('hidden');


                document.getElementById(
                    'edit-product-image'
                ).value = '';


                openModal('edit-product-modal');

            });

        });


    /*
    |--------------------------------------------------------------------------
    | Image Preview
    |--------------------------------------------------------------------------
    */

    function setupImagePreview(inputId, previewId) {

        const input =
            document.getElementById(inputId);

        const preview =
            document.getElementById(previewId);


        if (!input || !preview) {
            return;
        }


        input.addEventListener('change', function () {

            preview.innerHTML = '';


            if (!input.files || !input.files[0]) {

                preview.classList.add('hidden');

                return;

            }


            const file =
                input.files[0];


            if (!file.type.startsWith('image/')) {

                preview.classList.add('hidden');

                return;

            }


            const reader =
                new FileReader();


            reader.onload = function (event) {

                const img =
                    document.createElement('img');

                img.src =
                    event.target.result;

                img.alt =
                    'Image preview';


                preview.appendChild(img);

                preview.classList.remove('hidden');

            };


            reader.readAsDataURL(file);

        });

    }


    setupImagePreview(
        'category-image',
        'category-preview'
    );


    setupImagePreview(
        'edit-category-image',
        'edit-category-preview'
    );


    setupImagePreview(
        'product-image',
        'product-preview'
    );


    setupImagePreview(
        'edit-product-image',
        'edit-product-preview'
    );


    /*
    |--------------------------------------------------------------------------
    | Close Buttons
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-close-modal]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const modalId =
                    button.dataset.closeModal;

                closeModal(modalId);

            });

        });


    /*
    |--------------------------------------------------------------------------
    | Click Outside Modal
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.modal')
        .forEach(function (modal) {

            modal.addEventListener('click', function (event) {

                if (event.target === modal) {
                    modal.classList.remove('show');
                }

            });

        });


    /*
    |--------------------------------------------------------------------------
    | Escape Key
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }


        document
            .querySelectorAll('.modal.show')
            .forEach(function (modal) {

                modal.classList.remove('show');

            });

    });

});

</script>

@endpush