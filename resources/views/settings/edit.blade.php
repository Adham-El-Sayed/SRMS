@extends('layouts.app')

@section('title', __('Restaurant Settings'))

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ __('Settings') }}</span>
            <h1>{{ __('Restaurant Settings') }}</h1>
            <p>{{ __('How this restaurant serves guests. What you switch on here is what the counter and the menu can offer.') }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="success-message">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="card settings-card">
        @csrf
        @method('PATCH')

        <h2 class="settings-title">{{ __('Ways you serve') }}</h2>

        <label class="service-option is-fixed">
            <input type="checkbox" checked disabled>
            <span>
                <strong>{{ __('Dine-in') }}</strong>
                <small>{{ __('Guests order from the QR code on their table. Always on.') }}</small>
            </span>
        </label>

        @foreach (['takeaway' => __('Someone collects the order from the counter.'),
                   'delivery' => __('The order is taken to an address.'),
                   'online' => __('An order that reached you from outside — the cashier rings it up.')] as $type => $hint)
            <label class="service-option">
                <input type="checkbox" name="service_types[]" value="{{ $type }}" @checked(in_array($type, $enabled, true))>
                <span>
                    <strong>{{ \App\Support\Settings::typeLabel($type) }}</strong>
                    <small>{{ $hint }}</small>
                </span>
            </label>
        @endforeach

        <div class="form-group delivery-fee">
            <label for="delivery_fee">{{ __('Delivery fee') }} <small class="muted-text">({{ __('EGP') }})</small></label>
            <input type="number" step="0.01" min="0" id="delivery_fee" name="delivery_fee" value="{{ old('delivery_fee', $deliveryFee) }}">
            <small class="muted-text">{{ __('Added to every delivery order and shown on its invoice.') }}</small>
        </div>

        <div class="modal-actions" style="justify-content:flex-start">
            <button type="submit" class="primary-btn">{{ __('Save Changes') }}</button>
        </div>
    </form>

@endsection

@push('styles')
<style>
    .settings-card { max-width: 640px; }
    .settings-title { font-size: 18px; margin: 0 0 16px; }
    .service-option {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 14px 0; border-bottom: 1px solid var(--line); cursor: pointer;
    }
    .service-option.is-fixed { cursor: default; opacity: .75; }
    .service-option input { margin-top: 3px; }
    .service-option strong { display: block; font-size: 15px; }
    .service-option small { color: var(--muted); font-size: 13px; }
    .delivery-fee { margin-top: 20px; max-width: 220px; }
    .delivery-fee input { width: 100%; }
</style>
@endpush
