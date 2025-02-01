<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: Optional Sanctum Authentication
 *
 * This middleware allows optional authentication using Sanctum.
 * If a valid Sanctum token is provided, the user will be authenticated.
 * If no token is provided, the user remains a guest.
 *
 * Use this middleware for routes that need both guest and authenticated access.
 */
class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next, string $guard = null): Response
    {
        // Use the specified guard if provided
        if ($guard && $request->bearerToken()) {
            Auth::shouldUse($guard);
        }

        return $next($request);
    }
}
