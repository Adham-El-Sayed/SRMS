@php
    // What is already on the ticket, handed to the script below.
    $initialLines = $order->items->map(fn ($item) => [
        'id' => $item->product_id,
        'name' => $item->product_name,
        'price' => (float) $item->unit_price,
        'qty' => $item->quantity,
    ])->values();
@endphp

@extends('layouts.app')

@section('title', __('Edit Order'))

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ $order->typeLabel() }}</span>
            <h1>{{ __('Order #') }}{{ $order->id }}</h1>
            <p>
                @if ($order->table)
                    {{ __('Table') }} {{ $order->table->number }} &middot;
                @elseif ($order->client_name)
                    {{ $order->client_name }} &middot;
                @endif
                {{ $order->created_at->format('Y-m-d H:i') }}
            </p>
        </div>

        <a href="{{ route('cash-payments.index') }}" class="btn">{{ __('Back to Payments') }}</a>
    </div>

    @if (session('error'))<div class="error-message">{{ session('error') }}</div>@endif

    @if ($locked)
        <div class="alert">
            {{ $order->isPaid()
                ? __('This order is already paid and cannot be changed.')
                : __('This order is already closed.') }}
        </div>
    @else

    <form method="POST" action="{{ route('orders.update', $order) }}" id="edit-form" class="counter">
        @csrf
        @method('PATCH')

        <div class="counter__menu">
            @foreach ($menu as $category)
                <section>
                    <h2 class="section-title">{{ $category->name }}</h2>
                    <div class="counter-items">
                        @foreach ($category->products as $product)
                            <button type="button" class="counter-item"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}">
                                <span class="counter-item__name">{{ $product->name }}</span>
                                <span class="counter-item__price">{{ number_format($product->price, 2) }} <small>{{ __('EGP') }}</small></span>
                            </button>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <aside class="counter__ticket card">
            <h2 class="ticket-title">{{ __('Items on this order') }}</h2>

            <div id="ticket-lines" class="ticket-lines"></div>

            <div class="ticket-total">
                <span>{{ __('Total') }}</span>
                <strong id="ticket-total">0.00 {{ __('EGP') }}</strong>
            </div>

            @if ((float) $order->delivery_fee > 0)
                <p class="muted-text" style="margin:-8px 0 14px">
                    {{ __('Delivery fee') }}: {{ number_format((float) $order->delivery_fee, 2) }} {{ __('EGP') }}
                </p>
            @endif

            <button type="submit" class="primary-btn ticket-submit">{{ __('Save Changes') }}</button>
        </aside>
    </form>

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
    .counter-item:hover { border-color: var(--accent); background: #FFFDFB; }
    .counter-item__name { font-weight: 600; font-size: 14.5px; color: var(--ink); }
    .counter-item__price { color: var(--accent); font-weight: 600; font-size: 13.5px; }
    .counter__ticket { position: sticky; top: 76px; padding: 20px; }
    .ticket-title { font-size: 18px; margin: 0 0 12px; }
    .ticket-lines { border-top: 1px solid var(--line); }
    .ticket-line { display: flex; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid var(--line); }
    .ticket-line__name { flex: 1; font-size: 14px; min-width: 0; }
    .ticket-line__price { font-variant-numeric: tabular-nums; font-size: 13.5px; color: var(--muted); }
    .ticket-total { display: flex; justify-content: space-between; align-items: center; padding: 14px 0 18px; font-family: var(--font-display); font-size: 18px; }
    .ticket-submit { width: 100%; padding: 13px; }
    @media (max-width: 900px) { .counter { grid-template-columns: 1fr; } .counter__ticket { position: static; } }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('edit-form');
    if (!form) return;

    var lines = @json($initialLines);

    var box = document.getElementById('ticket-lines');
    var totalEl = document.getElementById('ticket-total');
    var fee = {{ (float) $order->delivery_fee }};
    var currency = @json(__('EGP'));
    var emptyText = @json(__('Tap items from the menu to add them.'));

    function money(n) { return n.toFixed(2) + ' ' + currency; }

    function render() {
        box.innerHTML = lines.length
            ? lines.map(function (l, i) {
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
              }).join('')
            : '<p class="muted-text" style="padding:18px 0;text-align:center">' + emptyText + '</p>';

        totalEl.textContent = money(lines.reduce(function (s, l) { return s + l.price * l.qty; }, 0) + fee);
    }

    form.querySelectorAll('.counter-item').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = Number(button.dataset.id);
            var found = lines.find(function (l) { return l.id === id; });
            if (found) found.qty += 1;
            else lines.push({ id: id, name: button.dataset.name, price: Number(button.dataset.price), qty: 1 });
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

    form.addEventListener('submit', function (event) {
        if (!lines.length) {
            event.preventDefault();
            alert(@json(__('An order must have at least one item.')));
        }
    });

    render();
})();
</script>
@endpush
