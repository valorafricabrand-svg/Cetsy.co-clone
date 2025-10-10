@extends('layouts.app')
@section('title', $product->name . ' | Edit Details')

@push('styles')
<style>
  .page-header-sticky{position:sticky;top:0;z-index:1020;background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
  .tab-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;white-space:nowrap}
  .tab-scroll .nav-link{border-radius:999px}
  .rounded-4{border-radius:1rem!important}
</style>
@endpush

@section('content')
@php $current = \Illuminate\Support\Facades\Route::currentRouteName(); @endphp

<div class="content" x-data="detailsForm()" x-init="init()">
  {{-- Header tabs --}}
  <div class="page-header-sticky">
    <div class="container-fluid px-0">
      <div class="tab-scroll px-2 py-2">
        <ul class="nav nav-pills gap-2 flex-nowrap">
          <li class="nav-item"><a class="nav-link {{ $current==='products.show' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.show', $product) }}"><i class="fa-regular fa-circle-question me-1"></i> About</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.pricing' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.pricing', $product) }}"><i class="fa-solid fa-tags me-1"></i> Price & Inventory</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.variations' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.variations', $product) }}"><i class="fa-solid fa-layer-group me-1"></i> Variations</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.details' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.details', $product) }}"><i class="fa-regular fa-rectangle-list me-1"></i> Details</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.shipping' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.shipping', $product) }}"><i class="fa-solid fa-truck me-1"></i> Shipping</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.media' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.media', $product) }}"><i class="fa-regular fa-images me-1"></i> Media</a></li>
          <li class="nav-item"><a class="nav-link {{ $current==='products.settings' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.settings', $product) }}"><i class="fa-solid fa-gear me-1"></i> Settings</a></li>


            
        </ul>
      </div>
    </div>
  </div>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="alert alert-danger mt-3">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 ps-3">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
  @endif

  <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
    <h2 class="mb-0">{{ $product->name }} — Edit Details</h2>
    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-dark btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>

  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
      <form action="{{ route('products.details.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PATCH')

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-semibold">Listing Name</label>
            <input type="text" name="name" id="name" spellcheck="true" autocapitalize="sentences" autocomplete="on"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $product->name) }}" required autofocus>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Listing Type</label>
            <select name="type" x-model="type" @change="loadCategories()"
                    class="form-select @error('type') is-invalid @enderror" required>
              <option value="">Choose type</option>
              <option value="physical" @selected(old('type',$product->type)=='physical')>Physical</option>
              <option value="digital"  @selected(old('type',$product->type)=='digital')>Digital</option>
              <option value="service"  @selected(old('type',$product->type)=='service')>Service</option>
            </select>
            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Category</label>
            <select id="category_id" name="category_id" x-model="categoryId"
                    class="form-select @error('category_id') is-invalid @enderror" required>
              <option value="">Choose category</option>
              {{-- filled by Alpine --}}
            </select>
            <div x-show="fallback" class="form-text text-warning">Showing all categories (fallback). Ask admin to tag categories by type.</div>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          

          <div class="col-12">
            <label class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="8" spellcheck="true"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Full product description...">{{ old('description',$product->description ?? '') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Digital-only file upload (optional) --}}
          <div class="col-md-6" x-show="type==='digital'">
            <label class="form-label fw-semibold">Digital File</label>
            <input type="file" name="digital_file"
                   class="form-control @error('digital_file') is-invalid @enderror"
                   accept=".zip,.pdf,.mp3,.mp4,.docx,.xlsx,.pptx">
            @error('digital_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Upload replacement or additional file for this digital listing.</div>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
          <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  {{-- Existing Digital Files (if any) --}}
  @if($product->digitalFiles->count())
    <div class="card mt-4 shadow-sm">
      <div class="card-header bg-light"><h5 class="mb-0">Current Digital Files</h5></div>
      <ul class="list-group list-group-flush">
        @foreach($product->digitalFiles as $file)
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <a href="{{ route('digital-files.download',$file) }}" target="_blank">
              <i class="fas fa-file-download me-2"></i>{{ $file->filename }}
            </a>
            <form action="{{ route('digital-files.destroy',$file) }}" method="POST" onsubmit="return confirm('Delete this file?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
          </li>
        @endforeach
      </ul>
    </div>
  @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
@php($__ALL_CATS = \App\Models\Category::select('id','name','listing_type')->orderBy('name')->get(['id','name','listing_type']))
<script>
// Local cache of categories for robust fallback when API fails/redirects
window.__ALL_CATEGORIES__ = @json($__ALL_CATS);
function detailsForm(){
  return {
    type: '{{ old('type',$product->type) }}',
    categoryId: '{{ old('category_id',$product->category_id) }}',
    fallback: false,
    init(){ this.loadCategories(); },
    filterLocalByType(tp){
      const map = { physical: 'products', service: 'services', digital: 'digital' };
      const want = map[String(tp) || ''] || null;
      if (!want) return [];
      const all = Array.isArray(window.__ALL_CATEGORIES__) ? window.__ALL_CATEGORIES__ : [];
      return all.filter(c => (String(c.listing_type || '').toLowerCase() === want));
    },
    async loadCategories(){
      const sel = document.getElementById('category_id');
      if(!sel) return;
      sel.innerHTML = '<option>Loading…</option>';
      if(!this.type){ sel.innerHTML = '<option value="">Choose category</option>'; return; }
      try{
        const url = `/api/categories/by-type/${encodeURIComponent(this.type)}?_=${Date.now()}`;
        const res = await fetch(url, { headers: { 'Accept':'application/json' } });
        if(!res.ok) throw new Error(`HTTP ${res.status}`);
        let cats;
        const ct = res.headers.get('content-type') || '';
        this.fallback = (res.headers.get('x-categories-fallback') === '1');
        if (ct.includes('application/json')) {
          cats = await res.json();
        } else {
          const txt = await res.text();
          try { cats = JSON.parse(txt); } catch { console.warn('Categories API non-JSON:', txt.slice(0,200)); cats = []; }
        }
        if (this.fallback || !Array.isArray(cats) || cats.length === 0) {
          cats = this.filterLocalByType(this.type);
        }
        sel.innerHTML = '<option value="">Choose category</option>';
        (Array.isArray(cats) ? cats : []).forEach(c=>{
          const o = document.createElement('option');
          o.value = c.id; o.text = c.name;
          if(String(c.id)===String(this.categoryId)) o.selected=true;
          sel.append(o);
        });
      }catch(e){
        console.warn('Categories load warning:', e);
        this.fallback=false;
        // Try local fallback by type
        const cats = this.filterLocalByType(this.type);
        if (cats.length) {
          sel.innerHTML = '<option value="">Choose category</option>';
          cats.forEach(c=>{ const o=document.createElement('option'); o.value=c.id; o.text=c.name; if(String(c.id)===String(this.categoryId)) o.selected=true; sel.append(o); });
        } else {
          sel.innerHTML = '<option>Error loading categories</option>';
        }
      }
    }
  }
}
document.addEventListener('DOMContentLoaded', function(){
  if (window.tinymce && document.getElementById('description')) {
    try { if (tinymce.get('description')) tinymce.get('description').remove(); } catch(_) {}
    tinymce.init({
      selector:'#description', height: 400, menubar:true,
      plugins:'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
      toolbar:'undo redo | styles | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code',
      branding:false,
      browser_spellcheck: true,
      gecko_spellcheck: true,
      elementpath: false
    });
  } else {
    console.warn('TinyMCE not loaded or #description missing');
  }
});
</script>
@endpush
