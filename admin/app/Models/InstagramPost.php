<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InstagramPost extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'caption',
        'image_path',
        'image_disk',
        'status',
        'scheduled_at',
        'published_at',
        'instagram_media_id',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function imageUrl(): string
    {
        return Storage::disk($this->image_disk)->url($this->image_path);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }
}
