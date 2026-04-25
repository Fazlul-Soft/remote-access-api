<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Events\LocationUpdated;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        $deviceId = $request->header('X-Device-ID');
        $device = Device::where('device_id', $deviceId)->first();

        // if (!$device || $device->role !== 'controlled') {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        // Find the controller this device is paired to
        $controller = Device::find($device->paired_to);
        if (!$controller) {
            return response()->json(['error' => 'No controller'], 404);
        }

        broadcast(new LocationUpdated(
            controllerDeviceId: $controller->device_id,
            latitude: $request->latitude,
            longitude: $request->longitude,
            accuracy: $request->accuracy ?? 0,
            timestamp: $request->timestamp ?? now()->timestamp,
        ));

        return response()->json(['status' => 'broadcasted']);
    }
}
