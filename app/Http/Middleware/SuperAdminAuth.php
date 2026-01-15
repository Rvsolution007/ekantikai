<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('superadmin')->check()) {
            return redirect()->route('superadmin.login');
        }

        // Check if user is actually a super admin
        $user = Auth::guard('superadmin')->user();
        if (!$user->isSuperAdmin()) {
            Auth::guard('superadmin')->logout();
            return redirect()->route('superadmin.login')
                ->withErrors(['email' => 'This area is only for Super Admins.']);
        }

        return $next($request);
    }
}
