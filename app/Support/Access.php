<?php

namespace App\Support;

use App\Models\User;

/**
 * Where each kind of account belongs, in one place, so sign-in, the
 * "no access yet" page and the navigation all agree.
 */
class Access
{
    /** Roles an admin can hand out, in the order they appear in the UI. */
    public const ROLES = ['admin', 'kitchen'];

    /** The first page an account should see after signing in. */
    public static function homeRouteFor(?User $user): string
    {
        if (! $user) {
            return 'login';
        }

        if ($user->hasRole('admin')) {
            return 'dashboard';
        }

        if ($user->hasRole('kitchen')) {
            return 'kitchen.orders';
        }

        return 'no-access';
    }

    public static function homeUrlFor(?User $user): string
    {
        return route(self::homeRouteFor($user));
    }

    /** A plain-language description of what a role may open. */
    public static function describe(string $role): string
    {
        return match ($role) {
            'admin' => __('Everything: menu, tables, payments, shifts and reports.'),
            'kitchen' => __('The kitchen board and the dashboard only.'),
            default => __('No access yet.'),
        };
    }
}
