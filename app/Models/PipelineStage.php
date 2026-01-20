<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $fillable = ['template_id', 'name', 'position', 'is_won', 'description'];

    public function template(): BelongsTo {
        return $this->belongsTo(PipelineTemplate::class, 'template_id');
    }

    public function lineScripts(): HasMany {
        return $this->hasMany(LineScript::class, 'stage_id');
    }
}
