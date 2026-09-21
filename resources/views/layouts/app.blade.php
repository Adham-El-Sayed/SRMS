@php
    $locale = app()->getLocale();
    $rtl    = $locale === 'ar';
    $isGuestMenu = request()->routeIs('tables.menu');
    $i18nFile = lang_path($locale . '.json');
    $i18n = ($locale !== 'en' && is_file($i18nFile)) ? json_decode(file_get_contents($i18nFile), true) : [];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#241D18">

    <title>@yield('title', 'SRMS')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">

    {{-- Component-based pages (Breeze profile) rely on the compiled bundle.
         Pages that @extend this layout bring their own CSS and must not get
         Tailwind's preflight, which would reset it. --}}
    @unless (View::hasSection('content'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless

    {{-- Page-level styles first, so the design system below can govern them --}}
    @stack('styles')

    <link rel="stylesheet" href="{{ asset('css/srms-theme.css') }}">

    <style>
        /* ==================================================================
           Application chrome — top bar, navigation, footer
           ================================================================== */

        .app-topbar {
            background: linear-gradient(180deg, #2C231C 0%, #241D18 100%);
            color: #F3EADF;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
        }

        .app-topbar__inner,
        .app-nav__inner {
            width: min(94%, 1240px);
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .app-topbar__inner { min-height: 64px; }

        /* --- Brand ------------------------------------------------------ */

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
            letter-spacing: .01em;
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

        /* --- Top bar utilities ------------------------------------------ */

        .topbar__tools {
            margin-inline-start: auto;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .lang-switch a.is-on {
            background: #F3EADF;
            color: #241D18;
        }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 6px 7px 6px 12px;
            border-radius: 100px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .09);
            font-size: 13.5px;
            color: #E4D7C8;
            text-decoration: none;
        }

        [dir="rtl"] .user-chip { padding: 6px 12px 6px 7px; }

        .user-chip:hover { background: rgba(255, 255, 255, .11); }

        .user-chip__avatar {
            display: grid;
            place-items: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--olive);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        .logout-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, .16);
            color: #CDBBA8;
            padding: 8px 14px;
            border-radius: 100px;
            font-family: var(--font-sans);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .16s ease, color .16s ease, border-color .16s ease;
        }

        .logout-btn:hover {
            background: rgba(189, 78, 44, .9);
            border-color: transparent;
            color: #fff;
        }

        /* --- Navigation strip ------------------------------------------- */

        .app-nav {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(250, 246, 240, .92);
            backdrop-filter: saturate(1.6) blur(10px);
            -webkit-backdrop-filter: saturate(1.6) blur(10px);
            border-bottom: 1px solid var(--line);
        }

        .app-nav__inner {
            gap: 2px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .app-nav__inner::-webkit-scrollbar { display: none; }

        .app-nav a {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 15px 13px;
            font-size: 14px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            white-space: nowrap;
            transition: color .16s ease;
        }

        .app-nav a::after {
            content: "";
            position: absolute;
            inset-inline: 13px;
            bottom: -1px;
            height: 2px;
            border-radius: 2px;
            background: var(--accent);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform .2s cubic-bezier(.2, .7, .3, 1);
        }

        .app-nav a:hover { color: var(--ink); }

        .app-nav a.active { color: var(--accent-dark); }
        .app-nav a.active::after { transform: scaleX(1); }

        .nav-divider {
            width: 1px;
            height: 18px;
            background: var(--line-strong);
            margin: 0 8px;
            flex-shrink: 0;
        }

        #alerts-badge {
            display: none;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 100px;
            background: var(--accent);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 18px;
            text-align: center;
        }

        /* --- Guest (QR menu) variant ------------------------------------ */

        .guest-table {
            margin-inline-start: auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 100px;
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .1);
            font-size: 13.5px;
            font-weight: 600;
            color: #EFE2D4;
        }

        .guest-table__dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7FB08C;
            box-shadow: 0 0 0 3px rgba(127, 176, 140, .2);
        }

        /* --- Footer ------------------------------------------------------ */

        .site-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .site-footer__dot { color: var(--line-strong); }

        /* --- Small screens ----------------------------------------------- */

        @media (max-width: 720px) {
            .app-topbar__inner { min-height: 58px; padding: 9px 0; flex-wrap: wrap; }
            .brand__tag { display: none; }
            .topbar__tools { gap: 7px; }
            .user-chip span:not(.user-chip__avatar) { display: none; }
            .user-chip { padding: 6px; }
            .app-nav__inner { gap: 0; }
            .app-nav a { padding: 13px 11px; font-size: 13.5px; }
            .nav-divider { display: none; }
        }
    </style>
</head>

<body>

    @if ($isGuestMenu)

        {{-- ============================================================
             Guest header — what a diner sees after scanning the QR code
             ============================================================ --}}

        <header class="app-topbar">
            <div class="app-topbar__inner">

                <a href="{{ request()->url() }}" class="brand">
                    <span class="brand__mark">S</span>
                    <span class="brand__text">
                        <span class="brand__name">SRMS</span>
                        <span class="brand__tag">{{ __('Restaurant Menu') }}</span>
                    </span>
                </a>

                @isset($table)
                    <span class="guest-table">
                        <span class="guest-table__dot"></span>
                        {{ __('Table') }} #{{ $table->number }}
                    </span>
                @endisset

                <div class="topbar__tools" @isset($table) style="margin-inline-start:12px" @endisset>
                    @include('layouts.partials.language-switch')
                </div>

            </div>
        </header>

    @else

        {{-- ============================================================
             Staff header — brand and account on top, sections below
             ============================================================ --}}

        <header class="app-topbar">
            <div class="app-topbar__inner">

                <a href="{{ route('dashboard') }}" class="brand">
                    <span class="brand__mark">S</span>
                    <span class="brand__text">
                        <span class="brand__name">SRMS</span>
                        <span class="brand__tag">{{ __('Smart Restaurant Management System') }}</span>
                    </span>
                </a>

                <div class="topbar__tools">
                    @include('layouts.partials.language-switch')

                    @auth
                        <a href="{{ route('profile.edit') }}" class="user-chip">
                            <span class="user-chip__avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span>{{ auth()->user()->name }}</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-btn">{{ __('Log Out') }}</button>
                        </form>
                    @endauth
                </div>

            </div>
        </header>

        <nav class="app-nav">
            <div class="app-nav__inner">

                <a href="{{ route('dashboard') }}"
                   class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">{{ __('Dashboard') }}</a>

                <a href="{{ route('tables.index') }}"
                   class="{{ request()->routeIs('tables.*') ? 'active' : '' }}">{{ __('Tables') }}</a>

                <a href="{{ route('kitchen.orders') }}"
                   class="{{ request()->routeIs('kitchen.orders') ? 'active' : '' }}">{{ __('Kitchen') }}</a>

                <a href="{{ route('order-change-requests.index') }}" id="alerts-link"
                   class="{{ request()->routeIs('order-change-requests.*') ? 'active' : '' }}">
                    {{ __('Alerts') }}
                    <span id="alerts-badge"></span>
                </a>

                <span class="nav-divider"></span>

                <a href="{{ route('menu.management') }}"
                   class="{{ request()->routeIs('menu.management*') ? 'active' : '' }}">{{ __('Menu Management') }}</a>

                <a href="{{ route('cash-payments.index') }}"
                   class="{{ request()->routeIs('cash-payments.*') ? 'active' : '' }}">{{ __('Cash Payments') }}</a>

                <span class="nav-divider"></span>

                <a href="{{ route('shifts.current') }}"
                   class="{{ request()->routeIs('shifts.current') ? 'active' : '' }}">{{ __('Shift') }}</a>

                <a href="{{ route('shifts.history') }}"
                   class="{{ request()->routeIs('shifts.history') || request()->routeIs('shifts.show') ? 'active' : '' }}">{{ __('Shift History') }}</a>

                <a href="{{ route('reports.monthly') }}"
                   class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">{{ __('Reports') }}</a>

            </div>
        </nav>

    @endif


    <main class="page-content">

        @isset($header)
            <div class="page-header">{{ $header }}</div>
        @endisset

        {{-- Works for both styles: @extends pages fill 'content',
             <x-app-layout> pages arrive as the slot. --}}
        @yield('content')
        {{ $slot ?? '' }}

    </main>


    <footer class="site-footer">
        <span>&copy; {{ date('Y') }} SRMS</span>
        <span class="site-footer__dot">&middot;</span>
        <span>{{ __('All rights reserved.') }}</span>
    </footer>


    {{-- Hands the active translations to page scripts: __t('Submit Order') --}}
    <script>
        window.SRMS_I18N = @json($i18n);
        window.__t = function (key) {
            return Object.prototype.hasOwnProperty.call(window.SRMS_I18N, key) ? window.SRMS_I18N[key] : key;
        };
    </script>

    @stack('scripts')

    @auth
    <script>
        (function () {
            var badge = document.getElementById('alerts-badge');
            if (!badge) return;

            function poll() {
                fetch('{{ route('order-change-requests.pending-count') }}')
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'inline-block';
                        } else {
                            badge.style.display = 'none';
                        }
                    })
                    .catch(function () {});
            }

            poll();
            setInterval(poll, 15000);
        })();
    </script>
    @endauth

</body>

</html>
