<?php

namespace App\Support;

/**
 * "احسبلي 15% من 7000".
 *
 * A small arithmetic parser for the question box. It does its own
 * tokenising and evaluation rather than handing the text to eval() or to a
 * database: the input comes from a browser, and a calculator is not worth
 * a way to run arbitrary code on the till.
 *
 * It understands + - * / ( ), percentages, Arabic-Indic digits and the
 * Arabic decimal separator, and the two ways people ask for a share of
 * something: "15% من 7000" and "15% of 7000".
 */
class Arithmetic
{
    private const DIGITS = ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
                            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9'];

    /** Worth trying: it has numbers and something to do with them. */
    public static function looksLikeSum(string $text): bool
    {
        $text = self::tidy($text);

        if (!preg_match('/\d/', $text)) {
            return false;
        }

        // two numbers with an operator between them, or a percentage of something
        return (bool) preg_match('/\d\s*[\+\-\*\/×÷%]\s*\(?\s*\d/u', $text)
            || (bool) preg_match('/\d\s*%\s*(من|of)\s*\d/u', $text);
    }

    /** The answer, or null when the text is not a sum after all. */
    public static function evaluate(string $text): ?float
    {
        $text = self::tidy($text);

        if ($text === '' || strlen($text) > 200) {
            return null;
        }

        // "15% من 7000" and "15% of 7000" both mean 15 ÷ 100 × 7000.
        $text = preg_replace('/(\d(?:[\d.]*\d)?)\s*%\s*(?:من|of)\s+/u', '($1/100)*', $text);

        $tokens = self::tokenise($text);

        if ($tokens === null || $tokens === []) {
            return null;
        }

        $rpn = self::toPostfix($tokens);

        return $rpn === null ? null : self::run($rpn);
    }

    /* ------------------------------------------------------------------ */

    /** Arabic digits and separators become the ones the parser reads. */
    private static function tidy(string $text): string
    {
        $text = strtr($text, self::DIGITS);
        $text = str_replace(['٫', '،', '×', '÷', '−', '٪'], ['.', ',', '*', '/', '-', '%'], $text);

        // thousands separators, but only between digits: 1,500 is 1500
        $text = preg_replace('/(?<=\d),(?=\d{3}\b)/', '', $text);

        // the words people put around the sum
        $text = preg_replace('/\b(احسبلي|احسب|حاسب|كام|يساوي|equals?|calculate|what\s+is|whats)\b/iu', ' ', $text);
        $text = str_replace(['=', '؟', '?'], ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** @return array<int, string>|null null when something is not arithmetic */
    private static function tokenise(string $text): ?array
    {
        $tokens = [];
        $length = strlen($text);
        $i = 0;

        while ($i < $length) {
            $c = $text[$i];

            if ($c === ' ') { $i++; continue; }

            if (ctype_digit($c) || $c === '.') {
                $number = '';
                while ($i < $length && (ctype_digit($text[$i]) || $text[$i] === '.')) {
                    $number .= $text[$i++];
                }
                if (!is_numeric($number)) return null;
                $tokens[] = $number;
                continue;
            }

            if (str_contains('+-*/()', $c)) { $tokens[] = $c; $i++; continue; }

            // a percentage with nothing after it is just a division by a hundred
            if ($c === '%') { $tokens[] = '/'; $tokens[] = '100'; $i++; continue; }

            // anything else means this was not a sum
            return null;
        }

        return $tokens;
    }

    /** Shunting-yard: infix to postfix. @return array<int, string>|null */
    private static function toPostfix(array $tokens): ?array
    {
        $rank = ['+' => 1, '-' => 1, '*' => 2, '/' => 2];
        $out = [];
        $ops = [];
        $previous = null;

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $out[] = $token;
            } elseif ($token === '(') {
                $ops[] = $token;
            } elseif ($token === ')') {
                while ($ops && end($ops) !== '(') $out[] = array_pop($ops);
                if (!$ops) return null;                       // a bracket that never opened
                array_pop($ops);
            } else {
                // a minus at the start, or after another operator, is a sign
                if ($token === '-' && ($previous === null || $previous === '(' || isset($rank[$previous]))) {
                    $out[] = '0';
                }
                while ($ops && end($ops) !== '(' && $rank[end($ops)] >= $rank[$token]) {
                    $out[] = array_pop($ops);
                }
                $ops[] = $token;
            }

            $previous = $token;
        }

        while ($ops) {
            $op = array_pop($ops);
            if ($op === '(') return null;                     // a bracket that never closed
            $out[] = $op;
        }

        return $out;
    }

    private static function run(array $rpn): ?float
    {
        $stack = [];

        foreach ($rpn as $token) {
            if (is_numeric($token)) { $stack[] = (float) $token; continue; }

            $b = array_pop($stack);
            $a = array_pop($stack);

            if ($a === null || $b === null) return null;

            $stack[] = match ($token) {
                '+' => $a + $b,
                '-' => $a - $b,
                '*' => $a * $b,
                '/' => $b == 0.0 ? null : $a / $b,            // asked to divide by nothing
                default => null,
            };

            if (end($stack) === null) return null;
        }

        return count($stack) === 1 && is_finite($stack[0]) ? round($stack[0], 4) : null;
    }
}
