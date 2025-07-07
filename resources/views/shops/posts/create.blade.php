{{-- resources/views/shops/posts/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h2 class="mb-0 fw-bold">Create New Shop Post</h2>
      <a href="{{ route('seller.shop-posts.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Posts
      </a>
    </div>
    @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">
      {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    <div class="card-body">
      <form action="{{ route('seller.shop-posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
          <div class="col-md-7">
            <div class="mb-3">
              <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
              <textarea name="description" id="description" class="form-control" rows="6" required>{{ old('description') }}</textarea>
            </div>
          </div>
          <div class="col-md-5">
            <div class="mb-3">
              <label for="image" class="form-label">Image</label>
              <input type="file" name="image" id="image" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" id="status" class="form-select" required>
                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="published_at" class="form-label">Published At</label>
                <input type="date" name="published_at" id="published_at" class="form-control" value="{{ old('published_at') }}">
              </div>
              <div class="col-md-6 mb-3">
                <label for="expired_at" class="form-label">Expired At</label>
                <input type="date" name="expired_at" id="expired_at" class="form-control" value="{{ old('expired_at') }}">
              </div>
            </div>
          </div>
        </div>
        <div class="text-end mt-4">
          <a href="{{ route('seller.shop-posts.index') }}" class="btn btn-outline-secondary px-4 me-2">
            <i class="fas fa-times me-1"></i> Cancel
          </a>
          <button type="submit" class="btn btn-success px-4">
            <i class="fas fa-check me-1"></i> Create Post
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection 