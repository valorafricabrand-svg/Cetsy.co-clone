@extends('layouts.frontapp')

@section('main')

<!-- Shop Hero Section -->
<section class="py-5 bg-white border-bottom">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-9 d-flex align-items-center gap-4">
        @if($shop->logo_url)
          <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }} logo"
               class="rounded-circle shadow-sm border"
               style="width: 80px; height: 80px; object-fit: cover;">
        @endif
        <div>
          <h1 class="h4 fw-bold mb-1">{{ $shop->name }}</h1>
          <span class="text-muted d-block mb-2">Owned by {{ $shop->user->name }}</span>
          <!-- Reviews and Rating -->
          @php
            $averageRating = $shop->reviews()->avg('rating') ?? 0;
            $reviewCount = $shop->reviews()->count();
          @endphp
          @if($reviewCount > 0)
            <a href="{{ route('shop.reviews', $shop) }}" class="text-decoration-none">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center me-2">
                  @for($i = 1; $i <= 5; $i++)
                    @if($i <= $averageRating)
                      <i class="fas fa-star text-warning" style="font-size: 16px;"></i>
                    @elseif($i - $averageRating < 1 && $i - $averageRating > 0)
                      <i class="fas fa-star-half-alt text-warning" style="font-size: 16px;"></i>
                    @else
                      <i class="far fa-star text-muted" style="font-size: 16px;"></i>
                    @endif
                  @endfor
                </div>
                <span class="fw-semibold text-dark">{{ number_format($averageRating, 1) }}</span>
                <span class="text-muted">({{ $reviewCount }} {{ Str::plural('review', $reviewCount) }})</span>
              </div>
            </a>
          @else
            <div class="d-flex align-items-center gap-2">
              <div class="d-flex align-items-center me-2">
                @for($i = 1; $i <= 5; $i++)
                  <i class="far fa-star text-muted" style="font-size: 16px;"></i>
                @endfor
              </div>
              <span class="text-muted">No reviews yet</span>
            </div>
          @endif
        </div>
      </div>
      <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
        @if(Auth::id() === $shop->user_id)
          <a href="{{ route('seller.shops.edit', $shop) }}" class="btn btn-outline-success rounded-pill">
            <i class="fas fa-edit me-1"></i> Edit Shop
          </a>
        @endif
      </div>
    </div>
  </div>
</section>

<!-- Shop Announcement -->
@if(!empty($shop->announcement))
  <div class="container mt-4">
    <div class="alert alert-info shadow-sm">
      <i class="fas fa-bullhorn me-2"></i>
      {{ $shop->announcement }}
    </div>
  </div>
@endif

<!-- Flash Message -->
@if(session('success'))
  <div class="container mt-4">
    <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
  </div>
@endif

<!-- Featured Image Section -->
@if($shop->featured_image)
<section class="py-4 bg-white">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <img 
          src="{{ asset('storage/' . $shop->featured_image) }}" 
          alt="{{ $shop->name }} featured image"
          class="w-100 rounded"
          style="height: 300px; object-fit: cover;"
        >
      </div>
    </div>
  </div>
</section>
@endif

