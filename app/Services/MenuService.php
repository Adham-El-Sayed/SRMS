<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class MenuService
{
    public function getMenu(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->with([
                'products' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();
    }
}