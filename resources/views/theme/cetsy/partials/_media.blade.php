{{-- resources/views/theme/{{ theme() }}/partials/_media.blade.php --}}
<div data-aos="fade-right">
  {{-- Main Carousel --}}
  <div id="productCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden mb-3" data-bs-ride="carousel">
    <div class="carousel-inner">
      @foreach($product->media as $i => $media)
        <div class="carousel-item @if($i === 0) active @endif">
          @if($media->type === 'video')
            <video controls class="d-block w-100" style="aspect-ratio:4/3; object-fit:cover">
              <source src="{{ asset('storage/'.$media->url) }}" />
            </video>
          @else
            <img
              src="{{ asset('storage/'.$media->url) }}"
              class="d-block w-100"
              style="aspect-ratio:4/3; object-fit:cover"
              alt="{{ $product->name }}"
            >
          @endif
        </div>
      @endforeach
    </div>
    @if($product->media->count() > 1)
      <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    @endif
  </div>

  {{-- Thumbnail Navigator --}}
  @if($product->media->count() > 1)
    <div class="d-flex gap-2 flex-wrap justify-content-center">
      @foreach($product->media as $i => $media)
        @if($media->type === 'video')
          <video
            src="{{ asset('storage/'.$media->url) }}"
            class="img-thumbnail thumb @if($i === 0) border-success @endif"
            style="width:70px; height:70px; object-fit:cover; cursor:pointer"
            data-bs-target="#productCarousel"
            data-bs-slide-to="{{ $i }}"
            muted
          ></video>
        @else
          <img
            src="{{ asset('storage/'.$media->url) }}"
            class="img-thumbnail thumb @if($i === 0) border-success @endif"
            style="width:70px; height:70px; object-fit:cover; cursor:pointer"
            data-bs-target="#productCarousel"
            data-bs-slide-to="{{ $i }}"
            alt="Thumbnail {{ $i + 1 }}"
          >
        @endif
      @endforeach
    </div>
  @endif
</div>
