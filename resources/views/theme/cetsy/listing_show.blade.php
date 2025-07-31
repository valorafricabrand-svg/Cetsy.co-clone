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

    <div class="row g-lg-5">
      {{-- GALLERY --}}
      <div class="col-lg-7" data-aos="fade-right">
        @include('theme.'.theme().'.partials._media')
      </div>

      {{-- DETAILS + CART + ACTIONS --}}
      <div class="col-lg-5" data-aos="fade-left">
        <div class="position-lg-sticky" style="top: 1rem;">
          {{-- Product Details --}}
          @include('theme.'.theme().'.partials._details')

          {{-- Add to Cart --}}
          @include('theme.'.theme().'.partials._cart')

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
      @include('theme.'.theme().'.partials._tab_shipping')
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
