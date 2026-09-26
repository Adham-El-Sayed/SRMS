@extends('layouts.app')

@section('title', __('Attendance'))

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ __('Staff') }}</span>
            <h1>{{ __('Attendance') }}</h1>
            <p>{{ __('Who worked on a given day. A day nobody has marked stays unmarked — it is never counted as an absence.') }}</p>
        </div>

        <form method="GET" class="month-form">
            <input type="date" name="day" value="{{ $day->toDateString() }}" max="{{ today()->toDateString() }}">
            <button type="submit" class="btn">{{ __('View') }}</button>
        </form>
    </div>

    <nav class="kitchen-tabs">
        <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.index') ? 'is-on' : '' }}">{{ __('Staff & Access') }}</a>
        <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'is-on' : '' }}">{{ __('Employee Records') }}</a>
        <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'is-on' : '' }}">{{ __('Attendance') }}</a>
    </nav>

    @if (session('success'))<div class="success-message">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="error-message">{{ session('error') }}</div>@endif

    <p class="day-note">
        {{ $day->isToday() ? __('Today') : $day->translatedFormat('l j F Y') }}
        &middot;
        {{ __('Absences counted for :month', ['month' => $month->translatedFormat('F Y')]) }}
    </p>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Role') }}</th>
                    <th>{{ __('This day') }}</th>
                    <th>{{ __('Hours') }}</th>
                    <th>{{ __('Absent this month') }}</th>
                    <th>{{ __('On leave') }}</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @forelse ($staff as $person)
                    @php ($mark = $person->attendance->first())

                    <tr>
                        <td><strong>{{ $person->name }}</strong></td>
                        {{-- roleLabel takes the role's name, not the person --}}
                        <td>{{ \App\Support\Access::roleLabel($person->getRoleNames()->first() ?? '') }}</td>

                        <td>
                            @if ($mark)
                                <span class="mark mark--{{ $mark->status }}">{{ \App\Models\Attendance::label($mark->status) }}</span>
                                @if ($mark->note)<span class="mark-note">{{ $mark->note }}</span>@endif
                            @else
                                <span class="mark mark--none">{{ __('Not marked') }}</span>
                            @endif
                        </td>

                        <td>
                            @if ($mark && $mark->hours() !== null)
                                {{ $mark->hours() }}
                                <small>{{ __('h') }}</small>
                                <span class="mark-note">{{ $mark->arrived_at->format('H:i') }} – {{ $mark->left_at->format('H:i') }}</span>
                            @else
                                <span class="muted-text">—</span>
                            @endif
                        </td>

                        <td>
                            @php ($absent = (int) ($absences[$person->id] ?? 0))
                            <span class="{{ $absent > 2 ? 'tally tally--high' : 'tally' }}">{{ $absent }}</span>
                        </td>

                        <td><span class="tally">{{ (int) ($leaves[$person->id] ?? 0) }}</span></td>

                        <td class="row-actions">
                            <button type="button" class="btn mark-btn"
                                    data-user="{{ $person->id }}"
                                    data-name="{{ $person->name }}"
                                    data-status="{{ $mark->status ?? '' }}"
                                    data-arrived="{{ $mark?->arrived_at?->format('H:i') }}"
                                    data-left="{{ $mark?->left_at?->format('H:i') }}"
                                    data-note="{{ $mark->note ?? '' }}">
                                {{ $mark ? __('Change') : __('Mark') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted-text">{{ __('No staff accounts yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- Marking someone's day --}}
    <div class="sheet" id="mark-sheet" hidden>
        <div class="sheet__box" role="dialog" aria-modal="true" aria-labelledby="mark-title">

            <div class="sheet__head">
                <div>
                    <h2 id="mark-title">{{ __('Mark') }} <span id="mark-name"></span></h2>
                    <p class="muted-text">{{ $day->isToday() ? __('Today') : $day->translatedFormat('j F Y') }}</p>
                </div>

                <button type="button" class="sheet__close" data-close aria-label="{{ __('Close') }}">&times;</button>
            </div>

            <form method="POST" id="mark-form">
                @csrf
                <input type="hidden" name="day" value="{{ $day->toDateString() }}">

                <div class="sheet__body">
                    <div class="choice-grid">
                        @foreach (\App\Models\Attendance::STATUSES as $status)
                            <label class="choice">
                                <input type="radio" name="status" value="{{ $status }}" @checked($status === 'present')>
                                <span class="choice__box">
                                    <span class="choice__name">{{ \App\Models\Attendance::label($status) }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div id="hours-fields">
                        <div class="two-up">
                            <div class="field">
                                <label for="arrived_at">{{ __('Arrived') }}</label>
                                <input type="time" id="arrived_at" name="arrived_at">
                            </div>

                            <div class="field">
                                <label for="left_at">{{ __('Left') }}</label>
                                <input type="time" id="left_at" name="left_at">
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="note">{{ __('Note') }}</label>
                        <input type="text" id="note" name="note" maxlength="255"
                               placeholder="{{ __('Optional — a reason, if there is one') }}">
                    </div>
                </div>

                <div class="sheet__foot">
                    <button type="submit" class="submit-button">{{ __('Save') }}</button>
                </div>
            </form>

            <form method="POST" id="clear-form" class="clear-form" hidden>
                @csrf
                @method('DELETE')
                <input type="hidden" name="day" value="{{ $day->toDateString() }}">
                <button type="submit" class="link-button">{{ __('Remove this mark') }}</button>
            </form>
        </div>
    </div>

@endsection


@push('styles')
<style>
    .header { display: flex; align-items: flex-start; gap: 20px; flex-wrap: wrap; margin-bottom: 22px; }
    .header h1 { margin: 0; font-size: 32px; }
    .header p { margin: 8px 0 0; color: var(--muted); max-width: 66ch; }
    .month-form { margin-inline-start: auto; display: flex; gap: 8px; align-items: center; }

    .kitchen-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
    .kitchen-tabs a {
        padding: 9px 16px; border-radius: 100px;
        background: var(--surface); border: 1px solid var(--line-strong);
        color: var(--ink-soft); text-decoration: none; font-size: 14px; font-weight: 600;
    }
    .kitchen-tabs a.is-on { background: var(--ink); border-color: var(--ink); color: #FBF5EE; }

    .day-note { margin: 0 0 14px; color: var(--muted); font-size: 13.5px; }

    .table-wrapper { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-md, 13px); overflow-x: auto; box-shadow: var(--sh-1); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: start; font-size: 11.5px; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); padding: 14px 16px; border-bottom: 1px solid var(--line); white-space: nowrap; }
    html[lang="ar"] th { letter-spacing: 0; text-transform: none; font-size: 13px; }
    td { padding: 14px 16px; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }

    .mark {
        display: inline-flex; padding: 4px 11px; border-radius: 100px;
        font-size: 12.5px; font-weight: 700; white-space: nowrap;
    }
    .mark--present { background: var(--ok-soft); color: var(--ok); }
    .mark--absent  { background: var(--danger-soft); color: var(--danger); }
    .mark--leave   { background: var(--info-soft); color: var(--info); }
    .mark--none    { background: var(--surface-sunk); color: var(--muted); }

    .mark-note { display: block; margin-top: 4px; font-size: 12px; color: var(--muted); }

    .tally {
        display: inline-grid; place-items: center; min-width: 28px; height: 26px; padding: 0 8px;
        border-radius: 100px; background: var(--surface-sunk); color: var(--ink-soft);
        font-weight: 700; font-size: 13px;
    }
    .tally--high { background: var(--danger-soft); color: var(--danger); }

    .row-actions { text-align: end; white-space: nowrap; }

    .two-up { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    .choice-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
    .choice { display: block; cursor: pointer; }
    .choice input { position: absolute; opacity: 0; pointer-events: none; }
    .choice__box {
        display: block; padding: 13px 12px; text-align: center;
        border-radius: var(--r-md, 13px); background: var(--surface);
        border: 1.5px solid var(--line-strong);
        transition: border-color .16s ease, background-color .16s ease;
    }
    .choice__name { font-weight: 600; font-size: 14px; }
    .choice input:checked + .choice__box { border-color: var(--accent); background: var(--accent-soft); }

    .field { margin-bottom: 14px; }
    .field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--ink-soft); }

    .clear-form { padding: 0 20px 18px; text-align: center; }
    .link-button {
        background: none; border: none; color: var(--danger);
        font: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;
        text-decoration: underline; text-underline-offset: 3px;
    }

    .sheet { position: fixed; inset: 0; z-index: 70; display: flex; align-items: center; justify-content: center; padding: 18px; background: rgba(36,29,24,.5); }
    .sheet[hidden] { display: none; }
    .sheet__box { width: min(100%, 460px); max-height: 92vh; overflow-y: auto; background: var(--surface); border-radius: var(--r-lg, 18px); box-shadow: 0 24px 60px -20px rgba(36,29,24,.45); }
    .sheet__head { display: flex; align-items: flex-start; gap: 12px; padding: 20px 20px 14px; border-bottom: 1px solid var(--line); }
    .sheet__head h2 { margin: 0; font-size: 20px; }
    .sheet__close { margin-inline-start: auto; background: none; border: none; font-size: 26px; line-height: 1; color: var(--muted); cursor: pointer; }
    .sheet__body { padding: 18px 20px; }
    .sheet__foot { padding: 0 20px 18px; }

    @media (max-width: 620px) {
        .choice-grid { grid-template-columns: 1fr; }
        .two-up { grid-template-columns: 1fr; }
        .month-form { margin-inline-start: 0; }
    }
</style>
@endpush


@push('scripts')
<script>
    (function () {
        'use strict';

        var sheet = document.getElementById('mark-sheet');
        var form = document.getElementById('mark-form');
        var clearForm = document.getElementById('clear-form');
        var nameEl = document.getElementById('mark-name');
        var hours = document.getElementById('hours-fields');

        var markUrl = @json(route('attendance.mark', '__ID__'));
        var clearUrl = @json(route('attendance.clear', '__ID__'));

        /* Times only make sense for someone who was actually here. */
        function syncHours() {
            var picked = form.querySelector('input[name="status"]:checked');
            hours.hidden = !picked || picked.value !== 'present';
        }

        document.querySelectorAll('.mark-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var id = button.dataset.user;

                form.action = markUrl.replace('__ID__', id);
                clearForm.action = clearUrl.replace('__ID__', id);
                nameEl.textContent = button.dataset.name;

                var status = button.dataset.status || 'present';
                var chosen = form.querySelector('input[name="status"][value="' + status + '"]');
                if (chosen) chosen.checked = true;

                form.querySelector('#arrived_at').value = button.dataset.arrived || '';
                form.querySelector('#left_at').value = button.dataset.left || '';
                form.querySelector('#note').value = button.dataset.note || '';

                clearForm.hidden = !button.dataset.status;

                syncHours();
                sheet.hidden = false;
            });
        });

        form.querySelectorAll('input[name="status"]').forEach(function (radio) {
            radio.addEventListener('change', syncHours);
        });

        function close() { sheet.hidden = true; }

        sheet.querySelector('[data-close]').addEventListener('click', close);
        sheet.addEventListener('click', function (e) { if (e.target === sheet) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    })();
</script>
@endpush
