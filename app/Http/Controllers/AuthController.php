<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'nullable|email|unique:users,email',
            'phone'    => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!$request->email && !$request->phone) {
            throw ValidationException::withMessages([
                'email' => 'Either email or phone is required.'
            ]);
        }

        $user = User::create([
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'user'
        ]);

        // Send verification email ONLY if email exists
        if ($user->email) {
            $user->sendEmailVerificationNotification();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
            'token'   => $token,
            'requires_verification' => $user->email ? true : false
        ]);
    }

    public function login(Request $request)
    {

        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::where($field, $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'The provided credentials are incorrect.',
            ]);
        }

        // Block login if email exists AND not verified
        if ($user->email && !$user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email before logging in.',
                'requires_verification' => true
            ], 403);
        }

        if (!$user->isActive()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account has been deactivated by admin.',
            ], 403);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token
        ]);
    }

    // NEW: Email verification endpoint
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token'
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified'
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully'
        ]);
    }

    // NEW: Resend verification email
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified'
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => 'success',
            'message' => 'Verification link sent'
        ]);
    }
}
