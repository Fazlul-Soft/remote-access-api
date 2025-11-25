<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function toggleActive(User $user)
    {
        // Optional: prevent admin from deactivating himself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate yourself!');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with(
            'success',
            $user->is_active
                ? 'User activated successfully'
                : 'User deactivated successfully'
        );
    }

    public function show(User $user)
    {
        // Load all needed relations
        $user->load(['devices', 'commandsAsController', 'commandsAsTarget']);

        return view('admin.users.show', compact('user'));
    }
}
