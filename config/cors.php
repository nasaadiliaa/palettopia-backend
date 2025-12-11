<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // tambahkan kedua origin (localhost & 127.0.0.1) sesuai browser yang kamu pakai
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // HARUS true supaya cookie bisa dikirim cross-site
    'supports_credentials' => true,
];
