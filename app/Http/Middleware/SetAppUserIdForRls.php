<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetAppUserIdForRls
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // NB: PostgreSQL's SET command does not accept bind parameters,
            // so we use set_config() instead. `false` = session-scope.
            DB::statement('SELECT set_config(?, ?, false)', ['app.user_id', (string) Auth::id()]);

            // The app connects as `app_user` (.env). Admin sessions explicitly
            // elevate to the `app_admin` PostgreSQL role so the admin GRANTs
            // and the `current_user = 'app_admin'` RLS bypass apply. Non-admins
            // stay least-privileged as app_user.
            if (Auth::user()?->isAdmin()) {
                DB::statement('SET ROLE app_admin');
            } else {
                DB::statement('RESET ROLE');
            }
        }

        return $next($request);
    }
}
