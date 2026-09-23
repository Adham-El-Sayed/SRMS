@extends('layouts.menu')

@section('title', __('Restaurant Menu'))

@section('content')

<div class="menu-app" id="menu-app"
     data-table-id="{{ isset($table) ? $table->id : '' }}"
     data-table-token="{{ isset($table) ? $table->qr_token : '' }}">

    {{-- ===== Welcome ===== --}}
    <header class="menu-hero">
        <p class="menu-hero__kicker">{{ __('Restaurant Menu') }}</p>
        <h1 class="menu-hero__title">{{ __('What would you like today?') }}</h1>

        @isset($table)
            <p class="menu-hero__note">
                {{ __('You are at table :number. Order from your phone and the kitchen starts right away.', ['number' => $table->number]) }}
            </p>
        @else
            <p class="menu-hero__note">{{ __('Have a look at what we serve.') }}</p>
        @endisset
    </header>

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
                    <article class="dish">
                        <div class="dish__image">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
                            @else
                                <div class="dish__image-placeholder">🍽</div>
                            @endif
                        </div>

                        <div class="dish__body">
                            <h3 class="dish__name">{{ $product->name }}</h3>
                            <p class="dish__note">{{ $product->description ?? '' }}</p>

                            <div class="dish__foot">
                                <span class="dish__price">{{ number_format($product->price, 2) }} <small>{{ __('EGP') }}</small></span>

                                @isset($table)
                                    <button type="button" class="add-button"
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-price="{{ $product->price }}"
                                            aria-label="{{ __('Add') }} {{ $product->name }}">
                                        {{ __('Add') }}
                                    </button>
                                @endisset
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

@isset($table)

    {{-- ===== The bar that follows you down the page ===== --}}
    <div class="cart-bar" id="cart-bar" hidden>
        <button type="button" class="cart-bar__button" id="open-order">
            <span class="cart-bar__count" id="cart-bar-count">0</span>
            <span class="cart-bar__label">{{ __('View your order') }}</span>
            <span class="cart-bar__total" id="cart-bar-total">0.00 {{ __('EGP') }}</span>
        </button>
    </div>

    {{-- ===== Your order ===== --}}
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
            </div>

            <div class="sheet__foot">
                <div class="order-total">
                    <span>{{ __('Total') }}</span>
                    <span id="order-total">0.00 {{ __('EGP') }}</span>
                </div>

                <div class="who">
                    <input type="text" id="client-name" placeholder="{{ __('Name (optional)') }}" maxlength="100">
                    <input type="tel" id="client-phone" placeholder="{{ __('Phone Number (optional)') }}" maxlength="20">
                </div>

                <div class="pay-choice">
                    <label><input type="radio" name="payment_method" value="cash" checked> {{ __('Cash') }}</label>
                    <label><input type="radio" name="payment_method" value="card"> {{ __('Visa') }}</label>
                </div>

                <button type="button" id="submit-order" class="submit-button" disabled>{{ __('Submit Order') }}</button>

                <div id="message"></div>
            </div>
        </div>
    </div>

    {{-- ===== After it is sent: the edit window ===== --}}
    <div class="sheet" id="submitted-order-panel" style="display:none;">
        <div class="sheet__box" role="dialog" aria-modal="true">

            <div class="sheet__head">
                <div>
                    <h2>{{ __('Order #') }}<span id="submitted-order-id"></span></h2>
                    <p class="muted-text" id="edit-window-status">
                        {{ __('You can still edit this order for') }}
                        <strong><span id="edit-timer">2:30</span></strong>
                    </p>
                </div>

                <button type="button" class="sheet__close" data-close-sheet aria-label="{{ __('Close') }}">&times;</button>
            </div>

            <div class="sheet__body">
                <div id="submitted-order-items"></div>
            </div>

            <div class="sheet__foot">
                <div class="order-total">
                    <span>{{ __('Total') }}</span>
                    <span id="submitted-order-total">0.00 {{ __('EGP') }}</span>
                </div>

                <button type="button" id="save-changes-button" class="submit-button">{{ __('Save Changes') }}</button>
                <button type="button" id="request-help-button" class="submit-button call-waiter" style="display:none;">{{ __('Request Waiter') }}</button>

                <div id="submitted-message"></div>
            </div>
        </div>
    </div>

