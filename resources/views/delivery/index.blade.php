@extends('layouts.app')

@section('title', __('Delivery'))

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ __('Delivery') }}</span>
            <h1>{{ __('Out for Delivery') }}</h1>
            <p>{{ __('Who is taking each order, and how far along it is.') }}</p>
        </div>

        <a href="{{ route('counter.create') }}" class="btn">{{ __('Take an Order') }}</a>
    </div>

    @if (session('success'))<div class="success-message">{{ session('success') }}</div>@endif

    <div id="live-delivery" data-live-url="{{ route('delivery.board') }}" class="delivery-grid">
        @include('delivery._list', ['orders' => $orders])
    </div>

    @if ($delivered->isNotEmpty())
        <h2 class="section-title">{{ __('Delivered recently') }}</h2>
        <div class="table-wrapper">
            <table>
                <thead><tr>
                    <th>{{ __('Order') }}</th><th>{{ __('Client') }}</th><th>{{ __('Driver') }}</th>
                    <th>{{ __('Total') }}</th><th>{{ __('Payment Status') }}</th>
                </tr></thead>
                <tbody>
                @foreach ($delivered as $order)
                    <tr>
                        <td><span class="token">#{{ $order->id }}</span></td>
                        <td>{{ $order->client_name ?: '—' }}</td>
                        <td>{{ $order->driver_name ?: '—' }}</td>
                        <td class="total">{{ number_format((float) $order->total, 2) }} {{ __('EGP') }}</td>
                        <td><span class="status {{ $order->payment_status }}">{{ __(ucfirst($order->payment_status)) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection

@push('styles')
<style>
    .delivery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 18px; align-items: start; }
    .delivery-card { padding: 18px; }
    .delivery-card__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .delivery-card__name { margin-inline-start: 8px; font-size: 15px; }
    .delivery-address { margin: 0 0 8px; line-height: 1.5; }
    .delivery-card__body p { margin: 0 0 6px; font-size: 13.5px; }
    .delivery-form { display: flex; gap: 8px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line); }
    .delivery-form input { flex: 1; min-width: 0; }
    .delivery-form select { width: auto; }
</style>
@endpush
