<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Who may open what, in one place, so sign-in, the navigation, the role
 * middleware and the "waiting for access" page all agree.
 *
 * A super admin may do everything. Because that means every page at once,
 * they also pick a workspace — admin, cashier or kitchen — which decides
 * what the navigation shows and where signing in lands them. The workspace
 * is a convenience, not a restriction: a super admin is never refused a page.
 */
class Access
{
    /** Roles an administrator can hand out, in the order they appear in the UI. */
    public const ROLES = ['super-admin', 'admin', 'cashier', 'kitchen'];

    /** Workspaces a super admin can switch between. */
    public const WORKSPACES = ['admin', 'cashier', 'kitchen'];

    public const SESSION_KEY = 'workspace';

    public static function isSuperAdmin(?User $user): bool
    {
        return (bool) $user?->hasRole('super-admin');
    }

    /**
     * The workspace whose navigation this account sees. Ordinary accounts get
     * the one that matches their role; a super admin gets the one they chose.
     */
    public static function workspaceFor(?User $user, ?Request $request = null): string
    {
        if (! $user) {
            return 'none';
        }

        if (self::isSuperAdmin($user)) {
            $chosen = ($request ?? request())->session()->get(self::SESSION_KEY);

            return in_array($chosen, self::WORKSPACES, true) ? $chosen : 'admin';
        }

        foreach (self::WORKSPACES as $workspace) {
            if ($user->hasRole($workspace)) {
                return $workspace;
            }
        }

        return 'none';
    }

    /** The first page an account should see after signing in. */
    public static function homeRouteFor(?User $user, ?Request $request = null): string
    {
        return match (self::workspaceFor($user, $request)) {
            'admin' => 'dashboard',
            'cashier' => 'cash-payments.index',
            'kitchen' => 'kitchen.orders',
            default => $user ? 'no-access' : 'login',
        };
    }

    public static function homeUrlFor(?User $user, ?Request $request = null): string
    {
        return route(self::homeRouteFor($user, $request));
    }

    /** What a role may open, in plain language. */
    public static function describe(string $role): string
    {
        return match ($role) {
            'super-admin' => __('Everything, and can switch between the admin, cashier and kitchen views.'),
            'admin' => __('Menu, tables, payments, shifts, reports and staff.'),
            'cashier' => __('Payments and shifts — the money side.'),
            'kitchen' => __('The kitchen board only.'),
            default => __('No access yet.'),
        };
    }

    /** The name of a role as staff would say it. */
    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'super-admin' => __('Super admin'),
            'admin' => __('Admin'),
            'cashier' => __('Cashier'),
            'kitchen' => __('Kitchen'),
            default => __('No access yet'),
        };
    }

    /** The name of a workspace as staff would say it. */
    public static function workspaceLabel(string $workspace): string
    {
        return match ($workspace) {
            'admin' => __('Admin'),
            'cashier' => __('Cashier'),
            'kitchen' => __('Kitchen'),
            default => __('No access yet'),
        };
    }
}
