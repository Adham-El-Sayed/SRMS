<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How an order reaches the guest: at a table, taken away, delivered, or
 * placed online. Delivery carries an address, a fee and a driver.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'order_type')) {
                // dine_in | takeaway | delivery | online
                $table->string('order_type', 20)->default('dine_in')->after('restaurant_table_id')->index();
            }

            if (! Schema::hasColumn('orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable();
            }

            if (! Schema::hasColumn('orders', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->default(0);
            }

            if (! Schema::hasColumn('orders', 'driver_name')) {
                $table->string('driver_name', 100)->nullable();
            }

            if (! Schema::hasColumn('orders', 'delivery_status')) {
                // waiting | out | delivered
                $table->string('delivery_status', 20)->nullable();
            }
        });

        // Orders placed before this migration were all eaten in.
        DB::table('orders')->whereNull('order_type')->update(['order_type' => 'dine_in']);

        // A table the restaurant's own settings live in: which service types
        // are offered, what delivery costs, and anything added later.
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'delivery_address', 'delivery_fee', 'driver_name', 'delivery_status']);
        });

        Schema::dropIfExists('settings');
    }
};
