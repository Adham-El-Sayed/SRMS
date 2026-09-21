<?php

namespace App\Services;

use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected ShiftService $shiftService = new ShiftService()
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Create Order
    |--------------------------------------------------------------------------
    */

    public function create(
        int $restaurantTableId,
        array $items,
        ?string $clientName = null,
        ?string $clientPhone = null,
        string $paymentMethod = 'cash'
    ): Order {
        return DB::transaction(function () use (
            $restaurantTableId,
            $items,
            $clientName,
            $clientPhone,
            $paymentMethod
        ) {
            /*
            |--------------------------------------------------------------------------
            | Get Restaurant Table
            |--------------------------------------------------------------------------
            */

            $restaurantTable = RestaurantTable::findOrFail(
                $restaurantTableId
            );


            /*
            |--------------------------------------------------------------------------
            | Check Table Availability
            |--------------------------------------------------------------------------
            */

            if (
                $restaurantTable->status !== TableStatus::Available
            ) {
                throw new InvalidArgumentException(
                    'This table is not available for new orders.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Get Current Open Shift
            |--------------------------------------------------------------------------
            */

            $openShift = $this->shiftService->currentOpenShift();


            /*
            |--------------------------------------------------------------------------
            | Create Order
            |--------------------------------------------------------------------------
            */

            $order = Order::create([
                'restaurant_table_id' => $restaurantTableId,
                'shift_id' => $openShift?->id,
                'status' => 'pending',
                'total' => 0,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentMethod === 'card' ? 'paid' : 'pending',
            ]);

            $total = 0;


            /*
            |--------------------------------------------------------------------------
            | Create Order Items
            |--------------------------------------------------------------------------
            */

            foreach ($items as $item) {

                $product = Product::findOrFail(
                    $item['product_id']
                );


                /*
                |--------------------------------------------------------------------------
                | Check Product Availability
                |--------------------------------------------------------------------------
                */

                if (! $product->is_active) {
                    throw new InvalidArgumentException(
                        'One or more selected products are not available.'
                    );
                }


                $unitPrice = $product->price;

                $subtotal =
                    $item['quantity'] * $unitPrice;


                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ]);


                $total += $subtotal;
            }


            /*
            |--------------------------------------------------------------------------
            | Update Order Total
            |--------------------------------------------------------------------------
            */

            $order->update([
                'total' => $total,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Return Created Order
            |--------------------------------------------------------------------------
            */

            return $order->load([
                'table',
                'items.product',
            ]);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Update Order Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Order $order,
        string $newStatus
    ): Order {
        return DB::transaction(function () use (
            $order,
            $newStatus
        ) {
            /*
            |--------------------------------------------------------------------------
            | Check Status Transition
            |--------------------------------------------------------------------------
            */

            if (! $order->canTransitionTo($newStatus)) {
                throw new InvalidArgumentException(
                    "Cannot change order status from {$order->status} to {$newStatus}."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Update Order Status
            |--------------------------------------------------------------------------
            */

            $order->update([
                'status' => $newStatus,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Get Restaurant Table
            |--------------------------------------------------------------------------
            */

            $restaurantTable = RestaurantTable::findOrFail(
                $order->restaurant_table_id
            );


            /*
            |--------------------------------------------------------------------------
            | Active Order States
            | Table becomes Occupied
            |--------------------------------------------------------------------------
            */

            if (in_array($newStatus, [
                'confirmed',
                'preparing',
                'ready',
            ], true)) {

                $restaurantTable->update([
                    'status' => TableStatus::Occupied,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Finished Order States
            | Check Other Active Orders
            |--------------------------------------------------------------------------
            */

            if (in_array($newStatus, [
                'completed',
                'cancelled',
            ], true)) {

                $hasActiveOrders = Order::query()
                    ->where(
                        'restaurant_table_id',
                        $restaurantTable->id
                    )
                    ->whereIn('status', [
                        'pending',
                        'confirmed',
                        'preparing',
                        'ready',
                    ])
                    ->exists();


                /*
                |--------------------------------------------------------------------------
                | Make Table Available
                |--------------------------------------------------------------------------
                */

                if (! $hasActiveOrders) {

                    $restaurantTable->update([
                        'status' => TableStatus::Available,
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Return Updated Order
            |--------------------------------------------------------------------------
            */

            return $order->fresh([
                'table',
                'items.product',
            ]);
        });
    }
        /*
    |--------------------------------------------------------------------------
    | Update Order Items (within edit window only)
    |--------------------------------------------------------------------------
    */

    public const EDIT_WINDOW_SECONDS = 150;

    public function update(Order $order, array $items): Order
    {
        return DB::transaction(function () use ($order, $items) {

            if (! $this->isWithinEditWindow($order)) {
                throw new InvalidArgumentException(
                    'The edit window for this order has expired.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Remove Old Items
            |--------------------------------------------------------------------------
            */

            $order->items()->delete();

            $total = 0;


            /*
            |--------------------------------------------------------------------------
            | Create New Items
            |--------------------------------------------------------------------------
            */

            foreach ($items as $item) {

                $product = Product::findOrFail(
                    $item['product_id']
                );

                if (! $product->is_active) {
                    throw new InvalidArgumentException(
                        'One or more selected products are not available.'
                    );
                }

                $unitPrice = $product->price;

                $subtotal = $item['quantity'] * $unitPrice;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ]);

                $total += $subtotal;
            }


            $order->update([
                'total' => $total,
            ]);


            return $order->fresh([
                'table',
                'items.product',
            ]);
        });
    }

    public function isWithinEditWindow(Order $order): bool
    {
        return $order->created_at
            ->addSeconds(self::EDIT_WINDOW_SECONDS)
            ->isFuture();
    }
}