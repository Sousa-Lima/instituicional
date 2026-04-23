<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Models\CaseStudy;
use App\Models\LinkedinPost;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class ContentSlugController extends Controller
{
    /**
     * GET /api/v1/content/slugs — slugs ativos para SSG (serviços, cases; blog quando existir).
     */
    #[OA\Get(
        path: '/api/v1/content/slugs',
        operationId: 'v1ContentSlugs',
        description: 'Lista enxuta de slugs ativos para getStaticPaths (SSG).',
        tags: ['Build-time'],
        security: [['jwtAuth' => []], ['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'slug', type: 'string'),
                                    new OA\Property(property: 'kind', type: 'string', enum: ['service', 'case', 'blog']),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $data = Cache::store('redis')->tags(['services', 'cases', 'linkedin-posts', 'blog'])->remember(
            'api.content.slugs',
            3600,
            function () {
                $services = Service::query()
                    ->published()
                    ->orderBy('order')
                    ->get(['slug', 'updated_at']);

                $cases = CaseStudy::query()
                    ->published()
                    ->orderByDesc('featured')
                    ->orderByDesc('updated_at')
                    ->get(['slug', 'updated_at']);

                $posts = LinkedinPost::query()
                    ->where('status', 'published')
                    ->orderByDesc('published_at')
                    ->orderByDesc('updated_at')
                    ->get(['id', 'text', 'updated_at']);

                return $services
                    ->map(fn (Service $s) => [
                        'slug' => $s->slug,
                        'kind' => 'service',
                        'updated_at' => $s->updated_at?->toIso8601String(),
                    ])
                    ->concat(
                        $cases->map(fn (CaseStudy $c) => [
                            'slug' => $c->slug,
                            'kind' => 'case',
                            'updated_at' => $c->updated_at?->toIso8601String(),
                        ])
                    )
                    ->concat(
                        $posts->map(fn (LinkedinPost $post) => [
                            'slug' => BlogPostResource::slugFor($post),
                            'kind' => 'blog',
                            'updated_at' => $post->updated_at?->toIso8601String(),
                        ])
                    )
                    ->values();
            }
        );

        return response()->json(['data' => $data]);
    }
}
