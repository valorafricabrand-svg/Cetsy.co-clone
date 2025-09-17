@extends('layouts.app')

@section('title', 'Create Blog Post')

@section('content')
<div class="container-xxl py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Create Blog Post</h1>
        <p class="text-muted mb-0">Share updates, announcements, and educational content with your community.</p>
    </div>

    <form method="POST" action="{{ route('admin.blog-posts.store') }}">
        @csrf
        @include('admin.blog.posts._form', ['submitLabel' => 'Create Post'])
    </form>
</div>
@endsection
