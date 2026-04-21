{{-- resources/views/products/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'My Listings')

@push('styles')
<style>
    .listing-card-shell {
        position: relative;
        overflow: visible;
        z-index: 1;
    }

    .listing-card-shell.is-menu-open,
    .listing-card-shell:focus-within {
        z-index: 80;
    }

    .listing-card-menu {
        position: relative;
    }

    .listing-card-menu-panel {
        z-index: 90;
    }
</style>
@endpush

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            @include('seller.partials.sidebar')

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">My Listings</h1>
                            <p class="mt-1 text-sm text-slate-500">Manage products, services, and digital listings from one place.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('seller.products.pricing.bulk') }}" class="inline-flex items-center rounded-xl border border-emerald-600 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                <i class="fas fa-money-bill-wave mr-2"></i> Bulk Edit Prices
                            </a>
                            <a href="{{ route('products.create') }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                                <i class="fas fa-plus mr-2"></i> Add New Listing
                            </a>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if(!empty($missingFeaturedActive) && (int)$missingFeaturedActive > 0)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-image mt-0.5"></i>
                            <div>
                                <strong>Action recommended:</strong> You have {{ (int)$missingFeaturedActive }} published listing(s) without a featured image.
                                <a class="font-semibold underline" href="{{ route('products.index', array_merge(request()->except('page'), ['status'=>1,'no_featured'=>1])) }}">Review them here</a> and add a featured image.
                            </div>
                        </div>
                    </div>
                @endif

                @php
                    $filters = $filters ?? [
                        'price_min'  => null,
                        'price_max'  => null,
                        'type'       => null,
                        'country_id' => null,
                    ];
                    $searchQuery = trim((string) request('q', ''));
                @endphp

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <form action="{{ route('products.index') }}" method="GET" class="space-y-3">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="no_featured" value="{{ request('no_featured') }}">
                        <input type="hidden" name="price_min" value="{{ $filters['price_min'] ?? '' }}">
                        <input type="hidden" name="price_max" value="{{ $filters['price_max'] ?? '' }}">
                        <input type="hidden" name="type" value="{{ $filters['type'] ?? '' }}">
                        <input type="hidden" name="country_id" value="{{ $filters['country_id'] ?? '' }}">

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="min-w-0 flex-1">
                                <label for="listing-search" class="mb-1 block text-sm font-medium text-slate-700">Search Listings</label>
                                <div class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 transition focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-100">
                                    <i class="fas fa-search text-slate-400"></i>
                                    <input id="listing-search" type="search" name="q" class="min-w-0 w-full border-0 bg-transparent px-0 py-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                           value="{{ $searchQuery }}" placeholder="Search by listing name or description">
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                                    <i class="fas fa-search mr-2"></i> Search
                                </button>
                                @if($searchQuery !== '')
                                    <a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => null]) }}"
                                       class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Clear Search
                                    </a>
                                @endif
                            </div>
                        </div>

                        <p class="text-xs text-slate-500">Find a listing quickly by title or description.</p>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    @php
                        $labels = [
                            0        => 'Pending',
                            1        => 'Active',
                            2        => 'Paused',
                            3        => 'Suspended',
                            'closed' => 'Closed',
                        ];
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($labels as $key => $label)
                            @php
                                $active = (string) request('status', '') === (string) $key;
                            @endphp
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['status' => $key])) }}"
                               class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $active ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100' }}">
                                {{ $label }}
                                <span class="ml-1 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-900">
                                    {{ $statusCounts[$key] ?? 0 }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <form action="{{ route('products.index') }}" method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="grid grid-cols-12 gap-3">
                        <input type="hidden" name="q" value="{{ $searchQuery }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="no_featured" value="{{ request('no_featured') }}">

                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Min Price</label>
                            <input type="number" step="0.01" min="0" name="price_min" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                                   value="{{ $filters['price_min'] ?? '' }}" placeholder="0.00">
                        </div>

                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Max Price</label>
                            <input type="number" step="0.01" min="0" name="price_max" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                                   value="{{ $filters['price_max'] ?? '' }}" placeholder="0.00">
                        </div>

                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Listing Type</label>
                            <select name="type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All types</option>
                                @foreach(['physical' => 'Physical', 'digital' => 'Digital', 'service' => 'Service'] as $typeKey => $typeLabel)
                                    <option value="{{ $typeKey }}" @selected(($filters['type'] ?? '') === $typeKey)>{{ $typeLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Country</label>
                            <select name="country_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All countries</option>
                                @foreach(($availableCountries ?? collect()) as $country)
                                    <option value="{{ $country->id }}" @selected(($filters['country_id'] ?? null) == $country->id)>{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-12 flex flex-col gap-2 sm:flex-row sm:items-end md:col-span-6 xl:col-span-4">
                            <button type="submit" class="inline-flex w-full flex-1 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
                                <i class="fas fa-filter mr-2"></i> Apply Filters
                            </button>
                            <a href="{{ route('products.index', $resetParams ?? []) }}" class="inline-flex w-full flex-1 items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                @if($products->count())
                    @php
                        $grouped = isset($groupedProducts) && $groupedProducts instanceof \Illuminate\Support\Collection
                            ? $groupedProducts
                            : $products->getCollection()->groupBy(function ($item) {
                                return $item->type ?? 'other';
                            });

                        $sectionMeta = [
                            'physical' => ['title' => 'Products', 'icon' => 'fa-box-open'],
                            'service'  => ['title' => 'Services', 'icon' => 'fa-briefcase'],
                            'digital'  => ['title' => 'Digital Downloads', 'icon' => 'fa-cloud-arrow-down'],
                        ];
                    @endphp

                    @foreach($sectionMeta as $type => $meta)
                        @php $items = $grouped->get($type, collect()); @endphp
                        <section>
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <i class="fas {{ $meta['icon'] }} text-slate-500"></i>
                                    <h2 class="text-lg font-bold text-slate-900">{{ $meta['title'] }}</h2>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-900">{{ $items->count() }}</span>
                            </div>

                            @if($items->isEmpty())
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">
                                    No {{ strtolower($meta['title']) }} on this page.
                                </div>
                            @else
                                <div class="grid grid-cols-12 gap-4">
                                    @foreach($items as $product)
                                        @include('products.partials.listing-card', ['product' => $product])
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endforeach

                    @foreach($grouped as $type => $items)
                        @continue(array_key_exists($type, $sectionMeta) || $items->isEmpty())
                        <section>
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <h2 class="text-lg font-bold capitalize text-slate-900">{{ $type }} Listings</h2>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-900">{{ $items->count() }}</span>
                            </div>
                            <div class="grid grid-cols-12 gap-4">
                                @foreach($items as $product)
                                    @include('products.partials.listing-card', ['product' => $product])
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                        {{ $products->links('pagination::tailwind') }}
                    </div>
                @else
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-5 text-center text-sm text-sky-800">
                        You have not listed any products yet.
                        <div class="mt-3">
                            <a href="{{ route('products.create') }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                                <i class="fas fa-plus-circle mr-1"></i> Create Your First Product
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function isInteractive(el) {
            if (!el) return false;
            var selector = 'a,button,input,select,textarea,label,summary,details,[role="button"]';
            return el.closest(selector) !== null;
        }

        document.querySelectorAll('.js-product-card').forEach(function(card) {
            card.addEventListener('click', function(e) {
                if (isInteractive(e.target)) return;
                var href = card.getAttribute('data-href');
                if (href) window.location.href = href;
            });

            card.addEventListener('keydown', function(e) {
                if (isInteractive(e.target)) return;
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    var href = card.getAttribute('data-href');
                    if (href) window.location.href = href;
                }
            });
        });

        var menus = Array.from(document.querySelectorAll('.listing-card-menu'));

        function closeMenu(menu) {
            if (!menu) return;
            menu.removeAttribute('open');
            var card = menu.closest('.listing-card-shell');
            if (card) {
                card.classList.remove('is-menu-open');
            }
        }

        menus.forEach(function(menu) {
            menu.addEventListener('toggle', function() {
                var card = menu.closest('.listing-card-shell');

                if (menu.open) {
                    menus.forEach(function(otherMenu) {
                        if (otherMenu !== menu) {
                            closeMenu(otherMenu);
                        }
                    });

                    if (card) {
                        card.classList.add('is-menu-open');
                    }
                    return;
                }

                if (card) {
                    card.classList.remove('is-menu-open');
                }
            });
        });

        document.addEventListener('click', function(e) {
            menus.forEach(function(menu) {
                if (menu.open && !menu.contains(e.target)) {
                    closeMenu(menu);
                }
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            menus.forEach(closeMenu);
        });
    });
</script>
@endsection
