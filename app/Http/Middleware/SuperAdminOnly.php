<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin || !$admin->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
