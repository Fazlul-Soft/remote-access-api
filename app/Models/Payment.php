<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'subscription_plan_id',
        'amount',
        'transaction_id',
        'gateway',
        'status',
        'admin_note',
        'verified_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($payment) {
            $payment->uuid = strtoupper(Str::random(10));
        });
    }

}
