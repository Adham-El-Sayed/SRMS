<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monthly Report — {{ $monthName }}</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 13px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0 0 4px;
            font-size: 22px;
        }

        .header p {
            margin: 0;
            color: #6b7280;
        }

        table.summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table.summary td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            width: 25%;
        }

        table.summary .label {
            color: #6b7280;
            font-size: 11px;
            display: block;
            margin-bottom: 4px;
        }

        table.summary .value {
            font-size: 16px;
            font-weight: bold;
        }

        h2 {
            font-size: 15px;
            border-bottom: 1px solid #1f2937;
            padding-bottom: 6px;
        }

        table.products {
            width: 100%;
            border-collapse: collapse;
        }

        table.products th {
            text-align: left;
            border-bottom: 2px solid #1f2937;
            padding: 8px 4px;
        }

        table.products td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 4px;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>SRMS Restaurant — Monthly Report</h1>
        <p>{{ $monthName }}</p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="label">Total Orders</span>
                <span class="value">{{ $totalOrders }}</span>
            </td>
            <td>
                <span class="label">Completed</span>
                <span class="value">{{ $completedOrders }}</span>
            </td>
            <td>
                <span class="label">Cancelled</span>
                <span class="value">{{ $cancelledOrders }}</span>
            </td>
            <td>
                <span class="label">Avg Order Value</span>
                <span class="value">{{ number_format($averageOrderValue, 2) }} EGP</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Total Revenue</span>
                <span class="value">{{ number_format($totalRevenue, 2) }} EGP</span>
            </td>
            <td>
                <span class="label">Cash Revenue</span>
                <span class="value">{{ number_format($cashRevenue, 2) }} EGP</span>
            </td>
            <td>
                <span class="label">Visa Revenue</span>
                <span class="value">{{ number_format($visaRevenue, 2) }} EGP</span>
            </td>
            <td></td>
        </tr>
    </table>

    <h2>Top 5 Products</h2>

    <table class="products">
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Quantity Sold</th>
                <th class="text-right">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProducts as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-right">{{ $item->total_quantity }}</td>
                    <td class="text-right">{{ number_format($item->total_sales, 2) }} EGP</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No sales this month.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>

</body>
</html>