<?php
// app/Http/Controllers/Auth/SuperAdminLoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SuperAdminLoginController extends Controller
{
    /**
     * Show the super admin login form.
     */
    public function showLoginForm()
    {
        return view('auth.superadmin-login');
    }

    /**
     * Handle super admin login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt login using the central 'web' guard
        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('web')->user();

            // Only super admins can access this
            if (! $user->isSuperAdmin()) {
                Auth::guard('web')->logout();
                throw ValidationException::withMessages([
                    'email' => 'You do not have super admin privileges.',
                ]);
            }

            if (! $user->is_active) {
                Auth::guard('web')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account has been deactivated.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->route('superadmin.dashboard');
        }

        // OWASP A09: Log failed super-admin login attempt
        Log::warning('OWASP A07: Failed super-admin login attempt.', [
            'email' => $request->input('email'),
            'ip'    => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Log out the super admin.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}
