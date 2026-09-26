<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Secret handed only to the guest who placed the order. Every guest
            // call about an existing order must present it.
            $table->string('access_token', 64)->nullable()->unique()->after('id');

            // When staff confirmed the money was received.
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });

        // Existing orders get a token too, so nothing is left unprotected.
        DB::table('orders')->whereNull('access_token')->orderBy('id')->chunkById(200, function ($orders) {
            foreach ($orders as $order) {
                DB::table('orders')->where('id', $order->id)->update(['access_token' => Str::random(48)]);
            }
        });

        Schema::table('shifts', function (Blueprint $table) {
            // What the system expected when the shift was closed. Frozen so a
            // closed shift's figures never move afterwards.
            $table->decimal('expected_cash', 10, 2)->nullable()->after('counted_cash');
            $table->decimal('expected_card', 10, 2)->nullable()->after('expected_cash');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['access_token']);
            $table->dropColumn(['access_token', 'paid_at']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['expected_cash', 'expected_card']);
        });
    }
};
