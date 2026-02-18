{{-- Tailwind media gallery for listing_show --}}
@php
  $mediaItems = $product->media ?? collect();
@endphp

<div data-media-gallery class="space-y-3">
  <div class="grid items-start gap-3 md:grid-cols-[76px_1fr]">
    @if($mediaItems->count() > 1)
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

    <div>
      <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @foreach($mediaItems as $i => $media)
          <div class="media-panel {{ $i === 0 ? '' : 'hidden' }}" data-media-panel="{{ $i }}">
            @if($media->type === 'video')
              <div class="relative">
                <span class="absolute left-3 top-3 z-10 rounded-md bg-slate-900/80 px-2 py-1 text-[11px] font-semibold text-white">
                  <i class="fas fa-play mr-1"></i>Video
                </span>
                <video controls class="h-[420px] w-full bg-slate-100 object-contain md:h-[460px]">
                  <source src="{{ media_url($media->url) }}">
                </video>
              </div>
            @else
              <button
                type="button"
                class="group relative block h-[420px] w-full cursor-zoom-in bg-slate-100 md:h-[460px]"
                data-open-image-lightbox
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
      </div>

      @if($mediaItems->count() > 1)
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

  <div id="imageLightboxTw" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/90 p-4">
    <button type="button" class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/30 text-white hover:bg-white/10" data-close-image-lightbox aria-label="Close image preview">
      <i class="fas fa-times"></i>
    </button>
    <img id="imageLightboxTwImg" src="" alt="Full size image preview" class="max-h-[92vh] max-w-[92vw] rounded-xl border border-white/20 bg-slate-900 object-contain shadow-2xl">
  </div>
</div>

@push('scripts')
<script>
(function () {
  function initGallery(root) {
    const panels = Array.from(root.querySelectorAll('[data-media-panel]'));
    const thumbs = Array.from(root.querySelectorAll('[data-media-thumb]'));
    if (!panels.length || !thumbs.length) return;

    function setActive(index) {
      panels.forEach((panel, i) => {
        panel.classList.toggle('hidden', i !== index);
      });
      thumbs.forEach((thumb, i) => {
        const active = i === index;
        thumb.classList.toggle('border-emerald-500', active);
        thumb.classList.toggle('ring-2', active);
        thumb.classList.toggle('ring-emerald-100', active);
        thumb.classList.toggle('border-slate-200', !active);
      });
    }

    thumbs.forEach(thumb => {
      thumb.addEventListener('click', () => {
        const idx = parseInt(thumb.getAttribute('data-media-thumb') || '0', 10);
        setActive(Number.isNaN(idx) ? 0 : idx);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-media-gallery]').forEach(initGallery);

    const lightbox = document.getElementById('imageLightboxTw');
    const lightboxImg = document.getElementById('imageLightboxTwImg');
    const closeBtn = lightbox?.querySelector('[data-close-image-lightbox]');

    function closeLightbox() {
      if (!lightbox || !lightboxImg) return;
      lightbox.classList.add('hidden');
      lightbox.classList.remove('flex');
      lightboxImg.src = '';
    }

    document.querySelectorAll('[data-open-image-lightbox]').forEach(trigger => {
      trigger.addEventListener('click', () => {
        if (!lightbox || !lightboxImg) return;
        const src = trigger.getAttribute('data-lightbox-src');
        if (!src) return;
        lightboxImg.src = src;
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
      });
    });

    closeBtn?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', (event) => {
      if (event.target === lightbox) {
        closeLightbox();
      }
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeLightbox();
    });
  });
})();
</script>
@endpush
