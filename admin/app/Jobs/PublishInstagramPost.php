<?php

namespace App\Jobs;

use App\Models\InstagramPost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublishInstagramPost implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60; // segundos entre tentativas

    public function __construct(
        public readonly string $instagramPostId,
    ) {}

    public function handle(): void
    {
        $post = InstagramPost::find($this->instagramPostId);

        if ($post === null || $post->status === 'published') {
            return;
        }

        $userId = config('services.instagram.user_id');
        $token  = config('services.instagram.access_token');
        $base   = 'https://graph.facebook.com/v21.0';

        try {
            // 1. Cria o media container
            $container = Http::throw()->post("{$base}/{$userId}/media", [
                'image_url'    => $post->imageUrl(),
                'caption'      => $post->caption,
                'access_token' => $token,
            ])->json('id');

            // 2. Aguarda o container estar pronto (max 30s)
            $this->waitForContainer($base, $container, $token);

            // 3. Publica
            $mediaId = Http::throw()->post("{$base}/{$userId}/media_publish", [
                'creation_id'  => $container,
                'access_token' => $token,
            ])->json('id');

            $post->update([
                'status'              => 'published',
                'published_at'        => now(),
                'instagram_media_id'  => $mediaId,
                'error_message'       => null,
            ]);

            Log::info("InstagramPost {$post->id} publicado com sucesso. Media ID: {$mediaId}");

        } catch (\Throwable $e) {
            $message = $e instanceof RequestException
                ? ($e->response->json('error.message') ?? $e->getMessage())
                : $e->getMessage();

            $post->update([
                'status'        => 'failed',
                'error_message' => $message,
            ]);

            Log::error("InstagramPost {$post->id} falhou: {$message}");

            throw $e; // permite as $tries do Laravel Queue
        }
    }

    private function waitForContainer(string $base, string $containerId, string $token): void
    {
        $attempts = 0;

        while ($attempts < 10) {
            $status = Http::get("{$base}/{$containerId}", [
                'fields'       => 'status_code',
                'access_token' => $token,
            ])->json('status_code');

            if ($status === 'FINISHED') {
                return;
            }

            if ($status === 'ERROR') {
                throw new \RuntimeException("Container {$containerId} retornou ERROR na API do Instagram.");
            }

            sleep(3);
            $attempts++;
        }

        throw new \RuntimeException("Timeout aguardando container {$containerId} ficar pronto.");
    }
}
