<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The menu guests see. Every QR scan asks for it and it only changes when
 * staff edit the menu, so it is cached and thrown away the moment a category
 * or product is saved, toggled or deleted.
 *
 * Only plain arrays are cached, never model objects: Laravel's database cache
 * refuses to rebuild arbitrary classes, and storing raw rows keeps the cache
 * readable and safe. The models are rebuilt on the way out, so views keep
 * working with ordinary Eloquent objects.
 */
class MenuService
{
    private const VERSION_KEY = 'menu.version';
    private const TTL_SECONDS = 600;

    /** Sold-out dishes stay on the menu, marked, so guests can see we serve them. */

    public function getMenu(): Collection
    {
        $rows = Cache::remember(
            'menu.' . self::version(),
            self::TTL_SECONDS,
            fn () => $this->query()->toArray()
        );

        return $this->hydrate($rows);
    }

    /** Called whenever menu content changes, so the next scan is rebuilt. */
    public static function forget(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    private function query(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->with([
                'products' => fn ($query) => $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            // The restaurant's own order — starters first, drinks last.
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /** Turn the cached rows back into categories, each with its products. */
    private function hydrate(array $rows): Collection
    {
        $categories = Category::hydrate(array_map(
            fn (array $row) => \Illuminate\Support\Arr::except($row, ['products']),
            $rows
        ));

        foreach ($categories as $index => $category) {
            $category->setRelation(
                'products',
                Product::hydrate($rows[$index]['products'] ?? [])
            );
        }

        return $categories;
    }
}
