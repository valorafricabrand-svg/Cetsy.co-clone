{{-- resources/views/shops/posts/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h2 class="mb-0 fw-bold">{{ $shopPost->title }}</h2>
      <a href="{{ route('seller.shop-posts.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Posts
      </a>
    </div>
    <div class="card-body">
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
      <div class="row mt-4">
        <div class="col-md-6 mb-2">
          <strong>Published At:</strong>
          <div>{{ $shopPost->published_at ? $shopPost->published_at->format('Y-m-d') : '-' }}</div>
        </div>
        <div class="col-md-6 mb-2">
          <strong>Expired At:</strong>
          <div>{{ $shopPost->expired_at ? $shopPost->expired_at->format('Y-m-d') : '-' }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection 