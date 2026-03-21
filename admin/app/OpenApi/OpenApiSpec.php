<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Metadados OpenAPI (L5 Swagger / swagger-php).
 *
 * @see https://github.com/DarkaOnLine/L5-Swagger
 */
#[OA\OpenApi(
    openapi: '3.0.0',
    info: new OA\Info(
        title: 'Sousa Lima Consultoria — API v1',
        version: '1.0.0',
        description: 'Rotas de site/build: JWT ou API_READ_TOKEN. Auth/me e logout: só JWT. Erros 4xx/5xx em application/json (message; 422 inclui errors).',
    ),
)]
#[OA\Schema(
    schema: 'HttpError',
    type: 'object',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
        new OA\Property(property: 'exception', type: 'string', nullable: true),
        new OA\Property(property: 'file', type: 'string', nullable: true),
        new OA\Property(property: 'line', type: 'integer', nullable: true),
    ],
    description: 'Corpo típico de erro (JSON). Com APP_DEBUG=true podem aparecer exception/file/line.'
)]
#[OA\Schema(
    schema: 'HttpValidationError',
    type: 'object',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(property: 'errors', description: 'Mapa campo → lista de mensagens', type: 'object'),
    ],
    description: 'Erro de validação (422).'
)]
#[OA\SecurityScheme(
    securityScheme: 'jwtAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'JWT de POST /api/v1/auth/login (utilizadores na tabela users).'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'API_READ_TOKEN',
    description: 'Token estático no .env (build CI / integrações). Mesmo header Authorization: Bearer.'
)]
class OpenApiSpec {}
