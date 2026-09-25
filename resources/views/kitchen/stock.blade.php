@extends('layouts.app')

@section('title', __('Availability'))

@section('content')

    <div class="header">
        <div>
            <span class="page-kicker">{{ __('Kitchen') }}</span>
            <h1>{{ __('What We Have') }}</h1>
            <p>{{ __('Mark a dish finished the moment you run out. It stays on the menu, shown as unavailable, and nobody can order it until you bring it back.') }}</p>
        </div>
    </div>

    {{-- The kitchen's two jobs --}}
    <nav class="kitchen-tabs">
        <a href="{{ route('kitchen.orders') }}" class="{{ request()->routeIs('kitchen.orders') ? 'is-on' : '' }}">
            {{ __('Orders') }}
        </a>
        <a href="{{ route('kitchen.stock') }}" class="{{ request()->routeIs('kitchen.stock') ? 'is-on' : '' }}">
            {{ __('Availability') }}
        </a>
    </nav>

    @if (session('success'))<div class="success-message">{{ session('success') }}</div>@endif

    @forelse ($categories as $category)
        @continue($category->products->isEmpty())

        <section class="stock-group">
            <h2 class="section-title">{{ $category->name }}</h2>

            <div class="stock-grid">
                @foreach ($category->products as $product)
                    <form method="POST" action="{{ route('kitchen.stock.toggle', $product) }}"
                          class="stock-item {{ $product->isSoldOut() ? 'is-out' : '' }}">
                        @csrf
                        @method('PATCH')

                        <div class="stock-item__text">
                            <strong>{{ $product->name }}</strong>
                            <small>
                                @if ($product->isSoldOut())
                                    {{ __('Finished :time', ['time' => $product->sold_out_at->diffForHumans()]) }}
                                @else
                                    {{ __('Available') }}
                                @endif
                            </small>
                        </div>

                        <button type="submit" class="{{ $product->isSoldOut() ? 'primary-btn' : 'delete-btn' }}">
                            {{ $product->isSoldOut() ? __('We have it again') : __("It's finished") }}
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    @empty
        <div class="empty">
            <div class="empty-icon">🍽</div>
            <h3>{{ __('There are currently no menu items available.') }}</h3>
        </div>
    @endforelse

@endsection

@push('styles')
<style>
    .kitchen-tabs { display: flex; gap: 8px; margin-bottom: 24px; }

    .kitchen-tabs a {
        padding: 9px 16px; border-radius: 100px;
        background: var(--surface); border: 1px solid var(--line-strong);
        color: var(--ink-soft); text-decoration: none;
        font-size: 14px; font-weight: 600;
    }

    .kitchen-tabs a.is-on { background: var(--ink); border-color: var(--ink); color: #FBF5EE; }

    .stock-group { margin-bottom: 30px; }

    .stock-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 12px; }

    .stock-item {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 14px 16px;
        background: var(--surface); border: 1px solid var(--line);
        border-radius: var(--r-sm); box-shadow: var(--sh-1);
    }

    .stock-item__text strong { display: block; font-size: 15px; }
    .stock-item__text small { color: var(--muted); font-size: 12.5px; }

    /* A finished dish should be obvious from across the kitchen */
    .stock-item.is-out { background: var(--danger-soft); border-color: rgba(174, 53, 39, .25); }
    .stock-item.is-out .stock-item__text strong { color: var(--danger); text-decoration: line-through; }
    .stock-item.is-out .stock-item__text small { color: var(--danger); }

    .stock-item button { white-space: nowrap; }
</style>
@endpush
