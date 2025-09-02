<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;   // ← add this
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Display a listing of settings.
     */
    public function index()
    {
        // Retrieve all settings (or paginate if many)
        $settings = Setting::first();
        return view('admin.settings.index', compact('settings'));
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
    /* ----------------------------------------------------------
     | 1. Validate input                                         |
     ---------------------------------------------------------- */
    $validated = $request->validate([
        // Branding
        'site_name'         => 'required|string|max:255',
        'meta_description'  => 'required|string|max:255',

        // Logo & Favicon: either file OR URL (both optional)
        'logo'              => 'nullable|image|max:2048',                       // 2 MB
        'logo_url'          => 'nullable|max:255',
        'favicon'           => 'nullable|image|mimes:png,ico,svg|max:1024',     // 1 MB
        'favicon_url'       => 'nullable|max:255',

        // Contact
        'phone'             => 'nullable|string|max:50',
        'email'             => 'required|email|max:255',
        'address'           => 'nullable|string|max:255',
        'whatsapp_number'   => 'nullable|string|max:50',
        'timezone'          => 'required|string|max:64',

        // Social
        'facebook_url'      => 'nullable|url|max:255',
        'instagram_url'     => 'nullable|url|max:255',
        'x_url'             => 'nullable|url|max:255',
        'linkedin_url'      => 'nullable|url|max:255',
        'tiktok_url'        => 'nullable|url|max:255',
        'youtube_url'       => 'nullable|url|max:255',

        // Payment & payouts
        'paypal_client_id'  => 'nullable|string|max:255',
        'paypal_transaction_fee_percent'  => 'nullable|string|max:255',
        'default_currency'  => 'required|string|size:3',
        // Payout settings (fee stored as percent; e.g. 1.5 for 1.5%)
        'fee_rate'          => 'nullable|numeric|min:0|max:100',
        'min_amount'        => 'nullable|numeric|min:0',
        'auto_release_days' => 'nullable|integer|min:1|max:365',

        // Shipping defaults
        'couriers'          => 'nullable|string',
    ]);

    /* ----------------------------------------------------------
     | 2. Handle file uploads                                    |
     ---------------------------------------------------------- */
    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->store('settings', 'public');
        $validated['logo_url'] = Storage::url($path);         // public URL
    }

    if ($request->hasFile('favicon')) {
        $path = $request->file('favicon')->store('settings', 'public');
        $validated['favicon_url'] = Storage::url($path);
    }

    /* ----------------------------------------------------------
     | 3. Persist to DB                                          |
     ---------------------------------------------------------- */
    // Couriers list: parse textarea into JSON array
    if (array_key_exists('couriers', $validated)) {
        $lines = preg_split("/\r\n|\r|\n/", (string) $validated['couriers']);
        $lines = array_map('trim', $lines);
        $lines = array_values(array_filter($lines, fn($v) => $v !== ''));
        $validated['couriers_json'] = json_encode($lines);
        unset($validated['couriers']);
    }

    $setting->update($validated);

    /* ----------------------------------------------------------
     | 4. Redirect with flash                                    |
     ---------------------------------------------------------- */
    return back()->with('success', 'Settings updated successfully.');
}



}
