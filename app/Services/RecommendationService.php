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

    /** And two dishes have to meet this often before they count as a pair. */
    public const MIN_TOGETHER = 4;

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
     * The handful of dishes for the strip at the top of the menu: the leader
     * of each course, best-selling first.
     *
     * Not simply the top sellers overall — almost every basket has a drink in
     * it, so a plain count fills the strip with water and tea. One per course
     * is both true and worth reading: a pizza, a pasta, something to start.
     *
     * @param  \Illuminate\Support\Collection|null  $menu  the menu, if the page already has it
     */
    public function popular(int $limit = 6, $menu = null): \Illuminate\Support\Collection
    {
        $menu ??= (new MenuService())->getMenu();

        $best = $this->bestPerCategory($menu);

        if (empty($best)) {
            return collect();
        }

        arsort($best);

        return Product::query()
            ->with('category')
            ->whereIn('id', array_keys($best))
            ->where('is_active', true)
            ->get()
            ->filter(fn (Product $product) => $product->isOrderable() && $product->category?->is_active)
            ->sortByDesc(fn (Product $product) => $best[$product->id] ?? 0)
            ->take($limit)
            ->values();
    }

    /**
     * What people put in the same basket.
     *
     * For every dish, the two or three things most often ordered alongside
     * it — worked out from real baskets, not from what the kitchen would
     * like to sell. A pair has to have happened a few times before it counts,
     * so one odd order never turns into a suggestion.
     *
     * The whole map is small enough to hand to the page in one go, which
     * saves asking the server again every time something is added.
     *
     * @return array<int,int[]>  product id => the ids that go with it
     */
    public function pairs(int $each = 3): array
    {
        return Cache::remember('menu.pairs.' . self::WINDOW_DAYS, self::TTL_SECONDS, function () use ($each) {
            $baskets = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', '!=', 'cancelled')
                ->where('orders.created_at', '>=', now()->subDays(self::WINDOW_DAYS))
                ->whereNotNull('order_items.product_id')
                ->get(['order_items.order_id', 'order_items.product_id'])
                ->groupBy('order_id')
                ->map(fn ($lines) => $lines->pluck('product_id')->unique()->values()->all())
                ->filter(fn (array $ids) => count($ids) > 1);

            $together = [];

            foreach ($baskets as $ids) {
                foreach ($ids as $one) {
                    foreach ($ids as $other) {
                        if ($one === $other) {
                            continue;
                        }

                        $together[$one][$other] = ($together[$one][$other] ?? 0) + 1;
                    }
                }
            }

            // Only what is still on the menu and can be ordered today.
            $orderable = Product::query()
                ->with('category')
                ->where('is_active', true)
                ->whereNull('sold_out_at')
                ->get()
                ->filter(fn (Product $product) => $product->category?->is_active)
                ->pluck('id')
                ->flip();

            $map = [];

            foreach ($together as $product => $counts) {
                if (! $orderable->has($product)) {
                    continue;
                }

                arsort($counts);

                $picked = [];

                foreach ($counts as $other => $times) {
                    if ($times < self::MIN_TOGETHER || ! $orderable->has($other)) {
                        continue;
                    }

                    $picked[] = (int) $other;

                    if (count($picked) >= $each) {
                        break;
                    }
                }

                if ($picked) {
                    $map[(int) $product] = $picked;
                }
            }

            return $map;
        });
    }

    /** Called when an order is placed, so the numbers do not go stale. */
    public static function forget(): void
    {
        Cache::forget('menu.sold.' . self::WINDOW_DAYS);
        Cache::forget('menu.pairs.' . self::WINDOW_DAYS);
    }
}
