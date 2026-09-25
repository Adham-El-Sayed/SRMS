<?php

namespace App\Support;

/**
 * Menu names are stored once, carrying both languages, the way Egyptian
 * menus are printed: "بيتزا مارجريتا · Margherita".
 *
 * Run together on one line they wrap three deep on a phone, so a card shows
 * the reader's own language first and the other underneath, smaller. A name
 * written in one language only passes through untouched.
 */
class Bilingual
{
    private const SEPARATOR = '·';

    /**
     * @return array{0: string, 1: ?string}  what to read first, then the rest
     */
    public static function split(?string $name): array
    {
        $name = trim((string) $name);

        if (! str_contains($name, self::SEPARATOR)) {
            return [$name, null];
        }

        [$first, $second] = array_map('trim', explode(self::SEPARATOR, $name, 2));

        if ($first === '' || $second === '') {
            return [$name ?: $first, null];
        }

        // Whichever side is Arabic leads for an Arabic reader.
        $firstIsArabic = self::isArabic($first);
        $wantArabic = app()->getLocale() === 'ar';

        return $firstIsArabic === $wantArabic
            ? [$first, $second]
            : [$second, $first];
    }

    public static function lead(?string $name): string
    {
        return self::split($name)[0];
    }

    private static function isArabic(string $text): bool
    {
        return (bool) preg_match('/\p{Arabic}/u', $text);
    }
}
