<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{

    protected $fillable = [
        'user_id',
        'device_id',
        'name',
        'role',
        'paired_to',
        'fcm_token'
    ];
}
