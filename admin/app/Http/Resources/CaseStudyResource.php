<?php

namespace App\Http\Resources;

use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CaseStudy
 */
class CaseStudyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'slug' => $this->slug,
            'status' => $this->status,
            'featured' => $this->featured,
            'title' => $this->title,
            'customer_name' => $this->customer_name,
            'sector' => $this->sector,
            'short_summary' => $this->short_summary,
            'content_html' => $this->content_html,
            'metrics' => $this->metrics,
            'main_image' => $this->main_image,
            'seo' => $this->seo,
        ];
    }
}
