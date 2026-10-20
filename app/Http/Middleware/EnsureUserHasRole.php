<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-based route protection.
 *
 * Usage in routes:  ->middleware('role:app_admin')
 *                   ->middleware('role:app_admin,app_user')
 *
 * Backed by the DB `roles` table (roles.name), which is the same source of
 * truth the PostgreSQL RLS policies and GRANTs use.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required.');
        }

        $roleName = Auth::user()->role?->name;

        if (!in_array($roleName, $roles, true)) {
            abort(403, 'Your role (' . ($roleName ?? 'none') . ') is not permitted to access this resource.');
        }

        return $next($request);
    }
}
