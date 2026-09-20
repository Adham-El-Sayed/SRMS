<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Table {{ $table->number }} QR</title>

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

        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="qr-container">

    <h1>Table {{ $table->number }}</h1>

    <p>Scan to view the menu</p>

    <div class="qr-code">
        {!! $qr !!}
    </div>

    <p>
        Table {{ $table->number }}
    </p>

    <button onclick="window.print()">
        Print QR
    </button>

</div>

</body>
</html>