<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // Autorise toutes les méthodes HTTP (GET, POST, etc.)

    'allowed_origins' => [
        'https://aeddi-antsiranana.onrender.com',
        'http://localhost:3000', // Si tu testes localement, précise un port concret
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Autorise tous les headers nécessaires

    'exposed_headers' => [
        'Authorization',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],

    'max_age' => 86400,

    'supports_credentials' => true, // ✅ Indispensable pour envoyer cookies/session

];
