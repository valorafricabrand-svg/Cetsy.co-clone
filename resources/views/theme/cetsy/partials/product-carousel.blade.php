{{-- resources/views/theme/'.theme().'/partials/product-carousel.blade.php --}}
@props([
    'items' => collect(),
    'title' => 'Recommended for You',
    'subtitle' => null,
    'eyebrow' => null,
    'eyebrowIcon' => 'fa-wand-magic-sparkles',
    'seeMoreUrl' => null,
    'seeMoreLabel' => 'See all',
    'wrapperTag' => 'section',
    'wrapperClass' => 'py-5 bg-white product-carousel-section',
    'containerClass' => 'container',
    'showHeader' => true,
])

@php
    $collection = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
@endphp

@if($collection->count())
    @once
        @push('styles')
            <style>
                .product-carousel { position: relative; }
                .product-carousel-track {
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    -webkit-overflow-scrolling: touch;
                    padding: 0.75rem 1rem;
                }
                .product-carousel-track::-webkit-scrollbar { height: 8px; }
                .product-carousel-track::-webkit-scrollbar-thumb {
                    background: rgba(25,135,84,.35);
                    border-radius: 12px;
                }
                .product-carousel-item {
                    flex: 0 0 auto;
                    min-width: 240px;
                    scroll-snap-align: start;
                }
                .carousel-control {
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    z-index: 5;
                    border-radius: 999px;
                    width: 36px;
                    height: 36px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: rgba(255,255,255,.95);
                    border: 1px solid rgba(25,135,84,.25);
                    color: #198754;
                    box-shadow: 0 8px 16px rgba(25,135,84,.15);
                }
                .carousel-control:hover {
                    background: #198754;
                    color: #fff;
                }
                .carousel-control.is-disabled {
                    opacity: .35;
                    pointer-events: none;
                }
                [data-carousel-prev] { left: -18px; }
                [data-carousel-next] { right: -18px; }
                @media (max-width: 992px) {
                    .carousel-control { display: none; }
                    .product-carousel-track { padding: 0.75rem 0; }
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const containers = document.querySelectorAll('[data-product-carousel]');
                    const update = (track, prev, next) => {
                        if (!prev || !next) return;
                        const maxScroll = track.scrollWidth - track.clientWidth;
                        const tolerance = 6;
                        const isOverflowing = maxScroll > tolerance;
                        prev.style.display = next.style.display = isOverflowing ? '' : 'none';
                        prev.classList.toggle('is-disabled', track.scrollLeft <= tolerance);
                        next.classList.toggle('is-disabled', track.scrollLeft >= maxScroll - tolerance);
                    };

                    containers.forEach(container => {
                        const track = container.querySelector('.product-carousel-track');
                        if (!track) return;
                        const prev = container.querySelector('[data-carousel-prev]');
                        const next = container.querySelector('[data-carousel-next]');
                        const scrollAmount = () => Math.max(track.clientWidth * 0.8, 220);

                        prev?.addEventListener('click', () => track.scrollBy({ left: -scrollAmount(), behavior: 'smooth' }));
                        next?.addEventListener('click', () => track.scrollBy({ left:  scrollAmount(), behavior: 'smooth' }));

                        const observer = () => update(track, prev, next);
                        track.addEventListener('scroll', observer, { passive: true });
                        window.addEventListener('resize', observer);
                        observer();
                    });
                });
            </script>
        @endpush
    @endonce

    @php
        $wrapperTag = $wrapperTag ?? 'section';
        $wrapperClass = trim($wrapperClass ?? '');
        $containerClass = trim($containerClass ?? 'container');
        $seeMoreLabel = $seeMoreLabel ?? 'See all';
    @endphp

    @if($wrapperTag === 'div')
        <div class="{{ $wrapperClass }}" data-product-carousel>
    @else
        <section class="{{ $wrapperClass }}" data-product-carousel>
    @endif
        <div class="{{ $containerClass }}">
            @if($showHeader)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        @if($eyebrow)
                            <span class="eyebrow text-success"><i class="fas {{ $eyebrowIcon }}"></i> {{ $eyebrow }}</span>
                        @endif
                        <h2 class="h4 fw-bold mb-0">{{ $title }}</h2>
                        @if($subtitle)
                            <p class="text-muted mb-0">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if($seeMoreUrl)
                        <a href="{{ $seeMoreUrl }}" class="btn btn-outline-success btn-sm">{{ $seeMoreLabel }}</a>
                    @endif
                </div>
            @endif

            <div class="product-carousel">
                <button type="button" class="carousel-control" data-carousel-prev>
                    <i class="fas fa-chevron-left"></i>
                    <span class="visually-hidden">Previous</span>
                </button>

                <div class="product-carousel-track d-flex flex-nowrap gap-3">
                    @foreach($collection as $item)
                        <div class="product-carousel-item">
                            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
                        </div>
                    @endforeach
                </div>

                <button type="button" class="carousel-control" data-carousel-next>
                    <i class="fas fa-chevron-right"></i>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    @if($wrapperTag === 'div')
        </div>
    @else
        </section>
    @endif
@endif
