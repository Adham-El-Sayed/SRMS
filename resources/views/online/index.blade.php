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

    {{-- ===== Finding a dish ===== --}}
    @if ($menu->count() > 0)
        @include('partials.menu-filter', ['categories' => $menu, 'scope' => '#online-app'])
    @endif

    {{-- ===== The menu ===== --}}
    @forelse ($menu as $category)
        <section class="menu-section" id="category-{{ $category->id }}"
                 data-filter-group data-category="{{ $category->id }}">

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
                    <article class="dish {{ $product->isSoldOut() ? 'is-sold-out' : '' }}"
                             data-filter-item
                             data-name="{{ $product->name }}"
                             data-category="{{ $category->id }}"
                             data-dish="{{ $product->id }}">
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

                                    {{-- Once it is in the order, the card itself
                                         counts it: no need to open the basket to
                                         ask for a second one. --}}
                                    <div class="dish__stepper" data-stepper="{{ $product->id }}" hidden>
                                        <button type="button" class="quantity-button" data-step="-1" data-product-id="{{ $product->id }}"
                                                aria-label="{{ __('Remove') }}">&minus;</button>
                                        <span class="quantity" data-qty="{{ $product->id }}">0</span>
                                        <button type="button" class="quantity-button" data-step="1" data-product-id="{{ $product->id }}"
                                                aria-label="{{ __('Add') }}">+</button>
                                    </div>
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

    <p class="filter-empty" data-filter-empty hidden>{{ __('Nothing on the menu matches that.') }}</p>

</div>


{{-- ===== The bars that follow you down the page ===== --}}
<div class="bottom-bars">

    {{-- A live order stays reachable here for as long as it is cooking, so
         closing the panel never loses it. --}}
    <div class="track-bar" id="track-bar" hidden>
        <button type="button" class="track-bar__button" id="open-tracking">
            <span class="track-bar__dot"></span>
            <span class="track-bar__text">
                <strong id="track-bar-id">{{ __('Order #') }}</strong>
                <span id="track-bar-status">{{ __('Received') }}</span>
            </span>
            <span class="track-bar__cta">{{ __('Follow it') }}</span>
        </button>
    </div>

    <div class="cart-bar" id="cart-bar" hidden>
        <button type="button" class="cart-bar__button" id="open-order">
            <span class="cart-bar__count" id="cart-bar-count">0</span>
            <span class="cart-bar__label">{{ __('View your order') }}</span>
            <span class="cart-bar__total" id="cart-bar-total">0.00 {{ __('EGP') }}</span>
        </button>
    </div>

</div>


