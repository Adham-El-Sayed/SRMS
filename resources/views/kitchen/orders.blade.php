@extends('layouts.app')

@section('title', __('Kitchen Orders'))

@section('content')

    <div class="header">

        <div>
            <h1>{{ __('Kitchen Orders') }}</h1>
            <p>{{ __('Manage and update restaurant orders.') }}</p>
        </div>

    </div>


    <div id="live-kitchen" data-live-url="{{ route('kitchen.board') }}">
        @include('kitchen._board')
    </div>

@endsection


@push('styles')

<style>

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
        gap: 15px;
        margin-bottom: 18px;
    }


    .order-header h2 {
        margin: 0;
        font-size: 22px;
    }


    .status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: bold;
    }


    .status.pending {
        background: #fff3cd;
        color: #856404;
    }


    .status.confirmed {
        background: #cfe2ff;
        color: #084298;
    }


    .status.preparing {
        background: #e2d9f3;
        color: #59359a;
    }


    .status.ready {
        background: #d1e7dd;
        color: #0f5132;
    }


    .info {
        margin-bottom: 20px;
    }


    .info p {
        margin: 8px 0;
        color: #4b5563;
    }


    .items {
        border-top: 1px solid #e5e7eb;
        padding-top: 15px;
    }


    .items h3 {
        margin-top: 0;
        font-size: 17px;
    }


    .item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }


    .item:last-child {
        border-bottom: none;
    }


    .item-name {
        font-weight: bold;
    }


    .item-details {
        color: #6b7280;
        font-size: 14px;
        margin-top: 5px;
    }


    .item-notes {
        color: #b45309;
        font-size: 13px;
        margin-top: 4px;
        font-style: italic;
    }


    .item-price {
        font-weight: bold;
        white-space: nowrap;
    }


    .no-items {
        color: #6b7280;
    }


    .total {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #e5e7eb;
        font-size: 18px;
        font-weight: bold;
    }


    .actions {
        margin-top: 20px;
    }


    .status-button {
        width: 100%;
        border: none;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        background: #2563eb;
        color: white;
        transition: 0.2s;
    }


    .status-button:hover {
        opacity: 0.9;
    }


    .status-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }


    .complete-button {
        background: #16a34a;
    }


    .empty {
        background: white;
        padding: 50px;
        border-radius: 14px;
        text-align: center;
        color: #6b7280;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .empty h2 {
        color: #1f2937;
    }


    @media (max-width: 768px) {

        .orders {
            grid-template-columns: 1fr;
        }

    }

</style>

@endpush


@push('scripts')
<script>
    /*
     * Status buttons on the kitchen board. The board itself is refreshed by
     * srms-live.js; after a change we ask it to refresh straight away.
     */
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('#live-kitchen .status-button[data-order-id]');
        if (!button) return;

        if (button.dataset.confirm && !window.confirm(button.dataset.confirm)) return;

        button.disabled = true;

        try {
            const response = await fetch(
                '{{ route('kitchen.orders.status', '__ID__') }}'.replace('__ID__', encodeURIComponent(button.dataset.orderId)),
                {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: button.dataset.status })
                }
            );

            if (window.SRMSLive && SRMSLive.handleAuthFailure(response)) return;

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                alert(data.message || __t('Failed to update order status.'));
                button.disabled = false;
                return;
            }

            window.SRMSLive ? SRMSLive.refreshNow() : window.location.reload();

        } catch (error) {
            console.error(error);
            alert(__t('Something went wrong while updating the order.'));
            button.disabled = false;
        }
    });
</script>
@endpush