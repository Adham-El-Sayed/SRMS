<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

class KitchenOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(): View
    {
        return view('kitchen.orders', ['orders' => $this->activeOrders()]);
    }

    /** The board alone, so the page can refresh itself every few seconds. */
    public function board(): JsonResponse
    {
        $orders = $this->activeOrders();

        return response()->json([
            'html' => view('kitchen._board', ['orders' => $orders])->render(),
            'count' => $orders->count(),
            // Changes whenever an order appears, moves or leaves the board.
            'signature' => $orders->map(fn ($o) => $o->id . ':' . $o->status . ':' . $o->updated_at?->timestamp)->implode(','),
        ]);
    }

    /** Staff move an order along. Signed-in, CSRF-protected, role-checked by the route. */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'preparing', 'ready', 'completed', 'cancelled'])],
        ]);

        try {
            $order = $this->orderService->updateStatus($order, $validated['status']);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => __('Order status updated successfully.'),
            'status' => $order->status,
        ]);
    }

    protected function activeOrders()
    {
        return Order::query()
            ->active()
            ->with(['table', 'items'])
            ->oldest()          // first in, first cooked
            ->get();
    }
}
