<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PaymentController;
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
        Route::resource('subscription-plans', SubscriptionPlanController::class)->names('subscription-plans');

        Route::get('payment-methods', [SubscriptionPlanController::class, 'paymentMethods'])->name('payment-methods');
        Route::get('payment-method-create', [SubscriptionPlanController::class, 'paymentMethodCreate'])->name('payment-method-create');
        Route::get('payment-method-edit/{paymentDetails}', [SubscriptionPlanController::class, 'paymentMethodEdit'])->name('payment-method-edit');
        Route::post('payment-method-add', [SubscriptionPlanController::class, 'paymentMethodAdd'])->name('payment-method-add');
        Route::post('payment-method-update/{paymentDetails}', [SubscriptionPlanController::class, 'paymentMethodUpdate'])->name('payment-method-update');
        Route::delete('payment-method-delete/{paymentDetails}', [SubscriptionPlanController::class, 'paymentMethodDelete'])->name('payment-method-delete');

        //payment verify
        // routes/web.php – admin
        Route::get('payments', [PaymentController::class, 'index'])->name('payments');
        Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');

        // Resource routes
        // Route::resource('users', UserController::class);
        // Route::resource('devices', DeviceController::class);
        // Route::resource('commands', CommandController::class);
    });
});


// TEMPORARY — DELETE AFTER USE
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "All caches cleared!";
});
