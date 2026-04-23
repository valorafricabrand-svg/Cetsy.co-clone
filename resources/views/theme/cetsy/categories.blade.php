@extends('theme.'.theme().'.layouts.app')

@php
  use Illuminate\Support\Str;

  $metaTitle = 'Categories | Cetsy Marketplace';
  $metaDescription = 'Browse Cetsy marketplace categories for products, services, and digital downloads from sellers around the world.';
  $categoriesUrl = localized_route('categories.index');
  $typeLabels = [
    'products' => 'Products',
    'services' => 'Services',
    'digital' => 'Digital',
  ];

  $categoryItems = $categories->values()->map(function ($category, $index) {
    return [
      '@type' => 'ListItem',
      'position' => $index + 1,
      'url' => localized_route('category.show', $category->slug),
      'item' => [
        '@type' => 'CollectionPage',
        'name' => $category->name,
        'url' => localized_route('category.show', $category->slug),
      ],
    ];
  })->all();

  $categoriesStructuredData = [
    '@context' => 'https://schema.org',
    '@graph' => [
      [
        '@type' => 'CollectionPage',
        '@id' => $categoriesUrl . '#webpage',
        'name' => 'Cetsy Categories',
        'description' => $metaDescription,
        'url' => $categoriesUrl,
      ],
      [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
          [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => localized_route('home'),
          ],
          [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Categories',
            'item' => $categoriesUrl,
          ],
        ],
      ],
      [
        '@type' => 'ItemList',
        'name' => 'Cetsy marketplace categories',
        'url' => $categoriesUrl,
        'numberOfItems' => $categories->count(),
        'itemListElement' => $categoryItems,
      ],
    ],
  ];
@endphp

@section('title', $metaTitle)
@section('meta_description', $metaDescription)
@section('canonical_url', $categoriesUrl)
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', request()->query() ? 'noindex, follow' : 'index, follow')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode($categoriesStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('styles')
<style>
  .category-directory-card {
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
  }
  .category-directory-card:hover {
    transform: translateY(-2px);
    border-color: rgba(16, 185, 129, .45);
    box-shadow: 0 14px 32px rgba(15, 23, 42, .10);
  }
  .category-directory-thumb {
    aspect-ratio: 4 / 3;
  }
  @media (max-width: 430px) {
    .category-directory-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
</style>
@endpush

@section('main')
<div class="bg-slate-50 pb-10">
  <section class="border-b border-slate-200 bg-white py-10">
    <div class="mx-auto grid w-full max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_380px] lg:items-end lg:px-8">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Marketplace Categories</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Browse Categories</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
          Find products, services, and digital downloads by category across the Cetsy marketplace.
        </p>
      </div>

      <form method="GET" action="{{ $categoriesUrl }}" role="search" class="space-y-3">
        <label for="categorySearch" class="sr-only">Search categories</label>
        <div class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 shadow-sm">
          <i class="fas fa-search text-slate-400"></i>
          <input id="categorySearch" type="search" name="q" value="{{ $search }}" class="min-w-0 w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none" placeholder="Search categories">
          @if($type)
            <input type="hidden" name="type" value="{{ $type }}">
          @endif
          <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">Search</button>
        </div>

        <div class="flex flex-wrap gap-2">
          <a href="{{ $categoriesUrl }}" class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $type === '' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white text-slate-700 hover:border-emerald-300 hover:text-emerald-700' }}">
            All
          </a>
          @foreach($typeLabels as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['type' => $key, 'page' => null]) }}" class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $type === $key ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white text-slate-700 hover:border-emerald-300 hover:text-emerald-700' }}">
              {{ $label }}
            </a>
          @endforeach
          @if($search || $type)
            <a href="{{ $categoriesUrl }}" class="rounded-full px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900">Clear</a>
          @endif
        </div>
      </form>
    </div>
  </section>

  <section class="py-6">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-extrabold text-slate-900">Categories</h2>
        <span class="text-sm text-slate-500">{{ $categories->count() }} {{ Str::plural('category', $categories->count()) }}</span>
      </div>

      @if($categories->isNotEmpty())
        <div class="category-directory-grid grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
          @foreach($categories as $category)
            @php
              $children = collect($category->children ?? []);
              $image = null;
              if (!empty($category->image)) {
                $imagePath = (string) $category->image;
                $image = Str::startsWith($imagePath, ['http://', 'https://', '//'])
                  ? $imagePath
                  : media_url($imagePath);
              }
              $activeCount = (int) ($category->active_products_count ?? 0) + (int) $children->sum('active_products_count');
            @endphp
            <article class="category-directory-card overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
              <a href="{{ localized_route('category.show', $category->slug) }}" class="block">
                <div class="category-directory-thumb bg-slate-100">
                  @if($image)
                    <img src="{{ $image }}" alt="{{ $category->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                    <div class="hidden flex h-full w-full items-center justify-center bg-emerald-50 text-emerald-700">
                      <i class="fas fa-folder-open text-2xl"></i>
                    </div>
                  @else
                    <div class="flex h-full w-full items-center justify-center bg-emerald-50 text-emerald-700">
                      <i class="fas fa-folder-open text-2xl"></i>
                    </div>
                  @endif
                </div>
                <div class="p-3">
                  <h3 class="line-clamp-1 text-sm font-bold text-slate-900 group-hover:text-emerald-700">{{ $category->name }}</h3>
                  <p class="mt-1 text-xs text-slate-500">{{ $activeCount }} {{ Str::plural('listing', $activeCount) }}</p>
                  @if($category->description)
                    <p class="mt-2 line-clamp-2 text-xs leading-5 text-slate-500">{{ Str::limit(strip_tags($category->description), 96) }}</p>
                  @endif
                </div>
              </a>

              @if($children->isNotEmpty())
                <div class="border-t border-slate-100 px-3 py-2">
                  <div class="flex flex-wrap gap-1.5">
                    @foreach($children->take(4) as $child)
                      <a href="{{ localized_route('category.show', $child->slug) }}" class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700">
                        {{ $child->name }}
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif
            </article>
          @endforeach
        </div>
      @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
          No categories found.
        </div>
      @endif
    </div>
  </section>

  @if($featuredProducts->isNotEmpty())
    <section class="py-4">
      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between gap-3">
          <h2 class="text-xl font-extrabold text-slate-900">Latest Listings</h2>
          <a href="{{ localized_route('listings') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-600">View all</a>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 lg:grid-cols-4">
          @foreach($featuredProducts as $item)
            @include('theme.'.theme().'.partials.product-card', ['item' => $item])
          @endforeach
        </div>
      </div>
    </section>
  @endif
</div>
@endsection
