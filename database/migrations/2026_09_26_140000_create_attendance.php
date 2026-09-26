<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who worked, and who did not.
 *
 * One row per person per day, so a month is counted by counting rows. The
 * day is unique per person: marking someone twice corrects the first mark
 * rather than adding a second one.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance')) {
            return;
        }

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            // present · absent · leave
            $table->string('status', 12)->default('present');
            $table->dateTime('arrived_at')->nullable();
            $table->dateTime('left_at')->nullable();
            $table->string('note', 255)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'day']);
            $table->index(['day', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
