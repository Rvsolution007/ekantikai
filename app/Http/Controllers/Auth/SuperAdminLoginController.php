<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminLoginController extends Controller
{
    /**
     * Show super admin login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('superadmin')->check()) {
            return redirect()->route('superadmin.dashboard');
        }

        return view('superadmin.auth.login');
    }

    /**
     * Handle super admin login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('superadmin')->attempt($credentials, $remember)) {
            $superAdmin = Auth::guard('superadmin')->user();

            // Check if super admin is active
            if (!$superAdmin->is_active) {
                Auth::guard('superadmin')->logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated.',
                ])->withInput($request->only('email'));
            }

            // Check if user is actually a super admin
            if (!$superAdmin->isSuperAdmin()) {
                Auth::guard('superadmin')->logout();
                return back()->withErrors([
                    'email' => 'This login is only for Super Admins. Please use Admin login.',
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            return redirect()->intended(route('superadmin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle super admin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}
