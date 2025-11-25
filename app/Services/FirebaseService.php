<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendPush($deviceToken, $data)
    {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withData($data);

        $this->messaging->send($message);
    }
}
