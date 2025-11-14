{{-- resources/views/theme/{{ theme() }}/partials/_media.blade.php --}}
@php
  /**
   * $product->media: collection with:
   * - type: 'image' | 'video'
   * - url: storage path (original/high-res)
   * - alt: optional alt text
   */

  // Helper: Build srcset for responsive sharpness.
  // Swap these with real resized variants if you generate them.
  $srcsetFor = function (string $path) {
      $url = media_url($path);
      return implode(', ', [
          "{$url} 800w",
          "{$url} 1200w",
          "{$url} 1600w",
          "{$url} 2048w",
      ]);
  };

  // Size hints: tune to your layout/container width
  $sizesAttr = '(max-width: 576px) 100vw, (max-width: 992px) 70vw, 600px';
@endphp

<div data-aos="fade-right">
  <div class="d-flex gap-3 align-items-start">
    {{-- Vertical Thumbnail Navigator --}}
    @if($product->media->count() > 1)
      <div class="d-none d-md-flex flex-column gap-2 me-1" style="max-height:420px;overflow-y:auto;">
        @foreach($product->media as $i => $media)
          @if($media->type === 'video')
            <div class="thumb video-thumb @if($i===0) border-success @endif"
                 data-bs-target="#productCarousel"
                 data-bs-slide-to="{{ $i }}"
                 style="width:64px;height:64px;cursor:pointer;overflow:hidden;border-radius:.5rem;border:1px solid rgba(0,0,0,.08)">
              <video
                src="{{ media_url($media->url) }}"
                class="w-100 h-100"
                style="object-fit: cover"
                muted
              ></video>
            </div>
          @else
            <img
              src="{{ media_url($media->url) }}"
              srcset="{{ $srcsetFor($media->url) }}"
              sizes="64px"
              class="img-thumbnail thumb @if($i === 0) border-success @endif"
              style="width:64px;height:64px;object-fit:cover;cursor:pointer;border-radius:.5rem"
              data-bs-target="#productCarousel"
              data-bs-slide-to="{{ $i }}"
              alt="Thumbnail {{ $i + 1 }}"
              loading="lazy"
              decoding="async"
            >
          @endif
        @endforeach
      </div>
    @endif

    {{-- Main Carousel (NO AUTO-SLIDE) --}}
    <div class="flex-grow-1">
      <div id="productCarousel"
           class="carousel slide shadow-sm rounded-4 overflow-hidden mb-3"
           data-bs-interval="false"
           data-bs-touch="true"
           data-bs-keyboard="true">

        <div class="carousel-inner">
          @foreach($product->media as $i => $media)
            <div class="carousel-item @if($i === 0) active @endif">
              @if($media->type === 'video')
                <div class="position-relative">
                  <span class="badge bg-dark bg-opacity-75 position-absolute top-0 start-0 m-2" style="font-size:.75rem;">
                    <i class="fas fa-play me-1"></i>Video
                  </span>
                  <video controls class="d-block w-100 ratio-box"
                         style="aspect-ratio: 4/3; object-fit: contain; background:#f7f7f7">
                    <source src="{{ media_url($media->url) }}" />
                  </video>
                </div>
              @else
                <div class="zoom-wrap"
                     data-full="{{ media_url($media->url) }}"
                     data-index="{{ $i }}"
                     role="button"
                     title="Click to open full-screen">
                  <img
                    src="{{ media_url($media->url) }}"
                    srcset="{{ $srcsetFor($media->url) }}"
                    sizes="{{ $sizesAttr }}"
                    class="d-block w-100 ratio-box"
                    style="aspect-ratio: 4/3; object-fit: contain; background:#f7f7f7"
                    alt="{{ $media->alt ?? ($product->name . ' image ' . ($i+1)) }}"
                    @if($i===0) fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif
                  >
                </div>
              @endif
            </div>
          @endforeach
        </div>

        @if($product->media->count() > 1)
          <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev" aria-label="Previous">
            <span class="carousel-control-prev-icon"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next" aria-label="Next">
            <span class="carousel-control-next-icon"></span>
          </button>
        @endif
      </div>

      {{-- Thumbnails for mobile (horizontal) --}}
      @if($product->media->count() > 1)
        <div class="d-flex d-md-none gap-2 flex-wrap justify-content-center">
          @foreach($product->media as $i => $media)
            @if($media->type === 'video')
              <div class="thumb video-thumb @if($i===0) border-success @endif"
                   data-bs-target="#productCarousel"
                   data-bs-slide-to="{{ $i }}"
                   style="width:64px;height:64px;cursor:pointer;overflow:hidden;border-radius:.5rem;border:1px solid rgba(0,0,0,.08)">
                <video
                  src="{{ media_url($media->url) }}"
                  class="w-100 h-100"
                  style="object-fit: cover"
                  muted
                ></video>
              </div>
            @else
              <img
                src="{{ media_url($media->url) }}"
                srcset="{{ $srcsetFor($media->url) }}"
                sizes="64px"
                class="img-thumbnail thumb @if($i === 0) border-success @endif"
                style="width:64px;height:64px;object-fit:cover;cursor:pointer;border-radius:.5rem"
                data-bs-target="#productCarousel"
                data-bs-slide-to="{{ $i }}"
                alt="Thumbnail {{ $i + 1 }}"
                loading="lazy"
                decoding="async"
              >
            @endif
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Full-screen Lightbox (manual only) --}}
<div class="modal fade" id="imageLightbox" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <button type="button" class="btn btn-light btn-sm" id="lbZoomIn" title="Zoom in (+)">+</button>
        <button type="button" class="btn btn-light btn-sm ms-2" id="lbZoomOut" title="Zoom out (–)">–</button>
        <button type="button" class="btn btn-light btn-sm ms-2" id="lbZoomReset" title="Reset">Reset</button>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 d-flex align-items-center justify-content-center">
        <div id="lbStage" class="lb-stage">
          {{-- Injected <img> goes here --}}
        </div>
      </div>
      @if($product->media->where('type','image')->count() > 1)
        <div class="modal-footer bg-dark border-0 justify-content-between">
          <button class="btn btn-outline-light" id="lbPrev"><i class="fa-solid fa-chevron-left me-1"></i>Prev</button>
          <button class="btn btn-outline-light" id="lbNext">Next<i class="fa-solid fa-chevron-right ms-1"></i></button>
        </div>
      @endif
    </div>
  </div>
