<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

// app/Events/WebRTCSignal.php
class WebRTCSignal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $targetDeviceId;
    public $type;
    public $data;

    public function __construct($targetDeviceId, $type, $data)
    {
        $this->targetDeviceId = $targetDeviceId; // The ID of the phone receiving the signal
        $this->type = $type; // 'offer', 'answer', or 'candidate'
        $this->data = $data;
    }

    public function broadcastOn()
    {
        // Each device listens on its own private channel
        return new PrivateChannel('device.' . $this->targetDeviceId);
    }
}
