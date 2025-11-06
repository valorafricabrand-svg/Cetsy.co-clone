{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')

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

@section('content')
@php
    $current = \Illuminate\Support\Facades\Route::currentRouteName();
@endphp

<div class="content">
  <a target="_blank" href="{{ route('listing.show', $product->slug) }}">View Public </a>


  {{-- ───────── Clickable Tabs Header (navigate to pages) ───────── --}}
  <div class="page-header-sticky">
    <div class="container-fluid px-0">
      <div class="tab-scroll px-2 py-2">
        <ul class="nav nav-pills gap-2 flex-nowrap">
          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.show' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.show', $product) }}">
              <i class="fa-regular fa-circle-question me-1"></i> About
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.pricing' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.pricing', $product) }}">
              <i class="fa-solid fa-tags me-1"></i> Price & Inventory
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.variations' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.variations', $product) }}">
              <i class="fa-solid fa-layer-group me-1"></i> Variations
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.details' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.details', $product) }}">
              <i class="fa-regular fa-rectangle-list me-1"></i> Details
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.shipping' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.shipping', $product) }}">
              <i class="fa-solid fa-truck me-1"></i> Shipping
            </a>
          </li>

          {{-- NEW: Media tab --}}
          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.media' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.media', $product) }}">
              <i class="fa-regular fa-images me-1"></i> Media
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.settings' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.settings', $product) }}">
              <i class="fa-solid fa-gear me-1"></i> Settings
            </a>
          </li>


        
        </ul>
      </div>
    </div>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- ───────── ABOUT PAGE CONTENT (this view is the About page) ───────── --}}
  <div class="row gx-5 gy-4 mt-2">
    {{-- ───────── Media Carousel ───────── --}}
    <div class="col-lg-6">
      @if($product->media->count())
        <div id="productCarousel" class="carousel slide rounded-4 shadow-sm border" data-bs-ride="carousel">
          <div class="carousel-inner">
            @foreach($product->media as $i => $media)
              <div class="carousel-item @if($i===0) active @endif">
                @if($media->type === 'video')
                  <video controls class="d-block w-100 rounded-4" style="height:400px; object-fit:cover;">
                    <source src="{{ asset('storage/'.$media->url) }}" />
                  </video>
                @else
                  <img src="{{ asset('storage/'.$media->url) }}"
                       class="d-block w-100 rounded-4"
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
        <div class="d-flex align-items-center justify-content-center border rounded-4 text-muted" style="height:400px;">
          No media available
        </div>
      @endif
    </div>

    {{-- ───────── Product Details ───────── --}}
    <div class="col-lg-6 d-flex flex-column">
      {{-- Title & Status Badge --}}
      <div class="d-flex justify-content-between align-items-center mb-3">
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
      <p class="mb-3 text-muted">
        <i class="fas fa-box me-1"></i>
        <strong>Type:</strong> {{ ucfirst($product->type) }}
      </p>

      {{-- Listing Dates --}}
      @if($product->is_active === 1)
        <ul class="list-unstyled mb-3 text-muted small">
            <li>
            <i class="fas fa-calendar-plus me-1"></i>
            <strong>Listing id:</strong>{{ $product->id }}
          </li>
          <li>
            <i class="fas fa-calendar-plus me-1"></i>
            <strong>Listed on:</strong>
            {{ \Carbon\Carbon::parse($product->listing_paid_at)->toDayDateTimeString() }}
          </li>
          <li>
            <i class="fas fa-calendar-check me-1"></i>
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
                class="d-inline-flex align-items-center">
            @csrf
            @method('PATCH')
            <select name="renewal_type"
                    class="form-select form-select-sm me-2 @error('renewal_type') is-invalid @enderror"
                    onchange="this.form.submit()">
              <option value="automatic" {{ $product->renewal_type === 'automatic' ? 'selected' : '' }}>
                Automatic
              </option>
              <option value="manual" {{ $product->renewal_type === 'manual' ? 'selected' : '' }}>
                Manual
              </option>
            </select>
            @error('renewal_type')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </form>
        @else
          <span class="text-muted">{{ ucfirst($product->renewal_type) }}</span>
        @endif
      </div>

      {{-- Stock --}}
      @if(! is_null($product->stock))
        <p class="mb-3">
          <i class="fas fa-layer-group me-1"></i>
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
        <div class="alert alert-danger d-flex align-items-center mb-4">
          <i class="fas fa-ban me-2"></i>
          This listing has been suspended. Please contact the administrator for assistance.
        </div>

      @elseif($product->is_active === 2)
        {{-- Paused --}}
        @if($product->next_due_date && Carbon::parse($product->next_due_date)->lte(Carbon::now()))
          {{-- Subscription expired: allow renewal --}}
          <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Your subscription expired on {{ Carbon::parse($product->next_due_date)->format('M d, Y') }}. Renew below to reactivate your listing.
          </div>

          @php $actionPrefix = 'Renew'; @endphp

          <div class="d-flex flex-wrap gap-2 mb-4">
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
          <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="fas fa-pause me-2"></i>
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
            <div class="alert alert-warning d-flex align-items-center mb-3">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Listing not live yet. Add a featured image, then publish your listing.
            </div>
            <div class="d-flex flex-wrap gap-2 mb-4">
              <a href="{{ route('products.media', $product) }}" class="btn btn-primary">Add Featured Image</a>
              <a href="{{ route('products.settings', $product) }}" class="btn btn-outline-secondary">Go to Settings</a>
            </div>
          @else
            <div class="alert alert-info d-flex align-items-center mb-3">
              <i class="fas fa-circle-info me-2"></i>
              Listing ready to publish. Review settings and publish when you're ready.
            </div>
            <div class="d-flex flex-wrap gap-2 mb-4">
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
                <button class="btn btn-success">
                  <i class="fa-solid fa-check me-1"></i> Publish Now
                </button>
              </form>
              <a href="{{ route('products.settings', $product) }}" class="btn btn-primary">Go to Settings</a>
              <a href="{{ route('products.media', $product) }}" class="btn btn-outline-secondary">Manage Media</a>
            </div>
          @endif
        @else
          <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            This listing isn’t live yet. Pay the fee below to activate it.
          </div>

          @php $actionPrefix = 'Pay'; @endphp

          <div class="d-flex flex-wrap gap-2 mb-4">
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
      
        <a href="{{ route('products.index') }}" class="btn btn-outline-dark">
          <i class="fas fa-arrow-left me-1"></i> Back
        </a>
      </div>
    </div>
  </div>

  {{-- Description --}}
  @if($product->description)
    <div class="mt-5">
      <h5>Description</h5>
      <div class="p-4 bg-light border rounded">
        {!! $product->description !!}
      </div>
    </div>
  @endif

</div>
@endsection

