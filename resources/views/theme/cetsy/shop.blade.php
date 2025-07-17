@extends('layouts.frontapp')

@section('main')

<!-- Shop Hero Section -->
<section class="py-5 bg-white border-bottom">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-9 d-flex align-items-center gap-4">
        @if($shop->logo)
          <img src="{{ asset('storage/' . $shop->logo) }}" alt="{{ $shop->name }} logo"
               class="rounded-circle shadow-sm border"
               style="width: 80px; height: 80px; object-fit: cover;">
        @else
          <img src="{{ asset('assets/images/cetsy_feture_logo.jpg') }}" alt="{{ $shop->name }} logo"
               class="rounded-circle shadow-sm border"
               style="width: 80px; height: 80px; object-fit: cover;">
        @endif
        <div class="flex-grow-1">
          <h1 class="h4 fw-bold mb-1">{{ $shop->name }}</h1>
          <span class="text-muted d-block mb-2">{{ country_name($shop->country) }}</span>
          
          <!-- Shop Stats -->
          @php
            $totalSales = $shop->orderItems()->whereHas('order', function($q) {
              $q->where('status', 'completed');
            })->count();
            $totalProducts = $shop->products()->where('is_active', true)->count();
            $memberSince = $shop->created_at->diffForHumans();
            $averageRating = $shop->reviews()->avg('rating') ?? 0;
            $reviewCount = $shop->reviews()->count();
          @endphp
          
          <div class="d-flex align-items-center gap-4 mb-3">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-shopping-bag text-success"></i>
              <span class="text-muted">{{ $totalSales }} sales</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-box text-primary"></i>
              <span class="text-muted">{{ $totalProducts }} items</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-calendar text-info"></i>
              <span class="text-muted">{{ $memberSince }} on Cetsy</span>
            </div>
            @if($reviewCount > 0)
              <div class="d-flex align-items-center gap-2">
                <i class="fas fa-star text-warning"></i>
                <span class="text-muted">{{ number_format($averageRating, 1) }} ({{ $reviewCount }})</span>
              </div>
            @endif
          </div>

          <!-- Reviews and Rating -->
          @if($reviewCount > 0)
            <a href="#reviews" class="text-decoration-none" data-bs-toggle="tab">
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
        @else
          <div class="d-flex gap-2 justify-content-lg-end">
            <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#messageModal">
              <i class="fas fa-comment me-1"></i> Message Seller
            </button>
            
            <div class="dropdown">
              <button class="btn btn-outline-secondary rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-share-alt"></i>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="shareOnFacebook()"><i class="fab fa-facebook me-2"></i>Facebook</a></li>
                <li><a class="dropdown-item" href="#" onclick="shareOnTwitter()"><i class="fab fa-twitter me-2"></i>Twitter</a></li>
                <li><a class="dropdown-item" href="#" onclick="shareOnWhatsApp()"><i class="fab fa-whatsapp me-2"></i>WhatsApp</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="copyShopUrl('{{ url('shop/' . $shop->slug) }}')"><i class="fas fa-link me-2"></i>Copy Link</a></li>
              </ul>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>

<!-- Shop Navigation -->
<section class="bg-white border-bottom">
  <div class="container">
    <nav class="nav nav-tabs border-0">
      <a class="nav-link active" href="#items" data-bs-toggle="tab">Items</a>
      <a class="nav-link" href="#reviews" data-bs-toggle="tab">Reviews</a>
      <a class="nav-link" href="#about" data-bs-toggle="tab">About</a>
      <a class="nav-link" href="#policies" data-bs-toggle="tab">Shop Policies</a>
    </nav>
  </div>
</section>

<!-- Shop Announcement -->
@if(!empty($shop->announcement))
  <div class="container mt-4">
    <div class="alert alert-info shadow-sm border-0">
      <i class="fas fa-bullhorn me-2"></i>
      {{ $shop->announcement }}
    </div>
  </div>
@endif

<!-- Flash Message -->
@if(session('success'))
  <div class="container mt-4">
    <div class="alert alert-success shadow-sm border-0">{{ session('success') }}</div>
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
          class="w-100 rounded shadow-sm"
          style="height: 300px; object-fit: cover;"
        >
      </div>
    </div>
  </div>
</section>
@endif

