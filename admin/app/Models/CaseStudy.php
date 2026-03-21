<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'status',
        'featured',
        'title',
        'customer_name',
        'sector',
        'short_summary',
        'content_html',
        'metrics',
        'main_image',
        'seo',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'metrics' => 'array',
            'main_image' => 'array',
            'seo' => 'array',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
