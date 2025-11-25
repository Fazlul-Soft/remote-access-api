<?php

if (!function_exists('getCurrentStats')) {
    function getCurrentStats()
    {
        return [
            'users'    => \App\Models\User::count(),
            'devices'  => \App\Models\Device::count(),
            'commands' => \App\Models\Command::count(),
            'pending'  => \App\Models\Command::where('status', 'pending')->count(),
        ];
    }
}
