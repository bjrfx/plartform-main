<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class SubdomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract the host
        $host = $request->getHost();

        // Split the host into parts
        $hostParts = explode('.', $host);

        // Determine the subdomain and domain
        $subdomain = null;

        // First part is the subdomain if more than 2 parts
        if(count($hostParts) > 2){
            $subdomain = Arr::pull($hostParts, 0);
        }
        if($subdomain === 'www'){
            $subdomain = null;
        }

        // Remaining parts form the domain
        $domain = implode('.', $hostParts);

        // Add subdomain and domain to the request
        $request->merge([
            'subdomain' => $subdomain,
            'domain' => $domain,
        ]);

        return $next($request);
    }
}
