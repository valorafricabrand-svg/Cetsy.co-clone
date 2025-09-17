@extends('layouts.app')

@section('title', 'Create Blog Category')

@section('content')
<div class="content">
  <div class="container-xxl">
    <div class="mb-4">
        <h1 class="h3 mb-1">Create Category</h1>
        <p class="text-muted mb-0">Group related posts and make discovery easier.</p>
    </div>

    <form method="POST" action="{{ route('admin.blog-categories.store') }}" class="col-12 col-xl-6">
        @csrf
        @include('admin.blog.categories._form', ['submitLabel' => 'Create Category'])
    </form>
  </div>
</div>
@endsection


