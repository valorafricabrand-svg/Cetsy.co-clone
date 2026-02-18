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

<div class="md:col-span-6 lg:col-span-4">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm position-relative h-full shadow-sm border-0 rounded-4 js-product-card"
         data-href="{{ route('products.show', $product) }}"
         tabindex="0"
         aria-label="Open {{ $product->name }} details">
        <span class="badge bg-{{ $statusClass }} text-white position-absolute top-0 start-0 m-2">{{ $statusLabel }}</span>
        @if(((int)($product->is_active ?? 0) === 1) && empty($product->featured_image))
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-slate-900 position-absolute top-0 end-0 m-2" title="This published listing has no featured image">No Featured Image</span>
        @endif

        @if ($thumb)
            @if ($mediaType === 'video')
                <video src="{{ $thumb }}" class="card-img-top rounded-top-4" style="height:220px;object-fit:cover;" controls></video>
            @else
                <img src="{{ $thumb }}" class="card-img-top rounded-top-4" style="height:220px;object-fit:cover;" alt="{{ $product->name }}"
                     onerror="this.onerror=null;this.src=@json(asset('assets/images/default-og-image-cetsy.jpg'));">
            @endif
        @else
            <div class="bg-slate-100 flex items-center justify-center" style="height:220px;">
                <span class="text-slate-500">No Media</span>
            </div>
        @endif

        <div class="p-4 sm:p-5 flex flex-col">
            <h5 class="text-lg font-semibold text-slate-900 mb-1">{{ Str::limit($product->name, 40) }}</h5>
            <div class="text-slate-500 text-xs mb-2">ID: #{{ $product->id }}</div>
            @php $due = $product->next_due_date ? \Carbon\Carbon::parse($product->next_due_date) : null; @endphp
            @if($due)
                <div class="text-xs mb-2">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900 border">
                        <i class="far fa-calendar-alt mr-1"></i>
                        {{ $due->isFuture() ? 'Expires' : 'Expired' }}: {{ $due->toFormattedDateString() }}
                    </span>
                </div>
            @endif
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

            <p class="mb-2 text-slate-500 text-xs flex items-center flex-wrap gap-2">
                <span>{{ ucfirst($product->type ?? 'Listing') }}</span>
                @if (! is_null($product->stock))
                    <span>| Stock: {{ $product->stock }}</span>
                @endif
                @if ($hasVariants)
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sky-100 bg-opacity-10 text-sky-600">Has variations</span>
                @else
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-500">No variations</span>
                @endif
            </p>

            <div class="font-bold mb-3">
                @php $isService = (strtolower((string)($product->type ?? '')) === 'service'); @endphp
                @if (!is_null($lowestVariantPrice))
                    <div class="text-xs text-slate-500 lh-1">{{ $isService ? 'Priced From' : 'From' }}</div>
                    <div class="text-emerald-600">{{ $formatMoney($lowestVariantPrice) }}</div>
                @else
                    @if ($isService)
                        <div class="text-xs text-slate-500 lh-1">Priced From</div>
                        <span class="text-emerald-600">{{ $formatMoney($product->discount_price && $product->discount_price < ($product->price ?? 0) ? $product->discount_price : $product->price) }}</span>
                        @if (!empty($product->discount_price) && $product->discount_price < ($product->price ?? 0))
                            <span class="text-slate-500 text-decoration-line-through ml-2">{{ $formatMoney($product->price) }}</span>
                        @endif
                    @else
                        @if (!empty($product->discount_price) && $product->discount_price < ($product->price ?? 0))
                            <span class="text-emerald-600 mr-2">{{ $formatMoney($product->discount_price) }}</span>
                            <span class="text-slate-500 text-decoration-line-through">{{ $formatMoney($product->price) }}</span>
                        @else
                            <span class="text-emerald-600">{{ $formatMoney($product->price) }}</span>
                        @endif
                    @endif
                @endif
            </div>

            <div class="mt-auto flex flex-wrap items-center gap-2">
                <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 py-1.5 text-xs">
                    <i class="fas fa-eye mr-1"></i> View
                </a>

                <div class="dropdown ms-auto">
                    <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-100 text-slate-700 hover:bg-slate-200 px-3 py-1.5 text-xs border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="absolute z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl dropdown-menu-end">
                        <li>
                            <form action="{{ route('products.duplicate', $product) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                    <i class="fas fa-copy mr-2"></i> Duplicate
                                </button>
                            </form>
                        </li>
                        <li>
                            <a href="{{ route('products.variations', $product) }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-layer-group mr-2"></i>
                                {{ $hasVariants ? 'Manage Variations' : 'Add Variations' }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


