{{-- resources/views/shops/posts/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h2 class="mb-0 fw-bold">Shop Posts</h2>
      <a href="{{ route('seller.shop-posts.create') }}" class="btn btn-outline-success">
        <i class="fas fa-plus me-1"></i> Create Post
      </a>
    </div>
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Title</th>
            <th>Description</th>
            <th class="text-center">Image</th>
            <th>Status</th>
            <th>Published At</th>
            <th>Expired At</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($shopPosts as $post)
            <tr>
              <td class="fw-semibold">{{ $post->title }}</td>
              <td>
                <span title="{{ $post->description }}">
                  {{ Str::limit($post->description, 60) }}
                </span>
              </td>
              <td class="text-center">
                @if($post->image)
                  <img src="{{ asset('storage/' . $post->image) }}" alt="Image" style="width: 60px; height: 40px; object-fit: cover;" class="rounded shadow-sm border">
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>
                @if($post->status === 'published')
                  <span class="badge bg-success">Published</span>
                @else
                  <span class="badge bg-secondary">Draft</span>
                @endif
              </td>
              <td>{{ $post->published_at ? $post->published_at->format('Y-m-d') : '-' }}</td>
              <td>{{ $post->expired_at ? $post->expired_at->format('Y-m-d') : '-' }}</td>
              <td class="text-center">
                <a href="{{ route('seller.shop-posts.edit', $post) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="{{ route('seller.shop-posts.show', $post) }}" class="btn btn-sm btn-outline-info me-1" title="View">
                  <i class="fas fa-eye"></i>
                </a>
                <form action="{{ route('seller.shop-posts.destroy', $post) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this post?')">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <i class="fas fa-file-alt fa-2x mb-2"></i>
                <div>No posts found. Click <b>Create Post</b> to add your first shop post.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection 