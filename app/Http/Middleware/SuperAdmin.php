<?php
// app/Http/Middleware/SuperAdmin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('web')->check() || ! Auth::guard('web')->user()->isSuperAdmin()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        return $next($request);
    }
}
