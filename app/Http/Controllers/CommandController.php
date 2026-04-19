<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CommandController extends Controller
{
    // Get single command by ID
    public function show($id)
    {
        $command = Command::find($id); // Use find() instead of findOrFail()

        if (!$command) {
            return response()->json([
                'message' => "Command not found",
            ], 404);
        }

        $user = Auth::user();
        $userDeviceIds = $user->devices->pluck('id')->toArray();

        if (
            !in_array($command->from_device_id, $userDeviceIds) &&
            !in_array($command->to_device_id, $userDeviceIds)
        ) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'id' => $command->id,
            'action' => $command->action,
            'status' => $command->status,
            'result' => $command->result,
            'error' => $command->error,
            'payload' => $command->payload,
            'from_device_id' => $command->from_device_id,
            'to_device_id' => $command->to_device_id,
            'created_at' => $command->created_at,
            'updated_at' => $command->updated_at,
        ]);
    }

    // Get pending commands for current device
    public function pending(Request $request)
    {
        $deviceId = $request->header('X-Device-ID');

        if (!$deviceId) {
            return response()->json([]);
        }

        // $user = Auth::user();
        // $device = $user->devices()->where('device_id', $deviceId)->first();
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([]);
        }

        // Get pending commands for this device (never fail)
        $commands = Command::where('to_device_id', $device->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Always return a JSON array
        return response()->json($commands->map(function ($cmd) {
            return [
                'id' => $cmd->id,
                'action' => $cmd->action,
                'payload' => $cmd->payload,
                'from_device_id' => $cmd->from_device_id,
                'created_at' => $cmd->created_at,
            ];
        }));
    }

    public function history(Request $request)
    {
        Log::info('Fetching command history', ['request' => $request->all()]);
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // Optional: filter by action
        $actionFilter = $request->query('action'); // e.g., message_access, files, gallery

        $deviceIds = $user->devices->pluck('id');

        $query = Command::whereIn('to_device_id', $deviceIds)
            ->where('status', 'completed')
            ->whereNotNull('result')
            ->latest();

        if ($actionFilter) {
            $query->where('action', $actionFilter);
        }

        $commands = $query->get(['id', 'action', 'result', 'status', 'created_at']);

        return response()->json([
            'data' => $commands
        ]);
    }

    // Optional: individual SMS view
    public function smsDetail(Command $command)
    {
        if ($command->action !== 'message_access') {
            return response()->json(['error' => 'Not a message command'], 400);
        }

        $result = json_decode($command->result, true) ?? [];

        return response()->json([
            'data' => $result
        ]);
    }

}
