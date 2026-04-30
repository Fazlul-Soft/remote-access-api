<?php

namespace App\Http\Controllers;

use App\Events\LocationUpdated;
use App\Models\Device;
use App\Models\DeviceLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        $deviceId = $request->header('X-Device-ID');
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        DeviceLocation::updateOrCreate(
            ['device_id' => $device->id],
            [
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy'  => $request->accuracy ?? 0,
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    // Auth required — controller device calls this
    public function latest(Request $request)
    {
        $user = Auth::user();

        $controllerDevice = $user->devices()
            ->where('device_id', $request->header('X-Device-ID'))
            ->first();

        if (!$controllerDevice) {
            return response()->json(null);
        }

        $controlled = Device::where('paired_to', $controllerDevice->id)->first();
        if (!$controlled) {
            return response()->json(null);
        }

        $location = DeviceLocation::where('device_id', $controlled->id)
            ->latest()
            ->first();

        if (!$location) {
            return response()->json(null);
        }

        return response()->json([
            'latitude'   => $location->latitude,
            'longitude'  => $location->longitude,
            'accuracy'   => $location->accuracy,
            'updated_at' => $location->updated_at,
        ]);
    }
}
