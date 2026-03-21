<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'title',
        'short_description',
        'content_html',
        'icon_name',
        'category',
        'seo',
        'order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'seo' => 'array',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
