<?php

namespace App\Http\Controllers;

use App\Services\AskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The manager types a question, this answers it from the database.
 *
 * Behind the admin role, rate limited, and it only ever reads. There is no
 * path from what is typed here to a query: the text picks one of a fixed
 * set of answers, each a query written in advance.
 */
class AskController extends Controller
{
    public function __construct(private AskService $ask) {}

    public function __invoke(Request $request): JsonResponse
    {
        $question = (string) $request->query('q', '');

        // Long enough for any real question; anything beyond it is noise.
        if (mb_strlen($question) > 120) {
            $question = mb_substr($question, 0, 120);
        }

        $answer = $this->ask->answer($question);

        if ($answer['kind'] === 'unknown') {
            $answer['examples'] = $this->ask->examples();
        }

        return response()->json($answer)->header('Cache-Control', 'no-store');
    }
}
