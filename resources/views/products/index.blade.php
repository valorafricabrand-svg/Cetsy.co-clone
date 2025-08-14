{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">My Listings</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-1"></i> Add New Listing
        </a>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Status‐counts bar --}}
    <div class="mb-4">
        @php
            $labels = [
                0        => 'Pending',
                1        => 'Active',
                2        => 'Paused',
                3        => 'Suspended',
                'closed' => 'Closed',
            ];
            $classes = [
                0        => 'warning',
                1        => 'success',
                2        => 'secondary',
                3        => 'secondary',
                'closed' => 'dark',
            ];
        @endphp

        @foreach($labels as $key => $label)
            <a
                href="{{ route('products.index', array_merge(request()->except('page'), ['status' => $key])) }}"
                class="btn btn-{{ $classes[$key] }} btn-sm me-2"
            >
                {{ $label }}
                <span class="badge bg-light text-dark ms-1">
                    {{ $statusCounts[$key] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    @if($products->count())
        <div class="row g-4">
            @foreach($products as $product)
                          <div class="col-md-6 col-lg-4">
                    {{-- position-relative lets us place the badge --}}
                    <div class="card position-relative h-100 shadow-sm border-0 rounded-4">

                     

                         @php
          switch($product->is_active) {
            case 0: $label='Pending'; $class='warning'; break;
            case 1: $label='Active';  $class='success'; break;
            case 2: $label='Paused';  $class='secondary'; break;
            case 3: $label='Suspended';  $class='secondary'; break;
            default:$label='Closed';  $class='dark'; break;
          }
        @endphp
        <span class="badge bg-{{ $class }} text-white position-absolute top-0 start-0 m-2">{{ $label }}</span>


  @php
      // If featured_image is a full URL use it; otherwise assume it's a storage path
      $thumb = null;
      $mediaType = 'image';
      if (!empty($product->featured_image)) {
          $thumb = str_starts_with($product->featured_image, 'http')
                  ? $product->featured_image
                  : asset('storage/' . ltrim($product->featured_image, '/'));
      } else {
          $firstMedia = $product->media->first();
          $thumb = $firstMedia
                  ? asset('storage/' . ltrim($firstMedia->url, '/'))
                  : asset('storage/placeholder.jpg');
          $mediaType = $firstMedia->type ?? 'image';
      }
    @endphp

  
                        {{-- Image --}}
                        @if($thumb)
                            @if($mediaType === 'video')
                                <video src="{{ $thumb }}" class="card-img-top rounded-top-4" style="height:220px;object-fit:cover;" controls></video>
                            @else
                                <img src="{{ $thumb }}"
                                     class="card-img-top rounded-top-4"
                                     style="height:220px;object-fit:cover;"
                                     alt="{{ $product->name }}">
                            @endif
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                 style="height:220px;">
                                <span class="text-muted">No Media</span>
                            </div>
                        @endif

                        {{-- Body --}}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">{{ Str::limit($product->name, 40) }}</h5>
                            <p class="mb-2 text-muted small">
                                {{ ucfirst($product->type) }}
                                @if(!is_null($product->stock))
                                    | Stock: {{ $product->stock }}
                                @endif
                            </p>

                            {{-- Price --}}
                            <p class="fw-bold mb-3">
                                @if($product->discount_price)
                                    <span class="text-danger me-2">
                                        {{ get_currency() }} {{ number_format($product->discount_price) }}
                                    </span>
                                    <span class="text-muted text-decoration-line-through">
                                        {{ get_currency() }} {{ number_format($product->price) }}
                                    </span>
                                @else
                                    <span>{{ get_currency() }} {{ number_format($product->price) }}</span>
                                @endif
                            </p>

                            {{-- Actions --}}
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('products.show', $product) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                              
                                <form action="{{ route('products.duplicate', $product) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-copy me-1"></i> Duplicate
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info rounded-3 text-center py-4">
            You haven’t listed any products yet.
            <div class="mt-2">
                <a href="{{ route('products.create') }}" class="btn btn-sm btn-success rounded-pill">
                    <i class="fas fa-plus-circle me-1"></i> Create Your First Product
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
