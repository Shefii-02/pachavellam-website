<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Guards server-to-server routes (routes/internal.php) called only by the
 * Node chat-service, never by a browser or the mobile app directly.
 */
class VerifyInternalSecret
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('services.chat.internal_secret');
        $provided = $request->header('X-Internal-Secret');

        if (!$expected || !$provided || !hash_equals($expected, $provided)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
