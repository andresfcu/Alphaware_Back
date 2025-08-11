<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'auth/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3039',
        'http://127.0.0.1:3039',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // o lista explícita: 'Content-Type','Authorization','X-Tenant','Accept','Origin','X-Requested-With'
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false, // pon true sólo si usarás cookies/sesiones (Sanctum)
];