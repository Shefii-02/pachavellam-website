<?php

namespace App\Http\Middleware;

use Closure;

class RedirectNonWww
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();

        if (strpos($host, 'www.') === false) {
            // Add 'www' to the URL without the domain
            $newHost = 'www.' . $host;
            $url = $request->url();
            $newUrl = str_replace($host, $newHost, $url);

            return redirect($newUrl, 301);
        }

        return $next($request);
    }
}
