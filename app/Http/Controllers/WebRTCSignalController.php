<?php

namespace App\Http\Controllers;

use App\Models\WebRTCSignal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebRTCSignalController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('📡 WebRTC Signal Incoming', $request->all());

            // Simple validation without strict exists check
            $request->validate([
                'from_device_id' => 'required|integer',
                'target_device_id' => 'required|integer',
                'type' => 'required|string',
                'data' => 'required',
            ]);

            // Verify devices exist before creating signal
            $fromExists = DB::table('devices')->where('id', $request->from_device_id)->exists();
            $toExists = DB::table('devices')->where('id', $request->target_device_id)->exists();

            if (!$fromExists) {
                Log::error('From device not found', ['id' => $request->from_device_id]);
                return response()->json([
                    'status' => 'error',
                    'message' => "From device ID {$request->from_device_id} not found"
                ], 404);
            }

            if (!$toExists) {
                Log::error('Target device not found', ['id' => $request->target_device_id]);
                return response()->json([
                    'status' => 'error',
                    'message' => "Target device ID {$request->target_device_id} not found"
                ], 404);
            }

            // Create signal
            $signal = WebRTCSignal::create([
                'from_device_id' => (int) $request->from_device_id,
                'target_device_id' => (int) $request->target_device_id,
                'type' => $request->type,
                'data' => $request->data,
            ]);

            if (!$signal) {
                throw new \Exception('Failed to create signal');
            }

            Log::info('✅ Signal stored', [
                'id' => $signal->id,
                'from' => $signal->from_device_id,
                'to' => $signal->target_device_id,
                'type' => $signal->type,
            ]);

            return response()->json([
                'status' => 'success',
                'id' => $signal->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation error', [
                'errors' => $e->errors(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Signal store error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPending(Request $request)
    {
        $deviceIdString = $request->header('X-Device-ID');

        if (!$deviceIdString) {
            return response()->json([]);
        }

        $device = DB::table('devices')
            ->where('device_id', $deviceIdString)
            ->first();

        if (!$device) {
            return response()->json([]);
        }

        $myDbId = $device->id;

        Log::info('📥 Polling signals', [
            'device_id' => $deviceIdString,
            'db_id' => $myDbId,
        ]);

        // Get signals for THIS device
        $signals = WebRTCSignal::where('target_device_id', $myDbId)
            ->orderBy('created_at', 'asc')
            ->get();

        // CRITICAL: Only delete if we actually found signals AND returned them
        if ($signals->isNotEmpty()) {
            Log::info('✅ Found signals', [
                'count' => $signals->count(),
                'types' => $signals->pluck('type')->toArray(),
                'ids' => $signals->pluck('id')->toArray(),
            ]);

            // Get the IDs we're about to return
            $signalIds = $signals->pluck('id')->toArray();

            // Convert to array before deleting
            $signalsArray = $signals->toArray();

            // NOW delete them
            WebRTCSignal::whereIn('id', $signalIds)->delete();

            Log::info('🗑️ Deleted signals', ['ids' => $signalIds]);

            // Return the signals we fetched BEFORE deletion
            return response()->json($signalsArray);
        }

        return response()->json([]);
    }
}
