@extends('layouts.app')

@section('title', __('Employees'))

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ __('Staff') }}</span>
            <h1>{{ __('Employee Records') }}</h1>
            <p>{{ __('National ID, salary, and what was added or taken off this month.') }}</p>
        </div>

        <form method="GET" class="month-form">
            <input type="month" name="month" value="{{ $month->format('Y-m') }}">
            <button type="submit" class="btn">{{ __('View') }}</button>
        </form>
    </div>

    {{-- Access is one thing, employment is another --}}
    <nav class="kitchen-tabs">
        <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.index') ? 'is-on' : '' }}">{{ __('Staff & Access') }}</a>
        <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'is-on' : '' }}">{{ __('Employee Records') }}</a>
        <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'is-on' : '' }}">{{ __('Attendance') }}</a>
    </nav>

    @if (session('success'))<div class="success-message">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="error-message">{{ session('error') }}</div>@endif

    @if ($errors->any())
        <div class="error-message">
            <div>
                <strong>{{ __('Please check the following') }}</strong>
                <ul style="margin:6px 0 0; padding-inline-start:18px">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('National ID') }}</th>
                    <th>{{ __('Job title') }}</th>
                    <th>{{ __('Salary') }}</th>
                    <th>{{ __('Bonuses') }}</th>
                    <th>{{ __('Deductions') }}</th>
                    <th>{{ __('Net pay') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $record = $user->employeeRecord;
                        $salary = (float) ($record->salary ?? 0);
                        $bonuses = (float) $user->payrollEntries->where('kind', 'bonus')->sum('amount');
                        $deductions = (float) $user->payrollEntries->where('kind', 'deduction')->sum('amount');
                        $net = $salary + $bonuses - $deductions;
                    @endphp

                    <tr>
                        <td><strong>{{ $user->name }}</strong><br><small class="muted-text">{{ $user->email }}</small></td>
                        <td>{{ $record->national_id ?? '—' }}</td>
                        <td>{{ $record->job_title ?? '—' }}</td>
                        <td class="total">{{ number_format($salary, 2) }}</td>
                        <td class="surplus">{{ $bonuses > 0 ? '+' . number_format($bonuses, 2) : '—' }}</td>
                        <td class="shortage">{{ $deductions > 0 ? '−' . number_format($deductions, 2) : '—' }}</td>
                        <td class="total"><strong>{{ number_format($net, 2) }}</strong> <small class="muted-text">{{ __('EGP') }}</small></td>
                        <td>
                            <div class="actions">
                                <button type="button" class="edit-btn" data-open="details-{{ $user->id }}">{{ __('Details') }}</button>
                                <button type="button" class="btn" data-open="pay-{{ $user->id }}">{{ __('Bonus / Deduction') }}</button>
                            </div>
                        </td>
                    </tr>

                    @if ($user->payrollEntries->isNotEmpty())
                        <tr class="entries-row">
                            <td colspan="8">
                                <div class="entries">
                                    @foreach ($user->payrollEntries as $entry)
                                        <span class="entry {{ $entry->isBonus() ? 'is-bonus' : 'is-deduction' }}">
                                            {{ $entry->isBonus() ? '+' : '−' }}{{ number_format((float) $entry->amount, 2) }}
                                            <small>{{ $entry->reason ?: ($entry->isBonus() ? __('Bonus') : __('Deduction')) }} · {{ $entry->happened_on->format('d/m') }}</small>

                                            <form method="POST" action="{{ route('employees.entries.destroy', $entry) }}"
                                                  onsubmit="return confirm('{{ __('Remove this entry?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="{{ __('Delete') }}">&times;</button>
                                            </form>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- One dialog per person: their file, and a new bonus or deduction --}}
    @foreach ($users as $user)
        @php $record = $user->employeeRecord; @endphp

        <div class="modal staff-modal" id="details-{{ $user->id }}" hidden>
            <div class="modal-box">
                <span class="modal-kicker">{{ $user->name }}</span>
                <h2 class="modal-header">{{ __('Employee details') }}</h2>

                <form method="POST" action="{{ route('employees.update', $user) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label>{{ __('National ID') }}</label>
                        <input type="text" name="national_id" value="{{ old('national_id', $record->national_id ?? '') }}" inputmode="numeric" maxlength="20">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Job title') }}</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $record->job_title ?? '') }}" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Phone') }}</label>
                        <input type="tel" name="phone" value="{{ old('phone', $record->phone ?? '') }}" maxlength="20">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Salary') }} <small class="muted-text">({{ __('EGP') }})</small></label>
                        <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $record->salary ?? 0) }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Hired on') }}</label>
                        <input type="date" name="hired_on" value="{{ old('hired_on', $record?->hired_on?->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Notes') }}</label>
                        <textarea name="notes" rows="2" maxlength="1000">{{ old('notes', $record->notes ?? '') }}</textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="cancel-btn" data-close>{{ __('Cancel') }}</button>
                        <button type="submit" class="primary-btn">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal staff-modal" id="pay-{{ $user->id }}" hidden>
            <div class="modal-box">
                <span class="modal-kicker">{{ $user->name }}</span>
                <h2 class="modal-header">{{ __('Bonus / Deduction') }}</h2>

                <form method="POST" action="{{ route('employees.entries.store', $user) }}">
                    @csrf

                    <div class="form-group">
                        <label>{{ __('Kind') }}</label>
                        <select name="kind">
                            <option value="bonus">{{ __('Bonus') }}</option>
                            <option value="deduction">{{ __('Deduction') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Amount') }} <small class="muted-text">({{ __('EGP') }})</small></label>
                        <input type="number" step="0.01" min="0.01" name="amount" required>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Reason') }}</label>
                        <input type="text" name="reason" maxlength="255" placeholder="{{ __('e.g. extra shift, late arrival') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Date') }}</label>
                        <input type="date" name="happened_on" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="cancel-btn" data-close>{{ __('Cancel') }}</button>
                        <button type="submit" class="primary-btn">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection

@push('styles')
<style>
    .kitchen-tabs { display: flex; gap: 8px; margin-bottom: 22px; }
    .kitchen-tabs a {
        padding: 9px 16px; border-radius: 100px;
        background: var(--surface); border: 1px solid var(--line-strong);
        color: var(--ink-soft); text-decoration: none; font-size: 14px; font-weight: 600;
    }
    .kitchen-tabs a.is-on { background: var(--ink); border-color: var(--ink); color: #FBF5EE; }

    .entries-row td { padding-top: 0; border-bottom: 1px solid var(--line); background: #FDFBF8; }
    .entries { display: flex; gap: 8px; flex-wrap: wrap; padding-bottom: 12px; }

    .entry {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 5px 10px; border-radius: 100px;
        font-size: 12.5px; font-weight: 700; font-variant-numeric: tabular-nums;
    }
    .entry small { font-weight: 500; opacity: .8; }
    .entry.is-bonus { background: var(--ok-soft); color: var(--ok); }
    .entry.is-deduction { background: var(--danger-soft); color: var(--danger); }
    .entry form { display: inline; }
    .entry button { background: none; border: 0; cursor: pointer; color: inherit; font-size: 15px; line-height: 1; padding: 0; opacity: .6; }
    .entry button:hover { opacity: 1; }

    .staff-modal { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; padding: 20px; z-index: 80; }
    .staff-modal[hidden] { display: none; }
    .staff-modal .modal-box { width: 100%; max-width: 430px; max-height: 90vh; overflow-y: auto; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        function close(modal) { if (modal) modal.hidden = true; }

        document.addEventListener('click', function (event) {
            var opener = event.target.closest('[data-open]');
            if (opener) {
                var modal = document.getElementById(opener.dataset.open);
                if (modal) modal.hidden = false;
                return;
            }

            if (event.target.closest('[data-close]')) close(event.target.closest('.staff-modal'));
            else if (event.target.classList.contains('staff-modal')) close(event.target);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') document.querySelectorAll('.staff-modal:not([hidden])').forEach(close);
        });
    })();
</script>
@endpush
