@extends('layouts.app')

@section('title', __('Cash Payments'))

@section('content')

    <div class="header">
        <div>
            <h1>{{ __('Payments') }}</h1>
            <p>{{ __('Confirm each payment once the money is actually received — cash in the drawer or a card on the terminal.') }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="error-message">
            {{ session('error') }}
        </div>
    @endif

    @unless ($shiftOpen)
        <div class="alert">
            {{ __('No shift is open. Open a shift before taking payments, so the money is counted in the right drawer.') }}
            <a href="{{ route('shifts.current') }}">{{ __('Open New Shift') }}</a>
        </div>
    @endunless

    <div id="live-payments" data-live-url="{{ route('cash-payments.board') }}">
        @include('cash-payments._list')
    </div>

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