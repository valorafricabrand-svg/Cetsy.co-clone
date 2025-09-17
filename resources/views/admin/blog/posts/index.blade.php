@extends('layouts.app')

@section('title', 'Blog Posts')

@section('content')
<div class="container-xxl py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Blog Posts</h1>
            <p class="text-muted mb-0">Manage editorial content showcased on your storefront blog.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-folder-open me-2"></i>Categories
            </a>
            <a href="{{ route('admin.blog-posts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Post
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.blog-posts.index') }}" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Title, slug, excerpt">
            </div>
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}" {{ $statusOption === $status ? 'selected' : '' }}>{{ ucfirst($statusOption) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 col-xl-2">
                <button class="btn btn-primary w-100" type="submit">Filter</button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $post->title }}</div>
                            <div class="text-muted small">/{{ $post->slug }}</div>
                        </td>
                        <td>
                            @php
                                $badge = match($post->status) {
                                    \App\Models\BlogPost::STATUS_PUBLISHED => 'bg-success',
                                    \App\Models\BlogPost::STATUS_SCHEDULED => 'bg-info',
                                    \App\Models\BlogPost::STATUS_ARCHIVED => 'bg-secondary',
                                    default => 'bg-warning text-dark',
                                };
                            @endphp
                            <span class="badge {{ $badge }} text-uppercase">{{ $post->status }}</span>
                            @if($post->published_at)
                                <div class="text-muted small">{{ $post->published_at->format('d M Y, H:i') }}</div>
                            @endif
                        </td>
                        <td>{{ optional($post->category)->name ?? '—' }}</td>
                        <td>{{ optional($post->author)->name ?? 'System' }}</td>
                        <td>{{ $post->updated_at?->diffForHumans() }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.blog-posts.show', $post) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            <a href="{{ route('admin.blog-posts.edit', $post) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('admin.blog-posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Move this post to trash?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No posts found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection
