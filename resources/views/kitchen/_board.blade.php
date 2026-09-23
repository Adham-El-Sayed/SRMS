{{-- Active kitchen orders. Rendered on load and by the live refresh. --}}
@if($orders->count() > 0)

    <div class="orders">

        @foreach($orders as $order)

            <div class="order-card">

                {{-- Order Header --}}
                <div class="order-header">

                    <h2>
                        {{ __('Order #') }}{{ $order->id }}
                    </h2>

                    <span class="status {{ $order->status }}">
                        {{ __(ucfirst($order->status)) }}
                    </span>

                </div>


                {{-- Order Information --}}
                <div class="info">

                    <p>
                        <strong>{{ __('Table') }}:</strong>

                        @if($order->table)
                            {{ $order->table->number }}
                        @else
                            {{ __('N/A') }}
                        @endif
                    </p>

                    <p>
                        <strong>{{ __('Capacity') }}:</strong>

                        @if($order->table)
                            {{ $order->table->capacity }}
                        @else
                            {{ __('N/A') }}
                        @endif
                    </p>

                    <p>

                        <strong>{{ __('Payment Method') }}:</strong>

                        {{ $order->payment_method === 'card' ? __('Visa') : __('Cash') }}

                    </p>


                    <p>
                        <strong>{{ __('Created') }}:</strong>
                        {{ $order->created_at->format('Y-m-d H:i') }}
                    </p>

                </div>


                {{-- Order Items --}}
                <div class="items">

                    <h3>{{ __('Items') }}</h3>

                    @forelse($order->items as $item)

                        <div class="item">

                            <div>

                                <div class="item-name">
                                    {{ $item->product_name }}
                                </div>

                                <div class="item-details">
                                    {{ __('Quantity') }}: {{ $item->quantity }}
                                    ×
                                    {{ number_format($item->unit_price, 2) }} {{ __('EGP') }}
                                </div>

                                @if($item->notes)

                                    <div class="item-notes">
                                        📝 {{ $item->notes }}
                                    </div>

                                @endif

                            </div>


                            <div class="item-price">

                                {{ number_format($item->subtotal, 2) }}
                                {{ __('EGP') }}

                            </div>

                        </div>

                    @empty

                        <p class="no-items">
                            {{ __('No items in this order.') }}
                        </p>

                    @endforelse

                </div>


                {{-- Total --}}
                <div class="total">

                    <span>{{ __('Total') }}</span>

                    <span>
                        {{ number_format($order->total, 2) }} {{ __('EGP') }}
                    </span>

                </div>


                {{-- Status Actions --}}
                <div class="actions">

                    @if($order->status === 'pending')

                        <button
                            type="button"
                            class="status-button"
                            data-order-id="{{ $order->id }}"
                            data-status="confirmed"
                        >
                            {{ __('Confirm Order') }}
                        </button>

                    @elseif($order->status === 'confirmed')

                        <button
                            type="button"
                            class="status-button"
                            data-order-id="{{ $order->id }}"
                            data-status="preparing"
                        >
                            {{ __('Start Preparing') }}
                        </button>

                    @elseif($order->status === 'preparing')

                        <button
                            type="button"
                            class="status-button"
                            data-order-id="{{ $order->id }}"
                            data-status="ready"
                        >
                            {{ __('Mark as Ready') }}
                        </button>

                    @elseif($order->status === 'ready')

                        <button
                            type="button"
                            class="status-button complete-button"
                            data-order-id="{{ $order->id }}"
                            data-status="completed"
                        >
                            {{ __('Complete Order') }}
                        </button>

                    @endif

                    @if(in_array($order->status, ['pending', 'confirmed', 'preparing'], true))
                        <button
                            type="button"
                            class="status-button cancel-order-button"
                            data-order-id="{{ $order->id }}"
                            data-status="cancelled"
                            data-confirm="{{ __('Cancel order #:id?', ['id' => $order->id]) }}"
                        >
                            {{ __('Cancel') }}
                        </button>
                    @endif

                </div>

            </div>

        @endforeach

    </div>

@else

    <div class="empty">

        <h2>{{ __('No Active Orders') }}</h2>

        <p>
            {{ __('There are currently no orders in the kitchen.') }}
        </p>

    </div>

@endif
