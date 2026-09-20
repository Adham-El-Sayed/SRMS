@extends('layouts.menu')

@section('title', 'Restaurant Menu')

@section('content')

    <div
        class="menu-page"
        id="menu-app"
        data-table-id="{{ isset($table) ? $table->id : '' }}"
    >

        {{-- =========================
             Page Header
        ========================== --}}

        <div class="page-header">

            <div>
                <h1>Restaurant Menu</h1>

                <p>
                    Choose your favorite items and create your order.
                </p>
            </div>

            @if(isset($table))

                <div class="table-badge">
                    Table #{{ $table->number }}
                </div>

            @endif

        </div>


        {{-- =========================
             Table Information
        ========================== --}}

        @if(isset($table))

            <div class="table-info">

                <div class="table-info-main">

                    <div class="table-icon">
                        T
                    </div>

                    <div>

                        <h2>
                            Table {{ $table->number }}
                        </h2>

                        <p>
                            Ready to take your order
                        </p>

                    </div>

                </div>


                <div class="table-details">

                    <div class="table-detail">

                        <span>
                            Capacity
                        </span>

                        <strong>
                            {{ $table->capacity }} Guests
                        </strong>

                    </div>


                    <div class="table-detail">

                        <span>
                            Status
                        </span>

                        <strong
                            class="table-status {{ $table->status->value ?? $table->status }}"
                        >
                            {{ ucfirst($table->status->value ?? $table->status) }}
                        </strong>

                    </div>

                </div>

            </div>

        @endif


        {{-- =========================
             Menu
        ========================== --}}

        @forelse($menu as $category)

            <section class="category">

                {{-- Category Header --}}

                <div class="category-header">

                    <div class="category-heading">

                        {{-- Category Image --}}

                        @if($category->image)

                            <img
                                src="{{ asset('storage/' . $category->image) }}"
                                alt="{{ $category->name }}"
                                class="category-image"
                            >

                        @else

                            <div class="category-image category-image-placeholder">
                                🍽
                            </div>

                        @endif


                        <div class="category-heading-text">

                            <h2 class="category-title">
                                {{ $category->name }}
                            </h2>


                            @if($category->description)

                                <p class="category-description">
                                    {{ $category->description }}
                                </p>

                            @endif

                        </div>

                    </div>


                    <span class="products-count">

                        {{ $category->products->count() }}

                        {{ $category->products->count() === 1 ? 'Item' : 'Items' }}

                    </span>

                </div>


                {{-- Products --}}

                <div class="products">

                    @forelse($category->products as $product)

                        <div
                            class="product-card"
                            data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->name }}"
                            data-product-price="{{ $product->price }}"
                        >

                            {{-- Product Image --}}

                            <div class="product-image-wrapper">

                                @if($product->image)

                                    <img
                                        src="{{ asset('storage/' . $product->image) }}"
                                        alt="{{ $product->name }}"
                                        class="product-image"
                                        loading="lazy"
                                    >

                                @else

                                    <div class="product-image product-image-placeholder">
                                        🍽
                                    </div>

                                @endif


                                <span class="available-badge">
                                    Available
                                </span>

                            </div>


                            {{-- Product Content --}}

                            <div class="product-content">

                                <h3>
                                    {{ $product->name }}
                                </h3>


                                <p class="product-description">

                                    {{ $product->description ?? 'No description available.' }}

                                </p>

                            </div>


                            {{-- Product Footer --}}

                            <div class="product-footer">

                                <div class="price">

                                    {{ number_format($product->price, 2) }}

                                    <span>
                                        EGP
                                    </span>

                                </div>


                                @if(isset($table))

                                    <button
                                        type="button"
                                        class="add-button"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->price }}"
                                    >
                                        + Add
                                    </button>

                                @endif

                            </div>

                        </div>

                    @empty

                        <div class="no-products">

                            <p>
                                No products available in this category.
                            </p>

                        </div>

                    @endforelse

                </div>

            </section>

        @empty

            {{-- Empty Menu --}}

            <div class="empty-menu">

                <div class="empty-icon">
                    🍽
                </div>

                <h2>
                    No Menu Available
                </h2>

                <p>
                    There are currently no menu items available.
                </p>

            </div>

        @endforelse


        {{-- =========================
             Order
        ========================== --}}

        @if(isset($table))

            <div class="order-box" id="order-box">

                <div class="order-header">

                    <div>

                        <h2>
                            Your Order
                        </h2>

                        <p>
                            Review your items before submitting.
                        </p>

                    </div>


                    <span
                        class="order-count"
                        id="order-count"
                    >
                        0 Items
                    </span>

                </div>


                {{-- Order Items --}}

                <div id="order-items">

                    <div class="empty-order">

                        <div class="empty-order-icon">
                            🛒
                        </div>

                        <p>
                            Your order is empty.
                        </p>

                        <span>
                            Add items from the menu above.
                        </span>

                    </div>

                </div>


                {{-- Total --}}

                <div class="order-total">

                    <span>
                        Total
                    </span>

                    <span id="order-total">
                        0.00 EGP
                    </span>

                </div>


                {{-- Client Info --}}

                <div class="client-info">

                    <input
                        type="text"
                        id="client-name"
                        placeholder="Name (optional)"
                    >

                    <input
                        type="tel"
                        id="client-phone"
                        placeholder="Phone Number (optional)"
                    >

                </div>


                {{-- Payment Method --}}

                <div class="payment-method">

                    <label>
                        <input type="radio" name="payment_method" value="cash" checked>
                        cash
                    </label>

                    <label>
                        <input type="radio" name="payment_method" value="card">
                        Visa
                    </label>

                </div>


                {{-- Submit --}}

                <button
                    type="button"
                    id="submit-order"
                    class="submit-button"
                    disabled
                >
                    Submit Order
                </button>


                {{-- Message --}}

                <div id="message"></div>

            </div>


            {{-- =========================
                 Submitted Order (Edit Window)
            ========================== --}}

            <div class="order-box" id="submitted-order-panel" style="display:none;">

                <div class="order-header">
                    <div>
                        <h2>Order #<span id="submitted-order-id"></span></h2>
                        <p id="edit-window-status">
                            You can still edit this order for
                            <strong><span id="edit-timer">2:30</span></strong>
                        </p>
                    </div>
                </div>

                <div id="submitted-order-items"></div>

                <div class="order-total">
                    <span>Total</span>
                    <span id="submitted-order-total">0.00 EGP</span>
                </div>

                <button type="button" id="save-changes-button" class="submit-button">
                    Save Changes
                </button>

                <button
                    type="button"
                    id="request-help-button"
                    class="submit-button"
                    style="display:none; background:#dc2626;"
                >
                    Request Waiter
                </button>

                <div id="submitted-message"></div>

            </div>

        @endif

    </div>


    {{-- =========================
         Fake Visa Payment Modal
    ========================== --}}

    @if(isset($table))

        <div id="payment-modal-overlay" class="payment-modal-overlay" style="display:none;">

            <div class="payment-modal">

                <h2>Pay with Visa</h2>

                <p class="payment-modal-amount">
                    Amount: <strong id="payment-modal-amount">0.00 EGP</strong>
                </p>

                <button type="button" id="scan-card-button" class="scan-card-button">
                    📷 Scan Card with Camera
                </button>

                <div id="camera-scan-container" class="camera-scan-container" style="display:none;">
                    <video id="camera-video" autoplay playsinline></video>
                    <div class="camera-scan-frame"></div>
                    <p id="camera-scan-status">Point your camera at the card...</p>
                    <button type="button" id="cancel-scan-button" class="cancel-payment-button">
                        Cancel Scan
                    </button>
                </div>

                <div class="payment-field">
                    <label>Card Number</label>
                    <input type="text" id="card-number" placeholder="4242 4242 4242 4242" maxlength="19">
                </div>

                <div class="payment-field-row">

                    <div class="payment-field">
                        <label>Expiry</label>
                        <input type="text" id="card-expiry" placeholder="MM/YY" maxlength="5">
                    </div>

                    <div class="payment-field">
                        <label>CVV</label>
                        <input type="text" id="card-cvv" placeholder="123" maxlength="3">
                    </div>

                </div>

                <div class="payment-field">
                    <label>Cardholder Name</label>
                    <input type="text" id="card-name" placeholder="Name on card">
                </div>

                <div id="payment-modal-error" class="payment-modal-error"></div>

                <button type="button" id="pay-now-button" class="submit-button">
                    Pay Now
                </button>

                <button type="button" id="cancel-payment-button" class="cancel-payment-button">
                    Cancel
                </button>

            </div>

        </div>

    @endif

