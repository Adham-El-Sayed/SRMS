<?php

namespace App\Http\Controllers;

use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $monthlyOrders = Order::query()
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear);

        $totalOrders = (clone $monthlyOrders)->count();

        $activeOrders = (clone $monthlyOrders)->whereIn('status', [
            'pending',
            'confirmed',
            'preparing',
            'ready',
        ])->count();

        $preparingOrders = (clone $monthlyOrders)
            ->where('status', 'preparing')
            ->count();

        $totalRevenue = (clone $monthlyOrders)
            ->where('status', 'completed')
            ->sum('total');

        $availableTables = RestaurantTable::where(
            'status',
            TableStatus::Available->value
        )->count();

        $occupiedTables = RestaurantTable::where(
            'status',
            TableStatus::Occupied->value
        )->count();

        $reservedTables = RestaurantTable::where(
            'status',
            TableStatus::Reserved->value
        )->count();

        $recentOrders = (clone $monthlyOrders)
            ->with('table')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalOrders',
            'activeOrders',
            'preparingOrders',
            'totalRevenue',
            'availableTables',
            'occupiedTables',
            'reservedTables',
            'recentOrders',
        ));
    }
}