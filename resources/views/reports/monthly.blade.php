@extends('layouts.app')

@section('title', __('Monthly Report'))

@section('content')

    <div class="header">
        <div>
            <h1>{{ __('Monthly Report') }} — {{ \Carbon\Carbon::create($year, $month, 1)->locale(app()->getLocale())->translatedFormat('F Y') }}</h1>
            <p>{{ __('Revenue and key numbers for the selected month.') }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.monthly') }}" class="month-form">

        <div class="field">
            <label>{{ __('Month') }}</label>
            <select name="month">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($m === $month)>
                        {{ \Carbon\Carbon::create(null, $m, 1)->locale(app()->getLocale())->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label>{{ __('Year') }}</label>
            <select name="year">
                @foreach(range(now()->year - 2, now()->year) as $y)
                    <option value="{{ $y }}" @selected($y === $year)>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="view-button">{{ __('View') }}</button>

        <a
            href="{{ route('reports.monthly.pdf', ['month' => $month, 'year' => $year]) }}"
            target="_blank"
            class="pdf-button"
        >
            ⬇ {{ __('Download PDF') }}
        </a>

    </form>

    <div class="summary-grid">

        <div class="summary-card">
            <p>{{ __('Total Orders') }}</p>
            <strong>{{ $totalOrders }}</strong>
        </div>

        <div class="summary-card">
            <p>{{ __('Completed') }}</p>
            <strong>{{ $completedOrders }}</strong>
        </div>

        <div class="summary-card">
            <p>{{ __('Cancelled') }}</p>
            <strong>{{ $cancelledOrders }}</strong>
        </div>

        <div class="summary-card highlight">
            <p>{{ __('Total Revenue') }}</p>
            <strong>{{ number_format($totalRevenue, 2) }} {{ __('EGP') }}</strong>
        </div>

        <div class="summary-card">
            <p>{{ __('Cash Revenue') }}</p>
            <strong>{{ number_format($cashRevenue, 2) }} {{ __('EGP') }}</strong>
        </div>

        <div class="summary-card">
            <p>{{ __('Visa Revenue') }}</p>
            <strong>{{ number_format($visaRevenue, 2) }} {{ __('EGP') }}</strong>
        </div>

        <div class="summary-card">
            <p>{{ __('Average Order Value') }}</p>
            <strong>{{ number_format($averageOrderValue, 2) }} {{ __('EGP') }}</strong>
        </div>

    </div>

    <h2 class="section-title">{{ __('Top 5 Products') }}</h2>

    <div class="products-table">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Quantity Sold') }}</th>
                    <th>{{ __('Total Sales') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $item)
                    <tr>
                        <td>{{ __($item->product->name ?? 'Unknown') }}</td>
                        <td>{{ $item->total_quantity }}</td>
                        <td>{{ number_format($item->total_sales, 2) }} {{ __('EGP') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">{{ __('No sales this month.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="section-title" style="margin-top:35px;">{{ __('Custom Date Range Export') }}</h2>

    <div class="report-card">

        <form method="GET" action="{{ route('reports.sales.export') }}" class="range-form">

            <div class="field">
                <label>{{ __('From') }}</label>
                <input type="date" name="from" required>
            </div>

            <div class="field">
                <label>{{ __('To') }}</label>
                <input type="date" name="to" required>
            </div>

            <button type="submit" class="pdf-button" style="margin-inline-start:0;">
                ⬇ {{ __('Download Excel') }}
            </button>

        </form>

    </div>

@endsection


@push('styles')
<style>

    .header {
        margin-bottom: 20px;
    }

    .header h1 {
        margin: 0;
        font-size: 28px;
    }

    .header p {
        margin: 8px 0 0;
        color: #6b7280;
    }

    .month-form {
        display: flex;
        align-items: flex-end;
        gap: 14px;
        background: white;
        padding: 18px 20px;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .field label {
        display: block;
        font-size: 13px;
        color: #4b5563;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .field select,
    .field input {
        padding: 9px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .view-button {
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        background: #2563eb;
        color: white;
        font-weight: bold;
        cursor: pointer;
    }

    .view-button:hover {
        background: #1d4ed8;
    }

    .pdf-button {
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        background: #16a34a;
        color: white;
        font-weight: bold;
        text-decoration: none;
        margin-inline-start: auto;
        cursor: pointer;
        font-size: 14px;
    }

    .pdf-button:hover {
        background: #15803d;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .summary-card {
        background: white;
        padding: 18px;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .summary-card.highlight {
        background: #16a34a;
    }

    .summary-card.highlight p,
    .summary-card.highlight strong {
        color: white;
    }

    .summary-card p {
        margin: 0 0 8px;
        color: #6b7280;
        font-size: 13px;
    }

    .summary-card strong {
        font-size: 20px;
        color: #1f2937;
    }

    .section-title {
        font-size: 20px;
        margin-bottom: 15px;
    }

    .products-table {
        background: white;
        border-radius: 14px;
        padding: 10px 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    th {
        text-align: left;
        padding: 12px 8px;
        border-bottom: 2px solid #e5e7eb;
        color: #4b5563;
    }

    td {
        padding: 12px 8px;
        border-bottom: 1px solid #f0f0f0;
    }

    .report-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        max-width: 500px;
    }

    .range-form {
        display: flex;
        align-items: flex-end;
        gap: 14px;
        flex-wrap: wrap;
    }

</style>
@endpush