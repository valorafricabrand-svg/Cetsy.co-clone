{{-- resources/views/theme/cetsy/partials/product-card.blade.php --}}
@props(['item'])   {{-- expects a \App\Models\Product in $item --}}


@once
  @push('styles')
    <style>
      /* Ratio utility (Bootstrap 5 polyfill) to ensure uniform thumbs */
      .ratio { position: relative; width: 100%; }
      .ratio::before { display: block; content: ""; padding-top: var(--bs-aspect-ratio); }
      .ratio > * { position: absolute; inset: 0; width: 100%; height: 100%; }
      .ratio-1x1 { --bs-aspect-ratio: 100%; }
      /* Thumb container background + media fit */
      .product-thumb { background: #f1f3f5; }
      .product-thumb img, .product-thumb video { object-fit: contain; }
      .badge-video { position:absolute; top:.4rem; left:.4rem; background:rgba(0,0,0,.7); color:#fff; font-size:.72rem; border-radius:.5rem; padding:.15rem .4rem; display:inline-flex; align-items:center; gap:.25rem; }
      .badge-video i{ font-size:.7rem; }
          .product-card { display:flex; }
      .product-card .card-body { display:flex; flex-direction:column; }
      .product-card .title-clamp { display:-webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow:hidden; }
    </style>
  @endpush
@endonce
@php
  $currency   = get_currency();
  $basePrice  = (float) ($item->price ?? 0);
  $salePrice  = (float) ($item->discounted_price ?? $basePrice);
  $isService  = (strtolower((string)($item->type ?? '')) === 'service');

  // Lowest variation price (only consider variants that actually have a price)
  // Make sure controller eager-loads: with('variations.options', 'media', 'shop', ...)
  $lowestVariantPrice = null;
  if ($item->relationLoaded('variations')) {
      $lowestVariantPrice = optional($item->variations)->whereNotNull('price')->min('price');
  } else {
      // still works without eager load (will lazy load)
      $lowestVariantPrice = optional($item->variations)->whereNotNull('price')->min('price');
  }

  // The price to display on listing:
  // - If variants exist with prices => "From {lowest variant}"
  // - Else => product sale/base price (show strike-through if discounted)
  $formatMoney = fn($n) => money((float)$n, null);

  // Decide how to render:
  $hasVariantPricing = $lowestVariantPrice !== null;
@endphp

<a href="{{ route('listing.show', $item->slug) }}"
   class="card text-decoration-none border-0 shadow-sm h-100 product-card">

  {{-- Thumbnail --}}
  <div class="ratio ratio-1x1 rounded-top overflow-hidden product-thumb">
    
@php
      // Build a safe thumbnail: prefer featured image, else first image media, else shop logo/placeholder.
      $thumb = null; $mediaType = 'image';
      if (!empty($item->featured_image)) {
        $thumb = str_starts_with($item->featured_image, 'http')
                ? $item->featured_image
                : asset('storage/' . ltrim($item->featured_image, '/'));
      } else {
        $firstImage = $item->relationLoaded('media') ? $item->media->firstWhere('type','image') : optional($item->media)->firstWhere('type','image');
        $firstVideo = $item->relationLoaded('media') ? $item->media->firstWhere('type','video') : optional($item->media)->firstWhere('type','video');
        if ($firstImage && !empty($firstImage->url)) {
          $thumb = asset('storage/' . ltrim($firstImage->url, '/'));
          $mediaType = 'image';
        } else {
          // No image in media; avoid using video files as <img> sources in listings
          $shopLogo = ($item->shop && $item->shop->logo)
                      ? asset('storage/' . ltrim($item->shop->logo, '/'))
                      : (setting('favicon_url') ?: asset('assets/img/placeholder.svg'));
          $thumb = $shopLogo;
          $mediaType = 'image';
        }
      }
      // Mark if product has any video media (to show a badge)
      $hasVideo = $item->relationLoaded('media') ? (bool) optional($item->media)->firstWhere('type','video') : (bool) optional($item->media)->firstWhere('type','video');
    @endphp

    @if($hasVideo)
      <span class="badge-video"><i class="fas fa-play"></i> Video</span>
    @endif

    @php
      $dataVideoSrc = (isset($firstVideo) && $firstVideo && empty($firstImage) && empty($item->featured_image))
        ? asset('storage/' . ltrim($firstVideo->url,'/'))
        : null;
    @endphp
    <img src="{{ $thumb }}"
         alt="{{ $item->name }}"
         class="img-fluid w-100 h-100"
         @if($dataVideoSrc) data-video-src="{{ $dataVideoSrc }}" style="opacity:.01; filter:blur(8px); transition:opacity .35s ease, filter .35s ease;" @endif
         loading="lazy" decoding="async">
  </div>

  <div class="card-body p-2 d-flex flex-column">

    {{-- Title --}}
    <h3 class="h6 mb-1 fw-semibold text-dark title-clamp">{{ $item->name }}</h3>

    {{-- Rating (shop-wide) --}}
    
@php
      // Prefer shop's overall rating/count; fallback to product values if needed
      $shop        = $item->shop;
      $shopAvg     = $shop ? ($shop->reviews_avg_rating ?? $shop->reviews()->avg('rating')) : null;
      $shopCount   = $shop ? ($shop->reviews_count ?? $shop->reviews()->count()) : null;
      $avg         = round((float) ($shopAvg ?? ($item->reviews_avg_rating ?? 0)));
      $reviewsCnt  = (int) ($shopCount ?? ($item->reviews_count ?? 0));
    @endphp
    <div class="mb-1 small text-warning">
      @for($i = 1; $i <= 5; $i++)
        <i class="fa-star{{ $i <= $avg ? ' fa-solid' : ' fa-regular text-muted' }}"></i>
      @endfor
      @if($reviewsCnt)
        <span class="text-muted">({{ $reviewsCnt }})</span>
      @endif
    </div>

    {{-- Price --}}
    @if ($isService)
      {{-- Services: always show "Priced From" --}}
      <div class="mb-3">
        <div class="small text-muted lh-1">Priced From</div>
        <div class="fw-bold text-success">
          {{ $formatMoney($hasVariantPricing ? $lowestVariantPrice : ($salePrice < $basePrice ? $salePrice : $basePrice)) }}
        </div>
      </div>
    @else
      @if ($hasVariantPricing)
        {{-- Variant-based listing price: show the lowest as "From" --}}
        <div class="mb-3">
          <div class="small text-muted lh-1">From</div>
          <div class="fw-bold text-success">{{ $formatMoney($lowestVariantPrice) }}</div>
        </div>
      @else
        {{-- No variant pricing, use product pricing with discount display if applicable --}}
        @if ($salePrice < $basePrice)
          <div class="d-flex align-items-baseline gap-3 mb-3">
            <span class="fw-bold text-success">{{ $formatMoney($salePrice) }}</span>
            <span class="text-muted text-decoration-line-through">{{ $formatMoney($basePrice) }}</span>
          </div>
        @else
          <p class="fw-bold text-success mb-3">{{ $formatMoney($basePrice) }}</p>
        @endif
      @endif
    @endif

  </div>
</a>
  @once
    @push('scripts')
      <script>
      (function(){
        if (window.__videoThumbInit) return; window.__videoThumbInit = true;
        function toFirstFrame(img){
          var src = img.getAttribute('data-video-src');
          if(!src) return;
          try{
            var v = document.createElement('video');
            v.preload = 'metadata';
            v.muted = true; v.playsInline = true; v.src = src + '#t=0.1';
            v.addEventListener('loadeddata', function(){
              try{
                var w = v.videoWidth || 480, h = v.videoHeight || 270;
                var c = document.createElement('canvas'); c.width = w; c.height = h;
                var ctx = c.getContext('2d'); ctx.drawImage(v,0,0,w,h);
                img.src = c.toDataURL('image/jpeg', 0.8);
                img.style.opacity = '1';
                img.style.filter = 'none';
                img.removeAttribute('data-video-src');
              }catch(e){}
            }, { once: true });
          }catch(e){}
        }
        function init(){
          var imgs = document.querySelectorAll('img[data-video-src]');
          if (!('IntersectionObserver' in window)) {
            imgs.forEach(toFirstFrame); // fallback
            return;
          }
          var io = new IntersectionObserver(function(entries){
            entries.forEach(function(entry){
              if(entry.isIntersecting){
                toFirstFrame(entry.target);
                io.unobserve(entry.target);
              }
            });
          }, { rootMargin: '200px' });
          imgs.forEach(function(img){ io.observe(img); });
        }
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', init);
        } else { init(); }
      })();
      </script>
    @endpush
  @endonce
