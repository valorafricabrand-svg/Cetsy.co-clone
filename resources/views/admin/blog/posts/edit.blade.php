@extends('layouts.app')

@section('title', 'Edit Blog Post')

@section('content')
<div class="content">
  <div class="container-xxl">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Blog Post</h1>
            <p class="text-muted mb-0">Update content and publication details.</p>
        </div>
        <a href="{{ route('admin.blog-posts.show', $post) }}" class="btn btn-outline-secondary">
            <i class="fas fa-eye me-2"></i>Preview
        </a>
    </div>

    <form method="POST" action="{{ route('admin.blog-posts.update', $post) }}">
        @csrf
        @method('PUT')
        @include('admin.blog.posts._form', ['submitLabel' => 'Save Changes'])
    </form>
  </div>
</div>
@endsection


