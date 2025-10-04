@extends('layouts.app')

@section('content')

@push('styles')
<style>
  /* Subtle card polish */
  .card { border: 1px solid rgba(0,0,0,.06); border-radius: .85rem; }
  .card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.08); }

  /* Rating bars */
  .rating-bars .progress { height: 8px; background-color: #f1f3f5; border-radius: 999px; }
  .rating-bars .progress-bar { border-radius: 999px; transition: width .35s ease; }

  /* Shop hero */
  .shop-hero {
    position: relative;
    min-height: 160px;
    background: linear-gradient(135deg, #f6f9ff 0%, #eef4ff 100%);
    border-radius: 1rem;
    overflow: hidden;
  }
  .shop-hero::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(0deg, rgba(0,0,0,.18), rgba(0,0,0,0) 45%);
    pointer-events: none;
  }
  .shop-hero-content { position: relative; z-index: 1; }
  .shop-avatar {
    width: 72px; height: 72px; object-fit: cover;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 6px 18px rgba(0,0,0,.15);
  }

  /* Chips */
  .chip { display: inline-flex; align-items:center; gap:.4rem; padding:.275rem .6rem; border-radius:999px; background:#f1f3f5; font-size:.8125rem; }
  .chip i { opacity:.8; }

  /* Helpers */
  .text-shadow-sm { text-shadow: 0 1px 2px rgba(0,0,0,.35); }
</style>
@endpush

<div class="content">

  {{-- Top: Logo + Title + Actions --}}
  <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
    
        <img src="{{ $shop->logo 
      ? asset('storage/' . $shop->logo) 
      : setting('favicon_url') }}" alt="{{ $shop->name }} logo" class="shop-avatar">
    

      <div>
        <h1 class="h4 mb-1">{{ $shop->name }}</h1>

        @if($shop->hasReviews())
          <div class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center">
              @for($i = 1; $i <= 5; $i++)
                @if($i <= $shop->average_rating)
                  <i class="fas fa-star text-warning"></i>
                @elseif($i - $shop->average_rating < 1 && $i - $shop->average_rating > 0)
                  <i class="fas fa-star-half-alt text-warning"></i>
                @else
                  <i class="far fa-star text-muted"></i>
                @endif
              @endfor
            </div>
            <span class="text-muted small">
              {{ number_format($shop->average_rating, 1) }} ({{ $shop->review_count }} {{ Str::plural('review', $shop->review_count) }})
            </span>
          </div>
        @endif
      </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('seller.payment-methods.index') }}" class="btn btn-outline-success">
        <i class="fas fa-credit-card me-2"></i>Payment Methods
      </a>

      <a href="{{ route('seller.products.pricing.bulk') }}" class="btn btn-outline-primary">
        <i class="bi bi-cash-coin me-2"></i>Bulk Edit Prices
      </a>

      @if(Auth::id() === $shop->user_id)
        <a href="{{ route('seller.shops.edit', $shop) }}" class="btn btn-primary">
          <i class="fas fa-edit me-2"></i>Edit Shop
        </a>
      @endif
    </div>
  </div>

  {{-- Holiday Mode Quick Action --}}
  @if(Auth::id() === $shop->user_id)
    <div class="mb-4">
      @if($isHolidayMode)
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#disableHolidayModeModal">
          <i class="fas fa-play me-2"></i>Disable Holiday Mode
        </button>
      @else
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#enableHolidayModeModal">
          <i class="fas fa-umbrella-beach me-2"></i>Enable Holiday Mode
        </button>
      @endif
    </div>
  @endif

  {{-- Announcement --}}
  @if(!empty($shop->announcement))
    <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
      <i class="fas fa-bullhorn mt-1"></i>
      <div class="flex-grow-1">{!! $shop->announcement !!}</div>
    </div>
  @endif

  {{-- Flash --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Featured Image --}}
  @if($shop->featured_image_url ?? $shop->featured_image)
    <div class="mb-4">
      <div class="ratio ratio-21x9 rounded overflow-hidden">
        <img
          src="{{ $shop->featured_image_url ?? asset('storage/' . $shop->featured_image) }}"
          class="w-100 h-100 object-fit-cover"
          alt="{{ $shop->name }} featured image">
      </div>
    </div>
  @endif

  <div class="row g-4">
    <div class="col-lg-8">

      {{-- Shop Rating --}}
      @if($shop->hasReviews())
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span class="d-inline-flex align-items-center gap-2">
              <i class="fas fa-star text-warning"></i>
              <strong>Shop Rating</strong>
            </span>
            <a href="{{ route('shop.reviews', $shop) }}" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-eye me-1"></i>View All Reviews
            </a>
          </div>
          <div class="card-body">
            <div class="row align-items-center gy-3">
              <div class="col-md-4 text-center">
                <div class="mb-2 d-flex justify-content-center">
                  @for($i = 1; $i <= 5; $i++)
                    @if($i <= $shop->average_rating)
                      <i class="fas fa-star text-warning fs-4"></i>
                    @elseif($i - $shop->average_rating < 1 && $i - $shop->average_rating > 0)
                      <i class="fas fa-star-half-alt text-warning fs-4"></i>
                    @else
                      <i class="far fa-star text-muted fs-4"></i>
                    @endif
                  @endfor
                </div>
                <div class="mb-1">
                  <span class="h3 fw-bold text-primary">{{ number_format($shop->average_rating, 1) }}</span>
                  <span class="text-muted">/ 5</span>
                </div>
                <div class="text-muted small">
                  {{ $shop->review_count }} {{ Str::plural('review', $shop->review_count) }}
                </div>
              </div>

              <div class="col-md-8">
                <div class="rating-bars">
                  @for($i = 5; $i >= 1; $i--)
                    @php
                      $percentage = (float)($shop->rating_percentages[$i] ?? 0);
                      $count = (int)($shop->rating_distribution[$i] ?? 0);
                    @endphp
                    <div class="d-flex align-items-center mb-2">
                      <div class="me-2" style="width: 22px;">
                        <span class="text-muted small">{{ $i }}</span>
                      </div>
                      <div class="me-2" style="width: 20px;">
                        <i class="fas fa-star text-warning"></i>
                      </div>
                      <div class="flex-grow-1 me-2">
                        <div class="progress">
                          <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      </div>
                      <div style="width: 48px;" class="text-end">
                        <span class="text-muted small">{{ $count }}</span>
                      </div>
                    </div>
                  @endfor
                </div>
              </div>
            </div>
          </div>
        </div>
      @else
        <div class="card">
          <div class="card-header d-flex align-items-center gap-2">
            <i class="fas fa-star text-muted"></i>
            <strong>Shop Rating</strong>
          </div>
          <div class="card-body text-center py-5">
            <i class="fas fa-star text-muted fs-1 mb-2"></i>
            <h5 class="mb-1">No Reviews Yet</h5>
            <p class="text-muted mb-0">This shop hasn't received any reviews yet. Be the first to leave one!</p>
          </div>
        </div>
      @endif

      {{-- About --}}
      <div class="card mt-4">
        <div class="card-body">
          <h5 class="card-title mb-2">About This Shop</h5>
          <div class="card-text">{!! $shop->bio !!}</div>
        </div>
      </div>

      {{-- Shop Policies --}}
      @if(!empty($shop->policies))
        <div class="card mt-4">
          <div class="card-body">
            <h5 class="card-title mb-2">Shop Policies</h5>
            <div class="card-text">{!! $shop->policies !!}</div>
          </div>
        </div>
      @endif

      {{-- Security --}}
      <div class="card mt-4">
        <div class="card-body d-flex align-items-center gap-3">
          <i class="fas fa-lock fa-lg text-success"></i>
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

      {{-- Placeholder for listings --}}
      <div class="text-center text-muted mt-4">
        (Product listings will appear here soon&hellip;)
      </div>

    </div>

    <div class="col-lg-4">

      {{-- Preferences --}}
      <div class="card">
        <div class="card-header">
          <strong>Preferences</strong>
        </div>
        <div class="card-body">
          <div class="row gy-3">
            <div class="col-12 d-flex justify-content-between">
              <span class="text-muted">Language</span>
              <span class="fw-medium">{{ $shop->language }}</span>
            </div>
            <div class="col-12 d-flex justify-content-between">
              <span class="text-muted">Country</span>
              <span class="fw-medium">{{ $shop->country }}</span>
            </div>
            <div class="col-12 d-flex justify-content-between">
              <span class="text-muted">Currency</span>
              <span class="fw-medium"> {{ $shop->currency }}</span>
            </div>
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">Shop URL</span>
                <a href="{{ route('seller.shops.show', $shop) }}" class="link-success text-truncate ms-2" style="max-width:60%;">
                  {{ url('shop/' . $shop->slug) }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Payment Details --}}
      <div class="card mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <strong>Payment Details</strong>
          <a href="{{ route('seller.payment-methods.index') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-plus me-1"></i> Add
          </a>
        </div>
        <div class="card-body">
          @if($paymentMethods->count() > 0)
            <div class="row gy-3">
              @foreach($paymentMethods as $paymentMethod)
                <div class="col-12">
                  <div class="d-flex align-items-start justify-content-between">
                    <div>
                      <div class="fw-semibold">{{ $paymentMethod->paymentType->name }}</div>
                      <div class="text-muted small">{{ $paymentMethod->account_number }}</div>
                    </div>
                    <div class="text-end">
                      <div class="text-muted small">Account Name</div>
                      <div class="fw-medium">{{ $paymentMethod->account_name }}</div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center text-muted py-3">
              <i class="fas fa-credit-card fa-2x mb-2"></i>
              <p class="mb-2">No payment methods added yet</p>
              <a href="{{ route('seller.payment-methods.index') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Add Your First Payment Method
              </a>
            </div>
          @endif
        </div>
      </div>

      {{-- Subscription --}}
      <div class="card mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <strong>Subscription Status</strong>
          <a href="{{ route('seller.subscription') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-cog me-1"></i> Manage
          </a>
        </div>
        <div class="card-body">
          @if($subscription)
            <div class="row gy-3">
              <div class="col-12 d-flex justify-content-between">
                <span class="text-muted">Status</span>
                @if($subscription->isActive())
                  <span class="badge bg-success">Active</span>
                @else
                  <span class="badge bg-danger">Inactive</span>
                @endif
              </div>
              <div class="col-12 d-flex justify-content-between">
                <span class="text-muted">Plan Amount</span>
                <span class="fw-medium">{{ get_currency()}} {{ number_format($subscription->amount, 2) }}</span>
              </div>
              <div class="col-12 d-flex justify-content-between">
                <span class="text-muted">Start Date</span>
                <span class="fw-medium">{{ $subscription->start_date ? $subscription->start_date->format('M d, Y') : 'N/A' }}</span>
              </div>
              <div class="col-12 d-flex justify-content-between">
                <span class="text-muted">End Date</span>
                <span class="fw-medium">{{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'N/A' }}</span>
              </div>

              @if($subscription->end_date)
                @php
                  $signedDays = (int) now()->diffInDays($subscription->end_date, false);
                @endphp
                <div class="col-12">
                  <div class="alert mb-0 {{ $signedDays > 0 ? 'alert-info' : 'alert-warning' }}">
                    <i class="fas fa-info-circle me-2"></i>
                    @if($signedDays > 0)
                      @php $daysLeft = $signedDays; @endphp
                      Your subscription will expire on {{ $subscription->end_date->format('M d, Y') }}
                      @if($daysLeft <= 30)
                        @php $cls = $daysLeft <= 7 ? 'text-danger' : 'text-warning'; @endphp
                        <span class="fw-bold {{ $cls }}">
                          (Expires in {{ number_format($daysLeft, 0) }} {{ Str::plural('day', $daysLeft) }})
                        </span>
                      @endif
                    @else
                      @php $daysAgo = abs($signedDays); @endphp
                      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                        <div>
                          Your subscription expired on {{ $subscription->end_date->format('M d, Y') }}
                          <span class="fw-bold text-danger">
                            (Expired {{ number_format($daysAgo, 0) }} {{ Str::plural('day', $daysAgo) }} ago)
                          </span>
                        </div>
                        @if(Auth::id() === $shop->user_id)
                          <a href="{{ route('seller.subscription') }}" class="btn btn-sm btn-warning mt-2 mt-md-0">
                            <i class="fas fa-undo me-1"></i> Renew Now
                          </a>
                        @endif
                      </div>
                    @endif
                  </div>
                </div>
              @endif
            </div>
          @else
            <div class="text-center text-muted py-3">
              <i class="fas fa-credit-card fa-2x mb-2"></i>
              <p class="mb-2">No active subscription found</p>
              <a href="{{ route('seller.subscription') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Subscribe Now
              </a>
            </div>
          @endif
        </div>
      </div>

      {{-- Billing --}}
      <div class="card mt-4">
        <div class="card-header">
          <strong>Billing Address</strong>
        </div>
        <div class="card-body">
          <div class="mb-1">{{ $shop->address }}</div>
          <div class="mb-0">{{ $shop->city }}, {{ $shop->postal }}</div>
        </div>
      </div>

    </div>
  </div>

  {{-- Enable Holiday Mode Modal --}}
  <div class="modal fade" id="enableHolidayModeModal" tabindex="-1" aria-labelledby="enableHolidayModeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header">
          <h5 class="modal-title" id="enableHolidayModeModalLabel">
            <i class="fas fa-umbrella-beach text-warning me-2"></i>Enable Holiday Mode
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">This action will pause all your active products, effectively putting your shop in holiday mode.</p>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> All active products will be paused and won’t be visible to buyers until you reactivate them.
          </div>
          <p class="text-muted small mb-0">
            <i class="fas fa-info-circle me-1"></i>
            You can reactivate individual products from your product listings page.
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <form action="{{ route('seller.holiday-mode.enable') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-umbrella-beach me-2"></i>Enable Holiday Mode
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Disable Holiday Mode Modal --}}
  <div class="modal fade" id="disableHolidayModeModal" tabindex="-1" aria-labelledby="disableHolidayModeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header">
          <h5 class="modal-title" id="disableHolidayModeModalLabel">
            <i class="fas fa-play text-success me-2"></i>Disable Holiday Mode
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">This action will reactivate all your paused products, bringing your shop back online.</p>
          <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Great!</strong> All paused products will be reactivated and visible to buyers again.
          </div>
          <p class="text-muted small mb-0">
            <i class="fas fa-info-circle me-1"></i>
            Your shop will be fully operational once holiday mode is disabled.
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <form action="{{ route('seller.holiday-mode.disable') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">
              <i class="fas fa-play me-2"></i>Disable Holiday Mode
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
