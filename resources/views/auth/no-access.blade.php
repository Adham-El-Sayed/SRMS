@extends('layouts.app')

@section('title', __('Waiting for access'))

@section('content')

    <div class="empty" style="max-width:620px;margin:40px auto">
        <div class="empty-icon">🔑</div>

        <h2>{{ __('Your account is not set up yet') }}</h2>

        <p style="margin:10px 0 0">
            {{ __('You are signed in as :email, but a manager still needs to say which part of the system you work with.', ['email' => auth()->user()->email]) }}
        </p>

        <p class="muted-text" style="margin:14px 0 22px">
            {{ __('Ask your manager to open Staff & Access and give your account its role. Then sign in again.') }}
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn">{{ __('Log Out') }}</button>
        </form>
    </div>

@endsection
