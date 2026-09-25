@extends('layouts.app')

@section('title', __('Online Orders'))

@section('content')

    <div class="header">
        <div>
            <h1>{{ __('Online Orders') }}</h1>
            <p>{{ __('Orders customers placed on the website. Call the customer back, accept the order, and it goes to the kitchen.') }}</p>
        </div>
    </div>

    @unless ($offered)
        <div class="alert">
            {{ __('Online ordering is switched off, so the public page is closed.') }}
            <a href="{{ route('settings.edit') }}">{{ __('Restaurant Settings') }}</a>
        </div>
    @endunless

    {{-- The link customers use. Worth having in front of whoever answers the phone. --}}
    <div class="share">
        <div class="share__text">
            <strong>{{ __('Your ordering page') }}</strong>
            <span>{{ __('Share this link with customers — on social media, a printed card, anywhere.') }}</span>
        </div>

        <div class="share__link">
            <input type="text" id="public-url" value="{{ $publicUrl }}" readonly aria-label="{{ __('Your ordering page') }}">
            <button type="button" id="copy-url" class="copy-button">{{ __('Copy') }}</button>
            <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="copy-button ghost">{{ __('Open') }}</a>
        </div>
    </div>

    <div class="today">
        <div class="today__box">
            <span class="today__number">{{ $today['count'] }}</span>
            <span class="today__label">{{ __('Online orders today') }}</span>
        </div>

        <div class="today__box">
            <span class="today__number">{{ number_format($today['money'], 2) }}</span>
            <span class="today__label">{{ __('EGP today') }}</span>
        </div>
    </div>

    <div id="live-online" data-live-url="{{ route('online.board') }}">
        @include('online._board')
    </div>

@endsection


