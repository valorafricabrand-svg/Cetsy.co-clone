@extends('layouts.app')

@section('title', 'View Blog Post')

@section('content')
<div class="content">
  <div class="container-xxl">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $blogPost->title }}</h1>
            <p class="text-muted mb-0">Last updated {{ $blogPost->updated_at?->diffForHumans() }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog-posts.edit', $blogPost) }}" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Edit</a>
            <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-outline-secondary">Back to list</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    @if($blogPost->featured_image_url)
                        <img src="{{ $blogPost->featured_image_url }}" class="img-fluid rounded mb-3" alt="Featured image">
                    @endif
                    <article class="prose">
                        {!! nl2br(e($blogPost->body)) !!}
                    </article>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Post Meta</h5>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted">Slug</dt>
                        <dd class="col-7">/{{ $blogPost->slug }}</dd>

                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7 text-capitalize">{{ $blogPost->status }}</dd>

                        <dt class="col-5 text-muted">Category</dt>
                        <dd class="col-7">{{ optional($blogPost->category)->name ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Author</dt>
                        <dd class="col-7">{{ optional($blogPost->author)->name ?? 'System' }}</dd>

                        <dt class="col-5 text-muted">Published At</dt>
                        <dd class="col-7">{{ $blogPost->published_at?->format('d M Y, H:i') ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Created</dt>
                        <dd class="col-7">{{ $blogPost->created_at?->format('d M Y, H:i') }}</dd>
                    </dl>
                </div>
            </div>

            @if(!empty($blogPost->meta))
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">SEO Meta</h5>
                    </div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            @foreach($blogPost->meta as $key => $value)
                                <dt class="col-4 text-muted text-capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                                <dd class="col-8">{{ $value }}</dd>
                            @endforeach
                        </dl>
                    </div>
                </div>
            @endif
        </div>
    </div>
  </div>
</div>
@endsection



