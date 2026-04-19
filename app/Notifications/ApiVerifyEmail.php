<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;

class ApiVerifyEmail extends VerifyEmailBase
{
    // protected function verificationUrl($notifiable)
    // {
    //     $token = sha1($notifiable->getKey() . now());

    //     // Save token temporarily
    //     $notifiable->forceFill([
    //         'email_verification_token' => $token
    //     ])->save();

    //     $url = Config::get('app.frontend_url', 'http://localhost:3000') . '/verify-email?token=' . $token;

    //     return $url;
    // }
    protected function verificationUrl($notifiable)
    {
        $token = sha1($notifiable->getEmailForVerification() . now());

        $notifiable->forceFill([
            'email_verification_token' => $token
        ])->save();

        // Point this to your web route (created in step 3)
        return url('/verify-email/' . $token);
    }

    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        // We use 'view' instead of 'line/action' to use our custom Blade
        return (new MailMessage)
            ->subject(config('app.name') . ': Confirm Your Email')
            ->view('emails.verify-email', [
                'url' => $url,
                'user' => $notifiable,
                'projectName' => config('app.name')
            ]);
    }
}
