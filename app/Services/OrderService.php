<?php

namespace App\Services;

use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    /** How long a guest may change an order after placing it. */
    public const EDIT_WINDOW_SECONDS = 150;

    public function __construct(
        protected ShiftService $shiftService = new ShiftService()
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Place an order
    |--------------------------------------------------------------------------
    | Prices always come from the database, never from the request. Payment
    | starts as pending for cash and card alike: nothing the browser says
    | about a payment is trusted until staff confirm it.
    */
    public function create(
        ?RestaurantTable $table,
        array $items,
        ?string $clientName = null,
        ?string $clientPhone = null,
        string $paymentMethod = 'cash',
        array $service = []
    ): Order {
        return DB::transaction(function () use ($table, $items, $clientName, $clientPhone, $paymentMethod, $service) {

            // Nothing may be ordered while the restaurant is closed: without an
            // open shift there is no drawer for the money to land in.
            $shift = $this->shiftService->currentOpenShift();

            if (! $shift) {
                throw new InvalidArgumentException(__('The restaurant is not taking orders right now.'));
            }

            $lines = $this->priceLines($items);

            $type = $service['order_type'] ?? 'dine_in';
            $deliveryFee = $type === 'delivery' ? (float) ($service['delivery_fee'] ?? 0) : 0;

            $order = Order::create([
                'restaurant_table_id' => $table?->id,
                'order_type' => $type,
                'delivery_address' => $service['delivery_address'] ?? null,
                'delivery_fee' => $deliveryFee,
                'delivery_status' => $type === 'delivery' ? 'waiting' : null,
                'shift_id' => $shift->id,
                'access_token' => Str::random(48),
                'status' => 'pending',
                'total' => $lines->sum('subtotal') + $deliveryFee,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
            ]);

            $order->items()->createMany($lines->all());

            // Someone is sitting here now, whatever the table said before.
            if ($table && $table->status !== TableStatus::Occupied) {
                $table->update(['status' => TableStatus::Occupied]);
            }

            return $order->load(['table', 'items']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Guest edits during the edit window
    |--------------------------------------------------------------------------
    | Allowed only while the kitchen has not picked the order up and nothing
    | has been paid, so a guest can never change a meal already being cooked
    | or a bill already settled.
    */
    public function update(Order $order, array $items): Order
    {
        return DB::transaction(function () use ($order, $items) {

            // Lock the row so a kitchen confirmation can't slip in mid-edit.
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $this->isWithinEditWindow($order)) {
                throw new InvalidArgumentException(__('The edit window has expired.'));
            }

            if ($order->status !== 'pending' || $order->isPaid()) {
                throw new InvalidArgumentException(__('This order is already being prepared and can no longer be changed.'));
            }

            $lines = $this->priceLines($items);

            $order->items()->delete();
            $order->items()->createMany($lines->all());
            $order->update(['total' => $lines->sum('subtotal') + (float) $order->delivery_fee]);

            // Staff must know the ticket changed after they first saw it.
            \App\Models\OrderChangeRequest::updateOrCreate(
                ['order_id' => $order->id, 'reason' => 'edited', 'status' => 'pending'],
                ['updated_at' => now()]
            );

            return $order->fresh(['table', 'items']);
        });
    }

    public function isWithinEditWindow(Order $order): bool
    {
        return $order->created_at
            ->copy()
            ->addSeconds(self::EDIT_WINDOW_SECONDS)
            ->isFuture();
    }

    /*
    |--------------------------------------------------------------------------
    | Kitchen status changes
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Order $order, string $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus) {

            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $order->canTransitionTo($newStatus)) {
                throw new InvalidArgumentException(__('This order cannot move from :from to :to.', [
                    'from' => __(ucfirst($order->status)),
                    'to' => __(ucfirst($newStatus)),
                ]));
            }

            $order->update(['status' => $newStatus]);

            $table = RestaurantTable::find($order->restaurant_table_id);

            if ($table && ! $order->isActive()) {
                $stillEating = Order::query()
                    ->where('restaurant_table_id', $table->id)
                    ->active()
                    ->exists();

                if (! $stillEating) {
                    $table->update(['status' => TableStatus::Available]);
                }
            }

            return $order->fresh(['table', 'items']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Staff confirm money was received
    |--------------------------------------------------------------------------
    | The payment is booked to the shift that is open *now*, because that is
    | the drawer the cash went into, even if the order was placed earlier.
    */
    public function confirmPayment(Order $order): Order
    {
        return DB::transaction(function () use ($order) {

            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->isPaid()) {
                throw new InvalidArgumentException(__('This order has already been paid.'));
            }

            if ($order->status === 'cancelled') {
                throw new InvalidArgumentException(__('A cancelled order cannot be paid.'));
            }

            $shift = $this->shiftService->currentOpenShift();

            if (! $shift) {
                throw new InvalidArgumentException(__('Open a shift before taking payments.'));
            }

            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'shift_id' => $shift->id,
            ]);

            return $order;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Price the requested lines from the database
    |--------------------------------------------------------------------------
    */
    protected function priceLines(array $items): \Illuminate\Support\Collection
    {
        $products = Product::query()
            ->with('category')
            ->whereIn('id', collect($items)->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        return collect($items)->map(function (array $item) use ($products) {
            $product = $products->get($item['product_id']);

            // Hidden products, and products in a hidden category, can't be ordered.
            if (! $product || ! $product->is_active || ($product->category && ! $product->category->is_active)) {
                throw new InvalidArgumentException(__('One or more selected products are not available.'));
            }

            $quantity = (int) $item['quantity'];

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => round($quantity * (float) $product->price, 2),
                'notes' => isset($item['notes']) && $item['notes'] !== '' ? $item['notes'] : null,
            ];
        });
    }
}
