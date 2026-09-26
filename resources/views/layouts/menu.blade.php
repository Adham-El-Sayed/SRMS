@php
    $locale = app()->getLocale();
    $rtl    = $locale === 'ar';
    $i18nFile = lang_path($locale . '.json');
    $i18n = ($locale !== 'en' && is_file($i18nFile)) ? json_decode(file_get_contents($i18nFile), true) : [];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#241D18">

    <title>@yield('title', 'SRMS')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">

    {{-- Page-level styles first, so the design system below can govern them --}}
    @stack('styles')

    <link rel="stylesheet" href="{{ asset('css/srms-theme.css') }}?v={{ @filemtime(public_path('css/srms-theme.css')) }}">

    <style>
        /* ==================================================================
           Guest menu chrome — the first thing a diner sees after scanning
           ================================================================== */

        .menu-topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: linear-gradient(180deg, #2C231C 0%, #241D18 100%);
            color: #F3EADF;
            box-shadow: 0 1px 0 rgba(255, 255, 255, .06), 0 8px 24px -14px rgba(36, 29, 24, .7);
        }

        .menu-topbar__inner {
            width: min(94%, 1200px);
            margin: 0 auto;
            min-height: 62px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
            color: inherit;
            margin: 0;
        }

        .brand__mark {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            font-family: var(--font-display);
            font-size: 19px;
            font-weight: 700;
            line-height: 1;
            box-shadow: 0 2px 8px rgba(189, 78, 44, .45);
            flex-shrink: 0;
        }

        .brand__text { display: flex; flex-direction: column; line-height: 1.15; }

        .brand__name {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 600;
            color: #FBF5EE;
        }

        .brand__tag {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #A2907F;
        }

        html[lang="ar"] .brand__tag { letter-spacing: 0; text-transform: none; font-size: 11.5px; }

        .menu-topbar__tools {
            margin-inline-start: auto;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .lang-switch {
            display: inline-flex;
            padding: 3px;
            border-radius: 100px;
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .09);
        }

        .lang-switch a {
            padding: 5px 12px;
            border-radius: 100px;
            font-size: 12.5px;
            font-weight: 600;
            color: #BCA994;
            text-decoration: none;
            line-height: 1.4;
            transition: background-color .16s ease, color .16s ease;
        }

        .lang-switch a:hover { color: #F3EADF; }
        .lang-switch a.is-on { background: #F3EADF; color: #241D18; }

        .guest-table {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 100px;
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .1);
            font-size: 13.5px;
            font-weight: 600;
            color: #EFE2D4;
            white-space: nowrap;
        }

        .guest-table__dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7FB08C;
            box-shadow: 0 0 0 3px rgba(127, 176, 140, .2);
        }

        /* A thin band of warm colour under the bar, so the page does not
           start on a cold edge. */
        .menu-ribbon {
            height: 3px;
            background: linear-gradient(90deg,
                var(--accent) 0%,
                var(--amber) 45%,
                var(--olive) 100%);
        }

        .menu-footer {
            margin-top: 56px;
            padding: 24px 5% 36px;
            text-align: center;
            font-size: 12.5px;
            color: var(--muted);
            border-top: 1px solid var(--line);
        }

        @media (max-width: 640px) {
            .menu-topbar__inner { min-height: 56px; }
            .brand__tag { display: none; }
            .guest-table { padding: 7px 11px; font-size: 12.5px; }
        }
    </style>
</head>

<body>

    <header class="menu-topbar">
        <div class="menu-topbar__inner">

            <a href="{{ request()->url() }}" class="brand">
                <span class="brand__mark">S</span>
                <span class="brand__text">
                    <span class="brand__name">SRMS</span>
                    <span class="brand__tag">{{ __('Restaurant Menu') }}</span>
                </span>
            </a>

            <div class="menu-topbar__tools">
                @isset($table)
                    <span class="guest-table">
                        <span class="guest-table__dot"></span>
                        {{ __('Table') }} #{{ $table->number }}
                    </span>
                @endisset

                @include('layouts.partials.language-switch')
            </div>

        </div>
    </header>

    <div class="menu-ribbon"></div>


    <main class="page-content">
        @yield('content')
    </main>


    <footer class="menu-footer">
        &copy; {{ date('Y') }} SRMS &middot; {{ __('All rights reserved.') }}
    </footer>


    {{-- Hands the active translations to page scripts: __t('Submit Order') --}}
    <script>
        window.SRMS_I18N = @json($i18n);
        window.__t = function (key) {
            return Object.prototype.hasOwnProperty.call(window.SRMS_I18N, key) ? window.SRMS_I18N[key] : key;
        };
    </script>

    @stack('scripts')

</body>

</html>
