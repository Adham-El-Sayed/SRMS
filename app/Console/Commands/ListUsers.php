<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Access;
use Illuminate\Console\Command;

/** Who has an account, and what each of them may open. */
class ListUsers extends Command
{
    protected $signature = 'srms:users {search? : Only accounts whose name or email contains this}';

    protected $description = 'List the accounts and their access';

    public function handle(): int
    {
        $search = $this->argument('search');

        $users = User::with('roles')
            ->when($search, fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            $this->warn($search ? "No account matches \"{$search}\"." : 'There are no accounts yet.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Access', 'Created'],
            $users->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
                $user->roles->pluck('name')->implode(', ') ?: '— no role yet —',
                $user->created_at?->format('Y-m-d'),
            ])->all()
        );

        $this->line('  Change access from Staff & Access in the app, or with srms:admin.');

        return self::SUCCESS;
    }
}
