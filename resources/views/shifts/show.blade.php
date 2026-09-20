@extends('layouts.app')

@section('title', 'Shift #' . $shift->id)

@section('content')

    <div class="header">
        <div>
            <h1>Shift #{{ $shift->id }}</h1>
            <p>
                {{ $shift->user->name ?? 'N/A' }} —
                {{ $shift->opened_at->format('Y-m-d H:i') }}
                to
                {{ $shift->closed_at?->format('Y-m-d H:i') ?? 'Still open' }}
            </p>
        </div>

        <a href="{{ route('shifts.history') }}" class="back-link">
            ← Back to History
        </a>
        
        <a href="{{ route('shifts.export', $shift) }}" class="back-link">
            ⬇ Export to Excel
        </a>
    </div>

    <div class="summary-grid">

        <div class="summary-card">
            <p>Cash (System)</p>
            <strong>{{ number_format($shift->systemCashTotal(), 2) }} EGP</strong>
        </div>

        <div class="summary-card">
            <p>Cash (Counted)</p>
            <strong>{{ number_format($shift->counted_cash, 2) }} EGP</strong>
        </div>

        <div class="summary-card">
            <p>Total Visa</p>
            <strong>{{ number_format($shift->systemVisaTotal(), 2) }} EGP</strong>
        </div>

        <div class="summary-card">
            <p>Difference</p>

            @php $diff = $shift->cashDifference(); @endphp

            @if($diff == 0)
                <strong class="ok">Balanced (no difference)</strong>
            @elseif($diff > 0)
                <strong class="surplus">Surplus {{ number_format($diff, 2) }} EGP</strong>
            @else
                <strong class="shortage">Shortage {{ number_format(abs($diff), 2) }} EGP</strong>
            @endif
        </div>

    </div>

    @if($shift->notes)
        <div class="notes-box">
            <strong>Notes:</strong> {{ $shift->notes }}
        </div>
    @endif

    <h2 class="section-title">Shift Orders ({{ $shift->orders->count() }})</h2>

    <div class="orders-table">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Table</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shift->orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->table->number ?? 'N/A' }}</td>
                        <td>{{ $order->payment_method === 'card' ? 'Visa' : 'Cash' }}</td>
                        <td>{{ ucfirst($order->payment_status) }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ number_format($order->total, 2) }} EGP</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection


@push('styles')
<style>

    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 25px;
        gap: 20px;
    }

    .header h1 {
        margin: 0;
        font-size: 30px;
    }

    .header p {
        margin: 8px 0 0;
        color: #6b7280;
    }

    .back-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: bold;
        white-space: nowrap;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .summary-card {
        background: white;
        padding: 18px;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
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

    .summary-card strong.ok {
        color: #166534;
    }

    .summary-card strong.surplus {
        color: #1e40af;
    }

    .summary-card strong.shortage {
        color: #991b1b;
    }

    .notes-box {
        background: white;
        padding: 15px 18px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .section-title {
        font-size: 20px;
        margin-bottom: 15px;
    }

    .orders-table {
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

</style>
@endpush