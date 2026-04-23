<?php

namespace App\Jobs;

use App\Models\LinkedinPost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublishLinkedinPost implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly string $linkedinPostId,
    ) {}

    public function handle(): void
    {
        $post = LinkedinPost::find($this->linkedinPostId);

        if ($post === null || $post->status === 'published') {
            return;
        }

        $target    = $post->publish_target ?: 'personal';
        $profile   = $this->resolveProfileConfig($target);
        $authorUrn = $profile['author_urn'];
        $token     = $profile['access_token'];
        $base      = 'https://api.linkedin.com/v2';

        if ($authorUrn === '') {
            $message = $target === 'company'
                ? 'Configuração ausente para empresa: LINKEDIN_COMPANY_AUTHOR_URN.'
                : 'Configuração ausente para perfil pessoal: LINKEDIN_PERSONAL_AUTHOR_URN/LINKEDIN_AUTHOR_URN.';

            $post->update([
                'status' => 'failed',
                'error_message' => $message,
            ]);

            Log::error("LinkedinPost {$post->id} falhou: {$message}");

            throw new \RuntimeException($message);
        }

        if ($token === '') {
            $message = $target === 'company'
                ? 'Configuração ausente para empresa: LINKEDIN_COMPANY_ACCESS_TOKEN/LINKEDIN_COMPANY_ACCESS_TOKEN_FILE.'
                : 'Configuração ausente para perfil pessoal: LINKEDIN_PERSONAL_ACCESS_TOKEN/LINKEDIN_PERSONAL_ACCESS_TOKEN_FILE ou LINKEDIN_ACCESS_TOKEN/LINKEDIN_ACCESS_TOKEN_FILE.';

            $post->update([
                'status' => 'failed',
                'error_message' => $message,
            ]);

            Log::error("LinkedinPost {$post->id} falhou: {$message}");

            throw new \RuntimeException($message);
        }

        $headers = [
            'Authorization'             => "Bearer {$token}",
            'Content-Type'              => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ];

        Log::debug("LinkedinPost {$post->id} iniciando.", [
            'publish_target' => $target,
            'author_urn' => $authorUrn,
            'has_image'  => $post->hasImage(),
        ]);

        try {
            $imageAssetUrn = null;

            if ($post->hasImage()) {
                $imageAssetUrn = $this->uploadImage($base, $headers, $authorUrn, $token, $post);
            }

            $body = $this->buildPostBody($authorUrn, $post, $imageAssetUrn);

            Log::debug("LinkedinPost {$post->id} POST /ugcPosts — payload.", ['body' => $body]);

            $response = Http::withHeaders($headers)
                ->throw()
                ->post("{$base}/ugcPosts", $body);

            Log::debug("LinkedinPost {$post->id} POST /ugcPosts — resposta.", [
                'status'  => $response->status(),
                'headers' => $response->headers(),
                'body'    => $response->json() ?? $response->body(),
            ]);

            // LinkedIn retorna o URN do post no header X-RestLi-Id
            $linkedinId = $response->header('X-RestLi-Id') ?? $response->json('id');
            $linkedinUrl = $this->buildLinkedinPostUrl($linkedinId);

            $post->update([
                'status'           => 'published',
                'published_at'     => now(),
                'linkedin_post_id' => $linkedinId,
                'linkedin_post_url' => $linkedinUrl,
                'error_message'    => null,
            ]);

            Log::info("LinkedinPost {$post->id} publicado. URN: {$linkedinId}");

        } catch (\Throwable $e) {
            $message = $e instanceof RequestException
                ? ($e->response->json('message') ?? $e->getMessage())
                : $e->getMessage();

            $responseBody = $e instanceof RequestException
                ? ($e->response->json() ?? $e->response->body())
                : null;

            Log::error("LinkedinPost {$post->id} falhou: {$message}", array_filter([
                'response_status' => $e instanceof RequestException ? $e->response->status() : null,
                'response_body'   => $responseBody,
                'exception'       => $e->getMessage(),
            ]));

            $post->update([
                'status'        => 'failed',
                'error_message' => $message,
            ]);

            throw $e;
        }
    }

    /**
     * Faz upload da imagem via LinkedIn Assets API e retorna o URN do asset.
     *
     * @param  array<string, string>  $headers
     */
    private function uploadImage(string $base, array $headers, string $authorUrn, string $token, LinkedinPost $post): string
    {
        // 1. Registra o upload
        $registerPayload = [
            'registerUploadRequest' => [
                'owner'                  => $authorUrn,
                'recipes'                => ['urn:li:digitalmediaRecipe:feedshare-image'],
                'serviceRelationships'   => [
                    [
                        'identifier'       => 'urn:li:userGeneratedContent',
                        'relationshipType' => 'OWNER',
                    ],
                ],
            ],
        ];

        Log::debug("LinkedinPost {$post->id} POST /assets?action=registerUpload — payload.", ['body' => $registerPayload]);

        $registerResponse = Http::withHeaders($headers)
            ->throw()
            ->post("{$base}/assets?action=registerUpload", $registerPayload)
            ->json();

        Log::debug("LinkedinPost {$post->id} POST /assets?action=registerUpload — resposta.", ['response' => $registerResponse]);

        $uploadUrl = $registerResponse['value']['uploadMechanism']
            ['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];

        $assetUrn = $registerResponse['value']['asset'];

        // 2. Faz o upload binário da imagem (prioriza leitura local para evitar timeout HTTP interno)
        $disk = (string) ($post->image_disk ?: 'public');

        if (! Storage::disk($disk)->exists((string) $post->image_path)) {
            throw new \RuntimeException("Arquivo de imagem não encontrado no disco {$disk}: {$post->image_path}");
        }

        $imageContents = Storage::disk($disk)->get((string) $post->image_path);

        if ($imageContents === '') {
            $urlContents = @file_get_contents($post->imageUrl());

            if ($urlContents === false) {
                throw new \RuntimeException("Não foi possível ler a imagem: {$post->image_path}");
            }

            $imageContents = $urlContents;
        }

        Log::debug("LinkedinPost {$post->id} PUT uploadUrl — enviando imagem.", [
            'upload_url'   => $uploadUrl,
            'asset_urn'    => $assetUrn,
            'disk'         => $disk,
            'path'         => $post->image_path,
            'size_bytes'   => strlen($imageContents),
        ]);

        $uploadResponse = Http::withBody($imageContents, 'application/octet-stream')
            ->withHeaders(['Authorization' => "Bearer {$token}"])
            ->throw()
            ->put($uploadUrl);

        Log::debug("LinkedinPost {$post->id} PUT uploadUrl — resposta.", [
            'status'  => $uploadResponse->status(),
            'headers' => $uploadResponse->headers(),
            'body'    => $uploadResponse->body() ?: '(vazio)',
        ]);

        return $assetUrn;
    }

    /**
     * Monta o corpo do POST para a LinkedIn Posts API.
     *
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    private function buildPostBody(string $authorUrn, LinkedinPost $post, ?string $imageAssetUrn): array
    {
        $body = [
            'author'         => $authorUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => [
                        'text' => $post->text,
                    ],
                    'shareMediaCategory' => $imageAssetUrn ? 'IMAGE' : 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        if ($imageAssetUrn !== null) {
            $body['specificContent']['com.linkedin.ugc.ShareContent']['media'] = [
                [
                    'status'      => 'READY',
                    'media'       => $imageAssetUrn,
                    'title'       => ['text' => $post->image_title ?? ''],
                ],
            ];
        }

        return $body;
    }

    /**
     * @return array{author_urn:string, access_token:string}
     */
    private function resolveProfileConfig(string $target): array
    {
        $target = $target === 'company' ? 'company' : 'personal';

        return [
            'author_urn' => trim((string) config("services.linkedin.{$target}.author_urn")),
            'access_token' => trim((string) config("services.linkedin.{$target}.access_token")),
        ];
    }

    private function buildLinkedinPostUrl(?string $linkedinId): ?string
    {
        if (! is_string($linkedinId) || trim($linkedinId) === '') {
            return null;
        }

        $urn = trim($linkedinId);

        if (! str_starts_with($urn, 'urn:li:')) {
            return null;
        }

        return "https://www.linkedin.com/feed/update/{$urn}/";
    }
}
