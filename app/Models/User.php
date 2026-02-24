<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ApiVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'is_active', 'subscription_plan_id'];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id', 'id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin'; // or however you store it
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new ApiVerifyEmail);
    }

    public function isActive(): bool
    {
        return $this->is_active == true;
    }

    public function commandsAsController()
    {
        return $this->hasManyThrough(Command::class, Device::class, 'user_id', 'from_device_id', 'id', 'id');
    }

    public function commandsAsTarget()
    {
        return $this->hasManyThrough(Command::class, Device::class, 'user_id', 'to_device_id', 'id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function activeSubscription()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function hasActiveSubscription()
    {
        return $this->subscription_plan_id && $this->subscription()->exists();
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
