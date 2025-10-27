{{-- resources/views/items/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name . ' – Item Details')

@section('main')
@php
    // Ensure all relations are loaded
    $product->loadMissing(
        'variationTypes.options',
        'variations.options.variationType',
        'shippingProfiles',
        'media',
        'shop',
        'category',
        'country'
    );

    // Price calculations
    $basePrice  = (float) ($product->price ?? 0);
    $finalPrice = (float) ($product->discounted_price ?? $basePrice);

    // Default shipping profile
    $defaultShipId = ($defaultProfileId ?? null)
        ?? optional(
             $product->shippingProfiles->firstWhere('pivot.is_default', true)
             ?? $product->shippingProfiles->first()
           )->id;
@endphp

<section class="py-6" style="background:#f8faf9">
  <div class="container"
       x-data="{
         qty: 1,
         busy: false,
         shippingProfileId: {{ $defaultShipId ? (int)$defaultShipId : 'null' }},
         dec(){ this.qty = Math.max(1, this.qty-1) },
         inc(){ this.qty++ }
       }">

    {{-- Flash --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        @foreach($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="row g-lg-5">
      {{-- GALLERY --}}
      <div class="col-lg-7" data-aos="fade-right">
        @include('theme.'.theme().'.partials._media')
      </div>

      {{-- DETAILS + CART + ACTIONS --}}
      <div class="col-lg-5" data-aos="fade-left">
        <div class="position-lg-sticky" style="top: 1rem;">
          {{-- Product Details --}}
          <!-- @include('theme.'.theme().'.partials._details') -->

          {{-- Add to Cart --}}
          @include('theme.'.theme().'.partials._cart')

          {{-- Shipping Summary --}}
          @php
            $currency = get_currency();
            $isPhysical = ($product->type ?? '') === 'physical';
            $shipProfiles = $product->shippingProfiles ?? collect();

            // New: prefer per-product shipping rows (shipping_profiles table)
            $rows = \App\Models\ShippingProfile::where('product_id', $product->id)->get();
            $shipCost = null;
            $pickupAvailable = false;

            if ($rows->isNotEmpty()) {
              $defaultGroup = optional($rows->firstWhere('is_default', true))->profile_name
                           ?? optional($rows->first())->profile_name;
              $groupRows = $defaultGroup ? $rows->where('profile_name', $defaultGroup) : collect();

              if ($groupRows->isNotEmpty()) {
                $allFree = $groupRows->every(function ($r) {
                  $type = strtolower((string)($r->charge_type ?? ''));
                  $base = (float)($r->base_rate ?? 0);
                  return $type === 'free' || $base <= 0.0;
                });
                if ($allFree) {
                  $shipCost = 0.0;
                } else {
                  $min = $groupRows->filter(function ($r) {
                    $type = strtolower((string)($r->charge_type ?? ''));
                    return $type !== 'free';
                  })->min(function ($r) { return (float)($r->base_rate ?? 0); });
                  if (is_numeric($min)) {
                    $shipCost = (float)$min;
                  }
                }
              }
              $pickupAvailable = $rows->contains(function ($r) {
                return (bool)($r->pickup_available ?? false);
              });
            }

            // Fallback: legacy pivot-based profiles if no rows computed
            if (is_null($shipCost)) {
              $defaultProfile = $defaultShipId
                ? $shipProfiles->firstWhere('id', (int) $defaultShipId)
                : null;
              if ($defaultProfile && isset($defaultProfile->base_rate)) {
                $shipCost = (float) $defaultProfile->base_rate;
              } elseif ($shipProfiles->isNotEmpty()) {
                $shipCost = (float) $shipProfiles->min(fn($sp)=> (float) ($sp->base_rate ?? 0));
              }

              if ($shipProfiles->isNotEmpty() && array_key_exists('pickup_available', $shipProfiles->first()->getAttributes())) {
                $pickupAvailable = $pickupAvailable || $shipProfiles->contains(fn($sp) => (bool) ($sp->pickup_available ?? false));
              } else {
                try {
                  $pickupAvailable = $pickupAvailable || $product->shippingProfiles()->where('pickup_available', true)->exists();
                } catch (\Throwable $e) {
                  $pickupAvailable = $pickupAvailable || false;
                }
              }
            }
          @endphp

          @if($isPhysical)
            <div class="card border-0 shadow-sm mb-4">
              <div class="card-body d-flex flex-wrap align-items-center gap-4">
                <div class="d-flex align-items-center gap-2">
                  <i class="fa-solid fa-truck text-success"></i>
                  <div class="small">
                    <div class="fw-semibold">Shipping</div>
                    @if(!is_null($shipCost))
                      <div class="text-muted">{{ $shipCost <= 0 ? 'Free' : ($currency.' '.number_format($shipCost, 2)) }}</div>
                    @else
                      <div class="text-muted">Calculated at checkout</div>
                    @endif
                  </div>
                </div>

                <div class="vr d-none d-md-block"></div>

                <div class="d-flex align-items-center gap-2">
                  <i class="fa-solid fa-store {{ $pickupAvailable ? 'text-primary' : 'text-muted' }}"></i>
                  <div class="small">
                    <div class="fw-semibold">Pickup</div>
                    <div class="text-muted">{{ $pickupAvailable ? 'Available' : 'Not available' }}</div>
                  </div>
                </div>
              </div>
            </div>
          @endif

          {{-- Service Notice --}}
          @include('theme.'.theme().'.partials._service_notice')

          {{-- Share Links --}}
          @include('theme.'.theme().'.partials._share')
        </div>
      </div>
    </div>

    {{-- DESCRIPTION / SHIPPING / REVIEWS / FAQ TABS --}}
    @include('theme.'.theme().'.partials._tabs_nav')
    <div class="tab-content bg-white p-4 border-bottom border-start border-end rounded-bottom-4 shadow-sm">
      @include('theme.'.theme().'.partials._tab_description')
      @if(($product->type ?? '') !== 'service')
        @include('theme.'.theme().'.partials._tab_shipping')
      @endif
      @include('theme.'.theme().'.partials._tab_reviews')
      @include('theme.'.theme().'.partials._tab_faq')
    </div>

    {{-- MORE FROM SHOP & RELATED ITEMS --}}
    @include('theme.'.theme().'.partials._more_from_shop')
    @include('theme.'.theme().'.partials._related')
  </div>
</section>

{{-- Modals --}}
@include('theme.'.theme().'.partials.modals._message')
@include('theme.'.theme().'.partials.modals._offer')
@include('theme.'.theme().'.partials.modals._report')
@endsection

@push('styles')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
      integrity="sha384-PU0QFv1kXlz9BM/UX5EwyV/ivxVMolZTUsjoeetfYxNdUswzqnMHipjInu6bcVCc"
      crossorigin="anonymous">
<style>
  .thumb.active,
  .thumb:hover { border:2px solid #198754!important; }
  .carousel-inner img { transition:.4s; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
        integrity="sha384-xKDJcyOgCjL2mK9ZcYnmQgSJvMREh4baN4GckSbnREV7mY4T0kT2LSpJxErL8xP8"
        crossorigin="anonymous"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => AOS.init({ duration:800, once:true }));

  // Alpine variant picker stub—cart uses simple form, but keep for future enhancements
  document.addEventListener('alpine:init', () => {
    Alpine.data('variantPicker', ({ types, variants, basePrice, currency }) => ({
      types, variants, basePrice, currency,
      selected: {}, currentVariantId: null, currentVariantPrice: null,
      init() { this.types.forEach(t => this.selected[t.id] = ''); },
      onChange() {
        const all = this.types.every(t => this.selected[t.id]);
        if (!all) return this.currentVariantId = this.currentVariantPrice = null;
        const match = this.variants.find(v =>
          this.types.every(t => String(v.byType[t.id]) === String(this.selected[t.id]))
        );
        if (match) [this.currentVariantId, this.currentVariantPrice] = [match.id, match.price];
        else this.currentVariantId = this.currentVariantPrice = null;
      },
      filteredOptions(typeId) {
        const chosen = {...this.selected}; delete chosen[typeId];
        const others = Object.entries(chosen).filter(([_,v])=>v);
        if (!others.length) return this.types.find(t=>t.id===typeId).options;
        const allowed = new Set();
        this.variants.forEach(v => {
          if (others.every(([tid,val])=>String(v.byType[tid])===String(val))) {
            allowed.add(String(v.byType[typeId]));
          }
        });
        return this.types.find(t=>t.id===typeId).options.filter(o=>allowed.has(String(o.id)));
      },
      priceFormatted() {
        const p = this.currentVariantPrice ?? this.basePrice;
        return `${this.currency} ${p.toFixed(2)}`;
      },
      canSubmit() {
        return !this.types.length || !!this.currentVariantId;
      }
    }));
  });
</script>
@endpush
