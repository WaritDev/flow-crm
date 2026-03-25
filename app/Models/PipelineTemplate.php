<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineTemplate extends Model
{
    protected $fillable = ['name', 'industry', 'description', 'is_default', 'organization_id'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class, 'template_id')->orderBy('position');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'template_id');
    }

    public function isSystemTemplate(): bool
    {
        return $this->organization_id === null;
    }
}
