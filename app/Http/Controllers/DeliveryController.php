<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Orders on their way to someone's door: who is taking them and how far
 * along they are.
 */
class DeliveryController extends Controller
{
    /** Orders still being delivered, newest first. */
    private function open()
    {
        return Order::query()
            ->where('order_type', 'delivery')
            ->whereNotIn('status', ['cancelled'])
            ->where(fn ($query) => $query->whereNull('delivery_status')->orWhere('delivery_status', '!=', 'delivered'))
            ->with(['items'])
            ->latest()
            ->get();
    }

    public function index(): View
    {
        return view('delivery.index', [
            'orders' => $this->open(),
            'delivered' => Order::where('order_type', 'delivery')
                ->where('delivery_status', 'delivered')
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }

    public function board(): JsonResponse
    {
        $orders = $this->open();

        return response()->json([
            'html' => view('delivery._list', ['orders' => $orders])->render(),
            'count' => $orders->count(),
            'signature' => $orders->map(fn ($o) => $o->id . ':' . $o->delivery_status . ':' . $o->status . ':' . $o->updated_at?->timestamp)->implode(','),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->isDelivery(), 404);

        $data = $request->validate([
            'driver_name' => ['nullable', 'string', 'max:100'],
            'delivery_status' => ['required', 'in:waiting,out,delivered'],
        ]);

        $order->update($data);

        return back()->with('success', __('Delivery updated for order #:id.', ['id' => $order->id]));
    }
}
