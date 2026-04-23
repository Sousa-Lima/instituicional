<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Models\LinkedinPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class BlogPostController extends Controller
{
    /**
     * GET /api/v1/blog/posts — blog público derivado dos posts publicados no LinkedIn.
     */
    #[OA\Get(
        path: '/api/v1/blog/posts',
        operationId: 'v1BlogPostsIndex',
        tags: ['Blog'],
        security: [['jwtAuth' => []], ['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(): JsonResponse
    {
        $latestUpdatedAt = LinkedinPost::query()
            ->where('status', 'published')
            ->whereNotNull('title')
            ->whereRaw("trim(title) <> ''")
            ->whereNotNull('excerpt')
            ->whereRaw("trim(excerpt) <> ''")
            ->max('updated_at');

        $postsVersion = md5((string) ($latestUpdatedAt ?? '0'));

        $data = Cache::store('redis')->tags(['linkedin-posts', 'blog'])->remember(
            "api.blog.posts.index.v3.{$postsVersion}",
            3600,
            fn () => BlogPostResource::collection(
                LinkedinPost::query()
                    ->where('status', 'published')
                    ->whereNotNull('title')
                    ->whereRaw("trim(title) <> ''")
                    ->whereNotNull('excerpt')
                    ->whereRaw("trim(excerpt) <> ''")
                    ->orderByDesc('published_at')
                    ->orderByDesc('created_at')
                    ->get()
            )->resolve()
        );

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/v1/blog/posts/{slug}
     */
    #[OA\Get(
        path: '/api/v1/blog/posts/{slug}',
        operationId: 'v1BlogPostsShow',
        tags: ['Blog'],
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
    public function show(string $slug): JsonResponse
    {
        $postId = $this->extractPostId($slug);

        abort_if($postId === null, 404, 'Post não encontrado.');

        $postUpdatedAt = LinkedinPost::query()
            ->whereKey($postId)
            ->value('updated_at');

        $postVersion = md5((string) ($postUpdatedAt ?? '0'));

        $data = Cache::store('redis')->tags(['linkedin-posts', 'blog'])->remember(
            "api.blog.posts.show.v3.{$postId}.{$postVersion}",
            3600,
            function () use ($postId) {
                $post = LinkedinPost::query()
                    ->where('status', 'published')
                    ->whereNotNull('title')
                    ->whereRaw("trim(title) <> ''")
                    ->whereNotNull('excerpt')
                    ->whereRaw("trim(excerpt) <> ''")
                    ->find($postId);

                return $post !== null ? (new BlogPostResource($post))->resolve() : null;
            }
        );

        abort_if($data === null, 404, 'Post não encontrado.');

        return response()->json(['data' => $data]);
    }

    private function extractPostId(string $slug): ?string
    {
        if (preg_match('/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/i', $slug, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}