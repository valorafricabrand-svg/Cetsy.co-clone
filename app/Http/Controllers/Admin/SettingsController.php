<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display a listing of settings.
     */
    public function index()
    {
        // Retrieve all settings (or paginate if many)
        $setting = Setting::first();
        return view('admin.settings.index', compact('setting'));
    }

    /**
     * Show the form for editing a specific setting.
     */
    public function edit(Setting $setting)
    {
        return view('admin.settings.edit', compact('setting'));
    }

    /**
     * Validate and update the specified setting.
     */
    public function update(Request $request, Setting $setting)
    {
        // Example validation rules - adjust keys to match your settings columns
        $data = $request->validate([
            'key'   => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ]);

        $setting->update($data);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }
}
