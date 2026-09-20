<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'SRMS')</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        .navbar {
            height: 70px;
            background: #1e3a5f;
            display: flex;
            align-items: center;
            padding: 0 5%;
            gap: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .brand {
            color: white;
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
            margin-right: 20px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #dbeafe;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 15px;
            transition: 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: #2563eb;
            color: white;
        }

        .table-info {
            color: #dbeafe;
            font-size: 14px;
            margin-left: auto;
        }

        .page-content {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .site-footer {
            margin-top: 60px;
            padding: 25px 5%;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        @media (max-width: 768px) {

            .navbar {
                height: auto;
                min-height: 70px;
                padding: 15px 5%;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .nav-links {
                width: 100%;
            }

            .table-info {
                margin-left: 0;
            }

            .page-content {
                width: 94%;
                margin: 25px auto;
            }
        }
    </style>

    @stack('styles')

</head>

<body>

    <nav class="navbar">

        {{-- ========================= --}}
        {{-- QR MENU NAVBAR --}}
        {{-- ========================= --}}

        @if(request()->routeIs('tables.menu'))

            <a href="{{ request()->url() }}" class="brand">
                SRMS
            </a>

            <!-- <div class="nav-links">

                <a
                    href="{{ request()->url() }}"
                    class="active"
                >
                    Menu
                </a>

            </div> -->

            @if(isset($table))
                <div class="table-info">
                    Table #{{ $table->number }}
                </div>
            @endif


        {{-- ========================= --}}
        {{-- ADMIN NAVBAR --}}
        {{-- ========================= --}}

        @else

            <a href="{{ route('dashboard') }}" class="brand">
                SRMS
            </a>

            <div class="nav-links">

                <a
                    href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('tables.index') }}"
                    class="{{ request()->routeIs('tables.*') ? 'active' : '' }}"
                >
                    Tables
                </a>

                <a
                    href="{{ route('menu.management') }}"
                    class="{{ request()->routeIs('menu.management*') ? 'active' : '' }}"
                >
                    Menu Management
                </a>

                <a
                    href="{{ route('kitchen.orders') }}"
                    class="{{ request()->routeIs('kitchen.orders') ? 'active' : '' }}"
                >
                    Kitchen
                </a>
                
                    <a            
                    href="{{ route('order-change-requests.index') }}"
                    class="{{ request()->routeIs('order-change-requests.*') ? 'active' : '' }}"
                    id="alerts-link"
                >
                    Alerts
                    <span id="alerts-badge" style="display: none; background: #dc2626; color: white; border-radius: 10px; padding: 2px 7px; font-size: 11px; margin-inline-start: 4px;"></span>
                </a>
                <a
                    href="{{ route('shifts.current') }}"
                    class="{{ request()->routeIs('shifts.*') ? 'active' : '' }}"
                >
                    Shift
                </a>
                <a    
                href="{{ route('shifts.history') }}"
                    class="{{ request()->routeIs('shifts.history') || request()->routeIs('shifts.show') ? 'active' : '' }}"
                >
                    Shift History
                </a>
                <a          
                    href="{{ route('reports.monthly') }}"
                    class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"
                >
                    Reports
                </a>
                       <a         
                    href="{{ route('cash-payments.index') }}"
                    class="{{ request()->routeIs('cash-payments.*') ? 'active' : '' }}"
                >
                    Cash Payments
                </a>
                 <form method="POST" action="{{ route('logout') }}" style="margin-inline-start: auto;">
                    @csrf
                    <button
                        type="submit"
                        style="background: transparent; border: 1px solid #dbeafe; color: #dbeafe; padding: 9px 14px; border-radius: 8px; cursor: pointer; font-size: 15px;"
                    >
                        Log Out
                    </button>
                </form>
            </div>

        @endif

    </nav>


    <main class="page-content">

        @yield('content')

    </main>


    <footer class="site-footer">

        © {{ date('Y') }} SRMS. All rights reserved.

    </footer>


    @stack('scripts')
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
</body>

</html>