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

    // Allow CORS for API paths and generic requests. For testing we allow
    // all origins/methods/headers. In production, narrow these settings.
    'paths' => ['api/*', 'v1/*', 'sanctum/csrf-cookie', '*'],

    // Allow all HTTP methods (preflight will succeed).
    'allowed_methods' => ['*'],

    // Allow all origins for now so Swagger UI and browser clients can call the API.
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    // Allow all headers (including Accept, X-CSRF-TOKEN, Authorization, etc.).
    'allowed_headers' => ['*'],

    // Expose standard headers if needed by the client.
    'exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining'],

    'max_age' => 0,

    'supports_credentials' => true,

];
