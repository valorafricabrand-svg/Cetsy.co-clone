{{-- resources/views/products/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name . ' | Product')

@push('styles')
<style>
  .product-show__hero {
    position: relative;
    overflow: hidden;
    background:
      radial-gradient(circle at top right, rgba(16, 185, 129, 0.18), transparent 34%),
      radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.16), transparent 40%),
      linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  }
  .product-show__hero::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
      linear-gradient(135deg, rgba(255, 255, 255, 0.35), rgba(255, 255, 255, 0) 42%),
      linear-gradient(315deg, rgba(15, 23, 42, 0.03), rgba(15, 23, 42, 0) 48%);
  }
  .product-show__thumbs {
    scrollbar-width: none;
    -ms-overflow-style: none;
    -webkit-overflow-scrolling: touch;
  }
  .product-show__thumbs::-webkit-scrollbar {
    display: none;
  }
  .product-show__richtext {
    color: #334155;
    line-height: 1.75;
    word-break: break-word;
    overflow-wrap: anywhere;
  }
  .product-show__richtext > * + * {
    margin-top: 1rem;
  }
  .product-show__richtext img,
  .product-show__richtext video,
  .product-show__richtext canvas,
  .product-show__richtext svg,
  .product-show__richtext iframe {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 1rem;
  }
  .product-show__richtext iframe {
    width: 100%;
    min-height: 18rem;
    border: 0;
    background: #0f172a;
  }
  .product-show__richtext table {
    display: block;
    width: 100%;
    overflow-x: auto;
    border-collapse: collapse;
  }
  .product-show__richtext table th,
  .product-show__richtext table td {
    border: 1px solid #e2e8f0;
    padding: 0.7rem 0.8rem;
    text-align: left;
    vertical-align: top;
  }
  .product-show__richtext ul,
  .product-show__richtext ol {
    padding-left: 1.25rem;
  }
  .product-show__richtext a {
    color: #047857;
    text-decoration: underline;
  }
  .product-show__richtext pre {
    max-width: 100%;
    overflow-x: auto;
    border-radius: 1rem;
    background: #0f172a;
    color: #e2e8f0;
    padding: 1rem;
  }
  @media (max-width: 639.98px) {
    .product-show__richtext iframe {
      min-height: 14rem;
    }
  }
</style>
@endpush

@section('main')
@php
    $current = \Illuminate\Support\Facades\Route::currentRouteName();
    $now = \Carbon\Carbon::now();
    $hasPaid = !empty($product->listing_paid_at);
    $hasBillingHistory = $hasPaid || !empty($product->next_due_date);
    try {
        $nextDueDate = $product->next_due_date ? \Carbon\Carbon::parse($product->next_due_date) : null;
    } catch (\Throwable $e) {
        $nextDueDate = null;
    }
    $isExpired = $hasPaid && $nextDueDate && $nextDueDate->lte($now);
    $isWithinPaidCycle = $hasPaid && (! $nextDueDate || $nextDueDate->gt($now));
    $hasFeatured = !empty($product->featured_image) || ($product->media && $product->media->count() > 0);
    $showStatusToggle = (int) $product->is_active === 1 || ((int) $product->is_active === 2 && $isWithinPaidCycle);
    $effectiveStatus = ((int) $product->is_active === 2 && ! $hasBillingHistory) ? 0 : (int) $product->is_active;
    $mediaItems = $product->media ?? collect();

    switch ($effectiveStatus) {
        case 0:
            $statusLabel = 'Pending';
            $statusBadge = 'border-amber-200 bg-amber-100 text-amber-800';
            break;
        case 1:
            $statusLabel = 'Active';
            $statusBadge = 'border-emerald-200 bg-emerald-100 text-emerald-800';
            break;
        case 2:
            $statusLabel = 'Paused';
            $statusBadge = 'border-slate-200 bg-slate-100 text-slate-700';
            break;
        case 3:
            $statusLabel = 'Suspended';
            $statusBadge = 'border-rose-200 bg-rose-100 text-rose-800';
            break;
        default:
            $statusLabel = 'Closed';
            $statusBadge = 'border-slate-300 bg-slate-200 text-slate-800';
            break;
    }

    $summaryStats = [
        ['label' => 'Type', 'value' => ucfirst((string) $product->type), 'icon' => 'fa-box'],
        ['label' => 'Renewal', 'value' => ucfirst((string) ($product->renewal_type ?? 'automatic')), 'icon' => 'fa-arrows-rotate'],
        ['label' => 'Listing ID', 'value' => (string) $product->id, 'icon' => 'fa-hashtag'],
    ];

    if ($product->type === 'physical' && !is_null($product->stock)) {
        $summaryStats[] = ['label' => 'Stock', 'value' => (string) $product->stock, 'icon' => 'fa-layer-group'];
    }

    if ($hasPaid && $product->listing_paid_at) {
        try {
            $summaryStats[] = [
                'label' => 'Listed On',
                'value' => \Carbon\Carbon::parse($product->listing_paid_at)->format('M d, Y'),
                'icon' => 'fa-calendar-plus',
            ];
        } catch (\Throwable $e) {
        }
    }

    if ($nextDueDate) {
        $summaryStats[] = [
            'label' => 'Next Due',
            'value' => $nextDueDate->format('M d, Y'),
            'icon' => 'fa-calendar-check',
        ];
    }
