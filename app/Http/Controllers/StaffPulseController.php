<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChangeRequest;
use App\Support\Access;
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
        $workspace = Access::workspaceFor($request->user(), $request);

        $pulse = [];

        if (in_array($workspace, ['admin', 'kitchen'], true)) {
            $pulse['kitchen'] = Order::active()->count();
            $pulse['latest_order_id'] = (int) Order::max('id');
        }

        if (in_array($workspace, ['admin', 'cashier'], true)) {
            $pulse['payments'] = Order::where('payment_status', 'pending')
                ->where('status', '!=', 'cancelled')
                ->count();
        }

        if ($workspace === 'admin') {
            $pulse['alerts'] = OrderChangeRequest::where('status', 'pending')->count();
            $pulse['latest_alert_id'] = (int) OrderChangeRequest::where('status', 'pending')->max('id');
        }

        return response()
            ->json($pulse)
            ->header('Cache-Control', 'no-store');
    }
}
