<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Target;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Organization extends Model {
    protected $fillable = ['name', 'slug', 'size', 'description', 'invite_code'];

    public function teams() { return $this->hasMany(Team::class); }
    public function users() { return $this->hasMany(User::class); }
    public function customers() { return $this->hasMany(Customer::class); }
    public function targets(): MorphMany {
        return $this->morphMany(Target::class, 'targetable');
    }

    public function currentMonthTarget($type = 'revenue'){
        return $this->targets()
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->where('type', $type)
            ->first();
    }
}
