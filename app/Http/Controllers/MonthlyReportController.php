<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MonthlyReportController extends Controller
{
    protected function buildData(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $orders = Order::query()->whereBetween('created_at', [$start, $end]);

        // Money counts once it is confirmed received, and never for a cancelled order.
        $paid = (clone $orders)->where('payment_status', 'paid')->where('status', '!=', 'cancelled');

        $paidCount = (clone $paid)->count();
        $totalRevenue = (float) (clone $paid)->sum('total');

        $topProducts = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled'))
            // Grouped by the name on the order, so renamed or deleted products still count.
            ->selectRaw('product_name, SUM(quantity) as total_quantity, SUM(subtotal) as total_sales')
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return [
            'month' => $month,
            'year' => $year,
            'monthName' => $start->format('F Y'),
            'totalOrders' => (clone $orders)->count(),
            'completedOrders' => (clone $orders)->where('status', 'completed')->count(),
            'cancelledOrders' => (clone $orders)->where('status', 'cancelled')->count(),
            'totalRevenue' => $totalRevenue,
            'cashRevenue' => (float) (clone $paid)->where('payment_method', 'cash')->sum('total'),
            'visaRevenue' => (float) (clone $paid)->where('payment_method', 'card')->sum('total'),
            'averageOrderValue' => $paidCount > 0 ? $totalRevenue / $paidCount : 0,
            'topProducts' => $topProducts,
        ];
    }

    protected function period(Request $request): array
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        return [
            (int) ($validated['month'] ?? now()->month),
            (int) ($validated['year'] ?? now()->year),
        ];
    }

    public function index(Request $request): View
    {
        [$month, $year] = $this->period($request);

        return view('reports.monthly', $this->buildData($month, $year));
    }

    public function pdf(Request $request): Response
    {
        [$month, $year] = $this->period($request);

        $data = $this->buildData($month, $year);

        // PDFs stay in English: the PDF library can't lay out Arabic.
        $pdf = $this->inEnglish(fn () => Pdf::loadView('reports.monthly-pdf', $data));

        return $pdf->stream("monthly-report-{$year}-{$month}.pdf");
    }
}
