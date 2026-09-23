<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Support\Access;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Someone signed in who hasn't been given a role yet: show them the
        // "waiting for access" page instead of a bare 403.
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('Your account is not set up yet')], 403);
            }

            $user = $request->user();

            return $user && ! $user->hasAnyRole(Access::ROLES)
                ? redirect()->route('no-access')
                : response()->view('errors.403-role', [], 403);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();