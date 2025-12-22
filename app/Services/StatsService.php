<?php

namespace App\Services;

use App\Models\User;
use App\Models\Device;
use App\Models\Command;

class StatsService
{
    public static function current()
    {
        return [
            'users'    => User::count(),
            'devices'  => Device::count(),
            'commands' => Command::count(),
            'pending'  => Command::where('status', 'pending')->count(),
        ];
    }
}

