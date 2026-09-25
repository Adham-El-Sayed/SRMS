<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employment details, kept apart from what an account may open: the national
 * ID and monthly salary on the record, and every bonus or deduction as its
 * own dated line so the month's pay can be explained.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('national_id', 20)->nullable()->unique();
            $table->string('job_title', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->date('hired_on')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // bonus | deduction
            $table->string('kind', 12);
            $table->decimal('amount', 10, 2);
            $table->string('reason', 255)->nullable();
            $table->date('happened_on');

            // Who recorded it, so a change to someone's pay has a name on it.
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'happened_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
        Schema::dropIfExists('employee_records');
    }
};
