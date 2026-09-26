<?php

namespace App\Http\Controllers;

use App\Models\EmployeeRecord;
use App\Models\PayrollEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Employment details and this month's pay. Kept apart from Staff & Access,
 * which is only about what an account may open.
 */
class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $month = $this->month($request);

        $users = User::query()
            ->with([
                'employeeRecord',
                'payrollEntries' => fn ($query) => $query
                    ->whereBetween('happened_on', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->latest('happened_on'),
            ])
            ->orderBy('name')
            ->get();

        return view('staff.employees', [
            'users' => $users,
            'month' => $month,
        ]);
    }

    /** Save the file on one employee. */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'national_id' => [
                'nullable', 'string', 'max:20', 'regex:/^[0-9]{6,20}$/',
                Rule::unique('employee_records', 'national_id')->ignore($user->id, 'user_id'),
            ],
            'job_title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{5,20}$/'],
            'salary' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'hired_on' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        EmployeeRecord::updateOrCreate(['user_id' => $user->id], $data + ['salary' => $data['salary'] ?? 0]);

        return back()->with('success', __('Details saved for :name.', ['name' => $user->name]));
    }

    /** Record a bonus or a deduction. */
    public function storeEntry(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(PayrollEntry::KINDS)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'reason' => ['nullable', 'string', 'max:255'],
            'happened_on' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $user->payrollEntries()->create($data + ['recorded_by' => $request->user()->id]);

        return back()->with('success', $data['kind'] === 'bonus'
            ? __('Bonus recorded for :name.', ['name' => $user->name])
            : __('Deduction recorded for :name.', ['name' => $user->name]));
    }

    public function destroyEntry(PayrollEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('success', __('The entry was removed.'));
    }

    private function month(Request $request): Carbon
    {
        $value = $request->query('month');

        return $value && preg_match('/^\d{4}-\d{2}$/', $value)
            ? Carbon::createFromFormat('Y-m-d', $value . '-01')
            : Carbon::now()->startOfMonth();
    }
}
