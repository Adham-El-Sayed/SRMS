<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * What the restaurant actually sells.
 *
 * Every figure here comes from orders that were really placed — nothing is
 * guessed and nothing is promoted by hand. A restaurant that has just opened
 * has no recommendations at all, which is the honest answer: better an
 * ordinary menu than a "most ordered" badge on a dish nobody has tried.
 *
 * Cancelled orders are left out; they were never served.
 */
class RecommendationService
{
    /** How far back a dish counts as popular. */
    public const WINDOW_DAYS = 30;

    /** Below this, a dish has not been ordered enough to call it popular. */
    public const MIN_SOLD = 3;

    private const TTL_SECONDS = 900;

    /**
     * How many of each product went out in the window.
     *
     * @return array<int,int>  product id => quantity sold
     */
    public function sold(): array
    {
        return Cache::remember('menu.sold.' . self::WINDOW_DAYS, self::TTL_SECONDS, function () {
            return OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', '!=', 'cancelled')
                ->where('orders.created_at', '>=', now()->subDays(self::WINDOW_DAYS))
                ->whereNotNull('order_items.product_id')
                ->groupBy('order_items.product_id')
                ->pluck(
                    DB::raw('SUM(order_items.quantity) as total'),
                    'order_items.product_id'
                )
                ->map(fn ($total) => (int) $total)
                ->all();
        });
    }

    /**
     * The one dish leading each category, as long as it has sold enough to
     * mean something and is still available to order.
     *
     * @param  \Illuminate\Support\Collection  $menu  categories, each with products
     * @return array<int,int>  product id => quantity sold
     */
    public function bestPerCategory($menu): array
    {
        $sold = $this->sold();

        if (empty($sold)) {
            return [];
        }

        $best = [];

        foreach ($menu as $category) {
            $leader = null;
            $leaderSold = 0;

            foreach ($category->products as $product) {
                $count = $sold[$product->id] ?? 0;

                if ($count < self::MIN_SOLD || $count <= $leaderSold) {
                    continue;
                }

                // Pointing at something the kitchen has run out of is worse
                // than pointing at nothing.
                if (method_exists($product, 'isSoldOut') && $product->isSoldOut()) {
                    continue;
                }

                $leader = $product->id;
                $leaderSold = $count;
            }

            if ($leader !== null) {
                $best[$leader] = $leaderSold;
            }
        }

        return $best;
    }

    /**
     * The handful of dishes the whole restaurant orders most, for the strip at
     * the top of the menu. Returns products in selling order.
     */
    public function popular(int $limit = 6): \Illuminate\Support\Collection
    {
        $sold = $this->sold();

        if (empty($sold)) {
            return collect();
        }

        arsort($sold);

        $ids = collect($sold)
            ->filter(fn ($count) => $count >= self::MIN_SOLD)
            ->keys()
            ->take($limit * 2)      // room to drop whatever is unavailable
            ->all();

        if (empty($ids)) {
            return collect();
        }

        return Product::query()
            ->with('category')
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->filter(fn (Product $product) => $product->isOrderable() && $product->category?->is_active)
            ->sortByDesc(fn (Product $product) => $sold[$product->id] ?? 0)
            ->take($limit)
            ->values();
    }

    /** Called when an order is placed, so the numbers do not go stale. */
    public static function forget(): void
    {
        Cache::forget('menu.sold.' . self::WINDOW_DAYS);
    }
}
