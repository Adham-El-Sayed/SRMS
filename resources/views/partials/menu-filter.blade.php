{{--
    The menu filter: a search box and one chip per course.

    $categories  a collection of categories (id + name)
    $scope       CSS selector for the part of the page holding the dishes
    $placeholder optional: what the search box says when empty
--}}
<div class="menu-filter" data-menu-filter="{{ $scope }}">

    <div class="menu-filter__search">
        <svg class="menu-filter__icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <circle cx="9" cy="9" r="6" stroke="currentColor" stroke-width="1.8"/>
            <path d="M13.5 13.5 17 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>

        <input type="search" data-filter-search autocomplete="off"
               placeholder="{{ $placeholder ?? __('Search the menu') }}"
               aria-label="{{ $placeholder ?? __('Search the menu') }}">

        <button type="button" class="menu-filter__clear" data-filter-clear hidden
                aria-label="{{ __('Clear') }}">&times;</button>
    </div>

    @if ($categories->count() > 1)
        <div class="menu-filter__chips" role="group" aria-label="{{ __('Categories') }}">
            <button type="button" class="filter-chip is-on" data-filter-chip data-category=""
                    aria-pressed="true">{{ __('All') }}</button>

            @foreach ($categories as $category)
                <button type="button" class="filter-chip" data-filter-chip
                        data-category="{{ $category->id }}" aria-pressed="false">{{ $category->name }}</button>
            @endforeach
        </div>
    @endif

</div>

@once
    @push('styles')
    <style>
        /* ==================================================================
           Finding something on a menu. Used by the guest menu, the online
           page, the counter and menu management, so it borrows nothing from
           any one of them.
           ================================================================== */

        .menu-filter { margin-bottom: 18px; }

        .menu-filter__search { position: relative; }

        .menu-filter__icon {
            position: absolute; inset-inline-start: 16px; top: 50%;
            transform: translateY(-50%);
            width: 18px; height: 18px;
            color: var(--muted); pointer-events: none;
        }

        /* The design system styles every input and loads after this block, so
           a rule of equal weight would lose the tie. This names one more. */
        body .menu-filter__search input[type="search"] {
            width: 100%;
            padding: 13px 46px;
            border-radius: 100px;
            border: 1.5px solid var(--line-strong);
            background: var(--surface);
            font-family: var(--font-sans); font-size: 15px; color: var(--ink);
            box-shadow: none;
            -webkit-appearance: none;
        }

        body .menu-filter__search input[type="search"]::-webkit-search-cancel-button { display: none; }

        body .menu-filter__search input[type="search"]:focus {
            outline: none; border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .menu-filter__clear {
            position: absolute; inset-inline-end: 8px; top: 50%;
            transform: translateY(-50%);
            width: 30px; height: 30px; padding: 0;
            display: grid; place-items: center;
            border: none; border-radius: 50%;
            background: var(--surface-sunk); color: var(--muted);
            font-size: 18px; line-height: 1; cursor: pointer;
        }

        .menu-filter__clear:hover { background: var(--line); color: var(--ink); }
        body .menu-filter__clear[hidden] { display: none; }

        .menu-filter__chips {
            display: flex; gap: 8px; margin-top: 12px;
            overflow-x: auto; scrollbar-width: none;
            padding-bottom: 2px;
        }

        .menu-filter__chips::-webkit-scrollbar { display: none; }

        body .filter-chip {
            flex-shrink: 0;
            padding: 8px 15px;
            border-radius: 100px;
            background: var(--surface);
            border: 1px solid var(--line-strong);
            color: var(--ink-soft);
            font-family: var(--font-sans);
            font-size: 13.5px; font-weight: 600;
            white-space: nowrap; cursor: pointer;
            transition: background-color .16s ease, color .16s ease, border-color .16s ease;
        }

        body .filter-chip:hover { border-color: var(--accent); color: var(--accent-dark); }

        body .filter-chip.is-on {
            background: var(--ink); border-color: var(--ink); color: #FBF5EE;
        }

        .filter-empty { text-align: center; padding: 40px 10px; color: var(--muted); }
        body .filter-empty[hidden] { display: none; }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('js/srms-menu-filter.js') }}?v={{ @filemtime(public_path('js/srms-menu-filter.js')) }}" defer></script>
    @endpush
@endonce
