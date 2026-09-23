{{-- Pending waiter requests. Rendered on page load and again by the live refresh. --}}
@if($requests->count() > 0)

    <div class="alerts">

        @foreach($requests as $req)

            <div class="alert-card">

                <div class="alert-info">
                    <strong>{{ __('Order #') }}{{ $req->order_id }}</strong>
                    — {{ __('Table') }} {{ $req->order?->table?->number ?? __('N/A') }}
                    <div class="alert-time">
                        {{ __('Requested') }} {{ $req->created_at->diffForHumans() }}
                    </div>
                </div>

                <form method="POST" action="{{ route('order-change-requests.resolve', $req) }}">
                    @csrf
                    <button type="submit" class="resolve-button">
                        {{ __('Mark as Handled') }}
                    </button>
                </form>

            </div>

        @endforeach

    </div>

@else

    <div class="empty">
        <h2>{{ __('No pending alerts') }}</h2>
    </div>

@endif
