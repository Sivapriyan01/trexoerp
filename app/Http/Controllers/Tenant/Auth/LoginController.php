<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // Show login page
    public function showLoginForm()
    {
        $tenant = tenant(); // cleaner helper

        return view('tenant.auth.login', compact('tenant'));
    }

    // Handle login
    public function login(Request $request)
    {
        $loginField = $request->input('email');
        $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType => $loginField,
            'password' => $request->input('password')
        ];

        if (Auth::guard('tenant')->attempt($credentials, $request->boolean('remember'))) {

            $user = Auth::guard('tenant')->user();

            // Check active status
            if (! $user->is_active) {
                Auth::guard('tenant')->logout();

                // OWASP A09: Log inactive account login attempt
                Log::warning('OWASP A07: Login attempt for deactivated tenant account.', [
                    'email' => $request->input('email'),
                    'ip'    => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Your account is inactive. Contact admin.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->route('tenant.dashboard')->with('success', 'Welcome back!');
        }

        // OWASP A09: Log failed login attempt
        Log::warning('OWASP A07: Failed tenant login attempt.', [
            'email' => $request->input('email'),
            'ip'    => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => 'Invalid credentials',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::guard('tenant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}