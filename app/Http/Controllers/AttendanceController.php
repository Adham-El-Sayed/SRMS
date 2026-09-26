<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Support\Access;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Who worked today, and who did not.
 *
 * A day is marked one person at a time. Nothing is assumed: an unmarked day
 * is shown as unmarked rather than counted as present, because counting a
 * day nobody looked at would put absences in a report that never happened.
 */
class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $day = $this->dayFrom($request);
        $month = $day->copy()->startOfMonth();

        $staff = User::query()
            ->with(['attendance' => fn ($q) => $q->whereDate('day', $day)])
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => Access::workspaceFor($user) !== 'none')
            ->values();

        // One query for the month rather than one per person.
        $absences = Attendance::query()
            ->where('status', Attendance::ABSENT)
            ->whereYear('day', $month->year)
            ->whereMonth('day', $month->month)
            ->selectRaw('user_id, count(*) as days')
            ->groupBy('user_id')
            ->pluck('days', 'user_id');

        $leaves = Attendance::query()
            ->where('status', Attendance::LEAVE)
            ->whereYear('day', $month->year)
            ->whereMonth('day', $month->month)
            ->selectRaw('user_id, count(*) as days')
            ->groupBy('user_id')
            ->pluck('days', 'user_id');

        return view('staff.attendance', [
            'staff' => $staff,
            'day' => $day,
            'month' => $month,
            'absences' => $absences,
            'leaves' => $leaves,
        ]);
    }

    public function mark(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'day' => ['required', 'date'],
            'status' => ['required', Rule::in(Attendance::STATUSES)],
            'arrived_at' => ['nullable', 'date_format:H:i'],
            'left_at' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $day = Carbon::parse($data['day'])->startOfDay();

        if ($day->isFuture()) {
            return back()->with('error', __('That day has not happened yet.'));
        }

        // Times belong to a day somebody actually worked.
        $present = $data['status'] === Attendance::PRESENT;

        $fields = [
            'status' => $data['status'],
            'arrived_at' => $present && ! empty($data['arrived_at'])
                ? $day->copy()->setTimeFromTimeString($data['arrived_at']) : null,
            'left_at' => $present && ! empty($data['left_at'])
                ? $day->copy()->setTimeFromTimeString($data['left_at']) : null,
            'note' => $data['note'] ?? null,
            'recorded_by' => $request->user()->id,
        ];

        // Looked up by whereDate rather than by an exact match: the column
        // is a date but the database keeps a time on it, so comparing
        // against "2026-09-26" would never find "2026-09-26 00:00:00" and
        // every correction would try to insert a second row for the day.
        $existing = Attendance::where('user_id', $user->id)->whereDate('day', $day)->first();

        if ($existing) {
            $existing->update($fields);
        } else {
            Attendance::create($fields + ['user_id' => $user->id, 'day' => $day]);
        }

        return back()->with('success', __(':name marked as :status.', [
            'name' => $user->name,
            'status' => Attendance::label($data['status']),
        ]));
    }

    public function clear(Request $request, User $user): RedirectResponse
    {
        $day = Carbon::parse($request->input('day', today()))->startOfDay();

        Attendance::where('user_id', $user->id)->whereDate('day', $day)->delete();

        return back()->with('success', __('Mark removed for :name.', ['name' => $user->name]));
    }

    private function dayFrom(Request $request): Carbon
    {
        try {
            $day = Carbon::parse($request->query('day', today()))->startOfDay();
        } catch (\Throwable) {
            $day = today();
        }

        return $day->isFuture() ? today() : $day;
    }
}
