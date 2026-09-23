<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChangeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderChangeRequestController extends Controller
{
    /** A guest asks for a waiter. Needs the order's own token. */
    public function store(Request $request, Order $order): JsonResponse
    {
        OrderController::authorizeGuest($request, $order);

        if (! $order->isActive()) {
            return response()->json([
                'message' => __('This order is already closed.'),
            ], 422);
        }

        $alreadyWaiting = OrderChangeRequest::query()
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyWaiting) {
            return response()->json([
                'message' => __('A waiter has already been notified for this order.'),
            ]);
        }

        OrderChangeRequest::create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => __('The waiter has been notified.'),
        ], 201);
    }

    public function index(): View
    {
        return view('order-change-requests.index', [
            'requests' => $this->pending(),
        ]);
    }

    /** The list alone, for the page to refresh itself without a reload. */
    public function board(): JsonResponse
    {
        $requests = $this->pending();

        return response()->json([
            'html' => view('order-change-requests._list', ['requests' => $requests])->render(),
            'count' => $requests->count(),
            // Includes the minute so "requested 3 minutes ago" keeps ticking.
            'signature' => $requests->pluck('id')->implode(',') . '|' . now()->format('H:i'),
        ]);
    }

    public function resolve(OrderChangeRequest $orderChangeRequest): RedirectResponse
    {
        if ($orderChangeRequest->status === 'pending') {
            $orderChangeRequest->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);
        }

        return redirect()
            ->route('order-change-requests.index')
            ->with('success', __('Request marked as handled.'));
    }

    protected function pending()
    {
        return OrderChangeRequest::query()
            ->where('status', 'pending')
            ->with(['order.table'])
            ->oldest()
            ->get();
    }
}
