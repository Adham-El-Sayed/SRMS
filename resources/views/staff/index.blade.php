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
                    @php $role = $user->roles->first()?->name; @endphp
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

                                <select name="role" onchange="this.form.submit()"
                                        @disabled($user->is(auth()->user()))>
                                    <option value="">{{ __('No access yet') }}</option>
                                    @foreach ($roles as $option)
                                        <option value="{{ $option }}" @selected($role === $option)>
                                            {{ __(ucfirst($option)) }}
                                        </option>
                                    @endforeach
                                </select>

                                <noscript><button type="submit" class="btn">{{ __('Save') }}</button></noscript>
                            </form>

                            <small class="muted-text">{{ \App\Support\Access::describe($role ?? '') }}</small>
                        </td>

                        <td>
                            @unless ($user->is(auth()->user()))
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
                            <option value="{{ $option }}">{{ __(ucfirst($option)) }}</option>
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
