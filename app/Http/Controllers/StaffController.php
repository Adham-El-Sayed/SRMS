<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Access;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Who works here and what each of them may open. Admin only.
 *
 * An admin can never take away their own admin role or delete their own
 * account here, so the restaurant cannot end up with no administrator.
 */
class StaffController extends Controller
{
    public function index(): View
    {
        return view('staff.index', [
            'users' => User::orderBy('name')->with('roles')->get(),
            'roles' => Access::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['nullable', Rule::in(Access::ROLES)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        if (! empty($data['role'])) {
            Role::findOrCreate($data['role']);
            $user->assignRole($data['role']);
        }

        return back()->with('success', __('Account created for :name.', ['name' => $user->name]));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['nullable', Rule::in(Access::ROLES)],
        ]);

        if ($user->is($request->user()) && ($data['role'] ?? null) !== 'admin') {
            return back()->with('error', __('You cannot take away your own admin access.'));
        }

        if (empty($data['role'])) {
            $user->syncRoles([]);
        } else {
            Role::findOrCreate($data['role']);
            $user->syncRoles([$data['role']]);
        }

        return back()->with('success', __('Access updated for :name.', ['name' => $user->name]));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', __('You cannot delete your own account here.'));
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', __('Account for :name was deleted.', ['name' => $name]));
    }
}
