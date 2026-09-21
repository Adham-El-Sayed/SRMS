@extends('layouts.app')

@section('title', __('Shift History'))

@section('content')

    <div class="header">
        <div>
            <h1>{{ __('Shift History') }}</h1>
            <p>{{ __('All closed shifts and their reconciliation details.') }}</p>
        </div>
    </div>

    @if($shifts->count() > 0)

        <div class="shifts-table">

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Staff') }}</th>
                        <th>{{ __('Opened') }}</th>
                        <th>{{ __('Closed') }}</th>
                        <th>{{ __('Cash (System)') }}</th>
                        <th>{{ __('Cash (Counted)') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                        <tr>
                            <td>#{{ $shift->id }}</td>
                            <td>{{ __($shift->user->name ?? 'N/A') }}</td>
                            <td>{{ $shift->opened_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $shift->closed_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ number_format($shift->systemCashTotal(), 2) }}</td>
                            <td>{{ number_format($shift->counted_cash, 2) }}</td>
                            <td>
                                @php $diff = $shift->cashDifference(); @endphp

                                @if($diff == 0)
                                    <span class="badge ok">{{ __('Balanced') }}</span>
                                @elseif($diff > 0)
                                    <span class="badge surplus">{{ __('Surplus') }} {{ number_format($diff, 2) }}</span>
                                @else
                                    <span class="badge shortage">{{ __('Shortage') }} {{ number_format(abs($diff), 2) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('shifts.show', $shift) }}">{{ __('Details') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <div class="pagination">
            {{ $shifts->links() }}
        </div>

    @else

        <div class="empty">
            <h2>{{ __('No closed shifts yet') }}</h2>
        </div>

    @endif

@endsection


@push('styles')
<style>

    .header {
        margin-bottom: 25px;
    }

    .header h1 {
        margin: 0;
        font-size: 32px;
    }

    .header p {
        margin: 8px 0 0;
        color: #6b7280;
    }

    .shifts-table {
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

    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        white-space: nowrap;
    }

    .badge.ok {
        background: #dcfce7;
        color: #166534;
    }

    .badge.surplus {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge.shortage {
        background: #fee2e2;
        color: #991b1b;
    }

    .pagination {
        margin-top: 20px;
    }

    .empty {
        background: white;
        padding: 50px;
        border-radius: 14px;
        text-align: center;
        color: #6b7280;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

</style>
@endpush