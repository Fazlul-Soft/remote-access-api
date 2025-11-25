<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    protected $fillable = [
        'from_device_id',
        'to_device_id',
        'action',
        'payload',
        'status',
    ];
}
