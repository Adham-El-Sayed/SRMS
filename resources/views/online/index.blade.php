@extends('layouts.menu')

@section('title', __('Order Online'))

@section('content')

<div class="online-app" id="online-app"
     data-delivery="{{ $deliveryOffered ? '1' : '' }}"
     data-delivery-fee="{{ number_format($deliveryFee, 2, '.', '') }}"
     data-open="{{ $open ? '1' : '' }}">

    {{-- ===== Welcome ===== --}}
    <header class="menu-hero">
        <p class="menu-hero__kicker">{{ __('Order Online') }}</p>
        <h1 class="menu-hero__title">{{ __('Order from home, we will do the rest.') }}</h1>
        <p class="menu-hero__note">
            @if ($deliveryOffered)
                {{ __('Choose your dishes, tell us where you are, and we will call you to confirm.') }}
            @else
                {{ __('Choose your dishes and collect them from us. We will call you to confirm.') }}
            @endif
        </p>
    </header>

    @unless ($open)
        <div class="closed-note">
            <strong>{{ __('We are closed right now.') }}</strong>
            <span>{{ __('Have a look at the menu — ordering opens again when the restaurant does.') }}</span>
        </div>
    @endunless

    {{-- ===== Jump to a section ===== --}}
    @if ($menu->count() > 1)
        <nav class="menu-tabs" aria-label="{{ __('Categories') }}">
            @foreach ($menu as $category)
                <a href="#category-{{ $category->id }}" class="menu-tab">{{ $category->name }}</a>
            @endforeach
        </nav>
    @endif

    {{-- ===== The menu ===== --}}
    @forelse ($menu as $category)
        <section class="menu-section" id="category-{{ $category->id }}">

            <div class="menu-section__head">
                @if ($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="menu-section__image" loading="lazy">
                @endif

                <div>
                    <h2 class="menu-section__title">{{ $category->name }}</h2>
                    @if ($category->description)
                        <p class="menu-section__note">{{ $category->description }}</p>
                    @endif
                </div>
            </div>

            <div class="dish-grid">
                @forelse ($category->products as $product)
                    <article class="dish {{ $product->isSoldOut() ? 'is-sold-out' : '' }}">
                        <div class="dish__image">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
                            @else
                                <div class="dish__image-placeholder">🍽</div>
                            @endif

                            @if ($product->isSoldOut())
                                <span class="dish__sold-out">{{ __('Finished for today') }}</span>
                            @endif
                        </div>

                        <div class="dish__body">
                            <h3 class="dish__name">{{ $product->name }}</h3>
                            <p class="dish__note">{{ $product->description ?? '' }}</p>

                            <div class="dish__foot">
                                <span class="dish__price">{{ number_format($product->price, 2) }} <small>{{ __('EGP') }}</small></span>

                                @if ($product->isSoldOut())
                                    <span class="dish__unavailable">{{ __('Unavailable') }}</span>
                                @elseif ($open)
                                    <button type="button" class="add-button"
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-price="{{ $product->price }}"
                                            aria-label="{{ __('Add') }} {{ $product->name }}">
                                        {{ __('Add') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="muted-text">{{ __('No products available in this category.') }}</p>
                @endforelse
            </div>
        </section>
    @empty
        <div class="empty">
            <div class="empty-icon">🍽</div>
            <h2>{{ __('No Menu Available') }}</h2>
            <p>{{ __('There are currently no menu items available.') }}</p>
        </div>
    @endforelse

</div>


{{-- ===== The bar that follows you down the page ===== --}}
<div class="cart-bar" id="cart-bar" hidden>
    <button type="button" class="cart-bar__button" id="open-order">
        <span class="cart-bar__count" id="cart-bar-count">0</span>
        <span class="cart-bar__label">{{ __('View your order') }}</span>
        <span class="cart-bar__total" id="cart-bar-total">0.00 {{ __('EGP') }}</span>
    </button>
</div>


{{-- ===== Your order, and how to get it ===== --}}
<div class="sheet" id="order-sheet" hidden>
    <div class="sheet__box" role="dialog" aria-modal="true" aria-labelledby="order-sheet-title">

        <div class="sheet__head">
            <div>
                <h2 id="order-sheet-title">{{ __('Your Order') }}</h2>
                <p class="muted-text">{{ __('Review your items before submitting.') }}</p>
            </div>

            <span class="order-count" id="order-count">0 {{ __('Items') }}</span>

            <button type="button" class="sheet__close" data-close-sheet aria-label="{{ __('Close') }}">&times;</button>
        </div>

        <div class="sheet__body">
            <div id="order-items"></div>

            {{-- How it reaches them --}}
            <div class="checkout">

                <h3 class="checkout__title">{{ __('How would you like it?') }}</h3>

                <div class="choice-grid">
                    <label class="choice">
                        <input type="radio" name="fulfilment" value="collection" checked>
                        <span class="choice__box">
                            <span class="choice__name">{{ __('I will collect it') }}</span>
                            <span class="choice__note">{{ __('Pick it up from the restaurant.') }}</span>
                        </span>
                    </label>

                    @if ($deliveryOffered)
                        <label class="choice">
                            <input type="radio" name="fulfilment" value="delivery">
                            <span class="choice__box">
                                <span class="choice__name">{{ __('Deliver to me') }}</span>
                                <span class="choice__note">
                                    @if ($deliveryFee > 0)
                                        {{ __('Delivery fee') }}: {{ number_format($deliveryFee, 2) }} {{ __('EGP') }}
                                    @else
                                        {{ __('Free delivery') }}
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endif
                </div>

                <div class="field">
                    <label for="client-name">{{ __('Name') }}</label>
                    <input type="text" id="client-name" maxlength="100" autocomplete="name"
                           placeholder="{{ __('Who is this order for?') }}">
                </div>

                <div class="field">
                    <label for="client-phone">{{ __('Phone Number') }}</label>
                    <input type="tel" id="client-phone" maxlength="20" autocomplete="tel"
                           placeholder="{{ __('So we can call you back') }}">
                </div>

                <div class="field" id="address-field" hidden>
                    <label for="delivery-address">{{ __('Delivery address') }}</label>
                    <textarea id="delivery-address" rows="3" maxlength="500"
                              placeholder="{{ __('Street, building, floor, flat — and a landmark if it helps.') }}"></textarea>
                </div>

                <p class="pay-note">
                    💵 {{ __('You pay in cash when you collect it or when it arrives.') }}
                </p>
            </div>
        </div>

        <div class="sheet__foot">
            <div class="order-total">
                <span>{{ __('Items') }}</span>
                <span id="order-subtotal">0.00 {{ __('EGP') }}</span>
            </div>

            <div class="order-total order-total--fee" id="fee-row" hidden>
                <span>{{ __('Delivery fee') }}</span>
                <span id="order-fee">0.00 {{ __('EGP') }}</span>
            </div>

            <div class="order-total order-total--grand">
                <span>{{ __('Total') }}</span>
                <span id="order-total">0.00 {{ __('EGP') }}</span>
            </div>

            <button type="button" id="submit-order" class="submit-button" disabled>{{ __('Place Order') }}</button>

            <div id="message"></div>
        </div>
    </div>
</div>


{{-- ===== After it is placed: following it along ===== --}}
<div class="sheet" id="tracking-sheet" hidden>
    <div class="sheet__box" role="dialog" aria-modal="true">

        <div class="sheet__head">
            <div>
                <h2>{{ __('Order #') }}<span id="tracked-id"></span></h2>
                <p class="muted-text" id="tracked-note">{{ __('We have your order. The restaurant will call you to confirm.') }}</p>
            </div>

            <button type="button" class="sheet__close" data-close-sheet aria-label="{{ __('Close') }}">&times;</button>
        </div>

        <div class="sheet__body">

            <ol class="track">
                <li data-step="pending"><span class="track__dot"></span>{{ __('Received') }}</li>
                <li data-step="confirmed"><span class="track__dot"></span>{{ __('Confirmed') }}</li>
                <li data-step="preparing"><span class="track__dot"></span>{{ __('Preparing') }}</li>
                <li data-step="ready"><span class="track__dot"></span>{{ __('Ready') }}</li>
            </ol>

            <div id="tracked-items"></div>
        </div>

        <div class="sheet__foot">
            <div class="order-total order-total--grand">
                <span>{{ __('Total') }}</span>
                <span id="tracked-total">0.00 {{ __('EGP') }}</span>
            </div>

            <button type="button" id="new-order-button" class="submit-button ghost">{{ __('Start another order') }}</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/srms-menu.css') }}?v={{ @filemtime(public_path('css/srms-menu.css')) }}">
<style>
    /* ==================================================================
       What the online page adds on top of the shared guest menu: the
       closed notice, the checkout block, and the little progress line a
       customer watches while their food is being cooked.
       ================================================================== */

    .online-app { padding-bottom: 96px; }

    .closed-note {
        display: flex; flex-direction: column; gap: 4px;
        margin: 0 0 26px; padding: 15px 18px;
        border-radius: var(--r-md);
        background: var(--amber-soft);
        border: 1px solid rgba(176, 123, 20, .25);
        color: var(--warn);
        font-size: 14px;
    }

    .closed-note strong { font-size: 15px; }
    .closed-note span { color: var(--ink-soft); }

    /* ---------- checkout ---------- */

    .checkout {
        margin-top: 22px; padding-top: 20px;
        border-top: 1px solid var(--line-strong);
    }

    .checkout__title { margin: 0 0 14px; font-size: 16px; }

    .choice-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px; margin-bottom: 18px;
    }

    .choice { display: block; cursor: pointer; }
    .choice input { position: absolute; opacity: 0; pointer-events: none; }

    .choice__box {
        display: flex; flex-direction: column; gap: 3px;
        padding: 13px 15px; height: 100%;
        border-radius: var(--r-md);
        background: var(--surface);
        border: 1.5px solid var(--line-strong);
        transition: border-color .16s ease, background-color .16s ease;
    }

    .choice__name { font-weight: 600; font-size: 14.5px; }
    .choice__note { font-size: 12.5px; color: var(--muted); }

    .choice input:checked + .choice__box {
        border-color: var(--accent);
        background: var(--accent-soft);
    }

    .choice input:focus-visible + .choice__box { outline: 2px solid var(--accent); outline-offset: 2px; }

    .field { margin-bottom: 14px; }

    .field label {
        display: block; margin-bottom: 6px;
        font-size: 13px; font-weight: 600; color: var(--ink-soft);
    }

    .field input, .field textarea {
        width: 100%; padding: 12px 14px;
        border-radius: var(--r-sm);
        border: 1.5px solid var(--line-strong);
        background: var(--surface);
        font: inherit; font-size: 15px; color: var(--ink);
    }

    .field input:focus, .field textarea:focus {
        outline: none; border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-soft);
    }

    .field textarea { resize: vertical; line-height: 1.55; }
    .field.has-error input, .field.has-error textarea { border-color: var(--danger); }

    .pay-note {
        margin: 4px 0 0; padding: 11px 14px;
        border-radius: var(--r-sm);
        background: var(--surface-sunk);
        color: var(--ink-soft); font-size: 13.5px;
    }

    /* The design system makes every .order-total a flex row, which would
       otherwise win against the hidden attribute. */
    .order-total--fee span:last-child { color: var(--muted); }
    #fee-row[hidden] { display: none; }

    .order-total--grand {
        margin-top: 4px; padding-top: 12px;
        border-top: 1px solid var(--line-strong);
        font-family: var(--font-display); font-size: 19px;
    }

    /* ---------- following the order ---------- */

    .track {
        display: flex; justify-content: space-between;
        gap: 6px; margin: 4px 0 24px; padding: 0;
        list-style: none; position: relative;
    }

    .track::before {
        content: ''; position: absolute;
        top: 9px; inset-inline: 22px; height: 2px;
        background: var(--line-strong);
    }

    .track li {
        position: relative; z-index: 1;
        flex: 1; text-align: center;
        font-size: 11.5px; font-weight: 600;
        color: var(--muted);
    }

    .track__dot {
        display: block; width: 20px; height: 20px; margin: 0 auto 7px;
        border-radius: 50%;
        background: var(--paper);
        border: 2px solid var(--line-strong);
        transition: background-color .2s ease, border-color .2s ease;
    }

    .track li.is-done { color: var(--olive); }
    .track li.is-done .track__dot { background: var(--olive); border-color: var(--olive); }

    .track li.is-now { color: var(--accent); }
    .track li.is-now .track__dot {
        background: var(--accent); border-color: var(--accent);
        box-shadow: 0 0 0 4px var(--accent-soft);
    }

    .submit-button.ghost {
        background: var(--surface); color: var(--ink);
        border: 1.5px solid var(--line-strong);
    }

    @media (max-width: 620px) {
        .choice-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
/*
 * Ordering from the website.
 *
 * The cart lives in this page only. The moment an order is placed the server
 * hands back a secret token; it is kept in this browser so the customer can
 * watch their own order and nobody else's — there is no way from here to ask
 * about an order you did not place.
 */
(function () {
    'use strict';

    const app = document.getElementById('online-app');
    if (!app) return;

    const DELIVERY_OFFERED = app.dataset.delivery === '1';
    const DELIVERY_FEE     = parseFloat(app.dataset.deliveryFee || '0') || 0;
    const IS_OPEN          = app.dataset.open === '1';
    const KEPT             = 'srms.onlineOrder';

    let cart    = [];
    let tracked = null;     // { id, token }
    let poll    = null;

    /* ---------- the bits of the page we talk to ---------- */

    const bar          = document.getElementById('cart-bar');
    const barCount     = document.getElementById('cart-bar-count');
    const barTotal     = document.getElementById('cart-bar-total');
    const sheet        = document.getElementById('order-sheet');
    const sheetItems   = document.getElementById('order-items');
    const countChip    = document.getElementById('order-count');
    const subtotalEl   = document.getElementById('order-subtotal');
    const feeRow       = document.getElementById('fee-row');
    const feeEl        = document.getElementById('order-fee');
    const totalEl      = document.getElementById('order-total');
    const submitButton = document.getElementById('submit-order');
    const message      = document.getElementById('message');
    const nameInput    = document.getElementById('client-name');
    const phoneInput   = document.getElementById('client-phone');
    const addressField = document.getElementById('address-field');
    const addressInput = document.getElementById('delivery-address');

    const tracking     = document.getElementById('tracking-sheet');
    const trackedId    = document.getElementById('tracked-id');
    const trackedNote  = document.getElementById('tracked-note');
    const trackedItems = document.getElementById('tracked-items');
    const trackedTotal = document.getElementById('tracked-total');

    /* ---------- small helpers ---------- */

    function escapeHtml(text) {
        const box = document.createElement('div');
        box.textContent = text === null || text === undefined ? '' : String(text);
        return box.innerHTML;
    }

    function money(amount) {
        return amount.toFixed(2) + ' ' + __t('EGP');
    }

    function keep(value) {
        try {
            if (value === null) window.localStorage.removeItem(KEPT);
            else window.localStorage.setItem(KEPT, JSON.stringify(value));
        } catch (e) { /* private browsing: the order still works, just not after a refresh */ }
    }

    function kept() {
        try {
            const raw = window.localStorage.getItem(KEPT);
            return raw ? JSON.parse(raw) : null;
        } catch (e) { return null; }
    }

    function say(text, kind) {
        message.textContent = text;
        message.className = kind === 'error' ? 'error-message' : 'success-message';
    }

    function hush() {
        message.textContent = '';
        message.className = '';
    }

    function chosenFulfilment() {
        const picked = document.querySelector('input[name="fulfilment"]:checked');
        return picked ? picked.value : 'collection';
    }

    function openSheet(el) {
        el.hidden = false;
        el.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeSheet(el) {
        el.classList.remove('is-open');
        el.hidden = true;
        document.body.style.overflow = '';
    }

    /* ---------- the cart ---------- */

    function render() {
        const wantsDelivery = DELIVERY_OFFERED && chosenFulfilment() === 'delivery';
        const fee = wantsDelivery ? DELIVERY_FEE : 0;

        if (addressField) addressField.hidden = !wantsDelivery;

        if (!cart.length) {
            sheetItems.innerHTML =
                '<div class="empty-order">' +
                    '<div class="empty-order-icon">🍽</div>' +
                    '<p>' + __t('Your order is empty') + '</p>' +
                    '<span>' + __t('Add something from the menu to get started.') + '</span>' +
                '</div>';
        } else {
            let html = '';

            cart.forEach(function (item, index) {
                const lineTotal = item.price * item.quantity;

                html +=
                    '<div class="order-item">' +
                        '<div class="order-item-info">' +
                            '<div class="order-item-name">' + escapeHtml(item.name) + '</div>' +
                            '<div class="order-item-price">' + item.price.toFixed(2) + ' ' + __t('EGP each') + '</div>' +
                        '</div>' +
                        '<div class="quantity-controls">' +
                            '<button type="button" class="quantity-button" data-action="decrease" data-index="' + index + '">&minus;</button>' +
                            '<span class="quantity">' + item.quantity + '</span>' +
                            '<button type="button" class="quantity-button" data-action="increase" data-index="' + index + '">+</button>' +
                        '</div>' +
                        '<strong class="item-subtotal">' + money(lineTotal) + '</strong>' +
                        '<button type="button" class="remove-button" data-action="remove" data-index="' + index + '">' + __t('Remove') + '</button>' +
                    '</div>';
            });

            sheetItems.innerHTML = html;
        }

        const pieces   = cart.reduce(function (sum, item) { return sum + item.quantity; }, 0);
        const subtotal = cart.reduce(function (sum, item) { return sum + item.price * item.quantity; }, 0);

        barCount.textContent  = pieces;
        barTotal.textContent  = money(subtotal + fee);
        countChip.textContent = pieces + ' ' + __t('Items');
        subtotalEl.textContent = money(subtotal);
        feeEl.textContent      = money(fee);
        totalEl.textContent    = money(subtotal + fee);

        if (feeRow) feeRow.hidden = !wantsDelivery;

        bar.hidden = cart.length === 0;
        submitButton.disabled = cart.length === 0;
    }

    function add(product) {
        const found = cart.find(function (item) { return item.product_id === product.id; });

        if (found) found.quantity += 1;
        else cart.push({ product_id: product.id, name: product.name, price: product.price, quantity: 1 });

        render();
    }

    /* ---------- adding from the menu ---------- */

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.add-button');
        if (!button || !IS_OPEN) return;

        add({
            id: parseInt(button.dataset.productId, 10),
            name: button.dataset.productName,
            price: parseFloat(button.dataset.productPrice)
        });

        button.textContent = __t('Added');
        setTimeout(function () { button.textContent = __t('Add'); }, 900);
    });

    /* ---------- changing what is in it ---------- */

    sheetItems.addEventListener('click', function (event) {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const index = parseInt(button.dataset.index, 10);
        if (!cart[index]) return;

        if (button.dataset.action === 'increase') cart[index].quantity += 1;
        if (button.dataset.action === 'decrease') {
            cart[index].quantity -= 1;
            if (cart[index].quantity < 1) cart.splice(index, 1);
        }
        if (button.dataset.action === 'remove') cart.splice(index, 1);

        render();

        if (!cart.length) closeSheet(sheet);
    });

    document.querySelectorAll('input[name="fulfilment"]').forEach(function (radio) {
        radio.addEventListener('change', render);
    });

    document.getElementById('open-order').addEventListener('click', function () {
        hush();
        openSheet(sheet);
    });

    document.querySelectorAll('[data-close-sheet]').forEach(function (button) {
        button.addEventListener('click', function () {
            closeSheet(button.closest('.sheet'));
        });
    });

    [sheet, tracking].forEach(function (el) {
        el.addEventListener('click', function (event) {
            if (event.target === el) closeSheet(el);
        });
    });

    /* ---------- placing it ---------- */

    function markError(field, wrong) {
        const wrap = field.closest('.field');
        if (wrap) wrap.classList.toggle('has-error', wrong);
    }

    submitButton.addEventListener('click', async function () {
        hush();

        const wantsDelivery = DELIVERY_OFFERED && chosenFulfilment() === 'delivery';
        const name    = nameInput.value.trim();
        const phone   = phoneInput.value.trim();
        const address = addressInput ? addressInput.value.trim() : '';

        markError(nameInput, !name);
        markError(phoneInput, !phone);
        if (addressInput) markError(addressInput, wantsDelivery && !address);

        if (!name || !phone || (wantsDelivery && !address)) {
            say(__t('Please fill in your name, phone number and address.'), 'error');
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = __t('Submitting...');

        try {
            const response = await fetch('/api/online-orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    items: cart.map(function (item) {
                        return { product_id: item.product_id, quantity: item.quantity, notes: item.notes || null };
                    }),
                    client_name: name,
                    client_phone: phone,
                    fulfilment: wantsDelivery ? 'delivery' : 'collection',
                    delivery_address: wantsDelivery ? address : null
                })
            });

            const data = await response.json().catch(function () { return {}; });

            if (!response.ok) {
                say(data.message || __t('Failed to create order.'), 'error');
                submitButton.disabled = false;
                submitButton.textContent = __t('Place Order');
                return;
            }

            cart = [];
            render();
            closeSheet(sheet);

            tracked = { id: data.order.id, token: data.order_token };
            keep(tracked);
            showTracking(data.order);
            watch();

        } catch (error) {
            say(__t('Something went wrong. Please try again.'), 'error');
        }

        submitButton.disabled = false;
        submitButton.textContent = __t('Place Order');
    });

    /* ---------- following it ---------- */

    const STEPS = ['pending', 'confirmed', 'preparing', 'ready'];

    const WORDS = {
        pending:   __t('We have your order. The restaurant will call you to confirm.'),
        confirmed: __t('Your order is confirmed and going to the kitchen.'),
        preparing: __t('The kitchen is cooking your order now.'),
        ready:     __t('Your order is ready.'),
        completed: __t('Your order is on its way. Enjoy your meal!'),
        cancelled: __t('This order was cancelled. Please call the restaurant.')
    };

    function showTracking(order) {
        trackedId.textContent = order.id;
        trackedNote.textContent = WORDS[order.status] || '';
        trackedTotal.textContent = money(parseFloat(order.total));

        let html = '';

        (order.items || []).forEach(function (item) {
            html +=
                '<div class="order-item">' +
                    '<div class="order-item-info">' +
                        '<div class="order-item-name">' + escapeHtml(item.product_name) + '</div>' +
                        '<div class="order-item-price">' + item.quantity + ' × ' + parseFloat(item.unit_price).toFixed(2) + ' ' + __t('EGP') + '</div>' +
                    '</div>' +
                    '<strong class="item-subtotal">' + money(parseFloat(item.subtotal)) + '</strong>' +
                '</div>';
        });

        const fee = parseFloat(order.delivery_fee || 0);

        if (fee > 0) {
            html +=
                '<div class="order-item">' +
                    '<div class="order-item-info">' +
                        '<div class="order-item-name">' + __t('Delivery fee') + '</div>' +
                        '<div class="order-item-price">' + escapeHtml(order.delivery_address || '') + '</div>' +
                    '</div>' +
                    '<strong class="item-subtotal">' + money(fee) + '</strong>' +
                '</div>';
        }

        trackedItems.innerHTML = html;

        const reached = STEPS.indexOf(order.status);

        tracking.querySelectorAll('.track li').forEach(function (step, index) {
            step.classList.toggle('is-done', reached > index || order.status === 'completed');
            step.classList.toggle('is-now', reached === index);
        });

        openSheet(tracking);
    }

    async function refreshTracked() {
        if (!tracked) return;

        try {
            const response = await fetch('/api/orders/' + tracked.id, {
                headers: { 'Accept': 'application/json', 'X-Order-Token': tracked.token }
            });

            if (!response.ok) { forget(); return; }

            const data = await response.json();

            if (!tracking.hidden) showTracking(data.order);

            // Once it is out of the kitchen there is nothing left to watch.
            if (['completed', 'cancelled'].indexOf(data.order.status) !== -1) stopWatching();

        } catch (error) { /* offline for a moment: the next tick tries again */ }
    }

    function watch() {
        stopWatching();
        poll = setInterval(refreshTracked, 10000);
    }

    function stopWatching() {
        if (poll) clearInterval(poll);
        poll = null;
    }

    function forget() {
        stopWatching();
        tracked = null;
        keep(null);
        closeSheet(tracking);
    }

    document.getElementById('new-order-button').addEventListener('click', forget);

    /* ---------- coming back to the page ---------- */

    const remembered = kept();

    if (remembered && remembered.id && remembered.token) {
        tracked = remembered;

        fetch('/api/orders/' + tracked.id, {
            headers: { 'Accept': 'application/json', 'X-Order-Token': tracked.token }
        }).then(function (response) {
            return response.ok ? response.json() : null;
        }).then(function (data) {
            if (!data) { forget(); return; }

            if (['completed', 'cancelled'].indexOf(data.order.status) !== -1) { forget(); return; }

            showTracking(data.order);
            watch();
        }).catch(function () { /* nothing to show, the menu is still there */ });
    }

    render();
})();
</script>
@endpush
