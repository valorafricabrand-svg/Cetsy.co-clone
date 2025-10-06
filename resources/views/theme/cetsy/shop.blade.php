@extends('theme.'.theme().'.layouts.app')

@section('main')
<!-- Shop Hero Section -->
<section class="py-5 bg-white border-bottom">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-9 d-flex align-items-center gap-4">
        {{-- Shop Logo --}}
<img 
  src="{{ $shop->logo 
      ? asset('storage/' . $shop->logo) 
      : setting('favicon_url') }}" 
  alt="{{ $shop->name }} logo" 
  class="rounded-circle shadow-sm border" 
  style="width:80px; height:80px; object-fit:cover;" 
>


        <div class="flex-grow-1">
          <h1 class="h4 fw-bold mb-1">{{ $shop->name }}</h1>
          <span class="text-muted d-block mb-2">{{ country_name($shop->country) }}</span>

          {{-- Shop Stats --}}
          @php
            $totalSales     = $shop->orderItems()->whereRelation('order', 'status', 'completed')->count();
            $totalProducts  = $shop->products()->where('is_active', true)->count();
            $memberSince    = $shop->created_at->diffForHumans();
            $averageRating  = $shop->reviews()->avg('rating') ?? 0;
            $reviewCount    = $shop->reviews()->count();
          @endphp
          <div class="d-flex flex-wrap align-items-center gap-4 mb-3">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-shopping-bag text-success"></i>
              <small class="text-muted">{{ $totalSales }} sales</small>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-box text-primary"></i>
              <small class="text-muted">{{ $totalProducts }} items</small>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-calendar text-info"></i>
              <small class="text-muted">Since {{ $memberSince }}</small>
            </div>
            @if($reviewCount)
              <div class="d-flex align-items-center gap-2">
                <i class="fas fa-star text-warning"></i>
                <small class="text-muted">{{ number_format($averageRating,1) }} ({{ $reviewCount }})</small>
              </div>
            @endif
          </div>

          {{-- Reviews & Stars --}}
          <div>
            @if($reviewCount)
              <a href="#reviews" class="text-decoration-none">
                <div class="d-flex align-items-center gap-2">
                  <div class="d-flex align-items-center">
                    @for($i=1; $i<=5; $i++)
                      @if($i <= floor($averageRating))
                        <i class="fas fa-star text-warning" style="font-size:16px;"></i>
                      @elseif($i - $averageRating < 1)
                        <i class="fas fa-star-half-alt text-warning" style="font-size:16px;"></i>
                      @else
                        <i class="far fa-star text-muted" style="font-size:16px;"></i>
                      @endif
                    @endfor
                  </div>
                  <small class="fw-semibold text-dark">{{ number_format($averageRating,1) }}</small>
                  <small class="text-muted">({{ $reviewCount }} reviews)</small>
                </div>
              </a>
            @else
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center">
                  @for($i=1; $i<=5; $i++)
                    <i class="far fa-star text-muted" style="font-size:16px;"></i>
                  @endfor
                </div>
                <small class="text-muted">No reviews yet</small>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
        @if(Auth::id() === $shop->user_id)
          <a href="{{ route('seller.shops.edit', $shop) }}" class="btn btn-outline-success rounded-pill">
            <i class="fas fa-edit me-1"></i> Edit Shop
          </a>
        @else
          <div class="d-flex justify-content-lg-end gap-2">
            <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#messageModal">
              <i class="fas fa-comment me-1"></i> Message Seller
            </button>
            <div class="dropdown">
              <button class="btn btn-outline-secondary rounded-pill dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-share-alt"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" onclick="shareOn('facebook')">
                  <i class="fab fa-facebook me-2"></i> Facebook
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="shareOn('twitter')">
                  <i class="fab fa-twitter me-2"></i> Twitter
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="shareOn('whatsapp')">
                  <i class="fab fa-whatsapp me-2"></i> WhatsApp
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="copyShopUrl('{{ url("shop/{$shop->slug}") }}')">
                  <i class="fas fa-link me-2"></i> Copy Link
                </a></li>
              </ul>
            </div>
          </div>
        @endif
      </div>

    </div>
  </div>
</section>

{{-- Navigation Tabs --}}
<section class="bg-white border-bottom">
  <div class="container">
    <ul class="nav nav-tabs nav-fill border-0">
      @foreach(['items'=>'Items','reviews'=>'Reviews','about'=>'About','policies'=>'Policies'] as $id=>$label)
        <li class="nav-item">
          <a class="nav-link @if($loop->first) active @endif" href="#{{ $id }}" data-bs-toggle="tab">{{ $label }}</a>
        </li>
      @endforeach
    </ul>
  </div>
