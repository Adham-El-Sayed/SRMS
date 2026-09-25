<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * What the kitchen has run out of. Marking a dish finished keeps it on the
 * menu, shown as unavailable, so nobody can order it and guests can still
 * see it is something the restaurant serves.
 */
class KitchenStockController extends Controller
{
    public function index(): View
    {
        return view('kitchen.stock', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->with(['products' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update([
            'sold_out_at' => $product->isSoldOut() ? null : now(),
        ]);

        return back()->with('success', $product->isSoldOut()
            ? __(':product is now marked finished.', ['product' => $product->name])
            : __(':product is available again.', ['product' => $product->name]));
    }
}
