@extends('layouts.app')

@section('title', 'View Blog Post')

@push('styles')
<style>
    .blog-post-content {
        color: #344054;
        line-height: 1.75;
        overflow-wrap: anywhere;
    }

    .blog-post-content > :first-child {
        margin-top: 0;
    }

    .blog-post-content > :last-child {
        margin-bottom: 0;
    }

    .blog-post-content h1,
    .blog-post-content h2,
    .blog-post-content h3,
    .blog-post-content h4,
    .blog-post-content h5,
    .blog-post-content h6 {
        color: #101828;
        font-weight: 700;
        line-height: 1.3;
        margin: 1.5rem 0 0.75rem;
    }

    .blog-post-content p,
    .blog-post-content ul,
    .blog-post-content ol,
    .blog-post-content blockquote,
    .blog-post-content table {
        margin-bottom: 1rem;
    }

    .blog-post-content ul,
    .blog-post-content ol {
        padding-left: 1.5rem;
    }

    .blog-post-content blockquote {
        border-left: 4px solid #d0d5dd;
        color: #667085;
        margin-left: 0;
        padding-left: 1rem;
    }

    .blog-post-content img {
        border-radius: 0.75rem;
        height: auto;
        max-width: 100%;
    }

    .blog-post-content table {
        width: 100%;
    }
</style>
@endpush

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
                    <article class="blog-post-content">
                        {!! $blogPost->body !!}
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
                        <dd class="col-7">{{ optional($blogPost->category)->name ?? '-' }}</dd>

                        <dt class="col-5 text-muted">Author</dt>
                        <dd class="col-7">{{ optional($blogPost->author)->name ?? 'System' }}</dd>

                        <dt class="col-5 text-muted">Published At</dt>
                        <dd class="col-7">{{ $blogPost->published_at?->format('d M Y, H:i') ?? '-' }}</dd>

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