<!-- Tab Content -->
<div class="tab-content">
  <!-- Items Tab -->
  <div class="tab-pane fade show active" id="items">
    <section class="py-5 bg-light">
      <div class="container">
        <!-- Advanced Filters -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label small fw-semibold">Price Range</label>
                <select class="form-select form-select-sm" id="priceFilter">
                  <option value="">Any Price</option>
                  <option value="0-10">Under $10</option>
                  <option value="10-25">$10 - $25</option>
                  <option value="25-50">$25 - $50</option>
                  <option value="50-100">$50 - $100</option>
                  <option value="100+">Over $100</option>
                </select>
              </div>
              <div class="col-md-3" style="display: none;">
                <label class="form-label small fw-semibold">Sort By</label>
                <select class="form-select form-select-sm" id="sortFilter">
                  <option value="newest">Most Recent</option>
                  <option value="price-low">Price: Low to High</option>
                  <option value="price-high">Price: High to Low</option>
                  <option value="rating">Best Rated</option>
                  <option value="popular">Most Popular</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-semibold">Product Type</label>
                <select class="form-select form-select-sm" id="typeFilter">
                  <option value="">All Types</option>
                  <option value="physical">Physical</option>
                  <option value="digital">Digital</option>
                  <option value="service">Service</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-semibold">View</label>
                <div class="btn-group btn-group-sm w-100" role="group">
                  <input type="radio" class="btn-check" name="viewMode" id="gridView" value="grid" checked>
                  <label class="btn btn-outline-secondary" for="gridView">
                    <i class="fas fa-th"></i>
                  </label>
                  <input type="radio" class="btn-check" name="viewMode" id="listView" value="list">
                  <label class="btn btn-outline-secondary" for="listView">
                    <i class="fas fa-list"></i>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="h5 fw-bold text-dark">All items ({{ $products->total() }})</h2>
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}</span>
          </div>
        </div>

        @if($products->isEmpty())
          <div class="alert alert-info shadow-sm border-0">
            <i class="fas fa-info-circle me-2"></i>
            This shop has not listed any products yet.
          </div>
        @else
          <!-- Grid View -->
          <div id="gridView" class="view-mode">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
              @foreach($products as $item)

               <div class="col-6 col-md-3 col-lg-3">
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          </div>

              @endforeach
            </div>
          </div>

        

          @if($products->hasPages())
            <div class="mt-4 d-flex justify-content-center">
              {{ $products->links('pagination::bootstrap-5') }}
            </div>
          @endif
        @endif
      </div>
    </section>
  </div>

  <!-- Reviews Tab -->
  <div class="tab-pane fade" id="reviews">
    <section class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <h3 class="h5 fw-bold mb-4">Shop Reviews</h3>
            @if($reviewCount > 0)
              @foreach($shop->reviews()->latest()->paginate(10) as $review)
                <div class="card mb-3 border-0 shadow-sm">
                  <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                      <div class="d-flex align-items-center me-2">
                        @for($i = 1; $i <= 5; $i++)
                          @if($i <= $review->rating)
                            <i class="fas fa-star text-warning"></i>
                          @else
                            <i class="far fa-star text-muted"></i>
                          @endif
                        @endfor
                      </div>
                      <span class="fw-semibold">{{ $review->user->name }}</span>
                      <span class="text-muted ms-2">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mb-0 text-secondary">{{ $review->comment }}</p>
                  </div>
                </div>
              @endforeach
            @else
              <div class="alert alert-info border-0">
                <i class="fas fa-info-circle me-2"></i>
                No reviews yet for this shop.
              </div>
            @endif
          </div>
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h5 class="card-title">Review Summary</h5>
                <div class="text-center mb-3">
                  <div class="display-6 fw-bold text-warning">{{ number_format($averageRating, 1) }}</div>
                  <div class="d-flex justify-content-center mb-2">
                    @for($i = 1; $i <= 5; $i++)
                      @if($i <= $averageRating)
                        <i class="fas fa-star text-warning"></i>
                      @else
                        <i class="far fa-star text-muted"></i>
                      @endif
                    @endfor
                  </div>
                  <span class="text-muted">{{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- About Tab -->
  <div class="tab-pane fade" id="about">
    <section class="py-5 bg-light">
      <div class="container">
        <div class="row g-4">
          <!-- About the Shop -->
          <div class="col-lg-8">
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

          <!-- Shop Details -->
          <div class="col-lg-4">
            <div class="card shadow-sm h-100 border-0">
              <div class="card-header bg-white fw-semibold border-bottom">Shop Details</div>
              <div class="card-body">
                <div class="mb-3">
                  <strong>Language:</strong><br>
                  <span class="text-muted">{{ $shop->language ?? 'N/A' }}</span>
                </div>
                <div class="mb-3">
                  <strong>Country:</strong><br>
                  <span class="text-muted">{{ country_name($shop->country) ?? 'N/A' }}</span>
                </div>
                <div class="mb-3">
                  <strong>Currency:</strong><br>
                  <span class="text-muted">{{ $shop->currency ?? 'N/A' }}</span>
                </div>
                <div class="mb-3">
                  <strong>Shop URL:</strong><br>
                  <div class="d-flex align-items-center gap-2">
                    <!-- <code class="small">{{ url('shop/' . $shop->slug) }}</code> -->
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
  </div>

  <!-- Policies Tab -->
  <div class="tab-pane fade" id="policies">
    <section class="py-5 bg-light">
      <div class="container">
        @if(!empty($shop->policies))
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">Shop Policies</div>
            <div class="card-body">
              <div class="text-start">
                {!! nl2br(e($shop->policies)) !!}
              </div>
            </div>
          </div>
        @else
          <div class="alert alert-info border-0">
            <i class="fas fa-info-circle me-2"></i>
            This shop has not provided any policies yet.
          </div>
        @endif
      </div>
    </section>
  </div>
</div>

{{-- Message Modal --}}
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('messages.store') }}">
      @csrf
      <input type="hidden" name="receiver_id" value="{{ $shop->user_id }}">
      <input type="hidden" name="product_id" value="{{ $shop->user_id }}">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Message Seller</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label for="messageBody" class="form-label">Your message</label>
        <textarea id="messageBody" name="message" rows="4" class="form-control" required></textarea>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Send Message</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
