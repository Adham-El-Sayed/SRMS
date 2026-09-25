<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOnlineOrderRequest;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Services\ShiftService;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

/**
 * Ordering from outside the restaurant.
 *
 * The page is public — that is the point of it — so it gives away nothing but
 * the menu. It never lists orders, and the customer follows their own order
 * only with the secret token handed back once, when they place it.
 */
class OnlineOrderController extends Controller
{
    public function __construct(
        private MenuService $menuService,
        private OrderService $orderService,
        private ShiftService $shiftService,
    ) {}

    public function create(): View
    {
        // Online ordering is something the restaurant switches on in Settings.
        abort_unless(Settings::offers('online'), 404);

        return view('online.index', [
            'menu' => $this->menuService->getMenu(),
            'deliveryOffered' => Settings::offers('delivery'),
            'deliveryFee' => Settings::deliveryFee(),
            // Closed kitchen: the page still shows the menu, but says so.
            'open' => (bool) $this->shiftService->currentOpenShift(),
        ]);
    }

    public function store(StoreOnlineOrderRequest $request): JsonResponse
    {
        abort_unless(Settings::offers('online'), 404);

        $wantsDelivery = $request->validated('fulfilment') === 'delivery'
            && Settings::offers('delivery');

        try {
            $order = $this->orderService->create(
                null,
                $request->validated('items'),
                $request->validated('client_name'),
                $request->validated('client_phone'),
                // Money is never settled by the browser: the cashier confirms
                // it when the customer pays on collection or at the door.
                'cash',
                [
                    'source' => 'web',
                    'order_type' => $wantsDelivery ? 'delivery' : 'online',
                    'delivery_address' => $wantsDelivery ? $request->validated('delivery_address') : null,
                    'delivery_fee' => $wantsDelivery ? Settings::deliveryFee() : 0,
                ]
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => __('Order placed. We will call you to confirm.'),
            'order' => $order,
            // Said once. The page keeps it in memory to follow this order.
            'order_token' => $order->access_token,
        ], 201);
    }
}
