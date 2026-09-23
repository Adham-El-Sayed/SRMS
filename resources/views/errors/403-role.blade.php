@extends('layouts.app')

@section('title', __('Not allowed'))

@section('content')

    <div class="empty" style="max-width:560px;margin:40px auto">
        <div class="empty-icon">🚫</div>
        <h2>{{ __('This page is not part of your job') }}</h2>
        <p style="margin:10px 0 20px">{{ __('Your account does not have access to this page. If you think it should, ask a manager.') }}</p>
        <a href="{{ \App\Support\Access::homeUrlFor(auth()->user()) }}" class="primary-btn">{{ __('Back to my pages') }}</a>
    </div>

@endsection
