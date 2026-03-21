<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadContactRequest;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class LeadContactController extends Controller
{
    /**
     * GET /api/v1/leads — listagem (só JWT).
     */
    #[OA\Get(
        path: '/api/v1/leads',
        operationId: 'v1LeadsIndex',
        description: 'Lista de leads recebidos pelo formulário. Apenas utilizadores autenticados (JWT).',
        tags: ['Leads'],
        security: [['jwtAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK (paginado)'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $leads = Lead::query()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return LeadResource::collection($leads);
    }

    /**
     * POST /api/v1/lead/contact — JWT ou API_READ_TOKEN (middleware api.public).
     */
    #[OA\Post(
        path: '/api/v1/lead/contact',
        operationId: 'v1LeadContact',
        description: 'Lead B2B. Bearer: JWT (login) ou API_READ_TOKEN. CORS + throttle no servidor.',
        tags: ['Leads'],
        security: [['jwtAuth' => []], ['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'company', 'interest', 'consent_lgpd'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'company', type: 'string'),
                    new OA\Property(property: 'job_title', type: 'string', nullable: true),
                    new OA\Property(property: 'interest', type: 'string', enum: ['process', 'software', 'cloud']),
                    new OA\Property(property: 'business_stage', type: 'string', enum: ['ideation', 'validation', 'scale', 'operations'], nullable: true),
                    new OA\Property(property: 'message', type: 'string', nullable: true),
                    new OA\Property(property: 'consent_lgpd', type: 'boolean', example: true),
                    new OA\Property(property: 'source_path', type: 'string', example: '/servicos/desenvolvimento-software', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Lead criado'),
            new OA\Response(response: 401, description: 'JWT em falta ou inválido'),
            new OA\Response(response: 422, description: 'Validação'),
            new OA\Response(response: 429, description: 'Rate limit'),
        ]
    )]
    public function store(StoreLeadContactRequest $request): JsonResponse
    {
        $lead = Lead::query()->create([
            ...$request->validated(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() !== null ? mb_substr($request->userAgent(), 0, 2000) : null,
        ]);

        return response()->json([
            'id' => $lead->id,
            'message' => 'Lead recebido.',
        ], JsonResponse::HTTP_CREATED);
    }
}
