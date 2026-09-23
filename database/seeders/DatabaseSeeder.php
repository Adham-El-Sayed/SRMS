<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // The roles the app hands out. An admin assigns them from
        // Staff & Access; the first one is made with `php artisan srms:admin`.
        $this->call(RoleSeeder::class);
    }
}
