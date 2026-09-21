<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MenuManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $categories = Category::with('products')
            ->orderBy('name')
            ->get();

        return view('menu-management.index', compact('categories'));
    }


    /*
    |--------------------------------------------------------------------------
    | Create Category
    |--------------------------------------------------------------------------
    */

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('menu/categories', 'public');
        }

        Category::create($validated);

        return redirect()
            ->route('menu.management')
            ->with('success', __('Category created successfully.'));
    }


    /*
    |--------------------------------------------------------------------------
    | Update Category
    |--------------------------------------------------------------------------
    */

   public function updateCategory(Request $request, Category $category)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        'is_active' => ['nullable', 'boolean'],
        'remove_image' => ['nullable', 'boolean'],
    ]);

    $validated['is_active'] = $request->boolean('is_active');

    // Remove old image
    if ($request->boolean('remove_image') && $category->image) {
        Storage::disk('public')->delete($category->image);
        $validated['image'] = null;
    }

    // Replace image
    if ($request->hasFile('image')) {

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $validated['image'] = $request
            ->file('image')
            ->store('menu/categories', 'public');
    }

    unset($validated['remove_image']);

    $category->update($validated);

    return redirect()
        ->route('menu.management')
        ->with('success', __('Category updated successfully.'));
}


    /*
    |--------------------------------------------------------------------------
    | Delete Category
    |--------------------------------------------------------------------------
    */

    // public function destroyCategory(Category $category)
    // {
    //     if ($category->products()->exists()) {
    //         return redirect()
    //             ->route('menu.management')
    //             ->with('error', __('Cannot delete this category because it contains products. Deactivate it instead.'));
    //     }

    //     if ($category->image) {
    //         Storage::disk('public')->delete($category->image);
    //     }

    //     $category->delete();

    //     return redirect()
    //         ->route('menu.management')
    //         ->with('success', __('Category deleted successfully.'));
    // }


    /*
    |--------------------------------------------------------------------------
    | Create Product
    |--------------------------------------------------------------------------
    */

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('menu/products', 'public');
        }

        Product::create($validated);

        return redirect()
            ->route('menu.management')
            ->with('success', __('Product created successfully.'));
    }


    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */

    public function updateProduct(Request $request, Product $product)
{
    $validated = $request->validate([
        'category_id' => ['required', 'exists:categories,id'],
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'price' => ['required', 'numeric', 'min:0'],
        'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        'is_active' => ['nullable', 'boolean'],
        'remove_image' => ['nullable', 'boolean'],
    ]);

    $validated['is_active'] = $request->boolean('is_active');

    // Remove old image
    if ($request->boolean('remove_image') && $product->image) {
        Storage::disk('public')->delete($product->image);
        $validated['image'] = null;
    }

    // Replace image
    if ($request->hasFile('image')) {

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $validated['image'] = $request
            ->file('image')
            ->store('menu/products', 'public');
    }

    unset($validated['remove_image']);

    $product->update($validated);

    return redirect()
        ->route('menu.management')
        ->with('success', __('Product updated successfully.'));
}


    /*
    |--------------------------------------------------------------------------
    | Delete Product
    |--------------------------------------------------------------------------
    */

// public function destroyProduct(Product $product)
// {
//     $isUsedInOrders = DB::table('order_items')
//         ->where('product_id', $product->id)
//         ->exists();

//     if ($isUsedInOrders) {
//         return redirect()
//             ->route('menu.management')
//             ->with(
//                 'error',
//                 'Cannot delete this product because it is already used in an order. Deactivate it instead.'
//             );
//     }

//     if ($product->image) {
//         Storage::disk('public')->delete($product->image);
//     }

//     $product->delete();

//     return redirect()
//         ->route('menu.management')
//         ->with('success', __('Product deleted successfully.'));
// }

public function toggleCategory(Category $category)
{
    $category->update([
        'is_active' => ! $category->is_active,
    ]);

    return redirect()
        ->route('menu.management')
        ->with(
            'success',
            $category->is_active
                ? 'Category activated successfully.'
                : 'Category deactivated successfully.'
        );
}

public function toggleProduct(Product $product)
{
    $product->update([
        'is_active' => ! $product->is_active,
    ]);

    return redirect()
        ->route('menu.management')
        ->with(
            'success',
            $product->is_active
                ? 'Product activated successfully.'
                : 'Product deactivated successfully.'
        );
}


}