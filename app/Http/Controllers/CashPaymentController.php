<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Staff confirm that money was actually received, whether cash in the drawer
 * or a card on the terminal. Until then an order counts as unpaid everywhere.
 */
class CashPaymentController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private ShiftService $shiftService
    ) {}

    public function index(): View
    {
        return view('cash-payments.index', [
            'orders' => $this->waiting(),
            'shiftOpen' => (bool) $this->shiftService->currentOpenShift(),
        ]);
    }

    public function board(): JsonResponse
    {
        $orders = $this->waiting();

        return response()->json([
            'html' => view('cash-payments._list', ['orders' => $orders])->render(),
            'count' => $orders->count(),
            'signature' => $orders->map(fn ($o) => $o->id . ':' . $o->status . ':' . $o->updated_at?->timestamp)->implode(','),
        ]);
    }

    public function confirm(Order $order): RedirectResponse
    {
        try {
            $this->orderService->confirmPayment($order);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('cash-payments.index')->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('cash-payments.index')
            ->with('success', __('Order #:id has been confirmed as paid.', ['id' => $order->id]));
    }

    protected function waiting()
    {
        return Order::query()
            ->where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->with(['table', 'items'])
            ->oldest()
            ->get();
    }
}
