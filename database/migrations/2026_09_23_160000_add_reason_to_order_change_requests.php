<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Why staff are being called: the guest asked for a waiter, or changed their order. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_change_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('order_change_requests', 'reason')) {
                // waiter | edited
                $table->string('reason', 20)->default('waiter')->after('order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_change_requests', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
