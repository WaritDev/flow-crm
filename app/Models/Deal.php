<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    protected $fillable = [
        'customer_id', 'user_id', 'team_id', 'stage_id', 'name', 'description',
        'value', 'currency', 'expected_close_date', 'next_action',
        'next_action_date', 'lost_reason', 'won_at', 'lost_at'
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'next_action_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
        'value' => 'decimal:2'
    ];

    public function isStale(): bool
    {
        return $this->next_action_date < now() &&!$this->won_at &&!$this->lost_at;
    }

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    public function stage(): BelongsTo {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function activities(): HasMany {
        return $this->hasMany(Activity::class);
    }

    public function organization(): BelongsTo {
        return $this->belongsTo(Organization::class);
    }
}
