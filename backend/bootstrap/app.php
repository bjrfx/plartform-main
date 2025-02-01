<?php

use App\Http\Middleware\MerchantMiddleware;
use App\Http\Middleware\OptionalSanctumAuth;
use App\Http\Middleware\PlatformMiddleware;
use App\Http\Middleware\SubdomainMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register global middleware
        $middleware->append(TrimStrings::class);
        $middleware->append(ConvertEmptyStringsToNull::class);

        // Register middleware here
        $middleware->alias([
            'subdomain' => SubdomainMiddleware::class,
            'merchant' => MerchantMiddleware::class,
            'platform' => PlatformMiddleware::class,
            'optional.auth' => OptionalSanctumAuth::class
        ]);

        // Add the RemoveTrailingSlash middleware globally
        //$middleware->append(RemoveTrailingSlash::class);
        //$middleware->append(ConvertNullBooleansMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Register the custom handler
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'API Route Not Found.'
                ], 404);
            }
            // Return null to let Laravel handle it using default behavior
            return null;
        });
        // Handle ValidationException to return JSON response with custom message
        $exceptions->render(function (ValidationException $e) {
            return response()->json($e->errors(), $e->status);
        });
    })->create();
