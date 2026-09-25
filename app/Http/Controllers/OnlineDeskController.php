<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * The desk that watches orders arriving from the website. Everything here is
 * read-and-decide: accepting or refusing an order runs through the same
 * kitchen endpoint the kitchen board uses, so an order has one life story
 * however it is moved along.
 */
class OnlineDeskController extends Controller
{
    public function index(): View
    {
        return view('online.desk', [
            'orders' => $this->liveOrders(),
            'today' => $this->todayTotals(),
            'offered' => Settings::offers('online'),
            'publicUrl' => route('online.create'),
        ]);
    }

    public function board(): JsonResponse
    {
        $orders = $this->liveOrders();

        return response()->json([
            'html' => view('online._board', ['orders' => $orders])->render(),
            'count' => $orders->count(),
            'signature' => $orders
                ->map(fn (Order $o) => $o->id . ':' . $o->status . ':' . $o->payment_status . ':' . $o->updated_at?->timestamp)
                ->implode(','),
        ]);
    }

    /** Orders placed on the website that still need somebody's attention. */
    protected function liveOrders(): Collection
    {
        return Order::query()
            ->where('source', 'web')
            ->where(function ($query) {
                $query->active()->orWhere('payment_status', 'pending');
            })
            ->whereNot('status', 'cancelled')
            ->with('items')
            ->latest('id')          // newest first: the desk works off the top
            ->get();
    }

    /** A line of context above the board, for the day so far. */
    protected function todayTotals(): array
    {
        $orders = Order::query()
            ->where('source', 'web')
            ->whereDate('created_at', today())
            ->get(['status', 'total']);

        return [
            'count' => $orders->count(),
            'money' => (float) $orders->where('status', '!=', 'cancelled')->sum('total'),
        ];
    }
}
