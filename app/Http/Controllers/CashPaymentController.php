<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CashPaymentController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('payment_method', 'cash')
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled'])
            ->with(['table', 'items.product'])
            ->latest()
            ->get();

        return view('cash-payments.index', [
            'orders' => $orders,
        ]);
    }

    public function confirm(Order $order): RedirectResponse
    {
        if ($order->payment_method !== 'cash') {
            abort(422, __('This order is not a cash order.'));
        }

        $order->update([
            'payment_status' => 'paid',
        ]);

        return redirect()
            ->route('cash-payments.index')
            ->with('success', __('Order #:id has been confirmed as paid.', ['id' => $order->id]));
    }
}