{{--
    The dishes people order most. Built only from real orders, so a new
    restaurant simply does not show it.

    $popular   products, best-selling first
    $ordering  whether the page can add things to an order
--}}
@if ($popular->count() > 0)
    <section class="popular" aria-labelledby="popular-title">

        <div class="popular__head">
            <h2 class="popular__title" id="popular-title">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M13.2 2.1c.3 2.6-.7 4.2-2 5.6-1.4 1.6-3.1 3-3.1 5.9a5.9 5.9 0 0 0 11.8 0c0-2.3-1-3.9-2.1-5.2-.2 1-.8 1.7-1.6 2-.1-3.4-1.4-6.4-3-8.3Zm-1.3 12c.8.6 1.3 1.3 1.3 2.3a2 2 0 0 1-4 0c0-1.4 1.6-2 2.7-4 .5.6 0 1.1 0 1.7Z"/>
                </svg>
                {{ __('What people order most') }}
            </h2>
            <p class="popular__note">{{ __('The favourites of the last month.') }}</p>
        </div>

        <div class="popular__rail">
            @foreach ($popular as $product)
                <article class="pick">
                    <div class="pick__image">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <div class="pick__placeholder">🍽</div>
                        @endif

                        <span class="pick__rank">{{ $loop->iteration }}</span>
                    </div>

                    <div class="pick__body">
                        <h3 class="pick__name">{{ \App\Support\Bilingual::lead($product->name) }}</h3>
                        <span class="pick__category">{{ \App\Support\Bilingual::lead($product->category?->name) }}</span>

                        <div class="pick__foot">
                            <span class="pick__price">{{ number_format($product->price, 2) }} <small>{{ __('EGP') }}</small></span>

                            @if ($ordering ?? false)
                                <button type="button" class="add-button"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-label="{{ \App\Support\Bilingual::lead($product->name) }}"
                                        data-product-price="{{ $product->price }}"
                                        aria-label="{{ __('Add') }} {{ $product->name }}">
                                    {{ __('Add') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

    </section>
@endif

@once
    @push('styles')
    <style>
        /* ==================================================================
           The favourites rail. It sits above the menu proper, scrolls
           sideways on a phone, and is never taller than it needs to be.
           ================================================================== */

        .popular { margin-bottom: 30px; }

        .popular__head { margin-bottom: 14px; }

        .popular__title {
            margin: 0;
            font-size: 20px;
            display: flex; align-items: center; gap: 9px;
        }

        .popular__title svg {
            width: 21px; height: 21px; flex-shrink: 0;
            color: var(--accent);
        }

        .popular__note { margin: 4px 0 0; color: var(--muted); font-size: 13px; }

        .popular__rail {
            display: flex; gap: 12px;
            overflow-x: auto; scrollbar-width: none;
            padding-bottom: 4px;
            margin-inline: -16px; padding-inline: 16px;
            scroll-snap-type: x mandatory;
        }

        .popular__rail::-webkit-scrollbar { display: none; }

        .pick {
            flex: 0 0 168px;
            scroll-snap-align: start;
            display: flex; flex-direction: column;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r);
            overflow: hidden;
            box-shadow: var(--sh-1);
        }

        /* overflow:hidden is what makes the ratio bite: without it the
           picture's own height pushes the box taller than 4:3. */
        .pick__image {
            position: relative; aspect-ratio: 4 / 3;
            background: var(--surface-sunk); overflow: hidden;
        }

        .pick__image img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .pick__placeholder { display: grid; place-items: center; height: 100%; font-size: 26px; opacity: .45; }

        .pick__rank {
            position: absolute; inset-inline-start: 8px; top: 8px;
            min-width: 22px; height: 22px; padding: 0 6px;
            display: grid; place-items: center;
            border-radius: 100px;
            background: var(--ink); color: #FBF5EE;
            font-size: 11.5px; font-weight: 700;
        }

        .pick__body { padding: 11px 12px 12px; display: flex; flex-direction: column; gap: 2px; flex: 1; }
        .pick__name { margin: 0; font-size: 14.5px; line-height: 1.3; }
        .pick__category { font-size: 11.5px; color: var(--muted); }

        .pick__foot {
            margin-top: auto; padding-top: 10px;
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
        }

        .pick__price { font-family: var(--font-display); font-size: 15px; font-weight: 600; white-space: nowrap; }
        .pick__price small { font-size: 10.5px; color: var(--muted); font-family: var(--font-sans); }

        body .pick .add-button { padding: 7px 13px; font-size: 13px; }

        /* ---------- the badge on a category's leader ---------- */

        .dish__loved, .counter-item__loved {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 9px;
            border-radius: 100px;
            background: var(--amber-soft);
            border: 1px solid rgba(193, 135, 28, .3);
            color: #8A5E0C;
            font-size: 11px; font-weight: 700;
            line-height: 1.45;
            white-space: nowrap;
        }

        .dish__loved {
            position: absolute; inset-inline-end: 10px; top: 10px;
            background: rgba(255, 248, 235, .96);
            box-shadow: 0 2px 8px rgba(36, 29, 24, .18);
        }

        html[lang="ar"] .dish__loved, html[lang="ar"] .counter-item__loved { font-size: 11.5px; }

        @media (max-width: 620px) {
            .pick { flex-basis: 150px; }
        }
    </style>
    @endpush
@endonce
