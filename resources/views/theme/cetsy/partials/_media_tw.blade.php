{{-- Tailwind media gallery for listing_show --}}
@php
  $mediaItems = $product->media ?? collect();
  $mediaTotal = $mediaItems->count();
@endphp

<div data-media-gallery class="space-y-3">
  <div class="grid items-start gap-3 md:grid-cols-[78px_1fr]">
    @if($mediaTotal > 1)
      <div class="hidden max-h-[460px] space-y-2 overflow-y-auto pr-1 md:block">
        @foreach($mediaItems as $i => $media)
          <button
            type="button"
            class="media-thumb inline-flex h-[68px] w-[68px] items-center justify-center overflow-hidden rounded-xl border bg-white {{ $i === 0 ? 'border-emerald-500 ring-2 ring-emerald-100' : 'border-slate-200' }}"
            data-media-thumb="{{ $i }}"
            aria-label="Select media {{ $i + 1 }}"
          >
            @if($media->type === 'video')
              <video src="{{ media_url($media->url) }}" class="h-full w-full object-cover" muted preload="metadata"></video>
            @else
              <img src="{{ media_url($media->url) }}" alt="Thumbnail {{ $i + 1 }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
            @endif
          </button>
        @endforeach
      </div>
    @endif

    <div class="min-w-0">
      <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @foreach($mediaItems as $i => $media)
          <div
            class="media-panel {{ $i === 0 ? '' : 'hidden' }}"
            data-media-panel="{{ $i }}"
            aria-hidden="{{ $i === 0 ? 'false' : 'true' }}"
            @if($i !== 0) style="display:none" @endif
          >
            @if($media->type === 'video')
              <div class="relative">
                <span class="absolute left-3 top-3 z-10 rounded-md bg-slate-900/80 px-2 py-1 text-[11px] font-semibold text-white">
                  <i class="fas fa-play mr-1"></i>Video
                </span>
                <video controls class="block h-[420px] w-full bg-slate-100 object-contain md:h-[460px]" preload="metadata">
                  <source src="{{ media_url($media->url) }}">
                </video>
              </div>
            @else
              <button
                type="button"
                class="group relative block h-[420px] w-full cursor-zoom-in bg-slate-100 md:h-[460px]"
                data-open-image-lightbox
                data-media-index="{{ $i }}"
                data-lightbox-src="{{ media_url($media->url) }}"
              >
                <img
                  src="{{ media_url($media->url) }}"
                  alt="{{ $media->alt ?? ($product->name . ' image ' . ($i + 1)) }}"
                  class="h-full w-full object-contain"
                  @if($i===0) fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif
                >
                <span class="pointer-events-none absolute bottom-3 right-3 rounded-md bg-slate-900/70 px-2 py-1 text-[11px] font-semibold text-white opacity-0 transition group-hover:opacity-100">
                  Click to zoom
                </span>
              </button>
            @endif
          </div>
        @endforeach

        @if($mediaTotal > 1)
          <button type="button" class="absolute left-3 top-1/2 z-20 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-slate-900/45 text-white backdrop-blur hover:bg-slate-900/65" data-media-prev aria-label="Previous media">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button type="button" class="absolute right-3 top-1/2 z-20 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-slate-900/45 text-white backdrop-blur hover:bg-slate-900/65" data-media-next aria-label="Next media">
            <i class="fas fa-chevron-right"></i>
          </button>
          <div class="absolute bottom-3 left-1/2 z-20 -translate-x-1/2 rounded-full bg-slate-900/65 px-2.5 py-1 text-[11px] font-semibold text-white" data-media-counter>
            1 / {{ $mediaTotal }}
          </div>
        @endif
      </div>

      @if($mediaTotal > 1)
        <div class="mt-3 flex flex-wrap gap-2 md:hidden">
          @foreach($mediaItems as $i => $media)
            <button
              type="button"
              class="media-thumb inline-flex h-14 w-14 items-center justify-center overflow-hidden rounded-lg border bg-white {{ $i === 0 ? 'border-emerald-500 ring-2 ring-emerald-100' : 'border-slate-200' }}"
              data-media-thumb="{{ $i }}"
              aria-label="Select media {{ $i + 1 }}"
            >
              @if($media->type === 'video')
                <video src="{{ media_url($media->url) }}" class="h-full w-full object-cover" muted preload="metadata"></video>
              @else
                <img src="{{ media_url($media->url) }}" alt="Thumbnail {{ $i + 1 }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
              @endif
            </button>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  <div id="imageLightboxTw" class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/90 p-4" aria-hidden="true">
    <div class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-slate-900/40 px-2.5 py-1 text-xs font-semibold text-white">
      <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-white/30 hover:bg-white/10" data-lb-prev aria-label="Previous image">
        <i class="fas fa-chevron-left"></i>
      </button>
      <span id="imageLightboxTwCount" class="min-w-[52px] text-center">1 / 1</span>
      <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-white/30 hover:bg-white/10" data-lb-next aria-label="Next image">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <div class="absolute right-4 top-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-slate-900/40 px-2 py-1">
      <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-white/30 text-white hover:bg-white/10" data-lb-zoom-out aria-label="Zoom out">
        <i class="fas fa-minus"></i>
      </button>
      <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-white/30 text-white hover:bg-white/10" data-lb-reset aria-label="Reset zoom">
        <i class="fas fa-arrows-rotate"></i>
      </button>
      <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-white/30 text-white hover:bg-white/10" data-lb-zoom-in aria-label="Zoom in">
        <i class="fas fa-plus"></i>
      </button>
      <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-white/30 text-white hover:bg-white/10" data-close-image-lightbox aria-label="Close image preview">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div id="imageLightboxTwStage" class="flex h-full w-full items-center justify-center overflow-hidden">
      <img id="imageLightboxTwImg" src="" alt="Full size image preview" class="max-h-[92vh] max-w-[92vw] rounded-xl border border-white/20 bg-slate-900 object-contain shadow-2xl transition-transform duration-150">
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  function initGallery(root) {
    if (!root || root.dataset.mediaInitialized === '1') return;
    root.dataset.mediaInitialized = '1';

    const panels = Array.from(root.querySelectorAll('[data-media-panel]'));
    const thumbs = Array.from(root.querySelectorAll('[data-media-thumb]'));
    const prevBtn = root.querySelector('[data-media-prev]');
    const nextBtn = root.querySelector('[data-media-next]');
    const counter = root.querySelector('[data-media-counter]');

    if (!panels.length) return;

    let current = 0;
    const swipe = { x: 0, y: 0, active: false };

    function normalize(index) {
      const total = panels.length;
      return ((index % total) + total) % total;
    }

    function updateCounter() {
      if (counter) {
        counter.textContent = `${current + 1} / ${panels.length}`;
      }
    }

    function syncThumbs() {
      thumbs.forEach((thumb) => {
        const idx = parseInt(thumb.getAttribute('data-media-thumb') || '-1', 10);
        const active = idx === current;
        thumb.classList.toggle('border-emerald-500', active);
        thumb.classList.toggle('ring-2', active);
        thumb.classList.toggle('ring-emerald-100', active);
        thumb.classList.toggle('border-slate-200', !active);
        thumb.setAttribute('aria-current', active ? 'true' : 'false');
      });
    }

    function show(index) {
      current = normalize(index);

      panels.forEach((panel, i) => {
        const active = i === current;
        panel.classList.toggle('hidden', !active);
        panel.style.display = active ? '' : 'none';
        panel.setAttribute('aria-hidden', active ? 'false' : 'true');

        if (!active) {
          panel.querySelectorAll('video').forEach((video) => {
            try {
              video.pause();
            } catch (e) {
              // ignore pause errors
            }
          });
        }
      });

      syncThumbs();
      updateCounter();
    }

    thumbs.forEach((thumb) => {
      thumb.addEventListener('click', () => {
        const idx = parseInt(thumb.getAttribute('data-media-thumb') || '0', 10);
        show(Number.isNaN(idx) ? 0 : idx);
      });
    });

    prevBtn?.addEventListener('click', () => show(current - 1));
    nextBtn?.addEventListener('click', () => show(current + 1));

    root.addEventListener('pointerdown', (event) => {
      if (event.pointerType !== 'touch') return;
      swipe.active = true;
      swipe.x = event.clientX;
      swipe.y = event.clientY;
    }, { passive: true });

    root.addEventListener('pointerup', (event) => {
      if (!swipe.active || event.pointerType !== 'touch') return;
      swipe.active = false;
      const dx = event.clientX - swipe.x;
      const dy = event.clientY - swipe.y;
      if (Math.abs(dx) > 48 && Math.abs(dx) > Math.abs(dy)) {
        show(dx < 0 ? current + 1 : current - 1);
      }
    }, { passive: true });

    show(0);
  }

  function initLightbox() {
    const lightbox = document.getElementById('imageLightboxTw');
    const img = document.getElementById('imageLightboxTwImg');
    const stage = document.getElementById('imageLightboxTwStage');
    const count = document.getElementById('imageLightboxTwCount');
    if (!lightbox || !img || !stage || lightbox.dataset.lightboxInitialized === '1') return;
    lightbox.dataset.lightboxInitialized = '1';

    const closeBtn = lightbox.querySelector('[data-close-image-lightbox]');
    const prevBtn = lightbox.querySelector('[data-lb-prev]');
    const nextBtn = lightbox.querySelector('[data-lb-next]');
    const zoomInBtn = lightbox.querySelector('[data-lb-zoom-in]');
    const zoomOutBtn = lightbox.querySelector('[data-lb-zoom-out]');
    const resetBtn = lightbox.querySelector('[data-lb-reset]');

    const body = document.body;
    const zoomStep = 0.25;
    const zoomMin = 1;
    const zoomMax = 4;

    let images = [];
    let current = 0;
    let zoom = 1;

    function collectImages() {
      images = Array.from(document.querySelectorAll('[data-open-image-lightbox]'))
        .map((btn) => ({
          src: btn.getAttribute('data-lightbox-src') || '',
          panelIndex: parseInt(btn.getAttribute('data-media-index') || '-1', 10),
        }))
        .filter((item) => !!item.src);
    }

    function applyZoom() {
      img.style.transform = `scale(${zoom})`;
    }

    function setZoom(value) {
      zoom = Math.min(zoomMax, Math.max(zoomMin, value));
      applyZoom();
    }

    function render() {
      if (!images.length) return;
      const safeIndex = ((current % images.length) + images.length) % images.length;
      current = safeIndex;
      img.src = images[current].src;
      if (count) count.textContent = `${current + 1} / ${images.length}`;
      setZoom(1);
    }

    function open(panelIndex) {
      collectImages();
      if (!images.length) return;

      const match = images.findIndex((item) => item.panelIndex === panelIndex);
      current = match >= 0 ? match : 0;
      render();

      lightbox.classList.remove('hidden');
      lightbox.classList.add('flex');
      lightbox.setAttribute('aria-hidden', 'false');
      body.classList.add('overflow-hidden');
    }

    function close() {
      lightbox.classList.add('hidden');
      lightbox.classList.remove('flex');
      lightbox.setAttribute('aria-hidden', 'true');
      img.src = '';
      body.classList.remove('overflow-hidden');
      setZoom(1);
    }

    function go(delta) {
      if (!images.length) return;
      current += delta;
      render();
    }

    document.addEventListener('click', (event) => {
      const trigger = event.target.closest('[data-open-image-lightbox]');
      if (!trigger) return;
      const panelIndex = parseInt(trigger.getAttribute('data-media-index') || '0', 10);
      open(Number.isNaN(panelIndex) ? 0 : panelIndex);
    });

    closeBtn?.addEventListener('click', close);
    prevBtn?.addEventListener('click', () => go(-1));
    nextBtn?.addEventListener('click', () => go(1));
    zoomInBtn?.addEventListener('click', () => setZoom(zoom + zoomStep));
    zoomOutBtn?.addEventListener('click', () => setZoom(zoom - zoomStep));
    resetBtn?.addEventListener('click', () => setZoom(1));

    lightbox.addEventListener('click', (event) => {
      if (event.target === lightbox) close();
    });

    stage.addEventListener('wheel', (event) => {
      if (lightbox.classList.contains('hidden')) return;
      event.preventDefault();
      setZoom(zoom + (event.deltaY < 0 ? zoomStep : -zoomStep));
    }, { passive: false });

    document.addEventListener('keydown', (event) => {
      if (lightbox.classList.contains('hidden')) return;
      if (event.key === 'Escape') close();
      if (event.key === 'ArrowLeft') go(-1);
      if (event.key === 'ArrowRight') go(1);
      if (event.key === '+' || event.key === '=') setZoom(zoom + zoomStep);
      if (event.key === '-') setZoom(zoom - zoomStep);
      if (event.key === '0') setZoom(1);
    });
  }

  onReady(() => {
    document.querySelectorAll('[data-media-gallery]').forEach(initGallery);
    initLightbox();
  });
})();
</script>
@endpush
