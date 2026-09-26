{{-- Orders that came in from the website. Rendered on load and by the live refresh. --}}
@if ($orders->count() > 0)

    <div class="orders">

        @foreach ($orders as $order)

            <div class="order-card {{ $order->status === 'pending' ? 'is-new' : '' }}">

                <div class="order-header">
                    <div class="order-head-left">
                        <h2>{{ __('Order #') }}{{ $order->id }}</h2>

                        <span class="order-type type-{{ $order->order_type }}">
                            {{ $order->typeLabel() }}
                        </span>
                    </div>

                    <span class="status {{ $order->status }}">
                        {{ __(ucfirst($order->status)) }}
                    </span>
                </div>

                <div class="info">
                    <p>
                        <strong>{{ __('Customer') }}:</strong>
                        {{ $order->client_name ?: __('No name') }}
                    </p>

                    <p>
                        <strong>{{ __('Phone') }}:</strong>
                        @if ($order->client_phone)
                            <a href="tel:{{ $order->client_phone }}" class="phone-link">{{ $order->client_phone }}</a>
                        @else
                            {{ __('N/A') }}
                        @endif
                    </p>

                    @if ($order->delivery_address)
                        <p>
                            <strong>{{ __('Delivery address') }}:</strong>
                            {{ $order->delivery_address }}
                        </p>
                    @endif

                    <p>
                        <strong>{{ __('Created') }}:</strong>
                        {{ $order->created_at->format('Y-m-d H:i') }}
                        <span class="ago">({{ $order->created_at->diffForHumans() }})</span>
                    </p>

                    <p>
                        <strong>{{ __('Payment') }}:</strong>
                        {{ $order->isPaid() ? __('Paid') : __('Not paid yet') }}
                    </p>
                </div>

                <div class="items">
                    @foreach ($order->items as $item)
                        <div class="item">
                            <span>{{ $item->quantity }} × {{ $item->product_name }}</span>
                            <span>{{ number_format($item->subtotal, 2) }} {{ __('EGP') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="total">
                    <span>{{ __('Total') }}</span>
                    <span>{{ number_format($order->total, 2) }} {{ __('EGP') }}</span>
                </div>

                <div class="actions-row">
                    @if ($order->status === 'pending')
                        {{-- Somebody rang the customer back: the order is real. --}}
                        <button type="button" class="confirm-button desk-button"
                                data-order-id="{{ $order->id }}" data-status="confirmed">
                            {{ __('Accept Order') }}
                        </button>

                        <button type="button" class="reject-button desk-button"
                                data-order-id="{{ $order->id }}" data-status="cancelled"
                                data-confirm="{{ __('Cancel order #:id?', ['id' => $order->id]) }}">
                            {{ __('Reject') }}
                        </button>
                    @else
                        <a href="{{ route('kitchen.orders') }}" class="print-button">{{ __('Kitchen') }}</a>

                        @unless ($order->isPaid())
                            <a href="{{ route('cash-payments.index') }}" class="print-button">{{ __('Take payment') }}</a>
                        @endunless
                    @endif
                </div>

            </div>

        @endforeach

    </div>

@else

    <div class="empty">
        <h2>{{ __('Nothing new online') }}</h2>
        <p>{{ __('Orders placed on the website land here the moment they arrive.') }}</p>
    </div>

@endif
