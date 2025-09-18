@extends('theme.'.theme().'.layouts.app')

@php
    $meta = $post->meta ?? [];
    $description = $meta['description'] ?? strip_tags($post->excerpt ?: \Illuminate\Support\Str::limit($post->body, 180));
    $heroImage = $post->featured_image_url ?? asset('assets/img/blog/blog-3.png');
@endphp

@section('title', $meta['title'] ?? $post->title.' | Cetsy Blog')
@section('meta_description', $description)
@section('meta_image', $heroImage)
@section('canonical_url', route('blog.show', $post->slug))

@section('main')
<div class="bg-success py-5 text-white">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb breadcrumb-dark mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50 text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-white-50 text-decoration-none">Blog</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
      </ol>
    </nav>
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge bg-white text-success text-uppercase mb-3">{{ optional($post->category)->name ?? 'Cetsy Updates' }}</span>
        <h1 class="display-5 fw-bold mb-3">{{ $post->title }}</h1>
        <p class="lead text-white-75 mb-4">{{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->body), 160) }}</p>
        <div class="d-flex flex-wrap align-items-center gap-3 text-white-75 small">
          @if($post->author)
            <div><i class="fas fa-user me-1"></i> {{ $post->author->name }}</div>
          @endif
          <div><i class="fas fa-calendar me-1"></i> {{ optional($post->published_at)->format('M j, Y') ?? $post->created_at->format('M j, Y') }}</div>
        </div>
      </div>
      <div class="col-lg-4 text-lg-end">
        <img src="{{ $heroImage }}" alt="{{ $post->title }}" class="img-fluid rounded shadow" style="max-height: 220px; object-fit: cover;">
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
  <div class="row g-5">
    <div class="col-lg-8">
      <article class="blog-article content-body">
        {!! $post->body !!}
      </article>

      <div class="border-top pt-4 mt-5">
        <h2 class="h6 text-uppercase text-success fw-semibold">Share</h2>
        @php
          $shareUrl = urlencode(route('blog.show', $post->slug));
          $shareText = urlencode($post->title.' on Cetsy');
        @endphp
        <div class="d-flex gap-2">
          <a class="btn btn-outline-success btn-sm" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}"><i class="fab fa-x-twitter me-2"></i>Tweet</a>
          <a class="btn btn-outline-success btn-sm" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"><i class="fab fa-facebook-f me-2"></i>Share</a>
          <a class="btn btn-outline-success btn-sm" target="_blank" rel="noopener" href="https://www.linkedin.com/shareArticle?mini=true&url={{ $shareUrl }}&title={{ $shareText }}"><i class="fab fa-linkedin-in me-2"></i>Post</a>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="bg-success-subtle border border-success-subtle rounded-4 p-4">
        <h3 class="h6 text-success text-uppercase fw-semibold mb-3">Stay in the loop</h3>
        <p class="text-success-emphasis small mb-0">Subscribe to seller tips and platform updates straight to your inbox.</p>
      </div>

      @if($relatedPosts->isNotEmpty())
        <div class="mt-5">
          <h3 class="h6 text-uppercase fw-semibold text-success mb-3">More for you</h3>
          <div class="list-group list-group-flush">
            @foreach($relatedPosts as $related)
              @php
                $relatedImage = $related->featured_image_url ?? asset('assets/img/blog/blog-4.png');
              @endphp
              <a href="{{ route('blog.show', $related->slug) }}" class="list-group-item list-group-item-action d-flex gap-3 align-items-center">
                <img src="{{ $relatedImage }}" alt="{{ $related->title }}" class="rounded" style="width: 64px; height: 64px; object-fit: cover;">
                <div>
                  <div class="fw-semibold text-dark mb-1">{{ $related->title }}</div>
                  <div class="small text-muted">{{ optional($related->published_at)->format('M j, Y') ?? $related->created_at->format('M j, Y') }}</div>
                </div>
              </a>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection


