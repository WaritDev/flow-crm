<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'deal_id', 'customer_id', 'user_id', 'team_id',
        'name', 'description', 'activity_type', 'priority', 'is_completed'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function deal(): BelongsTo {
        return $this->belongsTo(Deal::class);
    }

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }
}
