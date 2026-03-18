<?php
use App\Models\Setting;
use App\Models\Shop;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

function favicon_url(){
    try {
        $val = function_exists('setting') ? (string) setting('favicon_url', '') : '';
        if (trim($val) !== '') return $val;
    } catch (\Throwable $e) {
        // ignore
    }

    return asset('assets/img/favicons/favicon-32x32.png');
}

if (! function_exists('storage_rel_path')) {
    /**
     * Strip leading public/storage prefixes and leading slash.
     */
    function storage_rel_path(?string $path): ?string
    {
        if (!$path) return null;
        $p = ltrim($path, '/');
        if (str_starts_with($p, 'public/'))  { $p = substr($p, 7); }
        if (str_starts_with($p, 'storage/')) { $p = substr($p, 8); }
        return $p;
    }
}

if (! function_exists('media_url')) {
    /**
     * Build a public URL for a media path, with fallback across common dirs.
     */
    function media_url(?string $path): string
    {
        if (!$path) return asset('storage/placeholder.jpg');
        $rel = storage_rel_path($path);
        try {
            if (\Storage::disk('public')->exists($rel)) {
                return \Storage::disk('public')->url($rel);
            }
        } catch (\Throwable $e) {}

        $basename = basename($rel);
        foreach (['product-media','product_media','product-images','products'] as $dir) {
            $cand = $dir . '/' . $basename;
            try { if (\Storage::disk('public')->exists($cand)) return \Storage::disk('public')->url($cand); } catch (\Throwable $e) {}
        }

        return asset('storage/' . ltrim($rel ?: $path, '/'));
    }
}

if (! function_exists('is_video_media_path')) {
    /**
     * Determine whether a media path/URL points to a video file.
     */
    function is_video_media_path(?string $path): bool
    {
        if (!$path) return false;

        $candidate = trim($path);
        if ($candidate === '') return false;

        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://') || str_starts_with($candidate, '//')) {
            $parsedPath = parse_url($candidate, PHP_URL_PATH);
            if (is_string($parsedPath) && $parsedPath !== '') {
                $candidate = $parsedPath;
            }
        }

        $candidate = (string) preg_replace('/[?#].*$/', '', $candidate);
        $ext = strtolower((string) pathinfo($candidate, PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'mov', 'webm', 'm4v', 'avi', 'mkv', 'wmv', 'flv', 'ogv', 'ogg', '3gp'], true);
    }
}

function logo_url(){
    try {
        $logo = function_exists('setting') ? (string) setting('logo_url', '') : '';
        if (trim($logo) !== '') return $logo;

        $fav = function_exists('setting') ? (string) setting('favicon_url', '') : '';
        if (trim($fav) !== '') return $fav;
    } catch (\Throwable $e) {
        // ignore
    }

    return asset('assets/images/cetsylogmain.png');
}


function price($price = 0){
    // Backward-compatible: assumes $price is in USD base; formats in selected currency
    $amount = is_numeric($price) ? (float) $price : 0.0;
    return money($amount, null);
}

function settings() {
    $domain = request()->getHost();

    // Remove 'www.' from the beginning if present
    if (strpos($domain, 'www.') === 0) {
        $domain = substr($domain, 4);
    }

    $setting = Setting::where('domain_name', $domain)->first();

    return $setting;
}


function shop_id(){

    $shop = Shop::whereUserId(Auth::id())->first();
    return $shop->id;
}


function shop(){
    return 'Cetsy';
}

/**
 * Subscription grace period in days (from settings with a default of 5).
 */
