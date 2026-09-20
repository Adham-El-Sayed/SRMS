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

        .page-content {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
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

        {{-- QR MENU LOGO --}}
        <a href="{{ request()->url() }}" class="brand">
            SRMS
        </a>


        <div class="nav-links">

            {{-- QR MENU LINK --}}
            <a
                href="{{ request()->url() }}"
                class="active"
            >
                Menu
            </a>

        </div>

    </nav>


    <main class="page-content">

        @yield('content')

    </main>


    @stack('scripts')

</body>

</html>