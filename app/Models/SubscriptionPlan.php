<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'max_devices',
        'price',
        'payment_method',
        'duration',
        'grace_period_days',
        'hide_data_after_days',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}
