<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'email',
        'company',
        'job_title',
        'interest',
        'business_stage',
        'message',
        'consent_lgpd',
        'source_path',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'consent_lgpd' => 'boolean',
        ];
    }
}
