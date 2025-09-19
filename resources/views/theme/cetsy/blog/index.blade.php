@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy Blog')
@section('meta_description', 'Fresh stories, maker spotlights, and platform updates from the Cetsy team.')
@section('canonical_url', route('blog.index'))

@section('main')
<div class="bg-success-subtle border-bottom border-success-subtle py-5">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <h1 class="display-5 fw-bold text-success mb-3">Cetsy Blog</h1>
        <p class="lead text-success-emphasis mb-0">
          Stories, tips, and product updates to help you build a thriving handmade brand.
        </p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <img src="{{ asset('assets/img/blog/blog-1.png') }}" alt="Cetsy blog" class="img-fluid" style="max-height: 180px;">
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
  @if($categories->count())
    <div class="mb-4">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="fw-semibold text-success-emphasis">Browse by topic:</span>
        <a href="{{ route('blog.index') }}"
           class="btn btn-sm {{ empty($activeCategory) ? 'btn-success' : 'btn-outline-success' }} rounded-pill">
          All posts
        </a>
        @foreach($categories as $category)
          <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
             class="btn btn-sm {{ $activeCategory === $category->slug ? 'btn-success' : 'btn-outline-success' }} rounded-pill">
            {{ $category->name }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  @if($posts->isEmpty())
    <div class="text-center py-5">
      <div class="mb-3"><i class="fas fa-leaf fa-3x text-success"></i></div>
      <h2 class="h4 fw-semibold">No stories just yet</h2>
      <p class="text-muted mb-0">Check back soon for the latest maker spotlights and platform announcements.</p>
    </div>
  @else
    <div class="row g-4">
      @foreach($posts as $post)
        <div class="col-md-6 col-lg-4">
          <article class="card h-100 shadow-sm border-0">
            @php
              $image = $post->featured_image_url ?? asset('assets/img/blog/blog-2.png');
              $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160);
            @endphp
            <a href="{{ route('blog.show', $post->slug) }}" class="ratio ratio-16x9">
              <img src="{{ $image }}" class="card-img-top object-fit-cover rounded-top" alt="{{ $post->title }}">
            </a>
            <div class="card-body d-flex flex-column">
              <div class="mb-2 text-muted small text-uppercase">
                {{ optional($post->category)->name ?? 'Cetsy Updates' }}
                <span class="mx-1">•</span>
                {{ optional($post->published_at)->format('M j, Y') ?? $post->created_at->format('M j, Y') }}
              </div>
              <h2 class="h5 fw-bold"><a href="{{ route('blog.show', $post->slug) }}" class="stretched-link text-decoration-none text-dark">{{ $post->title }}</a></h2>
              <p class="text-muted mb-0">{{ $excerpt }}</p>
            </div>
          </article>
        </div>
      @endforeach
    </div>

    <div class="mt-5 d-flex justify-content-center">
      {{ $posts->links() }}
    </div>
  @endif
</div>
@endsection


