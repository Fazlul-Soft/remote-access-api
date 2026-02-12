<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Device;
use App\Models\Command;
use App\Models\AppVersion;
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

    public function apkView()
    {
        $versions = AppVersion::all();

        return view('admin.apk.index', compact('versions'));
    }


    public function appVersionUpload(Request $request)
    {
        try {
            $request->validate([
                'version_name'   => 'required|string|max:255',
                'platform'       => 'required|in:android,ios',
                'release_notes'  => 'nullable|string',
                'apk_file'       => 'required|file', // Validation for mimes:apk can be finicky, better to check extension manually as you did
                'is_active'      => 'nullable',
            ]);

            $file = $request->file('apk_file');
            $ext = $file->getClientOriginalExtension();

            if ($ext !== 'apk') {
                return back()->withErrors(['error' => 'Only APK files are allowed.']);
            }

            $filename = 'app-' . time() . '.apk';

            // Define the path relative to the public folder
            $destinationPath = public_path('apk_versions');

            // Move the file directly to public/apk_versions/
            $file->move($destinationPath, $filename);

            $app_version = new AppVersion();
            $app_version->version_name = $request->version_name;
            $app_version->platform     = $request->platform;
            $app_version->release_notes = $request->release_notes;

            // Store the relative path for easy URL generation later
            $app_version->file_path    = 'apk_versions/' . $filename;
            $app_version->is_active    = $request->boolean('is_active');
            $app_version->save();

            return redirect()->back()->with('success', 'App version uploaded successfully to public folder.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function toggleAppVersionActive($id)
    {
        $version = AppVersion::findOrFail($id);
        $version->is_active = !$version->is_active;
        $version->save();

        return back()->with('success', 'Status toggled successfully.');
    }

    public function deleteAppVersion($id)
    {
        $version = AppVersion::findOrFail($id);

        // Physical file path in the public folder
        $file_path = public_path($version->file_path);

        // Delete the record from DB
        $version->delete();

        // Delete the physical file if it exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        return back()->with('success', 'App version and file deleted successfully.');
    }

    
}