<!-- Shop Overview -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <!-- About the Shop -->
      <div class="col-lg-12">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white fw-semibold border-bottom">About This Shop</div>
          <div class="card-body">
            @if($shop->bio)
              <p class="mb-0 text-secondary">{{ $shop->bio }}</p>
            @else
              <p class="text-muted mb-0">This shop has not provided a description yet.</p>
            @endif
          </div>
        </div>
      </div>

      

      <!-- Preferences -->
      <div class="col-lg-12">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white fw-semibold border-bottom">Shop Preferences</div>
          <div class="card-body row">
            <div class="col-sm-6 mb-3">
              <strong>Language:</strong><br>
              <span class="text-muted">{{ $shop->language ?? 'N/A' }}</span>
            </div>
            <div class="col-sm-6 mb-3">
              <strong>Country:</strong><br>
              <span class="text-muted">{{ country_name($shop->country) ?? 'N/A' }}</span>
            </div>
            <div class="col-sm-6 mb-3">
              <strong>Currency:</strong><br>
              <span class="text-muted">{{ $shop->currency ?? 'N/A' }}</span>
            </div>
            <div class="col-12">
              <strong>Shop URL:</strong><br>
              <div class="d-flex align-items-center gap-2">
                
                <button type="button" 
                        class="btn btn-outline-success btn-sm" 
                        onclick="copyShopUrl('{{ url('shop/' . $shop->slug) }}')"
                        title="Copy shop URL">
                  <i class="fas fa-share-alt"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Billing Address -->
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white fw-semibold border-bottom">Billing Address</div>
          <div class="card-body">
            <p class="mb-1 text-secondary">{{ $shop->address ?? 'N/A' }}</p>
            <p class="mb-0 text-secondary">{{ $shop->city ?? '' }}{{ $shop->postal ? ', ' . $shop->postal : '' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Shop Products -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h5 fw-bold text-dark">Products from {{ $shop->name }}</h2>
      <a href="{{ route('listings') }}" class="text-decoration-none text-success small">See All Listings</a>
    </div>

    @if($products->isEmpty())
      <div class="alert alert-info shadow-sm">
        This shop has not listed any products yet.
      </div>
    @else
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @foreach($products as $product)
          <div class="col">
            <div class="card h-100 shadow-sm border-0 product-hover">
              <a href="{{ route('listing.show', $product) }}" class="text-decoration-none">
                @if($img = $product->media->first())
                  <img 
                    src="{{ asset('storage/'.$img->url) }}" 
                    alt="{{ $product->name }}" 
                    class="card-img-top"
                    style="height: 200px; object-fit: cover;"
                    loading="lazy">
                @else
                  <div class="bg-secondary d-flex justify-content-center align-items-center text-white" style="height: 200px;">
                    No Image
                  </div>
                @endif
              </a>
              <div class="card-body d-flex flex-column">
                <h6 class="card-title text-truncate">
                  <a href="{{ route('listing.show', $product) }}" class="text-dark text-decoration-none">
                    {{ $product->name }}
                  </a>
                </h6>
              @if(!empty($product->discount_price) && $product->discount_price < $product->price)
  <div class="d-flex align-items-baseline gap-3 mb-3">
    <span class="fw-bold text-success">
      {{ get_currency() }} {{ number_format($product->discount_price, 2) }}
    </span>
    <span class="text-muted text-decoration-line-through">
      {{ get_currency() }} {{ number_format($product->price, 2) }}
    </span>
  </div>
@else
  <p class="fw-bold text-success mb-3">
    {{ get_currency() }} {{ number_format($product->price, 2) }}
  </p>
@endif

                <div class="mt-auto d-flex justify-content-center">
                  <a href="{{ route('listing.show', $product) }}"
                     class="btn btn-outline-success btn-sm w-100 d-flex justify-content-center align-items-center gap-2"
                     aria-label="View {{ $product->name }}">
                    <span>View Listing</span>
                    <i class="fas fa-eye"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      @if($products->hasPages())
        <div class="mt-4 d-flex justify-content-center">
          {{ $products->links('pagination::bootstrap-5') }}
        </div>
      @endif
    @endif
  </div>
</section>

@if(!empty($shop->policies))
  <div class="container my-5 text-center">
    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#shopPoliciesModal">
      <i class="fas fa-file-alt me-1"></i> View Shop Policies
    </button>
  </div>

  <!-- Shop Policies Modal -->
  <div class="modal fade" id="shopPoliciesModal" tabindex="-1" aria-labelledby="shopPoliciesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="shopPoliciesModalLabel">Shop Policies</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-start">
            {!! nl2br(e($shop->policies)) !!}
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

@endsection

@push('scripts')
<script>
function copyShopUrl(url) {
    // Create a temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = url;
    document.body.appendChild(tempInput);
    
    // Select and copy the text
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        // Show success feedback
        const button = event.target.closest('button');
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.remove('btn-outline-success');
        button.classList.add('btn-success');
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalIcon;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-success');
        }, 2000);
        
    } catch (err) {
        console.error('Failed to copy URL: ', err);
        alert('Failed to copy URL. Please copy manually.');
    }
    
    // Clean up
    document.body.removeChild(tempInput);
}
</script>
@endpush
