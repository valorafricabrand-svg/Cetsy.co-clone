@extends('layouts.app')

@section('content')
<div class="content">

  {{-- Header: Logo + Name + Edit Button --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
      @if($shop->logo_url)
        <img
          src="{{ $shop->logo_url }}"
          alt="{{ $shop->name }} logo"
          class="rounded-circle me-3"
          style="width: 64px; height: 64px; object-fit: cover;"
        >
      @endif
      <div>
        <h1 class="h3 mb-0">{{ $shop->name }}</h1>
        <small class="text-muted">Owned by {{ $shop->user->name }}</small>
      </div>
    </div>
    {{-- View Payment Methods --}}
    <a href="{{ route('seller.payment-methods.index') }}" class="btn btn-outline-">
      <i class="fas fa-credit-card me-1"></i> Payment Methods
    </a>
    {{-- Edit button: only the owner --}}
    @if(Auth::id() === $shop->user_id)
      <a href="{{ route('seller.shops.edit', $shop) }}" class="btn btn-primary">
        <i class="fas fa-edit me-1"></i> Edit Shop
      </a>
    @endif
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  {{-- Bio --}}
  @if($shop->bio)
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">About This Shop</h5>
        <p class="card-text">{{ $shop->bio }}</p>
      </div>
    </div>
  @endif

  {{-- Preferences --}}
  <div class="card mb-4">
    <div class="card-header">Preferences</div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 mb-3">
          <strong>Language</strong><br>
          <span class="text-muted">{{ $shop->language }}</span>
        </div>
        <div class="col-md-6 mb-3">
          <strong>Country</strong><br>
          <span class="text-muted">{{ $shop->country }}</span>
        </div>
        <div class="col-md-6 mb-3">
          <strong>Currency</strong><br>
          <span class="text-muted">{{ $shop->currency }}</span>
        </div>
        <div class="col-md-6 mb-3">
          <strong>Shop URL</strong><br>
          <a href="{{ route('seller.shops.show', $shop) }}" class="text-success">
            {{ url('shop/' . $shop->slug) }}
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Payment Details --}}
  <div class="card mb-4">
    <div class="card-header">Payment Details</div>
    <div class="card-body">
      <div class="row">
        @foreach($paymentMethods as $paymentMethod)
        <div class="col-md-6 mb-3">
          <strong>{{ $paymentMethod->paymentType->name }}</strong><br>
          <span class="text-muted">{{ $paymentMethod->account_number }}</span>
        </div>
        <div class="col-md-6 mb-3">
            <strong>Account Name</strong><br>
            <span class="text-muted">{{ $paymentMethod->account_name }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Billing Address --}}
  <div class="card mb-4">
    <div class="card-header">Billing Address</div>
    <div class="card-body">
      <p class="mb-1">{{ $shop->address }}</p>
      <p class="mb-0">{{ $shop->city }}, {{ $shop->postal }}</p>
    </div>
  </div>

  {{-- Security --}}
  <div class="card mb-4">
    <div class="card-body d-flex align-items-center">
      <i class="fas fa-lock fa-lg text-success me-3"></i>
      <div>
        Two-Factor Authentication:
        @if($shop->enable_2fa)
          <span class="badge bg-success">Enabled</span>
        @else
          <span class="badge bg-danger">Disabled</span>
        @endif
      </div>
    </div>
  </div>

  {{-- Placeholder for future listings --}}
  <div class="text-center text-muted">
    (Product listings will appear here soon…)
  </div>

</div>
@endsection
