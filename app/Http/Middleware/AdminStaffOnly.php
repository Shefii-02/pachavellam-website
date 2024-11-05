<?php

namespace App\Http\Middleware;

use Closure;

class AdminStaffOnly
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff())) {
            // User is authorized (admin or staff), allow access to the route.
            return $next($request);
        }

        // Redirect users who are not authorized to the homepage or any other route.
        return redirect('/');
    }
}
