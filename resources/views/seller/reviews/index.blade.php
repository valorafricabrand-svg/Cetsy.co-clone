@extends('layouts.app')

@section('title', 'Shop Reviews')

@push('styles')
<style>
  /* Star colors */
  .rating-stars .star-filled { color: #e5780b !important; }
  .rating-stars .star-empty { color: #000000 !important; }
  .stat-card { border: 0; }
  .stat-card .value { font-size: 2rem; font-weight: 700; }
  .stat-card .label { text-transform: uppercase; font-size: .75rem; letter-spacing: .08em; }
  .review-comment { white-space: pre-line; }
  .seller-response { white-space: pre-line; }
</style>
@endpush

@section('content')
@php
    $summary = array_merge([
        'count' => 0,
        'average' => 0,
        'five_star' => 0,
        'four_star' => 0,
        'three_star' => 0,
        'two_star' => 0,
        'one_star' => 0,
    ], $summary ?? []);

    $ratingFilter = $ratingFilter ?? null;
@endphp

<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
      <h2 class="mb-1">Shop Reviews</h2>
      <p class="text-muted mb-0">Only reviews from finished or delivered orders are shown here.</p>
    </div>
    <div class="text-end">
      <div class="value text-warning">{{ number_format($summary['average'], 1) }}</div>
      <div class="text-muted small">Average rating across {{ $summary['count'] }} review{{ $summary['count'] === 1 ? '' : 's' }}</div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm stat-card h-100">
        <div class="card-body text-center">
          <div class="label text-muted mb-1">Total Reviews</div>
          <div class="value">{{ $summary['count'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm stat-card h-100">
        <div class="card-body text-center">
          <div class="label text-muted mb-1">Five-Star</div>
          @php $fivePercent = $summary['count'] ? round(($summary['five_star'] / $summary['count']) * 100) : 0; @endphp
          <div class="value text-warning">{{ $summary['five_star'] }}</div>
          <div class="small text-muted">{{ $fivePercent }}% of reviews</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm stat-card h-100">
        <div class="card-body text-center">
          <div class="label text-muted mb-1">Four-Star</div>
          @php $fourPercent = $summary['count'] ? round(($summary['four_star'] / $summary['count']) * 100) : 0; @endphp
          <div class="value text-warning">{{ $summary['four_star'] }}</div>
          <div class="small text-muted">{{ $fourPercent }}% of reviews</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm stat-card h-100">
        <div class="card-body text-center">
          <div class="label text-muted mb-1">Three-Star & Below</div>
          @php $lowCount = $summary['three_star'] + $summary['two_star'] + $summary['one_star'];
               $lowPercent = $summary['count'] ? round(($lowCount / $summary['count']) * 100) : 0;
          @endphp
          <div class="value text-warning">{{ $lowCount }}</div>
          <div class="small text-muted">{{ $lowPercent }}% of reviews</div>
        </div>
      </div>
    </div>
  </div>

  <form action="{{ route('seller.reviews.index') }}" method="GET" class="card shadow-sm border-0 mb-4">
    <div class="card-body row g-3 align-items-end">
      <div class="col-md-5">
        <label class="form-label">Search</label>
        <input type="text" class="form-control" name="q" value="{{ $search }}" placeholder="Search by listing, buyer, order, or comment">
      </div>
      <div class="col-md-3 col-sm-6">
        <label class="form-label">Rating</label>
        <select name="rating" class="form-select">
          <option value="">All ratings</option>
          @for($i = 5; $i >= 1; $i--)
            <option value="{{ $i }}" @selected($ratingFilter === $i)>{{ $i }} star{{ $i === 1 ? '' : 's' }}</option>
          @endfor
        </select>
      </div>
      <div class="col-md-2 col-sm-6">
        <label class="form-label">Per Page</label>
        <select name="per_page" class="form-select">
          @foreach([10, 15, 25, 50, 100] as $option)
            <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i> Filter</button>
        @if($search !== '' || ! is_null($ratingFilter) || $perPage !== 15)
          <a href="{{ route('seller.reviews.index') }}" class="btn btn-outline-secondary" title="Reset filters">
            <i class="fas fa-rotate-left"></i>
          </a>
        @endif
      </div>
    </div>
  </form>

  <div class="card shadow-sm border-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th scope="col">Order</th>
            <th scope="col">Listing</th>
            <th scope="col">Buyer</th>
            <th scope="col">Rating</th>
            <th scope="col">Comment</th>
            <th scope="col">Left On</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reviews as $review)
            @php
                $order = $review->order;
                $orderNumber = $order?->order_number ?: ('#' . str_pad((string) ($order?->id ?? ''), 6, '0', STR_PAD_LEFT));
                $product = optional($review->orderItem)->product;
                $buyer = $review->user;
                $status = $order?->status;
                $statusBadge = method_exists($order, 'getStatusBadgeClass') ? $order->getStatusBadgeClass() : 'bg-secondary';
            @endphp
            <tr>
              <td>
                <div class="fw-semibold">{{ $orderNumber }}</div>
                @if($status)
                  <span class="badge {{ $statusBadge }} text-capitalize">{{ str_replace('_', ' ', $status) }}</span>
                @endif
              </td>
              <td>
                @if($product)
                  <div class="fw-semibold">{{ $product->name }}</div>
                  <div class="text-muted small text-capitalize">{{ $product->type }}</div>
                @else
                  <span class="text-muted">Listing removed</span>
                @endif
              </td>
              <td>
                <div class="fw-semibold">{{ $buyer->name ?? 'Unknown Buyer' }}</div>
              </td>
              <td>
                <div class="rating-stars mb-1">
                  @for($i = 1; $i <= 5; $i++)
                    @if($i <= (int) $review->rating)
                      <i class="fas fa-star star-filled"></i>
                    @else
                      <i class="far fa-star star-empty"></i>
                    @endif
                  @endfor
                </div>
                <div class="small text-muted">{{ $review->rating }} / 5</div>
              </td>
              <td>
                @if($review->comment)
                  <div class="review-comment small">{{ \Illuminate\Support\Str::limit($review->comment, 200) }}</div>
                @else
                  <span class="text-muted small">No comment left</span>
                @endif

                @if(!empty($review->image_path))
                  <div class="mt-2">
                    <a href="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" target="_blank">
                      <img src="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" alt="Review image" style="max-width: 120px; max-height: 120px; border-radius: 6px;"/>
                    </a>
                  </div>
                @endif

                {{-- Seller response display / form --}}
                <div class="mt-2">
                  @if(!empty($review->seller_response))
                    <div class="border rounded p-2 bg-light">
                      <div class="fw-semibold mb-1">Your response</div>
                      <div class="seller-response small">{{ $review->seller_response }}</div>
                      <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#respondForm-{{ $review->id }}" aria-expanded="false" aria-controls="respondForm-{{ $review->id }}">
                          Edit response
                        </button>
                      </div>
                    </div>
                  @else
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#respondForm-{{ $review->id }}" aria-expanded="false" aria-controls="respondForm-{{ $review->id }}">
                      Respond
                    </button>
                  @endif
                </div>

                <div class="collapse mt-2" id="respondForm-{{ $review->id }}">
                  <form action="{{ route('seller.reviews.respond', $review) }}" method="POST" class="border rounded p-2">
                    @csrf
                    <label for="seller_response_{{ $review->id }}" class="form-label mb-1">Your response</label>
                    <textarea class="form-control" id="seller_response_{{ $review->id }}" name="seller_response" rows="3" maxlength="2000" placeholder="Write a helpful, professional reply visible to the buyer.">{{ old('seller_response', $review->seller_response) }}</textarea>
                    <div class="d-flex justify-content-end gap-2 mt-2">
                      <button type="submit" class="btn btn-sm btn-primary">Save</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#respondForm-{{ $review->id }}">Cancel</button>
                    </div>
                  </form>
                </div>
              </td>
              <td>
                <div class="fw-semibold">{{ optional($review->created_at)->format('M d, Y') }}</div>
                @if($order?->delivered_at)
                  <div class="text-muted small">Delivered: {{ optional($order->delivered_at)->format('M d, Y') }}</div>
                @elseif($order?->completed_at)
                  <div class="text-muted small">Completed: {{ optional($order->completed_at)->format('M d, Y') }}</div>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">
                <i class="fas fa-star-half-alt fa-2x mb-2"></i>
                <div>No reviews yet. Encourage buyers to leave feedback once orders are delivered.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($reviews->hasPages())
      <div class="card-footer bg-white">
        {{ $reviews->links('pagination::bootstrap-5') }}
      </div>
    @endif
  </div>
</div>
@endsection
