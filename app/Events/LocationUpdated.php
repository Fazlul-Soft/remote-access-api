<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $controllerDeviceId,
        public float $latitude,
        public float $longitude,
        public float $accuracy,
        public int $timestamp,
    ) {}

    public function broadcastOn(): array
    {
        // Private channel per controller device
        return [new PrivateChannel('location.' . $this->controllerDeviceId)];
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }
}
