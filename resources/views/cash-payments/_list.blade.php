{{-- Orders waiting for staff to confirm payment. Rendered on load and by the live refresh. --}}
@if ($orders->count() > 0)

    <div class="orders">

        @foreach ($orders as $order)

            <div class="order-card">

                <div class="order-header">
                    <h2>{{ __('Order #') }}{{ $order->id }}</h2>
                    <span class="actions">
                        <span class="payment-method">
                            {{ $order->payment_method === 'card' ? __('Visa') : __('Cash') }}
                        </span>
                        <span class="status {{ $order->status }}">
                            {{ __(ucfirst($order->status)) }}
                        </span>
                    </span>
                </div>

                <div class="info">
                    <p>
                        <strong>{{ __('Table') }}:</strong>
                        {{ $order->table?->number ?? __('N/A') }}
                    </p>

                    @if ($order->client_name)
                        <p><strong>{{ __('Client') }}:</strong> {{ $order->client_name }}</p>
                    @endif

                    @if ($order->client_phone)
                        <p><strong>{{ __('Phone') }}:</strong> {{ $order->client_phone }}</p>
                    @endif

                    <p>
                        <strong>{{ __('Created') }}:</strong>
                        {{ $order->created_at->format('Y-m-d H:i') }}
                    </p>
                </div>

                <div class="items">
                    @foreach ($order->items as $item)
                        <div class="item">
                            <span>
                                {{ $item->quantity }} × {{ $item->product_name }}
                            </span>
                            <span>{{ number_format($item->subtotal, 2) }} {{ __('EGP') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="total">
                    <span>{{ __('Total') }}</span>
                    <span>{{ number_format($order->total, 2) }} {{ __('EGP') }}</span>
                </div>

                        <div class="actions-row">

                    <a
                        href="{{ route('orders.invoice', $order) }}"
                        target="_blank"
                        class="print-button"
                    >
                        {{ __('Print Invoice') }}
                    </a>

                    <form method="POST" action="{{ route('cash-payments.confirm', $order) }}">
                        @csrf
                        <button type="submit" class="confirm-button">
                            {{ __('Confirm Payment') }}
                        </button>
                    </form>

                </div>

            </div>

        @endforeach

    </div>

@else

    <div class="empty">
        <h2>{{ __('No payments waiting') }}</h2>
        <p>{{ __('Every order has been paid.') }}</p>
    </div>

@endif
