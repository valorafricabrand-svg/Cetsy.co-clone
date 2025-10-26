{{-- resources/views/products/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Create New Listing</h2>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back to Listings
    </a>
  </div>

  {{-- Flash & Validation --}}
  @foreach (['success','info','warning','danger'] as $msg)
    @if(session()->has($msg))
      <div class="alert alert-{{ $msg }} alert-dismissible fade show">
        {{ session($msg) }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach
  @if($errors->any())
    <div class="alert alert-danger"><strong>Please fix:</strong>
      <ul class="mt-2 mb-0 ps-3">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card shadow-sm rounded-4 p-4" x-data="listingForm()" x-init="init()">
    <form action="{{ route('products.store') }}"
          method="POST"
          enctype="multipart/form-data"
          @submit.prevent="$el.submit()">
      @csrf

      {{-- 1) Listing Name --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">Listing Name</label>
        <input type="text" name="name" id="name" spellcheck="true" autocapitalize="sentences" autocomplete="on"
               class="form-control form-control-lg @error('name') is-invalid @enderror"
               value="{{ old('name') }}"
               placeholder="e.g. Handmade Wooden Spoon" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      {{-- 2) Product Type --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">Product Type</label>
        <select name="type" id="type"
                class="form-select @error('type') is-invalid @enderror"
                x-model="type"
                @change="loadCategories()"
                required>
          <option value="">Select type</option>
          <option value="physical" @selected(old('type')=='physical')>Physical</option>
          <option value="service"  @selected(old('type')=='service')>Service</option>
          <option value="digital"  @selected(old('type')=='digital')>Digital</option>
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      {{-- 3) Category --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">Category</label>
        <select name="category_id" id="category_id"
                class="form-select @error('category_id') is-invalid @enderror"
                x-model="categoryId"
                required>
          <option value="">Choose category</option>
          <template x-for="cat in categories" :key="cat.id">
            <option :value="cat.id" x-text="cat.name"
                    :selected="String(cat.id) === '{{ old('category_id') }}'">
            </option>
          </template>
        </select>
        <div x-show="loading" class="form-text text-muted">Loading categories…</div>
        <div x-show="fallback && !loading" class="form-text text-warning">
          Showing all categories. Ask admin to tag categories by listing type for better filtering.
        </div>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

  {{-- Description --}}
          <div class="col-12">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="6" spellcheck="true"
                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')<div class="text-danger mt-1">{{ $message }}</div>@enderror
          </div>

      {{-- 5) Price & Discount --}}
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Price ({{ get_currency() }})</label>
          <input type="number" name="price" step="0.01" min="0"
                 class="form-control @error('price') is-invalid @enderror"
                 value="{{ old('price') }}" required>
          @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
     <div class="col-md-6">
  <label class="form-label fw-semibold">% Discount <small class="text-muted">(optional)</small></label>
  <input
    type="number"
    name="discount_percent"
    step="1"
    min="0"
    max="100"
    class="form-control @error('discount_percent') is-invalid @enderror"
    value="{{ old('discount_percent') }}"
    placeholder="Enter discount percentage"
  >
  @error('discount_percent')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

      </div>



      {{-- 7) Country & Postal Code --}}
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Country of Origin</label>
          <select name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
            <option value="">Choose a country</option>
            @foreach($countries as $c)
              <option value="{{ $c->id }}" @selected(old('country_id')==$c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
          @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Origin Postal Code</label>
          <input type="text" name="origin_postal_code"
                 class="form-control @error('origin_postal_code') is-invalid @enderror"
                 value="{{ old('origin_postal_code') }}" placeholder="e.g. 90210">
          @error('origin_postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      {{-- 8) Processing Time --}}
      <div class="mb-4">
        <label class="form-label fw-semibold">Processing Time</label>
        <select name="processing_time_id" class="form-select @error('processing_time_id') is-invalid @enderror">
          <option value="">Choose a processing time</option>
          @foreach($processingTimes as $pt)
            <option value="{{ $pt->id }}" @selected(old('processing_time_id')==$pt->id)>
              {{ $pt->name }} ({{ $pt->days }} days)
            </option>
          @endforeach
        </select>
        @error('processing_time_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>



  
 

      {{-- Submit --}}
      <div class="d-grid">
        <button type="submit" class="btn btn-success btn-lg rounded-pill">
          <i class="fas fa-check-circle me-2"></i> Continue
        </button>
      </div>
    </form>
  </div>
</div>

@include('shipping_profiles._create_modal', ['countries'=>$countries])
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
  // Local cache of categories (id, name, listing_type) as a robust fallback
  window.__ALL_CATEGORIES__ = @json(\App\Models\Category::select('id','name','listing_type')->orderBy('name')->get());


  function listingForm() {
    return {
      type: '{{ old('type','physical') }}',
      categoryId: '{{ old('category_id','') }}',
      categories: [],
      loading: false,
      fallback: false,
      variations: [], variationType:'', variationOption:'',
      previews: [], idCounter:0, sortable:null,

      filterLocalByType(tp){
        const map = { physical: 'products', service: 'services', digital: 'digital' };
        const want = map[String(tp) || ''] || null;
        if (!want) return [];
        const all = Array.isArray(window.__ALL_CATEGORIES__) ? window.__ALL_CATEGORIES__ : [];
        return all.filter(c => (c.listing_type || '').toLowerCase() === want);
      },
      async loadCategories() {
        this.categories = [];
        this.categoryId = '';
        if (! this.type) return;
        this.loading = true;
        try {
          const url = `/api/categories/by-type/${encodeURIComponent(this.type)}?_=${Date.now()}`;
          const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
          if (!res.ok) throw new Error(`Fetch failed (${res.status})`);
          let data;
          const ct = res.headers.get('content-type') || '';
          this.fallback = (res.headers.get('x-categories-fallback') === '1');
          if (ct.includes('application/json')) {
            data = await res.json();
          } else {
            const text = await res.text();
            try { data = JSON.parse(text); } catch { console.warn('Non-JSON response from categories API:', text.slice(0, 200)); data = []; }
          }
          this.categories = Array.isArray(data) ? data : [];
          if (this.fallback || this.categories.length === 0) {
            this.categories = this.filterLocalByType(this.type);
          }
        } catch (e) {
          console.warn('Categories load warning:', e);
          this.categories = this.filterLocalByType(this.type);
          this.fallback = false;
        } finally {
          this.loading = false;
          if ('{{ old('type') }}' === this.type && '{{ old('category_id') }}') {
            this.categoryId = '{{ old('category_id') }}';
          }
        }
      },

      addManualVariation() {
        const key = Date.now();
        this.variations.push({ key, type:this.variationType.trim(), option:this.variationOption.trim() });
        this.variationType=''; this.variationOption='';
      },

      handleFiles(files) { Array.from(files).forEach(f=> this.previewFile(f)); },
      previewFile(file) {
        const reader = new FileReader();
        reader.onload = e => {
          this.previews.push({ id:this.idCounter++, url:e.target.result, fileObject:file });
          this.$nextTick(()=> this.initSortable());
        };
        reader.readAsDataURL(file);
      },
      removeFile(i){ this.previews.splice(i,1); },
      handleDrop(e){ if(e.dataTransfer.files.length) this.handleFiles(e.dataTransfer.files); },
      initSortable(){
        if (this.sortable) this.sortable.destroy();
        this.sortable = Sortable.create(document.getElementById('previewList'), {
          animation:150, onEnd:evt=>{
            let m=this.previews.splice(evt.oldIndex,1)[0];
            this.previews.splice(evt.newIndex,0,m);
          }
        });
      },

      init() {
        if (this.type) this.loadCategories();
      }
    }
  }
</script>

    <!-- Include TinyMCE from the local directory -->
    <script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
if (document.compatMode !== 'CSS1Compat') {
  console.warn('TinyMCE disabled: document not in standards mode');
} else {
if (window.tinymce) tinymce.init({
  selector: '#description',
  height: 400,
  min_height: 400,
  menubar: true,

  /* Enable extra plugins for styling */
  plugins: [
    'advlist autolink lists link image charmap preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table paste code help wordcount',
    'quickbars emoticons autoresize'
  ],

  /* Add font‑size, font‑family, line‑height and Format Painter to toolbar */
  toolbar: [
    'undo redo | fontselect fontsizeselect |', 
    'bold italic underline strikethrough forecolor backcolor |',
    'alignleft aligncenter alignright alignjustify |',
    'bullist numlist outdent indent | removeformat | link image media | code'
  ].join(' '),

  /* Define your available fonts and sizes */
  font_formats: [
    'Arial=arial,helvetica,sans-serif;', 
    'Courier New=courier new,courier,monospace;', 
    'Georgia=georgia,palatino,serif;', 
    'Tahoma=tahoma,arial,helvetica,sans-serif;', 
    'Times New Roman=times new roman,times,serif;', 
    'Verdana=verdana,geneva,sans-serif'
  ].join(' '),

  fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',

  /* Let browser context menu & keyboard shortcuts continue to work */
  browser_contextmenu: true,
  browser_spellcheck: true,
  gecko_spellcheck: true,
  elementpath: false,
  branding: false,
  content_style: 'body { min-height:400px !important; }',
  base_url: '{{ asset('assets/js/tinymce') }}',

  setup(editor) {
    editor.on('change', () => editor.save());
  }
});
}
</script>
<script>
// Fallback loader: if TinyMCE failed to load locally, load from CDN and init
;(function(){
  function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
  onReady(function(){
    if (window.tinymce) return; // already available
    const el = document.getElementById('description');
    if (!el) return;
    const start = function(){
      try { const inst = tinymce.get('description'); if (inst) inst.remove(); } catch(_) {}
      tinymce.init({
        selector: '#description',
        height: 400,
        min_height: 400,
        menubar: true,
        plugins: [
          'advlist autolink lists link image charmap preview anchor',
          'searchreplace visualblocks code fullscreen',
          'insertdatetime media table paste code help wordcount',
          'quickbars emoticons autoresize'
        ],
        toolbar: [
          'undo redo | fontselect fontsizeselect |', 
          'bold italic underline strikethrough forecolor backcolor |',
          'alignleft aligncenter alignright alignjustify |',
          'bullist numlist outdent indent | removeformat | link image media | code'
        ].join(' '),
        font_formats: [
          'Arial=arial,helvetica,sans-serif;', 
          'Courier New=courier new,courier,monospace;', 
          'Georgia=georgia,palatino,serif;', 
          'Tahoma=tahoma,arial,helvetica,sans-serif;', 
          'Times New Roman=times new roman,times,serif;', 
          'Verdana=verdana,geneva,sans-serif'
        ].join(' '),
        fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
        browser_contextmenu: true,
        browser_spellcheck: true,
        gecko_spellcheck: true,
        elementpath: false,
        branding: false,
        content_style: 'body { min-height:400px !important; }',
        base_url: '{{ asset('assets/js/tinymce') }}',
        setup(editor) {
          editor.on('change', () => editor.save());
        }
      });
    };
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js';
    s.referrerPolicy = 'origin';
    s.onload = start;
    s.onerror = function(){ console.warn('TinyMCE CDN failed to load'); };
    document.head.appendChild(s);
  });
})();
</script>
@endpush
