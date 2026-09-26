@extends('layouts.app')

@section('title', __('Staff & Access'))

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ __('Access') }}</span>
            <h1>{{ __('Staff & Access') }}</h1>
            <p>{{ __('Who can sign in, and what each of them is allowed to open.') }}</p>
        </div>

        <button type="button" class="primary-btn" id="add-staff-btn">{{ __('+ Add Account') }}</button>
    </div>

    {{-- Access is one thing, employment is another --}}
    <nav class="kitchen-tabs">
        <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.index') ? 'is-on' : '' }}">{{ __('Staff & Access') }}</a>
        <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'is-on' : '' }}">{{ __('Employee Records') }}</a>
        <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'is-on' : '' }}">{{ __('Attendance') }}</a>
    </nav>

    @if (session('success'))
        <div class="success-message">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="error-message">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="error-message">
            <div>
                <strong>{{ __('Please check the following') }}</strong>
                <ul style="margin:6px 0 0; padding-inline-start:18px">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Access') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $role = $user->roles->first()?->name;
                        // Your own row, and a super admin's row when you are not one.
                        $locked = $user->is(auth()->user())
                            || (\App\Support\Access::isSuperAdmin($user) && ! \App\Support\Access::isSuperAdmin(auth()->user()));
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            @if ($user->is(auth()->user()))
                                <span class="badge" style="margin-inline-start:6px">{{ __('You') }}</span>
                            @endif
                        </td>

                        <td>{{ $user->email }}</td>

                        <td>
                            <form method="POST" action="{{ route('staff.role', $user) }}" class="role-form">
                                @csrf
                                @method('PATCH')

                                <select name="role" onchange="this.form.submit()" @disabled($locked)>
                                    <option value="">{{ __('No access yet') }}</option>
                                    @foreach ($roles as $option)
                                        <option value="{{ $option }}" @selected($role === $option)>
                                            {{ \App\Support\Access::roleLabel($option) }}
                                        </option>
                                    @endforeach
                                    @if ($role && ! in_array($role, $roles, true))
                                        <option value="{{ $role }}" selected>{{ __('Super admin') }}</option>
                                    @endif
                                </select>

                                <noscript><button type="submit" class="btn">{{ __('Save') }}</button></noscript>
                            </form>

                            <small class="muted-text">{{ \App\Support\Access::describe($role ?? '') }}</small>
                        </td>

                        <td>
                            @unless ($locked)
                                <form method="POST" action="{{ route('staff.destroy', $user) }}"
                                      onsubmit="return confirm('{{ __('Delete this account?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn">{{ __('Delete') }}</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Add account --}}
    <div class="modal" id="staff-modal" hidden>
        <div class="modal-box">
            <span class="modal-kicker">{{ __('New account') }}</span>
            <h2 class="modal-header">{{ __('Add Account') }}</h2>

            <form method="POST" action="{{ route('staff.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">{{ __('Name') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="email">{{ __('Email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="password">{{ __('Password') }}</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">{{ __('Confirm Password') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="form-group">
                    <label for="role">{{ __('Access') }}</label>
                    <select id="role" name="role">
                        <option value="">{{ __('No access yet') }}</option>
                        @foreach ($roles as $option)
                            <option value="{{ $option }}">{{ \App\Support\Access::roleLabel($option) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="cancel-btn" id="staff-cancel">{{ __('Cancel') }}</button>
                    <button type="submit" class="primary-btn">{{ __('Create Account') }}</button>
                </div>
            </form>
        </div>
    </div>

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

    .role-form { display: flex; gap: 8px; align-items: center; margin: 0 0 4px; }
    .role-form select { width: auto; min-width: 150px; }
    #staff-modal { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; padding: 20px; z-index: 80; }
    #staff-modal[hidden] { display: none; }
    #staff-modal .modal-box { width: 100%; max-width: 440px; max-height: 90vh; overflow: auto; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var modal = document.getElementById('staff-modal');
        var open = document.getElementById('add-staff-btn');
        var cancel = document.getElementById('staff-cancel');
        if (!modal || !open) return;

        open.addEventListener('click', function () { modal.hidden = false; });
        cancel.addEventListener('click', function () { modal.hidden = true; });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.hidden = true; });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') modal.hidden = true; });

        @if ($errors->any() && old('email'))
            modal.hidden = false;
        @endif
    })();
</script>
@endpush
