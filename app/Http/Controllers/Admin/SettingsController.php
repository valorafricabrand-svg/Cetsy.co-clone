<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Admin\RunProductImageOptimization;
use App\Models\Setting;
use App\Services\Translation\TranslationProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;   // ← add this
use Illuminate\Support\Carbon;
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
        $imageOptimizerStatus = null;
        if ($settings) {
            $imageOptimizerStatus = $this->currentImageOptimizerStatus((int) $settings->id);
        }

        return view('admin.settings.index', compact('settings', 'imageOptimizerStatus'));
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
        // Payout schedule
        'payout_schedule'   => 'nullable|in:manual,weekly,biweekly,monthly',
        'payout_weekday'    => 'nullable|integer|min:0|max:6',
        'payout_month_day'  => 'nullable|integer|min:1|max:28',
        'payout_auto_approve'  => 'nullable|boolean',
        'payout_auto_disburse' => 'nullable|boolean',
        // Subscription grace period (days)
        'subscription_grace_days' => 'nullable|integer|min:0|max:60',
        // Subscription trial
        'subscription_trial_enabled' => 'nullable|boolean',
        'subscription_trial_days' => 'nullable|integer|min:1|max:365',
        'seller_signup_auto_approve' => 'nullable|boolean',
        'seller_signup_require_logo' => 'nullable|boolean',
        'home_listings_cache_ttl_minutes' => 'nullable|integer|min:1|max:1440',

        // Shipping defaults
        'couriers'          => 'nullable|string',

        // Product duplication controls (optional)
        'duplicate_sku_strategy'    => 'nullable|in:append,clear,keep',
        'duplicate_sku_suffix'      => 'nullable|string|max:16',
        'duplicate_sku_random_len'  => 'nullable|integer|min:1|max:12',

        // Multilingual support
        'default_locale'            => 'required|string',
        'locale_rows'               => ['required', 'array', 'min:1'],
        'locale_rows.*.code'        => ['required', 'string', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z]{2,4})?$/'],
        'locale_rows.*.name'        => ['required', 'string', 'max:64'],
        'locale_rows.*.native'      => ['nullable', 'string', 'max:64'],
        'locale_rows.*.html'        => ['nullable', 'string', 'max:16'],
        'locale_rows.*.og'          => ['nullable', 'string', 'max:16'],
        'locale_rows.*.enabled'     => 'nullable|boolean',
        'translation_enabled'       => 'nullable|boolean',
        'translation_auto_translate_on_write' => 'nullable|boolean',
        'translation_queue'         => 'nullable|string|max:64',
        'translation_timeout'       => 'nullable|integer|min:1|max:120',
        'translation_retries'       => 'nullable|integer|min:1|max:10',
        'translation_chunk_size'    => 'nullable|integer|min:1|max:1000',
    ]);

    $gatewayValidated = $request->validate([
        'payments_mpesa_enabled'   => 'nullable|boolean',
        'payments_paypal_enabled'  => 'nullable|boolean',
        'payments_stripe_enabled'  => 'nullable|boolean',
        'payments_paystack_enabled' => 'nullable|boolean',
        'payments_default_gateway' => 'nullable|in:mpesa,paypal,stripe,paystack',
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
    $localeRows = array_values(array_filter((array) ($validated['locale_rows'] ?? []), 'is_array'));
    $localeCatalog = [];
    $enabledLocales = [];
    $seenLocaleCodes = [];

    foreach ($localeRows as $row) {
        $code = sanitize_locale_code((string) ($row['code'] ?? null));
        $name = trim((string) ($row['name'] ?? ''));

        if (! $code || $name === '') {
            continue;
        }

        if (in_array($code, $seenLocaleCodes, true)) {
            return back()
                ->withErrors(['locale_rows' => 'Each language code must be unique.'])
                ->withInput();
        }

        $seenLocaleCodes[] = $code;
        $native = trim((string) ($row['native'] ?? ''));
        $html = trim((string) ($row['html'] ?? ''));
        $og = trim((string) ($row['og'] ?? ''));

        $localeCatalog[$code] = [
            'name' => $name,
            'native' => $native !== '' ? $native : $name,
            'html' => $html !== '' ? $html : str_replace('_', '-', $code),
            'og' => $og !== '' ? $og : str_replace('-', '_', strtoupper($code)),
        ];

        if ((bool) ($row['enabled'] ?? false)) {
            $enabledLocales[] = $code;
        }
    }

    if ($localeCatalog === []) {
        return back()
            ->withErrors(['locale_rows' => 'Add at least one valid language.'])
            ->withInput();
    }

    $defaultLocale = sanitize_locale_code((string) ($validated['default_locale'] ?? ''));
    if (! $defaultLocale || ! isset($localeCatalog[$defaultLocale])) {
        $defaultLocale = $enabledLocales[0] ?? array_key_first($localeCatalog);
    }

    if (! $defaultLocale) {
        return back()
            ->withErrors(['default_locale' => 'Choose a valid default language.'])
            ->withInput();
    }

    if ($enabledLocales === []) {
        $enabledLocales[] = $defaultLocale;
    }

    if (! in_array($defaultLocale, $enabledLocales, true)) {
        array_unshift($enabledLocales, $defaultLocale);
    }

    $enabledLocales = array_values(array_unique(array_filter($enabledLocales)));

    if ($request->boolean('translation_enabled')) {
        $provider = app(TranslationProviderFactory::class)->make();
        $providerName = match ((string) config('translation.provider', 'deepl')) {
            'deepl' => 'DeepL',
            default => 'the active translation provider',
        };

        if (! $provider->configured()) {
            return back()
                ->withErrors([
                    'translation_enabled' => 'Configure ' . $providerName . ' credentials before enabling auto translation.',
                ])
                ->withInput();
        }

        $translatableLocales = array_fill_keys(
            array_map(
                static fn (string $locale): string => str_replace('_', '-', strtolower($locale)),
                $provider->translatableLocales()
            ),
            true
        );

        $unsupportedLocales = [];

        foreach ($localeCatalog as $localeCode => $meta) {
            $providerLocale = str_replace('_', '-', strtolower($localeCode));

            if (! isset($translatableLocales[$providerLocale])) {
                $unsupportedLocales[] = sprintf(
                    '%s (%s)',
                    (string) ($meta['name'] ?? strtoupper($localeCode)),
                    $localeCode
                );
            }
        }

        if ($unsupportedLocales !== []) {
            sort($unsupportedLocales);

            return back()
                ->withErrors([
                    'locale_rows' => $providerName . ' cannot auto-translate every language in this catalog: '
                        . implode(', ', $unsupportedLocales)
                        . '. Remove those languages or turn off auto translation before saving.',
                ])
                ->withInput();
        }
    }

    // Persisted via key-value fallback below; keep it out of mass update.
    unset(
        $validated['home_listings_cache_ttl_minutes'],
        $validated['locale_rows'],
        $validated['default_locale'],
        $validated['translation_enabled'],
        $validated['translation_auto_translate_on_write'],
        $validated['translation_queue'],
        $validated['translation_timeout'],
        $validated['translation_retries'],
        $validated['translation_chunk_size']
    );

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

        // Subscription settings
        if ($request->has('subscription_grace_days')) {
            $putSetting('subscription_grace_days', $request->input('subscription_grace_days'));
        }
        if ($request->has('subscription_trial_enabled')) {
            $putSetting('subscription_trial_enabled', $request->boolean('subscription_trial_enabled'));
        }
        if ($request->filled('subscription_trial_days')) {
            $putSetting('subscription_trial_days', $request->input('subscription_trial_days'));
        }
        if ($request->has('seller_signup_auto_approve')) {
            $putSetting('seller_signup_auto_approve', $request->boolean('seller_signup_auto_approve'));
        }
        if ($request->has('seller_signup_require_logo')) {
            $putSetting('seller_signup_require_logo', $request->boolean('seller_signup_require_logo'));
        }
        if ($request->has('home_listings_cache_ttl_minutes')) {
            $putSetting('home_listings_cache_ttl_minutes', (int) $request->input('home_listings_cache_ttl_minutes'));
        }

        $putSetting('locale_catalog', json_encode($localeCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $putSetting('default_locale', $defaultLocale);
        $putSetting('supported_locales', json_encode($enabledLocales, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $putSetting('translation_enabled', $request->boolean('translation_enabled'));
        $putSetting('translation_auto_translate_on_write', $request->boolean('translation_auto_translate_on_write'));
        $putSetting('translation_queue', trim((string) $request->input('translation_queue', 'default')) ?: 'default');
        $putSetting('translation_timeout', (int) $request->input('translation_timeout', translation_timeout_seconds()));
        $putSetting('translation_retries', (int) $request->input('translation_retries', translation_retry_count()));
        $putSetting('translation_chunk_size', (int) $request->input('translation_chunk_size', translation_chunk_size()));

        // Payout scheduling
        foreach (['payout_schedule','payout_weekday','payout_month_day'] as $k) {
            if ($request->has($k)) {
                $putSetting($k, $request->input($k));
            }
        }
        if ($request->has('payout_auto_approve')) {
            $putSetting('payout_auto_approve', $request->boolean('payout_auto_approve'));
        }
        if ($request->has('payout_auto_disburse')) {
            $putSetting('payout_auto_disburse', $request->boolean('payout_auto_disburse'));
        }

        // Payment gateways (enable/disable + default)
        $mpesaEnabled  = $request->boolean('payments_mpesa_enabled');
        $paypalEnabled = $request->boolean('payments_paypal_enabled');
        $stripeEnabled = $request->boolean('payments_stripe_enabled');
        $paystackEnabled = $request->boolean('payments_paystack_enabled');

        if (! ($mpesaEnabled || $paypalEnabled || $stripeEnabled || $paystackEnabled)) {
            return back()
                ->withErrors(['payments_gateways' => 'Enable at least one payment gateway.'])
                ->withInput();
        }

        $defaultGateway = (string) ($gatewayValidated['payments_default_gateway'] ?? 'paypal');
        $enabledMap = [
            'mpesa' => $mpesaEnabled,
            'paypal' => $paypalEnabled,
            'stripe' => $stripeEnabled,
            'paystack' => $paystackEnabled,
        ];
        if (! ($enabledMap[$defaultGateway] ?? false)) {
            foreach (['paypal', 'stripe', 'paystack', 'mpesa'] as $candidate) {
                if ($enabledMap[$candidate]) {
                    $defaultGateway = $candidate;
                    break;
                }
            }
        }

        $putSetting('payments_mpesa_enabled', $mpesaEnabled);
        $putSetting('payments_paypal_enabled', $paypalEnabled);
        $putSetting('payments_stripe_enabled', $stripeEnabled);
        $putSetting('payments_paystack_enabled', $paystackEnabled);
        $putSetting('payments_default_gateway', $defaultGateway);

        if ($request->has('home_listings_cache_ttl_minutes')) {
            foreach ([
                'home:mixed-listing-ids:all:v1',
                'home:mixed-listing-ids:digital:v1',
                'home:mixed-listing-ids:service:v1',
            ] as $cacheKey) {
                Cache::forget($cacheKey);
            }
        }

        if ($setting->isDirty()) $setting->save();
    } catch (\Throwable $e) {
        // swallow - settings table shape varies by install
    }

    if (function_exists('forget_settings_runtime_cache')) {
        forget_settings_runtime_cache();
    }

    /* ----------------------------------------------------------
     | 4. Redirect with flash                                    |
     ---------------------------------------------------------- */
    return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Bulk optimize product images and resize oversized files.
     */
    public function optimizeProductImages(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'optimizer_max_width' => ['nullable', 'integer', 'min:320', 'max:4096'],
            'optimizer_max_height' => ['nullable', 'integer', 'min:320', 'max:4096'],
            'optimizer_quality' => ['nullable', 'integer', 'min:40', 'max:95'],
        ]);

        $maxWidth = (int) ($validated['optimizer_max_width'] ?? 1600);
        $maxHeight = (int) ($validated['optimizer_max_height'] ?? 1600);
        $quality = (int) ($validated['optimizer_quality'] ?? 82);

        $cacheKey = $this->imageOptimizerStatusCacheKey((int) $setting->id);
        $currentStatus = $this->currentImageOptimizerStatus((int) $setting->id);
        $currentState = (string) ($currentStatus['state'] ?? 'idle');

        if (in_array($currentState, ['queued', 'running', 'cancel_requested'], true)) {
            $busyMessage = $currentState === 'cancel_requested'
                ? 'Cancellation is already in progress for the active run.'
                : 'An optimization run is already in progress.';

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $busyMessage,
                    'status' => $currentStatus,
                ], 409);
            }

            return back()->with('warning', $busyMessage);
        }

        $runId = (string) Str::uuid();
        $status = [
            'run_id' => $runId,
            'state' => 'queued',
            'requested_at' => now()->toIso8601String(),
            'started_at' => null,
            'finished_at' => null,
            'updated_at' => now()->toIso8601String(),
            'requested_by' => auth()->id(),
            'params' => [
                'max_width' => $maxWidth,
                'max_height' => $maxHeight,
                'quality' => $quality,
            ],
            'summary' => null,
            'message' => 'Optimization queued. Processing will start shortly.',
            'error' => null,
        ];
        Cache::put($cacheKey, $status, now()->addHours(12));
        $status = $this->decorateImageOptimizerStatus($status);

        RunProductImageOptimization::dispatch(
            (int) $setting->id,
            $runId,
            $maxWidth,
            $maxHeight,
            $quality,
            auth()->id()
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Image optimization queued.',
                'status' => $status,
                'status_url' => route('admin.settings.optimize-product-images.status', $setting->id),
                'cancel_url' => route('admin.settings.optimize-product-images.cancel', $setting->id),
            ]);
        }

        return back()->with('success', 'Image optimization queued and will run in the background.');
    }

    /**
     * Return latest optimizer run status for AJAX polling.
     */
    public function optimizeProductImagesStatus(Request $request, Setting $setting)
    {
        $status = $this->currentImageOptimizerStatus((int) $setting->id);

        return response()->json([
            'ok' => true,
            'status' => $status,
        ]);
    }

    /**
     * Request cancellation for current background optimization run.
     */
    public function cancelOptimizeProductImages(Request $request, Setting $setting)
    {
        $cacheKey = $this->imageOptimizerStatusCacheKey((int) $setting->id);
        $status = Cache::get($cacheKey, $this->defaultImageOptimizerStatus());
        if (!is_array($status)) {
            $status = $this->defaultImageOptimizerStatus();
        }
        $state = strtolower((string) ($status['state'] ?? 'idle'));

        if (!in_array($state, ['queued', 'running', 'cancel_requested'], true)) {
            $status = $this->decorateImageOptimizerStatus($status);

            return response()->json([
                'ok' => false,
                'message' => 'No active optimization run to cancel.',
                'status' => $status,
            ], 409);
        }

        if ($state === 'queued') {
            $status['state'] = 'cancelled';
            $status['cancel_requested_at'] = now()->toIso8601String();
            $status['finished_at'] = now()->toIso8601String();
            $status['updated_at'] = now()->toIso8601String();
            $status['message'] = 'Queued optimization canceled before starting.';
            Cache::put($cacheKey, $status, now()->addDays(2));
        } elseif ($state !== 'cancel_requested') {
            $status['state'] = 'cancel_requested';
            $status['cancel_requested_at'] = now()->toIso8601String();
            $status['updated_at'] = now()->toIso8601String();
            $status['message'] = 'Cancellation requested. Waiting for the worker to stop safely.';
            Cache::put($cacheKey, $status, now()->addHours(12));
        }

        $status = $this->decorateImageOptimizerStatus($status);

        return response()->json([
            'ok' => true,
            'message' => 'Cancellation requested.',
            'status' => $status,
        ]);
    }

    private function defaultImageOptimizerStatus(): array
    {
        return [
            'state' => 'idle',
            'message' => 'No optimization has been queued yet.',
        ];
    }

    private function currentImageOptimizerStatus(int $settingId): array
    {
        $status = Cache::get($this->imageOptimizerStatusCacheKey($settingId), $this->defaultImageOptimizerStatus());
        if (!is_array($status)) {
            $status = $this->defaultImageOptimizerStatus();
        }

        return $this->decorateImageOptimizerStatus($status);
    }

    private function decorateImageOptimizerStatus(array $status): array
    {
        $state = strtolower((string) ($status['state'] ?? 'idle'));
        $warnings = [];
        $health = 'ok';
        $now = now();
        $summary = is_array($status['summary'] ?? null) ? $status['summary'] : null;

        $requestedAt = $this->parseIsoTimestamp($status['requested_at'] ?? null);
        $startedAt = $this->parseIsoTimestamp($status['started_at'] ?? null);
        $updatedAt = $this->parseIsoTimestamp($status['updated_at'] ?? null);
        $schedulerHeartbeatAt = $this->parseIsoTimestamp(Cache::get($this->schedulerHeartbeatCacheKey()));

        if ($state === 'failed') {
            $health = 'error';
        }

        if ($state === 'queued' && $requestedAt && $requestedAt->diffInSeconds($now) > 120) {
            $warnings[] = 'Still queued for over 2 minutes. Ensure a queue worker is running, or cron executes "php artisan schedule:run" every minute.';
        }

        if (in_array($state, ['running', 'cancel_requested'], true) && $updatedAt && $updatedAt->diffInSeconds($now) > 180) {
            $warnings[] = 'No progress heartbeat in over 3 minutes. The worker may be stuck.';
        }

        if (in_array($state, ['running', 'cancel_requested'], true) && $startedAt && $startedAt->diffInSeconds($now) > 6600) {
            $warnings[] = 'Running near the 2-hour timeout. Consider canceling and rerunning with lower limits.';
        }

        if (in_array($state, ['queued', 'running', 'cancel_requested'], true)) {
            if (!$schedulerHeartbeatAt || $schedulerHeartbeatAt->diffInSeconds($now) > 150) {
                $warnings[] = 'Scheduler heartbeat is stale. Cron for "php artisan schedule:run" may not be running every minute.';
            } elseif ($state === 'queued' && $requestedAt && $requestedAt->diffInSeconds($now) > 120) {
                $warnings[] = 'Scheduler is alive but queue remains queued. Run "php artisan queue:work --stop-when-empty --tries=1 --timeout=7200" manually and check failed jobs.';
            }
        }

        if (!function_exists('exif_read_data')) {
            $warnings[] = 'PHP EXIF extension is not enabled in this runtime. JPEG files may be skipped by orientation safety guard.';
        }

        if ($summary) {
            $exifGuardSkipped = (int) ($summary['exif_guard_skipped'] ?? 0);
            if ($exifGuardSkipped > 0) {
                $warnings[] = sprintf(
                    'Orientation safety guard skipped %d JPEG image(s). Ensure EXIF is enabled for queue workers to optimize these safely.',
                    $exifGuardSkipped
                );
            }
        }

        if (!empty($warnings) && $health !== 'error') {
            $health = 'warning';
        }

        $status['warnings'] = $warnings;
        $status['health'] = $health;

        return $status;
    }

    private function schedulerHeartbeatCacheKey(): string
    {
        return 'system:scheduler:heartbeat';
    }

    private function parseIsoTimestamp(mixed $value): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function imageOptimizerStatusCacheKey(int $settingId): string
    {
        return 'admin:settings:' . $settingId . ':image-optimizer';
    }



}
