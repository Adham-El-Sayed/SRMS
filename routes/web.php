<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\TableMenuController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashPaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OrderChangeRequestController;
use App\Http\Controllers\MonthlyReportController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/tables/{table}/qr', [QrCodeController::class, 'show'])
    ->name('tables.qr');


Route::get('/menu/{qr_token}', [TableMenuController::class, 'show'])
    ->name('tables.menu');


Route::get('/menu', [MenuController::class, 'index'])
    ->name('menu.index');


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Authenticated Routes (any logged-in user)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');


    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');


    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});


/*
|--------------------------------------------------------------------------
| Kitchen Staff Routes (admin or kitchen)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|kitchen'])->group(function () {

    Route::get('/kitchen/orders', [KitchenOrderController::class, 'index'])
        ->name('kitchen.orders');

});


/*
|--------------------------------------------------------------------------
| Admin-Only Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::resource('tables', RestaurantTableController::class);


    Route::get('/menu-management', [MenuManagementController::class, 'index'])
        ->name('menu.management');

    Route::post('/menu-management/categories', [MenuManagementController::class, 'storeCategory'])
        ->name('menu.management.categories.store');

    Route::put('/menu-management/categories/{category}', [MenuManagementController::class, 'updateCategory'])
        ->name('menu.management.categories.update');

    Route::delete('/menu-management/categories/{category}', [MenuManagementController::class, 'destroyCategory'])
        ->name('menu.management.categories.destroy');

    Route::post('/menu-management/products', [MenuManagementController::class, 'storeProduct'])
        ->name('menu.management.products.store');

    Route::put('/menu-management/products/{product}', [MenuManagementController::class, 'updateProduct'])
        ->name('menu.management.products.update');

    Route::delete('/menu-management/products/{product}', [MenuManagementController::class, 'destroyProduct'])
        ->name('menu.management.products.destroy');

    Route::patch('/menu-management/categories/{category}/toggle', [MenuManagementController::class, 'toggleCategory'])
        ->name('menu.management.categories.toggle');

    Route::patch('/menu-management/products/{product}/toggle', [MenuManagementController::class, 'toggleProduct'])
        ->name('menu.management.products.toggle');


    Route::get('/shifts/current', [ShiftController::class, 'current'])
        ->name('shifts.current');

    Route::post('/shifts/open', [ShiftController::class, 'open'])
        ->name('shifts.open');

    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])
        ->name('shifts.close');

    Route::get('/cash-payments', [CashPaymentController::class, 'index'])
        ->name('cash-payments.index');

    Route::post('/cash-payments/{order}/confirm', [CashPaymentController::class, 'confirm'])
        ->name('cash-payments.confirm');
    
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])
        ->name('orders.invoice');
    Route::get('/shifts/history', [ShiftController::class, 'history'])
        ->name('shifts.history');

    Route::get('/shifts/{shift}', [ShiftController::class, 'show'])
        ->name('shifts.show');
    
    Route::get('/shifts/{shift}/export', [ShiftController::class, 'export'])
        ->name('shifts.export');

    Route::get('/reports/sales', [ReportController::class, 'index'])
        ->name('reports.sales');

    Route::get('/reports/sales/export', [ReportController::class, 'export'])
        ->name('reports.sales.export');

    Route::get('/order-change-requests', [OrderChangeRequestController::class, 'index'])
        ->name('order-change-requests.index');

    Route::post('/order-change-requests/{orderChangeRequest}/resolve', [OrderChangeRequestController::class, 'resolve'])
        ->name('order-change-requests.resolve');

    Route::get('/order-change-requests/pending-count', [OrderChangeRequestController::class, 'pendingCount'])
        ->name('order-change-requests.pending-count');

    Route::get('/reports/monthly', [MonthlyReportController::class, 'index'])
        ->name('reports.monthly');

    Route::get('/reports/monthly/pdf', [MonthlyReportController::class, 'pdf'])
        ->name('reports.monthly.pdf');

        });


require __DIR__ . '/auth.php';