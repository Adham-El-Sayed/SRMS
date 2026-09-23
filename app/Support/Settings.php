<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The restaurant's own settings: which ways it serves guests, and what
 * delivery costs. Read on nearly every page, so they are cached and the
 * cache is dropped the moment they are saved.
 */
class Settings
{
    public const CACHE_KEY = 'settings.all';

    /** Every way an order can reach a guest. */
    public const TYPES = ['dine_in', 'takeaway', 'delivery', 'online'];

    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return [];
            }

            return DB::table('settings')->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => is_array($value) ? implode(',', $value) : (string) $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    /** The service types this restaurant offers. Dine-in is always on. */
    public static function enabledTypes(): array
    {
        $saved = self::get('service_types');

        if ($saved === null || $saved === '') {
            return ['dine_in'];
        }

        $types = array_values(array_intersect(explode(',', $saved), self::TYPES));

        return $types ?: ['dine_in'];
    }

    public static function offers(string $type): bool
    {
        return in_array($type, self::enabledTypes(), true);
    }

    public static function deliveryFee(): float
    {
        return (float) self::get('delivery_fee', 0);
    }

    /** What staff call each type. */
    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'dine_in' => __('Dine-in'),
            'takeaway' => __('Takeaway'),
            'delivery' => __('Delivery'),
            'online' => __('Online'),
            default => $type,
        };
    }
}
