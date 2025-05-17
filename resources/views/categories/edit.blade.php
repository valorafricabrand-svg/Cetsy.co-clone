@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Edit Category') }}
</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-lg mx-auto sm:px-6 lg:px-8">

    @if($errors->any())
      <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul class="list-disc pl-5 mb-0">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('categories.update', $category) }}" method="POST" enctype="multipart/form-data"
          x-data="{ name:'{{ addslashes($category->name) }}', slug:'{{ addslashes($category->slug) }}' }"
          @input.debounce.500ms="slug = name.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'')">
      @csrf
      @method('PUT')

      <div class="mb-4">
        <label for="name" class="block font-medium mb-1">Name</label>
        <input id="name" x-model="name" name="name" type="text"
               class="w-full border rounded px-3 py-2"
               value="{{ old('name', $category->name) }}" required>
      </div>

      <div class="mb-4">
        <label for="slug" class="block font-medium mb-1">Slug</label>
        <input id="slug" x-model="slug" name="slug" type="text"
               class="w-full border rounded px-3 py-2 bg-gray-100"
               value="{{ old('slug', $category->slug) }}" readonly>
      </div>

      <div class="mb-4">
        <label for="parent_id" class="block font-medium mb-1">Parent Category</label>
        <select id="parent_id" name="parent_id" class="w-full border rounded px-3 py-2">
          <option value="">— None —</option>
          @foreach($parents as $p)
            <option value="{{ $p->id }}"
              @selected(old('parent_id', $category->parent_id)==$p->id)>
              {{ $p->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="mb-4">
        <label class="block font-medium mb-1">Current Featured Image</label>
        @if($category->image)
          <img src="{{ asset('storage/'.$category->image) }}"
               class="h-24 w-24 object-cover rounded">
        @else
          <span class="text-gray-500">No image uploaded.</span>
        @endif
      </div>

      <div class="mb-6">
        <label for="image" class="block font-medium mb-1">Replace Image</label>
        <input id="image" name="image" type="file" accept="image/*" class="w-full">
      </div>

      <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        Update Category
      </button>
    </form>

  </div>
</div>
@endsection
