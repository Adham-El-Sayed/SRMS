<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('Invoice #') }}{{ $order->id }}</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 13px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
        }

        .meta {
            width: 100%;
            margin-bottom: 20px;
        }

        .meta td {
            padding: 4px 0;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.items th {
            text-align: left;
            border-bottom: 2px solid #1f2937;
            padding: 8px 4px;
        }

        table.items td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 4px;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #1f2937;
            border-bottom: none;
            padding-top: 10px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ __('SRMS Restaurant') }}</h1>
        <p>{{ __('Invoice #') }}{{ $order->id }}</p>
    </div>

    <table class="meta">
        <tr>
            <td><strong>{{ __('Table') }}:</strong> {{ __($order->table->number ?? 'N/A') }}</td>
            <td class="text-right"><strong>{{ __('Date') }}:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</td>
        </tr>

        @if($order->client_name)
            <tr>
                <td colspan="2"><strong>{{ __('Client') }}:</strong> {{ $order->client_name }}</td>
            </tr>
        @endif

        <tr>
            <td><strong>{{ __('Payment Method') }}:</strong> {{ __($order->payment_method === 'card' ? 'Visa' : 'Cash') }}</td>
            <td class="text-right"><strong>{{ __('Status') }}:</strong> {{ __(ucfirst($order->payment_status)) }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>{{ __('Item') }}</th>
                <th class="text-right">{{ __('Qty') }}</th>
                <th class="text-right">{{ __('Unit Price') }}</th>
                <th class="text-right">{{ __('Subtotal') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ __($item->product?->name ?? 'Unknown Product') }}
                        @if($item->notes)
                            <br><small>({{ $item->notes }})</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="3">{{ __('Total') }}</td>
                <td class="text-right">{{ number_format($order->total, 2) }} {{ __('EGP') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        {{ __('Thank you for your order.') }}
    </div>

</body>
</html>