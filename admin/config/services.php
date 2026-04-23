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

    /*
    | Meta / Instagram Graph API
    | INSTAGRAM_USER_ID   — Instagram Business/Creator Account ID (numérico)
    | INSTAGRAM_ACCESS_TOKEN — Page Access Token de longa duração
    */
    'instagram' => [
        'user_id'      => env('INSTAGRAM_USER_ID'),
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN') ?: (
            env('INSTAGRAM_ACCESS_TOKEN_FILE') && is_readable((string) env('INSTAGRAM_ACCESS_TOKEN_FILE'))
                ? trim((string) file_get_contents((string) env('INSTAGRAM_ACCESS_TOKEN_FILE')))
                : ''
        ),
    ],

    /*
    | LinkedIn API v2
    | Pode publicar por destino: pessoal ou empresa.
    | Compatibilidade: LINKEDIN_AUTHOR_URN e LINKEDIN_ACCESS_TOKEN continuam válidos
    | como fallback para o destino pessoal.
    */
    'linkedin' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID') ?: (
            env('LINKEDIN_CLIENT_ID_FILE') && is_readable((string) env('LINKEDIN_CLIENT_ID_FILE'))
                ? trim((string) file_get_contents((string) env('LINKEDIN_CLIENT_ID_FILE')))
                : ''
        ),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET') ?: (
            env('LINKEDIN_CLIENT_SECRET_FILE') && is_readable((string) env('LINKEDIN_CLIENT_SECRET_FILE'))
                ? trim((string) file_get_contents((string) env('LINKEDIN_CLIENT_SECRET_FILE')))
                : ''
        ),
        'redirect_uri'  => env('LINKEDIN_REDIRECT_URI', rtrim((string) env('APP_URL', ''), '/').'/admin/integrations/linkedin/callback'),
        'scopes'        => array_values(array_filter(array_map(
            static fn (string $scope): string => trim($scope),
            preg_split('/[\s,]+/', (string) env('LINKEDIN_SCOPES', 'w_organization_social')) ?: [],
        ))),
        'author_urn'    => (string) env('LINKEDIN_AUTHOR_URN', ''),
        'access_token'  => env('LINKEDIN_ACCESS_TOKEN') ?: (
            env('LINKEDIN_ACCESS_TOKEN_FILE') && is_readable((string) env('LINKEDIN_ACCESS_TOKEN_FILE'))
                ? trim((string) file_get_contents((string) env('LINKEDIN_ACCESS_TOKEN_FILE')))
                : ''
        ),
        'personal'      => [
            'author_urn'   => (string) env('LINKEDIN_PERSONAL_AUTHOR_URN', (string) env('LINKEDIN_AUTHOR_URN', '')),
            'access_token' => env('LINKEDIN_PERSONAL_ACCESS_TOKEN') ?: (
                env('LINKEDIN_PERSONAL_ACCESS_TOKEN_FILE') && is_readable((string) env('LINKEDIN_PERSONAL_ACCESS_TOKEN_FILE'))
                    ? trim((string) file_get_contents((string) env('LINKEDIN_PERSONAL_ACCESS_TOKEN_FILE')))
                    : (env('LINKEDIN_ACCESS_TOKEN') ?: (
                        env('LINKEDIN_ACCESS_TOKEN_FILE') && is_readable((string) env('LINKEDIN_ACCESS_TOKEN_FILE'))
                            ? trim((string) file_get_contents((string) env('LINKEDIN_ACCESS_TOKEN_FILE')))
                            : ''
                    ))
            ),
        ],
        'company'       => [
            'author_urn'   => (string) env('LINKEDIN_COMPANY_AUTHOR_URN', ''),
            'access_token' => env('LINKEDIN_COMPANY_ACCESS_TOKEN') ?: (
                env('LINKEDIN_COMPANY_ACCESS_TOKEN_FILE') && is_readable((string) env('LINKEDIN_COMPANY_ACCESS_TOKEN_FILE'))
                    ? trim((string) file_get_contents((string) env('LINKEDIN_COMPANY_ACCESS_TOKEN_FILE')))
                    : ''
            ),
        ],
    ],

    /*
    | Google Gemini API
    | GEMINI_API_KEY — chave da API (ou GEMINI_API_KEY_FILE no Swarm)
    | GEMINI_MODEL   — modelo, ex: gemini-1.5-flash
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY') ?: (
            env('GEMINI_API_KEY_FILE') && is_readable((string) env('GEMINI_API_KEY_FILE'))
                ? trim((string) file_get_contents((string) env('GEMINI_API_KEY_FILE')))
                : ''
        ),
        'model'   => env('GEMINI_MODEL', 'gemini-1.5-flash'),
    ],

];
