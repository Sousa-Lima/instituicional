<?php

namespace App\Http\Resources;

use App\Models\LinkedinPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @mixin LinkedinPost
 */
class BlogPostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $title = self::titleFor($this->resource);
        $excerpt = self::excerptFor($this->resource);

        return [
            'id' => (string) $this->id,
            'slug' => self::slugFor($this->resource),
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => self::sanitizeText(trim((string) $this->text)),
            'image_url' => self::imageUrlFor($this->resource),
            'external_url' => $this->linkedin_post_url,
            'published_at' => ($this->published_at ?? $this->created_at)?->toIso8601String(),
            'seo' => [
                'title' => $title . ' | Blog Everton Lima',
                'description' => $excerpt,
            ],
        ];
    }

    public static function slugFor(LinkedinPost $post): string
    {
        $baseSource = filled($post->title) ? (string) $post->title : self::normalizedText($post);
        $base = Str::slug(Str::limit(self::sanitizeText($baseSource), 72, ''));

        if ($base === '') {
            $base = 'post';
        }

        return $base . '-' . $post->getKey();
    }

    public static function titleFor(LinkedinPost $post): string
    {
        return Str::limit(self::sanitizeText(trim((string) $post->title)), 96, '...');
    }

    public static function excerptFor(LinkedinPost $post): string
    {
        return Str::limit(self::sanitizeText((string) Str::of((string) $post->excerpt)->squish()->trim()), 220, '...');
    }

    public static function imageUrlFor(LinkedinPost $post): ?string
    {
        if (blank($post->image_path)) {
            return null;
        }

        return Storage::disk($post->image_disk ?: 'public')->url((string) $post->image_path);
    }

    private static function normalizedText(LinkedinPost $post): string
    {
        return (string) Str::of((string) $post->text)->squish()->trim();
    }

    private static function sanitizeText(string $value): string
    {
        $decoded = self::decodeEntitiesRecursively($value);

        $patterns = [
            "/Por\s+que\s+o\s+Traefik\s+se\s+tornou\s+meu\s+.*braço\s+direito.*\s+na\s+orquestração\s+com\s+Docker\s+Swarm\?\s*[\p{So}\p{Sk}\x{1F300}-\x{1FAFF}]*/iu",
            '/Muitos\s+anos\s+de\s+experiência\s+ou\s+um\s+ano\s+repetido\s+trinta\s+vezes\?/iu',
        ];

        $clean = preg_replace($patterns, '', $decoded) ?? $decoded;
        $clean = preg_replace('/\s{2,}/u', ' ', $clean) ?? $clean;

        return trim($clean);
    }

    private static function decodeEntitiesRecursively(string $value): string
    {
        $current = $value;

        for ($i = 0; $i < 3; $i++) {
            $decoded = html_entity_decode($current, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($decoded === $current) {
                break;
            }

            $current = $decoded;
        }

        return $current;
    }
}