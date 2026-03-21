<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Segredo JWT (Swarm: JWT_SECRET_FILE em /run/secrets/...).
    | Consumir em código com config('services.jwt.secret') quando integrar pacotes/API.
    */
    'jwt' => [
        'secret' => env('JWT_SECRET') ?: (
            env('JWT_SECRET_FILE') && is_readable((string) env('JWT_SECRET_FILE'))
                ? trim((string) file_get_contents((string) env('JWT_SECRET_FILE')))
                : ''
        ),
    ],

    /*
    | Token estático (Bearer) para build SSG / integrações que não usam login.
    | Rotas em middleware api.public aceitam este token OU JWT.
    */
    'api' => [
        'read_token' => env('API_READ_TOKEN'),
    ],

];
