<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ __('Table') }} {{ $table->number }} {{ __('QR') }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 40px;
        }

        .qr-container {
            max-width: 400px;
            margin: auto;
        }

        .qr-code {
            margin: 30px 0;
        }

        button {
            padding: 10px 20px;
            cursor: pointer;
        }

        .menu-link {
            font-size: 12px;
            word-break: break-all;
        }
        .menu-link a { color: #BD4E2C; }
        @media print {
            button,
            .menu-link {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="qr-container">

    <h1>{{ __('Table') }} {{ $table->number }}</h1>

    <p>{{ __('Scan to view the menu') }}</p>

    <div class="qr-code">
        {!! $qr !!}
    </div>

    <p>
        {{ __('Table') }} {{ $table->number }}
    </p>

    {{-- The same address the code points at, so it can be opened or tested
         by hand. Left out of the printed sheet. --}}
    <p class="menu-link">
        <a href="{{ route('tables.menu', $table->qr_token) }}">{{ route('tables.menu', $table->qr_token) }}</a>
    </p>

    <button onclick="window.print()">
        {{ __('Print QR') }}
    </button>

</div>

</body>
</html>