function subscription_grace_days(): int
{
    try {
        $v = (int) setting('subscription_grace_days', 5);
        return $v > 0 ? $v : 5;
    } catch (\Throwable $e) {
        return 5;
    }
}

    function avatar_img_url($path, $storage = null){
      if (!$path) {
        return '';
      }

      $path = trim((string) $path);
      if ($path === '') {
        return '';
      }

      $storage = $storage ? trim((string) $storage) : null;

      // Backward compatibility for any old calls that passed storage first.
      if (in_array($path, ['public', 's3'], true) && !in_array((string) $storage, ['public', 's3'], true)) {
        [$path, $storage] = [(string) $storage, $path];
      }

      if ($path === '') {
        return '';
      }

      if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
        return $path;
      }

      $rel = storage_rel_path($path) ?? ltrim($path, '/');

      if ($storage === 's3') {
        try {
          return \Illuminate\Support\Facades\Storage::disk('s3')->url($rel);
        } catch (\Throwable $e) {
          return '';
        }
      }

      try {
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($rel)) {
          return \Illuminate\Support\Facades\Storage::disk('public')->url($rel);
        }
      } catch (\Throwable $e) {}

      $basename = basename($rel);
      foreach (['profile-photos', 'avatars', 'uploads/photos', 'uploads/avatar'] as $dir) {
        $candidate = $dir . '/' . $basename;
        try {
          if (\Illuminate\Support\Facades\Storage::disk('public')->exists($candidate)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($candidate);
          }
        } catch (\Throwable $e) {}
      }

      return asset('storage/' . ltrim($rel ?: $path, '/'));
    }

    function get_option($option_key = ''){
      $get = \App\Models\Setting::where('option_key', $option_key)->first();
      if($get) {
          return $get->option_value;
      }
      return $option_key;
  }


   function wallet($status = 'completed', $userId = null){
     $id = $userId ?? Auth::id();
     if (!$id) {
         return 0;
     }

     $walletBalance = \App\Models\Wallet::where('user_id', $id)
                            ->where('status', $status)
                            ->selectRaw('SUM(credit - debit) as balance')
                            ->value('balance');

     if ($walletBalance === null || abs((float) $walletBalance) < 0.00001) {
         $walletBalance = \App\Models\Wallet::where('user_id', $id)
                                ->where('status', $status)
                                ->latest('id')
                                ->value('balance') ?? 0;
     }

        return (float) $walletBalance;
  }

   function wallet_on_hold(){
     return wallet('on_hold');
   }


     function admin_wallet($status = 'completed') {

     $walletBalance = \App\Models\Wallet::where('status', $status)
                            ->selectRaw('SUM(credit - debit) as balance')
                            ->value('balance') ?? 0;

        return $walletBalance;
  }


  function get_currency() {
    // Priority: Auth user's preference -> session override -> cookie -> site default
    try {
        if (function_exists('auth') && auth()->check()) {
            $u = auth()->user();
            if (!empty($u->preferred_currency) && strlen($u->preferred_currency) === 3) {
                return strtoupper($u->preferred_currency);
            }
        }
    } catch (\Throwable $e) {}

    try {
        $sessionCode = session()->has('currency_code') ? session('currency_code') : null;
        if (!empty($sessionCode) && strlen($sessionCode) === 3) {
            return strtoupper($sessionCode);
        }
    } catch (\Throwable $e) {}

    try {
        $cookieCode = request()->cookies->get('currency_code');
        if (!empty($cookieCode) && strlen($cookieCode) === 3) {
            return strtoupper($cookieCode);
        }
    } catch (\Throwable $e) {}

    $default = setting('default_currency', 'USD') ?: 'USD';
    $code = strtoupper((string) $default);
    return strlen($code) === 3 ? $code : 'USD';
 }

