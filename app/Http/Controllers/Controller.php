<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;

abstract class Controller
{
    /**
     * Run something in English and put the visitor's language back after,
     * even if it fails. Used for PDFs: the PDF library can't join Arabic letters.
     */
    protected function inEnglish(callable $callback): mixed
    {
        $previous = App::getLocale();

        App::setLocale('en');

        try {
            return $callback();
        } finally {
            App::setLocale($previous);
        }
    }
}
