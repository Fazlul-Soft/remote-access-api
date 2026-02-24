<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'starts_at',
        'expires_at',
        'data_hidden_at',
        'grace_period_days',
        'status',
    ];
    protected $casts = ['expires_at' => 'datetime', 'data_hidden_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
