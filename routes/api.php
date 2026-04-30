<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebRTCSignalController;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
Route::post('/email/resend', [AuthController::class, 'resendVerification']);

//without auth only device id for controlled device

Route::post('send-web-rtc-signal', [WebRTCSignalController::class, 'store']);
Route::get('web-rtc-signals-pending', [WebRTCSignalController::class, 'getPending']);
Route::get('/commands/pending', [CommandController::class, 'pending']);
Route::post('/command/complete', [AccessController::class, 'completeCommand']);
Route::get('/devices/my-id', [DeviceController::class, 'getMyDeviceId']);
Route::post('/location/update', [LocationController::class, 'update']);

// Route::post('/devices/auto-pair', [DeviceController::class, 'autoPair']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [UserController::class, 'me']);

    Route::get('/plans', [SubscriptionController::class, 'plans']);
    Route::get('/payment-method', [SubscriptionController::class, 'paymentMethod']);
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);

    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::post('/devices/pair', [DeviceController::class, 'pair']);
    Route::post('/devices/fcm', [DeviceController::class, 'updateFcmToken']);
    Route::get('/devices/check', [DeviceController::class, 'checkRegistered']);

    Route::post('/webrtc/signal', [AccessController::class, 'signal']);
    Route::post('/access/camera', [AccessController::class, 'camera']);
    Route::post('/camera/upload', [AccessController::class, 'uploadCameraFile']);
    Route::post('/access/screen', [AccessController::class, 'requestScreenShare']);

    Route::post('/access/call', [AccessController::class, 'call']);

    Route::post('/access/file', [AccessController::class, 'file']);
    Route::post('/command/auto_sync', [AccessController::class, 'fileAutoSync']);

    Route::post('/access/gallery', [AccessController::class, 'gallery']);
    Route::post('/access/gallery/auto-sync', [AccessController::class, 'galleryAutoSync']);
    Route::post('/access/gallery/upload', [AccessController::class, 'uploadMedia']);


    Route::post('/access/message', [AccessController::class, 'message']);
    // NEW: Get single command by ID
    Route::get('/commands/{id}', [CommandController::class, 'show']);
    Route::post('/commands/history', [CommandController::class, 'history']);
    Route::get('/commands/sms/{command}', [CommandController::class, 'smsDetail']);

    Route::post('/commands/sms_sync', [AccessController::class, 'smsAutoSync']);

    Route::post('/access/location', [AccessController::class, 'location']);

    Route::get('/location/latest', [LocationController::class, 'latest']);
    Route::post('/access/location', [AccessController::class, 'location']);
});
