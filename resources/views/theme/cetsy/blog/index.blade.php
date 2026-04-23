@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy.co Blog')
@section('meta_description', 'Fresh stories, maker spotlights, and platform updates from the Cetsy.co team.')
@section('canonical_url', route('blog.index'))
@section('meta_robots', request()->query() ? 'noindex, follow' : 'index, follow')

@section('main')
<div class="relative overflow-x-clip pb-10">
  <div class="pointer-events-none absolute -right-24 -top-20 h-80 w-80 rounded-full bg-emerald-200/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-24 top-[22rem] h-72 w-72 rounded-full bg-cyan-200/30 blur-3xl"></div>

  <section class="relative bg-gradient-to-b from-emerald-900 to-emerald-700 py-12 text-white">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="grid items-center gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">Cetsy.co Journal</p>
          <h1 class="mt-2 text-4xl font-extrabold leading-tight md:text-5xl">Cetsy.co Blog</h1>
          <p class="mt-3 max-w-2xl text-sm text-emerald-50/95 md:text-base">
            Stories, tips, and product updates to help you build a thriving handmade brand.
          </p>
        </div>

        <div class="justify-self-start lg:justify-self-end">
          <img src="{{ asset('assets/img/blog/blog-1.png') }}" alt="Cetsy.co blog" class="h-36 w-auto max-w-full rounded-2xl border border-white/20 bg-white/10 p-2 shadow-xl md:h-44">
        </div>
      </div>
    </div>
  </section>

  <section class="bg-slate-50 py-6">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      @if($categories->count())
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="flex flex-wrap items-center gap-2">
            <span class="mr-2 text-sm font-semibold text-slate-700">Browse by topic:</span>
            <a
              href="{{ route('blog.index') }}"
              class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ empty($activeCategory) ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:border-emerald-300 hover:text-emerald-700' }}"
            >
              All posts
            </a>
            @foreach($categories as $category)
              <a
                href="{{ route('blog.index', ['category' => $category->slug]) }}"
                class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $activeCategory === $category->slug ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:border-emerald-300 hover:text-emerald-700' }}"
              >
                {{ $category->name }}
              </a>
            @endforeach
          </div>
        </div>
      @endif

      @if($posts->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-emerald-300 bg-white px-6 py-12 text-center text-slate-500">
          <i class="fas fa-leaf mb-3 block text-4xl text-emerald-500"></i>
          <h2 class="text-xl font-semibold text-slate-800">No stories just yet</h2>
          <p class="mt-2 text-sm">Check back soon for the latest maker spotlights and platform announcements.</p>
        </div>
      @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          @foreach($posts as $post)
            @php
              $image = $post->featured_image_url ?? asset('assets/img/blog/blog-2.png');
              $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160);
              $published = optional($post->published_at)->format('M j, Y') ?? $post->created_at->format('M j, Y');
            @endphp

            <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
              <a href="{{ route('blog.show', $post->slug) }}" class="block">
                <div class="aspect-[16/9] overflow-hidden border-b border-slate-200 bg-slate-100">
                  <img src="{{ $image }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                </div>

                <div class="p-4">
                  <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                    {{ optional($post->category)->name ?? 'Cetsy.co Updates' }}
                    <span class="mx-1">-</span>
                    {{ $published }}
                  </p>

                  <h2 class="line-clamp-2 text-lg font-bold text-slate-900 group-hover:text-emerald-700">{{ $post->title }}</h2>
                  <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $excerpt }}</p>
                </div>
              </a>
            </article>
          @endforeach
        </div>

        <div class="mt-6 flex justify-center">
          {{ $posts->links() }}
        </div>
      @endif
    </div>
  </section>
</div>
@endsection