</section>

{{-- Announcement & Flash --}}
@if($shop->announcement)
  <div class="container mt-4">
    <div class="alert alert-info shadow-sm border-0">
      <i class="fas fa-bullhorn me-2"></i>{!! $shop->announcement !!}
    </div>
  </div>
@endif
@if(session('success'))
  <div class="container mt-4">
    <div class="alert alert-success shadow-sm border-0">{{ session('success') }}</div>
  </div>
@endif

{{-- Featured Image --}}
@if($shop->featured_image)
  <section class="py-4 bg-white">
    <div class="container">
      <img src="{{ asset('storage/' . $shop->featured_image) }}"
           alt="Featured image for {{ $shop->name }}"
           class="w-100 rounded shadow-sm"
           style="height:300px; object-fit:cover;">
    </div>
  </section>
@endif

<div class="tab-content">

  {{-- Items Tab --}}
  <div class="tab-pane fade show active" id="items">
    <section class="py-5 bg-light">
      <div class="container">
        {{-- Filters & Controls --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
          <div class="d-flex flex-wrap gap-3">
            <div>
              <small class="form-label fw-semibold">Price</small>
              <select id="priceFilter" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="0-10">Under $10</option>
                <option value="10-25">$10–25</option>
                <option value="25-50">$25–50</option>
                <option value="50-100">$50–100</option>
                <option value="100+">Over $100</option>
              </select>
            </div>
            <div>
              <small class="form-label fw-semibold">Type</small>
              <select id="typeFilter" class="form-select form-select-sm">
                <option value="">All Types</option>
                <option value="physical">Products</option>
                <option value="digital">Digital</option>
                <option value="service">Services</option>
              </select>
            </div>
            <div>
              <small class="form-label fw-semibold">Sort By</small>
              <select id="sortFilter" class="form-select form-select-sm">
                <option value="newest">Newest</option>
                <option value="price-low">Price: Low → High</option>
                <option value="price-high">Price: High → Low</option>
                <option value="rating">Top Rated</option>
              </select>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="small text-muted">
              {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
            </span>
            <div class="btn-group btn-group-sm" role="group">
              <input type="radio" class="btn-check" name="viewMode" id="viewGrid" value="grid" autocomplete="off" checked>
              <label class="btn btn-outline-secondary" for="viewGrid"><i class="fas fa-th"></i></label>
              <input type="radio" class="btn-check" name="viewMode" id="viewList" value="list" autocomplete="off">
              <label class="btn btn-outline-secondary" for="viewList"><i class="fas fa-list"></i></label>
            </div>
          </div>
        </div>

        {{-- Grid View --}}
        <div id="gridView" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
          @forelse($products as $product)
            <div class="col product-item" data-price="{{ $product->price }}" data-type="{{ $product->type }}" data-rating="{{ $shop->reviews_avg_rating ?? ($shop->average_rating ?? 0) }}">
              @include('theme.'.theme().'.partials.product-card', ['item'=>$product])
            </div>
          @empty
            <div class="col-12">
              <div class="alert alert-info border-0 shadow-sm">
                <i class="fas fa-info-circle me-2"></i>No products listed.
              </div>
            </div>
          @endforelse
        </div>

        {{-- List View --}}
        <div id="listView" class="list-group d-none">
          @foreach($products as $product)
            @php
              if (!empty($product->featured_image)) {
                  $thumbUrl = str_starts_with($product->featured_image, 'http')
                            ? $product->featured_image
                            : asset('storage/' . ltrim($product->featured_image, '/'));
              } else {
                  $firstMedia = $product->media->first();
                  if ($firstMedia) {
                      $thumbUrl = asset('storage/' . ltrim($firstMedia->url, '/'));
                  } else {
                      $thumbUrl = ($product->shop && $product->shop->logo)
                                  ? asset('storage/' . ltrim($product->shop->logo, '/'))
                                  : (setting('favicon_url') ?: asset('storage/placeholder.jpg'));
                  }
              }
            @endphp
            <div class="list-group-item product-item d-flex align-items-center" data-price="{{ $product->price }}" data-type="{{ $product->type }}" data-rating="{{ $shop->reviews_avg_rating ?? ($shop->average_rating ?? 0) }}">
              <img src="{{ $thumbUrl }}" alt="{{ $product->name }}" class="rounded" style="width:80px; height:80px; object-fit:cover;">
              <div class="ms-3 flex-grow-1">
                <h6 class="mb-1">{{ $product->name }}</h6>
                <small class="text-muted">${{ number_format($product->price,2) }}</small>
              </div>
              <button class="btn btn-sm btn-success" onclick="addToCart({{ $product->id }})">
                <i class="fas fa-cart-plus"></i>
              </button>
            </div>
          @endforeach
        </div>

        {{-- Load More Button --}}
        @if($products->hasMorePages())
          <div class="mt-4 d-flex justify-content-center">
            <button id="loadMore" class="btn btn-outline-secondary" data-next-page-url="{{ $products->nextPageUrl() }}">
              Load More
            </button>
          </div>
        @endif
      </div>
    </section>
  </div>

  {{-- Reviews Tab --}}
  <div class="tab-pane fade" id="reviews">
    <section class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <h3 class="h5 fw-bold mb-4">Reviews</h3>
            @forelse($shop->reviews()->latest()->paginate(10) as $review)
              <div class="card mb-3 shadow-sm">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-2">
                    <div class="me-3">
                      @for($i=1;$i<=5;$i++)
                        <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star text-warning"></i>
                      @endfor
                    </div>
                    <div>
                      <strong>{{ $review->user->name }}</strong>
                      <small class="text-muted ms-2">{{ $review->created_at->diffForHumans() }}</small>
                    </div>
                  </div>
                  <p class="mb-0 text-secondary">{{ $review->comment }}</p>
                </div>
              </div>
            @empty
              <div class="alert alert-info border-0">
                <i class="fas fa-info-circle me-2"></i>No reviews yet.
              </div>
            @endforelse
          </div>
          <div class="col-lg-4">
            <div class="card shadow-sm">
              <div class="card-body text-center">
                <div class="display-6 fw-bold text-warning">{{ number_format($averageRating,1) }}</div>
                <div class="mb-2">
                  @for($i=1;$i<=5;$i++)
                    <i class="fa{{ $i <= floor($averageRating) ? 's' : 'r' }} fa-star text-warning"></i>
                  @endfor
                </div>
                <small class="text-muted">{{ $reviewCount }} reviews</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  {{-- About Tab --}}
  <div class="tab-pane fade" id="about">
    <section class="py-5 bg-light">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-8">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-white fw-semibold">About This Shop</div>
              <div class="card-body">
                {!! $shop->bio ? $shop->bio : 'No description provided.' !!}
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-white fw-semibold">Shop Details</div>
              <div class="card-body">
                <p class="mb-2"><strong>Language:</strong> <span class="text-muted">{{ $shop->language ?? 'N/A' }}</span></p>
                <p class="mb-2"><strong>Country:</strong> <span class="text-muted">{{ country_name($shop->country) }}</span></p>
                <p class="mb-2"><strong>Currency:</strong> <span class="text-muted">{{ $shop->currency ?? 'N/A' }}</span></p>
                <p class="mb-0"><strong>Shop URL:</strong>
                  <button class="btn btn-outline-success btn-sm ms-2" onclick="copyShopUrl('{{ url("shop/{$shop->slug}") }}')" data-bs-toggle="tooltip" title="Copy URL">
                    <i class="fas fa-link"></i>
                  </button>
                </p>
              </div>
            </div>
          </div>

          {{-- Billing Address --}}
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-white fw-semibold">Billing Address</div>
              <div class="card-body">
                <p class="mb-1 text-secondary">{{ $shop->address ?? 'N/A' }}</p>
                <p class="mb-0 text-secondary">{{ $shop->city }}{{ $shop->postal ? ', ' . $shop->postal : '' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  {{-- Policies Tab --}}
  <div class="tab-pane fade" id="policies">
    <section class="py-5 bg-light">
      <div class="container">
        @if($shop->policies)
          <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Shop Policies</div>
            <div class="card-body">
              {!! $shop->policies !!}
            </div>
          </div>
        @else
          <div class="alert alert-info border-0">
            <i class="fas fa-info-circle me-2"></i>No policies available.
          </div>
        @endif
      </div>
    </section>
  </div>

</div>

{{-- Message Modal --}}
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" action="{{ route('messages.store') }}" method="POST">
      @csrf
      <input type="hidden" name="receiver_id" value="{{ $shop->user_id }}">
      <input type="hidden" name="product_id" value="">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Message Seller – {{ $shop->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label for="messageBody" class="form-label">Your message</label>
        <textarea id="messageBody" name="message" class="form-control" rows="4" required></textarea>
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
    
    // Auto-scroll to tab content when tab is clicked
    const navTabs = document.querySelectorAll('.nav-tabs .nav-link');
    navTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the target tab content
            const targetId = this.getAttribute('href');
            const targetContent = document.querySelector(targetId);
            
            if (targetContent) {
                // Smooth scroll to the tab content
                targetContent.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start',
                    inline: 'nearest'
                });
                
                // Add a small delay to ensure the scroll happens before the tab switch
                setTimeout(() => {
                    // Trigger the tab switch
                    const tabTrigger = new bootstrap.Tab(this);
                    tabTrigger.show();
                }, 100);
            }
        });
    });
    
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
  .product-item:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
  .nav-tabs .nav-link { border: none; padding:1rem; font-weight:500; color:#6c757d; }
  .nav-tabs .nav-link.active { color:#198754; border-bottom:2px solid #198754; }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const filters = {
      price: document.getElementById('priceFilter'),
      type: document.getElementById('typeFilter'),
      sort: document.getElementById('sortFilter'),
      grid: document.getElementById('viewGrid'),
      list: document.getElementById('viewList')
    };
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');

    function items() {
      return Array.from(document.querySelectorAll('.product-item'));
    }

    function applyFilters() {
      items().forEach(el => {
        let show = true;
        const price = parseFloat(el.dataset.price);
        const type = el.dataset.type;
        const [min, max] = filters.price.value.split('-').map(x=>x==='+'?Infinity:parseFloat(x));

        if (filters.price.value && !(price >= min && (max===Infinity||price <= max))) show = false;
        if (filters.type.value && type !== filters.type.value) show = false;
        el.style.display = show ? '' : 'none';
      });
    }

    function applySort() {
      const sorted = items().filter(el => el.style.display !== 'none');
      const parent = gridView;
      sorted.sort((a,b) => {
        const aP = parseFloat(a.dataset.price), bP = parseFloat(b.dataset.price);
        const aR = parseFloat(a.dataset.rating), bR = parseFloat(b.dataset.rating);
        switch(filters.sort.value) {
          case 'price-low': return aP - bP;
          case 'price-high': return bP - aP;
          case 'rating': return bR - aR;
          default: return 0;
        }
      });
      sorted.forEach(el => parent.appendChild(el));
    }

    [filters.price, filters.type, filters.sort].forEach(el => el.addEventListener('change', () => {
      applyFilters(); applySort();
    }));

    filters.grid.addEventListener('change', () => {
      gridView.classList.remove('d-none');
      listView.classList.add('d-none');
    });
    filters.list.addEventListener('change', () => {
      gridView.classList.add('d-none');
      listView.classList.remove('d-none');
    });

    window.shareOn = (platform) => {
      const url = encodeURIComponent(location.href);
      const text = encodeURIComponent('Check out this shop on Cetsy!');
      const routes = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
        twitter:  `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
        whatsapp: `https://wa.me/?text=${text}%20${url}`
      };
      window.open(routes[platform], '_blank');
    };

    window.copyShopUrl = (url) => {
      navigator.clipboard.writeText(url)
        .then(() => alert('Shop URL copied!'))
        .catch(() => alert('Copy failed, please try manually.'));
    };

    const loadMoreBtn = document.getElementById('loadMore');
    if (loadMoreBtn) {
      loadMoreBtn.addEventListener('click', () => {
        const btn = loadMoreBtn;
        const nextUrl = btn.dataset.nextPageUrl;
        btn.disabled = true;
        btn.textContent = 'Loading...';

        fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })

          .then(res => res.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            doc.querySelectorAll('#gridItems .product-item').forEach(el => gridView.appendChild(el));
            doc.querySelectorAll('#listItems .product-item').forEach(el => listView.appendChild(el));

            const newBtn = doc.getElementById('loadMore');
            if (newBtn && newBtn.dataset.nextPageUrl) {
              btn.dataset.nextPageUrl = newBtn.dataset.nextPageUrl;
              btn.disabled = false;
              btn.textContent = 'Load More';
            } else {
              btn.remove();
            }
            applyFilters();
            applySort();
          })
          .catch(() => {
            btn.disabled = false;
            btn.textContent = 'Load More';
          });
      });
    }
  });
</script>
@endpush
