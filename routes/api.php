<?php

use App\Http\Controllers\OrderChangeRequestController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest ordering API (used by the QR menu)
|--------------------------------------------------------------------------
| Placing an order needs the table's QR token. Everything about an existing
| order needs that order's own token in the X-Order-Token header. There is
| deliberately no way to list orders or change their status from here —
| that is staff work and lives behind the login in routes/web.php.
*/

Route::middleware('throttle:guest-orders')->group(function () {
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

    // The same rate limit covers the website's own ordering page.
    Route::post('/online-orders', [OnlineOrderController::class, 'store'])->name('online.store');
});

Route::middleware('throttle:guest-api')->group(function () {
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::put('/orders/{order}/items', [OrderController::class, 'updateItems'])->whereNumber('order')->name('orders.update-items');
    Route::post('/orders/{order}/request-help', [OrderChangeRequestController::class, 'store'])->whereNumber('order')->name('orders.request-help');
});
