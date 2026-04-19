<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Frontend\CommonController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


//main page
Route::get('/', function () {
    return view('welcome');
});
//public link
Route::get('/download-apk', [CommonController::class, 'index'])->name('public-link.index');
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmailWeb']);

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        //app versions
        Route::get('/app-versions', [DashboardController::class, 'apkView'])->name('app-versions.index');
        Route::post('/app-versions/upload', [DashboardController::class, 'appVersionUpload'])->name('app-versions.upload');
        Route::post('/app-versions/{id}/toggle-active', [DashboardController::class, 'toggleAppVersionActive'])->name('app-versions.toggle-active');
        Route::delete('/app-versions/{id}', [DashboardController::class, 'deleteAppVersion'])->name('app-versions.delete');

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

Route::get('/force-reset', function () {
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    return "OPcache Reset!";
});

Route::get('/run-link', function () {
    $exitCode = Artisan::call('storage:link');
    return 'Storage link created! Code: ' . $exitCode;
});
Route::get('/fix-link', function () {
    $target = public_path('storage');
    if (is_link($target) || is_dir($target)) {
        // This deletes the existing 'storage' folder/link in /public
        if (PHP_OS_FAMILY === 'Windows') {
            exec('rd /s /q "' . $target . '"');
        } else {
            exec('rm -rf "' . $target . '"');
        }
    }
    Artisan::call('storage:link');
    return 'Old link removed and new storage link created!';
});

Route::get('/fix-permissions', function () {
    $path = storage_path('app/public/files');

    if (file_exists($path)) {
        chmod($path, 0755); // Directory readable
        $files = glob($path . '/*');
        foreach ($files as $file) {
            chmod($file, 0644); // Files readable
        }
        return "Permissions updated for files in storage/app/public/files";
    }
    return "Path not found: " . $path;
});
