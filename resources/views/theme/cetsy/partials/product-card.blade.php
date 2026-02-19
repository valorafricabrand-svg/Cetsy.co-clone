{{-- resources/views/theme/cetsy/partials/product-card.blade.php --}}
@props(['item'])

@php
  $basePrice  = (float) ($item->price ?? 0);
  $salePrice  = (float) ($item->discounted_price ?? $basePrice);
  $isService  = (strtolower((string) ($item->type ?? '')) === 'service');

  $lowestVariantPrice = null;
  if ($item->relationLoaded('variations') && $item->variations) {
      $lowestVariantPrice = $item->variations
          ->pluck('price')
          ->filter(fn ($v) => $v !== null)
          ->min();
  } else {
      $lowestVariantPrice = $item->variations()
          ->whereNotNull('price')
          ->min('price');
  }

  $formatMoney = fn($n) => money((float) $n, null);
  $hasVariantPricing = $lowestVariantPrice !== null;

  $isReserved = (($item->type ?? '') === 'physical')
      && (int) ($item->stock ?? 0) === 1
      && (($item->is_reserved ?? false));

  $thumb = product_thumb_url($item);
  $firstImage = $item->relationLoaded('media')
      ? $item->media->firstWhere('type', 'image')
      : optional($item->media)->firstWhere('type', 'image');
  $firstVideo = $item->relationLoaded('media')
      ? $item->media->firstWhere('type', 'video')
      : optional($item->media)->firstWhere('type', 'video');
  $hasVideo = (bool) ($item->relationLoaded('media')
      ? optional($item->media)->firstWhere('type', 'video')
      : optional($item->media)->firstWhere('type', 'video'));

  $dataVideoSrc = (isset($firstVideo) && $firstVideo && empty($firstImage) && empty($item->featured_image))
      ? media_url($firstVideo->url)
      : null;

  $shop = $item->shop;
  $shopAvg = $shop ? ($shop->reviews_avg_rating ?? $shop->reviews()->avg('rating')) : null;
  $shopCount = $shop ? ($shop->reviews_count ?? $shop->reviews()->count()) : null;
  $avg = max(0, min(5, (int) round((float) ($shopAvg ?? ($item->reviews_avg_rating ?? 0)))));
  $reviewsCnt = (int) ($shopCount ?? ($item->reviews_count ?? 0));
@endphp

<a href="{{ route('listing.show', $item->slug) }}"
   class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">

  <div class="relative aspect-square overflow-hidden bg-slate-100">
    @if($isReserved)
      <span class="absolute right-2 top-2 z-10 rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-semibold text-white">Reserved</span>
    @endif

    @if($hasVideo)
      <span class="absolute left-2 top-2 z-10 inline-flex items-center gap-1 rounded-md bg-slate-900/80 px-2 py-0.5 text-[10px] font-semibold text-white">
        <i class="fas fa-play text-[9px]"></i> Video
      </span>
    @endif

    <img src="{{ $thumb }}"
         alt="{{ $item->name }}"
         class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.03]"
         onerror="this.onerror=null;this.src=@json(asset('assets/images/default-og-image-cetsy.jpg'));"
         @if($dataVideoSrc) data-video-src="{{ $dataVideoSrc }}" style="opacity:.01;filter:blur(8px);transition:opacity .35s ease,filter .35s ease;" @endif
         loading="lazy" decoding="async">
  </div>

  <div class="flex flex-1 flex-col p-3">
    <h3 class="text-sm font-semibold text-slate-900"
        style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
      {{ $item->name }}
    </h3>

    <div class="mt-2 flex items-center gap-1 text-[11px] text-amber-500">
      @for($i = 1; $i <= 5; $i++)
        <i class="fa-star {{ $i <= $avg ? 'fa-solid' : 'fa-regular text-slate-300' }}"></i>
      @endfor
      @if($reviewsCnt)
        <span class="ml-1 text-slate-400">({{ $reviewsCnt }})</span>
      @endif
    </div>

    <div class="mt-3">
      @if ($isService)
        <p class="text-[11px] uppercase tracking-[0.12em] text-slate-400">Priced From</p>
        <p class="text-sm font-bold text-emerald-700">
          {{ $formatMoney($hasVariantPricing ? $lowestVariantPrice : ($salePrice < $basePrice ? $salePrice : $basePrice)) }}
        </p>
      @else
        @if ($hasVariantPricing)
          <p class="text-[11px] uppercase tracking-[0.12em] text-slate-400">From</p>
          <p class="text-sm font-bold text-emerald-700">{{ $formatMoney($lowestVariantPrice) }}</p>
        @else
          @if ($salePrice < $basePrice)
            <div class="flex items-center gap-2">
              <span class="text-sm font-bold text-emerald-700">{{ $formatMoney($salePrice) }}</span>
              <span class="text-xs text-slate-400 line-through">{{ $formatMoney($basePrice) }}</span>
            </div>
          @else
            <p class="text-sm font-bold text-emerald-700">{{ $formatMoney($basePrice) }}</p>
          @endif
        @endif
      @endif
    </div>
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
          imgs.forEach(toFirstFrame);
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
