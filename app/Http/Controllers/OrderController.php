<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderItemsRequest;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * The guest-facing ordering API used by the QR menu.
 *
 * A guest can only reach a table through its QR token, and can only see or
 * change an order by presenting the order's own secret token (returned once,
 * when the order is placed). Staff actions live in the web routes instead.
 */
class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $table = RestaurantTable::where('qr_token', $request->string('table_token'))->firstOrFail();

        try {
            $order = $this->orderService->create(
                $table,
                $request->validated('items'),
                $request->validated('client_name'),
                $request->validated('client_phone'),
                $request->validated('payment_method')
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => __('Order created successfully.'),
            'order' => $order,
            // The only time the token is ever sent. The page keeps it in memory.
            'order_token' => $order->access_token,
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorizeGuest($request, $order);

        return response()->json([
            'order' => $order->load(['table', 'items']),
        ]);
    }

    public function updateItems(UpdateOrderItemsRequest $request, Order $order): JsonResponse
    {
        $this->authorizeGuest($request, $order);

        try {
            $order = $this->orderService->update($order, $request->validated('items'));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => __('Order updated successfully.'),
            'order' => $order,
        ]);
    }

    /**
     * Answer 404 rather than 403 so a wrong token doesn't even confirm that
     * the order number exists.
     */
    public static function authorizeGuest(Request $request, Order $order): void
    {
        abort_unless($order->tokenMatches($request->header('X-Order-Token')), 404);
    }
}
