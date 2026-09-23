<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Access;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

/**
 * Creates the first administrator, or promotes an existing account.
 * After this, every other account is managed from Staff & Access.
 */
class MakeAdmin extends Command
{
    protected $signature = 'srms:admin {email? : The account to create or promote}';

    protected $description = 'Create the first administrator account (or promote an existing one)';

    public function handle(): int
    {
        foreach (Access::ROLES as $role) {
            Role::findOrCreate($role);
        }

        $email = $this->argument('email') ?: $this->ask('Email address');

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->syncRoles(['admin']);
            $this->info("{$user->name} ({$email}) is now an administrator.");

            return self::SUCCESS;
        }

        $name = $this->ask('Name');
        $password = $this->secret('Password');

        $check = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            [
                'email' => ['required', 'email', 'unique:users,email'],
                'name' => ['required', 'string', 'max:100'],
                'password' => ['required', Password::defaults()],
            ]
        );

        if ($check->fails()) {
            foreach ($check->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('admin');

        $this->info("Administrator {$name} ({$email}) created. You can sign in now.");

        return self::SUCCESS;
    }
}
