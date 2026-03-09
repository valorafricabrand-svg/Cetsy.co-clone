{{-- resources/views/products/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name . ' | Product')

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
@endphp

<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $product->name }}</h1>
              <p class="mt-1 text-sm text-slate-500">Manage this listing details, visibility, and renewal status.</p>
            </div>
            <a target="_blank" href="{{ route('listing.show', $product->slug) }}" class="inline-flex items-center rounded-xl border border-emerald-600 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
              <i class="fas fa-external-link-alt mr-2"></i> View Public Listing
            </a>
          </div>
        </div>

        <div class="sticky top-16 z-20 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
          <div class="overflow-x-auto">
            <div class="flex min-w-max gap-2">
              <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.show' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.show', $product) }}"><i class="fa-regular fa-circle-question mr-1"></i> About</a>
              <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.pricing' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.pricing', $product) }}"><i class="fa-solid fa-tags mr-1"></i> Price & Inventory</a>
              <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.variations' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.variations', $product) }}"><i class="fa-solid fa-layer-group mr-1"></i> Variations</a>
              <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.details' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.details', $product) }}"><i class="fa-regular fa-rectangle-list mr-1"></i> Details</a>
              <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.shipping' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.shipping', $product) }}"><i class="fa-solid fa-truck mr-1"></i> Shipping</a>
              <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.media' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.media', $product) }}"><i class="fa-regular fa-images mr-1"></i> Media</a>
              <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.settings' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.settings', $product) }}"><i class="fa-solid fa-gear mr-1"></i> Settings</a>
            </div>
          </div>
        </div>

        @if(session('success'))
          <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
          <div>
            @if($product->media->count())
              <div x-data="{ active: 0, total: {{ $product->media->count() }} }" class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="relative overflow-hidden rounded-xl bg-slate-100">
                  @foreach($product->media as $i => $media)
                    <div x-show="active === {{ $i }}" x-cloak>
                      @if($media->type === 'video')
                        <video controls class="h-[26rem] w-full object-cover">
                          <source src="{{ asset('storage/'.$media->url) }}" />
                        </video>
                      @else
                        <img src="{{ asset('storage/'.$media->url) }}" class="h-[26rem] w-full object-cover" alt="{{ $product->name }}">
                      @endif
                    </div>
                  @endforeach

                  @if($product->media->count() > 1)
                    <button type="button" @click="active = (active - 1 + total) % total" class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 px-2 py-1 text-slate-700 shadow hover:bg-white" aria-label="Previous media">
                      <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" @click="active = (active + 1) % total" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 px-2 py-1 text-slate-700 shadow hover:bg-white" aria-label="Next media">
                      <i class="fas fa-chevron-right"></i>
                    </button>
                  @endif
                </div>

                @if($product->media->count() > 1)
                  <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach($product->media as $i => $media)
                      <button type="button" @click="active = {{ $i }}" class="overflow-hidden rounded-lg border border-slate-200">
                        @if($media->type === 'video')
                          <div class="flex h-16 items-center justify-center bg-slate-900 text-white"><i class="fas fa-play"></i></div>
                        @else
                          <img src="{{ asset('storage/'.$media->url) }}" class="h-16 w-full object-cover" alt="thumb">
                        @endif
                      </button>
                    @endforeach
                  </div>
                @endif
              </div>
            @else
              <div class="flex h-[26rem] items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm">No media available</div>
            @endif
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between gap-3">
              <h2 class="text-xl font-bold text-slate-900">{{ $product->name }}</h2>
              @php
                switch($effectiveStatus) {
                  case 0: $label='Pending'; $badge='border-amber-200 bg-amber-100 text-amber-800'; break;
                  case 1: $label='Active'; $badge='border-emerald-200 bg-emerald-100 text-emerald-800'; break;
                  case 2: $label='Paused'; $badge='border-slate-200 bg-slate-100 text-slate-700'; break;
                  case 3: $label='Suspended'; $badge='border-slate-200 bg-slate-100 text-slate-700'; break;
                  default: $label='Closed'; $badge='border-slate-300 bg-slate-200 text-slate-800'; break;
                }
              @endphp
              <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $label }}</span>
            </div>

            @if($showStatusToggle)
              <form method="POST" action="{{ route('products.changeStatus', $product) }}" class="mb-4">
                @csrf
                <input type="hidden" name="status" value="{{ (int)$product->is_active === 1 ? 2 : 1 }}">
                <button type="submit" class="inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold {{ (int)$product->is_active === 1 ? 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                  <i class="fas fa-{{ (int)$product->is_active===1 ? 'pause' : 'play' }} mr-1"></i>
                  {{ (int)$product->is_active===1 ? 'Pause' : 'Publish' }}
                </button>
              </form>
            @endif

            <p class="mb-2 text-sm text-slate-600"><i class="fas fa-box mr-1"></i><strong>Type:</strong> {{ ucfirst($product->type) }}</p>

            @if($product->type === 'digital')
              <div class="mb-4 rounded-xl border px-4 py-3 text-sm {{ $product->digitalFiles->count() ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
                <i class="fas {{ $product->digitalFiles->count() ? 'fa-cloud-check' : 'fa-cloud-arrow-down' }} mr-2"></i>
                @if($product->digitalFiles->count())
                  {{ $product->digitalFiles->count() }} digital delivery asset{{ $product->digitalFiles->count() === 1 ? '' : 's' }} configured.
                @else
                  Buyers cannot download this listing yet. Add an uploaded file or external link in the Details tab before publishing.
                @endif
              </div>
            @endif

            @if((int)$product->is_active === 1)
              <ul class="mb-4 space-y-1 text-xs text-slate-500">
                <li><i class="fas fa-hashtag mr-1"></i><strong>Listing ID:</strong> {{ $product->id }}</li>
                <li><i class="fas fa-calendar-plus mr-1"></i><strong>Listed on:</strong> {{ $product->listing_paid_at ? \Carbon\Carbon::parse($product->listing_paid_at)->toDayDateTimeString() : '-' }}</li>
                <li><i class="fas fa-calendar-check mr-1"></i><strong>Next due:</strong> {{ $product->next_due_date ? \Carbon\Carbon::parse($product->next_due_date)->toFormattedDateString() : '-' }}</li>
              </ul>
            @endif

            <div class="mb-4 text-sm">
              <strong>Renewal:</strong>
              @if((int)$product->is_active === 1)
                <form method="POST" action="{{ route('products.updateRenewal', $product) }}" class="inline-flex items-center gap-2">
                  @csrf
                  @method('PATCH')
                  <select name="renewal_type" class="rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('renewal_type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" onchange="this.form.submit()">
                    <option value="automatic" {{ $product->renewal_type === 'automatic' ? 'selected' : '' }}>Automatic</option>
                    <option value="manual" {{ $product->renewal_type === 'manual' ? 'selected' : '' }}>Manual</option>
                  </select>
                  @error('renewal_type')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                </form>
              @else
                <span class="text-slate-500">{{ ucfirst($product->renewal_type) }}</span>
              @endif
            </div>

            @if($product->type === 'physical' && ! is_null($product->stock))
              <p class="mb-4 text-sm text-slate-600"><i class="fas fa-layer-group mr-1"></i><strong>Stock:</strong> {{ $product->stock }}</p>
            @endif

            @php
              $baseFee      = (float) ($product->category?->listing_fee ?? 0);
              $freq         = (int) ($product->category?->listing_frequency ?? 4);
              $freq         = in_array($freq, [1,4], true) ? $freq : 4;
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
            @endphp

            @if((int)$product->is_active === 3)
              <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <i class="fas fa-ban mr-2"></i>
                This listing has been suspended. Please contact the administrator for assistance.
              </div>

            @elseif((int)$product->is_active === 2)
              @if(! $hasPaid)
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                  <i class="fas fa-exclamation-triangle mr-2"></i>
                  This listing is not live yet. Pay the fee below to activate it.
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                  @foreach($planButtons as $planKey => $option)
                    <form method="POST" action="{{ route('products.pay-fee', $product) }}">
                      @csrf
                      <input type="hidden" name="plan" value="{{ $planKey }}">
                      <button class="inline-flex flex-col items-start rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                        <span>Pay {{ $option['label'] }}</span>
                        <small>{{ money($option['amount']) }}</small>
                      </button>
                    </form>
                  @endforeach
                </div>
              @elseif($isExpired)
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                  <i class="fas fa-exclamation-triangle mr-2"></i>
                  Your subscription expired on {{ $nextDueDate ? $nextDueDate->format('M d, Y') : '-' }}. Renew below to reactivate your listing.
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                  @foreach($planButtons as $planKey => $option)
                    <form method="POST" action="{{ route('products.pay-fee', $product) }}">
                      @csrf
                      <input type="hidden" name="plan" value="{{ $planKey }}">
                      <button class="inline-flex flex-col items-start rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                        <span>Renew {{ $option['label'] }}</span>
                        <small>{{ money($option['amount']) }}</small>
                      </button>
                    </form>
                  @endforeach
                </div>
              @else
                <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                  <i class="fas fa-pause mr-2"></i>
                  This listing is paused. You can publish it again whenever you're ready{{ $nextDueDate ? ' before ' . $nextDueDate->format('M d, Y') : '' }}.
                </div>
              @endif

            @elseif((int)$product->is_active !== 1)
              @php
                $dueFuture = $isWithinPaidCycle;
              @endphp

              @if($hasPaid && $dueFuture)
                @if(! $hasFeatured)
                  <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Listing not live yet. Add a featured image, then publish your listing.
                  </div>
                  <div class="mb-4 flex flex-wrap gap-2">
                    <a href="{{ route('products.media', $product) }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">Add Featured Image</a>
                    <a href="{{ route('products.settings', $product) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Go to Settings</a>
                  </div>
                @else
                  <div class="mb-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                    <i class="fas fa-circle-info mr-2"></i>
                    Listing ready to publish. Review settings and publish when ready.
                  </div>
                  <div class="mb-4 flex flex-wrap gap-2">
                    <form action="{{ route('products.settings.update', $product) }}" method="POST" onsubmit="return confirm('Publish this listing now?');">
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
                      <button class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500"><i class="fa-solid fa-check mr-1"></i> Publish Now</button>
                    </form>
                    <a href="{{ route('products.settings', $product) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Go to Settings</a>
                    <a href="{{ route('products.media', $product) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Manage Media</a>
                  </div>
                @endif
              @else
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                  <i class="fas fa-exclamation-triangle mr-2"></i>
                  This listing is not live yet. Pay the fee below to activate it.
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                  @foreach($planButtons as $planKey => $option)
                    <form method="POST" action="{{ route('products.pay-fee', $product) }}">
                      @csrf
                      <input type="hidden" name="plan" value="{{ $planKey }}">
                      <button class="inline-flex flex-col items-start rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                        <span>Pay {{ $option['label'] }}</span>
                        <small>{{ money($option['amount']) }}</small>
                      </button>
                    </form>
                  @endforeach
                </div>
              @endif
            @endif

            <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
              <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
          </div>
        </div>

        @if($product->description)
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-lg font-bold text-slate-900">Description</h3>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
              {!! $product->description !!}
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
