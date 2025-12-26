<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $settings = null;
        try {
            if (Schema::hasColumn('settings', 'option_key')) {
                $settings = Setting::whereNull('option_key')->first();
            }
        } catch (\Throwable $e) {
            // ignore (DB might not be ready during install)
        }
        $settings = $settings ?: Setting::first();
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
        'release_fee_percent' => 'nullable|numeric|min:0|max:100',
        'fee_rate'          => 'nullable|numeric|min:0|max:100',
        'min_amount'        => 'nullable|numeric|min:0',
        'auto_release_days' => 'nullable|integer|min:1|max:365',
        // Subscription grace period (days)
        'subscription_grace_days' => 'nullable|integer|min:0|max:60',

        // Shipping defaults
        'couriers'          => 'nullable|string',

        // Product duplication controls (optional)
        'duplicate_sku_strategy'    => 'nullable|in:append,clear,keep',
        'duplicate_sku_suffix'      => 'nullable|string|max:16',
        'duplicate_sku_random_len'  => 'nullable|integer|min:1|max:12',
    ]);

    $gatewayValidated = $request->validate([
        'payments_mpesa_enabled'   => 'nullable|boolean',
        'payments_paypal_enabled'  => 'nullable|boolean',
        'payments_stripe_enabled'  => 'nullable|boolean',
        'payments_default_gateway' => 'nullable|in:mpesa,paypal,stripe',
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

    // Persist settings that may be stored either as columns or key-value rows
    try {
        $putSetting = function (string $key, $value) use ($setting): void {
            if (Schema::hasColumn('settings', $key)) {
                $setting->setAttribute($key, $value);
                return;
            }
            if (Schema::hasColumn('settings', 'option_key') && Schema::hasColumn('settings', 'option_value')) {
                DB::table('settings')->updateOrInsert(
                    ['option_key' => $key],
                    ['option_value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
                );
            }
        };

        // Duplication settings
        foreach (['duplicate_sku_strategy','duplicate_sku_suffix','duplicate_sku_random_len','release_fee_percent'] as $k) {
            if (! $request->has($k)) continue;
            $putSetting($k, $request->input($k));
        }

        // Payment gateways (enable/disable + default)
        $mpesaEnabled  = $request->boolean('payments_mpesa_enabled');
        $paypalEnabled = $request->boolean('payments_paypal_enabled');
        $stripeEnabled = $request->boolean('payments_stripe_enabled');

        if (! ($mpesaEnabled || $paypalEnabled || $stripeEnabled)) {
            return back()
                ->withErrors(['payments_gateways' => 'Enable at least one payment gateway.'])
                ->withInput();
        }

        $defaultGateway = (string) ($gatewayValidated['payments_default_gateway'] ?? 'paypal');
        $enabledMap = ['mpesa' => $mpesaEnabled, 'paypal' => $paypalEnabled, 'stripe' => $stripeEnabled];
        if (! ($enabledMap[$defaultGateway] ?? false)) {
            foreach (['paypal', 'stripe', 'mpesa'] as $candidate) {
                if ($enabledMap[$candidate]) {
                    $defaultGateway = $candidate;
                    break;
                }
            }
        }

        $putSetting('payments_mpesa_enabled', $mpesaEnabled);
        $putSetting('payments_paypal_enabled', $paypalEnabled);
        $putSetting('payments_stripe_enabled', $stripeEnabled);
        $putSetting('payments_default_gateway', $defaultGateway);

        if ($setting->isDirty()) $setting->save();
    } catch (\Throwable $e) {
        // swallow - settings table shape varies by install
    }

    /* ----------------------------------------------------------
     | 4. Redirect with flash                                    |
     ---------------------------------------------------------- */
    return back()->with('success', 'Settings updated successfully.');
    }



}
