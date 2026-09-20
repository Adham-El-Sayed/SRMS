<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class KitchenOrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with([
            'table',
            'items.product',
        ])
        ->whereIn('status', [
            'pending',
            'confirmed',
            'preparing',
            'ready',
        ])
        ->latest()
        ->get();

        return view('kitchen.orders', compact('orders'));
    }
}