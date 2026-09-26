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
        'rice'    => ['#F0D7A4', '#B4883A'],
        'steak'   => ['#E6B08A', '#8E4A2A'],
        'icecream'=> ['#F6D7C6', '#B97A55'],
        'cake'    => ['#EFC6AE', '#A45F3C'],
        'bottle'  => ['#C9E2E9', '#35708A'],
        'hotcup'  => ['#E8CBA8', '#8A5A2B'],
        'juice'   => ['#F8CE8E', '#C8781A'],
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

            // A bowl of rice with a spoon resting in it.
            'rice' => '<path d="M44 64h72a36 36 0 0 1-72 0Z"/>
                       <path d="M52 56c8-7 18-7 26 0M82 56c8-7 18-7 26 0"/>
                       <path d="M112 44c9 0 13 9 9 16l-7 12"/>',

            // A steak with grill marks.
            'steak' => '<path d="M46 60c8-16 34-22 54-14 16 6 20 24 8 34-14 12-40 10-54-4-5-5-9-10-8-16Z"/>
                        <path d="M62 56 78 72M78 52 94 68M94 50l14 14"/>',

            // A cone with two scoops.
            'icecream' => '<circle cx="70" cy="48" r="14"/>
                           <circle cx="92" cy="48" r="14"/>
                           <path d="M54 58h54l-27 46Z"/>',

            // A round cake with a slice cut out.
            'cake' => '<path d="M40 70a41 26 0 0 0 82 0Z"/>
                       <path d="M40 70a41 26 0 0 1 82 0"/>
                       <path d="M81 44v26l26 12"/>
                       <path d="M81 44v-8"/>',

            // A bottle with a cap.
            'bottle' => '<path d="M70 34h22v12c0 6 8 8 8 18v38a6 6 0 0 1-6 6H68a6 6 0 0 1-6-6V64c0-10 8-12 8-18Z"/>
                         <path d="M62 70h38"/>',

            // A cup and saucer, steam rising.
            'hotcup' => '<path d="M52 56h52v20a26 26 0 0 1-52 0Z"/>
                         <path d="M104 62h10a9 9 0 0 1 0 18h-8"/>
                         <path d="M42 104h74"/>
                         <path d="M70 42c-5-6 5-9 0-15M88 42c-5-6 5-9 0-15"/>',

            // A tall glass with a slice on the rim.
            'juice' => '<path d="M60 40h42l-6 62H66Z"/>
                        <path d="M64 58h34"/>
                        <circle cx="104" cy="40" r="10"/>
                        <path d="M104 30v20M94 40h20"/>',

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
