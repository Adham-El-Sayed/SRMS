<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Where an order came from, which is a different question from where it is
 * going: a takeaway rung up at the counter and a takeaway ordered from the
 * website are the same kind of order, but only one of them needs watching
 * on the online desk.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'source')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 16)->nullable()->after('order_type');
            $table->index('source');
        });

        // Everything placed until now came in one of two ways.
        DB::table('orders')->whereNull('restaurant_table_id')->update(['source' => 'counter']);
        DB::table('orders')->whereNotNull('restaurant_table_id')->update(['source' => 'qr']);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'source')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });
    }
};
