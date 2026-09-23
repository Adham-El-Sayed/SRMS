<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // A table places a handful of orders an evening; ten a minute from one
        // phone is already generous and stops anyone flooding the kitchen.
        RateLimiter::for('guest-orders', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Reading an order, editing it, calling the waiter.
        RateLimiter::for('guest-api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
