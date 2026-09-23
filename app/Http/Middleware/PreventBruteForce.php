<?php
// app/Http/Middleware/PreventBruteForce.php
// OWASP A07: Identification & Authentication Failures — Rate limits login attempts

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PreventBruteForce
{
    protected RateLimiter $limiter;

    public function __construct()
    {
        // IMPORTANT: Use 'file' cache store explicitly.
        // The default CACHE_STORE=database requires a 'cache' table which
        // does not exist in tenant databases. File cache works out of the box.
        $fileCache = Cache::store('file');
        $this->limiter = new RateLimiter($fileCache);
    }

    /**
     * Throttle login attempts: max 5 per minute per IP + email combo.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'login:' . sha1($request->ip() . '|' . strtolower((string) $request->input('email')));

        if ($this->limiter->tooManyAttempts($key, 5)) {
            $seconds = $this->limiter->availableIn($key);

            // OWASP A09: Log the blocked brute-force attempt
            Log::warning('OWASP A07: Brute-force login attempt blocked.', [
                'ip'    => $request->ip(),
                'email' => $request->input('email'),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ], 429);
            }

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $response = $next($request);

        // Increment counter only on validation failure (wrong credentials = 422)
        // Do NOT count 302 redirects as failures — that includes successful logins
        if ($response->getStatusCode() === 422) {
            $this->limiter->hit($key, 60); // 60-second decay
        } else {
            // Successful login or any other outcome — clear the counter
            $this->limiter->clear($key);
        }

        return $response;
    }
}
