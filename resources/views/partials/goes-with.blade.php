{{--
    "People also order…" — the row inside the basket.

    $pairs  product id => the ids most often ordered with it
--}}
<div class="goes-with" id="goes-with" hidden></div>

@once
    @push('styles')
    <style>
        /* ==================================================================
           Suggestions inside the basket. Small and quiet: a row of three,
           each one tap to add, never in the way of the order itself.
           ================================================================== */

        .goes-with {
            margin-top: 18px; padding-top: 16px;
            border-top: 1px solid var(--line);
        }

        body .goes-with[hidden] { display: none; }

        .goes-with__title {
            margin: 0 0 11px;
            font-size: 13px; font-weight: 700;
            color: var(--ink-soft);
        }

        .goes-with__row { display: flex; flex-direction: column; gap: 8px; }

        .goes-with__item {
            display: flex; align-items: center; gap: 11px;
            padding: 8px 10px;
            border-radius: var(--r-sm);
            background: var(--surface-sunk);
        }

        .goes-with__item img, .goes-with__blank {
            width: 42px; height: 42px; flex-shrink: 0;
            border-radius: 8px; object-fit: cover;
            background: var(--line);
            display: grid; place-items: center; font-size: 17px;
        }

        .goes-with__text { display: flex; flex-direction: column; min-width: 0; }

        .goes-with__name {
            font-size: 13.5px; font-weight: 600;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        .goes-with__price { font-size: 12px; color: var(--muted); }

        body .goes-with__add {
            margin-inline-start: auto; flex-shrink: 0;
            width: 32px; height: 32px; padding: 0;
            display: grid; place-items: center;
            border-radius: 50%;
            font-size: 18px; font-weight: 700; line-height: 1;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        /* What goes with what, worked out from real baskets. */
        window.SRMS_PAIRS = @json($pairs ?? new stdClass);
    </script>
    <script src="{{ asset('js/srms-goes-with.js') }}?v={{ @filemtime(public_path('js/srms-goes-with.js')) }}" defer></script>
    @endpush
@endonce
