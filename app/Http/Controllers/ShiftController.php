<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\ShiftOrdersExport;
use Maatwebsite\Excel\Facades\Excel;

class ShiftController extends Controller
{
    public function __construct(protected ShiftService $shiftService) {}

    public function current()
    {
        $shift = $this->shiftService->currentOpenShift();

        return view('shifts.current', [
            'shift' => $shift,
        ]);
    }

    public function open()
    {
        $this->shiftService->open(Auth::user());

        return redirect()
            ->route('shifts.current')
            ->with('success', 'The shift has been opened successfully.');
    }

    public function close(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'counted_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $shift = $this->shiftService->close(
            $shift,
            $validated['counted_cash'],
            $validated['notes'] ?? null
        );

        return redirect()
            ->route('shifts.current')
            ->with('success', 'The shift has been closed. The teams:' . $shift->cashDifference());
    }
        public function export(Shift $shift)
    {
        return Excel::download(
            new ShiftOrdersExport($shift),
            "shift-{$shift->id}.xlsx"
        );
    }
        public function history()
    {
        $shifts = Shift::query()
            ->where('status', 'closed')
            ->with('user')
            ->latest('closed_at')
            ->paginate(15);

        return view('shifts.history', [
            'shifts' => $shifts,
        ]);
    }

    public function show(Shift $shift)
    {
        $shift->load(['user', 'orders.table']);

        return view('shifts.show', [
            'shift' => $shift,
        ]);
    }
}