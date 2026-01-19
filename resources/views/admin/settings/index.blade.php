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

      {{-- Subscription Grace Period (days) --}}
      @php
        $graceDaysSetting = function_exists('setting') ? setting('subscription_grace_days', 5) : 5;
        $trialEnabledSetting = function_exists('setting') ? setting('subscription_trial_enabled', 1) : 1;
        $trialDaysSetting = function_exists('setting') ? setting('subscription_trial_days', 30) : 30;
        $trialEnabledValue = (bool) (int) old('subscription_trial_enabled', $trialEnabledSetting);
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
        <label class="form-label">Trial Length (days)</label>
        <input type="number"
               name="subscription_trial_days"
               class="form-control @error('subscription_trial_days') is-invalid @enderror"
               value="{{ old('subscription_trial_days', $trialDaysSetting) }}"
               step="1" min="1" max="365" placeholder="30">
        <div class="form-text">Used when a new seller starts a trial.</div>
        @error('subscription_trial_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
@endpush
@endsection
