{{-- resources/views/theme/cetsy/partials/product-carousel.blade.php --}}
@props([
    'items' => collect(),
    'title' => 'Recommended for You',
    'subtitle' => null,
    'eyebrow' => null,
    'eyebrowIcon' => 'fa-wand-magic-sparkles',
    'seeMoreUrl' => null,
    'seeMoreLabel' => 'See all',
    'wrapperTag' => 'section',
    'wrapperClass' => 'py-10 bg-white',
    'containerClass' => 'mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8',
    'showHeader' => true,
])

@php
    $collection = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
    $wrapperTag = $wrapperTag ?? 'section';
    $wrapperClass = trim($wrapperClass ?? '');
    $containerClass = trim($containerClass ?? 'mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8');
    $seeMoreLabel = $seeMoreLabel ?? 'See all';
@endphp

@if($collection->count())
    @once
        @push('styles')
            <style>
                .pc-track { scrollbar-width: none; }
                .pc-track::-webkit-scrollbar { display: none; }
                .pc-item { flex: 0 0 auto; width: 260px; scroll-snap-align: start; }
                .pc-btn.is-disabled { opacity: .35; pointer-events: none; }
                @media (max-width: 640px) {
                    .pc-item { width: 176px; }
                    .pc-track { gap: .5rem; }
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
                        prev.classList.toggle('is-disabled', track.scrollLeft <= tolerance || !isOverflowing);
                        next.classList.toggle('is-disabled', track.scrollLeft >= maxScroll - tolerance || !isOverflowing);
                    };

                    containers.forEach((container) => {
                        const track = container.querySelector('[data-carousel-track]');
                        if (!track) return;

                        const prev = container.querySelector('[data-carousel-prev]');
                        const next = container.querySelector('[data-carousel-next]');

                        const scrollAmount = () => Math.max(track.clientWidth * 0.8, 220);
                        const tolerance = 6;

                        const scrollPrev = () => {
                            const maxScroll = track.scrollWidth - track.clientWidth;
                            if (track.scrollLeft <= tolerance) {
                                track.scrollTo({ left: maxScroll, behavior: 'smooth' });
                            } else {
                                track.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
                            }
                        };

                        const scrollNext = () => {
                            const maxScroll = track.scrollWidth - track.clientWidth;
                            if (track.scrollLeft >= maxScroll - tolerance) {
                                track.scrollTo({ left: 0, behavior: 'smooth' });
                            } else {
                                track.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
                            }
                        };

                        prev?.addEventListener('click', scrollPrev);
                        next?.addEventListener('click', scrollNext);

                        const observer = () => update(track, prev, next);
                        track.addEventListener('scroll', observer, { passive: true });
                        window.addEventListener('resize', observer);
                        observer();

                        let autoTimer = null;
                        const startAuto = () => {
                            if (autoTimer) return;
                            autoTimer = setInterval(scrollNext, 5000);
                        };
                        const stopAuto = () => {
                            if (!autoTimer) return;
                            clearInterval(autoTimer);
                            autoTimer = null;
                        };

                        container.addEventListener('mouseenter', stopAuto);
                        container.addEventListener('mouseleave', startAuto);
                        startAuto();
                    });
                });
            </script>
        @endpush
    @endonce

    @if($wrapperTag === 'div')
        <div class="{{ $wrapperClass }}" data-product-carousel>
    @else
        <section class="{{ $wrapperClass }}" data-product-carousel>
    @endif
        <div class="{{ $containerClass }}">
            @if($showHeader)
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        @if($eyebrow)
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                <i class="fas {{ $eyebrowIcon }}"></i> {{ $eyebrow }}
                            </span>
                        @endif
                        <h2 class="mt-2 text-2xl font-extrabold text-slate-900">{{ $title }}</h2>
                        @if($subtitle)
                            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <div class="flex items-center gap-2">
                            <button type="button" class="pc-btn inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 text-slate-700 hover:border-emerald-300 hover:text-emerald-700" data-carousel-prev aria-label="Previous products">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="pc-btn inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 text-slate-700 hover:border-emerald-300 hover:text-emerald-700" data-carousel-next aria-label="Next products">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        @if($seeMoreUrl)
                            <a href="{{ $seeMoreUrl }}" class="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">{{ $seeMoreLabel }}</a>
                        @endif
                    </div>
                </div>
            @endif

            <div class="relative overflow-hidden">
                <div class="pc-track flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2" data-carousel-track>
                    @foreach($collection as $item)
                        <div class="pc-item">
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
