{{-- resources/views/shops/posts/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4 shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="mb-0 font-bold">Shop Posts</h2>
      <a href="{{ route('seller.shop-posts.create') }}" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 sm:w-auto">
        <i class="fas fa-plus mr-1"></i> Create Post
      </a>
    </div>
    <div class="overflow-x-auto p-0">
      <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
        <thead class="bg-slate-50">
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
              <td class="font-semibold">{{ $post->title }}</td>
              <td>
                <span title="{{ $post->description }}">
                  {{ Str::limit($post->description, 60) }}
                </span>
              </td>
              <td class="text-center">
                @if($post->image)
                  <img src="{{ asset('storage/' . $post->image) }}" alt="Image" style="width: 60px; height: 40px; object-fit: cover;" class="rounded shadow-sm border">
                @else
                  <span class="text-slate-500">â€”</span>
                @endif
              </td>
              <td>
                @if($post->status === 'published')
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success">Published</span>
                @else
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200">Draft</span>
                @endif
              </td>
              <td>{{ $post->published_at ? $post->published_at->format('Y-m-d') : '-' }}</td>
              <td>{{ $post->expired_at ? $post->expired_at->format('Y-m-d') : '-' }}</td>
              <td class="text-center">
                <a href="{{ route('seller.shop-posts.edit', $post) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50 mr-1" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="{{ route('seller.shop-posts.show', $post) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs btn-outline-info mr-1" title="View">
                  <i class="fas fa-eye"></i>
                </a>
                <form action="{{ route('seller.shop-posts.destroy', $post) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50" title="Delete" onclick="return confirm('Are you sure you want to delete this post?')">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-slate-500 py-5">
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
