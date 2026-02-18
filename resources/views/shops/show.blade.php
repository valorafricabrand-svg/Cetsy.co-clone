@extends('theme.'.theme().'.layouts.app')

@section('main')

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
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-3">
    
        <img src="{{ $shop->logo ? ($shop->logo_url ?? asset('storage/' . $shop->logo)) : setting('favicon_url') }}" alt="{{ $shop->name }} logo" class="shop-avatar">
    

      <div>
        <h1 class="text-lg font-semibold mb-1">{{ $shop->name }}</h1>

        @if($shop->hasReviews())
          <div class="flex items-center gap-2">
            <div class="flex items-center">
              @for($i = 1; $i <= 5; $i++)
                @if($i <= $shop->average_rating)
                  <i class="fas fa-star text-amber-600"></i>
                @elseif($i - $shop->average_rating < 1 && $i - $shop->average_rating > 0)
                  <i class="fas fa-star-half-alt text-amber-600"></i>
                @else
                  <i class="far fa-star text-slate-500"></i>
                @endif
              @endfor
            </div>
            <span class="text-slate-500 text-xs">
              {{ number_format($shop->average_rating, 1) }} ({{ $shop->review_count }} {{ Str::plural('review', $shop->review_count) }})
            </span>
          </div>
        @endif
      </div>
    </div>

    <div class="flex flex-wrap gap-2">
      <a href="{{ route('seller.payment-methods.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
        <i class="fas fa-credit-card mr-2"></i>Payment Methods
      </a>

      @if(Auth::id() === $shop->user_id)
        <a href="{{ route('seller.shops.edit', $shop) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
          <i class="fas fa-edit mr-2"></i>Edit Shop
        </a>
      @endif
    </div>
  </div>

  {{-- Holiday Mode Quick Action --}}
  @if(Auth::id() === $shop->user_id)
    <div class="mb-4">
      @if($isHolidayMode)
        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" data-bs-toggle="modal" data-bs-target="#disableHolidayModeModal">
          <i class="fas fa-play mr-2"></i>Disable Holiday Mode
        </button>
      @else
        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400" data-bs-toggle="modal" data-bs-target="#enableHolidayModeModal">
          <i class="fas fa-umbrella-beach mr-2"></i>Enable Holiday Mode
        </button>
      @endif
    </div>
  @endif

  {{-- Announcement --}}
  @if(!empty($shop->announcement))
    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 flex items-start gap-2 mb-4">
      <i class="fas fa-bullhorn mt-1"></i>
      <div class="flex-grow-1">{!! $shop->announcement !!}</div>
    </div>
  @endif

  {{-- Flash --}}
  @if(session('success'))
    <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
  @endif

  {{-- Featured Image --}}
  @if($shop->featured_image_url ?? $shop->featured_image)
    <div class="mb-4">
      <div class="ratio ratio-21x9 rounded overflow-hidden">
        <img
          src="{{ $shop->featured_image_url ?? asset('storage/' . $shop->featured_image) }}"
          class="w-full h-full object-fit-cover"
          alt="{{ $shop->name }} featured image">
      </div>
    </div>
  @endif

  <div class="grid grid-cols-12 gap-4 gap-4">
    <div class="lg:col-span-8">

      {{-- Shop Rating --}}
      @if($shop->hasReviews())
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-4 py-3 flex items-center justify-between">
            <span class="inline-flex items-center gap-2">
              <i class="fas fa-star text-amber-600"></i>
              <strong>Shop Rating</strong>
            </span>
            <a href="{{ route('shop.reviews', $shop) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
              <i class="fas fa-eye mr-1"></i>View All Reviews
            </a>
          </div>
          <div class="p-4 sm:p-5">
            <div class="grid grid-cols-12 gap-4 items-center gap-y-3">
              <div class="md:col-span-4 text-center">
                <div class="mb-2 flex justify-center">
                  @for($i = 1; $i <= 5; $i++)
                    @if($i <= $shop->average_rating)
                      <i class="fas fa-star text-amber-600 fs-4"></i>
                    @elseif($i - $shop->average_rating < 1 && $i - $shop->average_rating > 0)
                      <i class="fas fa-star-half-alt text-amber-600 fs-4"></i>
                    @else
                      <i class="far fa-star text-slate-500 fs-4"></i>
                    @endif
                  @endfor
                </div>
                <div class="mb-1">
                  <span class="text-xl font-semibold font-bold text-primary">{{ number_format($shop->average_rating, 1) }}</span>
                  <span class="text-slate-500">/ 5</span>
                </div>
                <div class="text-slate-500 text-xs">
                  {{ $shop->review_count }} {{ Str::plural('review', $shop->review_count) }}
                </div>
              </div>

              <div class="md:col-span-8">
                <div class="rating-bars">
                  @for($i = 5; $i >= 1; $i--)
                    @php
                      $percentage = (float)($shop->rating_percentages[$i] ?? 0);
                      $count = (int)($shop->rating_distribution[$i] ?? 0);
                    @endphp
                    <div class="flex items-center mb-2">
                      <div class="mr-2" style="width: 22px;">
                        <span class="text-slate-500 text-xs">{{ $i }}</span>
                      </div>
                      <div class="mr-2" style="width: 20px;">
                        <i class="fas fa-star text-amber-600"></i>
                      </div>
                      <div class="flex-grow-1 mr-2">
                        <div class="progress">
                          <div class="progress-bar bg-amber-100" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      </div>
                      <div style="width: 48px;" class="text-right">
                        <span class="text-slate-500 text-xs">{{ $count }}</span>
                      </div>
                    </div>
                  @endfor
                </div>
              </div>
            </div>
          </div>
        </div>
      @else
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-4 py-3 flex items-center gap-2">
            <i class="fas fa-star text-slate-500"></i>
            <strong>Shop Rating</strong>
          </div>
          <div class="p-4 sm:p-5 text-center py-5">
            <i class="fas fa-star text-slate-500 fs-1 mb-2"></i>
            <h5 class="mb-1">No Reviews Yet</h5>
            <p class="text-slate-500 mb-0">This shop hasn't received any reviews yet. Be the first to leave one!</p>
          </div>
        </div>
      @endif

      {{-- About --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
        <div class="p-4 sm:p-5">
          <h5 class="text-lg font-semibold text-slate-900 mb-2">About This Shop</h5>
          <div class="card-text">{!! $shop->bio !!}</div>
        </div>
      </div>

      {{-- Shop Policies --}}
      @if(!empty($shop->policies))
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
          <div class="p-4 sm:p-5">
            <h5 class="text-lg font-semibold text-slate-900 mb-2">Shop Policies</h5>
            <div class="card-text">{!! $shop->policies !!}</div>
          </div>
        </div>
      @endif

      {{-- Security --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
        <div class="p-4 sm:p-5 flex items-center gap-3">
          <i class="fas fa-lock fa-lg text-emerald-600"></i>
          <div>
            Two-Factor Authentication:
            @if($shop->enable_2fa)
              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success">Enabled</span>
            @else
              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-danger">Disabled</span>
            @endif
          </div>
        </div>
      </div>

    </div>

    <div class="lg:col-span-4">

      {{-- Preferences --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3">
          <strong>Preferences</strong>
        </div>
        <div class="p-4 sm:p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 flex justify-between">
              <span class="text-slate-500">Language</span>
              <span class="fw-medium">{{ $shop->language }}</span>
            </div>
            <div class="col-span-12 flex justify-between">
              <span class="text-slate-500">Country</span>
              <span class="fw-medium">{{ country_name($shop->country) }}</span>
            </div>
            <div class="col-span-12 flex justify-between">
              <span class="text-slate-500">Currency</span>
              <span class="fw-medium"> {{ $shop->currency }}</span>
            </div>
            <div class="col-span-12">
              <div class="flex justify-between items-center">
                <span class="text-slate-500">Shop URL</span>
                <a href="{{ route('seller.shops.show', $shop) }}" class="link-success text-truncate ml-2" style="max-width:60%;">
                  {{ url('shop/' . $shop->slug) }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Payment Details --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
        <div class="border-b border-slate-200 px-4 py-3 flex items-center justify-between">
          <strong>Payment Details</strong>
          <a href="{{ route('seller.payment-methods.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
            <i class="fas fa-plus mr-1"></i> Add
          </a>
        </div>
        <div class="p-4 sm:p-5">
            @if($paymentMethods->count() > 0)
            <div class="grid grid-cols-12 gap-4 gap-y-3">
              @foreach($paymentMethods as $paymentMethod)
                <div class="col-span-12">
                  <div class="grid grid-cols-12 gap-4 gap-2 items-start">
                    <div class="col-span-12 sm:col-span-7">
                      <div class="font-semibold">{{ $paymentMethod->paymentType->name }}</div>
                      <div class="text-slate-500 text-xs text-break">{{ $paymentMethod->account_number }}</div>
                    </div>
                    <div class="col-span-12 sm:col-span-5 text-sm-end">
                      <div class="text-slate-500 text-xs">Account Name</div>
                      <div class="fw-medium text-break">{{ $paymentMethod->account_name }}</div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center text-slate-500 py-3">
              <i class="fas fa-credit-card fa-2x mb-2"></i>
              <p class="mb-2">No payment methods added yet</p>
              <a href="{{ route('seller.payment-methods.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                <i class="fas fa-plus mr-1"></i> Add Your First Payment Method
              </a>
            </div>
          @endif
        </div>
      </div>

      {{-- Subscription --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
        <div class="border-b border-slate-200 px-4 py-3 flex items-center justify-between">
          <strong>Subscription Status</strong>
          <a href="{{ route('seller.subscription') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
            <i class="fas fa-cog mr-1"></i> Manage
          </a>
        </div>
        <div class="p-4 sm:p-5">
          @if($subscription)
            <div class="grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12 flex justify-between">
                <span class="text-slate-500">Status</span>
                @if($subscription->isActive())
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success">Active</span>
                @else
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-danger">Inactive</span>
                @endif
              </div>
              <div class="col-span-12 flex justify-between">
                <span class="text-slate-500">Plan Amount</span>
                <span class="fw-medium">{{ get_currency()}} {{ number_format($subscription->amount, 2) }}</span>
              </div>
              <div class="col-span-12 flex justify-between">
                <span class="text-slate-500">Start Date</span>
                <span class="fw-medium">{{ $subscription->start_date ? $subscription->start_date->format('M d, Y') : 'N/A' }}</span>
              </div>
              <div class="col-span-12 flex justify-between">
                <span class="text-slate-500">End Date</span>
                <span class="fw-medium">{{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'N/A' }}</span>
              </div>

              @if($subscription->end_date)
                @php
                  $signedDays = (int) now()->diffInDays($subscription->end_date, false);
                @endphp
                <div class="col-span-12">
                  <div class="alert mb-0 {{ $signedDays > 0 ? 'alert-info' : 'alert-warning' }}">
                    <i class="fas fa-info-circle mr-2"></i>
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
                      <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                          Your subscription expired on {{ $subscription->end_date->format('M d, Y') }}
                          <span class="font-bold text-rose-600">
                            (Expired {{ number_format($daysAgo, 0) }} {{ Str::plural('day', $daysAgo) }} ago)
                          </span>
                        </div>
                        @if(Auth::id() === $shop->user_id)
                          <a href="{{ route('seller.subscription') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-amber-500 text-slate-900 hover:bg-amber-400 mt-2 mt-0 md:mt-0">
                            <i class="fas fa-undo mr-1"></i> Renew Now
                          </a>
                        @endif
                      </div>
                    @endif
                  </div>
                </div>
              @endif
            </div>
          @else
            <div class="text-center text-slate-500 py-3">
              <i class="fas fa-credit-card fa-2x mb-2"></i>
              <p class="mb-2">No active subscription found</p>
              <a href="{{ route('seller.subscription') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                <i class="fas fa-plus mr-1"></i> Subscribe Now
              </a>
            </div>
          @endif
        </div>
      </div>

      {{-- Billing --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
        <div class="border-b border-slate-200 px-4 py-3">
          <strong>Billing Address</strong>
        </div>
        <div class="p-4 sm:p-5">
          <div class="mb-1">{{ $shop->address }}</div>
          <div class="mb-0">{{ $shop->city }}, {{ $shop->postal }}</div>
        </div>
      </div>

    </div>
  </div>

  {{-- Enable Holiday Mode Modal --}}
  <div class="modal" id="enableHolidayModeModal" tabindex="-1" aria-labelledby="enableHolidayModeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-xl rounded-4">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900" id="enableHolidayModeModalLabel">
            <i class="fas fa-umbrella-beach text-amber-600 mr-2"></i>Enable Holiday Mode
          </h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="px-4 py-4">
          <p class="mb-3">This action will pause all your active products, effectively putting your shop in holiday mode.</p>
          <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Warning:</strong> All active products will be paused and won’t be visible to buyers until you reactivate them.
          </div>
          <p class="text-slate-500 text-xs mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            You can reactivate individual products from your product listings page.
          </p>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-bs-dismiss="modal">Cancel</button>
          <form action="{{ route('seller.holiday-mode.enable') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400">
              <i class="fas fa-umbrella-beach mr-2"></i>Enable Holiday Mode
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Disable Holiday Mode Modal --}}
  <div class="modal" id="disableHolidayModeModal" tabindex="-1" aria-labelledby="disableHolidayModeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-xl rounded-4">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900" id="disableHolidayModeModalLabel">
            <i class="fas fa-play text-emerald-600 mr-2"></i>Disable Holiday Mode
          </h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="px-4 py-4">
          <p class="mb-3">This action will reactivate all your paused products, bringing your shop back online.</p>
          <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Great!</strong> All paused products will be reactivated and visible to buyers again.
          </div>
          <p class="text-slate-500 text-xs mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Your shop will be fully operational once holiday mode is disabled.
          </p>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-bs-dismiss="modal">Cancel</button>
          <form action="{{ route('seller.holiday-mode.disable') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
              <i class="fas fa-play mr-2"></i>Disable Holiday Mode
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection


