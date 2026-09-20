<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'client_name')) {
                $table->string('client_name')->nullable()->after('shift_id');
            }
            if (! Schema::hasColumn('orders', 'client_phone')) {
                $table->string('client_phone')->nullable()->after('client_name');
            }
            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'card'])->nullable()->after('total');
            }
            if (! Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->after('payment_method');
            }
            if (! Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        //
    }
};