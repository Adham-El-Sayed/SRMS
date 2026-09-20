<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderChangeRequestController;



Route::post('/orders', [OrderController::class, 'store'])
    ->name('orders.store');

Route::get('/orders', [OrderController::class, 'index'])
    ->name('orders.index');

Route::get('/orders/{order}', [OrderController::class, 'show'])
    ->name('orders.show');

Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
    ->name('orders.update-status');

Route::put('/orders/{order}/items', [OrderController::class, 'updateItems'])
    ->name('orders.update-items');

    Route::post('/orders/{order}/request-help', [OrderChangeRequestController::class, 'store'])
    ->name('orders.request-help');