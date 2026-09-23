<?php

use App\Http\Controllers\CashPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\OrderChangeRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffPulseController;
use App\Http\Controllers\TableMenuController;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Interface language. Returns the visitor to the page they came from, but
// only if that page is on this site (never an outside address).
Route::get('/locale/{locale}', function (Request $request, string $locale) {
    if (in_array($locale, SetLocale::SUPPORTED, true)) {
        $request->session()->put('locale', $locale);
    }

    $previous = url()->previous();
    $sameSite = parse_url($previous, PHP_URL_HOST) === $request->getHost();

    return redirect()->to($sameSite ? $previous : url('/'));
})->name('locale.switch');

// The guest menu. A table is reached only through its secret QR token.
Route::get('/menu/{qr_token}', [TableMenuController::class, 'show'])
    ->where('qr_token', '[A-Za-z0-9\-]{8,64}')
    ->name('tables.menu');

Route::get('/menu', [MenuController::class, 'index'])
    ->name('menu.index');

/*
|--------------------------------------------------------------------------
| Any signed-in user
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Staff (admin or kitchen)
|--------------------------------------------------------------------------
| A signed-in account with no role sees nothing here, not even the
| dashboard, so an account registered by a stranger is useless to them.
*/

Route::middleware(['auth', 'role:admin|kitchen'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/kitchen/orders', [KitchenOrderController::class, 'index'])->name('kitchen.orders');
    Route::get('/kitchen/orders/board', [KitchenOrderController::class, 'board'])->name('kitchen.board');
    Route::patch('/kitchen/orders/{order}/status', [KitchenOrderController::class, 'updateStatus'])
        ->whereNumber('order')
        ->name('kitchen.orders.status');

    // Badge counts and "new order" detection for the navigation bar.
    Route::get('/staff/pulse', StaffPulseController::class)->name('staff.pulse');
});

/*
|--------------------------------------------------------------------------
| Admin only
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    // Tables and their QR codes
    Route::resource('tables', RestaurantTableController::class)->except(['show']);
    Route::get('/tables/{table}/qr', [QrCodeController::class, 'show'])->name('tables.qr');

    // Menu
    Route::get('/menu-management', [MenuManagementController::class, 'index'])->name('menu.management');
    Route::post('/menu-management/categories', [MenuManagementController::class, 'storeCategory'])->name('menu.management.categories.store');
    Route::put('/menu-management/categories/{category}', [MenuManagementController::class, 'updateCategory'])->name('menu.management.categories.update');
    Route::patch('/menu-management/categories/{category}/toggle', [MenuManagementController::class, 'toggleCategory'])->name('menu.management.categories.toggle');
    Route::post('/menu-management/products', [MenuManagementController::class, 'storeProduct'])->name('menu.management.products.store');
    Route::put('/menu-management/products/{product}', [MenuManagementController::class, 'updateProduct'])->name('menu.management.products.update');
    Route::patch('/menu-management/products/{product}/toggle', [MenuManagementController::class, 'toggleProduct'])->name('menu.management.products.toggle');

    // Shifts
    Route::get('/shifts/current', [ShiftController::class, 'current'])->name('shifts.current');
    Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::get('/shifts/history', [ShiftController::class, 'history'])->name('shifts.history');
    Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->whereNumber('shift')->name('shifts.show');
    Route::get('/shifts/{shift}/export', [ShiftController::class, 'export'])->whereNumber('shift')->name('shifts.export');

    // Payments
    Route::get('/cash-payments', [CashPaymentController::class, 'index'])->name('cash-payments.index');
    Route::get('/cash-payments/board', [CashPaymentController::class, 'board'])->name('cash-payments.board');
    Route::post('/cash-payments/{order}/confirm', [CashPaymentController::class, 'confirm'])->name('cash-payments.confirm');
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])->name('orders.invoice');

    // Waiter alerts
    Route::get('/order-change-requests', [OrderChangeRequestController::class, 'index'])->name('order-change-requests.index');
    Route::get('/order-change-requests/board', [OrderChangeRequestController::class, 'board'])->name('order-change-requests.board');
    Route::post('/order-change-requests/{orderChangeRequest}/resolve', [OrderChangeRequestController::class, 'resolve'])->name('order-change-requests.resolve');

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'index'])->name('reports.sales');
    Route::get('/reports/sales/export', [ReportController::class, 'export'])->name('reports.sales.export');
    Route::get('/reports/monthly', [MonthlyReportController::class, 'index'])->name('reports.monthly');
    Route::get('/reports/monthly/pdf', [MonthlyReportController::class, 'pdf'])->name('reports.monthly.pdf');
});

require __DIR__ . '/auth.php';
