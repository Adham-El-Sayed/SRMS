<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Category;
class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(): View
    {
        $products = $this->productService->getAll();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
    $categories = Category::where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->create(
            $request->validated()
        );

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
    $categories = Category::where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('products.edit', compact('product', 'categories'));
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ): RedirectResponse {
        $this->productService->update(
            $product,
            $request->validated()
        );

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}