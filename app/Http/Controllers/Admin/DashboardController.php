<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Device;
use App\Models\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_devices' => Device::count(),
            'total_commands' => Command::count(),
            'pending_commands' => Command::where('status', 'pending')->count(),
        ];

        $recentCommands = Command::with(['fromDevice', 'toDevice'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentCommands'));
    }
}
