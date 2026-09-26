<?php

namespace Database\Seeders;

use App\Support\Access;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/** The roles the app hands out. Safe to run again at any time. */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Access::ROLES as $role) {
            Role::findOrCreate($role);
        }
    }
}
