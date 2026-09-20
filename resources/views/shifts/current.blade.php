@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">

    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-green-900/40 text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if (! $shift)
        <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 text-center">
            <p class="text-slate-300 mb-4">No shift is currently open.</p>
            <form method="POST" action="{{ route('shifts.open') }}">
                @csrf
                <button type="submit"
                    class="px-6 py-2 rounded-lg bg-teal-600 hover:bg-teal-500 text-white font-medium">
                    Open New Shift
                </button>
            </form>
        </div>
    @else
        <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Current Shift</h2>
            <p class="text-slate-400 text-sm mb-4">
                Opened: {{ $shift->opened_at->format('Y-m-d H:i') }}
            </p>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-slate-800 rounded-lg p-4">
                    <p class="text-slate-400 text-sm">Cash Sales (System)</p>
                    <p class="text-2xl font-bold text-teal-400">
                        {{ number_format($shift->systemCashTotal(), 2) }}
                    </p>
                </div>
                <div class="bg-slate-800 rounded-lg p-4">
                    <p class="text-slate-400 text-sm">Total Visa</p>
                    <p class="text-2xl font-bold text-purple-400">
                        {{ number_format($shift->systemVisaTotal(), 2) }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('shifts.close', $shift) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-300 mb-1">
                        Cash You Actually Have
                    </label>
                    <input type="number" step="0.01" name="counted_cash" required
                        class="w-full rounded-lg bg-slate-800 border-slate-700 text-white">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Notes (optional)</label>
                    <textarea name="notes" rows="2"
                        class="w-full rounded-lg bg-slate-800 border-slate-700 text-white"></textarea>
                </div>
                <button type="submit"
                    class="px-6 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white font-medium">
                    Close Shift
                </button>
            </form>
        </div>
    @endif

</div>
@endsection