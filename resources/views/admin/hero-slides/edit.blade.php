@extends('layouts.app')

@section('header')
  <div class="d-flex align-items-center justify-content-between">
    <h2 class="h4 mb-0">Edit Hero Slide</h2>
    <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>
@endsection

@section('content')
  <div class="content">
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
