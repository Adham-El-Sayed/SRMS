@extends('layouts.app')

@section('title', __('Counter'))

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ __('Counter') }}</span>
            <h1>{{ __('Take an Order') }}</h1>
            <p>{{ __('For a guest collecting their food, a delivery, or an order that came in from outside.') }}</p>
        </div>
    </div>

    @if (session('success'))<div class="success-message">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="error-message">{{ session('error') }}</div>@endif

    @if ($errors->any())
        <div class="error-message">
            <div>
                <strong>{{ __('Please check the following') }}</strong>
                <ul style="margin:6px 0 0; padding-inline-start:18px">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (! $shiftOpen)
        <div class="alert">
            {{ __('No shift is open. Open a shift before taking orders, so the money is counted in the right drawer.') }}
            <a href="{{ route('shifts.current') }}">{{ __('Open New Shift') }}</a>
        </div>
    @endif

    @if (empty($types))
        <div class="empty">
            <div class="empty-icon">🛎</div>
            <h3>{{ __('Only dine-in is switched on') }}</h3>
            <p>{{ __('Turn on takeaway, delivery or online in Restaurant Settings to take orders here.') }}</p>
        </div>
    @else

    <form method="POST" action="{{ route('counter.store') }}" id="counter-form" class="counter">
        @csrf

        {{-- Menu --}}
        <div class="counter__menu">
            @forelse ($menu as $category)
                <section class="counter-category">
                    <h2 class="section-title">{{ $category->name }}</h2>

                    <div class="counter-items">
                        @foreach ($category->products as $product)
                            <button type="button" class="counter-item {{ $product->isSoldOut() ? 'is-out' : '' }}"
                                    @disabled($product->isSoldOut())
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}">
                                <span class="counter-item__name">{{ $product->name }}</span>
                                <span class="counter-item__price">
                                    @if ($product->isSoldOut())
                                        {{ __('Finished') }}
                                    @else
                                        {{ number_format($product->price, 2) }} <small>{{ __('EGP') }}</small>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="empty"><p>{{ __('There are currently no menu items available.') }}</p></div>
            @endforelse
        </div>

        {{-- Ticket --}}
        <aside class="counter__ticket card">
            <h2 class="ticket-title">{{ __('This Order') }}</h2>

            <div class="form-group">
                <label for="order_type">{{ __('Order type') }}</label>
                <select name="order_type" id="order_type" required>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(old('order_type') === $type)>{{ \App\Support\Settings::typeLabel($type) }}</option>
                    @endforeach
                </select>
            </div>

            <div id="ticket-lines" class="ticket-lines">
                <p class="muted-text ticket-empty">{{ __('Tap items from the menu to add them.') }}</p>
            </div>

            <div class="ticket-total">
                <span>{{ __('Total') }}</span>
                <strong id="ticket-total">0.00 {{ __('EGP') }}</strong>
            </div>

            <div class="form-group">
                <label for="client_name">{{ __('Customer name') }}</label>
                <input type="text" id="client_name" name="client_name" value="{{ old('client_name') }}" maxlength="100">
            </div>

            <div class="form-group" id="phone-group">
                <label for="client_phone">{{ __('Phone') }}</label>
                <input type="tel" id="client_phone" name="client_phone" value="{{ old('client_phone') }}" maxlength="20">
            </div>

            <div class="form-group" id="address-group" hidden>
                <label for="delivery_address">{{ __('Delivery address') }}</label>
                <textarea id="delivery_address" name="delivery_address" rows="2" maxlength="500">{{ old('delivery_address') }}</textarea>
                <small class="muted-text">{{ __('Delivery fee') }}: {{ number_format($deliveryFee, 2) }} {{ __('EGP') }}</small>
            </div>

            <div class="form-group">
                <label>{{ __('Payment Method') }}</label>
                <div class="pay-choice">
                    <label><input type="radio" name="payment_method" value="cash" checked> {{ __('Cash') }}</label>
                    <label><input type="radio" name="payment_method" value="card"> {{ __('Visa') }}</label>
                </div>
            </div>

            <button type="submit" class="primary-btn ticket-submit" id="ticket-submit" @disabled(! $shiftOpen)>
                {{ __('Place Order') }}
            </button>
        </aside>
    </form>

    @if ($recent->isNotEmpty())
        <h2 class="section-title">{{ __('Latest counter orders') }}</h2>
        <div class="table-wrapper">
            <table>
                <thead><tr>
                    <th>{{ __('Order') }}</th><th>{{ __('Type') }}</th><th>{{ __('Client') }}</th>
                    <th>{{ __('Total') }}</th><th>{{ __('Status') }}</th><th>{{ __('Payment Status') }}</th>
                </tr></thead>
                <tbody>
                @foreach ($recent as $order)
                    <tr>
                        <td><span class="token">#{{ $order->id }}</span></td>
                        <td><span class="badge type-{{ $order->order_type }}">{{ $order->typeLabel() }}</span></td>
                        <td>{{ $order->client_name ?: '—' }}</td>
                        <td class="total">{{ number_format((float) $order->total, 2) }} {{ __('EGP') }}</td>
                        <td><span class="status {{ $order->status }}">{{ __(ucfirst($order->status)) }}</span></td>
                        <td><span class="status {{ $order->payment_status }}">{{ __(ucfirst($order->payment_status)) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @endif

@endsection

@push('styles')
<style>
    .counter { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
    .counter-items { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 10px; }
    .counter-item {
        display: flex; flex-direction: column; gap: 6px; text-align: start;
        padding: 13px 14px; border-radius: var(--r-sm);
        border: 1px solid var(--line-strong); background: var(--surface);
        cursor: pointer; font-family: inherit;
    }
    .counter-item:hover:not(:disabled) { border-color: var(--accent); background: #FFFDFB; }
    .counter-item.is-out { opacity: .55; cursor: not-allowed; }
    .counter-item.is-out .counter-item__price { color: var(--danger); }
    .counter-item__name { font-weight: 600; font-size: 14.5px; color: var(--ink); }
    .counter-item__price { color: var(--accent); font-weight: 600; font-size: 13.5px; }
    .counter__ticket { position: sticky; top: 76px; padding: 20px; }
    .ticket-title { font-size: 18px; margin: 0 0 16px; }
    .ticket-lines { border-top: 1px solid var(--line); margin: 6px 0 0; }
    .ticket-empty { padding: 18px 0; text-align: center; font-size: 13.5px; }
    .ticket-line { display: flex; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid var(--line); }
    .ticket-line__name { flex: 1; font-size: 14px; min-width: 0; }
    .ticket-line__price { font-variant-numeric: tabular-nums; font-size: 13.5px; color: var(--muted); }
    .ticket-total { display: flex; justify-content: space-between; align-items: center; padding: 14px 0 18px; font-family: var(--font-display); font-size: 18px; }
    .pay-choice { display: flex; gap: 14px; }
    .pay-choice label { display: inline-flex; align-items: center; gap: 6px; font-weight: 500; cursor: pointer; margin: 0; }
    .ticket-submit { width: 100%; padding: 13px; }
    .badge.type-delivery { background: var(--info-soft); color: var(--info); border-color: rgba(59,99,130,.22); }
    .badge.type-takeaway { background: var(--amber-soft); color: var(--warn); border-color: rgba(176,123,20,.22); }
    .badge.type-online   { background: var(--olive-soft); color: var(--olive); border-color: rgba(94,110,76,.25); }
    @media (max-width: 900px) {
        .counter { grid-template-columns: 1fr; }
        .counter__ticket { position: static; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('counter-form');
    if (!form) return;

    var lines = [];
    var box = document.getElementById('ticket-lines');
    var totalEl = document.getElementById('ticket-total');
    var typeEl = document.getElementById('order_type');
    var addressGroup = document.getElementById('address-group');
    var deliveryFee = {{ (float) $deliveryFee }};
    var currency = @json(__('EGP'));

    function money(n) { return n.toFixed(2) + ' ' + currency; }

    function render() {
        if (!lines.length) {
            box.innerHTML = '<p class="muted-text ticket-empty">' + @json(__('Tap items from the menu to add them.')) + '</p>';
        } else {
            box.innerHTML = lines.map(function (l, i) {
                return '<div class="ticket-line">' +
                    '<span class="ticket-line__name">' + l.name + '</span>' +
                    '<span class="quantity-controls">' +
                      '<button type="button" class="quantity-button" data-act="less" data-i="' + i + '">−</button>' +
                      '<span class="quantity">' + l.qty + '</span>' +
                      '<button type="button" class="quantity-button" data-act="more" data-i="' + i + '">+</button>' +
                    '</span>' +
                    '<span class="ticket-line__price">' + money(l.price * l.qty) + '</span>' +
                    '<input type="hidden" name="items[' + i + '][product_id]" value="' + l.id + '">' +
                    '<input type="hidden" name="items[' + i + '][quantity]" value="' + l.qty + '">' +
                    '</div>';
            }).join('');
        }

        var total = lines.reduce(function (sum, l) { return sum + l.price * l.qty; }, 0);
        if (typeEl.value === 'delivery') total += deliveryFee;
        totalEl.textContent = money(total);
    }

    form.querySelectorAll('.counter-item').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = Number(button.dataset.id);
            var found = lines.find(function (l) { return l.id === id; });
            if (found) { found.qty += 1; }
            else { lines.push({ id: id, name: button.dataset.name, price: Number(button.dataset.price), qty: 1 }); }
            render();
        });
    });

    box.addEventListener('click', function (event) {
        var button = event.target.closest('button[data-act]');
        if (!button) return;
        var i = Number(button.dataset.i);
        if (button.dataset.act === 'more') lines[i].qty += 1;
        else if (--lines[i].qty <= 0) lines.splice(i, 1);
        render();
    });

    function syncType() {
        var isDelivery = typeEl.value === 'delivery';
        addressGroup.hidden = !isDelivery;
        document.getElementById('delivery_address').required = isDelivery;
        document.getElementById('client_phone').required = isDelivery || typeEl.value === 'online';
        render();
    }

    typeEl.addEventListener('change', syncType);
    syncType();

    form.addEventListener('submit', function (event) {
        if (!lines.length) {
            event.preventDefault();
            alert(@json(__('Add at least one item to the order.')));
        }
    });
})();
</script>
@endpush
