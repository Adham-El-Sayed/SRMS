<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Takeaway, delivery and online orders belong to a customer, not a table,
 * so an order may now have no table at all.
 *
 * The column carries a foreign key, which MySQL will not let Laravel's
 * column builder rewrite, so MySQL is given the plain ALTER it accepts and
 * every other driver keeps the portable path.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `orders` MODIFY `restaurant_table_id` BIGINT UNSIGNED NULL');

            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('restaurant_table_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Leaving the column nullable is harmless; making it required again
        // would fail on any order that has no table.
    }
};
