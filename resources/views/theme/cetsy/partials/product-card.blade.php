{{-- resources/views/theme/cetsy/partials/product-card.blade.php --}}
@props(['item'])

@php
  $basePrice  = (float) ($item->price ?? 0);
  $salePrice  = (float) ($item->discounted_price ?? $basePrice);
  $effectiveType = product_effective_type($item);
  $isService  = ($effectiveType === 'service');

  $lowestVariantPrice = null;
  if ($item->relationLoaded('variations') && $item->variations) {
      $lowestVariantPrice = $item->variations
          ->filter(fn ($variant) => ($variant->options->count() ?? 0) > 0)
          ->pluck('price')
          ->filter(fn ($v) => $v !== null)
          ->min();
  } else {
      $lowestVariantPrice = $item->variations()
          ->whereHas('options')
          ->whereNotNull('price')
          ->min('price');
  }

  $formatMoney = fn($n) => money((float) $n, null);
  $hasVariantPricing = $lowestVariantPrice !== null;

  $isReserved = ($effectiveType === 'physical')
      && (int) ($item->stock ?? 0) === 1
      && (($item->is_reserved ?? false));
  $isDigitalPreview = product_is_digital($item);

  $thumb = product_thumb_url($item);
  $mediaItems = $item->media ?? collect();

  $firstImage = optional($mediaItems)->first(function ($media) {
      $url = (string) ($media->url ?? '');
      if ($url === '') return false;
      $type = strtolower((string) ($media->type ?? ''));
      if ($type === 'image') return true;
      if ($type === 'video') return false;
      return function_exists('is_video_media_path') ? !is_video_media_path($url) : true;
  });

  $firstVideo = optional($mediaItems)->first(function ($media) {
      $url = (string) ($media->url ?? '');
      if ($url === '') return false;
      $type = strtolower((string) ($media->type ?? ''));
      if ($type === 'video') return true;
      return function_exists('is_video_media_path') ? is_video_media_path($url) : false;
  });

  $featuredMedia = (string) ($item->featured_image ?? '');
  $featuredIsVideo = ($featuredMedia !== '')
      && function_exists('is_video_media_path')
      && is_video_media_path($featuredMedia);

  $hasVideo = (bool) $firstVideo || $featuredIsVideo;

  $videoSrc = null;
  if ($firstVideo && !empty($firstVideo->url)) {
      $videoSrc = media_url($firstVideo->url);
  } elseif ($featuredIsVideo) {
      $videoSrc = media_url($featuredMedia);
  }

  $dataVideoSrc = (empty($firstImage) && !empty($videoSrc))
      ? $videoSrc
      : null;

  $shop = $item->shop;
  $shopAvg = $shop ? ($shop->reviews_avg_rating ?? $shop->reviews()->avg('rating')) : null;
  $shopCount = $shop ? ($shop->reviews_count ?? $shop->reviews()->count()) : null;
  $avg = max(0, min(5, (int) round((float) ($shopAvg ?? ($item->reviews_avg_rating ?? 0)))));
  $reviewsCnt = (int) ($shopCount ?? ($item->reviews_count ?? 0));
@endphp

<a href="{{ route('listing.show', $item->slug) }}"
   class="group flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg sm:rounded-2xl">

  <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 sm:aspect-square {{ $isDigitalPreview ? 'cetsy-preview-watermark' : '' }}"
       @if($isDigitalPreview) data-watermark-label="Cetsy Preview" @endif>
    @if($isReserved)
      <span class="absolute right-1.5 top-1.5 z-10 rounded-full bg-red-600 px-1.5 py-0.5 text-[9px] font-semibold text-white sm:right-2 sm:top-2 sm:px-2 sm:text-[10px]">Reserved</span>
    @endif

    @if($hasVideo)
      <span class="absolute left-1.5 top-1.5 z-10 inline-flex items-center gap-1 rounded-md bg-slate-900/80 px-1.5 py-0.5 text-[9px] font-semibold text-white sm:left-2 sm:top-2 sm:px-2 sm:text-[10px]">
        <i class="fas fa-play text-[8px] sm:text-[9px]"></i> Video
      </span>
    @endif

    <img src="{{ $thumb }}"
         alt="{{ $item->localized_name ?? $item->name }}"
         class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.03]"
         onerror='this.onerror=null;this.src=@json(asset("assets/images/cetsylogmain.png"));'
         @if($dataVideoSrc) data-video-src="{{ $dataVideoSrc }}" style="opacity:.01;filter:blur(8px);transition:opacity .35s ease,filter .35s ease;" @endif
         loading="lazy" decoding="async">
  </div>

  <div class="flex flex-1 flex-col p-2 sm:p-3">
    <h3 class="line-clamp-1 text-[12px] font-semibold text-slate-900 sm:line-clamp-2 sm:text-sm">
      {{ $item->localized_name ?? $item->name }}
    </h3>

    <div class="mt-1.5 hidden items-center gap-1 text-[11px] text-amber-500 sm:mt-2 sm:flex">
      @for($i = 1; $i <= 5; $i++)
        <i class="fa-star {{ $i <= $avg ? 'fa-solid' : 'fa-regular text-slate-300' }}"></i>
      @endfor
      @if($reviewsCnt)
        <span class="ml-1 text-slate-400">({{ $reviewsCnt }})</span>
      @endif
    </div>

    <div class="mt-1.5 sm:mt-3">
      @if ($isService)
        <p class="hidden text-[11px] uppercase tracking-[0.12em] text-slate-400 sm:block">Priced From</p>
        <p class="text-[13px] font-bold text-emerald-700 sm:text-sm">
          {{ $formatMoney($hasVariantPricing ? $lowestVariantPrice : ($salePrice < $basePrice ? $salePrice : $basePrice)) }}
        </p>
      @else
        @if ($hasVariantPricing)
          <p class="hidden text-[11px] uppercase tracking-[0.12em] text-slate-400 sm:block">From</p>
          <p class="text-[13px] font-bold text-emerald-700 sm:text-sm">{{ $formatMoney($lowestVariantPrice) }}</p>
        @else
          @if ($salePrice < $basePrice)
            <div class="flex items-center gap-2">
              <span class="text-[13px] font-bold text-emerald-700 sm:text-sm">{{ $formatMoney($salePrice) }}</span>
              <span class="hidden text-xs text-slate-400 line-through sm:inline">{{ $formatMoney($basePrice) }}</span>
            </div>
          @else
            <p class="text-[13px] font-bold text-emerald-700 sm:text-sm">{{ $formatMoney($basePrice) }}</p>
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
