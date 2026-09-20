@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')

    <div class="header">
        <div>
            <h1>Sales Report</h1>
            <p>Export orders for a date range as an Excel file.</p>
        </div>
    </div>

    <div class="report-card">

        <form method="GET" action="{{ route('reports.sales.export') }}">

            <div class="field">
                <label>From</label>
                <input type="date" name="from" required>
            </div>

            <div class="field">
                <label>To</label>
                <input type="date" name="to" required>
            </div>

            <button type="submit" class="export-button">
                Download Excel Report
            </button>

        </form>

    </div>

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

    .report-card {
        background: white;
        border-radius: 14px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        max-width: 420px;
    }

    .field {
        margin-bottom: 16px;
    }

    .field label {
        display: block;
        font-size: 14px;
        color: #4b5563;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .field input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .export-button {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 10px;
        background: #16a34a;
        color: white;
        font-weight: bold;
        font-size: 15px;
        cursor: pointer;
    }

    .export-button:hover {
        background: #15803d;
    }

</style>
@endpush