{{-- ===== Your order: items first, then how to get it ===== --}}
<div class="sheet" id="order-sheet" hidden>
    <div class="sheet__box" role="dialog" aria-modal="true" aria-labelledby="sheet-title">

        <div class="sheet__head">
            <button type="button" class="sheet__back" id="step-back" hidden aria-label="{{ __('Back') }}">
                <span class="sheet__back-arrow">&#8592;</span>
            </button>

            <div>
                <h2 id="sheet-title">{{ __('Your Order') }}</h2>
                <p class="muted-text" id="sheet-sub">{{ __('Review your items before submitting.') }}</p>
            </div>

            <span class="order-count" id="order-count">0 {{ __('Items') }}</span>

            <button type="button" class="sheet__close" data-close-sheet aria-label="{{ __('Close') }}">&times;</button>
        </div>

        <div class="sheet__body">

            {{-- Step one --}}
            <div id="step-items">
                <div id="order-items"></div>

                <button type="button" class="add-more" data-close-sheet>
                    + {{ __('Add something else') }}
                </button>
            </div>

            {{-- Step two --}}
            <div id="step-details" hidden>

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

            <button type="button" id="to-details" class="submit-button" disabled>{{ __('Continue') }}</button>
            <button type="button" id="submit-order" class="submit-button" hidden>{{ __('Place Order') }}</button>

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
       What the online page adds on top of the shared guest menu: finding a
       dish, counting it on its own card, a two-step basket, and the bar
       that keeps a live order one tap away.
       ================================================================== */

    .online-app { padding-bottom: 120px; }

    .closed-note {
        display: flex; flex-direction: column; gap: 4px;
        margin: 0 0 22px; padding: 15px 18px;
        border-radius: var(--r-md, 13px);
        background: var(--amber-soft);
        border: 1px solid rgba(176, 123, 20, .25);
        color: var(--warn);
        font-size: 14px;
    }

    .closed-note strong { font-size: 15px; }
    .closed-note span { color: var(--ink-soft); }

    /* ---------- counting a dish on its own card ---------- */

    .dish__stepper {
        display: inline-flex; align-items: center; justify-content: center; gap: 14px;
        padding: 3px; border-radius: 100px;
        background: var(--accent-soft);
        border: 1px solid rgba(189, 78, 44, .22);
    }

    .dish__stepper[hidden] { display: none; }

    /* The design system gives these a display of their own, which would
       otherwise beat the hidden attribute. */
    body .dish .add-button[hidden],
    body .sheet .submit-button[hidden],
    body .sheet .order-count[hidden],
    body .sheet .sheet__back[hidden] { display: none; }

    .dish__stepper .quantity-button {
        width: 30px; height: 30px; padding: 0;
        display: grid; place-items: center;
        border-radius: 50%; border: none;
        background: var(--surface); color: var(--accent-dark);
        font-size: 17px; font-weight: 700; line-height: 1; cursor: pointer;
    }

    .dish__stepper .quantity-button:hover { background: var(--accent); color: #fff; }
    .dish__stepper .quantity { min-width: 22px; text-align: center; font-weight: 700; font-size: 14.5px; }

    .dish.is-chosen { border-color: var(--accent); }

    /* ---------- the bars at the bottom ---------- */

    .bottom-bars {
        position: fixed; inset-inline: 0; bottom: 0; z-index: 60;
        display: flex; flex-direction: column; gap: 8px;
        padding: 0 16px calc(14px + env(safe-area-inset-bottom));
        pointer-events: none;
    }

    .bottom-bars > * { pointer-events: auto; }

    /* The shared stylesheet pins the cart bar itself; inside this stack it
       simply sits in the flow. */
    .bottom-bars .cart-bar {
        position: static; padding: 0; margin: 0 auto; width: 100%;
        max-width: 560px; background: none; border: none; box-shadow: none;
        backdrop-filter: none; -webkit-backdrop-filter: none;
    }

    .track-bar { width: 100%; max-width: 560px; margin: 0 auto; }
    .track-bar[hidden] { display: none; }

    .track-bar__button {
        width: 100%;
        display: flex; align-items: center; gap: 11px;
        padding: 12px 16px;
        border: none; border-radius: 100px;
        background: var(--ink); color: #F3EADF;
        font: inherit; font-size: 14px; cursor: pointer;
        box-shadow: 0 10px 26px -12px rgba(36, 29, 24, .7);
        text-align: start;
    }

    .track-bar__dot {
        width: 9px; height: 9px; flex-shrink: 0;
        border-radius: 50%; background: #7FB08C;
        box-shadow: 0 0 0 4px rgba(127, 176, 140, .22);
        animation: track-pulse 1.8s ease-in-out infinite;
    }

    @keyframes track-pulse {
        50% { box-shadow: 0 0 0 7px rgba(127, 176, 140, .08); }
    }

    .track-bar__text { display: flex; flex-direction: column; line-height: 1.25; min-width: 0; }
    .track-bar__text strong { font-size: 14px; }
    .track-bar__text span { font-size: 12.5px; color: #BCA994; }

    .track-bar__cta {
        margin-inline-start: auto; flex-shrink: 0;
        padding: 6px 13px; border-radius: 100px;
        background: rgba(255, 255, 255, .12);
        font-size: 12.5px; font-weight: 600;
    }

    /* ---------- a line in the basket ---------- */

    .sheet .order-item { flex-wrap: nowrap; gap: 12px; }
    .sheet .order-item-info { min-width: 0; }
    .sheet .order-item-name { overflow-wrap: anywhere; }
    .sheet .item-subtotal { min-width: 78px; }

    .add-more {
        width: 100%; margin-top: 14px; padding: 12px;
        border-radius: var(--r-sm, 9px);
        border: 1.5px dashed var(--line-strong);
        background: transparent; color: var(--ink-soft);
        font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;
    }

    .add-more:hover { border-color: var(--accent); color: var(--accent-dark); }

    /* ---------- going back a step ---------- */

    .sheet__back {
        width: 34px; height: 34px; padding: 0; flex-shrink: 0;
        display: grid; place-items: center;
        border: 1px solid var(--line-strong); border-radius: 50%;
        background: var(--surface); color: var(--ink-soft);
        font-size: 16px; line-height: 1; cursor: pointer;
        margin-inline-end: 4px;
    }

    .sheet__back[hidden] { display: none; }
    .sheet__back:hover { border-color: var(--accent); color: var(--accent-dark); }
    html[dir="rtl"] .sheet__back-arrow { display: inline-block; transform: scaleX(-1); }

    /* ---------- telling us how to reach you ---------- */

    .checkout__title { margin: 0 0 14px; font-size: 16px; }

    .choice-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px; margin-bottom: 20px;
    }

    .choice { display: block; cursor: pointer; }
    .choice input { position: absolute; opacity: 0; pointer-events: none; }

    .choice__box {
        display: flex; flex-direction: column; gap: 3px;
        padding: 13px 15px; height: 100%;
        border-radius: var(--r-md, 13px);
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

    .field { margin-bottom: 15px; }

    .field label {
        display: block; margin-bottom: 6px;
        font-size: 13px; font-weight: 600; color: var(--ink-soft);
    }

    body .sheet .field input, body .sheet .field textarea {
        width: 100%; padding: 12px 14px;
        border-radius: var(--r-sm, 9px);
        border: 1.5px solid var(--line-strong);
        background: var(--surface);
        font-family: var(--font-sans); font-size: 15px; color: var(--ink);
    }

    body .sheet .field input:focus, body .sheet .field textarea:focus {
        outline: none; border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-soft);
    }

    /* Three lines is plenty for an address; the system's 96px minimum makes
       the step taller than the phone. */
    body .sheet .field textarea { min-height: 84px; resize: vertical; line-height: 1.55; }
    body .sheet .field.has-error input, body .sheet .field.has-error textarea { border-color: var(--danger); }

    .pay-note {
        margin: 2px 0 0; padding: 11px 14px;
        border-radius: var(--r-sm, 9px);
        background: var(--surface-sunk);
        color: var(--ink-soft); font-size: 13.5px;
    }

    /* The design system makes every .order-total a flex row, which would
       otherwise win against the hidden attribute. */
    .order-total--fee span:last-child { color: var(--muted); }
    #fee-row[hidden] { display: none; }

    .order-total--grand {
        margin-top: 2px; padding-top: 12px;
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
        .track-bar__cta { padding: 6px 11px; font-size: 12px; }
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
    let step    = 'items';

    /* ---------- the bits of the page we talk to ---------- */

    const bar          = document.getElementById('cart-bar');
    const barCount     = document.getElementById('cart-bar-count');
    const barTotal     = document.getElementById('cart-bar-total');
    const sheet        = document.getElementById('order-sheet');
    const sheetItems   = document.getElementById('order-items');
    const sheetTitle   = document.getElementById('sheet-title');
    const sheetSub     = document.getElementById('sheet-sub');
    const stepItems    = document.getElementById('step-items');
    const stepDetails  = document.getElementById('step-details');
    const backButton   = document.getElementById('step-back');
    const countChip    = document.getElementById('order-count');
    const subtotalEl   = document.getElementById('order-subtotal');
    const feeRow       = document.getElementById('fee-row');
    const feeEl        = document.getElementById('order-fee');
    const totalEl      = document.getElementById('order-total');
    const continueBtn  = document.getElementById('to-details');
    const submitButton = document.getElementById('submit-order');
    const message      = document.getElementById('message');
    const nameInput    = document.getElementById('client-name');
    const phoneInput   = document.getElementById('client-phone');
    const addressField = document.getElementById('address-field');
    const addressInput = document.getElementById('delivery-address');

    const trackBar     = document.getElementById('track-bar');
    const trackBarId   = document.getElementById('track-bar-id');
    const trackBarWhat = document.getElementById('track-bar-status');
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
        document.body.style.overflow = 'hidden';
    }

    function closeSheet(el) {
        el.hidden = true;
        document.body.style.overflow = '';
    }

    /* ---------- which step of the basket we are on ---------- */

    function showStep(which) {
        step = which;

        const onDetails = which === 'details';

        stepItems.hidden    = onDetails;
        stepDetails.hidden  = !onDetails;
        backButton.hidden   = !onDetails;
        continueBtn.hidden  = onDetails;
        submitButton.hidden = !onDetails;
        countChip.hidden    = onDetails;

        sheetTitle.textContent = onDetails ? __t('Almost done') : __t('Your Order');
        sheetSub.textContent   = onDetails
            ? __t('Tell us who you are and how to reach you.')
            : __t('Review your items before submitting.');

        sheet.querySelector('.sheet__body').scrollTop = 0;
        hush();
    }

    /* ---------- the cart ---------- */

    function quantityOf(productId) {
        const found = cart.find(function (item) { return item.product_id === productId; });
        return found ? found.quantity : 0;
    }

    /* Each dish card shows either an Add button or its own counter. */
    function paintCards() {
        document.querySelectorAll('.dish[data-dish]').forEach(function (card) {
            const id = parseInt(card.dataset.dish, 10);
            const quantity = quantityOf(id);
            const stepper = card.querySelector('[data-stepper]');
            const add = card.querySelector('.add-button');
            const number = card.querySelector('[data-qty]');

            if (!stepper || !add) return;

            stepper.hidden = quantity === 0;
            add.hidden = quantity > 0;
            card.classList.toggle('is-chosen', quantity > 0);
            if (number) number.textContent = quantity;
        });
    }

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

        barCount.textContent   = pieces;
        barTotal.textContent   = money(subtotal + fee);
        countChip.textContent  = pieces + ' ' + __t('Items');
        subtotalEl.textContent = money(subtotal);
        feeEl.textContent      = money(fee);
        totalEl.textContent    = money(subtotal + fee);

        if (feeRow) feeRow.hidden = !wantsDelivery;

        bar.hidden = cart.length === 0;
        continueBtn.disabled = cart.length === 0;
        submitButton.disabled = cart.length === 0;

        paintCards();
    }

    function bump(productId, by, seed) {
        const found = cart.find(function (item) { return item.product_id === productId; });

        if (found) {
            found.quantity += by;
            if (found.quantity < 1) cart.splice(cart.indexOf(found), 1);
        } else if (by > 0 && seed) {
            cart.push({ product_id: productId, name: seed.name, price: seed.price, quantity: by });
        }

        render();
    }

    /* ---------- adding from the menu ---------- */

    document.addEventListener('click', function (event) {
        if (!IS_OPEN) return;

        const add = event.target.closest('.dish .add-button');

        if (add) {
            bump(parseInt(add.dataset.productId, 10), 1, {
                name: add.dataset.productName,
                price: parseFloat(add.dataset.productPrice)
            });
            return;
        }

        const stepper = event.target.closest('.dish__stepper .quantity-button');

        if (stepper) {
            bump(parseInt(stepper.dataset.productId, 10), parseInt(stepper.dataset.step, 10));
        }
    });

    /* ---------- changing what is in the basket ---------- */

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
        showStep('items');
        openSheet(sheet);
    });

    continueBtn.addEventListener('click', function () {
        if (!cart.length) return;
        showStep('details');
    });

    backButton.addEventListener('click', function () { showStep('items'); });

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

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        if (!sheet.hidden) closeSheet(sheet);
        else if (!tracking.hidden) closeSheet(tracking);
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
            showStep('items');

            tracked = { id: data.order.id, token: data.order_token };
            keep(tracked);
            showTracking(data.order);
            openSheet(tracking);
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

    const SHORT = {
        pending:   __t('Received'),
        confirmed: __t('Confirmed'),
        preparing: __t('Preparing'),
        ready:     __t('Ready'),
        completed: __t('Completed'),
        cancelled: __t('Cancelled')
    };

    /* Fills the panel and the bar. The bar is what survives a closed panel. */
    function showTracking(order) {
        trackedId.textContent = order.id;
        trackedNote.textContent = WORDS[order.status] || '';
        trackedTotal.textContent = money(parseFloat(order.total));

        trackBarId.textContent = __t('Order #') + order.id;
        trackBarWhat.textContent = SHORT[order.status] || '';
        trackBar.hidden = false;

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

        tracking.querySelectorAll('.track li').forEach(function (li, index) {
            li.classList.toggle('is-done', reached > index || order.status === 'completed');
            li.classList.toggle('is-now', reached === index);
        });
    }

    document.getElementById('open-tracking').addEventListener('click', function () {
        openSheet(tracking);
        refreshTracked();
    });

    async function refreshTracked() {
        if (!tracked) return;

        try {
            const response = await fetch('/api/orders/' + tracked.id, {
                headers: { 'Accept': 'application/json', 'X-Order-Token': tracked.token }
            });

            if (!response.ok) { forget(); return; }

            const data = await response.json();

            showTracking(data.order);

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
        trackBar.hidden = true;
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

            // The bar appears; the panel opens only when they ask for it.
            showTracking(data.order);
            watch();
        }).catch(function () { /* nothing to show, the menu is still there */ });
    }

    render();
})();
</script>
@endpush
