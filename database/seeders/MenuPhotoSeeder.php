<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\MenuService;
use App\Support\Bilingual;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attaches the menu photographs to their dishes.
 *
 * A photo is matched by the dish's own name rather than by its id, because
 * ids differ between one installation and the next: the file for
 * "بيتزا مارجريتا · Margherita" is photo-margherita.jpg wherever it runs.
 *
 * Dishes with no photo yet keep their drawn tile, so the menu is never
 * half-empty while the pictures are still being made.
 */
class MenuPhotoSeeder extends Seeder
{
    private const FOLDER = 'menu/products';

    public function run(): void
    {
        $attached = 0;
        $waiting = [];

        foreach (Product::all() as $product) {
            $path = self::FOLDER . '/photo-' . self::slugFor($product->name) . '.jpg';

            if (! Storage::disk('public')->exists($path)) {
                if ($product->is_active) {
                    $waiting[] = Bilingual::lead($product->name);
                }
                continue;
            }

            if ($product->image !== $path) {
                $product->update(['image' => $path]);
            }

            $attached++;
        }

        MenuService::forget();

        $this->command?->info($attached . ' dishes now show a photograph.');

        if ($waiting) {
            $this->command?->warn(count($waiting) . ' still on a drawn tile: ' . implode(', ', array_slice($waiting, 0, 8))
                . (count($waiting) > 8 ? '…' : ''));
        }
    }

    /** The English half of the name, as a file-safe slug. */
    public static function slugFor(string $name): string
    {
        [$first, $second] = Bilingual::splitRaw($name);

        $label = $second ?? $first;

        if (preg_match('/\p{Arabic}/u', $label)) {
            $label = $first;
        }

        return Str::slug($label);
    }
}
