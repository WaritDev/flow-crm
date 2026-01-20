<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model {
    protected $fillable = ['name', 'slug', 'size', 'description', 'invite_code'];

    public function teams() { return $this->hasMany(Team::class); }
    public function users() { return $this->hasMany(User::class); }
    public function customers() { return $this->hasMany(Customer::class); }
}
