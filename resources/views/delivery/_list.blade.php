@forelse ($orders as $order)
    @php
        $stages = ['waiting' => __('Waiting'), 'out' => __('On the way'), 'delivered' => __('Delivered')];
        $stage = $order->delivery_status ?: 'waiting';
    @endphp

    <article class="delivery-card card">
        <header class="delivery-card__head">
            <div>
                <span class="token">#{{ $order->id }}</span>
                <strong class="delivery-card__name">{{ $order->client_name ?: __('No name') }}</strong>
            </div>

            <span class="status {{ $stage === 'out' ? 'preparing' : ($stage === 'delivered' ? 'completed' : 'pending') }}">
                {{ $stages[$stage] }}
            </span>
        </header>

        <div class="delivery-card__body">
            <p class="delivery-address">{{ $order->delivery_address }}</p>

            <p class="muted-text">
                @if ($order->client_phone)
                    <a href="tel:{{ $order->client_phone }}">{{ $order->client_phone }}</a> &middot;
                @endif
                {{ $order->items->sum('quantity') }} {{ __('Items') }} &middot;
                <strong>{{ number_format((float) $order->total, 2) }} {{ __('EGP') }}</strong>
                @if ((float) $order->delivery_fee > 0)
                    <small>({{ __('Delivery fee') }} {{ number_format((float) $order->delivery_fee, 2) }})</small>
                @endif
            </p>

            <p class="muted-text">
                {{ __('Kitchen') }}: <span class="status {{ $order->status }}">{{ __(ucfirst($order->status)) }}</span>
                &middot;
                {{ __('Payment Status') }}: <span class="status {{ $order->payment_status }}">{{ __(ucfirst($order->payment_status)) }}</span>
            </p>
        </div>

        <form method="POST" action="{{ route('delivery.update', $order) }}" class="delivery-form">
            @csrf
            @method('PATCH')

            <input type="text" name="driver_name" value="{{ $order->driver_name }}"
                   placeholder="{{ __('Driver') }}" maxlength="100">

            <select name="delivery_status">
                @foreach ($stages as $value => $label)
                    <option value="{{ $value }}" @selected($stage === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn">{{ __('Save') }}</button>
        </form>
    </article>
@empty
    <div class="empty">
        <div class="empty-icon">🛵</div>
        <h3>{{ __('Nothing out for delivery') }}</h3>
        <p>{{ __('Delivery orders taken at the counter show up here.') }}</p>
    </div>
@endforelse
