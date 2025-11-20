@extends('layouts.app')

@section('content')
  <div class="content">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Edit Hero Slide</h1>
        <p class="text-muted mb-0">Update the content and links for this homepage hero slide.</p>
      </div>
      <div>
        <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-1"></i> Back to Slides
        </a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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

    <form method="POST" action="{{ route('admin.hero-slides.update', $slide) }}" enctype="multipart/form-data" class="card shadow-sm border-0">
      @method('PUT')
      <div class="card-body">
        @include('admin.hero-slides._form', ['slide' => $slide, 'categories' => $categories, 'deals' => $deals])
      </div>
    </form>
  </div>
@endsection
