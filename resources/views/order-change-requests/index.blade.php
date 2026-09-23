@extends('layouts.app')

@section('title', __('Waiter Alerts'))

@section('content')

    <div class="header">
        <div>
            <h1>{{ __('Waiter Alerts') }}</h1>
            <p>{{ __('Clients who need a waiter after their edit window expired.') }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    <div id="live-alerts" data-live-url="{{ route('order-change-requests.board') }}">
        @include('order-change-requests._list')
    </div>

@endsection


@push('styles')
<style>

    .header {
        margin-bottom: 25px;
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

    .alerts {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .alert-card {
        background: white;
        border-left: 4px solid #dc2626;
        border-radius: 10px;
        padding: 16px 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }

    .alert-time {
        color: #6b7280;
        font-size: 13px;
        margin-top: 4px;
    }

    .resolve-button {
        border: none;
        border-radius: 8px;
        padding: 10px 16px;
        background: #16a34a;
        color: white;
        font-weight: bold;
        cursor: pointer;
        white-space: nowrap;
    }

    .resolve-button:hover {
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