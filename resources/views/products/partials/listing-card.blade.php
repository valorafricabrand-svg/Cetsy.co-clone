@php
    switch ($product->is_active) {
        case 0:
            $statusLabel = 'Pending';
            $statusClass = 'warning';
            break;
        case 1:
            $statusLabel = 'Active';
            $statusClass = 'success';
            break;
        case 2:
            $statusLabel = 'Paused';
            $statusClass = 'secondary';
            break;
        case 3:
            $statusLabel = 'Suspended';
            $statusClass = 'secondary';
            break;
        default:
            $statusLabel = 'Closed';
            $statusClass = 'dark';
            break;
    }

    $thumb = null;
    $mediaType = 'image';

    if (! empty($product->featured_image)) {
        $thumb = str_starts_with($product->featured_image, 'http')
            ? $product->featured_image
            : asset('storage/' . ltrim($product->featured_image, '/'));
    } else {
        $firstMedia = $product->media->first();

        if ($firstMedia) {
            $thumb = asset('storage/' . ltrim($firstMedia->url, '/'));
            $mediaType = $firstMedia->type ?? 'image';
        } else {
            $shopLogo = ($product->shop && $product->shop->logo)
                ? asset('storage/' . ltrim($product->shop->logo, '/'))
                : (setting('favicon_url') ?: asset('storage/placeholder.jpg'));

            $thumb = $shopLogo;
            $mediaType = 'image';
        }
    }
@endphp

<div class="col-md-6 col-lg-4">
    <div class="card position-relative h-100 shadow-sm border-0 rounded-4">
        <span class="badge bg-{{ $statusClass }} text-white position-absolute top-0 start-0 m-2">{{ $statusLabel }}</span>

        @if ($thumb)
            @if ($mediaType === 'video')
                <video src="{{ $thumb }}" class="card-img-top rounded-top-4" style="height:220px;object-fit:cover;" controls></video>
            @else
                <img src="{{ $thumb }}" class="card-img-top rounded-top-4" style="height:220px;object-fit:cover;" alt="{{ $product->name }}">
            @endif
        @else
            <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px;">
                <span class="text-muted">No Media</span>
            </div>
        @endif

        <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1">{{ Str::limit($product->name, 40) }}</h5>
            @php
                // Variation awareness for seller: show if variants exist and compute a lowest priced variant
                $hasVariants = false;
                $lowestVariantPrice = null;
                if ($product->relationLoaded('variations')) {
                    $hasVariants = $product->variations && $product->variations->count() > 0;
                    if ($hasVariants) {
                        $lowestVariantPrice = optional($product->variations)->whereNotNull('price')->min('price');
                    }
                } else {
                    // Fallback safe lazy check
                    $hasVariants = $product->variations()->exists();
                    if ($hasVariants) {
                        $lowestVariantPrice = $product->variations()->whereNotNull('price')->min('price');
                    }
                }
                $formatMoney = fn($n) => money((float) $n, null);
            @endphp

            <p class="mb-2 text-muted small d-flex align-items-center flex-wrap gap-2">
                <span>{{ ucfirst($product->type ?? 'Listing') }}</span>
                @if (! is_null($product->stock))
                    <span>| Stock: {{ $product->stock }}</span>
                @endif
                @if ($hasVariants)
                    <span class="badge bg-info bg-opacity-10 text-info">Has variations</span>
                @else
                    <span class="badge bg-light text-muted">No variations</span>
                @endif
            </p>

            <div class="fw-bold mb-3">
                @if (!is_null($lowestVariantPrice))
                    <div class="small text-muted lh-1">From</div>
                    <div class="text-success">{{ $formatMoney($lowestVariantPrice) }}</div>
                @else
                    @if (!empty($product->discount_price) && $product->discount_price < ($product->price ?? 0))
                        <span class="text-success me-2">{{ $formatMoney($product->discount_price) }}</span>
                        <span class="text-muted text-decoration-line-through">{{ $formatMoney($product->price) }}</span>
                    @else
                        <span class="text-success">{{ $formatMoney($product->price) }}</span>
                    @endif
                @endif
            </div>

            <div class="mt-auto d-flex flex-wrap gap-2">
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-1"></i> View
                </a>

                <form action="{{ route('products.duplicate', $product) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-copy me-1"></i> Duplicate
                    </button>
                </form>

                @if ($hasVariants)
                    <a href="{{ route('products.variations', $product) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-layer-group me-1"></i> Manage Variations
                    </a>
                @else
                    <a href="{{ route('products.variations', $product) }}" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-plus me-1"></i> Add Variations
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
