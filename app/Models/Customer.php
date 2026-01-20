<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'nickname',
        'phone_num',
        'email',
        'line_id',
        'province',
        'address',
        'status',
        'img_profile'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    protected $casts = [
        'tags' => 'array',
    ];

    protected $attributes = [
        'tags' => '[]', // JSON array default string for DB
        'img_profile' => null,
    ];
}
