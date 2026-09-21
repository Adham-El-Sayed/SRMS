@php
    $locale = app()->getLocale();
    $rtl    = $locale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#241D18">

    <title>{{ config('app.name', 'SRMS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/srms-theme.css') }}">

    <style>
        /* ==================================================================
           Sign-in — a two-panel page: the room on one side, the form on the
           other. On phones the panel collapses to a short header.
           ================================================================== */

        body.auth-body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            background: var(--paper);
        }

        .auth-aside {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 52px;
            background: linear-gradient(155deg, #33281F 0%, #241D18 58%, #1C1611 100%);
            color: #EFE3D5;
            overflow: hidden;
        }

        /* Soft warm light from the top corner, like a lamp over a table */
        .auth-aside::before {
            content: "";
            position: absolute;
            inset-inline-end: -120px;
            top: -140px;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(189, 78, 44, .42), transparent 68%);
            pointer-events: none;
        }

        .auth-aside::after {
            content: "";
            position: absolute;
            inset-inline-start: -90px;
            bottom: -150px;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(193, 135, 28, .26), transparent 70%);
            pointer-events: none;
        }

        .auth-aside > * { position: relative; z-index: 1; }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .auth-brand__mark {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--accent);
            color: #fff;
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(189, 78, 44, .5);
        }

        .auth-brand__name {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 600;
            color: #FBF5EE;
        }

        .auth-aside__lede h2 {
            font-family: var(--font-display);
            font-size: clamp(27px, 3vw, 37px);
            font-weight: 600;
            line-height: 1.22;
            color: #FBF5EE;
            margin: 0 0 14px;
        }

        .auth-aside__lede p {
            margin: 0;
            max-width: 40ch;
            color: #BCA994;
            font-size: 15px;
            line-height: 1.65;
        }

        .auth-points {
            list-style: none;
            margin: 26px 0 0;
            padding: 0;
            display: grid;
            gap: 11px;
        }

        .auth-points li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #CDBBA8;
        }

        .auth-points li::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--amber);
            flex-shrink: 0;
        }

        .auth-aside__foot {
            font-size: 12.5px;
            color: #8E7C6B;
        }

        /* --- Form side ---------------------------------------------------- */

        .auth-main {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 44px 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 412px;
        }

        .auth-card__head { margin-bottom: 26px; }

        .auth-card__head h1 {
            margin: 0 0 6px;
            font-size: 27px;
        }

        .auth-card__head p {
            margin: 0;
            color: var(--muted);
            font-size: 14.5px;
        }

        .auth-card__body {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            box-shadow: var(--sh-2);
            padding: 28px;
        }

        .auth-lang {
            margin-bottom: 22px;
            display: flex;
            justify-content: flex-end;
        }

        .auth-lang .lang-switch {
            display: inline-flex;
            padding: 3px;
            border-radius: 100px;
            background: var(--surface-sunk);
            border: 1px solid var(--line);
        }

        .auth-lang .lang-switch a {
            padding: 5px 12px;
            border-radius: 100px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
        }

        .auth-lang .lang-switch a.is-on {
            background: var(--ink);
            color: #FBF5EE;
        }

        /* Breeze markup uses utility classes; nudge the pieces the theme
           cannot reach by class name alone. */
        .auth-card__body .mt-4 { margin-top: 18px; }
        .auth-card__body .mt-6 { margin-top: 24px; }
        .auth-card__body label { color: var(--ink-soft); }
        .auth-card__body a { font-size: 13.5px; }

        @media (max-width: 900px) {
            body.auth-body { grid-template-columns: 1fr; }

            .auth-aside {
                padding: 26px 24px 30px;
                gap: 18px;
            }

            .auth-aside__lede h2 { font-size: 24px; }
            .auth-points,
            .auth-aside__foot { display: none; }

            .auth-main { padding: 30px 20px 48px; }
            .auth-card__body { padding: 22px; }
        }
    </style>
</head>

<body class="auth-body">

    <aside class="auth-aside">

        <a href="{{ url('/') }}" class="auth-brand">
            <span class="auth-brand__mark">S</span>
            <span class="auth-brand__name">SRMS</span>
        </a>

        <div class="auth-aside__lede">
            <h2>{{ __('Smart Restaurant Management System') }}</h2>
            <p>{{ __('Tables, menu, kitchen and takings — one place for the whole floor.') }}</p>

            <ul class="auth-points">
                <li>{{ __('QR menus your guests order from directly') }}</li>
                <li>{{ __('Live kitchen queue and table status') }}</li>
                <li>{{ __('Shift takings and monthly reports') }}</li>
            </ul>
        </div>

        <div class="auth-aside__foot">
            &copy; {{ date('Y') }} SRMS &middot; {{ __('All rights reserved.') }}
        </div>

    </aside>


    <main class="auth-main">
        <div class="auth-card">

            <div class="auth-lang">
                @include('layouts.partials.language-switch')
            </div>

            <div class="auth-card__head">
                <h1>{{ __('Welcome back') }}</h1>
                <p>{{ __('Sign in to continue') }}</p>
            </div>

            <div class="auth-card__body">
                {{ $slot }}
            </div>

        </div>
    </main>

</body>

</html>
