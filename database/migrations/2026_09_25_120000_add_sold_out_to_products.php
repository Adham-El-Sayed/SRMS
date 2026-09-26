<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The kitchen runs out of something mid-service. This marks it as finished
 * for tonight without touching whether the product is on the menu at all,
 * which is the manager's decision.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'sold_out_at')) {
                $table->timestamp('sold_out_at')->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sold_out_at');
        });
    }
};
