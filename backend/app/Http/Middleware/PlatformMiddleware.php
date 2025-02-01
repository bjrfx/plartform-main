<?php

namespace App\Http\Middleware;

use App\Helpers\General\DomainHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class PlatformMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //using middleware to handle "domain" & "tld" to prevent of passing them to the controller method.
        $domain = $request->route('domain');
        $tld = $request->route('tld');

        DomainHelper::setDomain($domain, $tld);

        //logger("PlatformMiddleware executed: {$domain}.{$tld}");

        // Remove them from route parameters to prevent them from being passed to the controller
        $request->route()->forgetParameter('domain');
        $request->route()->forgetParameter('tld');

        return $next($request);
    }
}
