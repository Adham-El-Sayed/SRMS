<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A menu has an order of its own: starters first, drinks last. Sorting by
 * name put the desserts in the middle and, in Arabic, moved everything
 * again. This is the order the restaurant decides.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'products'] as $table) {
            if (Schema::hasColumn($table, 'sort_order')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedInteger('sort_order')->default(0)->after('name');
                $blueprint->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        foreach (['categories', 'products'] as $table) {
            if (! Schema::hasColumn($table, 'sort_order')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['sort_order']);
                $blueprint->dropColumn('sort_order');
            });
        }
    }
};
