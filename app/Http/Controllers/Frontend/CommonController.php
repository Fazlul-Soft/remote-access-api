<?php

namespace App\Http\Controllers\Frontend;

use App\Models\AppVersion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Capture the controller ID from the URL (?id=xxxx)
        $refId = $request->query('id');

        // Get only the latest active version
        $version = AppVersion::where('is_active', true)
            ->where('platform', 'android')
            ->latest()
            ->first();

        return view('frontend.public-link.index', compact('version', 'refId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
