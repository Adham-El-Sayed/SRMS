<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser-side protections sent with every response.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // No other site may frame these pages (clickjacking).
            'X-Frame-Options' => 'SAMEORIGIN',
            // Browsers must not guess a different content type.
            'X-Content-Type-Options' => 'nosniff',
            // Don't leak full URLs (QR tokens live in URLs) to other sites.
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // The menu may ask for the camera (card scan); nothing else is needed.
            'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        // Only meaningful over HTTPS; tells browsers to never fall back to HTTP.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
