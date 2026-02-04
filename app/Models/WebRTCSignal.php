<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebRTCSignal extends Model
{
    protected $table = 'webrtc_signals';
    
    protected $fillable = [
        'from_device_id',
        'target_device_id',
        'type',
        'data'
    ];

    // Ensure 'data' is treated as an array/json
    protected $casts = [
        'data' => 'array'
    ];
}
