<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChangeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * One small request every few seconds from any staff page: the numbers for
 * the navigation badges and the newest order/alert ids, so the page knows
 * when to ring. Admin-only figures are left out for kitchen staff.
 */
class StaffPulseController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $isAdmin = $request->user()->hasRole('admin');

        $pulse = [
            'kitchen' => Order::active()->count(),
            'latest_order_id' => (int) Order::max('id'),
        ];

        if ($isAdmin) {
            $pulse['alerts'] = OrderChangeRequest::where('status', 'pending')->count();
            $pulse['latest_alert_id'] = (int) OrderChangeRequest::where('status', 'pending')->max('id');
            $pulse['payments'] = Order::where('payment_status', 'pending')->where('status', '!=', 'cancelled')->count();
        }

        return response()
            ->json($pulse)
            ->header('Cache-Control', 'no-store');
    }
}