@push('styles')
<style>
    .header { margin-bottom: 26px; }
    .header h1 { margin: 0; font-size: 32px; }
    .header p { margin: 8px 0 0; color: var(--muted); max-width: 70ch; }

    /* ---------- the public link ---------- */

    .share {
        display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
        padding: 18px 20px; margin-bottom: 18px;
        border-radius: var(--r-md);
        background: var(--surface);
        border: 1px solid var(--line-strong);
        box-shadow: var(--sh-1);
    }

    .share__text { display: flex; flex-direction: column; gap: 3px; }
    .share__text strong { font-size: 15px; }
    .share__text span { font-size: 13px; color: var(--muted); }

    .share__link { display: flex; gap: 8px; margin-inline-start: auto; flex-wrap: wrap; }

    .share__link input {
        min-width: 260px; padding: 10px 13px;
        border-radius: var(--r-sm);
        border: 1.5px solid var(--line-strong);
        background: var(--surface-sunk);
        font: inherit; font-size: 13.5px; color: var(--ink-soft);
    }

    .copy-button {
        padding: 10px 16px; border-radius: var(--r-sm);
        border: 1.5px solid transparent; background: var(--ink); color: #FBF5EE;
        font: inherit; font-size: 13.5px; font-weight: 600;
        text-decoration: none; cursor: pointer; white-space: nowrap;
        display: inline-flex; align-items: center;
    }

    .copy-button.ghost { background: var(--surface); color: var(--ink); border-color: var(--line-strong); }

    /* ---------- the day so far ---------- */

    .today { display: flex; gap: 14px; margin-bottom: 22px; flex-wrap: wrap; }

    .today__box {
        flex: 1; min-width: 160px;
        display: flex; flex-direction: column; gap: 2px;
        padding: 16px 18px; border-radius: var(--r-md);
        background: var(--surface); border: 1px solid var(--line-strong);
        box-shadow: var(--sh-1);
    }

    .today__number { font-family: var(--font-display); font-size: 26px; font-weight: 600; }
    .today__label { font-size: 12.5px; color: var(--muted); }

    /* ---------- the board ---------- */

    .orders { display: grid; grid-template-columns: repeat(auto-fit, minmax(330px, 1fr)); gap: 20px; }

    .order-card {
        background: var(--surface); border-radius: var(--r-md); padding: 22px;
        border: 1px solid var(--line); box-shadow: var(--sh-2);
    }

    /* A new one shouts a little, so nobody leaves a customer waiting. */
    .order-card.is-new { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft), var(--sh-2); }

    .order-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 15px; }
    .order-head-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .order-header h2 { margin: 0; font-size: 20px; }

    .order-type {
        display: inline-flex; padding: 4px 10px; border-radius: 100px;
        font-size: 11.5px; font-weight: 700; letter-spacing: .04em;
        border: 1px solid transparent; white-space: nowrap;
    }

    .order-type.type-online   { background: var(--olive-soft); color: var(--olive); border-color: rgba(94,110,76,.3); }
    .order-type.type-delivery { background: var(--info-soft); color: var(--info); border-color: rgba(59,99,130,.25); }
    .order-type.type-takeaway { background: var(--amber-soft); color: var(--warn); border-color: rgba(176,123,20,.25); }
    .order-type.type-dine_in  { background: var(--surface-sunk); color: var(--ink-soft); border-color: var(--line-strong); }

    .status { padding: 6px 12px; border-radius: 100px; font-size: 12.5px; font-weight: 700; }
    .status.pending   { background: var(--amber-soft); color: var(--warn); }
    .status.confirmed { background: var(--info-soft); color: var(--info); }
    .status.preparing { background: var(--accent-soft); color: var(--accent-dark); }
    .status.ready     { background: var(--olive-soft); color: var(--olive); }

    .info p { margin: 6px 0; color: var(--ink-soft); font-size: 14px; }
    .info .ago { color: var(--muted); font-size: 12.5px; }
    .phone-link { color: var(--accent-dark); font-weight: 600; text-decoration: none; }
    .phone-link:hover { text-decoration: underline; }

    .items { border-top: 1px solid var(--line); margin-top: 15px; padding-top: 15px; }
    .item { display: flex; justify-content: space-between; gap: 12px; font-size: 14px; padding: 5px 0; color: var(--ink-soft); }

    .total {
        display: flex; justify-content: space-between;
        margin-top: 14px; padding-top: 14px;
        border-top: 2px solid var(--line-strong);
        font-family: var(--font-display); font-size: 17px; font-weight: 600;
    }

    .actions-row { display: flex; gap: 10px; margin-top: 18px; }

    .confirm-button, .reject-button {
        flex: 1; padding: 12px; border: none; border-radius: var(--r-sm);
        font: inherit; font-size: 14.5px; font-weight: 700; cursor: pointer;
    }

    .confirm-button { background: var(--olive); color: #fff; }
    .reject-button { background: var(--danger-soft); color: var(--danger); }
    .desk-button:disabled { opacity: .6; cursor: not-allowed; }

    .print-button {
        flex: 1; padding: 12px; border-radius: var(--r-sm);
        background: var(--surface-sunk); color: var(--ink-soft);
        font-weight: 600; font-size: 14px; text-align: center; text-decoration: none;
    }

    .print-button:hover { background: var(--line); }

    .empty {
        background: var(--surface); padding: 50px; border-radius: var(--r-md);
        text-align: center; color: var(--muted);
        border: 1px solid var(--line); box-shadow: var(--sh-1);
    }

    .empty h2 { color: var(--ink); margin: 0 0 8px; font-size: 20px; }

    @media (max-width: 720px) {
        .share__link { margin-inline-start: 0; width: 100%; }
        .share__link input { min-width: 0; flex: 1; }
    }
</style>
@endpush


@push('scripts')
<script>
    /*
     * Accepting or refusing an online order runs through the same endpoint the
     * kitchen board uses, so an order has one life story however it is moved.
     */
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('#live-online .desk-button[data-order-id]');
        if (!button) return;

        if (button.dataset.confirm && !window.confirm(button.dataset.confirm)) return;

        const release = window.SRMSLive ? SRMSLive.hold() : function () {};

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
                release();
                alert(data.message || __t('Failed to update order status.'));
                button.disabled = false;
                return;
            }

            release();

            if (window.SRMSLive) {
                await SRMSLive.refreshNow();
            } else {
                window.location.reload();
            }

        } catch (error) {
            console.error(error);
            release();
            alert(__t('Something went wrong while updating the order.'));
            button.disabled = false;
        }
    });

    /* Copying the public link, with a fallback for browsers that refuse the API. */
    document.getElementById('copy-url')?.addEventListener('click', function () {
        const field = document.getElementById('public-url');
        const button = this;

        const done = function () {
            button.textContent = __t('Copied');
            setTimeout(function () { button.textContent = __t('Copy'); }, 1600);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(field.value).then(done).catch(function () {
                field.select(); document.execCommand('copy'); done();
            });
        } else {
            field.select();
            document.execCommand('copy');
            done();
        }
    });
</script>
@endpush
