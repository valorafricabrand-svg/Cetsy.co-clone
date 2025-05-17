@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-lg py-8" 
     x-data="{ 
       name: '{{ addslashes($product->name) }}', 
       slug: '{{ addslashes($product->slug) }}' 
     }"
     @input.debounce.500ms="
       slug = name.toLowerCase()
                  .replace(/[^a-z0-9]+/g,'-')
                  .replace(/(^-|-$)/g,'');
     ">
  <h2 class="text-2xl font-bold mb-6">Edit Product</h2>

  @if($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
      <ul class="list-disc pl-5 mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <!-- Name -->
    <div class="mb-4">
      <label for="name" class="block font-medium mb-1">Name</label>
      <input id="name" type="text" x-model="name" name="name"
             value="{{ old('name', $product->name) }}"
             class="w-full border rounded px-3 py-2" required>
    </div>

    <!-- Slug -->
    <div class="mb-4">
      <label for="slug" class="block font-medium mb-1">Slug</label>
      <input id="slug" type="text" x-model="slug" name="slug"
             value="{{ old('slug', $product->slug) }}"
             class="w-full border rounded px-3 py-2" readonly>
      <small class="text-gray-500">
        URL: <code>{{ url('products') }}/<span x-text="slug"></span></code>
      </small>
    </div>

    <!-- Category -->
    <div class="mb-4">
      <label for="category_id" class="block font-medium mb-1">Category</label>
      <select id="category_id" name="category_id" class="w-full border rounded px-3 py-2">
        <option value="">-- Select Category --</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}"
            @selected(old('category_id', $product->category_id)==$category->id)>
            {{ $category->name }}
          </option>
        @endforeach
      </select>
    </div>

    <!-- Description -->
    <div class="mb-4">
      <label for="description" class="block font-medium mb-1">Description</label>
      <textarea id="description" name="description" rows="4"
                class="w-full border rounded px-3 py-2">{{ old('description', $product->description) }}</textarea>
    </div>

    <!-- Price -->
    <div class="mb-4">
      <label for="price" class="block font-medium mb-1">Price (KES)</label>
      <input id="price" type="number" name="price"
             value="{{ old('price', $product->price) }}"
             class="w-full border rounded px-3 py-2" min="0" step="0.01" required>
    </div>

    <!-- Stock -->
    <div class="mb-4">
      <label for="stock" class="block font-medium mb-1">Stock</label>
      <input id="stock" type="number" name="stock"
             value="{{ old('stock', $product->stock) }}"
             class="w-full border rounded px-3 py-2" min="0" required>
    </div>

    <!-- Status -->
    <div class="mb-4">
      <label for="status" class="block font-medium mb-1">Status</label>
      <select id="status" name="status" class="w-full border rounded px-3 py-2" required>
        <option value="draft"   @selected(old('status', $product->status)=='draft')>Draft</option>
        <option value="active"  @selected(old('status', $product->status)=='active')>Active</option>
        <option value="archived"@selected(old('status', $product->status)=='archived')>Archived</option>
      </select>
    </div>

    <!-- Existing Images -->
    @if($product->media->count())
      <div class="mb-4">
        <p class="font-medium mb-2">Existing Images</p>
        <div class="flex flex-wrap gap-2">
          @foreach($product->media as $media)
            <img src="{{ asset('storage/' . $media->url) }}"
                 class="w-24 h-24 object-cover rounded">
          @endforeach
        </div>
      </div>
    @endif

    <!-- Add More Images -->
    <div class="mb-6">
      <label for="images" class="block font-medium mb-1">Add More Images</label>
      <input id="images" type="file" name="images[]" multiple accept="image/*" class="w-full">
    </div>

    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
      Update Product
    </button>
  </form>
</div>
@endsection
