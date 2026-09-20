<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('restaurant_tables', function (Blueprint $table) {
    $table->id();

        $table->string('number')->unique();

        $table->unsignedTinyInteger('capacity')->default(4);

        $table->string('status')->default('available');

        $table->uuid('qr_token')->unique();

        $table->boolean('is_active')->default(true);

        $table->softDeletes();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
