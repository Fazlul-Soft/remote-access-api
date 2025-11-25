<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'device_id' => 'required|unique:devices',
            'role' => 'required|in:controller,controlled',
        ]);

        $device = Device::create([
            'user_id' => Auth::id(),
            'device_id' => $request->device_id,
            'role' => $request->role,
        ]);

        return response()->json($device);
    }

    public function pair(Request $request)
    {
        $request->validate(['target_device_id' => 'required|exists:devices,device_id']);

        $user = Auth::user();
        $controlledCount = $user->devices()->where('role', 'controlled')->count();

        if ($controlledCount >= $user->subscriptionPlan->max_devices) {
            return response()->json(['error' => 'Subscription limit reached'], 403);
        }

        $controller = $user->devices()->where('role', 'controller')->first();
        $target = Device::where('device_id', $request->target_device_id)->first();

        if ($target->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $target->paired_to = $controller->id;
        $target->save();

        return response()->json(['message' => 'Paired successfully']);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $device = $request->user()->devices()->where('device_id', $request->header('X-Device-ID'))->first();

        if ($device) {
            $device->update(['fcm_token' => $request->fcm_token]);
        }

        return response()->json(['status' => 'updated']);
    }
}