@endsection


{{-- =========================================================
     STYLES
========================================================= --}}

@push('styles')

<style>

    /* =========================
       Base
    ========================== */

    .menu-page {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }


    /* =========================
       Page Header
    ========================== */

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 30px;
    }


    .page-header h1 {
        margin: 0;
        font-size: 32px;
        line-height: 1.2;
        color: #1f2937;
    }


    .page-header p {
        margin: 8px 0 0;
        color: #6b7280;
        line-height: 1.6;
    }


    .table-badge {
        background: #eff6ff;
        color: #2563eb;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: bold;
        white-space: nowrap;
    }


    /* =========================
       Table Information
    ========================== */

    .table-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 25px;

        background: white;

        padding: 22px;

        border-radius: 16px;

        margin-bottom: 35px;

        box-shadow:
            0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .table-info-main {
        display: flex;
        align-items: center;
        gap: 15px;
    }


    .table-icon {
        width: 52px;
        height: 52px;

        display: flex;
        justify-content: center;
        align-items: center;

        border-radius: 14px;

        background: #eff6ff;

        color: #2563eb;

        font-size: 20px;
        font-weight: bold;

        flex-shrink: 0;
    }


    .table-info h2 {
        margin: 0;

        font-size: 20px;

        color: #1f2937;
    }


    .table-info p {
        margin: 5px 0 0;

        color: #6b7280;

        line-height: 1.5;
    }


    .table-details {
        display: flex;
        gap: 35px;
    }


    .table-detail {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }


    .table-detail span {
        color: #6b7280;

        font-size: 13px;
    }


    .table-detail strong {
        color: #1f2937;
    }


    .table-status.available {
        color: #15803d;
    }


    .table-status.occupied {
        color: #dc2626;
    }


    .table-status.reserved {
        color: #d97706;
    }


    /* =========================
       Categories
    ========================== */

    .category {
        margin-bottom: 40px;
    }


    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;

        gap: 20px;

        margin-bottom: 20px;
    }


    .category-heading {
        display: flex;
        align-items: center;

        gap: 15px;

        min-width: 0;
    }


    .category-image {
        width: 64px;
        height: 64px;

        object-fit: cover;

        border-radius: 14px;

        flex-shrink: 0;
    }


    .category-image-placeholder {
        display: flex;
        justify-content: center;
        align-items: center;

        background: #eff6ff;

        color: #2563eb;

        font-size: 26px;
    }


    .category-heading-text {
        min-width: 0;
    }


    .category-title {
        margin: 0;

        font-size: 25px;

        line-height: 1.3;

        color: #1f2937;
    }


    .category-description {
        margin: 7px 0 0;

        color: #6b7280;

        line-height: 1.5;
    }


    .products-count {
        background: #f3f4f6;

        color: #4b5563;

        padding: 7px 12px;

        border-radius: 20px;

        font-size: 13px;

        font-weight: bold;

        white-space: nowrap;
    }


    /* =========================
       Products Grid
    ========================== */

    .products {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        align-items: stretch;
    }


    /* =========================
       Product Card
    ========================== */

    .product-card {
        background: white;

        padding: 12px;

        border-radius: 16px;

        box-shadow:
            0 4px 15px rgba(0, 0, 0, 0.08);

        display: flex;
        flex-direction: column;

        min-height: 0;

        overflow: hidden;

        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }


    .product-card:hover {
        transform: translateY(-4px);

        box-shadow:
            0 10px 25px rgba(0, 0, 0, 0.10);
    }


    /* =========================
       Product Image
    ========================== */

    .product-image-wrapper {
        position: relative;

        width: 100%;
        height: 190px;

        overflow: hidden;

        border-radius: 13px;

        background: #f8fafc;
    }


    .product-image {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;

        transition:
            transform 0.3s ease;
    }


    .product-card:hover .product-image {
        transform: scale(1.04);
    }


    .product-image-placeholder {
        display: flex;

        justify-content: center;
        align-items: center;

        font-size: 45px;

        color: #64748b;

        background: #f8fafc;
    }


    .product-image-wrapper .available-badge {
        position: absolute;

        top: 12px;
        right: 12px;

        z-index: 2;
    }


    .available-badge {
        background: #dcfce7;

        color: #15803d;

        padding: 5px 10px;

        border-radius: 20px;

        font-size: 12px;

        font-weight: bold;

        white-space: nowrap;
    }


    /* =========================
       Product Content
    ========================== */

    .product-content {
        flex: 1;

        padding:
            16px 8px 0;
    }


    .product-content h3 {
        margin: 0;

        font-size: 19px;

        line-height: 1.4;

        color: #1f2937;
    }


    .product-description {
        margin: 8px 0 0;

        color: #6b7280;

        line-height: 1.6;

        font-size: 14px;

        display: -webkit-box;

        -webkit-line-clamp: 3;
        line-clamp: 3;
        -webkit-box-orient: vertical;

        overflow: hidden;
    }


    /* =========================
       Product Footer
    ========================== */

    .product-footer {
        display: flex;

        justify-content: space-between;
        align-items: center;

        gap: 15px;

        padding:
            0 8px 8px;

        margin-top: 18px;
    }


    .price {
        color: #1f2937;

        font-size: 20px;

        font-weight: bold;

        white-space: nowrap;
    }


    .price span {
        color: #6b7280;

        font-size: 13px;

        font-weight: normal;
    }


    .add-button {
        border: none;

        background: #2563eb;

        color: white;

        padding: 10px 16px;

        border-radius: 9px;

        cursor: pointer;

        font-weight: bold;

        transition:
            background 0.2s ease,
            transform 0.2s ease;
    }


    .add-button:hover {
        background: #1d4ed8;

        transform: translateY(-1px);
    }


    .add-button:active {
        transform: translateY(0);
    }


    /* =========================
       No Products
    ========================== */

    .no-products {
        grid-column: 1 / -1;

        padding: 25px;

        text-align: center;

        background: white;

        border-radius: 14px;

        color: #6b7280;
    }


    /* =========================
       Order Box
    ========================== */

    .order-box {
        background: white;

        margin-top: 40px;

        padding: 25px;

        border-radius: 16px;

        box-shadow:
            0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .order-header {
        display: flex;

        justify-content: space-between;

        align-items: flex-start;

        gap: 20px;

        margin-bottom: 20px;
    }


    .order-header h2 {
        margin: 0;

        font-size: 24px;

        color: #1f2937;
    }


    .order-header p {
        margin: 7px 0 0;

        color: #6b7280;
    }


    .order-count {
        background: #eff6ff;

        color: #2563eb;

        padding: 7px 12px;

        border-radius: 20px;

        font-size: 13px;

        font-weight: bold;

        white-space: nowrap;
    }


    /* =========================
       Empty Order
    ========================== */

    .empty-order {
        text-align: center;

        padding: 35px 20px;

        color: #6b7280;
    }


    .empty-order-icon {
        font-size: 35px;

        margin-bottom: 10px;
    }


    .empty-order p {
        margin: 0;

        font-weight: bold;

        color: #4b5563;
    }


    .empty-order span {
        display: block;

        margin-top: 5px;

        font-size: 14px;
    }


    /* =========================
       Order Item
    ========================== */

    .order-item {
        display: flex;
        flex-wrap: wrap;

        justify-content: space-between;

        align-items: center;

        gap: 20px;

        padding: 16px 0;

        border-bottom:
            1px solid #e5e7eb;
    }


    .order-item-info {
        flex: 1;

        min-width: 0;
    }


    .order-item-name {
        font-weight: bold;

        color: #1f2937;

        word-break: break-word;
    }


    .order-item-price {
        margin-top: 5px;

        color: #6b7280;

        font-size: 13px;
    }


    /* =========================
       Quantity Controls
    ========================== */

    .quantity-controls {
        display: flex;

        align-items: center;

        gap: 10px;
    }


    .quantity-button {
        width: 32px;
        height: 32px;

        border: none;

        border-radius: 8px;

        background: #f1f5f9;

        color: #1f2937;

        cursor: pointer;

        font-size: 18px;

        font-weight: bold;

        transition:
            background 0.2s ease;
    }


    .quantity-button:hover {
        background: #e2e8f0;
    }


    .quantity {
        min-width: 25px;

        text-align: center;

        font-weight: bold;
    }


    /* =========================
       Item Notes
    ========================== */

    .item-notes {
        flex-basis: 100%;

        margin-top: 4px;

        padding: 6px 10px;

        border: 1px solid #e5e7eb;

        border-radius: 6px;

        font-size: 13px;
    }


    /* =========================
       Subtotal
    ========================== */

    .item-subtotal {
        min-width: 110px;

        text-align: right;

        color: #1f2937;
    }


    /* =========================
       Remove
    ========================== */

    .remove-button {
        border: none;

        background: #fee2e2;

        color: #dc2626;

        padding: 8px 12px;

        border-radius: 8px;

        cursor: pointer;

        font-weight: bold;

        transition:
            background 0.2s ease;
    }


    .remove-button:hover {
        background: #fecaca;
    }


    /* =========================
       Order Total
    ========================== */

    .order-total {
        display: flex;

        justify-content: space-between;

        align-items: center;

        margin-top: 20px;

        padding-top: 18px;

        border-top:
            2px solid #e5e7eb;

        font-size: 20px;

        font-weight: bold;

        color: #1f2937;
    }


    /* =========================
       Client Info
    ========================== */

    .client-info {
        display: flex;

        gap: 12px;

        margin-top: 20px;
    }


    .client-info input {
        flex: 1;

        padding: 10px 12px;

        border: 1px solid #e5e7eb;

        border-radius: 8px;
    }


    /* =========================
       Payment Method
    ========================== */

    .payment-method {
        display: flex;

        gap: 20px;

        margin-top: 14px;

        align-items: center;
    }


    .payment-method label {
        display: flex;

        align-items: center;

        gap: 6px;

        font-weight: 500;

        color: #1f2937;

        cursor: pointer;
    }


    /* =========================
       Submit Button
    ========================== */

    .submit-button {
        width: 100%;

        margin-top: 20px;

        padding: 14px;

        border: none;

        border-radius: 10px;

        background: #16a34a;

        color: white;

        font-size: 16px;

        font-weight: bold;

        cursor: pointer;

        transition:
            background 0.2s ease,
            transform 0.2s ease;
    }


    .submit-button:hover:not(:disabled) {
        background: #15803d;

        transform: translateY(-1px);
    }


    .submit-button:disabled {
        opacity: 0.6;

        cursor: not-allowed;
    }


    /* =========================
       Messages
    ========================== */

    #message,
    #submitted-message {
        display: none;

        margin-top: 18px;

        padding: 13px 16px;

        border-radius: 10px;

        font-weight: 500;
    }


    #message.success,
    #submitted-message.success {
        display: block;

        background: #dcfce7;

        color: #166534;
    }


    #message.error,
    #submitted-message.error {
        display: block;

        background: #fee2e2;

        color: #991b1b;
    }


    /* =========================
       Empty Menu
    ========================== */

    .empty-menu {
        background: white;

        padding: 60px 30px;

        border-radius: 16px;

        text-align: center;

        color: #6b7280;

        box-shadow:
            0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .empty-icon {
        font-size: 45px;

        margin-bottom: 15px;
    }


    .empty-menu h2 {
        margin: 0;

        color: #1f2937;
    }


    .empty-menu p {
        margin: 10px 0 0;
    }


    /* =========================
       Fake Visa Payment Modal
    ========================== */

    .payment-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
    }

    .payment-modal {
        background: white;
        border-radius: 16px;
        padding: 28px;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    }

    .payment-modal h2 {
        margin: 0 0 6px;
        font-size: 20px;
        color: #1f2937;
    }

    .payment-modal-amount {
        margin: 0 0 20px;
        color: #6b7280;
        font-size: 14px;
    }

    .payment-modal-amount strong {
        color: #1f2937;
        font-size: 17px;
    }

    .payment-field {
        margin-bottom: 14px;
    }

    .payment-field label {
        display: block;
        font-size: 13px;
        color: #4b5563;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .payment-field input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .payment-field-row {
        display: flex;
        gap: 12px;
    }

    .payment-field-row .payment-field {
        flex: 1;
    }

    .payment-modal-error {
        display: none;
        background: #fee2e2;
        color: #991b1b;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 14px;
    }

    .payment-modal-error.visible {
        display: block;
    }

    .cancel-payment-button {
        width: 100%;
        margin-top: 10px;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: white;
        color: #4b5563;
        font-weight: bold;
        font-size: 15px;
        cursor: pointer;
    }

    .cancel-payment-button:hover {
        background: #f9fafb;
    }


    .scan-card-button {
        width: 100%;
        margin-bottom: 16px;
        padding: 11px;
        border: 1px dashed #2563eb;
        border-radius: 8px;
        background: #eff6ff;
        color: #2563eb;
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
    }

    .scan-card-button:hover {
        background: #dbeafe;
    }

    .camera-scan-container {
        position: relative;
        margin-bottom: 16px;
        border-radius: 12px;
        overflow: hidden;
        background: #000;
        text-align: center;
    }

    .camera-scan-container video {
        width: 100%;
        display: block;
        max-height: 220px;
        object-fit: cover;
    }

    .camera-scan-frame {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 85%;
        height: 55%;
        border: 2px solid #22c55e;
        border-radius: 10px;
        pointer-events: none;
    }

    #camera-scan-status {
        color: white;
        font-size: 13px;
        margin: 8px 0;
    }

    .camera-scan-container .cancel-payment-button {
        margin: 0 0 10px;
        width: 90%;
    }


    /* =========================
       Responsive
    ========================== */

    @media (max-width: 768px) {

        .page-header,
        .table-info,
        .category-header,
        .order-header {
            flex-direction: column;

            align-items: flex-start;
        }


        .table-details {
            width: 100%;

            justify-content: space-between;

            gap: 15px;
        }


        .products {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }


        .order-item {
            flex-wrap: wrap;
        }


        .order-item-info {
            min-width: 100%;
        }


        .item-subtotal {
            min-width: auto;

            text-align: left;
        }

    }


    @media (max-width: 600px) {

        .products {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 500px) {

        .page-header h1 {
            font-size: 27px;
        }


        .category-heading {
            align-items: flex-start;
        }


        .category-image {
            width: 54px;
            height: 54px;
        }


        .category-title {
            font-size: 21px;
        }


        .category-description {
            font-size: 14px;
        }


        .product-image-wrapper {
            height: 200px;
        }


        .product-footer {
            align-items: flex-start;

            flex-direction: column;
        }


        .add-button {
            width: 100%;
        }


        .quantity-controls {
            order: 3;
        }


        .remove-button {
            margin-left: auto;
        }


        .table-details {
            flex-direction: column;
        }


        .order-box {
            padding: 20px 16px;
        }

        .client-info {
            flex-direction: column;
        }

    }

</style>

@endpush


{{-- =========================================================
     JAVASCRIPT
========================================================= --}}

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const app =
        document.getElementById('menu-app');


    if (!app) {
        return;
    }


    const tableId =
        app.dataset.tableId;


    const addButtons =
        document.querySelectorAll('.add-button');


    const orderItemsContainer =
        document.getElementById('order-items');


    const orderTotalElement =
        document.getElementById('order-total');


    const orderCountElement =
        document.getElementById('order-count');


    const submitButton =
        document.getElementById('submit-order');


    const messageElement =
        document.getElementById('message');


    const clientNameInput =
        document.getElementById('client-name');


    const clientPhoneInput =
        document.getElementById('client-phone');


    let orderItems = [];


    /* =========================
       Submitted Order Panel Elements
    ========================== */

    const submittedPanel =
        document.getElementById('submitted-order-panel');


    const submittedOrderIdElement =
        document.getElementById('submitted-order-id');


    const submittedItemsContainer =
        document.getElementById('submitted-order-items');


    const submittedTotalElement =
        document.getElementById('submitted-order-total');


    const editTimerElement =
        document.getElementById('edit-timer');


    const editWindowStatus =
        document.getElementById('edit-window-status');


    const saveChangesButton =
        document.getElementById('save-changes-button');


    const requestHelpButton =
        document.getElementById('request-help-button');


    const submittedMessageElement =
        document.getElementById('submitted-message');


    /* =========================
       Fake Payment Modal Elements
    ========================== */

    const paymentModalOverlay =
        document.getElementById('payment-modal-overlay');


    const paymentModalAmount =
        document.getElementById('payment-modal-amount');


    const paymentModalError =
        document.getElementById('payment-modal-error');


    const cardNumberInput =
        document.getElementById('card-number');


    const cardExpiryInput =
        document.getElementById('card-expiry');


    const cardCvvInput =
        document.getElementById('card-cvv');


    const cardNameInput =
        document.getElementById('card-name');


    const payNowButton =
        document.getElementById('pay-now-button');


    const cancelPaymentButton =
        document.getElementById('cancel-payment-button');


    const scanCardButton =
        document.getElementById('scan-card-button');


    const cameraScanContainer =
        document.getElementById('camera-scan-container');


    const cameraVideo =
        document.getElementById('camera-video');


    const cameraScanStatus =
        document.getElementById('camera-scan-status');


    const cancelScanButton =
        document.getElementById('cancel-scan-button');


    let cameraStream = null;


    const EDIT_WINDOW_SECONDS = 150;

    let currentOrderId = null;
    let submittedItems = [];
    let countdownInterval = null;


    /* =========================
       Add Product
    ========================== */

    addButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const productId =
                Number(this.dataset.productId);


            const productName =
                this.dataset.productName;


            const productPrice =
                Number(this.dataset.productPrice);


            const existingItem =
                orderItems.find(
                    function (item) {

                        return item.product_id === productId;

                    }
                );


            if (existingItem) {

                existingItem.quantity += 1;

            } else {

                orderItems.push({

                    product_id: productId,

                    name: productName,

                    price: productPrice,

                    quantity: 1,

                    notes: ''

                });

            }


            renderOrder();

        });

    });


    /* =========================
       Render Order
    ========================== */

    function renderOrder() {

        const totalQuantity =
            orderItems.reduce(
                function (total, item) {

                    return total + item.quantity;

                },
                0
            );


        if (orderCountElement) {

            orderCountElement.textContent =
                totalQuantity +
                (
                    totalQuantity === 1
                        ? ' Item'
                        : ' Items'
                );

        }


        if (orderItems.length === 0) {

            if (orderItemsContainer) {

                orderItemsContainer.innerHTML = `

                    <div class="empty-order">

                        <div class="empty-order-icon">
                            🛒
                        </div>

                        <p>
                            Your order is empty.
                        </p>

                        <span>
                            Add items from the menu above.
                        </span>

                    </div>

                `;

            }


            if (orderTotalElement) {

                orderTotalElement.textContent =
                    '0.00 EGP';

            }


            if (submitButton) {

                submitButton.disabled = true;

                submitButton.textContent =
                    'Submit Order';

            }


            return;

        }


        let html = '';

        let total = 0;


        orderItems.forEach(
            function (item, index) {

                const subtotal =
                    item.price * item.quantity;


                total += subtotal;


                html += `

                    <div class="order-item">

                        <div class="order-item-info">

                            <div class="order-item-name">
                                ${escapeHtml(item.name)}
                            </div>

                            <div class="order-item-price">
                                ${item.price.toFixed(2)} EGP each
                            </div>

                        </div>


                        <div class="quantity-controls">

                            <button
                                type="button"
                                class="quantity-button"
                                data-action="decrease"
                                data-index="${index}"
                            >
                                −
                            </button>


                            <span class="quantity">
                                ${item.quantity}
                            </span>


                            <button
                                type="button"
                                class="quantity-button"
                                data-action="increase"
                                data-index="${index}"
                            >
                                +
                            </button>

                        </div>


                        <strong class="item-subtotal">
                            ${subtotal.toFixed(2)} EGP
                        </strong>


                        <button
                            type="button"
                            class="remove-button"
                            data-action="remove"
                            data-index="${index}"
                        >
                            Remove
                        </button>


                        <input
                            type="text"
                            class="item-notes"
                            placeholder="Notes (e.g., without tomatoes)"
                            data-index="${index}"
                            value="${item.notes ? escapeHtml(item.notes) : ''}"
                        >

                    </div>

                `;

            }
        );


        if (orderItemsContainer) {

            orderItemsContainer.innerHTML =
                html;

        }


        if (orderTotalElement) {

            orderTotalElement.textContent =
                total.toFixed(2) +
                ' EGP';

        }


        if (submitButton) {

            submitButton.disabled = false;

        }


        attachOrderButtons();

    }


    /* =========================
       Quantity, Remove And Notes
    ========================== */

    function attachOrderButtons() {

        if (!orderItemsContainer) {
            return;
        }


        const buttons =
            orderItemsContainer.querySelectorAll(
                '[data-action]'
            );


        buttons.forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    const index =
                        Number(this.dataset.index);


                    const action =
                        this.dataset.action;


                    if (!orderItems[index]) {
                        return;
                    }


                    if (action === 'increase') {

                        orderItems[index].quantity += 1;

                    }


                    if (action === 'decrease') {

                        orderItems[index].quantity -= 1;


                        if (
                            orderItems[index].quantity <= 0
                        ) {

                            orderItems.splice(
                                index,
                                1
                            );

                        }

                    }


                    if (action === 'remove') {

                        orderItems.splice(
                            index,
                            1
                        );

                    }


                    renderOrder();

                }
            );

        });


        const notesInputs =
            orderItemsContainer.querySelectorAll(
                '.item-notes'
            );


        notesInputs.forEach(function (input) {

            input.addEventListener(
                'input',
                function () {

                    const index =
                        Number(this.dataset.index);


                    if (!orderItems[index]) {
                        return;
                    }


                    orderItems[index].notes =
                        this.value;

                }
            );

        });

    }


    /* =========================
       Submit Order
    ========================== */

    if (submitButton) {

        submitButton.addEventListener(
            'click',
            function () {

                if (orderItems.length === 0) {

                    return;

                }


                if (!tableId) {

                    showMessage(
                        'Table information is missing.',
                        'error'
                    );

                    return;

                }


                const selectedPaymentMethod =
                    document.querySelector(
                        'input[name="payment_method"]:checked'
                    );


                const paymentMethod =
                    selectedPaymentMethod
                        ? selectedPaymentMethod.value
                        : 'cash';


                if (paymentMethod === 'card') {

                    openPaymentModal();

                    return;

                }


                submitOrder('cash');

            }
        );

    }


    /* =========================
       Actual Order Submission
    ========================== */

    async function submitOrder(paymentMethod) {

        submitButton.disabled = true;

        submitButton.textContent =
            'Submitting...';


        hideMessage();


        const items =
            orderItems.map(
                function (item) {

                    return {

                        product_id:
                            item.product_id,

                        quantity:
                            item.quantity,

                        notes:
                            item.notes || null

                    };

                }
            );


        try {

            const response =
                await fetch(
                    '/api/orders',
                    {

                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json'

                        },

                        body:
                            JSON.stringify({

                                restaurant_table_id:
                                    Number(tableId),

                                items:
                                    items,

                                client_name:
                                    clientNameInput
                                        ? (clientNameInput.value || null)
                                        : null,

                                client_phone:
                                    clientPhoneInput
                                        ? (clientPhoneInput.value || null)
                                        : null,

                                payment_method:
                                    paymentMethod

                            })

                    }
                );


            const data =
                await response.json();


            if (!response.ok) {

                showMessage(
                    data.message ||
                    'Failed to create order.',
                    'error'
                );


                submitButton.disabled =
                    false;


                submitButton.textContent =
                    'Submit Order';


                return false;

            }


            orderItems = [];


            if (clientNameInput) {
                clientNameInput.value = '';
            }


            if (clientPhoneInput) {
                clientPhoneInput.value = '';
            }


            renderOrder();


            showMessage(
                'Order #' +
                data.order.id +
                ' created successfully!',
                'success'
            );


            submitButton.disabled =
                false;


            submitButton.textContent =
                'Submit Order';


            startEditWindow(data.order);


            return true;


        } catch (error) {

            console.error(error);


            showMessage(
                'Something went wrong while submitting the order.',
                'error'
            );


            submitButton.disabled =
                false;


            submitButton.textContent =
                'Submit Order';


            return false;

        }

    }


    /* =========================
       Fake Visa Payment Modal
    ========================== */

    function openPaymentModal() {

        if (!paymentModalOverlay) {
            return;
        }


        const total =
            orderItems.reduce(
                function (sum, item) {

                    return sum + (item.price * item.quantity);

                },
                0
            );


        paymentModalAmount.textContent =
            total.toFixed(2) + ' EGP';


        cardNumberInput.value = '';

        cardExpiryInput.value = '';

        cardCvvInput.value = '';

        cardNameInput.value = '';


        hidePaymentModalError();


        payNowButton.disabled = false;

        payNowButton.textContent = 'Pay Now';


        paymentModalOverlay.style.display = 'flex';

    }


    function closePaymentModal() {

        if (!paymentModalOverlay) {
            return;
        }


        stopCameraScan();


        paymentModalOverlay.style.display = 'none';

    }


    function showPaymentModalError(text) {

        if (!paymentModalError) {
            return;
        }


        paymentModalError.textContent = text;

        paymentModalError.classList.add('visible');

    }


    function hidePaymentModalError() {

        if (!paymentModalError) {
            return;
        }


        paymentModalError.textContent = '';

        paymentModalError.classList.remove('visible');

    }


    if (cancelPaymentButton) {

        cancelPaymentButton.addEventListener(
            'click',
            function () {

                closePaymentModal();

            }
        );

    }


    if (payNowButton) {

        payNowButton.addEventListener(
            'click',
            function () {

                hidePaymentModalError();


                const cardNumber =
                    cardNumberInput.value.replace(/\s/g, '');


                const expiry =
                    cardExpiryInput.value.trim();


                const cvv =
                    cardCvvInput.value.trim();


                const name =
                    cardNameInput.value.trim();


                if (cardNumber.length < 12 || !/^\d+$/.test(cardNumber)) {

                    showPaymentModalError(
                        'Please enter a valid card number.'
                    );

                    return;

                }


                if (! /^\d{2}\/\d{2}$/.test(expiry)) {

                    showPaymentModalError(
                        'Please enter expiry as MM/YY.'
                    );

                    return;

                }


                if (! /^\d{3}$/.test(cvv)) {

                    showPaymentModalError(
                        'Please enter a valid 3-digit CVV.'
                    );

                    return;

                }


                if (! name) {

                    showPaymentModalError(
                        'Please enter the cardholder name.'
                    );

                    return;

                }


                payNowButton.disabled = true;

                payNowButton.textContent = 'Processing Payment...';


                // Simulated payment processing delay.
                // Replace this block with a real gateway call
                // (e.g. Paymob) once a provider account is set up.
                setTimeout(
                    async function () {

                        const success =
                            await submitOrder('card');


                        if (success) {

                            closePaymentModal();

                        } else {

                            payNowButton.disabled = false;

                            payNowButton.textContent = 'Pay Now';

                            showPaymentModalError(
                                'Payment succeeded but the order failed to save. Please contact staff.'
                            );

                        }

                    },
                    1500
                );

            }
        );

    }


    /* =========================
       Submitted Order: Start Edit Window
    ========================== */

    function startEditWindow(order) {

        if (!submittedPanel) {
            return;
        }


        currentOrderId = order.id;


        submittedItems =
            order.items.map(
                function (item) {

                    return {

                        product_id: item.product_id,

                        name: item.product
                            ? item.product.name
                            : 'Unknown Product',

                        price: Number(item.unit_price),

                        quantity: item.quantity,

                        notes: item.notes || ''

                    };

                }
            );


        submittedOrderIdElement.textContent =
            order.id;


        submittedPanel.style.display = 'block';


        saveChangesButton.style.display = 'block';

        saveChangesButton.disabled = false;

        saveChangesButton.textContent = 'Save Changes';


        requestHelpButton.style.display = 'none';


        hideSubmittedMessage();


        renderSubmittedOrder();


        const orderCreatedAt =
            new Date(order.created_at);


        const deadline =
            new Date(
                orderCreatedAt.getTime() +
                (EDIT_WINDOW_SECONDS * 1000)
            );


        if (countdownInterval) {

            clearInterval(countdownInterval);

        }


        tickCountdown(deadline);


        countdownInterval =
            setInterval(
                function () {

                    tickCountdown(deadline);

                },
                1000
            );

    }


    function tickCountdown(deadline) {

        const secondsLeft =
            Math.max(
                0,
                Math.floor(
                    (deadline.getTime() - Date.now()) / 1000
                )
            );


        const minutes =
            Math.floor(secondsLeft / 60);


        const seconds =
            secondsLeft % 60;


        if (editTimerElement) {

            editTimerElement.textContent =
                minutes + ':' +
                (seconds < 10 ? '0' : '') + seconds;

        }


        if (secondsLeft <= 0) {

            clearInterval(countdownInterval);


            editWindowStatus.textContent =
                'The edit window has expired.';


            saveChangesButton.style.display = 'none';


            requestHelpButton.style.display = 'block';

        }

    }


    /* =========================
       Render Submitted Order Items
    ========================== */

    function renderSubmittedOrder() {

        let html = '';

        let total = 0;


        submittedItems.forEach(
            function (item, index) {

                const subtotal =
                    item.price * item.quantity;


                total += subtotal;


                html += `

                    <div class="order-item">

                        <div class="order-item-info">

                            <div class="order-item-name">
                                ${escapeHtml(item.name)}
                            </div>

                            <div class="order-item-price">
                                ${item.price.toFixed(2)} EGP each
                            </div>

                        </div>


                        <div class="quantity-controls">

                            <button
                                type="button"
                                class="quantity-button"
                                data-sub-action="decrease"
                                data-sub-index="${index}"
                            >
                                −
                            </button>


                            <span class="quantity">
                                ${item.quantity}
                            </span>


                            <button
                                type="button"
                                class="quantity-button"
                                data-sub-action="increase"
                                data-sub-index="${index}"
                            >
                                +
                            </button>

                        </div>


                        <strong class="item-subtotal">
                            ${subtotal.toFixed(2)} EGP
                        </strong>


                        <button
                            type="button"
                            class="remove-button"
                            data-sub-action="remove"
                            data-sub-index="${index}"
                        >
                            Remove
                        </button>


                        <input
                            type="text"
                            class="item-notes"
                            placeholder="Notes (e.g., without tomatoes)"
                            data-sub-index="${index}"
                            value="${item.notes ? escapeHtml(item.notes) : ''}"
                        >

                    </div>

                `;

            }
        );


        if (submittedItemsContainer) {

            submittedItemsContainer.innerHTML =
                html;

        }


        if (submittedTotalElement) {

            submittedTotalElement.textContent =
                total.toFixed(2) +
                ' EGP';

        }


        attachSubmittedButtons();

    }


    function attachSubmittedButtons() {

        if (!submittedItemsContainer) {
            return;
        }


        const buttons =
            submittedItemsContainer.querySelectorAll(
                '[data-sub-action]'
            );


        buttons.forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    const index =
                        Number(this.dataset.subIndex);


                    const action =
                        this.dataset.subAction;


                    if (!submittedItems[index]) {
                        return;
                    }


                    if (action === 'increase') {

                        submittedItems[index].quantity += 1;

                    }


                    if (action === 'decrease') {

                        submittedItems[index].quantity -= 1;


                        if (
                            submittedItems[index].quantity <= 0
                        ) {

                            submittedItems.splice(
                                index,
                                1
                            );

                        }

                    }


                    if (action === 'remove') {

                        submittedItems.splice(
                            index,
                            1
                        );

                    }


                    renderSubmittedOrder();

                }
            );

        });


        const notesInputs =
            submittedItemsContainer.querySelectorAll(
                '.item-notes'
            );


        notesInputs.forEach(function (input) {

            input.addEventListener(
                'input',
                function () {

                    const index =
                        Number(this.dataset.subIndex);


                    if (!submittedItems[index]) {
                        return;
                    }


                    submittedItems[index].notes =
                        this.value;

                }
            );

        });

    }


    /* =========================
       Save Changes
    ========================== */

    if (saveChangesButton) {

        saveChangesButton.addEventListener(
            'click',
            async function () {

                if (!currentOrderId) {
                    return;
                }


                if (submittedItems.length === 0) {

                    showSubmittedMessage(
                        'Order must have at least one item.',
                        'error'
                    );

                    return;

                }


                saveChangesButton.disabled = true;

                saveChangesButton.textContent = 'Saving...';


                hideSubmittedMessage();


                const items =
                    submittedItems.map(
                        function (item) {

                            return {

                                product_id: item.product_id,

                                quantity: item.quantity,

                                notes: item.notes || null

                            };

                        }
                    );


                try {

                    const response =
                        await fetch(
                            `/api/orders/${currentOrderId}/items`,
                            {

                                method: 'PUT',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json'

                                },

                                body:
                                    JSON.stringify({
                                        items: items
                                    })

                            }
                        );


                    const data =
                        await response.json();


                    if (!response.ok) {

                        showSubmittedMessage(
                            data.message ||
                            'Failed to update order.',
                            'error'
                        );


                        saveChangesButton.disabled = false;

                        saveChangesButton.textContent = 'Save Changes';


                        return;

                    }


                    showSubmittedMessage(
                        'Order updated successfully!',
                        'success'
                    );


                    saveChangesButton.disabled = false;

                    saveChangesButton.textContent = 'Save Changes';


                } catch (error) {

                    console.error(error);


                    showSubmittedMessage(
                        'Something went wrong while saving changes.',
                        'error'
                    );


                    saveChangesButton.disabled = false;

                    saveChangesButton.textContent = 'Save Changes';

                }

            }
        );

    }


    /* =========================
       Request Waiter
    ========================== */

    if (requestHelpButton) {

        requestHelpButton.addEventListener(
            'click',
            async function () {

                if (!currentOrderId) {
                    return;
                }


                requestHelpButton.disabled = true;

                requestHelpButton.textContent = 'Notifying...';


                hideSubmittedMessage();


                try {

                    const response =
                        await fetch(
                            `/api/orders/${currentOrderId}/request-help`,
                            {

                                method: 'POST',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json'

                                }

                            }
                        );


                    const data =
                        await response.json();


                    showSubmittedMessage(
                        data.message ||
                        'The waiter has been notified.',
                        'success'
                    );


                    requestHelpButton.textContent =
                        'Waiter Notified';


                } catch (error) {

                    console.error(error);


                    showSubmittedMessage(
                        'Something went wrong. Please try again.',
                        'error'
                    );


                    requestHelpButton.disabled = false;

                    requestHelpButton.textContent =
                        'Request Waiter';

                }

            }
        );

    }


    /* =========================
       Auto-Format Card Inputs
    ========================== */

    if (cardNumberInput) {

        cardNumberInput.addEventListener('input', function () {

            let digits =
                this.value.replace(/\D/g, '').slice(0, 16);


            let formatted = '';

            for (let i = 0; i < digits.length; i += 4) {

                if (formatted) formatted += ' ';

                formatted += digits.slice(i, i + 4);

            }


            this.value = formatted;

        });

    }


    if (cardExpiryInput) {

        cardExpiryInput.addEventListener('input', function () {

            let digits =
                this.value.replace(/\D/g, '').slice(0, 4);


            if (digits.length >= 3) {

                this.value =
                    digits.slice(0, 2) + '/' + digits.slice(2);

            } else {

                this.value = digits;

            }

        });

    }


    if (cardCvvInput) {

        cardCvvInput.addEventListener('input', function () {

            this.value =
                this.value.replace(/\D/g, '').slice(0, 3);

        });

    }


    if (cardNameInput) {

        cardNameInput.addEventListener('input', function () {

            this.value =
                this.value.replace(/[^a-zA-Z\s'-]/g, '');

        });

    }


    /* =========================
       Camera Card Scan (Simulated)
       Replace the setTimeout block below with a real card-scanning
       SDK call (e.g. Stripe Card Scan, Paymob SDK) once available.
    ========================== */

    if (scanCardButton) {

        scanCardButton.addEventListener(
            'click',
            async function () {

                if (! navigator.mediaDevices || ! navigator.mediaDevices.getUserMedia) {

                    showPaymentModalError(
                        'Camera is not supported on this device/browser.'
                    );

                    return;

                }


                try {

                    cameraStream =
                        await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'environment' }
                        });


                    cameraVideo.srcObject = cameraStream;


                    cameraScanContainer.style.display = 'block';

                    scanCardButton.style.display = 'none';

                    cameraScanStatus.textContent =
                        'Point your camera at the card...';


                    setTimeout(
                        function () {

                            cameraScanStatus.textContent =
                                'Scanning...';

                        },
                        1200
                    );


                    // Simulated scan result — swap this for real OCR /
                    // card-scanning SDK output once integrated.
                    setTimeout(
                        function () {

                            stopCameraScan();


                            cardNumberInput.value = '4242 4242 4242 4242';

                            cardExpiryInput.value = '12/29';

                            cardCvvInput.value = '123';


                            if (! cardNameInput.value) {

                                cardNameInput.value =
                                    clientNameInput && clientNameInput.value
                                        ? clientNameInput.value
                                        : 'Card Holder';

                            }

                        },
                        2800
                    );


                } catch (error) {

                    console.error(error);


                    showPaymentModalError(
                        'Camera access was denied. Please enter card details manually.'
                    );

                }

            }
        );

    }


    if (cancelScanButton) {

        cancelScanButton.addEventListener(
            'click',
            function () {

                stopCameraScan();

            }
        );

    }


    function stopCameraScan() {

        if (cameraStream) {

            cameraStream.getTracks().forEach(
                function (track) {
                    track.stop();
                }
            );

            cameraStream = null;

        }


        if (cameraScanContainer) {
            cameraScanContainer.style.display = 'none';
        }


        if (scanCardButton) {
            scanCardButton.style.display = 'block';
        }

    }


    /* =========================
       Messages (Cart)
    ========================== */

    function showMessage(text, type) {

        if (!messageElement) {
            return;
        }


        messageElement.textContent =
            text;


        messageElement.className =
            type;

    }


    function hideMessage() {

        if (!messageElement) {
            return;
        }


        messageElement.textContent =
            '';


        messageElement.className =
            '';

    }


    /* =========================
       Messages (Submitted Order)
    ========================== */

    function showSubmittedMessage(text, type) {

        if (!submittedMessageElement) {
            return;
        }


        submittedMessageElement.textContent =
            text;


        submittedMessageElement.className =
            type;

    }


    function hideSubmittedMessage() {

        if (!submittedMessageElement) {
            return;
        }


        submittedMessageElement.textContent =
            '';


        submittedMessageElement.className =
            '';

    }


    /* =========================
       Prevent HTML Injection
    ========================== */

    function escapeHtml(value) {

        const div =
            document.createElement('div');


        div.textContent =
            value;


        return div.innerHTML;

    }


    /* =========================
       Initial Render
    ========================== */

    renderOrder();

});

</script>

@endpush