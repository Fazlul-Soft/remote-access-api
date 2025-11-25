<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class ApiVerifyEmail extends VerifyEmailBase
{
    protected function verificationUrl($notifiable)
    {
        $token = sha1($notifiable->getKey() . now());

        // Save token temporarily
        $notifiable->forceFill([
            'email_verification_token' => $token
        ])->save();

        $url = Config::get('app.frontend_url', 'http://localhost:3000') . '/verify-email?token=' . $token;

        return $url;
    }
}
