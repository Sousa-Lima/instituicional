<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LinkedinPost extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'text',
        'publish_target',
        'title',
        'excerpt',
        'image_path',
        'image_disk',
        'image_title',
        'status',
        'scheduled_at',
        'published_at',
        'linkedin_post_id',
        'linkedin_post_url',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function hasImage(): bool
    {
        return filled($this->image_path);
    }

    public function imageUrl(): string
    {
        return Storage::disk($this->image_disk)->url($this->image_path);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }
}
