<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SubscriptionPlanController;


//admin part
// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::view('/users', 'admin.users.index')->name('users.index');
        Route::view('/devices', 'admin.devices.index')->name('devices.index');
        Route::view('/commands', 'admin.commands.index')->name('commands.index');

        //users
        Route::get('/users/{user}', [UserController::class, 'show'])
            ->name('users.show');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');

        //subscription
        Route::resource('subscription-plans', SubscriptionPlanController::class)
            ->names('subscription-plans');

        // Resource routes
        // Route::resource('users', UserController::class);
        // Route::resource('devices', DeviceController::class);
        // Route::resource('commands', CommandController::class);
    });
});
