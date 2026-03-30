<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationIntegration extends Model
{
    protected $fillable = [
        'organization_id',
        'n8n_service_user_id',
        'n8n_token_name',
        'line_webhook_secret',
        'line_webhook_path',
        'line_channel_access_token_encrypted',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function n8nServiceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'n8n_service_user_id');
    }
}