@endphp

<section class="bg-slate-50 py-6 sm:py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-5 lg:gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-5 sm:space-y-6">
        <div class="product-show__hero rounded-3xl border border-slate-200 p-4 shadow-sm sm:p-5 lg:p-6">
          <div class="relative z-[1] flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 space-y-3">
              <span class="inline-flex w-fit items-center rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-700 shadow-sm backdrop-blur">
                Listing Overview
              </span>
              <div>
                <h1 class="break-words text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $product->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600 sm:text-base">Manage this listing details, visibility, renewal, and launch readiness from one place.</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadge }}">
                  <i class="fas fa-circle mr-1 text-[9px]"></i>{{ $statusLabel }}
                </span>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/85 px-3 py-1 text-xs font-semibold text-slate-700">
                  <i class="fas fa-box mr-1 text-slate-400"></i>{{ ucfirst((string) $product->type) }}
                </span>
                @if($product->type === 'digital')
                  <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                    <i class="fas fa-cloud-arrow-down mr-1"></i>{{ $product->digitalFiles->count() }} delivery asset{{ $product->digitalFiles->count() === 1 ? '' : 's' }}
                  </span>
                @endif
                @if($product->type === 'physical' && !is_null($product->stock))
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/85 px-3 py-1 text-xs font-semibold text-slate-700">
                    <i class="fas fa-layer-group mr-1 text-slate-400"></i>Stock {{ $product->stock }}
                  </span>
                @endif
              </div>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:justify-end">
              <a target="_blank" href="{{ route('listing.show', $product->slug) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-emerald-600 bg-white/85 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 sm:w-auto">
                <i class="fas fa-external-link-alt mr-2"></i> View Public Listing
              </a>
              <a href="{{ route('products.index') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 bg-white/75 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">
                <i class="fas fa-arrow-left mr-2"></i> Back to Listings
              </a>
            </div>
          </div>
        </div>

        @include('products.partials.edit-tabs', ['product' => $product, 'current' => $current])

        @if(session('success'))
          <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">{{ session('success') }}</div>
        @endif

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1.08fr)_minmax(19rem,0.92fr)]">
          <div class="space-y-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
              <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Listing Preview</h2>
                  <p class="text-sm text-slate-500">See how your media is presented before buyers open the public listing.</p>
                </div>
                @if($mediaItems->count() > 1)
                  <span class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ $mediaItems->count() }} media items
                  </span>
                @endif
              </div>

              @if($mediaItems->count())
                <div x-data="{ active: 0, total: {{ $mediaItems->count() }} }" class="space-y-4">
                  <div class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-100">
                    <div class="absolute left-3 top-3 z-10 inline-flex items-center rounded-full bg-slate-900/70 px-3 py-1 text-[11px] font-semibold text-white backdrop-blur">
                      <i class="fas fa-eye mr-2"></i>Preview
                    </div>

                    @foreach($mediaItems as $i => $media)
                      <div x-show="active === {{ $i }}" x-transition.opacity.duration.200ms x-cloak class="flex h-[18rem] items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.55),_rgba(255,255,255,0)_45%),linear-gradient(180deg,_#f8fafc_0%,_#e2e8f0_100%)] sm:h-[24rem] lg:h-[30rem]">
                        @if($media->type === 'video')
                          <video controls class="h-full w-full bg-slate-950 object-contain">
                            <source src="{{ media_url($media->url) }}" />
                          </video>
                        @else
                          <img src="{{ media_url($media->url) }}" class="h-full w-full object-contain" alt="{{ $product->name }}">
                        @endif
                      </div>
                    @endforeach

                    @if($mediaItems->count() > 1)
                      <button type="button" @click="active = (active - 1 + total) % total" class="absolute left-3 top-1/2 z-10 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-white/90 text-slate-700 shadow-sm transition hover:bg-white" aria-label="Previous media">
                        <i class="fas fa-chevron-left"></i>
                      </button>
                      <button type="button" @click="active = (active + 1) % total" class="absolute right-3 top-1/2 z-10 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-white/90 text-slate-700 shadow-sm transition hover:bg-white" aria-label="Next media">
                        <i class="fas fa-chevron-right"></i>
                      </button>
                      <div class="absolute bottom-3 left-1/2 z-10 -translate-x-1/2 rounded-full bg-slate-900/70 px-3 py-1 text-[11px] font-semibold text-white backdrop-blur" x-text="`${active + 1} / ${total}`"></div>
                    @endif
                  </div>

                  @if($mediaItems->count() > 1)
                    <div class="product-show__thumbs flex gap-2 overflow-x-auto pb-1">
                      @foreach($mediaItems as $i => $media)
                        <button type="button"
                                @click="active = {{ $i }}"
                                class="group relative flex h-20 w-20 flex-none items-center justify-center overflow-hidden rounded-2xl border bg-slate-100 transition sm:h-24 sm:w-24"
                                :class="active === {{ $i }} ? 'border-emerald-500 ring-2 ring-emerald-100' : 'border-slate-200 hover:border-slate-300'">
                          @if($media->type === 'video')
                            <div class="flex h-full w-full items-center justify-center bg-slate-900 text-white">
                              <i class="fas fa-play"></i>
                            </div>
                          @else
                            <img src="{{ media_url($media->url) }}" class="h-full w-full object-cover" alt="Thumbnail {{ $i + 1 }}">
                          @endif
                        </button>
                      @endforeach
                    </div>
                  @endif
                </div>
              @else
                <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-5 py-12 text-center">
                  <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-sm">
                    <i class="fas fa-images text-lg"></i>
                  </div>
                  <h3 class="mt-4 text-lg font-semibold text-slate-900">No media available</h3>
                  <p class="mt-2 text-sm text-slate-500">Add a featured image or gallery items so your listing looks complete on mobile and desktop.</p>
                  <a href="{{ route('products.media', $product) }}" class="mt-4 inline-flex items-center rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                    <i class="fas fa-upload mr-2"></i> Manage Media
                  </a>
                </div>
              @endif
            </div>

            @if($product->description)
              <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
                  <h3 class="text-lg font-bold text-slate-900">Description</h3>
                  <p class="mt-1 text-sm text-slate-500">This content is what buyers will read on the public listing page.</p>
                </div>
                <div class="px-4 py-4 sm:px-5 sm:py-5">
                  <div class="product-show__richtext rounded-[1.75rem] border border-slate-200 bg-slate-50 p-4 text-sm sm:p-5">
                    {!! $product->description !!}
                  </div>
                </div>
              </div>
            @endif
          </div>

          <div class="space-y-5 xl:sticky xl:top-24 xl:self-start">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                  <h2 class="break-words text-xl font-bold text-slate-900">{{ $product->name }}</h2>
                  <p class="mt-1 text-sm text-slate-500">Review current status, renewal, stock, and launch requirements.</p>
                </div>
                <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadge }}">{{ $statusLabel }}</span>
              </div>

              <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach($summaryStats as $stat)
                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                      <i class="fas {{ $stat['icon'] }} text-slate-400"></i>
                      <span>{{ $stat['label'] }}</span>
                    </div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $stat['value'] }}</div>
                  </div>
                @endforeach
              </div>

              <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('products.details', $product) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">
                  <i class="fas fa-pen mr-2"></i> Edit Details
                </a>
                <a href="{{ route('products.media', $product) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">
                  <i class="fas fa-images mr-2"></i> Manage Media
                </a>
                <a href="{{ route('products.settings', $product) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">
                  <i class="fas fa-gear mr-2"></i> Settings
                </a>
              </div>

              @if($showStatusToggle)
                <form method="POST" action="{{ route('products.changeStatus', $product) }}" class="mt-4">
                  @csrf
                  <input type="hidden" name="status" value="{{ (int) $product->is_active === 1 ? 2 : 1 }}">
                  <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border px-4 py-2.5 text-sm font-semibold transition {{ (int) $product->is_active === 1 ? 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    <i class="fas fa-{{ (int) $product->is_active === 1 ? 'pause' : 'play' }} mr-2"></i>
                    {{ (int) $product->is_active === 1 ? 'Pause Listing' : 'Publish Listing' }}
                  </button>
                </form>
              @endif

              @if($product->type === 'digital')
                <div class="mt-4 rounded-2xl border px-4 py-3 text-sm {{ $product->digitalFiles->count() ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
                  <i class="fas {{ $product->digitalFiles->count() ? 'fa-cloud-check' : 'fa-cloud-arrow-down' }} mr-2"></i>
                  @if($product->digitalFiles->count())
                    {{ $product->digitalFiles->count() }} digital delivery asset{{ $product->digitalFiles->count() === 1 ? '' : 's' }} configured.
                  @else
                    Buyers cannot download this listing yet. Add an uploaded file or external link in the Details tab before publishing.
                  @endif
                </div>
              @endif

              <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <h3 class="text-sm font-semibold text-slate-900">Renewal</h3>
                    <p class="text-xs text-slate-500">Choose how this listing renews after its paid cycle.</p>
                  </div>
                  @if((int) $product->is_active === 1)
                    <form method="POST" action="{{ route('products.updateRenewal', $product) }}" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                      @csrf
                      @method('PATCH')
                      <select name="renewal_type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('renewal_type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror sm:w-auto" onchange="this.form.submit()">
                        <option value="automatic" {{ $product->renewal_type === 'automatic' ? 'selected' : '' }}>Automatic</option>
                        <option value="manual" {{ $product->renewal_type === 'manual' ? 'selected' : '' }}>Manual</option>
                      </select>
                      @error('renewal_type')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </form>
                  @else
                    <span class="inline-flex w-fit rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ ucfirst((string) $product->renewal_type) }}</span>
                  @endif
                </div>
              </div>

              @php
                $baseFee = (float) ($product->category?->listing_fee ?? 0);
                $freq = (int) ($product->category?->listing_frequency ?? 4);
                $freq = in_array($freq, [1, 4], true) ? $freq : 4;
                if ($freq === 1) {
                    $planButtons = [
                        'monthly' => [
                            'label' => 'Monthly',
                            'amount' => max($baseFee, 0),
                        ],
                    ];
                } else {
                    $planButtons = [
                        '4months' => [
                            'label' => '4-Month',
                            'amount' => max($baseFee, 0),
                        ],
                    ];
                }
                $planRequiresPayment = collect($planButtons)->contains(function ($option) {
                    return (float) ($option['amount'] ?? 0) > 0;
                });
              @endphp

              @if((int) $product->is_active === 3)
                <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                  <i class="fas fa-ban mr-2"></i>
                  This listing has been suspended. Please contact the administrator for assistance.
                </div>

              @elseif((int) $product->is_active === 2)
                @if(! $hasPaid)
                  <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    This listing is not live yet. {{ $planRequiresPayment ? 'Pay the fee below' : 'Activate the free plan below' }} to activate it.
                  </div>

                  <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    @foreach($planButtons as $planKey => $option)
                      <form method="POST" action="{{ route('products.pay-fee', $product) }}" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $planKey }}">
                        <button class="inline-flex w-full flex-col items-start rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
                          <span>{{ (float) $option['amount'] > 0 ? 'Pay ' : 'Activate ' }}{{ $option['label'] }}</span>
                          <small>{{ (float) $option['amount'] > 0 ? money($option['amount']) : 'Free' }}</small>
                        </button>
                      </form>
                    @endforeach
                  </div>
                @elseif($isExpired)
                  <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Your subscription expired on {{ $nextDueDate ? $nextDueDate->format('M d, Y') : '-' }}. Renew below to reactivate your listing.
                  </div>

                  <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    @foreach($planButtons as $planKey => $option)
                      <form method="POST" action="{{ route('products.pay-fee', $product) }}" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $planKey }}">
                        <button class="inline-flex w-full flex-col items-start rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
                          <span>Renew {{ $option['label'] }}</span>
                          <small>{{ (float) $option['amount'] > 0 ? money($option['amount']) : 'Free' }}</small>
                        </button>
                      </form>
                    @endforeach
                  </div>
                @else
                  <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                    <i class="fas fa-pause mr-2"></i>
                    This listing is paused. You can publish it again whenever you're ready{{ $nextDueDate ? ' before ' . $nextDueDate->format('M d, Y') : '' }}.
                  </div>
                @endif

              @elseif((int) $product->is_active !== 1)
                @if($hasPaid && $isWithinPaidCycle)
                  @if(! $hasFeatured)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                      <i class="fas fa-exclamation-triangle mr-2"></i>
                      Listing not live yet. Add a featured image, then publish your listing.
                    </div>
                    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                      <a href="{{ route('products.media', $product) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">Add Featured Image</a>
                      <a href="{{ route('products.settings', $product) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">Go to Settings</a>
                    </div>
                  @else
                    <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                      <i class="fas fa-circle-info mr-2"></i>
                      Listing ready to publish. Review settings and publish when ready.
                    </div>
                    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                      <form action="{{ route('products.settings.update', $product) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('Publish this listing now?');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="1">
                        <input type="hidden" name="renewal_type" value="{{ $product->renewal_type ?? 'automatic' }}">
                        @if(!empty($product->visibility))
                          <input type="hidden" name="visibility" value="{{ $product->visibility }}">
                        @endif
                        @if(!empty($product->slug))
                          <input type="hidden" name="slug" value="{{ $product->slug }}">
                        @endif
                        @if(!empty($product->tags))
                          <input type="hidden" name="tags" value="{{ $product->tags }}">
                        @endif
                        <button class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto"><i class="fa-solid fa-check mr-2"></i> Publish Now</button>
                      </form>
                      <a href="{{ route('products.settings', $product) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">Go to Settings</a>
                      <a href="{{ route('products.media', $product) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">Manage Media</a>
                    </div>
                  @endif
                @else
                  <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    This listing is not live yet. {{ $planRequiresPayment ? 'Pay the fee below' : 'Activate the free plan below' }} to activate it.
                  </div>

                  <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    @foreach($planButtons as $planKey => $option)
                      <form method="POST" action="{{ route('products.pay-fee', $product) }}" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $planKey }}">
                        <button class="inline-flex w-full flex-col items-start rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
                          <span>{{ (float) $option['amount'] > 0 ? 'Pay ' : 'Activate ' }}{{ $option['label'] }}</span>
                          <small>{{ (float) $option['amount'] > 0 ? money($option['amount']) : 'Free' }}</small>
                        </button>
                      </form>
                    @endforeach
                  </div>
                @endif
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
