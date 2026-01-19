<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineTemplate extends Model
{
    protected $fillable = ['name', 'industry', 'is_default'];

    public function stages(): HasMany {
        return $this->hasMany(PipelineStage::class, 'template_id')->orderBy('position');
    }
}
