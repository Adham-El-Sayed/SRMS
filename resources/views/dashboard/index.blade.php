@extends('layouts.app')

@section('title', 'Restaurant Dashboard')

@push('styles')
<style>

    .header {
        margin-bottom: 30px;
    }

    .header h1 {
        margin: 0;
        font-size: 32px;
    }

    .header p {
        color: #6b7280;
        margin-top: 8px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .card {
        background: white;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .card-title {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .card-value {
        font-size: 30px;
        font-weight: bold;
    }

    .revenue {
        color: #16a34a;
    }

    .section-title {
        font-size: 24px;
        margin: 35px 0 20px;
    }

    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
    }

    .table-card {
        background: white;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .status-name {
        font-size: 16px;
        font-weight: bold;
    }

    .status-count {
        font-size: 28px;
        font-weight: bold;
    }

    .available {
        color: #16a34a;
    }

    .occupied {
        color: #dc2626;
    }

    .reserved {
        color: #d97706;
    }

    .recent-orders {
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    th {
        background: #f9fafb;
        font-size: 14px;
        color: #6b7280;
    }

    tr:last-child td {
        border-bottom: none;
    }

    .order-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: bold;
        text-transform: capitalize;
    }

    .order-status.pending {
        background: #fff3cd;
        color: #856404;
    }

    .order-status.confirmed {
        background: #cfe2ff;
        color: #084298;
    }

    .order-status.preparing {
        background: #e2d9f3;
        color: #59359a;
    }

    .order-status.ready,
    .order-status.completed {
        background: #d1e7dd;
        color: #0f5132;
    }

    .order-status.cancelled {
        background: #f8d7da;
        color: #842029;
    }

    .empty {
        padding: 30px;
        text-align: center;
        color: #6b7280;
    }

</style>
@endpush


@section('content')

    <div class="header">
        <h1>Restaurant Dashboard</h1>
        <p>Overview of your restaurant activity.</p>
    </div>


    <div class="stats-grid">

        <div class="card">
            <div class="card-title">Total Orders</div>
            <div class="card-value">{{ $totalOrders }}</div>
        </div>

        <div class="card">
            <div class="card-title">Active Orders</div>
            <div class="card-value">{{ $activeOrders }}</div>
        </div>

        <div class="card">
            <div class="card-title">Preparing Orders</div>
            <div class="card-value">{{ $preparingOrders }}</div>
        </div>

        <div class="card">
            <div class="card-title">Completed Revenue</div>

            <div class="card-value revenue">
                {{ number_format($totalRevenue, 2) }} EGP
            </div>
        </div>

    </div>


    <h2 class="section-title">Tables Status</h2>

    <div class="tables-grid">

        <div class="table-card">
            <div class="status-row">
                <span class="status-name available">Available</span>
                <span class="status-count">{{ $availableTables }}</span>
            </div>
        </div>

        <div class="table-card">
            <div class="status-row">
                <span class="status-name occupied">Occupied</span>
                <span class="status-count">{{ $occupiedTables }}</span>
            </div>
        </div>

        <div class="table-card">
            <div class="status-row">
                <span class="status-name reserved">Reserved</span>
                <span class="status-count">{{ $reservedTables }}</span>
            </div>
        </div>

    </div>


    <h2 class="section-title">Recent Orders</h2>

    <div class="recent-orders">

        @if($recentOrders->count() > 0)

            <table>

                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Table</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Created At</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($recentOrders as $order)

                        <tr>

                            <td>#{{ $order->id }}</td>

                            <td>
                                {{ $order->table?->number ?? 'N/A' }}
                            </td>

                            <td>
                                <span class="order-status {{ $order->status }}">
                                    {{ $order->status }}
                                </span>
                            </td>

                            <td>
                                {{ number_format($order->total, 2) }} EGP
                            </td>

                            <td>
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="empty">
                No orders found.
            </div>

        @endif

    </div>

@endsection