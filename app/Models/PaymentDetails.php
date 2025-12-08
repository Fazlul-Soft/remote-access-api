<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetails extends Model
{
    protected $fillable = [
        'payment_method',
        'merchant_no',
        'details',
        'note',
        'logo'
    ];

    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('uploads/' . $this->logo) : asset('images/default-payment.png');
    }
}
