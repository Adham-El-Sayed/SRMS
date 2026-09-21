<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use App\Http\Requests\UpdateOrderItemsRequest;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Orders
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        $orders = Order::with([
            'table',
            'items.product',
        ])
            ->latest()
            ->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Order
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreOrderRequest $request
    ): JsonResponse {
        try {

            $order = $this->orderService->create(
                $request->integer('restaurant_table_id'),
                $request->input('items'),
                $request->input('client_name'),
                $request->input('client_phone'),
                $request->input('payment_method', 'cash')
            );

            return response()->json([
                'message' => __('Order created successfully.'),
                'order' => $order,
            ], 201);

        } catch (InvalidArgumentException $exception) {

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show Single Order
    |--------------------------------------------------------------------------
    */

    public function show(
        Order $order
    ): JsonResponse {
        $order->load([
            'table',
            'items.product',
        ]);

        return response()->json([
            'order' => $order,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Order Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order
    ): JsonResponse {
        try {

            $order = $this->orderService->updateStatus(
                $order,
                $request->input('status')
            );

            return response()->json([
                'message' => __('Order status updated successfully.'),
                'order' => $order,
            ]);

        } catch (InvalidArgumentException $exception) {

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
        /*
    |--------------------------------------------------------------------------
    | Update Order Items (edit window only)
    |--------------------------------------------------------------------------
    */

    public function updateItems(
        UpdateOrderItemsRequest $request,
        Order $order
    ): JsonResponse {
        try {

            $order = $this->orderService->update(
                $order,
                $request->input('items')
            );

            return response()->json([
                'message' => __('Order updated successfully.'),
                'order' => $order,
            ]);

        } catch (InvalidArgumentException $exception) {

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}