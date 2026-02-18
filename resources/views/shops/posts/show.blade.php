{{-- resources/views/shops/posts/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm mb-4">
    <div class="border-b border-slate-200 px-4 py-3 flex justify-between items-center bg-white">
      <h2 class="mb-0 font-bold">{{ $shopPost->title }}</h2>
      <a href="{{ route('seller.shop-posts.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">
        <i class="fas fa-arrow-left mr-1"></i> Back to Posts
      </a>
    </div>
    <div class="p-4 sm:p-5">
      @if($shopPost->image)
        <div class="mb-4 text-center">
          <img src="{{ asset('storage/' . $shopPost->image) }}" alt="Post Image" style="max-width: 350px; max-height: 200px; object-fit: cover;" class="rounded shadow-sm border">
        </div>
      @endif
      <div class="mb-3">
        <span class="badge {{ $shopPost->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
          {{ ucfirst($shopPost->status) }}
        </span>
      </div>
      <div class="mb-3">
        <strong>Description:</strong>
        <div class="mt-2">{!! nl2br(e($shopPost->description)) !!}</div>
      </div>
      <div class="grid grid-cols-12 gap-4 mt-4">
        <div class="md:col-span-6 mb-2">
          <strong>Published At:</strong>
          <div>{{ $shopPost->published_at ? $shopPost->published_at->format('Y-m-d') : '-' }}</div>
        </div>
        <div class="md:col-span-6 mb-2">
          <strong>Expired At:</strong>
          <div>{{ $shopPost->expired_at ? $shopPost->expired_at->format('Y-m-d') : '-' }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection 

