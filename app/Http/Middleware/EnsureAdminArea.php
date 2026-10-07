<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Guards /admin/*: only users.type "Admin" or "Admin Staff" get through.
 * Guests go to the admin login page; any other logged-in user (students)
 * is sent to the normal sign-in page.
 */
class EnsureAdminArea
{
    public const ALLOWED_TYPES = ['Admin', 'Admin Staff'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? abort(401)
                : redirect()->guest(route('admin.login'));
        }

        if (!in_array($user->type, self::ALLOWED_TYPES, true)) {
            return $request->expectsJson() ? abort(403) : redirect('/sign-in');
        }

        return $next($request);
    }
}
