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
        'result',
        'error',
    ];

    /**
     * Controller device (sender)
     */
    public function fromDevice()
    {
        return $this->belongsTo(Device::class, 'from_device_id');
    }

    /**
     * Controlled device (receiver)
     */
    public function toDevice()
    {
        return $this->belongsTo(Device::class, 'to_device_id');
    }
}
