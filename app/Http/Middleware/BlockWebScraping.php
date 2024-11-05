<?php

namespace App\Http\Middleware;

use Closure;

class BlockWebScraping
{
    protected $blockedUserAgents = [
        'HTTrack',
        'Wget',
        // Add more user-agents as necessary
    ];

    public function handle($request, Closure $next)
    {
        $userAgent = $request->header('User-Agent');

        foreach ($this->blockedUserAgents as $blockedAgent) {
            if (stripos($userAgent, $blockedAgent) !== false) {
                // You can log the attempt or return a custom response here to block the request
                abort(403, 'Web scraping is not allowed on this website.');
            }
        }

        return $next($request);
    }
}