{{-- resources/views/admin/categories/edit.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Edit Category') }}
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

@section('main')
<div class="content">
  <div class="container-lg">

    {{-- NAV BUTTON --}}
    <div class="flex justify-between items-center mb-4">
      <a href="{{ route('admin.categories.show', $category) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">
        &larr; Back to details
      </a>
    </div>

    {{-- Validation errors --}}
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
      action="{{ route('admin.categories.update', $category) }}"
      method="POST"
      enctype="multipart/form-data"
    >
      @csrf
      @method('PUT')

      <!-- Category Name -->
      <div class="mb-4">
        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
        <input
          id="name"
          name="name"
          type="text"
          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
          value="{{ old('name', $category->name) }}"
          required
        >
      </div>

      <!-- Category Slug -->
      <div class="mb-4">
        <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">Slug</label>
        <input
          id="slug"
          name="slug"
          type="text"
          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-100"
          value="{{ old('slug', $category->slug) }}"
          readonly
        >
      </div>

      <!-- Parent Category Selection -->
      <div class="mb-4">
        <label for="parent_id" class="mb-1 block text-sm font-medium text-slate-700">Parent Category</label>
        <select id="parent_id" name="parent_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
          <option value="">- None -</option>
          @foreach($parents as $p)
            <option
              value="{{ $p->id }}"
              @selected(old('parent_id', $category->parent_id) == $p->id)
            >
              {{ $p->name }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Listing Type -->
      <div class="mb-4">
        <label for="listing_type" class="mb-1 block text-sm font-medium text-slate-700">Listing Type</label>
        <select id="listing_type" name="listing_type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required>
          <option value="products" @selected(old('listing_type', $category->listing_type)=='products')>
            Products
          </option>
          <option value="services" @selected(old('listing_type', $category->listing_type)=='services')>
            Services
          </option>
          <option value="digital" @selected(old('listing_type', $category->listing_type)=='digital')>
            Digital Downloads
          </option>
        </select>
      </div>

      <!-- Listing Fee -->
      <div class="mb-4">
        <label for="listing_fee" class="mb-1 block text-sm font-medium text-slate-700">Listing Fee</label>
        <input
          id="listing_fee"
          name="listing_fee"
          type="number"
          step="0.01"
          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
          value="{{ old('listing_fee', $category->listing_fee) }}"
        >
      </div>

      <!-- Listing Frequency -->
      <div class="mb-4">
        <label for="listing_frequency" class="mb-1 block text-sm font-medium text-slate-700">Listing Frequency</label>
        <select id="listing_frequency" name="listing_frequency" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required>
          <option value="1" @selected(old('listing_frequency', $category->listing_frequency) == 1 || old('listing_frequency', $category->listing_frequency) === '1')>1 month</option>
          <option value="4" @selected(old('listing_frequency', $category->listing_frequency) == 4 || old('listing_frequency', $category->listing_frequency) === '4')>4 months</option>
        </select>
      </div>

      <!-- Description -->
      <div class="mb-4">
        <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
        <textarea
          id="description"
          name="description"
          rows="3"
          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
        >{{ old('description', $category->description) }}</textarea>
        <div class="mt-1 text-xs text-slate-500">Optional - briefly describe this category.</div>
      </div>

      <!-- Current Featured Image -->
      <div class="mb-4">
        <label class="mb-1 block text-sm font-medium text-slate-700">Current Featured Image</label><br>
        @if($category->image)
          <img
            src="{{ asset('storage/' . $category->image) }}"
            class="img-fluid rounded"
            style="max-width: 150px;"
          >
        @else
          <span class="text-slate-500">No image uploaded.</span>
        @endif
      </div>

      <!-- Replace Image -->
      <div class="mb-6">
        <label for="image" class="mb-1 block text-sm font-medium text-slate-700">Replace Image</label>
        <input
          id="image"
          name="image"
          type="file"
          accept="image/*"
          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
        >
      </div>

      <!-- Submit Button -->
      <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
        Update Category
      </button>
    </form>
  </div>
</div>
@endsection
