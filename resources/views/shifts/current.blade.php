@extends('layouts.app')

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ __('Shift') }}</span>
            <h1>{{ __('Current Shift') }}</h1>
            @if ($shift)
                <p>{{ __('Opened') }}: {{ $shift->opened_at->format('Y-m-d H:i') }}</p>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="success-message">{{ session('success') }}</div>
    @endif

    @if (! $shift)

        <div class="empty shift-empty">
            <div class="empty-icon">⏱</div>
            <h3>{{ __('No shift is currently open.') }}</h3>
            <form method="POST" action="{{ route('shifts.open') }}" style="margin-top:18px">
                @csrf
                <button type="submit" class="primary-btn">{{ __('Open New Shift') }}</button>
            </form>
        </div>

    @else

        <div class="summary-grid shift-grid">
            <div class="summary-card">
                <div class="label">{{ __('Cash Sales (System)') }}</div>
                <div class="value">{{ number_format($shift->systemCashTotal(), 2) }} <small class="muted-text">{{ __('EGP') }}</small></div>
            </div>
            <div class="summary-card">
                <div class="label">{{ __('Total Visa') }}</div>
                <div class="value">{{ number_format($shift->systemVisaTotal(), 2) }} <small class="muted-text">{{ __('EGP') }}</small></div>
            </div>
        </div>

        <div class="card shift-close">
            <h2 style="margin:0 0 18px;font-size:20px">{{ __('Close Shift') }}</h2>

            <form method="POST" action="{{ route('shifts.close', $shift) }}">
                @csrf
                <div class="form-group">
                    <label for="counted_cash">{{ __('Cash You Actually Have') }}</label>
                    <input type="number" step="0.01" name="counted_cash" id="counted_cash" required>
                </div>
                <div class="form-group">
                    <label for="notes">{{ __('Notes (optional)') }}</label>
                    <textarea name="notes" id="notes" rows="2"></textarea>
                </div>
                <button type="submit" class="delete-btn">{{ __('Close Shift') }}</button>
            </form>
        </div>

    @endif

@endsection

@push('styles')
<style>
    .shift-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 22px; max-width: 760px; }
    .shift-close { max-width: 760px; }
    .shift-empty { max-width: 760px; }
</style>
@endpush
