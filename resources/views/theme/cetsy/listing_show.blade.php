{{-- resources/views/items/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@php
use Illuminate\Support\Str;

$product->loadMissing(
    'variationTypes.options',
    'variations.options.variationType',
    'shippingProfiles',
    'media',
    'shop',
    'category',
    'country'
);

$primaryMedia = $product->media->firstWhere('type', 'image') ?? $product->media->first();
$metaImage = $primaryMedia && $primaryMedia->url ? media_url($primaryMedia->url) : asset('assets/images/default-og-image-cetsy.jpg');
$metaDescription = Str::limit(strip_tags($product->description ?? $product->name), 155);
@endphp

@section('title', $product->name . ' - Item Details | Cetsy')
@section('meta_description', $metaDescription)
@section('canonical_url', route('listing.show', $product->slug))
@section('meta_image', $metaImage)
@section('meta_robots', 'index, follow')

@section('main')
@php
$basePrice = (float) ($product->price ?? 0);
$finalPrice = (float) ($product->discounted_price ?? $basePrice);

$defaultShipId = ($defaultProfileId ?? null)
?? optional(
    $product->shippingProfiles->firstWhere('pivot.is_default', true)
    ?? $product->shippingProfiles->first()
)->id;
@endphp

<div class="relative overflow-x-clip bg-slate-50 pb-10">
  <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-emerald-200/35 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-20 top-[28rem] h-72 w-72 rounded-full bg-cyan-200/30 blur-3xl"></div>

  <section class="py-5 lg:py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8" x-data="{
      qty: 1,
      busy: false,
      shippingProfileId: {{ $defaultShipId ? (int)$defaultShipId : 'null' }},
      dec(){ this.qty = Math.max(1, this.qty-1) },
      inc(){ this.qty++ }
    }">

      @if(session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {!! session('success') !!}
        </div>
      @endif

      @if($errors->any())
        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <div class="mb-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm lg:p-6">
        <div class="grid items-start gap-6 lg:grid-cols-[1.1fr_0.9fr]">
          <div data-aos="fade-right">
            @include('theme.'.theme().'.partials._media_tw')
          </div>

          <div data-aos="fade-left">
            <div class="lg:sticky lg:top-4">
              @include('theme.'.theme().'.partials._cart_tw')

              @if(($product->type ?? '') === 'physical' && (int)($product->stock ?? 0) === 1 && ($product->is_reserved ?? false))
                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                  This item is reserved in another pending order and cannot be purchased right now.
                </div>
              @endif

              @php
                $currency = get_currency();
                $isPhysical = ($product->type ?? '') === 'physical';
                $shipProfiles = $product->shippingProfiles ?? collect();

                $rows = \App\Models\ShippingProfile::where('product_id', $product->id)->get();
                $shipCost = null;
                $pickupAvailable = (bool) ($product->pickup_available ?? false);

                if ($rows->isNotEmpty()) {
                    $defaultGroup = optional($rows->firstWhere('is_default', true))->profile_name
                        ?? optional($rows->first())->profile_name;
                    $groupRows = $defaultGroup ? $rows->where('profile_name', $defaultGroup) : collect();

                    if ($groupRows->isNotEmpty()) {
                        $allFree = $groupRows->every(function ($r) {
                            $type = strtolower((string) ($r->charge_type ?? ''));
                            $base = (float) ($r->base_rate ?? 0);
                            return $type === 'free' || $base <= 0.0;
                        });

                        if ($allFree) {
                            $shipCost = 0.0;
                        } else {
                            $min = $groupRows->filter(function ($r) {
                                $type = strtolower((string) ($r->charge_type ?? ''));
                                return $type !== 'free';
                            })->min(function ($r) {
                                return (float) ($r->base_rate ?? 0);
                            });

                            if (is_numeric($min)) {
                                $shipCost = (float) $min;
                            }
                        }
                    }

                    $pickupAvailable = $pickupAvailable || $rows->contains(function ($r) {
                        return (bool) ($r->pickup_available ?? false);
                    });
                }

                if (is_null($shipCost)) {
                    $defaultProfile = $defaultShipId
                        ? $shipProfiles->firstWhere('id', (int) $defaultShipId)
                        : null;

                    if ($defaultProfile && isset($defaultProfile->base_rate)) {
                        $shipCost = (float) $defaultProfile->base_rate;
                    } elseif ($shipProfiles->isNotEmpty()) {
                        $shipCost = (float) $shipProfiles->min(fn($sp) => (float) ($sp->base_rate ?? 0));
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
                <div class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <div class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-truck text-emerald-600"></i>
                      <div class="text-sm">
                        <div class="font-semibold text-slate-900">Shipping</div>
                        @if(!is_null($shipCost))
                          <div class="text-slate-500">{{ $shipCost <= 0 ? 'Free' : ($currency.' '.number_format($shipCost, 2)) }}</div>
                        @else
                          <div class="text-slate-500">Calculated at checkout</div>
                        @endif
                      </div>
                    </div>

                    <div class="hidden h-8 w-px bg-slate-200 md:block"></div>

                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-store {{ $pickupAvailable ? 'text-sky-600' : 'text-slate-400' }}"></i>
                      <div class="text-sm">
                        <div class="font-semibold text-slate-900">Pickup</div>
                        <div class="text-slate-500">{{ $pickupAvailable ? 'Available' : 'Not available' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              @endif

              @include('theme.'.theme().'.partials._service_notice')
              @include('theme.'.theme().'.partials._share')
            </div>
          </div>
        </div>
      </div>
      @php
        $etaLabel = null; $procLabel = null; $transitLabel = null;
        try {
          $procMin = null; $procMax = null;

          if (!empty($product->processing_time_id)) {
            $pt = \App\Models\ProcessingTime::find($product->processing_time_id);
            if ($pt) {
              if (isset($pt->days) && is_numeric($pt->days)) {
                $procMin = $procMax = (int) $pt->days;
              } else {
                $procMin = is_numeric($pt->start_day ?? null) ? (int) $pt->start_day : null;
                $procMax = is_numeric($pt->end_day ?? null) ? (int) $pt->end_day : null;
              }
            }
          }

          $rows = \App\Models\ShippingProfile::where('product_id', $product->id)->get();
          if (($procMin === null && $procMax === null) && $rows->isNotEmpty()) {
            $minRow = $rows->min(function($r){ return (int) ($r->processing_custom_min ?? PHP_INT_MAX); });
            if (is_int($minRow) && $minRow !== PHP_INT_MAX) {
              $procMin = $minRow;
            }

            $rowPtId = optional($rows->firstWhere('processing_time_id', '!=', null))->processing_time_id;
            if ($rowPtId && ($pt2 = \App\Models\ProcessingTime::find($rowPtId))) {
              if ($procMin === null && isset($pt2->days) && is_numeric($pt2->days)) {
                $procMin = (int)$pt2->days;
              }
              if (isset($pt2->start_day) && is_numeric($pt2->start_day)) {
                $procMin = $procMin ?? (int)$pt2->start_day;
              }
              if (isset($pt2->end_day) && is_numeric($pt2->end_day)) {
                $procMax = (int)$pt2->end_day;
              }
            }
          }

          $daysMin = null; $daysMax = null;
          if ($rows->isNotEmpty()) {
            $defaultGroup = optional($rows->firstWhere('is_default', true))->profile_name ?? optional($rows->first())->profile_name;
            $groupRows = $defaultGroup ? $rows->where('profile_name', $defaultGroup) : collect();
            if ($groupRows->isNotEmpty()) {
              $daysMin = $groupRows->min(function($r){ return is_numeric($r->days_min ?? null) ? (int)$r->days_min : PHP_INT_MAX; });
              $daysMax = $groupRows->max(function($r){ return is_numeric($r->days_max ?? null) ? (int)$r->days_max : 0; });
              if ($daysMin === PHP_INT_MAX) { $daysMin = null; }
              if ($daysMax === 0) { $daysMax = null; }
            }
          }

          if ($procMin !== null && $procMax !== null) {
            $procLabel = ($procMin === $procMax) ? ($procMin.' day'.($procMin==1?'':'s')) : ($procMin.'-'.$procMax.' days');
          } elseif ($procMin !== null) {
            $procLabel = $procMin.' day'.($procMin==1?'':'s');
          }

          if ($daysMin !== null && $daysMax !== null) {
            $transitLabel = ($daysMin === $daysMax) ? ($daysMin.' day'.($daysMin==1?'':'s')) : ($daysMin.'-'.$daysMax.' days');
          } elseif ($daysMin !== null) {
            $transitLabel = $daysMin.' day'.($daysMin==1?'':'s');
          }

          if ($procMin !== null || $daysMin !== null || $procMax !== null || $daysMax !== null) {
            $minTotal = (int) (($procMin ?? 0) + ($daysMin ?? 0));
            $maxTotal = ($procMax !== null || $daysMax !== null)
              ? (int) (($procMax ?? ($procMin ?? 0)) + ($daysMax ?? ($daysMin ?? 0)))
              : null;

            $base = now();
            $fmt = function($d){ return $d ? $d->format('M j') : null; };
            $etaStart = $minTotal > 0 ? $base->copy()->addDays($minTotal) : null;
            $etaEnd = $maxTotal !== null ? $base->copy()->addDays($maxTotal) : null;

            if ($etaStart && $etaEnd) {
              $etaLabel = $fmt($etaStart).' - '.$fmt($etaEnd);
            } elseif ($etaStart) {
              $etaLabel = $fmt($etaStart);
            } elseif ($etaEnd) {
              $etaLabel = $fmt($etaEnd);
            }
          }
        } catch (\Throwable $e) {
          // ignore
        }
      @endphp

      <div class="mb-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <div class="grid gap-4 text-sm text-slate-600 md:grid-cols-3">
          <div class="flex items-start gap-2">
            <i class="fas fa-truck-fast mt-0.5 text-emerald-600"></i>
            <div>
              <div class="font-semibold text-slate-900">Delivery</div>
              <div>
                @if(!empty($etaLabel))
                  Estimated {{ $etaLabel }}
                @else
                  Delivery dates shown at checkout
                @endif
              </div>
            </div>
          </div>

          <div class="flex items-start gap-2">
            <i class="fas fa-store mt-0.5 {{ $pickupAvailable ? 'text-sky-600' : 'text-slate-400' }}"></i>
            <div>
              <div class="font-semibold text-slate-900">Click & collect</div>
              <div>
                @if($pickupAvailable)
                  Click & collect available for this item.
                @else
                  Click & collect not available for this item.
                @endif
              </div>
            </div>
          </div>

          <div class="flex items-start gap-2">
            <i class="fas fa-wallet mt-0.5 text-amber-500"></i>
            <div>
              <div class="font-semibold text-slate-900">Flexible payments</div>
              <div>Pay with wallet, cards, and more.</div>
            </div>
          </div>
        </div>
      </div>

      @include('theme.'.theme().'.partials._tabs_nav')
      <div id="listing-tab-panels" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        @include('theme.'.theme().'.partials._tab_description')
        @if(($product->type ?? '') !== 'service')
          @include('theme.'.theme().'.partials._tab_shipping', ['etaLabel'=>$etaLabel, 'procLabel'=>$procLabel, 'transitLabel'=>$transitLabel])
        @endif
        @include('theme.'.theme().'.partials._tab_reviews')
        @include('theme.'.theme().'.partials._tab_faq')
      </div>

      @include('theme.'.theme().'.partials._more_from_shop')
      @include('theme.'.theme().'.partials._related')
    </div>
  </section>
</div>

@include('theme.'.theme().'.partials.modals._message')
@include('theme.'.theme().'.partials.modals._offer')
@include('theme.'.theme().'.partials.modals._report')
@endsection
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
    integrity="sha384-PU0QFv1kXlz9BM/UX5EwyV/ivxVMolZTUsjoeetfYxNdUswzqnMHipjInu6bcVCc" crossorigin="anonymous">
<style>
  .listing-tab-btn.is-active {
    border-color: rgb(16 185 129);
    color: rgb(5 150 105);
    background: rgb(236 253 245);
  }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
    integrity="sha384-xKDJcyOgCjL2mK9ZcYnmQgSJvMREh4baN4GckSbnREV7mY4T0kT2LSpJxErL8xP8" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (window.AOS) {
    AOS.init({ duration: 800, once: true });
  }

  const tabButtons = Array.from(document.querySelectorAll('.listing-tab-btn'));
  const panes = Array.from(document.querySelectorAll('.listing-tab-pane'));

  function activateTab(tabId, pushHash = true) {
    tabButtons.forEach(btn => {
      const active = btn.dataset.tabTarget === tabId;
      btn.classList.toggle('is-active', active);
      btn.classList.toggle('border-slate-300', !active);
      btn.classList.toggle('bg-white', !active);
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    panes.forEach(pane => {
      pane.classList.toggle('hidden', pane.id !== tabId);
    });

    if (pushHash) {
      history.replaceState(null, '', `#${tabId}`);
    }
  }

  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => activateTab(btn.dataset.tabTarget));
  });

  const hashTab = window.location.hash ? window.location.hash.replace('#', '') : '';
  if (hashTab && panes.some(p => p.id === hashTab)) {
    activateTab(hashTab, false);
  } else {
    const first = tabButtons[0]?.dataset.tabTarget;
    if (first) activateTab(first, false);
  }

  const body = document.body;
  const modalEls = Array.from(document.querySelectorAll('.tw-modal'));
  let activeModal = null;

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    if (activeModal === modal) {
      activeModal = null;
      body.classList.remove('overflow-hidden');
    }
  }

  function openModal(modal) {
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    activeModal = modal;
    body.classList.add('overflow-hidden');
  }

  document.querySelectorAll('[data-tw-modal-open]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const id = trigger.getAttribute('data-tw-modal-open');
      const modal = id ? document.getElementById(id) : null;
      openModal(modal);
    });
  });

  document.querySelectorAll('[data-tw-modal-close]').forEach(trigger => {
    trigger.addEventListener('click', () => closeModal(trigger.closest('.tw-modal')));
  });

  modalEls.forEach(modal => {
    modal.addEventListener('click', (event) => {
      if (event.target === modal) closeModal(modal);
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && activeModal) {
      closeModal(activeModal);
    }
  });
});

// Alpine variant picker stub for forward compatibility
document.addEventListener('alpine:init', () => {
  Alpine.data('variantPicker', ({ types, variants, basePrice, currency }) => ({
    types,
    variants,
    basePrice,
    currency,
    selected: {},
    currentVariantId: null,
    currentVariantPrice: null,
    init() {
      this.types.forEach(t => this.selected[t.id] = '');
    },
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
      const chosen = { ...this.selected };
      delete chosen[typeId];
      const others = Object.entries(chosen).filter(([_, v]) => v);
      if (!others.length) return this.types.find(t => t.id === typeId).options;
      const allowed = new Set();
      this.variants.forEach(v => {
        if (others.every(([tid, val]) => String(v.byType[tid]) === String(val))) {
          allowed.add(String(v.byType[typeId]));
        }
      });
      return this.types.find(t => t.id === typeId).options.filter(o => allowed.has(String(o.id)));
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
