@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('New Category') }}
</h2>
@endsection

@section('content')
<div class="content">
  <div class="container-lg">

    @if($errors->any())
      <div class="mb-4 p-4 bg-danger text-white rounded">
        <ul class="list-unstyled mb-0">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data"
          x-data="{ name:'', slug:'' }"
          @input.debounce.500ms="slug = name.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'')">
      @csrf

      <!-- Category Name -->
      <div class="mb-4">
        <label for="name" class="form-label">Name</label>
        <input id="name" x-model="name" name="name" type="text"
               class="form-control"
               value="{{ old('name') }}" required>
      </div>

      <!-- Category Slug -->
      <div class="mb-4">
        <label for="slug" class="form-label">Slug</label>
        <input id="slug" x-model="slug" name="slug" type="text"
               class="form-control bg-light"
               value="{{ old('slug') }}" readonly>
      </div>

      <!-- Parent Category -->
      <div class="mb-4">
        <label for="parent_id" class="form-label">Parent Category</label>
        <select id="parent_id" name="parent_id" class="form-select">
          <option value="">— None —</option>
          @foreach($parents as $p)
            <option value="{{ $p->id }}" @selected(old('parent_id')==$p->id)>
              {{ $p->name }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Listing Fee -->
      <div class="mb-4">
        <label for="listing_fee" class="form-label">Listing Fee</label>
        <input id="listing_fee" name="listing_fee" type="number" class="form-control" value="{{ old('listing_fee') }}" step="0.01">
      </div>

      <!-- Featured Image -->
      <div class="mb-6">
        <label for="image" class="form-label">Featured Image</label>
        <input id="image" name="image" type="file" accept="image/*" class="form-control">
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn btn-success btn-lg">
        Save Category
      </button>
    </form>

  </div>
</div>
@endsection
