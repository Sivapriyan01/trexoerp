<?php
// app/Http/Middleware/SecurityHeaders.php
// OWASP A05: Security Misconfiguration — Adds critical HTTP security headers

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add OWASP-recommended security headers to every HTTP response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing (OWASP A05)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking attacks (OWASP A05)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Enable browser XSS protection (OWASP A03)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Enforce HTTPS in production (OWASP A02)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Control referrer information (privacy + OWASP A01)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict browser features (OWASP A05)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Remove the server fingerprint header (information disclosure)
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
