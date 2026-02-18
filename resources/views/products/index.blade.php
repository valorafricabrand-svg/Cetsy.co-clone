{{-- resources/views/products/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
    <style>
      .js-product-card { cursor: pointer; }
    </style>
    <div class="flex justify-between items-center mb-4">
        <h2 class="mb-0">My Listings</h2>
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('seller.products.pricing.bulk') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 rounded-full">
              <i class="bi bi-cash-coin mr-1"></i> Bulk Edit Prices
          </a>
          <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 rounded-full">
              <i class="fas fa-plus mr-1"></i> Add New Listing
          </a>
        </div>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 alert-dismissible rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Reminder: active listings without featured image --}}
    @if(!empty($missingFeaturedActive) && (int)$missingFeaturedActive > 0)
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 alert-dismissible rounded-3" role="alert">
            <div class="flex items-start gap-2">
                <i class="fas fa-image mt-1"></i>
                <div>
                    <strong>Action recommended:</strong> You have {{ (int)$missingFeaturedActive }} published listing(s) without a featured image.
                    <a class="alert-link" href="{{ route('products.index', array_merge(request()->except('page'), ['status'=>1,'no_featured'=>1])) }}">Review them here</a> and add a featured image.
                </div>
            </div>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Statusâ€counts bar --}}
    <div class="mb-4">
        @php
            $labels = [
                0        => 'Pending',
                1        => 'Active',
                2        => 'Paused',
                3        => 'Suspended',
                'closed' => 'Closed',
            ];
            $classes = [
                0        => 'warning',
                1        => 'success',
                2        => 'secondary',
                3        => 'secondary',
                'closed' => 'dark',
            ];
        @endphp

        @foreach($labels as $key => $label)
            <a
                href="{{ route('products.index', array_merge(request()->except('page'), ['status' => $key])) }}"
                class="btn btn-{{ $classes[$key] }} btn-sm me-2"
            >
                {{ $label }}
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900 ml-1">
                    {{ $statusCounts[$key] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    @php
        $filters = $filters ?? [
            'price_min' => null,
            'price_max' => null,
        'type'      => null,
        'country_id'=> null,
        ];
    @endphp

    <form action="{{ route('products.index') }}" method="GET" class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm border-0 mb-4">
      <div class="p-4 sm:p-5 grid grid-cols-12 gap-4 gap-3 items-end">
        <input type="hidden" name="q" value="{{ request('q') }}">
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

        <div class="col-span-12 md:col-span-6 xl:col-span-4 flex gap-2 items-end justify-end">
          <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 flex-grow-1"><i class="fas fa-filter mr-1"></i> Apply Filters</button>
          <a href="{{ route('products.index', $resetParams ?? []) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 flex-grow-1 text-center" title="Reset filters">Reset</a>
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
            <section class="mb-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas {{ $meta['icon'] }} text-muted"></i>
                        <h4 class="mb-0">{{ $meta['title'] }}</h4>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900">{{ $items->count() }}</span>
                </div>

                @if($items->isEmpty())
                    <div class="rounded-xl border px-4 py-3 text-sm alert-light border rounded-3 text-slate-500 mb-0">
                        No {{ strtolower($meta['title']) }} on this page.
                    </div>
                @else
                    <div class="grid grid-cols-12 gap-4 gap-4">
                        @foreach($items as $product)
                            @include('products.partials.listing-card', ['product' => $product])
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        @foreach($grouped as $type => $items)
            @continue(array_key_exists($type, $sectionMeta) || $items->isEmpty())
            <section class="mb-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="mb-0 text-capitalize">{{ $type }} Listings</h4>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900">{{ $items->count() }}</span>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-4">
                    @foreach($items as $product)
                        @include('products.partials.listing-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $products->links('pagination::tailwind') }}
        </div>
    @else
        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 rounded-3 text-center py-4">
            You havenâ€™t listed any products yet.
            <div class="mt-2">
                <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500 rounded-full">
                    <i class="fas fa-plus-circle mr-1"></i> Create Your First Product
                </a>
            </div>
        </div>
    @endif
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    function isInteractive(el) {
      if (!el) return false;
      var selector = 'a,button,input,select,textarea,label,.btn,[data-bs-toggle]';
      return el.closest(selector) !== null;
    }
    document.querySelectorAll('.js-product-card').forEach(function(card){
      card.addEventListener('click', function(e){
        if (isInteractive(e.target)) return;
        var href = card.getAttribute('data-href');
        if (href) window.location.href = href;
      });
      card.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          var href = card.getAttribute('data-href');
          if (href) window.location.href = href;
        }
      });
    });
  });
</script>
@endsection


