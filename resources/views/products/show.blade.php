@extends('layouts.app')

@section('content')
<div class="content">
  <div class="row gx-5 gy-4">
    {{-- ───────── Image Carousel ───────── --}}
    <div class="col-lg-6">
      @if($product->media->count())
        <div id="productCarousel" class="carousel slide rounded-4 shadow-sm border" data-bs-ride="carousel">
          <div class="carousel-inner">
            @foreach($product->media as $i => $media)
              <div class="carousel-item @if($i===0) active @endif">
                <img src="{{ asset('storage/'.$media->url) }}"
                     class="d-block w-100 rounded-4"
                     style="height:400px; object-fit:cover;"
                     alt="{{ $product->name }}">
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
          No image available
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

      {{-- Type & Price --}}
      <p class="mb-2 text-muted">
        <i class="fas fa-box me-1"></i>
        <strong>Type:</strong> {{ ucfirst($product->type) }}
      </p>
      <div class="mb-3">
        @php
          $basePrice  = $product->price;
          $finalPrice = $product->discounted_price;
        @endphp

        @if($finalPrice < $basePrice)
          <div class="d-flex align-items-baseline gap-3 mb-3">
            <span class="fw-bold text-success">
              {{ get_currency() }} {{ number_format($finalPrice, 2) }}
            </span>
            <span class="text-muted text-decoration-line-through">
              {{ get_currency() }} {{ number_format($basePrice, 2) }}
            </span>
          </div>
        @else
          <p class="fw-bold text-success mb-3">
            {{ get_currency() }} {{ number_format($basePrice, 2) }}
          </p>
        @endif
      </div>

      {{-- Listing Dates --}}
      @if($product->is_active === 1)
        <ul class="list-unstyled mb-3 text-muted small">
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

      {{-- Listing Fee Prompt --}}
{{-- Listing Fee / Renewal / Suspension Prompt --}}
@php
    use Carbon\Carbon;
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

        @php
            $baseFee    = $product->category?->listing_fee ?? 0;
            $monthlyFee = $baseFee / 3;
        @endphp

        <div class="d-flex gap-2 mb-4">
            <form method="POST" action="{{ route('products.pay-fee', $product) }}">
                @csrf
                <input type="hidden" name="plan" value="monthly">
                <button class="btn btn-outline-success">
                    Renew Monthly<br>
                    <small>{{ get_currency() }}{{ number_format($monthlyFee,2) }}</small>
                </button>
            </form>
            <form method="POST" action="{{ route('products.pay-fee', $product) }}">
                @csrf
                <input type="hidden" name="plan" value="4months">
                <button class="btn btn-success">
                    Renew 4‑Month<br>
                    <small>{{ get_currency() }}{{ number_format($baseFee,2) }}</small>
                </button>
            </form>
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
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        This listing isn’t live yet. Pay the fee below to activate it.
    </div>

    @php
        $baseFee    = $product->category?->listing_fee ?? 0;
        $monthlyFee = $baseFee / 3;
    @endphp

    <div class="d-flex gap-2 mb-4">
        <form method="POST" action="{{ route('products.pay-fee', $product) }}">
            @csrf
            <input type="hidden" name="plan" value="monthly">
            <button class="btn btn-outline-success">
                Pay Monthly<br>
                <small>{{ get_currency() }}{{ number_format($monthlyFee,2) }}</small>
            </button>
        </form>
        <form method="POST" action="{{ route('products.pay-fee', $product) }}">
            @csrf
            <input type="hidden" name="plan" value="4months">
            <button class="btn btn-success">
                Pay 4‑Month<br>
                <small>{{ get_currency() }}{{ number_format($baseFee,2) }}</small>
            </button>
        </form>
    </div>
@endif


      {{-- Action Links --}}
      <div class="mt-auto">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary me-2">
          <i class="fas fa-edit me-1"></i> Edit
        </a>
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