// Filter and sort functionality
document.addEventListener('DOMContentLoaded', function() {
    const priceFilter = document.getElementById('priceFilter');
    const sortFilter = document.getElementById('sortFilter');
    const typeFilter = document.getElementById('typeFilter');
    const viewModeInputs = document.querySelectorAll('input[name="viewMode"]');
    
    // View mode toggle
    viewModeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            
            if (this.value === 'grid') {
                gridView.classList.remove('d-none');
                listView.classList.add('d-none');
            } else {
                gridView.classList.add('d-none');
                listView.classList.remove('d-none');
            }
        });
    });
    
    // Filter functionality
    function filterProducts() {
        const priceRange = priceFilter.value;
        const productType = typeFilter.value;
        const products = document.querySelectorAll('.product-item');
        
        products.forEach(product => {
            const price = parseFloat(product.dataset.price);
            const type = product.dataset.type;
            let show = true;
            
            // Price filter
            if (priceRange) {
                const [min, max] = priceRange.split('-').map(p => p === '+' ? Infinity : parseFloat(p));
                if (price < min || (max !== Infinity && price > max)) {
                    show = false;
                }
            }
            
            // Type filter
            if (productType && type !== productType) {
                show = false;
            }
            
            product.style.display = show ? 'block' : 'none';
        });
    }
    
    // Sort functionality
    function sortProducts() {
        const sortBy = sortFilter.value;
        const container = document.querySelector('.row');
        const products = Array.from(document.querySelectorAll('.product-item'));
        
        products.sort((a, b) => {
            const priceA = parseFloat(a.dataset.price);
            const priceB = parseFloat(b.dataset.price);
            const ratingA = parseFloat(a.dataset.rating);
            const ratingB = parseFloat(b.dataset.rating);
            
            switch(sortBy) {
                case 'price-low':
                    return priceA - priceB;
                case 'price-high':
                    return priceB - priceA;
                case 'rating':
                    return ratingB - ratingA;
                default:
                    return 0;
            }
        });
        
        products.forEach(product => container.appendChild(product));
    }
    
    priceFilter.addEventListener('change', filterProducts);
    typeFilter.addEventListener('change', filterProducts);
    sortFilter.addEventListener('change', sortProducts);
});

function copyShopUrl(url) {
    const tempInput = document.createElement('input');
    tempInput.value = url;
    document.body.appendChild(tempInput);
    
    tempInput.select();
    tempInput.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        
        const button = event.target.closest('button');
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.remove('btn-outline-success');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalIcon;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-success');
        }, 2000);
        
    } catch (err) {
        console.error('Failed to copy URL: ', err);
        alert('Failed to copy URL. Please copy manually.');
    }
    
    document.body.removeChild(tempInput);
}

function contactShop() {
    alert('Contact functionality will be implemented here');
}

function followShop() {
    alert('Follow functionality will be implemented here');
}

function addToWishlist(productId) {
    // Implement wishlist functionality
    alert('Add to wishlist functionality will be implemented here');
}

function addToCart(productId) {
    // Implement cart functionality
    alert('Add to cart functionality will be implemented here');
}

function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent('Check out this amazing shop on Cetsy!');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent('Check out this amazing shop on Cetsy!');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
}

function shareOnWhatsApp() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent('Check out this amazing shop on Cetsy!');
    window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush

@push('styles')
<style>
.product-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
    padding: 1rem 1.5rem;
}

.nav-tabs .nav-link.active {
    color: #198754;
    background: none;
    border-bottom: 2px solid #198754;
}

.nav-tabs .nav-link:hover {
    color: #198754;
    border-color: transparent;
}

.btn-check:checked + .btn-outline-secondary {
    background-color: #198754;
    border-color: #198754;
    color: white;
}

.view-mode {
    transition: opacity 0.3s ease;
}

.view-mode.d-none {
    opacity: 0;
}
</style>
@endpush
