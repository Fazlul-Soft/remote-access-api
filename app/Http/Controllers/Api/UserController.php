<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Device;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get current authenticated user with subscription details
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('subscriptionPlan', 'devices');

        return response()->json([
            'id'                    => $user->id,
            'email'                 => $user->email,
            'phone'                 => $user->phone,
            'is_active'             => $user->is_active ?? true,
            'email_verified_at'     => $user->email_verified_at,

            // Subscription
            'subscription_status' => $user->latestPayment?->status,
            'subscription_plan_id'  => $user->subscription_plan_id,
            'plan_name'             => $user->subscriptionPlan?->name ?? 'Free',
            'max_devices'           => $user->subscriptionPlan?->max_devices ?? 1,

            // Device role (for Flutter UI: controller or controlled)
            'role'                  => $user->devices->first()?->role, // null if no device registered

            // Optional: All devices (for future use)
            'devices'               => $user->devices->map(function ($device) {
                return [
                    'id'         => $device->id,
                    'device_id'  => $device->device_id,
                    'role'       => $device->role,
                    'paired_to'  => $device->paired_to,
                    'fcm_token'  => $device->fcm_token ? 'present' : null,
                ];
            }),
        ]);
    }
}
