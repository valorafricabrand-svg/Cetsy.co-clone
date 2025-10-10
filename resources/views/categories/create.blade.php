{{-- resources/views/admin/categories/create.blade.php --}}
@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('New Category') }}
</h2>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
(function(){
  function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
  onReady(function(){
    const el = document.getElementById('description');
    if(!el) return;
    const start = function(){
      try{ const i=tinymce.get('description'); if(i) i.remove(); }catch(_){}
      tinymce.init({
        selector:'#description',
        height:300,
        menubar:false,
        plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen help wordcount quickbars autoresize',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
        branding:false,
        browser_spellcheck:true,
        gecko_spellcheck:true,
        elementpath:false,
        base_url: '{{ asset('assets/js/tinymce') }}',
        setup(editor){ editor.on('change', () => editor.save()); }
      });
    };
    if(window.tinymce){ start(); }
    else {
      const s=document.createElement('script');
      s.src='https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js';
      s.referrerPolicy='origin';
      s.onload=start;
      s.onerror=function(){ console.warn('TinyMCE CDN failed to load'); };
      document.head.appendChild(s);
    }
  });
})();
</script>
@endpush

@section('content')
<div class="content">
  <div class="container-lg">

    {{-- Validation Errors --}}
    @if($errors->any())
      <div class="mb-4 p-4 bg-danger text-white rounded">
        <ul class="list-unstyled mb-0">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form
      action="{{ route('admin.categories.store') }}"
      method="POST"
      enctype="multipart/form-data"
      x-data="{ name:'', slug:'' }"
      @input.debounce.500ms="
        slug = name
          .toLowerCase()
          .replace(/[^a-z0-9]+/g,'-')
          .replace(/(^-|-$)/g,'')
      "
    >
      @csrf

      {{-- Category Name --}}
      <div class="mb-4">
        <label for="name" class="form-label">Name</label>
        <input
          id="name"
          x-model="name"
          name="name"
          type="text"
          class="form-control"
          value="{{ old('name') }}"
          required
        >
      </div>

      {{-- Category Slug --}}
      <div class="mb-4">
        <label for="slug" class="form-label">Slug</label>
        <input
          id="slug"
          x-model="slug"
          name="slug"
          type="text"
          class="form-control bg-light"
          value="{{ old('slug') }}"
          readonly
        >
      </div>

      {{-- Parent Category --}}
      <div class="mb-4">
        <label for="parent_id" class="form-label">Parent Category</label>
        <select id="parent_id" name="parent_id" class="form-select">
          <option value="">— None —</option>
          @foreach($parents as $p)
            <option
              value="{{ $p->id }}"
              @selected(old('parent_id') == $p->id)
            >
              {{ $p->name }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Listing Type --}}
      <div class="mb-4">
        <label for="listing_type" class="form-label">Listing Type</label>
        <select id="listing_type" name="listing_type" class="form-select" required>
          <option value="products"  @selected(old('listing_type')=='products')>Products</option>
          <option value="services"  @selected(old('listing_type')=='services')>Services</option>
          <option value="digital"   @selected(old('listing_type')=='digital')>Digital Downloads</option>
        </select>
      </div>

      {{-- Listing Fee --}}
      <div class="mb-4">
        <label for="listing_fee" class="form-label">Listing Fee</label>
        <input
          id="listing_fee"
          name="listing_fee"
          type="number"
          step="0.01"
          class="form-control"
          value="{{ old('listing_fee') }}"
        >
      </div>

      {{-- Description --}}
      <div class="mb-4">
        <label for="description" class="form-label">Description</label>
        <textarea
          id="description"
          name="description"
          rows="3"
          class="form-control"
        >{{ old('description') }}</textarea>
        <div class="form-text">Optional—describe this category for listing pages.</div>
      </div>

      {{-- Featured Image --}}
      <div class="mb-6">
        <label for="image" class="form-label">Featured Image</label>
        <input
          id="image"
          name="image"
          type="file"
          accept="image/*"
          class="form-control"
        >
      </div>

      {{-- Submit Button --}}
      <button type="submit" class="btn btn-success btn-lg">
        Save Category
      </button>
    </form>

  </div>
</div>
@endsection
