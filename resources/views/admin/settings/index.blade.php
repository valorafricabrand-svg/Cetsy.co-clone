{{-- resources/views/admin/settings/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <h1 class="mb-4 fw-bold">Site Settings</h1>

  <form action="{{ route('admin.settings.update', $settings->id) }}"
        method="POST"
        enctype="multipart/form-data"
        class="needs-validation"
        novalidate>
    @csrf
    @method('PUT')

    <!-- ========== BRANDING ========== -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Branding</div>
      <div class="card-body">
        <div class="row g-3">

          <!-- Site name / meta -->
          <div class="col-md-6">
            <label class="form-label">Site Name</label>
            <input type="text" name="site_name"
                   class="form-control @error('site_name') is-invalid @enderror"
                   value="{{ old('site_name', $settings->site_name) }}" required>
            @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Meta Description</label>
            <input type="text" name="meta_description"
                   class="form-control @error('meta_description') is-invalid @enderror"
                   value="{{ old('meta_description', $settings->meta_description) }}" required>
            @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Logo upload & URL -->
          <div class="col-md-6">
            <label class="form-label">Logo Image (PNG/JPG&nbsp;&le;&nbsp;2 MB)</label>
            <input type="file" name="logo" accept="image/*"
                   class="form-control @error('logo') is-invalid @enderror">
            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if($settings->logo_url)
              <div class="mt-2">
                <img src="{{ $settings->logo_url }}" alt="Current logo"
                     class="img-thumbnail" style="max-height:80px">
              </div>
            @endif

            <small class="text-muted">…or keep / paste a URL:</small>
            <input type="text" name="logo_url"
                   class="form-control mt-1 @error('logo_url') is-invalid @enderror"
                   value="{{ old('logo_url', $settings->logo_url) }}">
            @error('logo_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Favicon upload & URL -->
          <div class="col-md-6">
            <label class="form-label">Favicon Image (PNG/ICO/SVG&nbsp;&le;&nbsp;1 MB)</label>
            <input type="file" name="favicon" accept="image/*,.ico"
                   class="form-control @error('favicon') is-invalid @enderror">
            @error('favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if($settings->favicon_url)
              <div class="mt-2">
                <img src="{{ $settings->favicon_url }}" alt="Current favicon"
                     class="img-thumbnail" style="max-height:48px">
              </div>
            @endif

            <small class="text-muted">…or keep / paste a URL:</small>
            <input type="text" name="favicon_url"
                   class="form-control mt-1 @error('favicon_url') is-invalid @enderror"
                   value="{{ old('favicon_url', $settings->favicon_url) }}">
            @error('favicon_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

        </div>
      </div>
    </div>

    <!-- ========== CONTACT ========== -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Contact</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone"
                   class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $settings->phone) }}">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $settings->email) }}" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="address" rows="2"
                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $settings->address) }}</textarea>
            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">WhatsApp Number</label>
            <input type="text" name="whatsapp_number"
                   class="form-control @error('whatsapp_number') is-invalid @enderror"
                   value="{{ old('whatsapp_number', $settings->whatsapp_number) }}">
            @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Timezone</label>
            <input type="text" name="timezone"
                   class="form-control @error('timezone') is-invalid @enderror"
                   value="{{ old('timezone', $settings->timezone) }}"
                   placeholder="e.g. Africa/Nairobi" required>
            @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- ========== SOCIAL LINKS ========== -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Social Links</div>
      <div class="card-body">
        <div class="row g-3">
          @php
            $social = [
              'facebook_url'  => 'Facebook URL',
              'instagram_url' => 'Instagram URL',
              'x_url'         => 'X (Twitter) URL',
              'linkedin_url'  => 'LinkedIn URL',
              'tiktok_url'    => 'TikTok URL',
              'youtube_url'   => 'YouTube URL',
            ];
          @endphp
          @foreach($social as $field => $label)
            <div class="col-md-6">
              <label class="form-label">{{ $label }}</label>
              <input type="url" name="{{ $field }}"
                     class="form-control @error($field) is-invalid @enderror"
                     value="{{ old($field, $settings->$field) }}">
              @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- ========== MULTILINGUAL SUPPORT ========== -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Multilingual Support</div>
      <div class="card-body">
        @php
          $localeCatalog = function_exists('locale_catalog')
            ? locale_catalog()
            : ((array) config('locales.catalog', config('locales.supported', ['en' => [], 'sw' => []])));
          $activeLocales = array_keys(supported_locales());
          $defaultLocaleValue = old('default_locale', default_locale());
          $localeRows = old('locale_rows');
          if (!is_array($localeRows) || $localeRows === []) {
            $localeRows = [];
            foreach ($localeCatalog as $localeCode => $localeMeta) {
              $localeRows[] = [
                'code' => $localeCode,
                'name' => $localeMeta['name'] ?? strtoupper($localeCode),
                'native' => $localeMeta['native'] ?? ($localeMeta['name'] ?? strtoupper($localeCode)),
                'html' => $localeMeta['html'] ?? str_replace('_', '-', $localeCode),
                'og' => $localeMeta['og'] ?? '',
                'enabled' => in_array($localeCode, $activeLocales, true) ? '1' : '0',
              ];
            }
          }
          $localeRows = array_values(array_filter($localeRows, 'is_array'));
          if ($localeRows === []) {
            $localeRows[] = [
              'code' => 'en',
              'name' => 'English',
              'native' => 'English',
              'html' => 'en',
              'og' => 'en_US',
              'enabled' => '1',
            ];
          }
          $translationEnabledValue = (bool) (int) old('translation_enabled', translation_enabled() ? 1 : 0);
          $translationOnWriteValue = (bool) (int) old('translation_auto_translate_on_write', translation_auto_translate_on_write() ? 1 : 0);
          $translationQueueValue = old('translation_queue', translation_queue_name());
          $translationTimeoutValue = old('translation_timeout', translation_timeout_seconds());
          $translationRetriesValue = old('translation_retries', translation_retry_count());
          $translationChunkValue = old('translation_chunk_size', translation_chunk_size());
        @endphp

        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
              <div>
                <label class="form-label mb-0">Supported Languages</label>
                <div class="form-text">Add, edit, enable, or remove storefront languages here.</div>
                <div class="form-text">When auto translation is enabled, every catalog language must also be supported by DeepL before the settings can be saved.</div>
              </div>
              <button type="button" class="btn btn-outline-primary btn-sm" id="addLocaleRow">
                Add Language
              </button>
            </div>

            <div class="table-responsive border rounded">
              <table class="table table-sm align-middle mb-0" id="localeRowsTable">
                <thead class="table-light">
                  <tr>
                    <th style="min-width:90px;">Default</th>
                    <th style="min-width:90px;">Enabled</th>
                    <th style="min-width:120px;">Code</th>
                    <th style="min-width:160px;">Name</th>
                    <th style="min-width:160px;">Native</th>
                    <th style="min-width:120px;">HTML</th>
                    <th style="min-width:120px;">OG</th>
                    <th style="min-width:80px;">Action</th>
                  </tr>
                </thead>
                <tbody id="localeRowsBody">
                  @foreach($localeRows as $index => $row)
                    @php
                      $rowCode = strtolower(trim((string) ($row['code'] ?? '')));
                      $rowName = (string) ($row['name'] ?? '');
                      $rowNative = (string) ($row['native'] ?? '');
                      $rowHtml = (string) ($row['html'] ?? '');
                      $rowOg = (string) ($row['og'] ?? '');
                      $rowEnabled = (bool) (int) ($row['enabled'] ?? 0);
                      $rowDefault = $rowCode !== '' && $defaultLocaleValue === $rowCode;
                    @endphp
                    <tr data-locale-row>
                      <td>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="default_locale" value="{{ $rowCode }}" data-locale-default-radio {{ $rowDefault ? 'checked' : '' }}>
                          <label class="form-check-label small">Default</label>
                        </div>
                      </td>
                      <td>
                        <input type="hidden" name="locale_rows[{{ $index }}][enabled]" value="0">
                        <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" role="switch" name="locale_rows[{{ $index }}][enabled]" value="1" {{ $rowEnabled ? 'checked' : '' }}>
                        </div>
                      </td>
                      <td>
                        <input type="text" name="locale_rows[{{ $index }}][code]" value="{{ $rowCode }}" class="form-control form-control-sm @error("locale_rows.$index.code") is-invalid @enderror" placeholder="en" data-locale-code>
                        @error("locale_rows.$index.code") <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </td>
                      <td>
                        <input type="text" name="locale_rows[{{ $index }}][name]" value="{{ $rowName }}" class="form-control form-control-sm @error("locale_rows.$index.name") is-invalid @enderror" placeholder="English">
                        @error("locale_rows.$index.name") <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </td>
                      <td>
                        <input type="text" name="locale_rows[{{ $index }}][native]" value="{{ $rowNative }}" class="form-control form-control-sm @error("locale_rows.$index.native") is-invalid @enderror" placeholder="English">
                        @error("locale_rows.$index.native") <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </td>
                      <td>
                        <input type="text" name="locale_rows[{{ $index }}][html]" value="{{ $rowHtml }}" class="form-control form-control-sm @error("locale_rows.$index.html") is-invalid @enderror" placeholder="en">
                        @error("locale_rows.$index.html") <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </td>
                      <td>
                        <input type="text" name="locale_rows[{{ $index }}][og]" value="{{ $rowOg }}" class="form-control form-control-sm @error("locale_rows.$index.og") is-invalid @enderror" placeholder="en_US">
                        @error("locale_rows.$index.og") <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-locale-row>&times;</button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @error('locale_rows') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            @error('default_locale') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Enable Auto Translation</label>
            <input type="hidden" name="translation_enabled" value="0">
            <div class="form-check form-switch mt-1">
              <input class="form-check-input" type="checkbox" role="switch" id="translation-enabled"
                     name="translation_enabled" value="1" {{ $translationEnabledValue ? 'checked' : '' }}>
              <label class="form-check-label" for="translation-enabled">Enabled</label>
            </div>
            <div class="form-text">Automatically translate missing marketplace content when supported.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Auto Translate On Save</label>
            <input type="hidden" name="translation_auto_translate_on_write" value="0">
            <div class="form-check form-switch mt-1">
              <input class="form-check-input" type="checkbox" role="switch" id="translation-on-write"
                     name="translation_auto_translate_on_write" value="1" {{ $translationOnWriteValue ? 'checked' : '' }}>
              <label class="form-check-label" for="translation-on-write">Enabled</label>
            </div>
            <div class="form-text">Queues translation jobs after shop and listing content changes.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Translation Queue</label>
            <input type="text"
                   name="translation_queue"
                   class="form-control @error('translation_queue') is-invalid @enderror"
                   value="{{ $translationQueueValue }}"
                   placeholder="default">
            @error('translation_queue') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Request Timeout (seconds)</label>
            <input type="number"
                   name="translation_timeout"
                   class="form-control @error('translation_timeout') is-invalid @enderror"
                   value="{{ $translationTimeoutValue }}"
                   min="1" max="120" step="1">
            @error('translation_timeout') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Retry Attempts</label>
            <input type="number"
                   name="translation_retries"
                   class="form-control @error('translation_retries') is-invalid @enderror"
                   value="{{ $translationRetriesValue }}"
                   min="1" max="10" step="1">
            @error('translation_retries') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Backfill Chunk Size</label>
            <input type="number"
                   name="translation_chunk_size"
                   class="form-control @error('translation_chunk_size') is-invalid @enderror"
                   value="{{ $translationChunkValue }}"
                   min="1" max="1000" step="1">
            <div class="form-text">Used by the translation backfill command for batch scans.</div>
            @error('translation_chunk_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <div class="small text-muted">
              DeepL credentials stay in <code>.env</code> via <code>DEEPL_API_KEY</code> and <code>DEEPL_API_URL</code>.
              Added languages become available in the storefront immediately, but full UI translation still depends on having language files for that locale.
            </div>
          </div>
        </div>
      </div>
    </div>

    <template id="localeRowTemplate">
      <tr data-locale-row>
        <td>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="default_locale" value="" data-locale-default-radio>
            <label class="form-check-label small">Default</label>
          </div>
        </td>
        <td>
          <input type="hidden" data-locale-enabled-hidden value="0">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" value="1" checked>
          </div>
        </td>
        <td><input type="text" class="form-control form-control-sm" placeholder="en" data-locale-code></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="English" data-locale-name></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="English" data-locale-native></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="en" data-locale-html></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="en_US" data-locale-og></td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-danger btn-sm" data-remove-locale-row>&times;</button>
        </td>
      </tr>
    </template>

<!-- ========== PAYMENT, CURRENCY & PAYOUTS ========== -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-semibold">Payment, Currency &amp; Payouts</div>

  <div class="card-body">
    <div class="row g-3">

      {{-- PayPal Client ID --}}
      <div class="col-md-6">
        <label class="form-label">PayPal&nbsp;Client&nbsp;ID</label>
        <input type="text"
               name="paypal_client_id"
               class="form-control @error('paypal_client_id') is-invalid @enderror"
               value="{{ old('paypal_client_id', $settings->paypal_client_id) }}">
        @error('paypal_client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Payment Gateways --}}
      @php
        $mpesaEnabled  = (bool) (int) old('payments_mpesa_enabled', $settings->payments_mpesa_enabled ?? (function_exists('setting') ? setting('payments_mpesa_enabled', 1) : 1));
        $paypalEnabled = (bool) (int) old('payments_paypal_enabled', $settings->payments_paypal_enabled ?? (function_exists('setting') ? setting('payments_paypal_enabled', 1) : 1));
        $stripeEnabled = (bool) (int) old('payments_stripe_enabled', $settings->payments_stripe_enabled ?? (function_exists('setting') ? setting('payments_stripe_enabled', 1) : 1));
        $paystackEnabled = (bool) (int) old('payments_paystack_enabled', $settings->payments_paystack_enabled ?? (function_exists('setting') ? setting('payments_paystack_enabled', 1) : 1));
        $defaultGateway = (string) old('payments_default_gateway', $settings->payments_default_gateway ?? (function_exists('setting') ? setting('payments_default_gateway', 'paypal') : 'paypal'));

        // Config presence (for admin visibility only)
        $paypalConfigured = !empty(config('services.paypal.client_id')) || !empty($settings->paypal_client_id) || (function_exists('setting') && !empty(setting('paypal_client_id')));
        $stripeConfigured = !empty(config('services.stripe.secret')) || (function_exists('setting') && !empty(setting('stripe_secret')));
        $paystackConfigured = !empty(config('services.paystack.secret')) || (function_exists('setting') && !empty(setting('paystack_secret')));
        $mpesaConfigured  = !empty(env('SAFARICOM_DARAJA_BASE_URL')) && !empty(env('SAFARICOM_SHORTCODE')) && !empty(env('SAFARICOM_PASSKEY'));
      @endphp
      <div class="col-12">
        <div class="border rounded p-3 bg-light">
          <div class="fw-semibold mb-2">Checkout Payment Gateways</div>
          @if($errors->has('payments_gateways'))
            <div class="text-danger small mb-2">{{ $errors->first('payments_gateways') }}</div>
          @endif
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="gw-mpesa" name="payments_mpesa_enabled" value="1" {{ $mpesaEnabled ? 'checked' : '' }}>
                <label class="form-check-label" for="gw-mpesa">Enable M-Pesa (STK)</label>
              </div>
              <div class="form-text">Configured: {{ $mpesaConfigured ? 'Yes' : 'No' }}</div>
            </div>
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="gw-paypal" name="payments_paypal_enabled" value="1" {{ $paypalEnabled ? 'checked' : '' }}>
                <label class="form-check-label" for="gw-paypal">Enable PayPal</label>
              </div>
              <div class="form-text">Configured: {{ $paypalConfigured ? 'Yes' : 'No' }}</div>
            </div>
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="gw-stripe" name="payments_stripe_enabled" value="1" {{ $stripeEnabled ? 'checked' : '' }}>
                <label class="form-check-label" for="gw-stripe">Enable Stripe</label>
              </div>
              <div class="form-text">Configured: {{ $stripeConfigured ? 'Yes' : 'No' }}</div>
            </div>
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="gw-paystack" name="payments_paystack_enabled" value="1" {{ $paystackEnabled ? 'checked' : '' }}>
                <label class="form-check-label" for="gw-paystack">Enable Paystack</label>
              </div>
              <div class="form-text">Configured: {{ $paystackConfigured ? 'Yes' : 'No' }}</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Default Gateway</label>
              <select name="payments_default_gateway" class="form-select @error('payments_default_gateway') is-invalid @enderror">
                <option value="paypal" {{ $defaultGateway === 'paypal' ? 'selected' : '' }}>PayPal</option>
                <option value="stripe" {{ $defaultGateway === 'stripe' ? 'selected' : '' }}>Stripe</option>
                <option value="paystack" {{ $defaultGateway === 'paystack' ? 'selected' : '' }}>Paystack</option>
                <option value="mpesa"  {{ $defaultGateway === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
              </select>
              @error('payments_default_gateway') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <div class="form-text">Controls the pre-selected method on Deposit/Pay Now screens.</div>
            </div>
            <div class="col-12">
              <div class="small text-muted">
                Paystack webhook health check:
                <a href="{{ url('/webhooks/paystack/health') }}" target="_blank" rel="noopener">
                  {{ url('/webhooks/paystack/health') }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Default Currency --}}
      <div class="col-md-4">
        <label class="form-label">Default&nbsp;Currency</label>
        <input type="text"
               name="default_currency"
               class="form-control @error('default_currency') is-invalid @enderror"
               value="{{ old('default_currency', $settings->default_currency) }}"
               placeholder="e.g. USD" required>
        @error('default_currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- PayPal Fee % --}}
      <div class="col-md-2">
        <label class="form-label">PayPal&nbsp;Fee&nbsp;%</label>
        <input type="number"
               name="paypal_transaction_fee_percent"
               class="form-control @error('paypal_transaction_fee_percent') is-invalid @enderror"
               value="{{ old('paypal_transaction_fee_percent', $settings->paypal_transaction_fee_percent) }}"
               step="0.0001" min="0" max="10"
               placeholder="0.0398" title="Enter as decimal e.g. 0.0398 for 3.98 %">
        @error('paypal_transaction_fee_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Release Fee % (applied when on-hold funds are released to available) --}}
      @php
        $releaseFee = function_exists('setting') ? setting('release_fee_percent', env('HOLD_RELEASE_FEE_PERCENT', 5.5)) : env('HOLD_RELEASE_FEE_PERCENT', 5.5);
      @endphp
      <div class="col-md-2">
        <label class="form-label">Release&nbsp;Fee&nbsp;%</label>
        <input type="number"
               name="release_fee_percent"
               class="form-control @error('release_fee_percent') is-invalid @enderror"
               value="{{ old('release_fee_percent', $settings->release_fee_percent ?? $releaseFee) }}"
               step="0.01" min="0" max="100"
               placeholder="5.5"
               title="Percent deducted from on-hold funds when released to available">
        <div class="form-text">Default: {{ env('HOLD_RELEASE_FEE_PERCENT', 5.5) }}% (overridden here if set).</div>
        @error('release_fee_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Payout Fee % (store as percent, e.g., 1.5) --}}
      <div class="col-md-2">
        <label class="form-label">Payout&nbsp;Fee&nbsp;%</label>
        <input type="number"
               name="fee_rate"
               class="form-control @error('fee_rate') is-invalid @enderror"
               value="{{ old('fee_rate', $settings->fee_rate) }}"
               step="0.01" min="0" max="100"
               placeholder="1.5" title="Enter as percent, e.g. 1.5 for 1.5%">
        @error('fee_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Minimum Payout Amount --}}
      <div class="col-md-4">
        <label class="form-label">Minimum&nbsp;Payout&nbsp;Amount</label>
        <input type="number"
               name="min_amount"
               class="form-control @error('min_amount') is-invalid @enderror"
               value="{{ old('min_amount', number_format($settings->min_amount,2,'.','')) }}"
               step="0.01" min="0">
        @error('min_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Auto-release Days --}}
      <div class="col-md-4">
        <label class="form-label">Auto-release&nbsp;Days</label>
        <input type="number"
               name="auto_release_days"
               class="form-control @error('auto_release_days') is-invalid @enderror"
               value="{{ old('auto_release_days', $settings->auto_release_days) }}"
               step="1" min="1" max="365" placeholder="3">
        <div class="form-text">Days after shipment before on-hold funds auto-release to seller.</div>
        @error('auto_release_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Payout Schedule --}}
      @php
        $payoutSchedule = old('payout_schedule', $settings->payout_schedule ?? (function_exists('setting') ? setting('payout_schedule', 'manual') : 'manual'));
        $payoutWeekday = (int) old('payout_weekday', $settings->payout_weekday ?? (function_exists('setting') ? setting('payout_weekday', 5) : 5));
        $payoutMonthDay = (int) old('payout_month_day', $settings->payout_month_day ?? (function_exists('setting') ? setting('payout_month_day', 15) : 15));
        $payoutAutoApprove = (bool) (int) old('payout_auto_approve', $settings->payout_auto_approve ?? (function_exists('setting') ? setting('payout_auto_approve', 0) : 0));
        $payoutAutoDisburse = (bool) (int) old('payout_auto_disburse', $settings->payout_auto_disburse ?? (function_exists('setting') ? setting('payout_auto_disburse', 0) : 0));
        $weekdays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
      @endphp
      <div class="col-md-4">
        <label class="form-label">Payout Schedule</label>
        <select name="payout_schedule" class="form-select @error('payout_schedule') is-invalid @enderror">
          <option value="manual" {{ $payoutSchedule === 'manual' ? 'selected' : '' }}>Manual</option>
          <option value="weekly" {{ $payoutSchedule === 'weekly' ? 'selected' : '' }}>Weekly</option>
          <option value="biweekly" {{ $payoutSchedule === 'biweekly' ? 'selected' : '' }}>Bi-weekly</option>
          <option value="monthly" {{ $payoutSchedule === 'monthly' ? 'selected' : '' }}>Monthly</option>
        </select>
        <div class="form-text">Controls when pending payouts are processed automatically.</div>
        @error('payout_schedule') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-4">
        <label class="form-label">Payout Weekday</label>
        <select name="payout_weekday" class="form-select @error('payout_weekday') is-invalid @enderror">
          @foreach($weekdays as $idx => $label)
            <option value="{{ $idx }}" {{ $payoutWeekday === $idx ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <div class="form-text">Used for weekly/bi-weekly schedules.</div>
        @error('payout_weekday') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-4">
        <label class="form-label">Payout Day of Month</label>
        <input type="number"
               name="payout_month_day"
               class="form-control @error('payout_month_day') is-invalid @enderror"
               value="{{ $payoutMonthDay }}"
               step="1" min="1" max="28" placeholder="15">
        <div class="form-text">Used for monthly schedules (1-28).</div>
        @error('payout_month_day') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-4">
        <label class="form-label">Auto-approve Payouts</label>
        <input type="hidden" name="payout_auto_approve" value="0">
        <div class="form-check form-switch mt-1">
          <input class="form-check-input" type="checkbox" role="switch" id="payout-auto-approve"
                 name="payout_auto_approve" value="1" {{ $payoutAutoApprove ? 'checked' : '' }}>
          <label class="form-check-label" for="payout-auto-approve">Enabled</label>
        </div>
        <div class="form-text">Automatically approve eligible payout requests on schedule.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Auto-disburse Payouts</label>
        <input type="hidden" name="payout_auto_disburse" value="0">
        <div class="form-check form-switch mt-1">
          <input class="form-check-input" type="checkbox" role="switch" id="payout-auto-disburse"
                 name="payout_auto_disburse" value="1" {{ $payoutAutoDisburse ? 'checked' : '' }}>
          <label class="form-check-label" for="payout-auto-disburse">Enabled</label>
        </div>
        <div class="form-text">Auto-send for supported methods (PayPal/M-Pesa/Wise).</div>
      </div>

      {{-- Subscription Grace Period (days) --}}
      @php
        $graceDaysSetting = function_exists('setting') ? setting('subscription_grace_days', 5) : 5;
        $trialEnabledSetting = function_exists('setting') ? setting('subscription_trial_enabled', 1) : 1;
        $trialDaysSetting = function_exists('setting') ? setting('subscription_trial_days', 30) : 30;
        $homeListingsCacheTtlSetting = function_exists('setting') ? setting('home_listings_cache_ttl_minutes', 10) : 10;
        $trialEnabledValue = (bool) (int) old('subscription_trial_enabled', $trialEnabledSetting);
        $sellerAutoApproveSetting = function_exists('setting_bool')
          ? setting_bool('seller_signup_auto_approve', true)
          : ((bool) (int) (function_exists('setting') ? setting('seller_signup_auto_approve', 1) : 1));
        $sellerAutoApproveValue = (bool) (int) old('seller_signup_auto_approve', $sellerAutoApproveSetting ? 1 : 0);
        $sellerLogoRequiredSetting = function_exists('setting_bool')
          ? setting_bool('seller_signup_require_logo', false)
          : ((bool) (int) (function_exists('setting') ? setting('seller_signup_require_logo', 0) : 0));
        $sellerLogoRequiredValue = (bool) (int) old('seller_signup_require_logo', $sellerLogoRequiredSetting ? 1 : 0);
      @endphp
      <div class="col-md-4">
        <label class="form-label">Subscription Grace Period (days)</label>
        <input type="number"
               name="subscription_grace_days"
               class="form-control @error('subscription_grace_days') is-invalid @enderror"
               value="{{ old('subscription_grace_days', $graceDaysSetting) }}"
               step="1" min="0" max="60" placeholder="5">
        <div class="form-text">Number of days after end date that a shop remains active.</div>
        @error('subscription_grace_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-4">
        <label class="form-label">Enable Free Seller Trial</label>
        <input type="hidden" name="subscription_trial_enabled" value="0">
        <div class="form-check form-switch mt-1">
          <input class="form-check-input" type="checkbox" role="switch" id="subscription-trial-enabled"
                 name="subscription_trial_enabled" value="1" {{ $trialEnabledValue ? 'checked' : '' }}>
          <label class="form-check-label" for="subscription-trial-enabled">Enabled</label>
        </div>
        <div class="form-text">Applies to new sellers only.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Auto-approve Seller Signups</label>
        <input type="hidden" name="seller_signup_auto_approve" value="0">
        <div class="form-check form-switch mt-1">
          <input class="form-check-input" type="checkbox" role="switch" id="seller-signup-auto-approve"
                 name="seller_signup_auto_approve" value="1" {{ $sellerAutoApproveValue ? 'checked' : '' }}>
          <label class="form-check-label" for="seller-signup-auto-approve">Enabled</label>
        </div>
        <div class="form-text">When enabled, new sellers are activated immediately without manual admin approval.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Require Shop Logo On Signup</label>
        <input type="hidden" name="seller_signup_require_logo" value="0">
        <div class="form-check form-switch mt-1">
          <input class="form-check-input" type="checkbox" role="switch" id="seller-signup-require-logo"
                 name="seller_signup_require_logo" value="1" {{ $sellerLogoRequiredValue ? 'checked' : '' }}>
          <label class="form-check-label" for="seller-signup-require-logo">Enabled</label>
        </div>
        <div class="form-text">When disabled, sellers can open a shop first and upload a logo later.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Trial Length (days)</label>
        <input type="number"
               name="subscription_trial_days"
               class="form-control @error('subscription_trial_days') is-invalid @enderror"
               value="{{ old('subscription_trial_days', $trialDaysSetting) }}"
               step="1" min="1" max="365" placeholder="30">
        <div class="form-text">Used when a new seller starts a trial.</div>
        @error('subscription_trial_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-4">
        <label class="form-label">Homepage Listings Cache TTL (minutes)</label>
        <input type="number"
               name="home_listings_cache_ttl_minutes"
               class="form-control @error('home_listings_cache_ttl_minutes') is-invalid @enderror"
               value="{{ old('home_listings_cache_ttl_minutes', $homeListingsCacheTtlSetting) }}"
               step="1" min="1" max="1440" placeholder="10">
        <div class="form-text">Controls how often homepage listing pools are refreshed.</div>
        @error('home_listings_cache_ttl_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

    </div>
  </div>
  </div>

  <!-- ========== SHIPPING DEFAULTS ========== -->
  <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold">Shipping Defaults</div>
      <div class="card-body">
        @php
          $couriersFromSettings = '';
          try {
            $arr = json_decode($settings->couriers_json ?? '[]', true);
            if (is_array($arr) && !empty($arr)) {
              $couriersFromSettings = implode("\n", $arr);
            }
          } catch (\Throwable $e) {}
          if ($couriersFromSettings === '') {
            $couriersFromSettings = implode("\n", couriers_list());
          }
        @endphp
        <label class="form-label">Default Couriers (one per line)</label>
        <textarea name="couriers" rows="6" class="form-control" placeholder="e.g. DHL\nFedEx\nUPS">{{ old('couriers', $couriersFromSettings) }}</textarea>
        <div class="form-text">Shown in "Service" selects. Sellers can still choose Manual/Other to type a custom courier.</div>
  </div>

  <!-- ========== PRODUCT DUPLICATION ========== -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-semibold">Product Duplication</div>
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">SKU Strategy</label>
          @php
            $dupStrategy = function_exists('setting') ? setting('duplicate_sku_strategy', env('DUPLICATE_SKU_STRATEGY','append')) : env('DUPLICATE_SKU_STRATEGY','append');
          @endphp
          <select name="duplicate_sku_strategy" class="form-select">
            @foreach(['append'=>'Append suffix','clear'=>'Clear on duplicate','keep'=>'Keep as-is'] as $k=>$label)
              <option value="{{ $k }}" {{ old('duplicate_sku_strategy', $dupStrategy) === $k ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <div class="form-text">How variant SKUs are generated on duplicates.</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">SKU Suffix</label>
          @php $dupSuffix = function_exists('setting') ? setting('duplicate_sku_suffix', env('DUPLICATE_SKU_SUFFIX','DUP')) : env('DUPLICATE_SKU_SUFFIX','DUP'); @endphp
          <input type="text" name="duplicate_sku_suffix" class="form-control" value="{{ old('duplicate_sku_suffix', $dupSuffix) }}">
          <div class="form-text">Used when strategy is Append (e.g., DUP).</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Random Length</label>
          @php $dupLen = (int) (function_exists('setting') ? setting('duplicate_sku_random_len', env('DUPLICATE_SKU_RANDOM_LEN',4)) : env('DUPLICATE_SKU_RANDOM_LEN',4)); @endphp
          <input type="number" name="duplicate_sku_random_len" class="form-control" step="1" min="1" max="12" value="{{ old('duplicate_sku_random_len', $dupLen) }}">
          <div class="form-text">Length of trailing random token (1–12).</div>
        </div>
      </div>
    </div>
  </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-end">
      <button type="submit" class="btn btn-primary px-4">Save Changes</button>
      <a href="{{ url()->previous() }}" class="btn btn-outline-secondary ms-2">Cancel</a>
    </div>
  </form>

  <!-- ========== PRODUCT IMAGE OPTIMIZER ========== -->
  <div class="card shadow-sm mt-4">
    <div class="card-header bg-light fw-semibold">Product Image Optimizer</div>
    <div class="card-body">
      <p class="text-muted mb-3">
        Resize and recompress all locally stored product images to reduce page weight and improve loading speed.
      </p>

      @php
        $optimizerStatus = is_array($imageOptimizerStatus ?? null) ? $imageOptimizerStatus : ['state' => 'idle'];
        $optimizerState = strtolower((string) ($optimizerStatus['state'] ?? 'idle'));
        $optimizerSummary = is_array($optimizerStatus['summary'] ?? null) ? $optimizerStatus['summary'] : null;
        $optimizerWarnings = array_values(array_filter(
          is_array($optimizerStatus['warnings'] ?? null) ? $optimizerStatus['warnings'] : [],
          fn ($warning) => is_string($warning) && trim($warning) !== ''
        ));

        $stateBadgeClass = match ($optimizerState) {
          'queued' => 'bg-secondary',
          'running' => 'bg-primary',
          'cancel_requested' => 'bg-warning text-dark',
          'cancelled' => 'bg-dark',
          'completed' => 'bg-success',
          'failed' => 'bg-danger',
          default => 'bg-light text-dark',
        };
        $stateLabel = match ($optimizerState) {
          'queued' => 'Queued',
          'running' => 'Running',
          'cancel_requested' => 'Cancel Requested',
          'cancelled' => 'Cancelled',
          'completed' => 'Completed',
          'failed' => 'Failed',
          default => 'Idle',
        };
        $statusMessage = (string) ($optimizerStatus['message'] ?? 'No optimization has been queued yet.');
      @endphp

      <div id="imageOptimizerStatusBox" class="border rounded-3 p-3 bg-white" style="color:#1f2937;">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
          <span class="fw-semibold text-dark">Background Job Status</span>
          <span id="imageOptimizerStateBadge" class="badge {{ $stateBadgeClass }}">{{ $stateLabel }}</span>
          <small id="imageOptimizerUpdatedAt" class="text-secondary">
            @if(!empty($optimizerStatus['updated_at']))
              Updated {{ \Illuminate\Support\Carbon::parse($optimizerStatus['updated_at'])->diffForHumans() }}
            @endif
          </small>
        </div>
        <div id="imageOptimizerMessage" class="small text-dark">{{ $statusMessage }}</div>
        <div id="imageOptimizerWarnings" class="small mt-2" style="color:#b45309;">
          @if($optimizerWarnings)
            <ul class="mb-0 ps-3">
              @foreach($optimizerWarnings as $warning)
                <li>{{ $warning }}</li>
              @endforeach
            </ul>
          @endif
        </div>
        <div id="imageOptimizerMeta" class="small text-secondary mt-2">
          @if(!empty($optimizerStatus['run_id']))
            Run ID: <code>{{ $optimizerStatus['run_id'] }}</code>
          @endif
        </div>
        <div id="imageOptimizerSummary" class="small mt-2 text-dark">
          @if($optimizerSummary)
            Scanned: <strong>{{ (int) ($optimizerSummary['scanned'] ?? 0) }}</strong> |
            Unique files: <strong>{{ (int) ($optimizerSummary['unique_paths'] ?? 0) }}</strong> |
            Optimized: <strong>{{ (int) ($optimizerSummary['optimized'] ?? 0) }}</strong> |
            Resized: <strong>{{ (int) ($optimizerSummary['resized'] ?? 0) }}</strong> |
            Orientation corrected: <strong>{{ (int) ($optimizerSummary['orientation_corrected'] ?? 0) }}</strong> |
            EXIF-guard skipped: <strong>{{ (int) ($optimizerSummary['exif_guard_skipped'] ?? 0) }}</strong> |
            Skipped: <strong>{{ (int) ($optimizerSummary['skipped'] ?? 0) }}</strong> |
            Missing: <strong>{{ (int) ($optimizerSummary['missing'] ?? 0) }}</strong> |
            Errors: <strong>{{ (int) ($optimizerSummary['errors'] ?? 0) }}</strong>
            <br>
            Saved: <strong>{{ number_format(((int) ($optimizerSummary['saved_bytes'] ?? 0)) / 1048576, 2) }} MB</strong>
          @endif
        </div>
      </div>

      <form id="imageOptimizerForm"
            action="{{ route('admin.settings.optimize-product-images', $settings->id) }}"
            method="POST"
            class="row g-3 align-items-end"
            data-status-url="{{ route('admin.settings.optimize-product-images.status', $settings->id) }}"
            data-cancel-url="{{ route('admin.settings.optimize-product-images.cancel', $settings->id) }}">
        @csrf
        <div class="col-md-3">
          <label class="form-label">Max Width (px)</label>
          <input type="number" name="optimizer_max_width" class="form-control @error('optimizer_max_width') is-invalid @enderror"
                 value="{{ old('optimizer_max_width', 1600) }}" min="320" max="4096" step="1">
          @error('optimizer_max_width') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Max Height (px)</label>
          <input type="number" name="optimizer_max_height" class="form-control @error('optimizer_max_height') is-invalid @enderror"
                 value="{{ old('optimizer_max_height', 1600) }}" min="320" max="4096" step="1">
          @error('optimizer_max_height') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Quality (JPEG/WEBP)</label>
          <input type="number" name="optimizer_quality" class="form-control @error('optimizer_quality') is-invalid @enderror"
                 value="{{ old('optimizer_quality', 82) }}" min="40" max="95" step="1">
          @error('optimizer_quality') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
          <div class="d-grid gap-2">
            <button id="imageOptimizerSubmit" type="submit" class="btn btn-warning w-100">
              Optimize All Product Images
            </button>
            <button id="imageOptimizerCancel" type="button" class="btn btn-outline-danger w-100">
              Cancel Current Optimization
            </button>
          </div>
        </div>
      </form>
      <div class="form-text mt-2">
        Processes product media, featured images, and legacy image paths on the public disk.
        This now runs in the background via queue.
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', evt => {
      if (!form.checkValidity()) {
        evt.preventDefault();
        evt.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
<script>
(() => {
  'use strict';

  const body = document.getElementById('localeRowsBody');
  const addButton = document.getElementById('addLocaleRow');
  const template = document.getElementById('localeRowTemplate');

  if (!body || !addButton || !template) return;

  let nextIndex = body.querySelectorAll('[data-locale-row]').length;

  function sanitizeLocaleCode(value) {
    return String(value || '')
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9_-]/g, '');
  }

  function updateRowBindings(row, index) {
    const codeInput = row.querySelector('[data-locale-code]');
    const defaultRadio = row.querySelector('[data-locale-default-radio]');
    const enabledHidden = row.querySelector('[data-locale-enabled-hidden]');
    const enabledCheckbox = row.querySelector('input[type="checkbox"][role="switch"]');
    const nameInput = row.querySelector('[data-locale-name]');
    const nativeInput = row.querySelector('[data-locale-native]');
    const htmlInput = row.querySelector('[data-locale-html]');
    const ogInput = row.querySelector('[data-locale-og]');

    if (codeInput) codeInput.name = `locale_rows[${index}][code]`;
    if (nameInput) nameInput.name = `locale_rows[${index}][name]`;
    if (nativeInput) nativeInput.name = `locale_rows[${index}][native]`;
    if (htmlInput) htmlInput.name = `locale_rows[${index}][html]`;
    if (ogInput) ogInput.name = `locale_rows[${index}][og]`;
    if (enabledHidden) enabledHidden.name = `locale_rows[${index}][enabled]`;
    if (enabledCheckbox) enabledCheckbox.name = `locale_rows[${index}][enabled]`;

    const syncDefaultValue = () => {
      if (!defaultRadio || !codeInput) return;
      defaultRadio.value = sanitizeLocaleCode(codeInput.value);
    };

    if (codeInput) {
      codeInput.addEventListener('input', syncDefaultValue);
      syncDefaultValue();
    }
  }

  function ensureDefaultSelection() {
    const radios = Array.from(body.querySelectorAll('[data-locale-default-radio]'));
    if (radios.some(radio => radio.checked)) return;

    const first = radios[0];
    if (first) first.checked = true;
  }

  function addRow(values = {}) {
    const fragment = template.content.cloneNode(true);
    const row = fragment.querySelector('[data-locale-row]');
    const codeInput = row.querySelector('[data-locale-code]');
    const nameInput = row.querySelector('[data-locale-name]');
    const nativeInput = row.querySelector('[data-locale-native]');
    const htmlInput = row.querySelector('[data-locale-html]');
    const ogInput = row.querySelector('[data-locale-og]');
    const enabledCheckbox = row.querySelector('input[type="checkbox"][role="switch"]');
    const defaultRadio = row.querySelector('[data-locale-default-radio]');

    if (codeInput) codeInput.value = values.code || '';
    if (nameInput) nameInput.value = values.name || '';
    if (nativeInput) nativeInput.value = values.native || '';
    if (htmlInput) htmlInput.value = values.html || '';
    if (ogInput) ogInput.value = values.og || '';
    if (enabledCheckbox) enabledCheckbox.checked = values.enabled !== false;
    if (defaultRadio) defaultRadio.checked = !!values.default;

    updateRowBindings(row, nextIndex);
    nextIndex += 1;
    body.appendChild(fragment);
    ensureDefaultSelection();
  }

  body.querySelectorAll('[data-locale-row]').forEach((row, index) => {
    updateRowBindings(row, index);
  });
  nextIndex = body.querySelectorAll('[data-locale-row]').length;
  ensureDefaultSelection();

  addButton.addEventListener('click', () => {
    addRow({ enabled: true });
  });

  body.addEventListener('click', (event) => {
    const button = event.target.closest('[data-remove-locale-row]');
    if (!button) return;

    const rows = body.querySelectorAll('[data-locale-row]');
    if (rows.length <= 1) {
      window.alert('At least one language row is required.');
      return;
    }

    const row = button.closest('[data-locale-row]');
    const wasDefault = !!row?.querySelector('[data-locale-default-radio]')?.checked;
    row?.remove();

    if (wasDefault) {
      ensureDefaultSelection();
    }
  });
})();
</script>
<script>
(() => {
  'use strict';

  const form = document.getElementById('imageOptimizerForm');
  if (!form) return;

  const submitButton = document.getElementById('imageOptimizerSubmit');
  const cancelButton = document.getElementById('imageOptimizerCancel');
  const stateBadge = document.getElementById('imageOptimizerStateBadge');
  const messageBox = document.getElementById('imageOptimizerMessage');
  const warningsBox = document.getElementById('imageOptimizerWarnings');
  const metaBox = document.getElementById('imageOptimizerMeta');
  const summaryBox = document.getElementById('imageOptimizerSummary');
  const updatedAtBox = document.getElementById('imageOptimizerUpdatedAt');
  const statusUrl = form.getAttribute('data-status-url');
  const cancelUrl = form.getAttribute('data-cancel-url');
  const csrf = form.querySelector('input[name="_token"]')?.value || '';

  let pollingTimer = null;
  let isQueueing = false;
  let isCancelling = false;
  let currentState = 'idle';
  const activeStates = new Set(['queued', 'running', 'cancel_requested']);

  const stateMap = {
    queued: { cls: 'bg-secondary', label: 'Queued' },
    running: { cls: 'bg-primary', label: 'Running' },
    cancel_requested: { cls: 'bg-warning text-dark', label: 'Cancel Requested' },
    cancelled: { cls: 'bg-dark', label: 'Cancelled' },
    completed: { cls: 'bg-success', label: 'Completed' },
    failed: { cls: 'bg-danger', label: 'Failed' },
    idle: { cls: 'bg-light text-dark', label: 'Idle' },
  };

  function normalizeState(value) {
    return String(value || 'idle').trim().toLowerCase().replace(/\s+/g, '_');
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function summarize(summary) {
    if (!summary || typeof summary !== 'object') return '';
    const savedBytes = Number(summary.saved_bytes || 0);
    const savedMb = (savedBytes / 1048576).toFixed(2);

    return `
      Scanned: <strong>${Number(summary.scanned || 0).toLocaleString()}</strong> |
      Unique files: <strong>${Number(summary.unique_paths || 0).toLocaleString()}</strong> |
      Optimized: <strong>${Number(summary.optimized || 0).toLocaleString()}</strong> |
      Resized: <strong>${Number(summary.resized || 0).toLocaleString()}</strong> |
      Orientation corrected: <strong>${Number(summary.orientation_corrected || 0).toLocaleString()}</strong> |
      EXIF-guard skipped: <strong>${Number(summary.exif_guard_skipped || 0).toLocaleString()}</strong> |
      Skipped: <strong>${Number(summary.skipped || 0).toLocaleString()}</strong> |
      Missing: <strong>${Number(summary.missing || 0).toLocaleString()}</strong> |
      Errors: <strong>${Number(summary.errors || 0).toLocaleString()}</strong>
      <br>
      Saved: <strong>${savedMb} MB</strong>
    `;
  }

  function renderWarnings(warnings) {
    if (!warningsBox) return;

    if (!Array.isArray(warnings) || warnings.length === 0) {
      warningsBox.innerHTML = '';
      return;
    }

    const items = warnings
      .map(item => String(item || '').trim())
      .filter(Boolean)
      .map(item => `<li>${escapeHtml(item)}</li>`)
      .join('');

    warningsBox.innerHTML = items ? `<ul class="mb-0 ps-3">${items}</ul>` : '';
  }

  function syncButtons() {
    const hasActiveRun = activeStates.has(currentState);

    if (submitButton) {
      submitButton.disabled = isQueueing || isCancelling || hasActiveRun;
      submitButton.textContent = isQueueing ? 'Queuing...' : 'Optimize All Product Images';
    }

    if (cancelButton) {
      cancelButton.disabled = isQueueing || isCancelling || !hasActiveRun;
      cancelButton.textContent = isCancelling ? 'Cancelling...' : 'Cancel Current Optimization';
    }
  }

  function renderStatus(status) {
    currentState = normalizeState(status?.state || 'idle');
    const config = stateMap[currentState] || stateMap.idle;

    stateBadge.className = `badge ${config.cls}`;
    stateBadge.textContent = config.label;

    messageBox.textContent = String(status?.message || '');
    renderWarnings(status?.warnings);

    if (status?.run_id) {
      metaBox.innerHTML = `Run ID: <code>${escapeHtml(status.run_id)}</code>`;
    } else {
      metaBox.innerHTML = '';
    }

    if (status?.updated_at) {
      updatedAtBox.textContent = `Updated ${new Date(status.updated_at).toLocaleString()}`;
    } else {
      updatedAtBox.textContent = '';
    }

    summaryBox.innerHTML = summarize(status?.summary);
    syncButtons();
  }

  function stopPolling() {
    if (pollingTimer) {
      clearInterval(pollingTimer);
      pollingTimer = null;
    }
  }

  async function pollStatus() {
    if (!statusUrl) return;
    try {
      const response = await fetch(statusUrl, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      });

      if (!response.ok) return;

      const data = await response.json();
      const status = data?.status || {};
      renderStatus(status);

      const state = normalizeState(status?.state || '');
      if (state === 'completed' || state === 'failed' || state === 'idle' || state === 'cancelled') {
        stopPolling();
      }
    } catch (_) {
      // Ignore intermittent network errors and keep polling.
    }
  }

  function startPolling() {
    stopPolling();
    pollStatus();
    pollingTimer = setInterval(pollStatus, 3000);
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const confirmed = window.confirm('Queue image optimization in background now? You can leave this page while it runs.');
    if (!confirmed) return;

    isQueueing = true;
    syncButtons();

    try {
      const formData = new FormData(form);
      const response = await fetch(form.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf,
        },
        body: formData,
        credentials: 'same-origin',
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        const fallback = response.status === 409
          ? 'An optimization run is already in progress.'
          : 'Unable to queue optimization.';
        messageBox.textContent = data?.message || fallback;
        if (data?.status) {
          renderStatus(data.status);
          if (activeStates.has(normalizeState(data.status.state || ''))) {
            startPolling();
          }
        }
        return;
      }

      if (data?.status) {
        renderStatus(data.status);
      }
      startPolling();
    } catch (error) {
      messageBox.textContent = 'Network error while queuing optimization.';
    } finally {
      isQueueing = false;
      syncButtons();
    }
  });

  if (cancelButton) {
    cancelButton.addEventListener('click', async () => {
      if (!cancelUrl) return;

      const confirmed = window.confirm('Cancel the currently running optimization job?');
      if (!confirmed) return;

      isCancelling = true;
      syncButtons();

      try {
        const response = await fetch(cancelUrl, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf,
          },
          credentials: 'same-origin',
        });

        const data = await response.json().catch(() => ({}));

        if (data?.status) {
          renderStatus(data.status);
        }

        if (!response.ok) {
          messageBox.textContent = data?.message || 'Unable to cancel optimization.';
          return;
        }

        startPolling();
      } catch (_) {
        messageBox.textContent = 'Network error while requesting cancellation.';
      } finally {
        isCancelling = false;
        syncButtons();
      }
    });
  }

  currentState = normalizeState(stateBadge.textContent || 'idle');
  syncButtons();
  if (activeStates.has(currentState)) {
    startPolling();
  }
})();
</script>
@endpush
@endsection
