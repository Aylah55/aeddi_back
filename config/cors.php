<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    'allowed_origins' => ['https://aeddi-antsiranana.onrender.com', 'http://localhost:*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN', 'X-XSRF-TOKEN', 'Accept'],

    'exposed_headers' => [
        'Authorization',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-Requested-With',
        'Content-Type',
        'Accept'
    ],

    'max_age' => 86400,

    'supports_credentials' => true,

    'allowed_credentials' => true,
];
