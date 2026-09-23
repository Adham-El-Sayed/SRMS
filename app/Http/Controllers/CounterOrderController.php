<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Services\ShiftService;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * The counter: where a cashier takes an order that has no table — someone
 * collecting it, or having it delivered, or ringing up an online order.
 */
class CounterOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private MenuService $menuService,
        private ShiftService $shiftService
    ) {}

    public function create(): View
    {
        return view('counter.create', [
            'menu' => $this->menuService->getMenu(),
            'types' => array_values(array_diff(Settings::enabledTypes(), ['dine_in'])),
            'deliveryFee' => Settings::deliveryFee(),
            'shiftOpen' => (bool) $this->shiftService->currentOpenShift(),
            'recent' => Order::query()
                ->whereIn('order_type', ['takeaway', 'delivery', 'online'])
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $offered = array_values(array_diff(Settings::enabledTypes(), ['dine_in']));

        $data = $request->validate([
            'order_type' => ['required', Rule::in($offered)],
            'items' => ['required', 'array', 'min:1', 'max:40'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:100'],
            'client_phone' => [
                Rule::requiredIf(fn () => in_array($request->input('order_type'), ['delivery', 'online'], true)),
                'nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{5,20}$/',
            ],
            'delivery_address' => [Rule::requiredIf(fn () => $request->input('order_type') === 'delivery'), 'nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:cash,card'],
        ]);

        try {
            $order = $this->orderService->create(
                null,
                $data['items'],
                $data['client_name'] ?? null,
                $data['client_phone'] ?? null,
                $data['payment_method'],
                [
                    'order_type' => $data['order_type'],
                    'delivery_address' => $data['delivery_address'] ?? null,
                    'delivery_fee' => $data['order_type'] === 'delivery' ? Settings::deliveryFee() : 0,
                ]
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('counter.create')
            ->with('success', __('Order #:id taken — :total EGP. It is on the kitchen board now.', [
                'id' => $order->id,
                'total' => number_format((float) $order->total, 2),
            ]));
    }
}
