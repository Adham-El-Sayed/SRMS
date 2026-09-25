<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * A drawing for a dish that has no photograph yet.
 *
 * A menu of empty grey squares looks broken; a menu of warm, hand-drawn
 * tiles looks deliberate. These are plainly illustrations, not photographs
 * pretending to be the food, and staff replace them from Menu Management
 * the moment they have real pictures.
 *
 * Each dish gets the line art of its family over a colour taken from its own
 * name, so a grid of pasta never comes out as the same tile ten times.
 */
class DishArt
{
    /** Two warm tones per family: the background fades from one to the other. */
    private const PALETTES = [
        'pizza'   => ['#F3C878', '#D2641F'],
        'pasta'   => ['#F2D08A', '#C07F2B'],
        'salad'   => ['#CFE0AE', '#6E8A4E'],
        'soup'    => ['#F2C08C', '#B4611F'],
        'sandwich'=> ['#EFC489', '#A9662B'],
        'chicken' => ['#F3CE92', '#B0762A'],
        'seafood' => ['#BCD9E4', '#3F6C82'],
        'dessert' => ['#F4CBB4', '#B0653E'],
        'drink'   => ['#C6DDE6', '#3B6382'],
        'starter' => ['#EFD3A0', '#9A6C24'],
    ];

    /**
     * The line art itself, drawn on a 160 x 120 canvas. Kept simple on
     * purpose: at the size a dish card shows, detail turns to mud.
     */
    private static function glyph(string $family): string
    {
        return match ($family) {
            'pizza' => '<path d="M80 34 122 92a52 52 0 0 1-84 0Z"/>
                        <circle cx="80" cy="60" r="5" fill="currentColor" stroke="none"/>
                        <circle cx="66" cy="76" r="4.5" fill="currentColor" stroke="none"/>
                        <circle cx="94" cy="76" r="4.5" fill="currentColor" stroke="none"/>',

            'pasta' => '<path d="M44 66h72a36 36 0 0 1-72 0Z"/>
                        <path d="M56 60c6-9 14-9 20 0M84 60c6-9 14-9 20 0"/>
                        <path d="M38 96h84"/>',

            'salad' => '<path d="M46 64h68a34 34 0 0 1-68 0Z"/>
                        <path d="M66 62c-8-12 2-24 14-20 10-10 24-2 22 10"/>
                        <path d="M80 42v20"/>',

            'soup' => '<path d="M48 62h64a32 32 0 0 1-64 0Z"/>
                       <path d="M112 70h10a9 9 0 0 1 0 18h-6"/>
                       <path d="M70 40c-6-8 6-12 0-20M90 40c-6-8 6-12 0-20"/>',

            'sandwich' => '<path d="M44 58c0-10 16-16 36-16s36 6 36 16Z"/>
                           <path d="M44 66h72M44 76h72"/>
                           <path d="M44 84c0 10 16 14 36 14s36-4 36-14Z"/>',

            // A drumstick: a closed piece of meat, then the bone and its knuckles.
            'chicken' => '<path d="M92 34c16 0 26 12 26 26 0 14-10 24-24 25-7 1-11 3-14 8l-8-8c5-3 7-7 8-14 1-14 10-23 24-23Z"/>
                          <path d="M79 85 64 100"/>
                          <circle cx="58" cy="97" r="8"/>
                          <circle cx="67" cy="106" r="8"/>',

            'seafood' => '<path d="M40 66c14-18 44-24 62-10 10 8 10 22 0 30-18 14-48 8-62-10Z"/>
                          <path d="M120 66 136 52v28Z"/>
                          <circle cx="62" cy="62" r="3" fill="currentColor" stroke="none"/>',

            // A slice of cake, on its side, with a cherry on top.
            'dessert' => '<path d="M46 96h68l-34-52Z"/>
                          <path d="M57 79h46"/>
                          <path d="M80 44v-6"/>
                          <circle cx="80" cy="33" r="6"/>',

            'drink' => '<path d="M58 44h44l-6 54H64Z"/>
                        <path d="M62 62h36"/>
                        <path d="M80 44V28h14"/>',

            // A plate laid with a fork and a knife.
            default => '<circle cx="80" cy="66" r="26"/>
                        <circle cx="80" cy="66" r="17"/>
                        <path d="M38 40v20a6 6 0 0 0 12 0V40M44 60v34"/>
                        <path d="M116 40c8 0 10 20 2 22v32"/>',
        };
    }

    /** Draws the tile and returns the path to store on the product. */
    public static function make(string $family, string $seed, string $folder = 'menu/products'): string
    {
        [$light, $deep] = self::PALETTES[$family] ?? self::PALETTES['starter'];

        // The same dish always gets the same tilt and wash.
        $hash = crc32($seed);
        $angle = ($hash % 60) - 30;
        $shift = 26 + ($hash >> 6) % 34;
        $glyph = self::glyph($family);

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 120" width="640" height="480" role="img">
            <defs>
                <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1" gradientTransform="rotate({$angle} .5 .5)">
                    <stop offset="0%" stop-color="{$light}"/>
                    <stop offset="100%" stop-color="{$deep}"/>
                </linearGradient>
            </defs>

            <rect width="160" height="120" fill="url(#bg)"/>

            <circle cx="{$shift}" cy="18" r="34" fill="#FFFFFF" opacity=".08"/>
            <circle cx="140" cy="108" r="28" fill="#2B1C10" opacity=".07"/>

            <g fill="none" stroke="#FFF6EA" stroke-width="3.2"
               stroke-linecap="round" stroke-linejoin="round" opacity=".92" color="#FFF6EA">
                {$glyph}
            </g>
        </svg>
        SVG;

        $path = $folder . '/art-' . substr(sha1($family . '|' . $seed), 0, 20) . '.svg';

        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
