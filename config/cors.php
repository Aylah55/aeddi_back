<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://aeddi-antsiranana.onrender.com',
        'http://localhost:3000',  // Pour le développement local
    ],

    'allowed_origins_patterns' => [],


    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Accept'
    ],


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
];