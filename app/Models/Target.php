<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'targetable_id',
        'targetable_type',
        'amount',
        'type',
        'month',
        'year',
        'description'
    ];

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }
}