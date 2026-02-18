@extends('theme.'.theme().'.layouts.app')
@section('title', $product->name . ' | Edit Settings')

@push('styles')
<style>
  .page-header-sticky{position:sticky;top:0;z-index:1020;background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
  .tab-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;white-space:nowrap}
  .tab-scroll .nav-link{border-radius:999px}
  .rounded-4{border-radius:1rem!important}
</style>
@endpush

@section('main')
@php $current = \Illuminate\Support\Facades\Route::currentRouteName(); @endphp

<div class="content">
  {{-- Header tabs --}}
  <div class="page-header-sticky">
    <div class="mx-auto w-full px-4 sm:px-6 px-0">
      <div class="tab-scroll px-2 py-2">
        <ul class="nav nav-pills gap-2 flex-nowrap">
          <li class=""><a class="nav-link {{ $current === 'products.show' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.show', $product) }}"><i class="fa-regular fa-circle-question mr-1"></i> About</a></li>
          <li class=""><a class="nav-link {{ $current === 'products.pricing' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.pricing', $product) }}"><i class="fa-solid fa-tags mr-1"></i> Price & Inventory</a></li>
          <li class=""><a class="nav-link {{ $current === 'products.variations' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.variations', $product) }}"><i class="fa-solid fa-layer-group mr-1"></i> Variations</a></li>
          <li class=""><a class="nav-link {{ $current === 'products.details' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.details', $product) }}"><i class="fa-regular fa-rectangle-list mr-1"></i> Details</a></li>
          <li class=""><a class="nav-link {{ $current === 'products.shipping' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.shipping', $product) }}"><i class="fa-solid fa-truck mr-1"></i> Shipping</a></li>
          <li class=""><a class="nav-link {{ $current === 'products.settings' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.settings', $product) }}"><i class="fa-solid fa-gear mr-1"></i> Settings</a></li>
        </ul>
      </div>
    </div>
  </div>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 mt-3">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 pl-3">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="flex justify-between items-center mt-3 mb-3">
    <h2 class="mb-0">{{ $product->name }} â€” Edit Settings</h2>
    <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-900 text-slate-900 hover:bg-slate-100 px-3 py-1.5 text-xs"><i class="fas fa-arrow-left mr-1"></i>Back</a>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm border-0 rounded-4">
    <div class="p-4 sm:p-5">
      <form action="{{ route('products.settings.update', $product) }}" method="POST">
        @csrf @method('PATCH')

        <div class="grid grid-cols-12 gap-4 gap-3">
          <div class="md:col-span-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">Listing Status</label>
            @php
              $isActive = (int) old('is_active', $product->is_active);
              $hasFeatured = !empty($product->featured_image);
              // A listing is eligible to activate when it's been paid at least once
              // and the next due date is either empty or in the future.
              $eligibleToActivate = !empty($product->listing_paid_at)
                                    && (empty($product->next_due_date) || \Carbon\Carbon::parse($product->next_due_date)->isFuture());
              $canToggleOn = $eligibleToActivate && $hasFeatured;
            @endphp
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="statusToggle"
                     {{ $isActive===1 ? 'checked' : '' }} {{ $canToggleOn || $isActive===1 ? '' : 'disabled' }}>
              <label class="form-check-label" for="statusToggle">{{ $isActive===1 ? 'Active' : 'Paused' }}</label>
            </div>
            <input type="hidden" name="is_active" id="is_active" value="{{ $isActive===1 ? 1 : 2 }}">
            <div class="mt-1 text-xs text-slate-500">
              @if(!$hasFeatured)
                Add a featured image to enable activation.
              @endif
              {{-- Only show pay/renew guidance if the listing is not currently active --}}
              @if($isActive !== 1 && !$eligibleToActivate)
                @if(empty($product->listing_paid_at))
                  <div class="mt-1">
                    <span>Pay the listing fee to activate.</span>
                    @php
                      $freq    = (int) ($product->category?->listing_frequency ?? 4);
                      $planKey = $freq === 1 ? 'monthly' : '4months';
                    @endphp
                    <button type="button"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition text-emerald-700 hover:text-emerald-600 underline-offset-2 hover:underline p-0 align-baseline"
                            onclick="document.getElementById('payFeeForm-{{ $product->id }}').submit();">
                      Pay to activate
                    </button>
                  </div>
                @else
                  <div class="mt-1">Renew your listing to activate.</div>
                @endif
              @endif
            </div>
            @error('is_active') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>

          <div class="md:col-span-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">Renewal Type</label>
            <select name="renewal_type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('renewal_type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
              <option value="automatic" {{ old('renewal_type',$product->renewal_type)==='automatic' ? 'selected' : '' }}>Automatic</option>
              <option value="manual"    {{ old('renewal_type',$product->renewal_type)==='manual'    ? 'selected' : '' }}>Manual</option>
            </select>
            @error('renewal_type') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>

          <div class="md:col-span-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">Visibility</label>
            @php $visibility = old('visibility', $product->visibility ?? 'Public'); @endphp
            <select name="visibility" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('visibility') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
              <option value="Public"  {{ $visibility==='Public'  ? 'selected' : '' }}>Public</option>
              <option value="Private" {{ $visibility==='Private' ? 'selected' : '' }}>Private</option>
              <option value="Unlisted"{{ $visibility==='Unlisted'? 'selected' : '' }}>Unlisted</option>
            </select>
            @error('visibility') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="grid grid-cols-12 gap-4 gap-3 mt-1">
          <div class="md:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">Slug (optional)</label>
            <input type="text" name="slug" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('slug') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   value="{{ old('slug', $product->slug) }}" placeholder="custom-url-slug">
            @error('slug') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>
          <div class="md:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">Tags (comma-separated, optional)</label>
            <input type="text" name="tags" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('tags') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   value="{{ old('tags', $product->tags ?? '') }}" placeholder="electronics, phone, samsung">
            @error('tags') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="mt-4 flex gap-2">
          <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500"><i class="fas fa-save mr-1"></i> Save</button>
          <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
      </form>

      {{-- Hidden standalone form for paying listing fee (avoids nesting forms) --}}
      <form id="payFeeForm-{{ $product->id }}" class="hidden" method="POST" action="{{ route('products.pay-fee', $product) }}">
        @csrf
        @php
          $freqHidden    = (int) ($product->category?->listing_frequency ?? 4);
          $planKeyHidden = $freqHidden === 1 ? 'monthly' : '4months';
        @endphp
        <input type="hidden" name="plan" value="{{ $planKeyHidden }}">
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    var cb = document.getElementById('statusToggle');
    var hidden = document.getElementById('is_active');
    if(cb && hidden){
      cb.addEventListener('change', function(){ hidden.value = cb.checked ? 1 : 2; });
    }
  });
  </script>
@endpush


