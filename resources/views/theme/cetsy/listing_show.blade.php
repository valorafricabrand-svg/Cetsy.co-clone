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
// Price calculations
$basePrice = (float) ($product->price ?? 0);
$finalPrice = (float) ($product->discounted_price ?? $basePrice);

// Default shipping profile
$defaultShipId = ($defaultProfileId ?? null)
?? optional(
    $product->shippingProfiles->firstWhere('pivot.is_default', true)
    ?? $product->shippingProfiles->first()
)->id;
@endphp

<section class="py-4 py-lg-5" style="background:#f3f4f6">
    <div class="container" x-data="{
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

        <div class="bg-white rounded-4 shadow-sm p-3 p-lg-4 mb-4">
            <div class="row g-lg-5 align-items-start">
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
                    @if(($product->type ?? '') === 'physical' && (int)($product->stock ?? 0) === 1 &&
                    ($product->is_reserved ?? false))
                    <div class="alert alert-warning mt-3">This item is reserved in another pending order and cannot be
                        purchased right now.</div>
                    @endif

                    {{-- Shipping Summary --}}
                    @php
                    $currency = get_currency();
                    $isPhysical = ($product->type ?? '') === 'physical';
                    $shipProfiles = $product->shippingProfiles ?? collect();

                    // New: prefer per-product shipping rows (shipping_profiles table)
                    $rows = \App\Models\ShippingProfile::where('product_id', $product->id)->get();
                    $shipCost = null;
                    // Prefer explicit per-listing pickup flag, but also
                    // respect any existing pickup-enabled shipping rows.
                    $pickupAvailable = (bool) ($product->pickup_available ?? false);

                    if ($rows->isNotEmpty()) {
                    $defaultGroup = optional($rows->firstWhere('is_default', true))->profile_name
                    ?? optional($rows->first())->profile_name;
                    $groupRows = $defaultGroup ? $rows->where('profile_name', $defaultGroup) : collect();

                    if ($groupRows->isNotEmpty()) {
                    $allFree = $groupRows->every(function ($r) {
                    $type = strtolower((string)($r->charge_type ?? ''));
                    $base = (float)($r->base_rate ?? 0);
                    return $type === 'free' || $base <= 0.0; }); if ($allFree) { $shipCost=0.0; } else {
                        $min=$groupRows->filter(function ($r) {
                        $type = strtolower((string)($r->charge_type ?? ''));
                        return $type !== 'free';
                        })->min(function ($r) { return (float)($r->base_rate ?? 0); });
                        if (is_numeric($min)) {
                        $shipCost = (float)$min;
                        }
                        }
                        }
                        $pickupAvailable = $pickupAvailable || $rows->contains(function ($r) {
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

                        if ($shipProfiles->isNotEmpty() && array_key_exists('pickup_available',
                        $shipProfiles->first()->getAttributes())) {
                        $pickupAvailable = $pickupAvailable || $shipProfiles->contains(fn($sp) => (bool)
                        ($sp->pickup_available ?? false));
                        } else {
                        try {
                        $pickupAvailable = $pickupAvailable || $product->shippingProfiles()->where('pickup_available',
                        true)->exists();
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
                                        <div class="text-muted">
                                            {{ $shipCost <= 0 ? 'Free' : ($currency.' '.number_format($shipCost, 2)) }}
                                        </div>
                                        @else
                                        <div class="text-muted">Calculated at checkout</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="vr d-none d-md-block"></div>

                                <div class="d-flex align-items-center gap-2">
                                    <i
                                        class="fa-solid fa-store {{ $pickupAvailable ? 'text-primary' : 'text-muted' }}"></i>
                                    <div class="small">
                                        <div class="fw-semibold">Pickup</div>
                                        <div class="text-muted">{{ $pickupAvailable ? 'Available' : 'Not available' }}
                                        </div>
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
        </div>

        {{-- DESCRIPTION / SHIPPING / REVIEWS / FAQ TABS --}}
        @php
        // Prepare ETA labels for Shipping tab
        $etaLabel = null; $procLabel = null; $transitLabel = null;
        try {
        $procMin = null; $procMax = null;
        if (!empty($product->processing_time_id)) {
        $pt = \App\Models\ProcessingTime::find($product->processing_time_id);
        if ($pt) {
        if (isset($pt->days) && is_numeric($pt->days)) { $procMin = $procMax = (int) $pt->days; }
        else {
        $procMin = is_numeric($pt->start_day ?? null) ? (int) $pt->start_day : null;
        $procMax = is_numeric($pt->end_day ?? null) ? (int) $pt->end_day : null;
        }
        }
        }
        $rows = \App\Models\ShippingProfile::where('product_id', $product->id)->get();
        if (($procMin === null && $procMax === null) && $rows->isNotEmpty()) {
        $minRow = $rows->min(function($r){ return (int) ($r->processing_custom_min ?? PHP_INT_MAX); });
        if (is_int($minRow) && $minRow !== PHP_INT_MAX) { $procMin = $minRow; }
        $rowPtId = optional($rows->firstWhere('processing_time_id', '!=', null))->processing_time_id;
        if ($rowPtId && ($pt2 = \App\Models\ProcessingTime::find($rowPtId))) {
        if ($procMin === null && isset($pt2->days) && is_numeric($pt2->days)) { $procMin = (int)$pt2->days; }
        if (isset($pt2->start_day) && is_numeric($pt2->start_day)) { $procMin = $procMin ?? (int)$pt2->start_day; }
        if (isset($pt2->end_day) && is_numeric($pt2->end_day)) { $procMax = (int)$pt2->end_day; }
        }
        }
        $daysMin = null; $daysMax = null;
        if ($rows->isNotEmpty()) {
        $defaultGroup = optional($rows->firstWhere('is_default', true))->profile_name ??
        optional($rows->first())->profile_name;
        $groupRows = $defaultGroup ? $rows->where('profile_name', $defaultGroup) : collect();
        if ($groupRows->isNotEmpty()) {
        $daysMin = $groupRows->min(function($r){ return is_numeric($r->days_min ?? null) ? (int)$r->days_min :
        PHP_INT_MAX; });
        $daysMax = $groupRows->max(function($r){ return is_numeric($r->days_max ?? null) ? (int)$r->days_max : 0; });
        if ($daysMin === PHP_INT_MAX) { $daysMin = null; }
        if ($daysMax === 0) { $daysMax = null; }
        }
        }
        if ($procMin !== null && $procMax !== null) {
        $procLabel = ($procMin === $procMax) ? ($procMin.' day'.($procMin==1?'':'s')) : ($procMin.'-'.$procMax.' days');
        } elseif ($procMin !== null) { $procLabel = $procMin.' day'.($procMin==1?'':'s'); }
        if ($daysMin !== null && $daysMax !== null) {
        $transitLabel = ($daysMin === $daysMax) ? ($daysMin.' day'.($daysMin==1?'':'s')) : ($daysMin.'-'.$daysMax.'
        days');
        } elseif ($daysMin !== null) { $transitLabel = $daysMin.' day'.($daysMin==1?'':'s'); }
        if ($procMin !== null || $daysMin !== null || $procMax !== null || $daysMax !== null) {
        $minTotal = (int) (($procMin ?? 0) + ($daysMin ?? 0));
        $maxTotal = ($procMax !== null || $daysMax !== null)
        ? (int) (($procMax ?? ($procMin ?? 0)) + ($daysMax ?? ($daysMin ?? 0)))
        : null;
        $base = now();
        $fmt = function($d){ return $d ? $d->format('M j') : null; };
        $etaStart = $minTotal > 0 ? $base->copy()->addDays($minTotal) : null;
        $etaEnd = $maxTotal !== null ? $base->copy()->addDays($maxTotal) : null;
        if ($etaStart && $etaEnd) { $etaLabel = $fmt($etaStart).' - '.$fmt($etaEnd); }
        elseif ($etaStart) { $etaLabel = $fmt($etaStart); }
        elseif ($etaEnd) { $etaLabel = $fmt($etaEnd); }
        }
        } catch (\Throwable $e) { /* ignore */ }
        @endphp

        {{-- Slim reassurance row (delivery / click & collect / payments) --}}
        <div class="bg-white rounded-4 shadow-sm mb-4 px-3 px-lg-4 py-3 listing-benefit-row">
            <div class="row text-muted small text-center text-md-start g-3">
                <div class="col-md-4 d-flex justify-content-center justify-content-md-start align-items-center gap-2">
                    <i class="fas fa-truck-fast text-success fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">Delivery</div>
                        <div class="text-muted">
                            @if(!empty($etaLabel))
                                Estimated {{ $etaLabel }}
                            @else
                                Delivery dates shown at checkout
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex justify-content-center justify-content-md-start align-items-center gap-2">
                    <i class="fas fa-store {{ $pickupAvailable ? 'text-primary' : 'text-muted' }} fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">Click &amp; collect</div>
                        <div class="text-muted">
                            @if($pickupAvailable)
                                Click &amp; collect available for this item.
                            @else
                                Click &amp; collect not available for this item.
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex justify-content-center justify-content-md-start align-items-center gap-2">
                    <i class="fas fa-wallet text-warning fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark">Flexible payments</div>
                        <div class="text-muted">Pay with wallet, cards, and more.</div>
                    </div>
                </div>
            </div>
        </div>

        @include('theme.'.theme().'.partials._tabs_nav')
        <div class="tab-content bg-white p-4 border-bottom border-start border-end rounded-bottom-4 shadow-sm">
            @include('theme.'.theme().'.partials._tab_description')
            @if(($product->type ?? '') !== 'service')
            @include('theme.'.theme().'.partials._tab_shipping', ['etaLabel'=>$etaLabel, 'procLabel'=>$procLabel,
            'transitLabel'=>$transitLabel])
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
    integrity="sha384-PU0QFv1kXlz9BM/UX5EwyV/ivxVMolZTUsjoeetfYxNdUswzqnMHipjInu6bcVCc" crossorigin="anonymous">
<style>
.listing-benefit-row {
    font-size: .9rem;
}
.thumb.active,
.thumb:hover {
    border: 2px solid #198754 !important;
}

.carousel-inner img {
    transition: .4s;
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"
    integrity="sha384-xKDJcyOgCjL2mK9ZcYnmQgSJvMREh4baN4GckSbnREV7mY4T0kT2LSpJxErL8xP8" crossorigin="anonymous">
</script>
<script>
document.addEventListener('DOMContentLoaded', () => AOS.init({
    duration: 800,
    once: true
}));

// Alpine variant picker stub—cart uses simple form, but keep for future enhancements
document.addEventListener('alpine:init', () => {
    Alpine.data('variantPicker', ({
        types,
        variants,
        basePrice,
        currency
    }) => ({
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
            if (match)[this.currentVariantId, this.currentVariantPrice] = [match.id, match.price];
            else this.currentVariantId = this.currentVariantPrice = null;
        },
        filteredOptions(typeId) {
            const chosen = {
                ...this.selected
            };
            delete chosen[typeId];
            const others = Object.entries(chosen).filter(([_, v]) => v);
            if (!others.length) return this.types.find(t => t.id === typeId).options;
            const allowed = new Set();
            this.variants.forEach(v => {
                if (others.every(([tid, val]) => String(v.byType[tid]) === String(val))) {
                    allowed.add(String(v.byType[typeId]));
                }
            });
            return this.types.find(t => t.id === typeId).options.filter(o => allowed.has(String(o
                .id)));
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
