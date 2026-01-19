<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineScript extends Model
{
    protected $fillable = ['stage_id', 'team_id', 'name', 'content', 'use_count'];

    public function stage(): BelongsTo {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }
}
