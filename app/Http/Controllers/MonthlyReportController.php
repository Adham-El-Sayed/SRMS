<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MonthlyReportController extends Controller
{
    protected function buildData(int $month, int $year): array
    {
        $orders = Order::query()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year);

        $totalOrders = (clone $orders)->count();

        $completedOrders = (clone $orders)
            ->where('status', 'completed')
            ->count();

        $cancelledOrders = (clone $orders)
            ->where('status', 'cancelled')
            ->count();

        $paidOrders = (clone $orders)->where('payment_status', 'paid');

        $totalRevenue = (clone $paidOrders)->sum('total');

        $cashRevenue = (clone $paidOrders)
            ->where('payment_method', 'cash')
            ->sum('total');

        $visaRevenue = (clone $paidOrders)
            ->where('payment_method', 'card')
            ->sum('total');

        $averageOrderValue = $completedOrders > 0
            ? $totalRevenue / $completedOrders
            : 0;

        $topProducts = OrderItem::query()
            ->whereHas('order', function ($query) use ($month, $year) {
                $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);
            })
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(subtotal) as total_sales')
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return [
            'month' => $month,
            'year' => $year,
            'monthName' => \Carbon\Carbon::create($year, $month, 1)->format('F Y'),
            'totalOrders' => $totalOrders,
            'completedOrders' => $completedOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalRevenue' => $totalRevenue,
            'cashRevenue' => $cashRevenue,
            'visaRevenue' => $visaRevenue,
            'averageOrderValue' => $averageOrderValue,
            'topProducts' => $topProducts,
        ];
    }

    public function index(Request $request): View
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        return view('reports.monthly', $this->buildData($month, $year));
    }

    public function pdf(Request $request): Response
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $data = $this->buildData($month, $year);

        $pdf = Pdf::loadView('reports.monthly-pdf', $data);

        return $pdf->stream("monthly-report-{$data['year']}-{$data['month']}.pdf");
    }
}