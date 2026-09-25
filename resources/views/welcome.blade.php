@php
    $locale = app()->getLocale();
    $rtl    = $locale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#241D18">

    <title>SRMS — {{ __('Smart Restaurant Management System') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">

    <link rel="stylesheet" href="{{ asset('css/srms-theme.css') }}?v={{ @filemtime(public_path('css/srms-theme.css')) }}">

    <style>
        body.landing {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(170deg, #2C231C 0%, #241D18 52%, #1C1611 100%);
            background-attachment: fixed;
            color: #EFE3D5;
        }

        .landing__bar {
            width: min(94%, 1140px);
            margin: 0 auto;
            padding: 22px 0;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand { display: inline-flex; align-items: center; gap: 11px; text-decoration: none; color: inherit; }

        .brand__mark {
            display: grid; place-items: center;
            width: 38px; height: 38px;
            border-radius: 11px;
            background: var(--accent); color: #fff;
            font-family: var(--font-display); font-size: 20px; font-weight: 700;
            box-shadow: 0 3px 12px rgba(189, 78, 44, .5);
        }

        .brand__name {
            font-family: var(--font-display);
            font-size: 20px; font-weight: 600; color: #FBF5EE;
        }

        .landing__tools { margin-inline-start: auto; display: flex; align-items: center; gap: 10px; }

        .lang-switch {
            display: inline-flex; padding: 3px; border-radius: 100px;
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .09);
        }

        .lang-switch a {
            padding: 5px 12px; border-radius: 100px;
            font-size: 12.5px; font-weight: 600;
            color: #BCA994; text-decoration: none; line-height: 1.4;
        }

        .lang-switch a.is-on { background: #F3EADF; color: #241D18; }

        .landing__hero {
            flex: 1;
            width: min(94%, 1140px);
            margin: 0 auto;
            display: grid;
            place-items: center;
            padding: 56px 0 72px;
            position: relative;
        }

        .landing__hero::before {
            content: "";
            position: absolute;
            top: -40px;
            inset-inline-end: 4%;
            width: 460px; height: 460px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(189, 78, 44, .3), transparent 66%);
            pointer-events: none;
        }

        .landing__inner { position: relative; max-width: 660px; text-align: center; }

        .landing__kicker {
            display: inline-block;
            font-size: 11.5px; font-weight: 700;
            letter-spacing: .16em; text-transform: uppercase;
            color: var(--amber);
            margin-bottom: 18px;
        }

        html[lang="ar"] .landing__kicker { letter-spacing: 0; text-transform: none; font-size: 13px; }

        .landing__inner h1 {
            font-family: var(--font-display);
            font-size: clamp(34px, 6vw, 58px);
            font-weight: 600;
            line-height: 1.12;
            color: #FBF5EE;
            margin: 0 0 18px;
        }

        .landing__inner p {
            margin: 0 auto 32px;
            max-width: 52ch;
            font-size: 16.5px;
            line-height: 1.7;
            color: #BCA994;
        }

        .landing__actions {
            display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;
        }

        .landing__actions a {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 14px 26px;
            border-radius: 100px;
            font-size: 15px; font-weight: 600;
            text-decoration: none;
            transition: background-color .16s ease, color .16s ease, border-color .16s ease;
        }

        .btn-solid {
            background: var(--accent); color: #fff;
            box-shadow: 0 6px 20px -8px rgba(189, 78, 44, .9);
        }
        .btn-solid:hover { background: var(--accent-dark); }

        .btn-ghost {
            background: transparent; color: #E4D7C8;
            border: 1px solid rgba(255, 255, 255, .2);
        }
        .btn-ghost:hover { background: rgba(255, 255, 255, .08); }

        .landing__foot {
            width: min(94%, 1140px);
            margin: 0 auto;
            padding: 22px 0 32px;
            text-align: center;
            font-size: 12.5px;
            color: #8E7C6B;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }
    </style>
</head>

<body class="landing">

    <header class="landing__bar">
        <a href="{{ url('/') }}" class="brand">
            <span class="brand__mark">S</span>
            <span class="brand__name">SRMS</span>
        </a>

        <div class="landing__tools">
            @include('layouts.partials.language-switch')
        </div>
    </header>


    <main class="landing__hero">
        <div class="landing__inner">

            <span class="landing__kicker">{{ __('Smart Restaurant Management System') }}</span>

            <h1>{{ __('Run the floor from one screen.') }}</h1>

            <p>{{ __('Tables, menu, kitchen and takings — one place for the whole floor.') }}</p>

            <div class="landing__actions">
                {{-- A customer arriving at the front door should be able to
                     order without hunting for the link. --}}
                @if (\App\Support\Settings::offers('online'))
                    <a href="{{ route('online.create') }}" class="btn-solid">{{ __('Order Online') }}</a>
                @endif

                @auth
                    <a href="{{ route('dashboard') }}" class="btn-solid">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}"
                       class="{{ \App\Support\Settings::offers('online') ? 'btn-ghost' : 'btn-solid' }}">{{ __('Log in') }}</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-ghost">{{ __('Register') }}</a>
                    @endif
                @endauth
            </div>

        </div>
    </main>


    <footer class="landing__foot">
        &copy; {{ date('Y') }} SRMS &middot; {{ __('All rights reserved.') }}
    </footer>

</body>

</html>
