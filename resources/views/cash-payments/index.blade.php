@extends('layouts.app')

@section('title', 'Cash Payments')

@section('content')

    <div class="header">
        <div>
            <h1>Cash Payments</h1>
            <p>Confirm receipt of cash payments for the orders.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    @if ($orders->count() > 0)

        <div class="orders">

            @foreach ($orders as $order)

                <div class="order-card">

                    <div class="order-header">
                        <h2>Order #{{ $order->id }}</h2>
                        <span class="status {{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="info">
                        <p>
                            <strong>Table:</strong>
                            {{ $order->table->number ?? 'N/A' }}
                        </p>

                        @if ($order->client_name)
                            <p><strong>Client:</strong> {{ $order->client_name }}</p>
                        @endif

                        @if ($order->client_phone)
                            <p><strong>Phone:</strong> {{ $order->client_phone }}</p>
                        @endif

                        <p>
                            <strong>Created:</strong>
                            {{ $order->created_at->format('Y-m-d H:i') }}
                        </p>
                    </div>

                    <div class="items">
                        @foreach ($order->items as $item)
                            <div class="item">
                                <span>
                                    {{ $item->quantity }} × {{ $item->product?->name ?? 'Unknown' }}
                                </span>
                                <span>{{ number_format($item->subtotal, 2) }} EGP</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="total">
                        <span>Total</span>
                        <span>{{ number_format($order->total, 2) }} EGP</span>
                    </div>

                            <div class="actions-row">

                        <a
                            href="{{ route('orders.invoice', $order) }}"
                            target="_blank"
                            class="print-button"
                        >
                            Print Invoice
                        </a>

                        <form method="POST" action="{{ route('cash-payments.confirm', $order) }}">
                            @csrf
                            <button type="submit" class="confirm-button">
                                Confirm Payment
                            </button>
                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="empty">
            <h2>No Pending Cash Orders</h2>
            <p>All cash orders have been confirmed.</p>
        </div>

    @endif

@endsection


@push('styles')
<style>

    .actions-row {
        display: flex;
        gap: 10px;
        margin-top: 18px;
    }

    .print-button {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        background: #f3f4f6;
        color: #374151;
        font-weight: bold;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
    }

    .print-button:hover {
        background: #e5e7eb;
    }

    .header {
        margin-bottom: 30px;
    }

    .header h1 {
        margin: 0;
        font-size: 32px;
    }

    .header p {
        margin: 8px 0 0;
        color: #6b7280;
    }

    .success-message {
        background: #dcfce7;
        color: #166534;
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .orders {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 20px;
    }

    .order-card {
        background: white;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .order-header h2 {
        margin: 0;
        font-size: 20px;
    }

    .status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: bold;
        background: #f3f4f6;
        color: #4b5563;
    }

    .info p {
        margin: 6px 0;
        color: #4b5563;
        font-size: 14px;
    }

    .items {
        border-top: 1px solid #e5e7eb;
        margin-top: 15px;
        padding-top: 15px;
    }

    .item {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        padding: 6px 0;
        color: #374151;
    }

    .total {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 2px solid #e5e7eb;
        font-weight: bold;
        font-size: 17px;
    }

        .confirm-button {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 10px;
        background: #16a34a;
        color: white;
        font-weight: bold;
        font-size: 15px;
        cursor: pointer;
    }

    .confirm-button:hover {
        background: #15803d;
    }

    .empty {
        background: white;
        padding: 50px;
        border-radius: 14px;
        text-align: center;
        color: #6b7280;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

</style>
@endpush