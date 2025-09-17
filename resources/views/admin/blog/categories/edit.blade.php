@extends('layouts.app')

@section('title', 'Edit Blog Category')

@section('content')
<div class="container-xxl py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Edit Category</h1>
        <p class="text-muted mb-0">Rename or retire categories as your content evolves.</p>
    </div>

    <form method="POST" action="{{ route('admin.blog-categories.update', $category) }}" class="col-12 col-xl-6">
        @csrf
        @method('PUT')
        @include('admin.blog.categories._form', ['submitLabel' => 'Save Changes'])
    </form>
</div>
@endsection
