<?php

namespace App\Http\Controllers;

use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** How this restaurant serves guests. Admin only. */
class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'enabled' => Settings::enabledTypes(),
            'deliveryFee' => Settings::deliveryFee(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_types' => ['nullable', 'array'],
            'service_types.*' => ['in:takeaway,delivery,online'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0', 'max:9999'],
        ]);

        // Dine-in is how a restaurant works; it is always on.
        $types = array_values(array_unique(array_merge(['dine_in'], $data['service_types'] ?? [])));

        Settings::put([
            'service_types' => $types,
            'delivery_fee' => $data['delivery_fee'] ?? 0,
        ]);

        return back()->with('success', __('Settings saved.'));
    }
}
