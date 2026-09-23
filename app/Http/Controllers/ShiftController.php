<?php

namespace App\Http\Controllers;

use App\Exports\ShiftOrdersExport;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

class ShiftController extends Controller
{
    public function __construct(protected ShiftService $shiftService) {}

    public function current()
    {
        $shift = $this->shiftService->currentOpenShift();

        return view('shifts.current', [
            'shift' => $shift,
            'unpaid' => $shift ? $this->shiftService->unpaidOrders($shift) : collect(),
        ]);
    }

    public function open()
    {
        try {
            $this->shiftService->open(Auth::user());
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('shifts.current')->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('shifts.current')
            ->with('success', __('The shift has been opened successfully.'));
    }

    public function close(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'counted_cash' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $shift = $this->shiftService->close(
                $shift,
                (float) $validated['counted_cash'],
                $validated['notes'] ?? null
            );
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('shifts.current')->with('error', $exception->getMessage());
        }

        $difference = $shift->cashDifference();

        $message = match (true) {
            $difference > 0 => __('Shift closed. The drawer has :amount EGP more than expected.', ['amount' => number_format($difference, 2)]),
            $difference < 0 => __('Shift closed. The drawer is :amount EGP short.', ['amount' => number_format(abs($difference), 2)]),
            default => __('Shift closed. The cash matches exactly.'),
        };

        return redirect()
            ->route('shifts.show', $shift)
            ->with('success', $message);
    }

    public function export(Shift $shift)
    {
        return Excel::download(new ShiftOrdersExport($shift), "shift-{$shift->id}.xlsx");
    }

    public function history()
    {
        $shifts = Shift::query()
            ->where('status', 'closed')
            ->with('user')
            ->latest('closed_at')
            ->paginate(15);

        return view('shifts.history', ['shifts' => $shifts]);
    }

    public function show(Shift $shift)
    {
        $shift->load(['user', 'orders.table']);

        return view('shifts.show', ['shift' => $shift]);
    }
}
