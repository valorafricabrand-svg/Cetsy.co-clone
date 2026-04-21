@extends('theme.'.theme().'.layouts.app')
@section('title', $product->name . ' | Edit Settings')

@section('main')
@php
  $current = \Illuminate\Support\Facades\Route::currentRouteName();
@endphp

<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Listing Settings</h1>
              <p class="mt-1 text-sm text-slate-500">Control status, renewal, visibility, and SEO metadata for this listing.</p>
            </div>
            <a href="{{ route('products.show', $product) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">
              <i class="fas fa-arrow-left mr-2"></i> Back to Listing
            </a>
          </div>
        </div>

        @include('products.partials.edit-tabs', ['product' => $product, 'current' => $current])

        @if ($errors->any())
          <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <strong>Please fix the following errors:</strong>
            <ul class="mt-2 list-disc space-y-1 pl-5">
              @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
          </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
          <form action="{{ route('products.settings.update', $product) }}" method="POST">
            @csrf @method('PATCH')

            <div class="grid grid-cols-12 gap-3">
              <div class="col-span-12 md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">Listing Status</label>
                @php
                  $rawStatus = (int) old('is_active', $product->is_active);
                  $hasPaid = !empty($product->listing_paid_at);
                  $listingFee = max(0, (float) ($product->category?->listing_fee ?? 0));
                  $hasBillingHistory = $hasPaid || !empty($product->next_due_date);
                  try {
                    $nextDueDate = $product->next_due_date ? \Carbon\Carbon::parse($product->next_due_date) : null;
                  } catch (\Throwable $e) {
                    $nextDueDate = null;
                  }
                  $hasFeatured = !empty($product->featured_image) || $product->media()->exists();
                  $eligibleToActivate = $hasPaid && (empty($nextDueDate) || $nextDueDate->isFuture());
                  $inactiveStatus = $hasBillingHistory ? 2 : 0;
                  $inactiveLabel = $inactiveStatus === 2 ? 'Paused' : 'Pending';
                  $isActive = $rawStatus === 1 ? 1 : $inactiveStatus;
                  $canToggleOn = $eligibleToActivate && $hasFeatured;
                @endphp
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                  <input class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" type="checkbox" id="statusToggle"
                         {{ $isActive===1 ? 'checked' : '' }} {{ $canToggleOn || $isActive===1 ? '' : 'disabled' }}>
                  <span id="statusToggleLabel">{{ $isActive===1 ? 'Active' : $inactiveLabel }}</span>
                </label>
                <input type="hidden" name="is_active" id="is_active" value="{{ $isActive===1 ? 1 : $inactiveStatus }}" data-inactive-status="{{ $inactiveStatus }}" data-inactive-label="{{ $inactiveLabel }}">
                <div class="mt-1 text-xs text-slate-500">
                  @if(!$hasFeatured)
                    Add a featured image to enable activation.
                  @endif
                  @if($isActive !== 1 && !$eligibleToActivate)
                    @if(empty($product->listing_paid_at))
                      <div class="mt-1">
                        <span>{{ $listingFee > 0 ? 'Pay the listing fee to activate.' : 'Activate the free listing plan to go live.' }}</span>
                        <button type="button" class="ml-1 text-emerald-700 underline decoration-emerald-500 underline-offset-2 hover:text-emerald-600" onclick="document.getElementById('payFeeForm-{{ $product->id }}').submit();">
                          {{ $listingFee > 0 ? 'Pay to activate' : 'Activate listing' }}
                        </button>
                      </div>
                    @else
                      <div class="mt-1">Renew your listing to activate.</div>
                    @endif
                  @elseif($isActive !== 1 && $inactiveStatus === 2)
                    <div class="mt-1">This listing is paused. Toggle it back on when you're ready.</div>
                  @endif
                </div>
                @error('is_active') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
              </div>

              <div class="col-span-12 md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">Renewal Type</label>
                <select name="renewal_type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('renewal_type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
                  <option value="automatic" {{ old('renewal_type',$product->renewal_type)==='automatic' ? 'selected' : '' }}>Automatic</option>
                  <option value="manual" {{ old('renewal_type',$product->renewal_type)==='manual' ? 'selected' : '' }}>Manual</option>
                </select>
                @error('renewal_type') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
              </div>

              <div class="col-span-12 md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">Visibility</label>
                @php $visibility = old('visibility', $product->visibility ?? 'Public'); @endphp
                <select name="visibility" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('visibility') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                  <option value="Public" {{ $visibility==='Public' ? 'selected' : '' }}>Public</option>
                  <option value="Private" {{ $visibility==='Private' ? 'selected' : '' }}>Private</option>
                  <option value="Unlisted" {{ $visibility==='Unlisted' ? 'selected' : '' }}>Unlisted</option>
                </select>
                @error('visibility') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="mt-1 grid grid-cols-12 gap-3">
              <div class="col-span-12 md:col-span-6">
                <label class="mb-1 block text-sm font-medium text-slate-700">Slug (optional)</label>
                <input type="text" name="slug" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('slug') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                       value="{{ old('slug', $product->slug) }}" placeholder="custom-url-slug">
                @error('slug') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
              </div>
              <div class="col-span-12 md:col-span-6">
                <label class="mb-1 block text-sm font-medium text-slate-700">Tags (comma-separated, optional)</label>
                <input type="text" name="tags" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('tags') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                       value="{{ old('tags', $product->tags ?? '') }}" placeholder="electronics, phone, samsung">
                @error('tags') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
              <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto"><i class="fas fa-save mr-1"></i> Save</button>
              <a href="{{ route('products.show', $product) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">Cancel</a>
            </div>
          </form>

          <form id="payFeeForm-{{ $product->id }}" class="hidden" method="POST" action="{{ route('products.pay-fee', $product) }}">
            @csrf
            @php
              $freqHidden = (int) ($product->category?->listing_frequency ?? 4);
              $planKeyHidden = $freqHidden === 1 ? 'monthly' : '4months';
            @endphp
            <input type="hidden" name="plan" value="{{ $planKeyHidden }}">
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  var cb = document.getElementById('statusToggle');
  var hidden = document.getElementById('is_active');
  var label = document.getElementById('statusToggleLabel');
  if(cb && hidden){
    var inactiveStatus = hidden.dataset.inactiveStatus || '0';
    var inactiveLabel = hidden.dataset.inactiveLabel || 'Pending';
    cb.addEventListener('change', function(){
      hidden.value = cb.checked ? 1 : inactiveStatus;
      if (label) label.textContent = cb.checked ? 'Active' : inactiveLabel;
    });
  }
});
</script>
@endpush
