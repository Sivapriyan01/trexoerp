<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $module
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = Auth::guard('tenant')->user();

        if (!$user) {
            return redirect()->route('tenant.login');
        }

        if ($user->hasPermission($module)) {
            return $next($request);
        }

        // If it's an AJAX request, return a 403
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized module access.'], 403);
        }

        // Otherwise, redirect back with an error or to the dashboard
        return redirect()->route('tenant.dashboard')->with('error', "Access Denied: You do not have permission to access the {$module} module.");
    }
}
