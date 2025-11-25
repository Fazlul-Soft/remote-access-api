<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminCommandCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $command;

    public function __construct($command)
    {
        $this->command = $command;
    }


    public function broadcastOn(): Channel
    {
        return new PrivateChannel('admin-commands');
    }
    public function broadcastAs(): string
    {
        return 'command.created';
    }
    public function broadcastWith(): array
    {
        return [
            'command' => [
                'id'     => $this->command->id,
                'from'   => $this->command->fromDevice?->device_id,
                'to'     => $this->command->toDevice?->device_id,
                'action' => $this->command->action,
                'status' => $this->command->status,
            ],
        ];
    }
}
