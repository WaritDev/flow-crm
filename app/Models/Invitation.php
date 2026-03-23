<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = ['email', 'token', 'organization_id', 'team_id', 'role', 'expires_at'];
}
