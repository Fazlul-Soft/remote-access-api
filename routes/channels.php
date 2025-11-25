<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('device.{deviceId}', function ($user, $deviceId) {
    // Allow only if the device belongs to the user
    return $user->devices()->where('device_id', $deviceId)->exists();
});

Broadcast::channel('admin-stats', fn ($user) => $user->isAdmin());
Broadcast::channel('admin-commands', fn ($user) => $user->isAdmin());
