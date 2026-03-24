<?php

$defaults = 'http://localhost:4321,http://127.0.0.1:4321,https://sousalimaconsultoria.com.br,https://www.sousalimaconsultoria.com.br';

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', $defaults))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
