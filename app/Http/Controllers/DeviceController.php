<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'device_id' => 'required|unique:devices',
            'role' => 'required|in:controller,controlled',
        ]);

        $existingCheck = Device::where('user_id', Auth::id())
            ->where('role', $request->role)
            ->count();
        if ($existingCheck > 0 && $request->role === 'controller') {
            return response()->json(['error' => 'already registered'], 400);
        }

        if ($existingCheck > 0 && $request->role === 'controlled') {
            return response()->json(['error' => 'already registered'], 400);
        }

        $device = Device::create([
            'user_id' => Auth::id(),
            'device_id' => $request->device_id,
            'role' => $request->role,
        ]);

        return response()->json($device);
    }

    public function pair(Request $request)
    {
        Log::info("Pairing request", ['user_id' => Auth::id(), 'target_device_id' => $request->target_device_id]);
        $request->validate([
            'target_device_id' => 'required|exists:devices,device_id',
        ]);

        $user = Auth::user();
        $controlledCount = $user->devices()->where('role', 'controlled')->count();

        if ($controlledCount >= $user->subscriptionPlan->max_devices) {
            return response()->json(['error' => 'Subscription limit reached'], 403);
        }

        $controller = $user->devices()->where('role', 'controller')->first();
        $target = Device::where('device_id', $request->target_device_id)->where('role', 'controlled')->first();

        Log::info("Found devices for pairing", ['controller_id' => $user?->id, 'target_id' => $target?->user_id]);
        if ((int) $target->user_id !== (int) $user->id) {
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

    public function checkRegistered(Request $request)
    {
        $deviceId = $request->header('X-Device-ID'); // Flutter sends this

        if (!$deviceId) {
            return response()->json(['registered' => false]);
        }

        $exists = $request->user()->devices()
            ->where('device_id', $deviceId)
            ->exists();

        return response()->json(['registered' => $exists]);
    }
}
