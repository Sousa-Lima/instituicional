<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseStudyResource;
use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class CaseStudyController extends Controller
{
    /**
     * GET /api/v1/cases — lista (build-time); ?status=published por defeito.
     */
    #[OA\Get(
        path: '/api/v1/cases',
        operationId: 'v1CasesIndex',
        description: 'Lista de cases para galeria e SSG. Por defeito apenas publicados.',
        tags: ['Cases'],
        security: [['jwtAuth' => []], ['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['published', 'draft'])
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = $request->query('status', 'published');

        $query = CaseStudy::query()->orderByDesc('featured')->orderByDesc('updated_at');

        if ($status === 'published') {
            $query->published();
        }

        return CaseStudyResource::collection($query->get());
    }

    /**
     * GET /api/v1/cases/{slug} — detalhe para /cases/[slug] (SSG).
     */
    #[OA\Get(
        path: '/api/v1/cases/{slug}',
        operationId: 'v1CasesShow',
        tags: ['Cases'],
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
    public function show(string $slug): CaseStudyResource
    {
        $case = CaseStudy::query()
            ->published()
            ->where('slug', $slug)
            ->first();

        abort_if($case === null, 404, 'Case não encontrado.');

        return new CaseStudyResource($case);
    }
}
