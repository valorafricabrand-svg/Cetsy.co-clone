@php
    switch ((int) ($product->is_active ?? 0)) {
        case 0:
            $statusLabel = 'Pending';
            $statusClass = 'bg-amber-100 text-amber-800 border-amber-200';
            break;
        case 1:
            $statusLabel = 'Active';
            $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            break;
        case 2:
            $statusLabel = 'Paused';
            $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
            break;
        case 3:
            $statusLabel = 'Suspended';
            $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
            break;
        default:
            $statusLabel = 'Closed';
            $statusClass = 'bg-slate-200 text-slate-800 border-slate-300';
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

<div class="col-span-12 md:col-span-6 lg:col-span-4">
    <article class="js-product-card listing-card-shell relative h-full cursor-pointer overflow-visible rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
             data-href="{{ route('products.show', $product) }}"
             tabindex="0"
             aria-label="Open {{ $product->name }} details">
        <span class="absolute left-3 top-3 z-10 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>

        @if(((int)($product->is_active ?? 0) === 1) && empty($product->featured_image))
            <span class="absolute right-3 top-3 z-10 inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800" title="This published listing has no featured image">No Featured Image</span>
        @endif

        @if ($thumb)
            @if ($mediaType === 'video')
                <video src="{{ $thumb }}" class="h-56 w-full rounded-t-2xl object-cover" controls></video>
            @else
                <img src="{{ $thumb }}" class="h-56 w-full rounded-t-2xl object-cover" alt="{{ $product->name }}"
                     onerror="this.onerror=null;this.src=@json(asset('assets/images/cetsylogmain.png'));">
            @endif
        @else
            <div class="flex h-56 w-full items-center justify-center rounded-t-2xl bg-slate-100">
                <span class="text-sm text-slate-500">No Media</span>
            </div>
        @endif

        <div class="flex h-[calc(100%-14rem)] min-h-[15rem] flex-col p-4 sm:p-5">
            <h3 class="mb-1 line-clamp-2 text-base font-semibold text-slate-900">{{ Str::limit($product->name, 40) }}</h3>
            <div class="mb-2 text-xs text-slate-500">ID: #{{ $product->id }}</div>

            @php $due = $product->next_due_date ? \Carbon\Carbon::parse($product->next_due_date) : null; @endphp
            @if($due)
                <div class="mb-2 text-xs">
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 font-medium text-slate-700">
                        <i class="far fa-calendar-alt mr-1"></i>
                        {{ $due->isFuture() ? 'Expires' : 'Expired' }}: {{ $due->toFormattedDateString() }}
                    </span>
                </div>
            @endif

            @php
                $validVariants = collect();
                $lowestVariantPrice = null;
                if ($product->relationLoaded('variations')) {
                    $validVariants = collect($product->variations ?? [])
                        ->filter(fn($variant) => ($variant->options->count() ?? 0) > 0)
                        ->values();
                } else {
                    $validVariants = $product->variations()
                        ->whereHas('options')
                        ->get();
                }
                $hasVariants = $validVariants->isNotEmpty();
                if ($hasVariants) {
                    $lowestVariantPrice = $validVariants->whereNotNull('price')->min('price');
                }
                $formatMoney = fn($n) => money((float) $n, null);
            @endphp

            <p class="mb-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span>{{ ucfirst($product->type ?? 'Listing') }}</span>
                @if (($product->type ?? null) === 'physical' && ! is_null($product->stock))
                    <span>| Stock: {{ $product->stock }}</span>
                @endif
                @if ($hasVariants)
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-medium text-sky-700">Has variations</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">No variations</span>
                @endif
            </p>

            <div class="mb-3 font-bold">
                @php $isService = (strtolower((string)($product->type ?? '')) === 'service'); @endphp
                @if (!is_null($lowestVariantPrice))
                    <div class="text-xs text-slate-500">{{ $isService ? 'Priced From' : 'From' }}</div>
                    <div class="text-emerald-600">{{ $formatMoney($lowestVariantPrice) }}</div>
                @else
                    @if ($isService)
                        <div class="text-xs text-slate-500">Priced From</div>
                        <span class="text-emerald-600">{{ $formatMoney($product->discount_price && $product->discount_price < ($product->price ?? 0) ? $product->discount_price : $product->price) }}</span>
                        @if (!empty($product->discount_price) && $product->discount_price < ($product->price ?? 0))
                            <span class="ml-2 text-slate-500 line-through">{{ $formatMoney($product->price) }}</span>
                        @endif
                    @else
                        @if (!empty($product->discount_price) && $product->discount_price < ($product->price ?? 0))
                            <span class="mr-2 text-emerald-600">{{ $formatMoney($product->discount_price) }}</span>
                            <span class="text-slate-500 line-through">{{ $formatMoney($product->price) }}</span>
                        @else
                            <span class="text-emerald-600">{{ $formatMoney($product->price) }}</span>
                        @endif
                    @endif
                @endif
            </div>

            <div class="mt-auto flex items-center gap-2">
                <a href="{{ route('products.show', $product) }}" class="inline-flex items-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                    <i class="fas fa-eye mr-1"></i> View
                </a>

                <details class="listing-card-menu relative ml-auto">
                    <summary class="inline-flex list-none items-center rounded-xl border border-slate-300 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </summary>
                    <div class="listing-card-menu-panel absolute right-0 top-full z-20 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                        <form action="{{ route('products.duplicate', $product) }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100">
                                <i class="fas fa-copy mr-2"></i> Duplicate
                            </button>
                        </form>
                        <a href="{{ route('products.variations', $product) }}" class="mt-1 block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100">
                            <i class="fa-solid fa-layer-group mr-2"></i>
                            {{ $hasVariants ? 'Manage Variations' : 'Add Variations' }}
                        </a>
                    </div>
                </details>
            </div>
        </div>
    </article>
</div>
