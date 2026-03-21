<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ServiceController extends Controller
{
    /**
     * GET /api/v1/services — vitrine + páginas internas (build-time).
     */
    #[OA\Get(
        path: '/api/v1/services',
        operationId: 'v1ServicesIndex',
        tags: ['Services'],
        security: [['jwtAuth' => []], ['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()
            ->published()
            ->orderBy('order')
            ->orderBy('title')
            ->get();

        return ServiceResource::collection($services);
    }

    /**
     * GET /api/v1/services/{slug}
     */
    #[OA\Get(
        path: '/api/v1/services/{slug}',
        operationId: 'v1ServicesShow',
        tags: ['Services'],
        security: [['jwtAuth' => []], ['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(string $slug): ServiceResource
    {
        $service = Service::query()
            ->published()
            ->where('slug', $slug)
            ->first();

        abort_if($service === null, 404, 'Serviço não encontrado.');

        return new ServiceResource($service);
    }
}
