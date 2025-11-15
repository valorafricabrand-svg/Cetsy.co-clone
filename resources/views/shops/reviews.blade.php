@extends('layouts.frontapp')

@section('main')

<!-- Shop Reviews Header -->
<section class="py-5 bg-white border-bottom">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-8">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('shop.show', $shop) }}" class="text-decoration-none">{{ $shop->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reviews</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mt-2 mb-1">Reviews for {{ $shop->name }}</h1>
        
        <!-- Rating Summary -->
        <div class="d-flex align-items-center gap-3 mt-3">
          <div class="d-flex align-items-center">
            @for($i = 1; $i <= 5; $i++)
              @if($i <= $averageRating)
                <i class="fas fa-star" style="font-size: 18px; color:#e5780b;"></i>
              @elseif($i - $averageRating < 1 && $i - $averageRating > 0)
                <i class="fas fa-star-half-alt" style="font-size: 18px; color:#e5780b;"></i>
              @else
                <i class="far fa-star" style="font-size: 18px; color:#000;"></i>
              @endif
            @endfor
          </div>
          <div>
            <span class="fw-bold fs-4">{{ number_format($averageRating, 1) }}</span>
            <span class="text-muted">out of 5</span>
          </div>
          <div class="text-muted">
            {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}
          </div>
        </div>
      </div>
      
      <div class="col-md-4 text-md-end">
        <a href="{{ route('shop.show', $shop) }}" class="btn btn-outline-success">
          <i class="fas fa-arrow-left me-1"></i> Back to Shop
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Reviews List -->
<section class="py-5 bg-light">
  <div class="container">
    @if($reviews->isEmpty())
      <div class="text-center py-5">
        <i class="fas fa-star text-muted" style="font-size: 48px;"></i>
        <h3 class="mt-3">No Reviews Yet</h3>
        <p class="text-muted">This shop hasn't received any reviews yet. Be the first to leave a review!</p>
      </div>
    @else
      <div class="row">
        <div class="col-lg-8">
          <!-- Reviews -->
          @foreach($reviews as $review)
            <div class="card shadow-sm border-0 mb-4">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center">
                      @for($i = 1; $i <= 5; $i++)
                        @if($i <= $review->rating)
                          <i class="fas fa-star" style="font-size: 14px; color:#e5780b;"></i>
                        @else
                          <i class="far fa-star" style="font-size: 14px; color:#000;"></i>
                        @endif
                      @endfor
                    </div>
                    <div>
                      <strong>{{ $review->user ? $review->user->name : 'Anonymous User' }}</strong>
                      <div class="text-muted small">{{ $review->created_at->diffForHumans() }}</div>
                    </div>
                  </div>
                  
                  @if($review->orderItem && $review->orderItem->product)
                    <div class="text-end">
                      <small class="text-muted">Reviewed:</small><br>
                      <a href="{{ route('products.show', $review->orderItem->product) }}" 
                         class="text-decoration-none text-success small">
                        {{ $review->orderItem->product->name }}
                      </a>
                    </div>
                  @endif
                </div>
                
                @if($review->comment)
                  <p class="mb-2">{{ $review->comment }}</p>
                @else
                  <p class="text-muted mb-2"><em>No comment provided</em></p>
                @endif

                @if(!empty($review->image_path))
                  <a href="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" target="_blank">
                    <img src="{{ asset('storage/'.ltrim($review->image_path,'/')) }}" alt="Review image" style="max-width: 160px;max-height:160px;border-radius:8px;"/>
                  </a>
                @endif
              </div>
            </div>
          @endforeach
          
          <!-- Pagination -->
          @if($reviews->hasPages())
            <div class="d-flex justify-content-center">
              {{ $reviews->links('pagination::bootstrap-5') }}
            </div>
          @endif
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold border-bottom">
              Shop Information
            </div>
            <div class="card-body">
              <div class="d-flex align-items-center gap-3 mb-3">
                @if($shop->logo_url)
                  <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }} logo"
                       class="rounded-circle"
                       style="width: 50px; height: 50px; object-fit: cover;">
                @endif
                <div>
                  <h6 class="mb-0">{{ $shop->name }}</h6>
                  <small class="text-muted">Owned by {{ $shop->user->name }}</small>
                </div>
              </div>
              
              @if($shop->bio)
                <p class="small text-muted mb-3">{{ Str::limit($shop->bio, 100) }}</p>
              @endif
              
              <a href="{{ route('shop.show', $shop) }}" class="btn btn-success w-100">
                Visit Shop
              </a>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>
</section>

@endsection 