@endisset

    ========================== --}}

    @if(isset($table))

        <div id="payment-modal-overlay" class="payment-modal-overlay" style="display:none;">

            <div class="payment-modal">

                <h2>{{ __('Pay with Visa') }}</h2>

                <p class="payment-modal-amount">
                    {{ __('Amount') }}: <strong id="payment-modal-amount">0.00 {{ __('EGP') }}</strong>
                </p>

                <button type="button" id="scan-card-button" class="scan-card-button">
                    📷 {{ __('Scan Card with Camera') }}
                </button>

                <div id="camera-scan-container" class="camera-scan-container" style="display:none;">
                    <video id="camera-video" autoplay playsinline></video>
                    <div class="camera-scan-frame"></div>
                    <p id="camera-scan-status">{{ __('Point your camera at the card...') }}</p>
                    <button type="button" id="cancel-scan-button" class="cancel-payment-button">
                        {{ __('Cancel Scan') }}
                    </button>
                </div>

                <div class="payment-field">
                    <label>{{ __('Card Number') }}</label>
                    <input type="text" id="card-number" placeholder="4242 4242 4242 4242" maxlength="19">
                </div>

                <div class="payment-field-row">

                    <div class="payment-field">
                        <label>{{ __('Expiry') }}</label>
                        <input type="text" id="card-expiry" placeholder="{{ __('MM/YY') }}" maxlength="5">
                    </div>

                    <div class="payment-field">
                        <label>{{ __('CVV') }}</label>
                        <input type="text" id="card-cvv" placeholder="123" maxlength="3">
                    </div>

                </div>

                <div class="payment-field">
                    <label>{{ __('Cardholder Name') }}</label>
                    <input type="text" id="card-name" placeholder="{{ __('Name on card') }}">
                </div>

                <div id="payment-modal-error" class="payment-modal-error"></div>

                <button type="button" id="pay-now-button" class="submit-button">
                    {{ __('Pay Now') }}
                </button>

                <button type="button" id="cancel-payment-button" class="cancel-payment-button">
                    {{ __('Cancel') }}
                </button>

            </div>

        </div>

    @endif


@endsection

