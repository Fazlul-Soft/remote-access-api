<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceLocation extends Model
{
    protected $fillable = ['device_id', 'latitude', 'longitude', 'accuracy'];
}
