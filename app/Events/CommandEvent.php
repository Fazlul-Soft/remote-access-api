<?php

namespace App\Events;

use App\Models\Command;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommandEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('device.' . $this->command->to_device_id);
    }

    public function broadcastWith(): array
    {
        return [
            'command_id' => $this->command->id,
            'action' => $this->command->action,
            'payload' => json_decode($this->command->payload, true),
            'from_device' => $this->command->from_device_id,
        ];
    }
}
