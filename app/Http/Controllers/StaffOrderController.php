<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MenuService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * A cashier correcting a ticket: the guest asked for one more, or the wrong
 * item was rung up. Only while the order is open and unpaid.
 */
class StaffOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private MenuService $menuService
    ) {}

    public function edit(Order $order): View
    {
        return view('orders.edit', [
            'order' => $order->load('items', 'table'),
            'menu' => $this->menuService->getMenu(),
            'locked' => $order->isPaid() || ! $order->isActive(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:40'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $order = $this->orderService->updateByStaff($order, $data['items']);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('cash-payments.index')
            ->with('success', __('Order #:id updated — :total EGP.', [
                'id' => $order->id,
                'total' => number_format((float) $order->total, 2),
            ]));
    }
}
