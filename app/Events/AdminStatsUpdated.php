<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class AdminStatsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $stats;

    public function __construct($stats)
    {
        $this->stats = $stats;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('admin-stats');
    }

    public function broadcastAs(): string
    {
        return 'stats.updated';
    }
}
