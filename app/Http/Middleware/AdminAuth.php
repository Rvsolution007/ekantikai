<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('admin.login');
        }

        // Check if admin is active
        $admin = Auth::guard('admin')->user();
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account deactivated.'], 403);
            }

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        return $next($request);
    }
}
