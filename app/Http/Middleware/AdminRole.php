<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super admin has access to everything
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check if admin has required role
        if (!empty($roles) && !in_array($admin->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
