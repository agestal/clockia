<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessOnboardingSource extends Model
{
    use HasFactory;

    protected $table = 'business_onboarding_sources';

    protected $fillable = [
        'business_onboarding_session_id',
        'url',
        'page_role',
        'title',
        'http_status',
        'content_type',
        'extracted_payload',
        'discovered_at',
    ];

    protected function casts(): array
    {
        return [
            'business_onboarding_session_id' => 'integer',
            'http_status' => 'integer',
            'extracted_payload' => 'array',
            'discovered_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(BusinessOnboardingSession::class, 'business_onboarding_session_id');
    }
}
