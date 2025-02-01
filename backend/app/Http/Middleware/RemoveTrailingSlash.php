<?php

namespace App\Http\Middleware;

use Closure;

class RemoveTrailingSlash
{
    public function handle($request, Closure $next)
    {
        $uri = $request->getRequestUri();
        //\Illuminate\Support\Facades\Log::info('RemoveTrailingSlash middleware triggered', ['uri' => $uri,'method' => $request->method()]);
        // Normalize the URI without redirection
        if (str_ends_with($uri, '/') && $uri !== '/') {
            $request->server->set('REQUEST_URI', rtrim($uri, '/'));
        }

        return $next($request);
    }
}