@push('styles')
<style>
    /* ==================================================================
       The guest menu. Read on a phone, at a table, usually one-handed:
       generous tap targets, the order always one thumb away, and nothing
       between the food and the guest.
       ================================================================== */

    .menu-app { padding-bottom: 96px; }

    /* ---------- welcome ---------- */

    .menu-hero { padding: 28px 0 22px; }

    .menu-hero__kicker {
        margin: 0 0 8px;
        font-size: 11.5px; font-weight: 700;
        letter-spacing: .14em; text-transform: uppercase;
        color: var(--accent);
    }

    html[lang="ar"] .menu-hero__kicker { letter-spacing: 0; text-transform: none; font-size: 13px; }

    .menu-hero__title {
        margin: 0 0 10px;
        font-size: clamp(28px, 6vw, 40px);
        line-height: 1.12;
    }

    .menu-hero__note { margin: 0; color: var(--muted); font-size: 15px; max-width: 46ch; line-height: 1.6; }

    /* ---------- section jump ---------- */

    .menu-tabs {
        position: sticky; top: 0; z-index: 30;
        display: flex; gap: 8px;
        margin: 0 -16px 26px; padding: 12px 16px;
        overflow-x: auto; scrollbar-width: none;
        background: rgba(250, 246, 240, .94);
        backdrop-filter: saturate(1.6) blur(10px);
        -webkit-backdrop-filter: saturate(1.6) blur(10px);
        border-bottom: 1px solid var(--line);
    }

    .menu-tabs::-webkit-scrollbar { display: none; }

    .menu-tab {
        padding: 8px 15px; border-radius: 100px;
        background: var(--surface); border: 1px solid var(--line-strong);
        color: var(--ink-soft); text-decoration: none;
        font-size: 13.5px; font-weight: 600; white-space: nowrap;
        transition: background-color .16s ease, color .16s ease, border-color .16s ease;
    }

    .menu-tab:hover { border-color: var(--accent); color: var(--accent-dark); }

    /* ---------- a course ---------- */

    .menu-section { margin-bottom: 44px; scroll-margin-top: 72px; }

    .menu-section__head {
        display: flex; align-items: center; gap: 14px;
        margin-bottom: 18px; padding-bottom: 14px;
        border-bottom: 1px solid var(--line-strong);
    }

    .menu-section__image {
        width: 54px; height: 54px; flex-shrink: 0;
        object-fit: cover; border-radius: 12px;
        border: 1px solid var(--line); box-shadow: var(--sh-1);
    }

    .menu-section__title { margin: 0; font-size: 22px; }
    .menu-section__note { margin: 3px 0 0; color: var(--muted); font-size: 13.5px; }

    /* ---------- a dish ---------- */

    .dish-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 16px;
    }

    .dish {
        display: flex; flex-direction: column;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--r);
        overflow: hidden;
        box-shadow: var(--sh-1);
        transition: box-shadow .18s ease, transform .18s ease, border-color .18s ease;
    }

    .dish:hover { box-shadow: var(--sh-3); border-color: var(--line-strong); transform: translateY(-2px); }

    .dish__image { aspect-ratio: 4 / 3; background: var(--surface-sunk); overflow: hidden; }
    .dish__image img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s cubic-bezier(.2,.7,.3,1); }
    .dish:hover .dish__image img { transform: scale(1.04); }

    .dish__image-placeholder {
        width: 100%; height: 100%;
        display: grid; place-items: center;
        font-size: 30px; color: var(--muted);
        background-image: repeating-linear-gradient(45deg, rgba(138,125,113,.05) 0 8px, transparent 8px 16px);
    }

    .dish__body { display: flex; flex-direction: column; flex: 1; padding: 14px 16px 16px; }
    .dish__name { margin: 0 0 4px; font-size: 16.5px; }
    .dish__note { margin: 0 0 14px; color: var(--muted); font-size: 13px; line-height: 1.5; }

    .dish__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: auto; }
    .dish__price { font-family: var(--font-display); font-size: 17px; font-weight: 600; color: var(--accent); }
    .dish__price small { font-size: 11.5px; font-weight: 600; color: var(--muted); }

    .add-button { padding: 9px 16px; font-size: 13.5px; border-radius: 100px; }

    /* ---------- the bar that follows you ---------- */

    .cart-bar {
        position: fixed; inset-inline: 0; bottom: 0; z-index: 60;
        padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
        background: linear-gradient(to top, var(--paper) 62%, rgba(250,246,240,0));
        pointer-events: none;
    }

    .cart-bar[hidden] { display: none; }

    .cart-bar__button {
        pointer-events: auto;
        display: flex; align-items: center; gap: 12px;
        width: min(100%, 560px); margin: 0 auto;
        padding: 14px 18px; border: 0; border-radius: 100px;
        background: var(--accent); color: #fff;
        font-family: inherit; font-size: 15px; font-weight: 600;
        cursor: pointer;
        box-shadow: 0 10px 28px -10px rgba(189, 78, 44, .85);
        transition: background-color .16s ease, transform .08s ease;
    }

    .cart-bar__button:hover { background: var(--accent-dark); }
    .cart-bar__button:active { transform: translateY(1px); }

    .cart-bar__count {
        display: grid; place-items: center;
        min-width: 26px; height: 26px; padding: 0 7px;
        border-radius: 100px; background: rgba(255,255,255,.22);
        font-size: 13px; font-weight: 700;
    }

    .cart-bar__label { flex: 1; text-align: start; }
    .cart-bar__total { font-variant-numeric: tabular-nums; }

    /* ---------- sheets ---------- */

    .sheet {
        position: fixed; inset: 0; z-index: 70;
        background: rgba(36, 29, 24, .5);
        backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);
        display: flex; align-items: flex-end; justify-content: center;
        padding: 0;
    }

    .sheet[hidden] { display: none; }

    .sheet__box {
        display: flex; flex-direction: column;
        width: min(100%, 560px); max-height: 92vh;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: var(--r-lg) var(--r-lg) 0 0;
        box-shadow: 0 -20px 60px -24px rgba(36,29,24,.5);
        animation: sheet-up .22s cubic-bezier(.2,.7,.3,1);
    }

    @keyframes sheet-up { from { transform: translateY(14px); opacity: .6; } to { transform: none; opacity: 1; } }

    .sheet__head {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 20px 20px 14px; border-bottom: 1px solid var(--line);
    }

    .sheet__head h2 { margin: 0; font-size: 20px; }
    .sheet__head .muted-text { margin: 3px 0 0; font-size: 13px; }
    .sheet__head .order-count { margin-inline-start: auto; }

    .sheet__close {
        background: transparent; border: 0; cursor: pointer;
        font-size: 26px; line-height: 1; color: var(--muted);
        padding: 0 4px; margin-inline-start: 4px;
    }

    .sheet__close:hover { color: var(--ink); }

    .sheet__body { flex: 1; overflow-y: auto; padding: 4px 20px; -webkit-overflow-scrolling: touch; }
    .sheet__foot { padding: 14px 20px calc(20px + env(safe-area-inset-bottom)); border-top: 1px solid var(--line); background: #FDFBF8; }

    /* ---------- lines inside a sheet ---------- */

    .order-item {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        padding: 14px 0; border-bottom: 1px solid var(--line);
    }

    .order-item-info { flex: 1; min-width: 45%; }
    .order-item-name { font-weight: 600; font-size: 14.5px; }
    .order-item-price { color: var(--muted); font-size: 12.5px; margin-top: 2px; }
    .item-subtotal { font-family: var(--font-display); font-size: 15px; min-width: 88px; text-align: end; }
    .item-notes { flex-basis: 100%; margin-top: 4px; font-size: 13px; padding: 9px 11px; }

    /* Removing a line is a small, quiet control — not a button competing
       with the order itself. */
    .sheet .remove-button {
        order: 5;
        width: 30px; height: 30px; padding: 0;
        display: inline-grid; place-items: center;
        font-size: 0; line-height: 0;
        border-radius: 50%;
        background: transparent; border-color: transparent;
        color: var(--muted);
    }

    .sheet .remove-button::before { content: "\00d7"; font-size: 19px; line-height: 1; }
    .sheet .remove-button:hover { background: var(--danger-soft); color: var(--danger); border-color: transparent; }

    .order-item .quantity-controls { order: 2; }
    .item-subtotal { order: 3; }

    .empty-order { text-align: center; padding: 36px 10px; color: var(--muted); }
    .empty-order-icon { font-size: 30px; margin-bottom: 8px; }
    .empty-order p { margin: 0 0 4px; color: var(--ink); font-weight: 600; }
    .empty-order span { font-size: 13.5px; }

    .order-total {
        display: flex; align-items: center; justify-content: space-between;
        font-family: var(--font-display); font-size: 19px; font-weight: 600;
        margin-bottom: 14px;
    }

    .who { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
    .who input { width: 100%; }

    .pay-choice { display: flex; gap: 10px; margin-bottom: 14px; }

    .pay-choice label {
        flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 11px 12px; border-radius: var(--r-sm);
        border: 1px solid var(--line-strong); background: var(--surface);
        font-size: 14px; font-weight: 600; cursor: pointer;
        transition: border-color .16s ease, background-color .16s ease;
    }

    .pay-choice label:has(input:checked) { border-color: var(--accent); background: var(--accent-soft); color: var(--accent-dark); }

    .submit-button { width: 100%; padding: 14px; font-size: 15px; }
    .submit-button.call-waiter { background: var(--accent); margin-top: 10px; }

    #message:not(:empty), #submitted-message:not(:empty) { margin-top: 12px; }

    /* ---------- the visa form ---------- */

    .payment-modal-overlay {
        position: fixed; inset: 0; z-index: 80;
        align-items: center; justify-content: center; padding: 18px;
        background: rgba(36, 29, 24, .55);
        backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);
    }

    .payment-modal {
        width: min(100%, 420px); max-height: 92vh; overflow-y: auto;
        background: var(--surface); border-radius: var(--r-lg);
        box-shadow: 0 24px 60px -20px rgba(36,29,24,.45);
        padding: 24px;
    }

    .payment-modal h2 { margin: 0 0 4px; font-size: 21px; }
    .payment-modal-amount { font-family: var(--font-display); font-size: 26px; color: var(--accent); margin-bottom: 16px; }
    .payment-field { margin-bottom: 14px; }
    .payment-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .payment-modal .payment-modal-error { display: none; }
    .payment-modal .payment-modal-error.visible { display: flex; }

    .scan-card-button { width: 100%; margin-bottom: 16px; }
    .camera-scan-container { margin-bottom: 16px; }
    .camera-scan-container video { width: 100%; border-radius: var(--r-sm); display: block; }
    .camera-scan-frame { position: relative; margin-top: -60px; height: 60px; }

    .modal-actions { display: flex; gap: 10px; margin-top: 18px; }
    .modal-actions button { flex: 1; }

    /* ---------- phones ---------- */

    @media (max-width: 620px) {
        .dish-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
        .dish__body { padding: 12px 13px 14px; }
        .dish__name { font-size: 15px; }
        .dish__note { display: none; }
        .dish__foot { flex-direction: column; align-items: stretch; gap: 8px; }
        .add-button { width: 100%; justify-content: center; }
        .menu-section__image { width: 44px; height: 44px; }
        .who { grid-template-columns: 1fr; }
    }

    @media (min-width: 900px) {
        .sheet { align-items: center; padding: 20px; }
        .sheet__box { border-radius: var(--r-lg); max-height: 86vh; }
    }
</style>
@endpush

@push('scripts')

{{-- The cart bar and the two sheets. The ordering logic lives in the script
     below; this only decides what is on screen. --}}
<script>
(function () {
    var app = document.getElementById('menu-app');
    if (!app || !app.dataset.tableId) return;

    var bar = document.getElementById('cart-bar');
    var barCount = document.getElementById('cart-bar-count');
    var barTotal = document.getElementById('cart-bar-total');
    var orderSheet = document.getElementById('order-sheet');
    var submittedSheet = document.getElementById('submitted-order-panel');
    var openButton = document.getElementById('open-order');
    var orderItems = document.getElementById('order-items');
    var orderTotal = document.getElementById('order-total');

    function openSheet(sheet) {
        if (!sheet) return;
        if (sheet === submittedSheet) sheet.style.display = 'flex';
        else sheet.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeSheet(sheet) {
        if (!sheet) return;
        if (sheet === submittedSheet) sheet.style.display = 'none';
        else sheet.hidden = true;
        document.body.style.overflow = '';
    }

    openButton.addEventListener('click', function () {
        // Once an order is in, the bar reopens that order rather than a new one.
        openSheet(submittedSheet.dataset.live === 'yes' ? submittedSheet : orderSheet);
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-close-sheet]')) {
            closeSheet(event.target.closest('.sheet'));
        } else if (event.target === orderSheet || event.target === submittedSheet) {
            closeSheet(event.target);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        closeSheet(orderSheet);
        if (submittedSheet.style.display !== 'none') closeSheet(submittedSheet);
    });

    /* Adding from the menu should feel immediate: the bar updates and the
       button confirms, without the sheet jumping in front of the food. */
    document.querySelectorAll('.add-button').forEach(function (button) {
        button.addEventListener('click', function () {
            var original = button.textContent;
            button.textContent = @json(__('Added'));
            button.classList.add('is-added');
            setTimeout(function () {
                button.textContent = original;
                button.classList.remove('is-added');
            }, 900);
        });
    });

    /* The order panel is rendered by the script below; mirror its numbers
       onto the bar whenever they change. */
    function syncBar() {
        var count = orderItems ? orderItems.querySelectorAll('.order-item').length : 0;
        var quantities = orderItems ? orderItems.querySelectorAll('.order-item .quantity') : [];
        var total = 0;

        for (var i = 0; i < quantities.length; i++) total += Number(quantities[i].textContent) || 0;

        barCount.textContent = total || count;
        barTotal.textContent = orderTotal ? orderTotal.textContent : '';
        bar.hidden = count === 0 && submittedSheet.dataset.live !== 'yes';
    }

    new MutationObserver(syncBar).observe(orderItems, { childList: true, subtree: true, characterData: true });
    new MutationObserver(function () {
        var live = submittedSheet.style.display !== 'none';
        if (live) {
            submittedSheet.dataset.live = 'yes';
            closeSheet(orderSheet);
            openSheet(submittedSheet);
        }
        syncBar();
    }).observe(submittedSheet, { attributes: true, attributeFilter: ['style'] });

    syncBar();
})();
</script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const app =
        document.getElementById('menu-app');


    if (!app) {
        return;
    }


    const tableToken =
        app.dataset.tableToken;


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

    // Secret returned once by the server when the order is placed. Every later
    // request about this order must carry it; it lives only in this page.
    let currentOrderToken = null;
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
                        ? ' ' + __t('Item')
                        : ' ' + __t('Items')
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
                            ${__t('Your order is empty.')}
                        </p>

                        <span>
                            ${__t('Add items from the menu above.')}
                        </span>

                    </div>

                `;

            }


            if (orderTotalElement) {

                orderTotalElement.textContent =
                    '0.00 ' + __t('EGP');

            }


            if (submitButton) {

                submitButton.disabled = true;

                submitButton.textContent =
                    __t('Submit Order');

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
                                ${item.price.toFixed(2)} ${__t('EGP each')}
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
                            ${subtotal.toFixed(2)} ${__t('EGP')}
                        </strong>


                        <button
                            type="button"
                            class="remove-button"
                            data-action="remove"
                            data-index="${index}"
                        >
                            ${__t('Remove')}
                        </button>


                        <input
                            type="text"
                            class="item-notes"
                            placeholder="${__t('Notes (e.g., without tomatoes)')}"
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
                ' ' + __t('EGP');

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


                if (!tableToken) {

                    showMessage(
                        __t('Table information is missing.'),
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
            __t('Submitting...');


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

                                table_token:
                                    tableToken,

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
                    __t('Failed to create order.'),
                    'error'
                );


                submitButton.disabled =
                    false;


                submitButton.textContent =
                    __t('Submit Order');


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
                __t('Order #') +
                data.order.id +
                ' ' + __t('created successfully!'),
                'success'
            );


            submitButton.disabled =
                false;


            submitButton.textContent =
                __t('Submit Order');


            currentOrderToken = data.order_token;

            startEditWindow(data.order);


            return true;


        } catch (error) {

            console.error(error);


            showMessage(
                __t('Something went wrong while submitting the order.'),
                'error'
            );


            submitButton.disabled =
                false;


            submitButton.textContent =
                __t('Submit Order');


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
            total.toFixed(2) + ' ' + __t('EGP');


        cardNumberInput.value = '';

        cardExpiryInput.value = '';

        cardCvvInput.value = '';

        cardNameInput.value = '';


        hidePaymentModalError();


        payNowButton.disabled = false;

        payNowButton.textContent = __t('Pay Now');


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
                        __t('Please enter a valid card number.')
                    );

                    return;

                }


                if (! /^\d{2}\/\d{2}$/.test(expiry)) {

                    showPaymentModalError(
                        __t('Please enter expiry as MM/YY.')
                    );

                    return;

                }


                if (! /^\d{3}$/.test(cvv)) {

                    showPaymentModalError(
                        __t('Please enter a valid 3-digit CVV.')
                    );

                    return;

                }


                if (! name) {

                    showPaymentModalError(
                        __t('Please enter the cardholder name.')
                    );

                    return;

                }


                payNowButton.disabled = true;

                payNowButton.textContent = __t('Processing Payment...');


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

                            payNowButton.textContent = __t('Pay Now');

                            showPaymentModalError(
                                __t('Payment succeeded but the order failed to save. Please contact staff.')
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

                        name: item.product_name || __t('Unknown Product'),

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

        saveChangesButton.textContent = __t('Save Changes');


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
                __t('The edit window has expired.');


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
                                ${item.price.toFixed(2)} ${__t('EGP each')}
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
                            ${subtotal.toFixed(2)} ${__t('EGP')}
                        </strong>


                        <button
                            type="button"
                            class="remove-button"
                            data-sub-action="remove"
                            data-sub-index="${index}"
                        >
                            ${__t('Remove')}
                        </button>


                        <input
                            type="text"
                            class="item-notes"
                            placeholder="${__t('Notes (e.g., without tomatoes)')}"
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
                ' ' + __t('EGP');

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
                        __t('Order must have at least one item.'),
                        'error'
                    );

                    return;

                }


                saveChangesButton.disabled = true;

                saveChangesButton.textContent = __t('Saving...');


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
                                        'application/json',

                                    'X-Order-Token':
                                        currentOrderToken

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
                            __t('Failed to update order.'),
                            'error'
                        );


                        saveChangesButton.disabled = false;

                        saveChangesButton.textContent = __t('Save Changes');


                        return;

                    }


                    showSubmittedMessage(
                        __t('Order updated successfully!'),
                        'success'
                    );


                    saveChangesButton.disabled = false;

                    saveChangesButton.textContent = __t('Save Changes');


                } catch (error) {

                    console.error(error);


                    showSubmittedMessage(
                        __t('Something went wrong while saving changes.'),
                        'error'
                    );


                    saveChangesButton.disabled = false;

                    saveChangesButton.textContent = __t('Save Changes');

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

                requestHelpButton.textContent = __t('Notifying...');


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
                                        'application/json',

                                    'X-Order-Token':
                                        currentOrderToken

                                }

                            }
                        );


                    const data =
                        await response.json();


                    showSubmittedMessage(
                        data.message ||
                        __t('The waiter has been notified.'),
                        'success'
                    );


                    requestHelpButton.textContent =
                        __t('Waiter Notified');


                } catch (error) {

                    console.error(error);


                    showSubmittedMessage(
                        __t('Something went wrong. Please try again.'),
                        'error'
                    );


                    requestHelpButton.disabled = false;

                    requestHelpButton.textContent =
                        __t('Request Waiter');

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
                        __t('Camera is not supported on this device/browser.')
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
                        __t('Point your camera at the card...');


                    setTimeout(
                        function () {

                            cameraScanStatus.textContent =
                                __t('Scanning...');

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
                                        : __t('Card Holder');

                            }

                        },
                        2800
                    );


                } catch (error) {

                    console.error(error);


                    showPaymentModalError(
                        __t('Camera access was denied. Please enter card details manually.')
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
            value == null ? '' : String(value);


        // textContent escapes < > & only; the result is also placed inside
        // value="..." attributes, so quotes must be escaped as well.
        return div.innerHTML
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

    }


    /* =========================
       Initial Render
    ========================== */

    renderOrder();

});

</script>

@endpush