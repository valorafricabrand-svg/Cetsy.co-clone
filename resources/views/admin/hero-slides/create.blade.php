@extends('layouts.app')

@section('header')
  <div class="d-flex align-items-center justify-content-between">
    <h2 class="h4 mb-0">Create Hero Slide</h2>
    <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>
@endsection

@section('content')
  <div class="content">
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.hero-slides.store') }}" enctype="multipart/form-data" class="card shadow-sm border-0">
      <div class="card-body">
        @include('admin.hero-slides._form', ['slide' => $slide, 'categories' => $categories, 'deals' => $deals])
      </div>
    </form>
  </div>
@endsection
