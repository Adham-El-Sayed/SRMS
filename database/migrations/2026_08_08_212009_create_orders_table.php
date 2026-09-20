<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            $table->foreignId('restaurant_table_id')
                ->constrained('restaurant_tables')
                ->restrictOnDelete();

            $table->string('client_name')->nullable();

            $table->string('client_phone')->nullable();

            $table->enum('status', [
                'pending',
                'preparing',
                'ready',
                'completed',
                'cancelled',
                'confirmed',
            ])->default('pending');

            $table->decimal('subtotal', 10, 2)->default(0);

            $table->decimal('fees', 10, 2)->default(0);

            $table->decimal('total', 10, 2)->default(0);

            $table->enum('payment_method', [
                'cash',
                'card',
            ])->nullable();

            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};