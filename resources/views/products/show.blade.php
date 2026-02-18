{{-- resources/views/products/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name . ' | Product')

@push('styles')
<style>
  /* Sticky tab header */
  .page-header-sticky {
    position: sticky;
    top: 0;        /* adjust if your main navbar is fixed */
    z-index: 1020;
    background: #fff;
    border-bottom: 1px solid rgba(0,0,0,.06);
  }
  /* Horizontal scroll for tabs on small screens */
  .tab-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    white-space: nowrap;
  }
  .tab-scroll .nav-link { border-radius: 999px; }
  .rounded-4, .rounded-top-4 { border-radius: 1rem !important; }
</style>
@endpush

@section('main')
@php
    $current = \Illuminate\Support\Facades\Route::currentRouteName();
@endphp

<div class="content">
  <a target="_blank" href="{{ route('listing.show', $product->slug) }}">View Public </a>


  {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€ Clickable Tabs Header (navigate to pages) â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
  <div class="page-header-sticky">
    <div class="mx-auto w-full px-4 sm:px-6 px-0">
      <div class="tab-scroll px-2 py-2">
        <ul class="nav nav-pills gap-2 flex-nowrap">
          <li class="">
            <a class="nav-link {{ $current === 'products.show' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.show', $product) }}">
              <i class="fa-regular fa-circle-question mr-1"></i> About
            </a>
          </li>

          <li class="">
            <a class="nav-link {{ $current === 'products.pricing' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.pricing', $product) }}">
              <i class="fa-solid fa-tags mr-1"></i> Price & Inventory
            </a>
          </li>

          <li class="">
            <a class="nav-link {{ $current === 'products.variations' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.variations', $product) }}">
              <i class="fa-solid fa-layer-group mr-1"></i> Variations
            </a>
          </li>

          <li class="">
            <a class="nav-link {{ $current === 'products.details' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.details', $product) }}">
              <i class="fa-regular fa-rectangle-list mr-1"></i> Details
            </a>
          </li>

          <li class="">
            <a class="nav-link {{ $current === 'products.shipping' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.shipping', $product) }}">
              <i class="fa-solid fa-truck mr-1"></i> Shipping
            </a>
          </li>

          {{-- NEW: Media tab --}}
          <li class="">
            <a class="nav-link {{ $current === 'products.media' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.media', $product) }}">
              <i class="fa-regular fa-images mr-1"></i> Media
            </a>
          </li>

          <li class="">
            <a class="nav-link {{ $current === 'products.settings' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.settings', $product) }}">
              <i class="fa-solid fa-gear mr-1"></i> Settings
            </a>
          </li>


        
        </ul>
      </div>
    </div>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 alert-dismissible rounded-3 mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€ ABOUT PAGE CONTENT (this view is the About page) â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
  <div class="grid grid-cols-12 gap-4 gap-x-5 gap-y-4 mt-2">
    {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€ Media Carousel â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="lg:col-span-6">
      @if($product->media->count())
        <div id="productCarousel" class="carousel slide rounded-4 shadow-sm border" data-bs-ride="carousel">
          <div class="carousel-inner">
            @foreach($product->media as $i => $media)
              <div class="carousel-item @if($i===0) active @endif">
                @if($media->type === 'video')
                  <video controls class="block w-full rounded-4" style="height:400px; object-fit:cover;">
                    <source src="{{ asset('storage/'.$media->url) }}" />
                  </video>
                @else
                  <img src="{{ asset('storage/'.$media->url) }}"
                       class="block w-full rounded-4"
                       style="height:400px; object-fit:cover;"
                       alt="{{ $product->name }}">
                @endif
              </div>
            @endforeach
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
          </button>
        </div>
      @else
        <div class="flex items-center justify-center border rounded-4 text-slate-500" style="height:400px;">
          No media available
        </div>
      @endif
    </div>

    {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€ Product Details â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="lg:col-span-6 flex flex-col">
      {{-- Title & Status Badge --}}
      <div class="flex justify-between items-center mb-3">
        <h2 class="mb-0">{{ $product->name }}</h2>
        @php
          switch($product->is_active) {
            case 0: $label='Pending'; $class='warning'; break;
            case 1: $label='Active';  $class='success'; break;
            case 2: $label='Paused';  $class='secondary'; break;
            case 3: $label='Suspended';  $class='secondary'; break;
            default:$label='Closed';  $class='dark'; break;
          }
        @endphp
        <span class="badge bg-{{ $class }} text-uppercase">{{ $label }}</span>
      </div>

      {{-- Pause / Publish Toggle --}}
      @if(in_array($product->is_active, [1,2]))
        <form method="POST" action="{{ route('products.changeStatus', $product) }}" class="mb-3">
          @csrf
          <input type="hidden" name="status" value="{{ $product->is_active === 1 ? 2 : 1 }}">
          <button type="submit" class="btn 
            @if($product->is_active === 1) btn-outline-warning 
            @else btn-outline-success 
            @endif
          ">
            <i class="fas fa-{{ $product->is_active===1 ? 'pause' : 'play' }} me-1"></i>
            {{ $product->is_active===1 ? 'Pause' : 'Publish' }}
          </button>
        </form>
      @endif

      {{-- Type (price removed per request) --}}
      <p class="mb-3 text-slate-500">
        <i class="fas fa-box mr-1"></i>
        <strong>Type:</strong> {{ ucfirst($product->type) }}
      </p>

      {{-- Listing Dates --}}
      @if($product->is_active === 1)
        <ul class="list-unstyled mb-3 text-slate-500 text-xs">
            <li>
            <i class="fas fa-calendar-plus mr-1"></i>
            <strong>Listing id:</strong>{{ $product->id }}
          </li>
          <li>
            <i class="fas fa-calendar-plus mr-1"></i>
            <strong>Listed on:</strong>
            {{ \Carbon\Carbon::parse($product->listing_paid_at)->toDayDateTimeString() }}
          </li>
          <li>
            <i class="fas fa-calendar-check mr-1"></i>
            <strong>Next due:</strong>
            {{ \Carbon\Carbon::parse($product->next_due_date)->toFormattedDateString() }}
          </li>
        </ul>
      @endif

      {{-- Renewal Type (toggle only when active) --}}
      <div class="mb-4">
        <strong>Renewal:</strong>
        @if($product->is_active === 1)
          <form method="POST" action="{{ route('products.updateRenewal', $product) }}"
                class="inline-flex items-center">
            @csrf
            @method('PATCH')
            <select name="renewal_type"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs mr-2 @error('renewal_type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    onchange="this.form.submit()">
              <option value="automatic" {{ $product->renewal_type === 'automatic' ? 'selected' : '' }}>
                Automatic
              </option>
              <option value="manual" {{ $product->renewal_type === 'manual' ? 'selected' : '' }}>
                Manual
              </option>
            </select>
            @error('renewal_type')
              <div class="mt-1 text-xs text-rose-600 block">{{ $message }}</div>
            @enderror
          </form>
        @else
          <span class="text-slate-500">{{ ucfirst($product->renewal_type) }}</span>
        @endif
      </div>

      {{-- Stock --}}
      @if(! is_null($product->stock))
        <p class="mb-3">
          <i class="fas fa-layer-group mr-1"></i>
          <strong>Stock:</strong> {{ $product->stock }}
        </p>
      @endif

      {{-- Listing Fee / Renewal / Suspension Prompt --}}
      @php
        use Carbon\Carbon;

        $baseFee      = (float) ($product->category?->listing_fee ?? 0);
        $freq         = (int) ($product->category?->listing_frequency ?? 4);
        $freq         = in_array($freq, [1,4], true) ? $freq : 4;
        // Only offer the plan matching the category frequency
        if ($freq === 1) {
          $planButtons = [
            'monthly' => [
              'label' => 'Monthly',
              'class' => 'btn-success',
              'amount' => max($baseFee, 0),
            ],
          ];
        } else {
          $planButtons = [
            '4months' => [
              'label' => '4-Month',
              'class' => 'btn-success',
              'amount' => max($baseFee, 0),
            ],
          ];
        }
      @endphp

      @if($product->is_active === 3)
        {{-- Suspended --}}
        <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 flex items-center mb-4">
          <i class="fas fa-ban mr-2"></i>
          This listing has been suspended. Please contact the administrator for assistance.
        </div>

      @elseif($product->is_active === 2)
        {{-- Paused --}}
        @if($product->next_due_date && Carbon::parse($product->next_due_date)->lte(Carbon::now()))
          {{-- Subscription expired: allow renewal --}}
          <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 flex items-center mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Your subscription expired on {{ Carbon::parse($product->next_due_date)->format('M d, Y') }}. Renew below to reactivate your listing.
          </div>

          @php $actionPrefix = 'Renew'; @endphp

          <div class="flex flex-wrap gap-2 mb-4">
            @foreach($planButtons as $planKey => $option)
              <form method="POST" action="{{ route('products.pay-fee', $product) }}">
                @csrf
                <input type="hidden" name="plan" value="{{ $planKey }}">
                <button class="btn {{ $option['class'] }}">
                  {{ $actionPrefix }} {{ $option['label'] }}<br>
                  <small>{{ money($option['amount']) }}</small>
                </button>
              </form>
            @endforeach
          </div>
        @else
          {{-- Paused but not yet due --}}
          <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 flex items-center mb-4">
            <i class="fas fa-pause mr-2"></i>
            This listing is paused. It will automatically become eligible for renewal on {{ Carbon::parse($product->next_due_date)->format('M d, Y') }}.
          </div>
        @endif

      @elseif($product->is_active !== 1)
        {{-- Not active / pending --}}
        @php
          $hasPaid     = !empty($product->listing_paid_at);
          $isDueSoon   = $product->next_due_date && !\Carbon\Carbon::parse($product->next_due_date)->isFuture();
          $dueFuture   = $product->next_due_date && \Carbon\Carbon::parse($product->next_due_date)->isFuture();
          $hasFeatured = !empty($product->featured_image)
                        || ($product->media && $product->media->count() > 0);
        @endphp

        @if($hasPaid && $dueFuture)
          @if(! $hasFeatured)
            <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 flex items-center mb-3">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              Listing not live yet. Add a featured image, then publish your listing.
            </div>
            <div class="flex flex-wrap gap-2 mb-4">
              <a href="{{ route('products.media', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Add Featured Image</a>
              <a href="{{ route('products.settings', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Go to Settings</a>
            </div>
          @else
            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 flex items-center mb-3">
              <i class="fas fa-circle-info mr-2"></i>
              Listing ready to publish. Review settings and publish when you're ready.
            </div>
            <div class="flex flex-wrap gap-2 mb-4">
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
                <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                  <i class="fa-solid fa-check mr-1"></i> Publish Now
                </button>
              </form>
              <a href="{{ route('products.settings', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Go to Settings</a>
              <a href="{{ route('products.media', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Manage Media</a>
            </div>
          @endif
        @else
          <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 flex items-center mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            This listing isnâ€™t live yet. Pay the fee below to activate it.
          </div>

          @php $actionPrefix = 'Pay'; @endphp

          <div class="flex flex-wrap gap-2 mb-4">
            @foreach($planButtons as $planKey => $option)
              <form method="POST" action="{{ route('products.pay-fee', $product) }}">
                @csrf
                <input type="hidden" name="plan" value="{{ $planKey }}">
                <button class="btn {{ $option['class'] }}">
                  {{ $actionPrefix }} {{ $option['label'] }}<br>
                  <small>{{ money($option['amount']) }}</small>
                </button>
              </form>
            @endforeach
          </div>
        @endif
      @endif

      {{-- Action Links --}}
      <div class="mt-auto">
      
        <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-900 text-slate-900 hover:bg-slate-100">
          <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
      </div>
    </div>
  </div>

  {{-- Description --}}
  @if($product->description)
    <div class="mt-5">
      <h5>Description</h5>
      <div class="p-4 bg-slate-100 border rounded">
        {!! $product->description !!}
      </div>
    </div>
  @endif

</div>
@endsection