</div>

@push('styles')
<style>
  /* Preserve crispness & avoid blur */
  .ratio-box {
    image-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    backface-visibility: hidden;
    transform: translateZ(0);
  }

  /* Hover lens zoom (desktop) */
  .zoom-wrap {
    position: relative;
    overflow: hidden;
    cursor: zoom-in;
  }
  .zoom-wrap::after {
    content: "Click to view full";
    position: absolute;
    right: .5rem;
    bottom: .5rem;
    background: rgba(0,0,0,.5);
    color: #fff;
    font-size: .75rem;
    padding: .25rem .5rem;
    border-radius: .375rem;
    opacity: .0;
    transition: opacity .2s ease;
    pointer-events: none;
  }
  .zoom-wrap:hover::after { opacity: 1; }

  .zoom-lens {
    position: absolute;
    display: none;
    pointer-events: none;
    border: 1px solid rgba(0,0,0,.12);
    border-radius: .375rem;
    width: 180px;
    height: 180px;
    background-repeat: no-repeat;
    /* background-size and background-position are set dynamically for accuracy */
    box-shadow: 0 6px 24px rgba(0,0,0,.25);
    z-index: 2;
  }

  /* Lightbox stage (drag/pinch zoom) */
  .lb-stage {
    position: relative;
    width: 100%;
    height: 100%;
    touch-action: none;
    background: #111;
    display: grid;
    place-items: center;
    overflow: hidden;
  }
  .lb-stage img {
    max-width: 100%;
    max-height: 100%;
    will-change: transform;
    user-select: none;
    -webkit-user-drag: none;
    pointer-events: none; /* pan on stage */
  }

  .thumb:focus { outline: 2px solid #198754; outline-offset: 2px; }
</style>
@endpush

@push('scripts')
<script>
(function(){
  // =========================
  // CONFIG
  // =========================
  const LENS_ZOOM = 2.0;      // Hover lens zoom factor
  const LIGHTBOX_STEP = 0.25; // Button/wheel zoom step
  const LIGHTBOX_MIN = 1.0;
  const LIGHTBOX_MAX = 6.0;

  // =========================
  // HOVER LENS ZOOM (desktop)
  // Accurate cursor-centered zoom with object-fit: contain
  // =========================
  const wraps = document.querySelectorAll('.zoom-wrap');
  wraps.forEach(wrap => {
    const img = wrap.querySelector('img');
    if (!img) return;

    // Create lens
    const lens = document.createElement('div');
    lens.className = 'zoom-lens';
    wrap.appendChild(lens);

    // Use full-res source for lens
    const fullSrc = wrap.getAttribute('data-full') || img.currentSrc || img.src;
    lens.style.backgroundImage = `url("${fullSrc}")`;

    // State for displayed image box inside wrapper (due to object-fit: contain)
    const state = {
      dispW: 0, dispH: 0, offX: 0, offY: 0 // displayed width/height and left/top offsets within wrap
    };

    function computeImageBox(){
      const wrapRect = wrap.getBoundingClientRect();
      const containerW = wrapRect.width;
      const containerH = wrapRect.height;

      const natW = img.naturalWidth || containerW;
      const natH = img.naturalHeight || containerH;

      const imgAR = natW / natH;
      const containerAR = containerW / containerH;

      let dispW, dispH, offX, offY;
      if (imgAR > containerAR) {
        // Image is wider -> full width, height letterboxed
        dispW = containerW;
        dispH = containerW / imgAR;
        offX = 0;
        offY = (containerH - dispH) / 2;
      } else {
        // Image is taller -> full height, width letterboxed
        dispH = containerH;
        dispW = containerH * imgAR;
        offX = (containerW - dispW) / 2;
        offY = 0;
      }
      state.dispW = dispW;
      state.dispH = dispH;
      state.offX = offX;
      state.offY = offY;
    }

    // Recompute when needed
    const computeWhenReady = () => {
      if (img.complete && img.naturalWidth) {
        computeImageBox();
      } else {
        img.addEventListener('load', computeImageBox, { once: true });
      }
    };
    computeWhenReady();
    window.addEventListener('resize', computeImageBox);

    const isFine = matchMedia('(pointer:fine)').matches;

    function moveLens(e){
      const wrapRect = wrap.getBoundingClientRect();
      const mouseX = e.clientX - wrapRect.left;
      const mouseY = e.clientY - wrapRect.top;

      const lensW = lens.offsetWidth;
      const lensH = lens.offsetHeight;

      // Clamp lens center within the displayed image box
      const minX = state.offX + lensW / 2;
      const maxX = state.offX + state.dispW - lensW / 2;
      const minY = state.offY + lensH / 2;
      const maxY = state.offY + state.dispH - lensH / 2;

      // If image box not computed yet, skip
      if (state.dispW <= 0 || state.dispH <= 0) return;

      let centerX = Math.min(Math.max(mouseX, minX), maxX);
      let centerY = Math.min(Math.max(mouseY, minY), maxY);

      // Position lens (top-left)
      lens.style.left = (centerX - lensW / 2) + 'px';
      lens.style.top  = (centerY - lensH / 2) + 'px';

      // Coordinates within the displayed image (relative to its top-left)
      const xInImage = centerX - state.offX;
      const yInImage = centerY - state.offY;

      // Set background size based on displayed image size and zoom factor
      const bgW = state.dispW * LENS_ZOOM;
      const bgH = state.dispH * LENS_ZOOM;
      lens.style.backgroundSize = `${bgW}px ${bgH}px`;

      // Center the cursor point in the lens:
      // background-position = -(x*Z - lensW/2), -(y*Z - lensH/2)
      const posX = -(xInImage * LENS_ZOOM - lensW / 2);
      const posY = -(yInImage * LENS_ZOOM - lensH / 2);
      lens.style.backgroundPosition = `${posX}px ${posY}px`;
    }

    function enter(){
      if (!isFine) return; // skip on touch devices
      if (state.dispW === 0 || state.dispH === 0) computeImageBox();
      lens.style.display = 'block';
      wrap.style.cursor = 'zoom-in';
    }
    function leave(){
      lens.style.display = 'none';
    }

    wrap.addEventListener('mousemove', moveLens);
    wrap.addEventListener('mouseenter', enter);
    wrap.addEventListener('mouseleave', leave);

    // Open lightbox at this index
    const openIndex = () => openLightbox(parseInt(wrap.getAttribute('data-index'),10) || 0);
    wrap.addEventListener('click', openIndex);
    wrap.addEventListener('dblclick', openIndex);
  });

  // =========================
  // LIGHTBOX (manual only)
  // =========================
  const lightbox = document.getElementById('imageLightbox');
  const lbStage = document.getElementById('lbStage');
  const lbZoomIn = document.getElementById('lbZoomIn');
  const lbZoomOut = document.getElementById('lbZoomOut');
  const lbZoomReset = document.getElementById('lbZoomReset');
  const lbPrev = document.getElementById('lbPrev');
  const lbNext = document.getElementById('lbNext');

  // Gather image sources from main carousel
  const gallery = Array.from(document.querySelectorAll('#productCarousel .carousel-item .zoom-wrap'))
    .map(w => w.getAttribute('data-full'))
    .filter(Boolean);

  let currentIndex = 0;
  let scale = 1, lastX = 0, lastY = 0, isPanning = false, startX = 0, startY = 0, pinchActive = false, initialDist = 0;

  function renderImage(idx) {
    if (!gallery.length) return;
    currentIndex = (idx + gallery.length) % gallery.length;

    lbStage.innerHTML = '';
    const img = document.createElement('img');
    img.src = gallery[currentIndex];
    img.alt = 'Product image ' + (currentIndex+1);
    img.draggable = false;
    lbStage.appendChild(img);

    // Reset view
    scale = 1; lastX = 0; lastY = 0; applyTransform();
  }

  function applyTransform(){
    const img = lbStage.querySelector('img');
    if (!img) return;
    img.style.transform = `translate(${lastX}px, ${lastY}px) scale(${scale})`;
    img.style.transformOrigin = 'center center';
  }

  function zoom(delta){
    scale = Math.min(LIGHTBOX_MAX, Math.max(LIGHTBOX_MIN, scale + delta));
    applyTransform();
  }

  function openLightbox(idx=0){
    if (!gallery.length) return;
    renderImage(idx);
    bootstrap.Modal.getOrCreateInstance(lightbox).show();
  }

  // Buttons
  lbZoomIn?.addEventListener('click', () => zoom(LIGHTBOX_STEP));
  lbZoomOut?.addEventListener('click', () => zoom(-LIGHTBOX_STEP));
  lbZoomReset?.addEventListener('click', () => { scale = 1; lastX = 0; lastY = 0; applyTransform(); });

  lbPrev?.addEventListener('click', () => renderImage(currentIndex - 1));
  lbNext?.addEventListener('click', () => renderImage(currentIndex + 1));

  // Drag / Pan
  lbStage.addEventListener('pointerdown', (e) => {
    lbStage.setPointerCapture(e.pointerId);
    isPanning = true;
    pinchActive = false;
    startX = e.clientX - lastX;
    startY = e.clientY - lastY;
  });

  lbStage.addEventListener('pointermove', (e) => {
    if (!isPanning || pinchActive) return;
    lastX = e.clientX - startX;
    lastY = e.clientY - startY;
    applyTransform();
  });

  const stopPan = () => { isPanning = false; pinchActive = false; };
  lbStage.addEventListener('pointerup', stopPan);
  lbStage.addEventListener('pointercancel', stopPan);
  lbStage.addEventListener('pointerleave', stopPan);

  // Wheel zoom
  lbStage.addEventListener('wheel', (e) => {
    e.preventDefault();
    const delta = -Math.sign(e.deltaY) * LIGHTBOX_STEP;
    zoom(delta);
  }, { passive: false });

  // Pinch zoom
  lbStage.addEventListener('touchstart', (e) => {
    if (e.touches.length === 2) {
      pinchActive = true;
      initialDist = getTouchDistance(e);
    }
  }, {passive:true});

  lbStage.addEventListener('touchmove', (e) => {
    if (e.touches.length === 2 && pinchActive) {
      e.preventDefault();
      const dist = getTouchDistance(e);
      const step = (dist - initialDist) / 200;
      initialDist = dist;
      scale = Math.min(LIGHTBOX_MAX, Math.max(LIGHTBOX_MIN, scale + step));
      applyTransform();
    }
  }, {passive:false});

  function getTouchDistance(e){
    const [t1, t2] = [e.touches[0], e.touches[1]];
    return Math.hypot(t1.clientX - t2.clientX, t1.clientY - t2.clientY);
  }

  // Keyboard: open lightbox from focused image container
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && document.activeElement?.classList.contains('zoom-wrap')) {
      openLightbox(parseInt(document.activeElement.getAttribute('data-index'),10) || 0);
    }
  });

  // Keep current index in sync with manual slides
  const carouselEl = document.getElementById('productCarousel');
  if (carouselEl) {
    carouselEl.addEventListener('slid.bs.carousel', (evt) => {
      currentIndex = evt.to;
    });
  }
})();
</script>
@endpush
