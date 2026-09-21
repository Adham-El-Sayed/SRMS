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
    // Called by the client (public, no auth) once the edit window has expired.
    public function store(Order $order): JsonResponse
    {
        // Avoid creating duplicate pending alerts for the same order.
        $existing = OrderChangeRequest::query()
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
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

    // Admin-facing list.
    public function index(): View
    {
        $requests = OrderChangeRequest::query()
            ->where('status', 'pending')
            ->with(['order.table'])
            ->latest()
            ->get();

        return view('order-change-requests.index', [
            'requests' => $requests,
        ]);
    }

    // Admin marks it as handled.
    public function resolve(OrderChangeRequest $orderChangeRequest): RedirectResponse
    {
        $orderChangeRequest->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return redirect()
            ->route('order-change-requests.index')
            ->with('success', __('Request marked as handled.'));
    }

    // Small JSON endpoint the nav badge polls periodically.
    public function pendingCount(): JsonResponse
    {
        return response()->json([
            'count' => OrderChangeRequest::where('status', 'pending')->count(),
        ]);
    }
}