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
                .product-carousel { position: relative; overflow: hidden; }
                .product-carousel-track {
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    -webkit-overflow-scrolling: touch;
                    padding: 0.75rem 0.25rem;
                    display: flex;
                    gap: 1rem;
                    scrollbar-width: none;
                }
                .product-carousel-track::-webkit-scrollbar { display: none; }
                .product-carousel-item { flex: 0 0 auto; width: 260px; scroll-snap-align: start; }
                .product-carousel-nav .btn {
                    width: 36px; height: 36px; border-radius: 999px; padding: 0;
                    display: inline-flex; align-items: center; justify-content: center;
                }
                .product-carousel-nav .btn:focus { box-shadow: 0 0 0 0.2rem rgba(25,135,84,.25); }
                .product-carousel-nav .btn.is-disabled { opacity: .35; pointer-events: none; }
                @media (max-width: 992px) { .product-carousel-track { padding: 0.75rem 0; } }
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
                        prev.classList.toggle('is-disabled', track.scrollLeft <= tolerance || !isOverflowing);
                        next.classList.toggle('is-disabled', track.scrollLeft >= maxScroll - tolerance || !isOverflowing);
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

                        // Auto slide every 5s
                        let autoTimer = null;
                        const autoNext = () => track.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
                        const startAuto = () => { if (!autoTimer) autoTimer = setInterval(autoNext, 5000); };
                        const stopAuto = () => { if (autoTimer) { clearInterval(autoTimer); autoTimer = null; } };
                        container.addEventListener('mouseenter', stopAuto);
                        container.addEventListener('mouseleave', startAuto);
                        startAuto();
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
                <div class="d-flex align-items-center justify-content-between mb-3 gap-3">
                    <div>
                        @if($eyebrow)
                            <span class="eyebrow text-success"><i class="fas {{ $eyebrowIcon }}"></i> {{ $eyebrow }}</span>
                        @endif
                        <h2 class="h4 fw-bold mb-0">{{ $title }}</h2>
                        @if($subtitle)
                            <p class="text-muted mb-0">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <div class="product-carousel-nav d-flex gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm" data-carousel-prev aria-label="Previous products">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" data-carousel-next aria-label="Next products">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        @if($seeMoreUrl)
                            <a href="{{ $seeMoreUrl }}" class="btn btn-outline-success btn-sm">{{ $seeMoreLabel }}</a>
                        @endif
                    </div>
                </div>
            @endif

            <div class="product-carousel">
                <div class="product-carousel-track d-flex flex-nowrap">
                    @foreach($collection as $item)
                        <div class="product-carousel-item">
                            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @if($wrapperTag === 'div')
        </div>
    @else
        </section>
    @endif
@endif
