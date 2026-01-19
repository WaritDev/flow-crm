<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Team extends Model
{
    protected $fillable = ['name', 'template_id',
        'team_id'
    ];

    public function pipelineTemplate(): BelongsTo {
        return $this->belongsTo(PipelineTemplate::class, 'template_id');
    }

    public function members(): HasMany {
        return $this->hasMany(User::class);
    }

    public function organization(): BelongsTo {
        return $this->belongsTo(Organization::class);
    }
}