// ---- Currency conversion helpers (server-side) ----
if (! function_exists('fx_rates')) {
    /**
     * Return decoded USD-based rates from settings.
     * @return array<string,float>
     */
    function fx_rates(): array
    {
        $json = setting('exchange_rates_json');
        if (empty($json)) return [];
        try {
            $arr = json_decode($json, true);
            return is_array($arr) ? $arr : [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}

if (! function_exists('fx_rate')) {
    /** Get USD→code rate. Missing => 1.0 for USD, else 0.0 */
    function fx_rate(string $code): float
    {
        $code = strtoupper($code);
        if ($code === 'USD') return 1.0;
        // Prefer DB table if present
        try {
            if (class_exists('App\\Models\\Currency')) {
                $c = \App\Models\Currency::where('code', $code)->first();
                if ($c && $c->usd_rate > 0) return (float) $c->usd_rate;
            }
        } catch (\Throwable $e) {}
        // Fallback to settings JSON
        $rates = fx_rates();
        $r = $rates[$code] ?? null;
        return $r ? (float) $r : 0.0;
    }
}

if (! function_exists('convert_usd')) {
    /** Convert an amount from USD to target currency (default: site default). */
    function convert_usd(float $amountUsd, ?string $to = null): float
    {
        $to = $to ? strtoupper($to) : get_currency();
        $rate = fx_rate($to);
        if ($rate <= 0) return round($amountUsd, 2);
        return round($amountUsd * $rate, 2);
    }
}

if (! function_exists('money')) {
    /** Format amount using default currency with USD-based conversion. */
  function money(float $amountUsd = 0.0, ?int $precision = null): string
  {
      $code = get_currency();
      $value = convert_usd($amountUsd, $code);
        // Determine decimals if not provided (prefer DB)
        $dec = 2;
        if ($precision === null) {
            try {
                if (class_exists('App\\Models\\Currency')) {
                    $c = \App\Models\Currency::where('code', $code)->first();
                    if ($c && is_numeric($c->decimal_places)) {
                        $dec = max(0, min(6, (int) $c->decimal_places));
                    }
                }
            } catch (\Throwable $e) {}
        } else {
            $dec = $precision;
        }
      return $code.' '.number_format($value, $dec);
  }
}

// Backward-compat alias used by some legacy views
if (! function_exists('shop_currency')) {
    function shop_currency(): string
    {
        return get_currency();
    }
}

if (! function_exists('currency_symbol')) {
    /** Map currency code to symbol for display (fallback to code). */
    function currency_symbol(?string $code = null): string
    {
        $code = strtoupper($code ?: get_currency());
        // Prefer DB symbol if set
        try {
            if (class_exists('App\\Models\\Currency')) {
                $c = \App\Models\Currency::where('code', $code)->first();
                if ($c && !empty($c->symbol)) return $c->symbol;
            }
        } catch (\Throwable $e) {}
        return match ($code) {
            'USD' => '$',
            'EUR' => '',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'NGN' => '₦',
            'AUD' => 'A$',
            'CAD' => 'C$',
            default => $code,
        };
    }
}

if (! function_exists('symbol_money')) {
    /** Format with currency symbol instead of code. */
    function symbol_money(float $amountUsd, ?int $precision = 2): string
    {
        $code = get_currency();
        $value = convert_usd($amountUsd, $code);
        return currency_symbol($code).' '.number_format($value, $precision ?? 2);
    }
}

if (! function_exists('apply_discount')) {
    /**
     * Calculate the discounted price for a given product.
     *
     * @param  float  $price      Base or variant price.
     * @param  int    $productId  ID of the product.
     * @return float              Price after applying any product or deal discount.
     */
    function apply_discount(float $price, int $productId): float
    {
        $product = Product::find($productId);

        return $product ? $product->applyDiscount($price) : $price;
    }
}

if (! function_exists('product_has_available_stock')) {
    /**
     * Determine whether a product currently has sellable stock.
     * For items with variations, at least one variant must have stock > 0 or be unlimited (null).
     * For simple items, a null stock means "unlimited".
     */
    function product_has_available_stock($product): bool
    {
        if (! $product) {
            return false;
        }

        $type = strtolower((string)($product->product_type ?? $product->type ?? ''));
        $digitalTypes = ['digital', 'download', 'digital_download', 'digital-download', 'service'];
        if (in_array($type, $digitalTypes, true)) {
            return true;
        }

        // Attempt to reuse any loaded variants to avoid N+1 queries.
        $variants = collect();
        if (method_exists($product, 'variations')) {
            try {
                if (method_exists($product, 'loadMissing')) {
                    $product->loadMissing('variations');
                }
                if ($product->relationLoaded('variations')) {
                    $variants = $product->variations ?? collect();
                } else {
                    $variants = $product->variations()->get();
                }
            } catch (\Throwable $e) {
                $variants = collect();
            }
        }

        if ($variants->isNotEmpty()) {
            foreach ($variants as $variant) {
                $stock = $variant->stock;
                if ($stock === null || (int) $stock > 0) {
                    return true;
                }
            }
            return false;
        }

        // Fallback to legacy/simple stock column.
        $stock = $product->stock;
        if ($stock === null) {
            return true;
        }

        return (int) $stock > 0;
    }
}

if (! function_exists('product_is_out_of_stock')) {
    function product_is_out_of_stock($product): bool
    {
        return ! product_has_available_stock($product);
    }
}


if (! function_exists('setting')) {
    /**
     * Get a setting value by logical key.
     * Supports two storage models:
     * 1) Column-based single row (e.g., default_currency on the main settings row)
     * 2) Key-value rows (option_key/option_value)
     */
    function setting(string $key, $default = null)
    {
        static $cachedRow = null;

        // Try key-value store first if the table uses option_key/option_value
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('settings', 'option_key')) {
                $val = Setting::where('option_key', $key)->value('option_value');
                if ($val !== null) return $val;
            }
        } catch (\Throwable $e) {}

        // Fallback to column-based single-row model
        try {
            if (! $cachedRow) {
                // Prefer a row without option_key if the column exists
                if (\Illuminate\Support\Facades\Schema::hasColumn('settings', 'option_key')) {
                    $cachedRow = Setting::whereNull('option_key')->orderByDesc('id')->first();
                    if (! $cachedRow) {
                        // Some installs have option_key non-nullable; treat empty key row or a row with null option_value as the "main" settings row.
                        $cachedRow = Setting::where('option_key', '')->orderByDesc('id')->first();
                    }
                    if (! $cachedRow && \Illuminate\Support\Facades\Schema::hasColumn('settings', 'option_value')) {
                        $cachedRow = Setting::whereNull('option_value')->orderByDesc('id')->first();
                    }
                    // If none, fallback to any row
                    if (! $cachedRow) $cachedRow = Setting::orderByDesc('id')->first();
                } else {
                    $cachedRow = Setting::orderByDesc('id')->first();
                }
            }
            if ($cachedRow && isset($cachedRow->$key)) {
                return $cachedRow->$key;
            }
        } catch (\Throwable $e) {}

        return $default;
    }
}

if (! function_exists('setting_bool')) {
    /**
     * Read a setting and normalize it to boolean.
     */
    function setting_bool(string $key, bool $default = false): bool
    {
        if (! function_exists('setting')) {
            return $default;
        }

        $raw = setting($key, null);
        if ($raw === null || $raw === '') {
            return $default;
        }
        if (is_bool($raw)) {
            return $raw;
        }
        if (is_numeric($raw)) {
            return ((int) $raw) === 1;
        }

        $raw = strtolower(trim((string) $raw));
        if (in_array($raw, ['1', 'true', 'yes', 'on', 'enabled', 'active'], true)) {
            return true;
        }
        if (in_array($raw, ['0', 'false', 'no', 'off', 'disabled', 'inactive'], true)) {
            return false;
        }

        return $default;
    }
}

if (! function_exists('payment_gateway_enabled')) {
    function payment_gateway_enabled(string $gateway): bool
    {
        $gateway = strtolower(trim($gateway));
        $key = match ($gateway) {
            'mpesa'  => 'payments_mpesa_enabled',
            'paypal' => 'payments_paypal_enabled',
            'stripe' => 'payments_stripe_enabled',
            'paystack' => 'payments_paystack_enabled',
            default  => null,
        };
        if (! $key) return false;

        $raw = function_exists('setting') ? setting($key, null) : null;
        if ($raw === null || $raw === '') return true; // default on

        $rawStr = strtolower(trim((string) $raw));
        if (in_array($rawStr, ['1','true','yes','on','enabled','active'], true)) return true;
        if (in_array($rawStr, ['0','false','no','off','disabled','inactive'], true)) return false;
        return (bool) $raw;
    }
}

if (! function_exists('payment_default_gateway')) {
    function payment_default_gateway(): string
    {
        $raw = function_exists('setting') ? (string) setting('payments_default_gateway', 'paypal') : 'paypal';
        $gw = strtolower(trim($raw));
        return in_array($gw, ['paypal', 'stripe', 'mpesa', 'paystack'], true) ? $gw : 'paypal';
    }
}

if (! function_exists('payment_gateway_configured')) {
    function payment_gateway_configured(string $gateway): bool
    {
        $gateway = strtolower(trim($gateway));

        if ($gateway === 'paypal') {
            return !empty(config('services.paypal.client_id'))
                || (function_exists('setting') && !empty(setting('paypal_client_id')));
        }

        if ($gateway === 'stripe') {
            return !empty(config('services.stripe.secret'))
                || (function_exists('setting') && !empty(setting('stripe_secret')));
        }

        if ($gateway === 'paystack') {
            return !empty(config('services.paystack.secret'))
                || (function_exists('setting') && !empty(setting('paystack_secret')));
        }

        if ($gateway === 'mpesa') {
            return !empty(env('SAFARICOM_DARAJA_BASE_URL'))
                && !empty(env('SAFARICOM_SHORTCODE'))
                && !empty(env('SAFARICOM_PASSKEY'));
        }

        return false;
    }
}

if (! function_exists('payment_gateway_available')) {
    function payment_gateway_available(string $gateway): bool
    {
        return payment_gateway_enabled($gateway) && payment_gateway_configured($gateway);
    }
}

if (! function_exists('payment_method_label')) {
    function payment_method_label(?string $method, string $fallback = '-'): string
    {
        $value = trim((string) $method);

        if ($value === '') {
            return $fallback;
        }

        return match (strtolower($value)) {
            'paypal' => 'PayPal',
            'mpesa', 'm-pesa' => 'M-Pesa',
            'stripe' => 'Stripe',
            'paystack' => 'Paystack',
            'wallet' => 'Wallet',
            'card', 'credit_card', 'credit-card' => 'Card',
            'bank_transfer', 'bank-transfer' => 'Bank Transfer',
            'cash_on_delivery', 'cash-on-delivery' => 'Cash on Delivery',
            'cash' => 'Cash',
            'pending' => 'Pending',
            default => \Illuminate\Support\Str::of($value)
                ->replace(['_', '-'], ' ')
                ->title()
                ->value(),
        };
    }
}

if (! function_exists('theme')) {
    /**
     * Shortcut for 'theme' setting.
     */
    function theme(): string
    {
        return setting('theme', 'cetsy');
    }
}

if (! function_exists('support_email')) {
    /**
     * Resolve the public support email.
     * Priority: settings table (support_email) -> env SUPPORT_EMAIL -> default fallback.
     */
    function support_email(): string
    {
        try {
            $email = setting('support_email');
            if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        } catch (\Throwable $e) {}

        $env = env('SUPPORT_EMAIL');
        if (is_string($env) && filter_var($env, FILTER_VALIDATE_EMAIL)) {
            return $env;
        }

        return 'hello@cetsy.co';
    }
}

if (! function_exists('support_phone')) {
    /**
     * Resolve the public support phone number.
     * Priority: settings table (support_phone) -> env SUPPORT_PHONE -> settings table (phone).
     */
    function support_phone(): string
    {
        $candidates = [];

        try { $candidates[] = setting('support_phone'); } catch (\Throwable $e) {}
        $candidates[] = env('SUPPORT_PHONE');
        try { $candidates[] = setting('phone'); } catch (\Throwable $e) {}

        foreach ($candidates as $candidate) {
            if (! is_string($candidate)) continue;
            $value = trim($candidate);
            if ($value !== '') return $value;
        }

        return '+12342883361';
    }
}

if (! function_exists('support_address')) {
    /**
     * Resolve the public support address.
     * Priority: settings table (support_address) -> settings table (address) -> env SUPPORT_ADDRESS.
     */
    function support_address(): string
    {
        $candidates = [];

        try { $candidates[] = setting('support_address'); } catch (\Throwable $e) {}
        try { $candidates[] = setting('address'); } catch (\Throwable $e) {}
        $candidates[] = env('SUPPORT_ADDRESS');

        foreach ($candidates as $candidate) {
            if (! is_string($candidate)) continue;
            $value = trim($candidate);
            if ($value !== '') return $value;
        }

        return '';
    }
}

if (! function_exists('operating_region')) {
    /**
     * Resolve the country/region of operation shown on policy pages.
     * Priority: settings table (operating_region / region) -> env OPERATING_COUNTRY.
     */
    function operating_region(): string
    {
        $candidates = [];

        try { $candidates[] = setting('operating_region'); } catch (\Throwable $e) {}
        try { $candidates[] = setting('region'); } catch (\Throwable $e) {}
        $candidates[] = env('OPERATING_COUNTRY');

        foreach ($candidates as $candidate) {
            if (! is_string($candidate)) continue;
            $value = trim($candidate);
            if ($value === '' || strtolower($value) === 'country') continue;
            return $value;
        }

        // Last-resort fallback: must be explicit for payment reviewers.
        return 'Kenya';
    }
}

if (! function_exists('legal_jurisdiction')) {
    /**
     * Resolve governing law / jurisdiction shown in Terms.
     * Priority: settings table (legal_jurisdiction) -> env LEGAL_JURISDICTION -> operating_region().
     */
    function legal_jurisdiction(): string
    {
        $candidates = [];

        try { $candidates[] = setting('legal_jurisdiction'); } catch (\Throwable $e) {}
        $candidates[] = env('LEGAL_JURISDICTION');

        foreach ($candidates as $candidate) {
            if (! is_string($candidate)) continue;
            $value = trim($candidate);
            if ($value !== '') return $value;
        }

        return operating_region();
    }
}

if (! function_exists('policy_effective_label')) {
    /**
     * Resolve the shared effective date label shown on policy pages.
     * Priority: settings table (policy_effective_date) -> env POLICY_EFFECTIVE_DATE -> default fallback.
     */
    function policy_effective_label(): string
    {
        $candidates = [];

        try { $candidates[] = setting('policy_effective_date'); } catch (\Throwable $e) {}
        $candidates[] = env('POLICY_EFFECTIVE_DATE');

        foreach ($candidates as $candidate) {
            if (! is_string($candidate)) continue;
            $value = trim($candidate);
            if ($value !== '') return $value;
        }

        return 'August 2025';
    }
}

if (! function_exists('product_thumb_url')) {
    /**
     * Build a product thumbnail URL with fallbacks:
     * 1) featured_image (absolute or storage path)
     * 2) first media item
     * 3) product->shop->logo
     * 4) setting('favicon_url')
     * 5) storage/placeholder.jpg
     */
    function product_thumb_url($product): string
    {
        if (! $product) {
            return setting('favicon_url') ?: asset('storage/placeholder.jpg');
        }

        // Normalize a path to a public-disk relative path
        $normalize = function (?string $path): ?string {
            if (!$path) return null;
            $p = ltrim($path, '/');
            if (str_starts_with($p, 'public/'))  { $p = substr($p, 7); }
            if (str_starts_with($p, 'storage/')) { $p = substr($p, 8); }
            return $p;
        };

        // Try to resolve to an existing file on the public disk.
        $resolvePublic = function (?string $rel) use ($normalize): ?string {
            if (!$rel) return null;
            $rel = $normalize($rel);
            try {
                if (\Storage::disk('public')->exists($rel)) {
                    return $rel;
                }
            } catch (\Throwable $e) {}

            $basename = basename($rel);
            $candidates = [];
            // If original included a directory, try its filename in common folders
            $candidates[] = $rel;
            $dirs = ['product-media','product_media','product-images','products'];
            foreach ($dirs as $dir) { $candidates[] = $dir . '/' . $basename; }
            foreach ($candidates as $cand) {
                try { if (\Storage::disk('public')->exists($cand)) return $cand; } catch (\Throwable $e) {}
            }
            return $rel;
        };

        // 1) Featured image (skip video files for <img> thumbnails)
        $fi = $product->featured_image ?? null;
        if (!empty($fi) && !is_video_media_path($fi)) {
            if (str_starts_with($fi, 'http')) {
                return $fi;
            }
            $rel = $resolvePublic($fi);
            try { return \Storage::disk('public')->url($rel); }
            catch (\Throwable $e) { return asset('storage/' . ltrim($rel ?: $fi, '/')); }
        }

        // 2) First image-like media (never return video URL as image thumb)
        $mediaItems = collect();
        try {
            if (method_exists($product, 'relationLoaded') && $product->relationLoaded('media')) {
                $mediaItems = collect($product->media ?? []);
            } elseif (isset($product->media)) {
                $mediaItems = collect($product->media ?? []);
                if ($mediaItems->isEmpty() && method_exists($product, 'media')) {
                    $mediaItems = $product->media()->get();
                }
            } elseif (method_exists($product, 'media')) {
                $mediaItems = $product->media()->get();
            }
        } catch (\Throwable $e) {
            $mediaItems = collect();
        }

        $firstImageMedia = $mediaItems->first(function ($media) {
            $url = (string) ($media->url ?? '');
            if ($url === '') return false;
            $type = strtolower((string) ($media->type ?? ''));
            if ($type === 'video') return false;
            if ($type === 'image') return true;
            return !is_video_media_path($url);
        });

        if ($firstImageMedia && !empty($firstImageMedia->url)) {
            $rel = $resolvePublic($firstImageMedia->url);
            try { return \Storage::disk('public')->url($rel); }
            catch (\Throwable $e) { return asset('storage/' . ltrim($rel ?: $firstImageMedia->url, '/')); }
        }

        // 3) Shop logo
        if ($product->shop && !empty($product->shop->logo)) {
            $rel = $resolvePublic($product->shop->logo);
            try { return \Storage::disk('public')->url($rel); }
            catch (\Throwable $e) { return asset('storage/' . ltrim($rel ?: $product->shop->logo, '/')); }
        }

        // 4) Fallbacks
        return setting('favicon_url') ?: asset('storage/placeholder.jpg');
    }
}


if (! function_exists('couriers_list')) {
    /**
     * Return a list of default couriers from settings (JSON),
     * or a sensible fallback list if not configured.
     */
    function couriers_list(): array
    {
        $default = [
            'DHL','FedEx','UPS','USPS','Royal Mail','DPD','Evri','GLS',
            'Canada Post','Australia Post','PostNL','La Poste','SEUR','Correos','Aramex','TNT',
        ];

        $raw = setting('couriers_json');
        if (!empty($raw)) {
            try {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && !empty($decoded)) {
                    // Normalize: trim strings and remove empties
                    $list = array_values(array_filter(array_map('trim', $decoded), fn($v) => $v !== ''));
                    if (!empty($list)) {
                        return $list;
                    }
                }
            } catch (\Throwable $e) {}
        }

        return $default;
    }
}


 function country_name($id){
    if (!$id) {
        return 'N/A';
    }
    $country = Country::find($id);
    return $country ? $country->name : 'N/A';
 }




    function themed_view(string $view, array $data = [])
    {
       
 $theme    = theme();             ;

        // Compose full themed view name
        $themedView = "theme."."$theme.$view";

   return view($themedView, $data);

       
    }






    function currencies(){
    return array(
        'AED' => 'United Arab Emirates dirham',
        'AFN' => 'Afghan afghani',
        'ALL' => 'Albanian lek',
        'AMD' => 'Armenian dram',
        'ANG' => 'Netherlands Antillean guilder',
        'AOA' => 'Angolan kwanza',
        'ARS' => 'Argentine peso',
        'AUD' => 'Australian dollar',
        'AWG' => 'Aruban florin',
        'AZN' => 'Azerbaijani manat',
        'BAM' => 'Bosnia and Herzegovina convertible mark',
        'BBD' => 'Barbadian dollar',
        'BDT' => 'Bangladeshi taka',
        'BGN' => 'Bulgarian lev',
        'BHD' => 'Bahraini dinar',
        'BIF' => 'Burundian franc',
        'BMD' => 'Bermudian dollar',
        'BND' => 'Brunei dollar',
        'BOB' => 'Bolivian boliviano',
        'BRL' => 'Brazilian real',
        'BSD' => 'Bahamian dollar',
        'BTC' => 'Bitcoin',
        'BTN' => 'Bhutanese ngultrum',
        'BWP' => 'Botswana pula',
        'BYR' => 'Belarusian ruble',
        'BZD' => 'Belize dollar',
        'CAD' => 'Canadian dollar',
        'CDF' => 'Congolese franc',
        'CHF' => 'Swiss franc',
        'CLP' => 'Chilean peso',
        'CNY' => 'Chinese yuan',
        'COP' => 'Colombian peso',
        'CRC' => 'Costa Rican col&oacute;n',
        'CUC' => 'Cuban convertible peso',
        'CUP' => 'Cuban peso',
        'CVE' => 'Cape Verdean escudo',
        'CZK' => 'Czech koruna',
        'DJF' => 'Djiboutian franc',
        'DKK' => 'Danish krone',
        'DOP' => 'Dominican peso',
        'DZD' => 'Algerian dinar',
        'EGP' => 'Egyptian pound',
        'ERN' => 'Eritrean nakfa',
        'ETB' => 'Ethiopian birr',
        'EUR' => 'Euro',
        'FJD' => 'Fijian dollar',
        'FKP' => 'Falkland Islands pound',
        'GBP' => 'Pound sterling',
        'GEL' => 'Georgian lari',
        'GGP' => 'Guernsey pound',
        'GHS' => 'Ghana cedi',
        'GIP' => 'Gibraltar pound',
        'GMD' => 'Gambian dalasi',
        'GNF' => 'Guinean franc',
        'GTQ' => 'Guatemalan quetzal',
        'GYD' => 'Guyanese dollar',
        'HKD' => 'Hong Kong dollar',
        'HNL' => 'Honduran lempira',
        'HRK' => 'Croatian kuna',
        'HTG' => 'Haitian gourde',
        'HUF' => 'Hungarian forint',
        'IDR' => 'Indonesian rupiah',
        'ILS' => 'Israeli new shekel',
        'IMP' => 'Manx pound',
        'INR' => 'Indian rupee',
        'IQD' => 'Iraqi dinar',
        'IRR' => 'Iranian rial',
        'ISK' => 'Icelandic kr&oacute;na',
        'JEP' => 'Jersey pound',
        'JMD' => 'Jamaican dollar',
        'JOD' => 'Jordanian dinar',
        'JPY' => 'Japanese yen',
        'KES' => 'Kenyan shilling',
        'KGS' => 'Kyrgyzstani som',
        'KHR' => 'Cambodian riel',
        'KMF' => 'Comorian franc',
        'KPW' => 'North Korean won',
        'KRW' => 'South Korean won',
        'KWD' => 'Kuwaiti dinar',
        'KYD' => 'Cayman Islands dollar',
        'KZT' => 'Kazakhstani tenge',
        'LAK' => 'Lao kip',
        'LBP' => 'Lebanese pound',
        'LKR' => 'Sri Lankan rupee',
        'LRD' => 'Liberian dollar',
        'LSL' => 'Lesotho loti',
        'LYD' => 'Libyan dinar',
        'MAD' => 'Moroccan dirham',
        'MDL' => 'Moldovan leu',
        'MGA' => 'Malagasy ariary',
        'MKD' => 'Macedonian denar',
        'MMK' => 'Burmese kyat',
        'MNT' => 'Mongolian t&ouml;gr&ouml;g',
        'MOP' => 'Macanese pataca',
        'MRO' => 'Mauritanian ouguiya',
        'MUR' => 'Mauritian rupee',
        'MVR' => 'Maldivian rufiyaa',
        'MWK' => 'Malawian kwacha',
        'MXN' => 'Mexican peso',
        'MYR' => 'Malaysian ringgit',
        'MZN' => 'Mozambican metical',
        'NAD' => 'Namibian dollar',
        'NGN' => 'Nigerian naira',
        'NIO' => 'Nicaraguan c&oacute;rdoba',
        'NOK' => 'Norwegian krone',
        'NPR' => 'Nepalese rupee',
        'NZD' => 'New Zealand dollar',
        'OMR' => 'Omani rial',
        'PAB' => 'Panamanian balboa',
        'PEN' => 'Peruvian nuevo sol',
        'PGK' => 'Papua New Guinean kina',
        'PHP' => 'Philippine peso',
        'PKR' => 'Pakistani rupee',
        'PLN' => 'Polish z&#x142;oty',
        'PRB' => 'Transnistrian ruble',
        'PYG' => 'Paraguayan guaran&iacute;',
        'QAR' => 'Qatari riyal',
        'RON' => 'Romanian leu',
        'RSD' => 'Serbian dinar',
        'RUB' => 'Russian ruble',
        'RWF' => 'Rwandan franc',
        'SAR' => 'Saudi riyal',
        'SBD' => 'Solomon Islands dollar',
        'SCR' => 'Seychellois rupee',
        'SDG' => 'Sudanese pound',
        'SEK' => 'Swedish krona',
        'SGD' => 'Singapore dollar',
        'SHP' => 'Saint Helena pound',
        'SLL' => 'Sierra Leonean leone',
        'SOS' => 'Somali shilling',
        'SRD' => 'Surinamese dollar',
        'SSP' => 'South Sudanese pound',
        'STD' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra',
        'SYP' => 'Syrian pound',
        'SZL' => 'Swazi lilangeni',
        'THB' => 'Thai baht',
        'TJS' => 'Tajikistani somoni',
        'TMT' => 'Turkmenistan manat',
        'TND' => 'Tunisian dinar',
        'TOP' => 'Tongan pa&#x2bb;anga',
        'TRY' => 'Turkish lira',
        'TTD' => 'Trinidad and Tobago dollar',
        'TWD' => 'New Taiwan dollar',
        'TZS' => 'Tanzanian shilling',
        'UAH' => 'Ukrainian hryvnia',
        'UGX' => 'Ugandan shilling',
        'USD' => 'United States dollar',
        'UYU' => 'Uruguayan peso',
        'UZS' => 'Uzbekistani som',
        'VEF' => 'Venezuelan bol&iacute;var',
        'VND' => 'Vietnamese &#x111;&#x1ed3;ng',
        'VUV' => 'Vanuatu vatu',
        'WST' => 'Samoan t&#x101;l&#x101;',
        'XAF' => 'Central African CFA franc',
        'XCD' => 'East Caribbean dollar',
        'XOF' => 'West African CFA franc',
        'XPF' => 'CFP franc',
        'YER' => 'Yemeni rial',
        'ZAR' => 'South African rand',
        'ZMW' => 'Zambian kwacha',
    );

}


?>
