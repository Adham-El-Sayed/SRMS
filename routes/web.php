<?php

use App\Http\Controllers\CashPaymentController;
use App\Http\Controllers\CounterOrderController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\KitchenStockController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\OnlineDeskController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\OrderChangeRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffController;
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

// Ordering from home. Public on purpose; it shows the menu and nothing else.
Route::get('/order', [OnlineOrderController::class, 'create'])->name('online.create');

/*
|--------------------------------------------------------------------------
| Any signed-in user
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Signed in, but nobody has said yet what this account may open.
    Route::view('/no-access', 'auth.no-access')->name('no-access');

    // A super admin chooses which side of the business they're working on.
    Route::get('/workspace/{workspace}', function (Request $request, string $workspace) {
        abort_unless(\App\Support\Access::isSuperAdmin($request->user()), 403);

        if (in_array($workspace, \App\Support\Access::WORKSPACES, true)) {
            $request->session()->put(\App\Support\Access::SESSION_KEY, $workspace);
        }

        return redirect()->to(\App\Support\Access::homeUrlFor($request->user(), $request));
    })->name('workspace.switch');

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

Route::middleware(['auth', 'role:super-admin|admin|cashier|kitchen'])->group(function () {
    // Badge counts and "new order" detection, for whichever badges the
    // signed-in account is allowed to see.
    Route::get('/staff/pulse', StaffPulseController::class)->name('staff.pulse');
});

/*
|--------------------------------------------------------------------------
| The kitchen: kitchen staff, admin, super admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super-admin|admin|kitchen|cashier'])->group(function () {
    Route::get('/kitchen/orders', [KitchenOrderController::class, 'index'])->name('kitchen.orders');
    Route::get('/kitchen/orders/board', [KitchenOrderController::class, 'board'])->name('kitchen.board');
    Route::patch('/kitchen/orders/{order}/status', [KitchenOrderController::class, 'updateStatus'])
        ->whereNumber('order')
        ->name('kitchen.orders.status');

    // What the kitchen has run out of
    Route::get('/kitchen/stock', [KitchenStockController::class, 'index'])->name('kitchen.stock');
    Route::patch('/kitchen/stock/{product}', [KitchenStockController::class, 'toggle'])
        ->whereNumber('product')
        ->name('kitchen.stock.toggle');
});

/*
|--------------------------------------------------------------------------
| Admin only
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| The money: cashier, admin, super admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super-admin|admin|cashier'])->group(function () {
    // Taking an order at the counter: collection, delivery, or an online one
    Route::get('/counter', [CounterOrderController::class, 'create'])->name('counter.create');
    Route::post('/counter', [CounterOrderController::class, 'store'])->name('counter.store');

    // Orders arriving from the website
    Route::get('/online-orders', [OnlineDeskController::class, 'index'])->name('online.desk');
    Route::get('/online-orders/board', [OnlineDeskController::class, 'board'])->name('online.board');

    // Orders on their way out
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/board', [DeliveryController::class, 'board'])->name('delivery.board');
    Route::patch('/delivery/{order}', [DeliveryController::class, 'update'])->whereNumber('order')->name('delivery.update');

    // Waiter calls and order changes reach whoever is on the floor
    Route::get('/order-change-requests', [OrderChangeRequestController::class, 'index'])->name('order-change-requests.index');
    Route::get('/order-change-requests/board', [OrderChangeRequestController::class, 'board'])->name('order-change-requests.board');
    Route::post('/order-change-requests/{orderChangeRequest}/resolve', [OrderChangeRequestController::class, 'resolve'])->name('order-change-requests.resolve');

    Route::get('/cash-payments', [CashPaymentController::class, 'index'])->name('cash-payments.index');
    Route::get('/cash-payments/board', [CashPaymentController::class, 'board'])->name('cash-payments.board');
    Route::post('/cash-payments/{order}/confirm', [CashPaymentController::class, 'confirm'])->name('cash-payments.confirm');
    // Correcting a ticket before it is paid
    Route::get('/orders/{order}/edit', [StaffOrderController::class, 'edit'])->whereNumber('order')->name('orders.edit');
    Route::patch('/orders/{order}', [StaffOrderController::class, 'update'])->whereNumber('order')->name('orders.update');

    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])->whereNumber('order')->name('orders.invoice');

    Route::get('/shifts/current', [ShiftController::class, 'current'])->name('shifts.current');
    Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::get('/shifts/history', [ShiftController::class, 'history'])->name('shifts.history');
    Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->whereNumber('shift')->name('shifts.show');
    Route::get('/shifts/{shift}/export', [ShiftController::class, 'export'])->whereNumber('shift')->name('shifts.export');
});

/*
|--------------------------------------------------------------------------
| Running the restaurant: admin and super admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super-admin|admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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


    // Staff and what each of them may open
    // How the restaurant serves guests
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');

    // Employment details and this month's pay
    Route::get('/staff/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::patch('/staff/employees/{user}', [EmployeeController::class, 'update'])->whereNumber('user')->name('employees.update');
    Route::post('/staff/employees/{user}/entries', [EmployeeController::class, 'storeEntry'])->whereNumber('user')->name('employees.entries.store');
    Route::delete('/staff/entries/{entry}', [EmployeeController::class, 'destroyEntry'])->whereNumber('entry')->name('employees.entries.destroy');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::patch('/staff/{user}', [StaffController::class, 'updateRole'])->whereNumber('user')->name('staff.role');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->whereNumber('user')->name('staff.destroy');

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'index'])->name('reports.sales');
    Route::get('/reports/sales/export', [ReportController::class, 'export'])->name('reports.sales.export');
    Route::get('/reports/monthly', [MonthlyReportController::class, 'index'])->name('reports.monthly');
    Route::get('/reports/monthly/pdf', [MonthlyReportController::class, 'pdf'])->name('reports.monthly.pdf');
});

require __DIR__ . '/auth.php';
