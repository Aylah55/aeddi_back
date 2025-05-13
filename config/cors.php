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


    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'https://aeddi-antsiranana.vercel.app',
        'https://www.aeddi-antsiranana.vercel.app',
        'https://aeddi-antsiranana.onrender.com',
        'https://aeddi-backend.onrender.com'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Origin',
        'Content-Type',
        'X-Auth-Token',
        'X-Requested-With',
        'Accept',
        'Authorization',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
    ],

    'exposed_headers' => [
        'Authorization',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],

    'max_age' => 0,

    'supports_credentials' => true,

];
