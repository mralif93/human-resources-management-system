<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // If no specific roles requested
        if (empty($roles)) {
            return $next($request);
        }

        // Super Admin has universal access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has one of the allowed roles via RBAC or legacy attribute
        if ($user->hasRole($roles) || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Access denied. You do not have sufficient administrative privileges for this section.');
    }
}
