<?php
// app/Http/Middleware/TenantAuth.php
// OWASP A01: Broken Access Control — Enforces authentication for all tenant routes

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantAuth
{
    /**
     * Handle an incoming request.
     * Ensures only authenticated, active tenant users can access protected routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('tenant')->check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('tenant.login');
        }

        $user = Auth::guard('tenant')->user();

        // OWASP A01: Double-check the user account is still active on every request
        if (! $user->is_active) {
            Auth::guard('tenant')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Account deactivated.'], 403);
            }
            return redirect()->route('tenant.login')->withErrors([
                'email' => 'Your account has been deactivated. Contact your administrator.',
            ]);
        }

        return $next($request);
    }
}
