<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'provider_id',
        'provider_name',
        'provider_nickname',
        'provider_avatar',
        'id_token',
        'provider_token',
        'provider_refresh_token'
    ];

    protected $hidden = ['created_at','updated_at'];
}


