<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings a database that was imported from elsewhere back in line with the
 * migrations.
 *
 * A database restored from someone else's dump can be missing columns the
 * migrations create — `order_items.product_name` in particular, which made
 * placing an order fail with "Unknown column". Every change here is guarded,
 * so on a database that is already correct this migration does nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // The item's name as it was when ordered, so old receipts keep
            // reading correctly after a product is renamed.
            if (! Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('order_items', 'notes')) {
                $table->text('notes')->nullable();
            }

            if (! Schema::hasColumn('order_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->default(0);
            }

            if (! Schema::hasColumn('order_items', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0);
            }
        });

        // Fill in the names of items ordered before the column existed.
        if (Schema::hasColumn('order_items', 'product_name')) {
            DB::table('order_items')
                ->whereNull('product_name')
                ->orWhere('product_name', '')
                ->update([
                    'product_name' => DB::raw(
                        '(select name from products where products.id = order_items.product_id)'
                    ),
                ]);

            DB::table('order_items')->whereNull('product_name')->update(['product_name' => '—']);
        }

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'client_name' => fn () => $table->string('client_name')->nullable(),
                'client_phone' => fn () => $table->string('client_phone', 20)->nullable(),
                'notes' => fn () => $table->text('notes')->nullable(),
                'shift_id' => fn () => $table->unsignedBigInteger('shift_id')->nullable(),
                'payment_method' => fn () => $table->string('payment_method')->nullable(),
                'payment_status' => fn () => $table->string('payment_status')->default('pending'),
                'subtotal' => fn () => $table->decimal('subtotal', 10, 2)->default(0),
                'fees' => fn () => $table->decimal('fees', 10, 2)->default(0),
            ] as $column => $add) {
                if (! Schema::hasColumn('orders', $column)) {
                    $add();
                }
            }
        });
    }

    public function down(): void
    {
        // Nothing to undo: this only adds columns the app already expects.
    }
};
