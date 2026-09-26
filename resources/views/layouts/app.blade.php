@php
    $locale = app()->getLocale();
    $rtl    = $locale === 'ar';
    $isGuestMenu = request()->routeIs('tables.menu');
    $staffUser  = auth()->user();
    $isSuper    = \App\Support\Access::isSuperAdmin($staffUser);
    $workspace  = \App\Support\Access::workspaceFor($staffUser);
    $isStaff    = $workspace !== 'none';
    // What the navigation shows. A super admin sees the workspace they picked.
    $showAdmin   = $workspace === 'admin';
    $showMoney   = in_array($workspace, ['admin', 'cashier'], true);
    $showKitchen = in_array($workspace, ['admin', 'kitchen', 'cashier'], true);
    $showAlerts  = in_array($workspace, ['admin', 'cashier'], true);
    $offers      = \App\Support\Settings::enabledTypes();

    /*
     | The navigation, as sections rather than a single long row. Fourteen
     | links side by side meant scrolling sideways to find half the app; in
     | sections, everything is two clicks away and nothing is off-screen.
     |
     | A section with one visible link is drawn as that link, so the kitchen
     | never sees a menu holding a single item.
     */
    $navSections = [
        [
            'label' => __('Dashboard'),
            'show'  => $showAdmin,
            'items' => [
                ['label' => __('Dashboard'), 'route' => 'dashboard', 'active' => 'dashboard'],
            ],
        ],
        [
            'label' => __('Orders'),
            'show'  => $showKitchen || $showAlerts,
            'items' => [
                ['label' => __('Kitchen'), 'route' => 'kitchen.orders', 'active' => 'kitchen.*',
                 'show' => $showKitchen, 'pulse' => 'kitchen'],
                ['label' => __('Alerts'), 'route' => 'order-change-requests.index', 'active' => 'order-change-requests.*',
                 'show' => $showAlerts, 'pulse' => 'alerts'],
                ['label' => __('Online Orders'), 'route' => 'online.desk', 'active' => 'online.*',
                 'show' => $showAdmin && in_array('online', $offers, true), 'pulse' => 'online'],
                ['label' => __('Delivery'), 'route' => 'delivery.index', 'active' => 'delivery.*',
                 'show' => $showMoney && in_array('delivery', $offers, true)],
            ],
        ],
        [
            'label' => __('Cash Desk'),
            'show'  => $showMoney,
            'items' => [
                ['label' => __('Payments'), 'route' => 'cash-payments.index', 'active' => 'cash-payments.*',
                 'pulse' => 'payments', 'quiet' => true],
                ['label' => __('Counter'), 'route' => 'counter.create', 'active' => 'counter.*',
                 'show' => count(array_diff($offers, ['dine_in'])) > 0],
                ['label' => __('Shift'), 'route' => 'shifts.current', 'active' => 'shifts.current'],
                // The history is a manager's record, not part of a cashier's shift.
                ['label' => __('Shift History'), 'route' => 'shifts.history',
                 'active' => 'shifts.history|shifts.show', 'show' => $showAdmin],
            ],
        ],
        [
            'label' => __('Restaurant'),
            'show'  => $showAdmin,
            'items' => [
                ['label' => __('Tables'), 'route' => 'tables.index', 'active' => 'tables.*'],
                ['label' => __('Menu Management'), 'route' => 'menu.management', 'active' => 'menu.management*'],
                ['label' => __('Reports'), 'route' => 'reports.monthly', 'active' => 'reports.*'],
                ['label' => __('Staff'), 'route' => 'staff.index', 'active' => 'staff.*|employees.*'],
                ['label' => __('Settings'), 'route' => 'settings.edit', 'active' => 'settings.*'],
            ],
        ],
    ];

    // Drop what this account may not open, and any section left empty.
    $navSections = collect($navSections)
        ->filter(fn ($section) => $section['show'] ?? true)
        ->map(function ($section) {
            $section['items'] = collect($section['items'])
                ->filter(fn ($item) => $item['show'] ?? true)
                ->map(function ($item) {
                    $item['is_active'] = request()->routeIs(explode('|', $item['active']));
                    return $item;
                })
                ->values()
                ->all();

            $section['open'] = collect($section['items'])->contains(fn ($item) => $item['is_active']);

            return $section;
        })
        ->filter(fn ($section) => count($section['items']) > 0)
        ->values()
        ->all();

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

    <link rel="stylesheet" href="{{ asset('css/srms-theme.css') }}?v={{ @filemtime(public_path('css/srms-theme.css')) }}">

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

        /* Which side of the business a super admin is working on */
        .workspace-switch {
            display: inline-flex;
            padding: 3px;
            border-radius: 100px;
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .09);
        }

        .workspace-switch a {
            padding: 5px 13px;
            border-radius: 100px;
            font-size: 12.5px;
            font-weight: 600;
            color: #BCA994;
            text-decoration: none;
            line-height: 1.4;
            white-space: nowrap;
            transition: background-color .16s ease, color .16s ease;
        }

        .workspace-switch a:hover { color: #F3EADF; }

        .workspace-switch a.is-on {
            background: var(--accent);
            color: #fff;
        }

        @media (max-width: 860px) {
            .workspace-switch a { padding: 5px 9px; font-size: 11.5px; }
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
            flex-wrap: wrap;
        }

        .nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            /* A fixed height keeps the underline level whether or not the
               link carries a counter. */
            height: 52px;
            padding: 0 13px;
            border: none;
            background: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: color .16s ease;
        }

        .nav-link::after {
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

        .nav-link:hover { color: var(--ink); }

        .nav-link.active { color: var(--accent-dark); }
        .nav-link.active::after { transform: scaleX(1); }

        /* --- A section of the navigation -------------------------------- */

        .nav-group { position: relative; }

        .nav-group__arrow {
            width: 0; height: 0;
            border-inline: 4px solid transparent;
            border-top: 5px solid currentColor;
            opacity: .65;
            transition: transform .18s ease;
        }

        .nav-group.is-open .nav-group__button { color: var(--ink); }
        .nav-group.is-open .nav-group__arrow { transform: rotate(180deg); }

        .nav-menu {
            position: absolute;
            inset-inline-start: 0;
            top: calc(100% - 2px);
            z-index: 50;
            min-width: 214px;
            padding: 6px;
            border-radius: var(--r);
            background: var(--surface);
            border: 1px solid var(--line-strong);
            box-shadow: 0 18px 38px -16px rgba(36, 29, 24, .38);
            animation: nav-drop .16s ease;
        }

        .nav-menu[hidden] { display: none; }

        @keyframes nav-drop {
            from { opacity: 0; transform: translateY(-5px); }
        }

        .nav-menu__item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--r-xs);
            font-size: 14px;
            font-weight: 600;
            color: var(--ink-soft);
            text-decoration: none;
            white-space: nowrap;
        }

        .nav-menu__item span:first-child { margin-inline-end: auto; }
        .nav-menu__item:hover { background: var(--surface-sunk); color: var(--ink); }

        .nav-menu__item.active {
            background: var(--accent-soft);
            color: var(--accent-dark);
        }

        /* Live counters beside a nav link */
        .nav-badge {
            display: inline-block;
            min-width: 19px;
            height: 19px;
            padding: 0 6px;
            border-radius: 100px;
            background: var(--accent);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 19px;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .nav-badge[hidden] { display: none; }

        .nav-badge--quiet {
            background: var(--surface-sunk);
            color: var(--ink-soft);
            border: 1px solid var(--line-strong);
            line-height: 17px;
        }

        /* Sound on/off. A dot means the browser is still waiting for a click
           before it will allow sound. */
        .sound-toggle {
            position: relative;
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .06);
            color: #E4D7C8;
            cursor: pointer;
            padding: 0;
        }

        .sound-toggle:hover { background: rgba(255, 255, 255, .12); }
        .sound-toggle svg { width: 17px; height: 17px; }
        .sound-toggle .bell-off { display: none; }
        .sound-toggle.is-off .bell-on { display: none; }
        .sound-toggle.is-off .bell-off { display: block; }
        .sound-toggle.is-off { color: #8E7C6B; }

        .sound-toggle.is-locked::after {
            content: "";
            position: absolute;
            top: 5px;
            inset-inline-end: 5px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--amber);
            box-shadow: 0 0 0 2px #2A211B;
        }

        /* Pop-up notices for new orders and waiter calls */
        .live-toasts {
            position: fixed;
            inset-inline-end: 20px;
            bottom: 20px;
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .live-toast {
            pointer-events: auto;
            display: block;
            min-width: 240px;
            max-width: 340px;
            padding: 14px 18px;
            border-radius: var(--r);
            background: var(--ink);
            color: #FBF5EE;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 18px 40px -14px rgba(36, 29, 24, .6);
            border-inline-start: 4px solid var(--accent);
            animation: toast-in .28s cubic-bezier(.2, .7, .3, 1);
            transition: opacity .5s ease, transform .5s ease;
        }

        .live-toast.is-leaving { opacity: 0; transform: translateY(8px); }

        @keyframes toast-in {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: none; }
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

        /* --- The assistant ------------------------------------------------
           These live here rather than in the partial: the partial is
           included while the body renders, by which time the head's
           style stack has already been written out and anything pushed
           to it is dropped on the floor.

           Placement uses logical properties throughout, so the bubble
           sits in the bottom-left corner in Arabic and the bottom-right
           in English without a second set of rules. */

        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0;
        }

        .ask-fab {
            position: fixed; z-index: 91;
            inset-block-end: 22px; inset-inline-end: 22px;
            display: inline-flex; align-items: center; gap: 9px;
            padding: 12px 18px 12px 15px;
            border: none; border-radius: 100px;
            background: var(--ink); color: #FBF5EE;
            font: inherit; font-size: 14px; font-weight: 600;
            cursor: pointer;
            box-shadow: 0 14px 30px -10px rgba(36, 29, 24, .55);
            transition: transform .18s cubic-bezier(.2, .7, .3, 1), box-shadow .18s ease;
        }

        .ask-fab:hover { transform: translateY(-2px); box-shadow: 0 18px 34px -10px rgba(36, 29, 24, .6); }
        .ask-fab:active { transform: translateY(0); }
        .ask-fab svg { width: 21px; height: 21px; flex-shrink: 0; }
        .ask-fab__shut { display: none; }

        .ask-fab.is-open .ask-fab__talk { display: none; }
        .ask-fab.is-open .ask-fab__shut { display: block; }
        .ask-fab.is-open .ask-fab__label { display: none; }
        .ask-fab.is-open { padding: 12px; }

        .ask {
            position: fixed; z-index: 90;
            inset-block-end: 84px; inset-inline-end: 22px;
            width: min(calc(100vw - 44px), 380px);
            height: min(calc(100vh - 128px), 540px);
            display: flex; flex-direction: column;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            color: var(--ink);
            box-shadow: 0 30px 70px -24px rgba(36, 29, 24, .5);
            overflow: hidden;
            animation: ask-in .18s cubic-bezier(.2, .7, .3, 1);
        }

        body .ask[hidden] { display: none; }

        @keyframes ask-in { from { opacity: 0; transform: translateY(10px) scale(.98); } }

        .ask__head {
            display: flex; align-items: center; gap: 10px;
            padding: 13px 14px;
            border-bottom: 1px solid var(--line);
            background: var(--surface-sunk);
            flex-shrink: 0;
        }

        .ask__avatar {
            width: 32px; height: 32px; flex-shrink: 0;
            display: grid; place-items: center;
            border-radius: 10px;
            background: var(--ink); color: #FBF5EE;
            font-family: var(--font-display); font-size: 15px; font-weight: 700;
        }

        .ask__who { display: flex; flex-direction: column; gap: 1px; min-width: 0; flex: 1; }
        .ask__who strong { font-size: 14px; font-weight: 600; }
        .ask__who small { font-size: 11.5px; color: var(--muted); }

        .ask__close {
            flex-shrink: 0;
            width: 28px; height: 28px;
            display: grid; place-items: center;
            border: none; border-radius: 8px;
            background: none; color: var(--muted);
            cursor: pointer;
            transition: background-color .16s ease, color .16s ease;
        }

        .ask__close:hover { background: var(--line); color: var(--ink); }
        .ask__close svg { width: 16px; height: 16px; }

        .ask__thread {
            flex: 1; min-height: 0;
            overflow-y: auto;
            padding: 16px 14px;
            display: flex; flex-direction: column; gap: 10px;
            scroll-behavior: smooth;
        }

        .ask-turn {
            max-width: 86%;
            padding: 11px 14px;
            border-radius: 16px;
            font-size: 14px; line-height: 1.55;
            animation: ask-turn-in .2s cubic-bezier(.2, .7, .3, 1);
        }

        @keyframes ask-turn-in { from { opacity: 0; transform: translateY(6px); } }

        .ask-turn--asked {
            align-self: flex-end;
            background: var(--ink); color: #FBF5EE;
            border-end-end-radius: 5px;
        }

        .ask-turn--said {
            align-self: flex-start;
            background: var(--surface-sunk);
            border: 1px solid var(--line);
            border-end-start-radius: 5px;
        }

        .ask-turn--waiting { color: var(--muted); font-size: 13.5px; }

        .ask-answer {
            color: var(--ink);
            font-family: var(--font-display);
            font-size: 26px; font-weight: 600; line-height: 1.2;
        }

        .ask-answer--text { font-size: 18px; line-height: 1.4; }
        .ask-detail { margin-top: 7px; color: var(--ink-soft); font-size: 13px; line-height: 1.6; }
        .ask-note { margin: 0; color: var(--ink-soft); font-size: 13.5px; line-height: 1.6; }

        .ask-link {
            display: inline-flex; margin-top: 12px;
            padding: 7px 13px; border-radius: 100px;
            background: var(--ink); color: #FBF5EE;
            font-size: 12.5px; font-weight: 600; text-decoration: none;
        }

        .ask-examples { display: flex; flex-wrap: wrap; gap: 6px; align-self: flex-start; max-width: 100%; }

        body .ask-example {
            padding: 7px 12px; border-radius: 100px;
            background: var(--surface);
            border: 1px solid var(--line);
            color: var(--ink-soft);
            font: inherit; font-size: 12.5px; text-align: start; cursor: pointer;
            transition: border-color .16s ease, color .16s ease;
        }

        body .ask-example:hover { border-color: var(--accent); color: var(--accent-dark); }

        .ask__compose {
            display: flex; align-items: center; gap: 8px;
            padding: 11px 12px;
            border-top: 1px solid var(--line);
            flex-shrink: 0;
        }

        body .ask__compose input {
            flex: 1; min-width: 0;
            padding: 10px 14px;
            border: 1px solid var(--line); border-radius: 100px;
            background: var(--surface-sunk); box-shadow: none;
            font-family: var(--font-sans); font-size: 14px; color: var(--ink);
        }

        body .ask__compose input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: none;
        }

        .ask__send {
            flex-shrink: 0;
            width: 38px; height: 38px;
            display: grid; place-items: center;
            border: none; border-radius: 50%;
            background: var(--ink); color: #FBF5EE;
            cursor: pointer;
            transition: opacity .16s ease;
        }

        .ask__send:hover { opacity: .85; }
        .ask__send svg { width: 18px; height: 18px; }
        /* The arrow points the way the language runs. */
        [dir="rtl"] .ask__send svg { transform: scaleX(-1); }

        @media (max-width: 720px) {
            .ask-fab { inset-block-end: 16px; inset-inline-end: 16px; padding: 12px; }
            .ask-fab__label { display: none; }

            /* The sheet reaches the bottom of the screen on a phone, so the
               bubble would sit on top of the send button. The header's own
               close button is the way out here. */
            .ask-fab.is-open { display: none; }

            .ask {
                inset-block-end: 0; inset-inline: 0;
                width: 100%; height: min(86vh, 560px);
                border-radius: 20px 20px 0 0;
                border-inline: none; border-block-end: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .ask, .ask-turn { animation: none; }
            .ask-fab { transition: none; }
            .ask__thread { scroll-behavior: auto; }
        }

        @media (max-width: 720px) {
            .app-topbar__inner { min-height: 58px; padding: 9px 0; flex-wrap: wrap; }
            .brand__tag { display: none; }
            .topbar__tools { gap: 7px; }
            .user-chip span:not(.user-chip__avatar) { display: none; }
            .user-chip { padding: 6px; }
            .app-nav__inner { gap: 0; }
            .nav-link { height: 46px; padding: 0 11px; font-size: 13.5px; }
            .nav-menu { min-width: 190px; }
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

                    @if ($isSuper)
                        {{-- A super admin works one side at a time and can move between them. --}}
                        <div class="workspace-switch" role="group" aria-label="{{ __('Workspace') }}">
                            @foreach (\App\Support\Access::WORKSPACES as $option)
                                <a href="{{ route('workspace.switch', $option) }}"
                                   class="{{ $workspace === $option ? 'is-on' : '' }}">
                                    {{ \App\Support\Access::workspaceLabel($option) }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @include('layouts.partials.language-switch')

                    @if ($isStaff)
                        <button type="button" id="sound-toggle" class="sound-toggle" aria-pressed="true" title="{{ __('Sound on') }}">
                            <svg class="bell-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                            <svg class="bell-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8.7 3A6 6 0 0 1 18 8a21.3 21.3 0 0 0 .6 5"/><path d="M17 17H3s3-2 3-9a4.67 4.67 0 0 1 .3-1.7"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><path d="m2 2 20 20"/></svg>
                        </button>
                    @endif

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

        @if ($isStaff)
        <nav class="app-nav">
            <div class="app-nav__inner">

                @foreach ($navSections as $section)
                    @if (count($section['items']) === 1)

                        @php ($only = $section['items'][0])
                        <a href="{{ route($only['route']) }}"
                           class="nav-link {{ $only['is_active'] ? 'active' : '' }}">
                            {{ $only['label'] }}
                            @isset($only['pulse'])
                                <span class="nav-badge {{ ($only['quiet'] ?? false) ? 'nav-badge--quiet' : '' }}"
                                      data-pulse="{{ $only['pulse'] }}" hidden></span>
                            @endisset
                        </a>

                    @else

                        <div class="nav-group" data-nav-group>
                            <button type="button"
                                    class="nav-link nav-group__button {{ $section['open'] ? 'active' : '' }}"
                                    aria-expanded="false" aria-haspopup="true">
                                {{ $section['label'] }}

                                {{-- Counts from inside add up here, so a closed
                                     menu still says something needs attention. --}}
                                <span class="nav-badge" data-badge-sum hidden></span>

                                <span class="nav-group__arrow" aria-hidden="true"></span>
                            </button>

                            <div class="nav-menu" hidden>
                                @foreach ($section['items'] as $item)
                                    <a href="{{ route($item['route']) }}"
                                       class="nav-menu__item {{ $item['is_active'] ? 'active' : '' }}">
                                        <span>{{ $item['label'] }}</span>
                                        @isset($item['pulse'])
                                            <span class="nav-badge {{ ($item['quiet'] ?? false) ? 'nav-badge--quiet' : '' }}"
                                                  data-pulse="{{ $item['pulse'] }}" hidden></span>
                                        @endisset
                                    </a>
                                @endforeach
                            </div>
                        </div>

                    @endif
                @endforeach

            </div>
        </nav>
        @endif

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

    {{-- Whoever runs the place can ask rather than go looking.

         It sits here rather than in the top bar for two reasons: it is a
         floating panel, and as a child of that dark strip it inherited the
         pale text colour meant for the bar, which left the headline of every
         answer almost invisible — and it has to come BEFORE the stack below,
         because a @push into a stack that has already rendered is dropped. --}}
    @if ($showAdmin)
        @include('partials.ask-box')
    @endif

    @stack('scripts')

    @if ($isStaff)
        <div id="live-toasts" class="live-toasts" aria-live="polite"></div>

        <script>
            window.SRMS_LIVE = {
                pulseUrl: @json(route('staff.pulse')),
                kitchenUrl: @json(route('kitchen.orders')),
                alertsUrl: @json($showAdmin ? route("order-change-requests.index") : null),
                loginUrl: @json(route('login')),
                text: {
                    newOrder: @json(__('New order received')),
                    newAlert: @json(__('A table is calling the waiter')),
                    signedOut: @json(__('Your session has ended. Taking you to sign in…')),
                    soundOn: @json(__('Sound on — click to mute')),
                    soundOff: @json(__('Sound off — click to turn on')),
                    soundLocked: @json(__('Click anywhere once so the browser allows sound'))
                }
            };
        </script>
        <script src="{{ asset('js/srms-live.js') }}?v={{ @filemtime(public_path('js/srms-live.js')) }}" defer></script>

        <script>
            /*
             * The navigation's sections. Opening is a click, not a hover, so a
             * finger works as well as a mouse, and only one is ever open.
             */
            (function () {
                'use strict';

                const groups = Array.prototype.slice.call(document.querySelectorAll('[data-nav-group]'));
                if (!groups.length) return;

                function close(group) {
                    group.classList.remove('is-open');
                    group.querySelector('.nav-menu').hidden = true;
                    group.querySelector('.nav-group__button').setAttribute('aria-expanded', 'false');
                }

                function closeAll(except) {
                    groups.forEach(function (group) { if (group !== except) close(group); });
                }

                groups.forEach(function (group) {
                    const button = group.querySelector('.nav-group__button');
                    const menu = group.querySelector('.nav-menu');

                    button.addEventListener('click', function (event) {
                        event.stopPropagation();
                        const opening = menu.hidden;
                        closeAll(group);
                        menu.hidden = !opening;
                        group.classList.toggle('is-open', opening);
                        button.setAttribute('aria-expanded', opening ? 'true' : 'false');
                    });
                });

                document.addEventListener('click', function (event) {
                    if (!event.target.closest('[data-nav-group]')) closeAll();
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') closeAll();
                });
            })();
        </script>
    @endif

</body>

</html>
