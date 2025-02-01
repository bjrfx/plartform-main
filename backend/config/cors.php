<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Include Sanctum CSRF endpoint

    'allowed_methods' => ['*'], // Allow all HTTP methods (GET, POST, PUT, etc.)

    'allowed_origins' => ['*'], // Allow all domains (can be customized)

    'allowed_origins_patterns' => ['.*'], // Match all subdomains dynamically

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [], // No specific headers exposed to the browser

    'max_age' => 0, // No caching of CORS preflight requests

    'supports_credentials' => true, // Enable cookies and credentials
];
