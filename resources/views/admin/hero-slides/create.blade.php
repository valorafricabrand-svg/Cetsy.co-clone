@extends('admin.layout')

@section('content')
  <h1 class="h3 mb-4">Create Hero Slide</h1>

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
@endsection
