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
        <input type="text"
               class="form-control form-control-sm mb-2"
               placeholder="Search categories..."
               x-model="categorySearch"
               @input="filterCategories()"
               autocomplete="off"
               aria-label="Search categories">
        <select name="category_id" id="category_id"
                class="form-select @error('category_id') is-invalid @enderror"
                x-model="categoryId"
                required>
          <option value="">Choose category</option>
          <template x-for="cat in catsFiltered" :key="cat.id">
            <option :value="cat.id" x-text="cat.indented"
                    :selected="String(cat.id) === '{{ old('category_id') }}'"></option>
          </template>
        </select>
        <div x-show="!loading && categorySearch && !catsFiltered.length" class="form-text text-muted">
          No categories match your search.
        </div>
        <div x-show="loading" class="form-text text-muted">Loading categories...</div>
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
          <label class="form-label fw-semibold"
                 x-text="type==='service' ? 'Priced From ({{ get_currency() }})' : 'Price ({{ get_currency() }})'">
            Price ({{ get_currency() }})
          </label>
          <input type="number" name="price" step="0.01" min="0"
                 class="form-control @error('price') is-invalid @enderror"
                 x-bind:placeholder="type==='service' ? 'Enter starting price' : ''"
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
          <label class="form-label fw-semibold"
                 x-text="type==='service' ? 'Service Area' : 'Country of Origin'">Country of Origin</label>
          <select name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
            <option value="">Choose a country</option>
            @foreach($countries as $c)
              <option value="{{ $c->id }}" @selected(old('country_id')==$c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
          @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <template x-if="type!=='service'">
            <div>
              <label class="form-label fw-semibold">Origin Postal Code</label>
              <input type="text" name="origin_postal_code"
                     class="form-control @error('origin_postal_code') is-invalid @enderror"
                     value="{{ old('origin_postal_code') }}" placeholder="e.g. 90210">
              @error('origin_postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </template>
          <template x-if="type==='service'">
            <div>
              <label class="form-label fw-semibold">State(s)/Region(s)</label>
              <div class="form-control d-flex flex-wrap gap-2 py-2">
                <template x-for="(tag,i) in serviceRegions" :key="i">
                  <span class="badge bg-primary bg-opacity-10 text-primary">
                    <span x-text="tag"></span>
                    <button type="button" class="btn-close btn-close-white ms-1" aria-label="Remove"
                            style="filter: invert(1); opacity:.6" @click="serviceRegions.splice(i,1)"></button>
                  </span>
                </template>
                <input type="text" class="border-0 flex-grow-1" placeholder="Type a region and press Enter"
                       x-model="serviceRegionInput"
                       @keydown.enter.prevent="addServiceRegion()"
                       @keydown.",".prevent="addServiceRegion()">
              </div>
              <input type="hidden" name="origin_postal_code" :value="serviceRegions.join(',')">
              <div class="form-text">Add multiple regions; press Enter after each.</div>
            </div>
          </template>
        </div>
      </div>

      {{-- 8) Processing Time --}}
      <div class="mb-4">
        <label class="form-label fw-semibold"
               x-text="type==='service' ? 'Response Time (optional)' : 'Processing Time'">Processing Time</label>
        <select name="processing_time_id" class="form-select @error('processing_time_id') is-invalid @enderror">
          <option value="" x-text="type==='service' ? 'Choose a Response time' : 'Choose a processing time'">Choose a processing time</option>
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
  window.__ALL_CATEGORIES__ = @json($categories);


  function listingForm() {
    return {
      type: @json(old('type','physical')),
      categoryId: @json(old('category_id','')),
      categories: [],
      catsFlat: [],
      catsFiltered: [],
      categorySearch: '',
      loading: false,
      fallback: false,
      // service regions tags input state
      serviceRegions: ((@json(old('origin_postal_code','')) || '').split(',').map(s=>s.trim()).filter(Boolean)),
      serviceRegionInput: '',
      variations: [], variationType:'', variationOption:'',
      previews: [], idCounter:0, sortable:null,

      filterLocalByType(tp){
        const map = { physical: 'products', service: 'services', digital: 'digital' };
        const want = map[String(tp) || ''] || null;
        if (!want) return [];
        const all = Array.isArray(window.__ALL_CATEGORIES__) ? window.__ALL_CATEGORIES__ : [];
        return all.filter(c => (c.listing_type || '').toLowerCase() === want);
      },
      addServiceRegion(){
        const t = (this.serviceRegionInput || '').trim();
        if(!t) return; if(this.serviceRegions.includes(t)) { this.serviceRegionInput=''; return; }
        this.serviceRegions.push(t); this.serviceRegionInput='';
      },
      async loadCategories() {
        this.categories = [];
        this.categoryId = '';
        this.categorySearch = '';
        this.catsFlat = [];
        this.catsFiltered = [];
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
          this.catsFlat = this.flattenWithIndent(this.categories);
          this.filterCategories();
        } catch (e) {
          console.warn('Categories load warning:', e);
          this.categories = this.filterLocalByType(this.type);
          this.catsFlat = this.flattenWithIndent(this.categories);
          this.filterCategories();
          this.fallback = false;
        } finally {
          this.loading = false;
          if ((@json(old('type')) || '') === this.type && (@json(old('category_id')) || '')) {
            this.categoryId = @json(old('category_id'));
          }
        }
      },

      // Build a tree and return a flattened list with indentation
      flattenWithIndent(items){
        const byId = new Map();
        const roots = [];
        (items || []).forEach(it => { byId.set(String(it.id), { ...it, children: [] }); });
        byId.forEach(node => {
          const pid = node.parent_id ? String(node.parent_id) : '';
          if (pid && byId.has(pid)) byId.get(pid).children.push(node); else roots.push(node);
        });
        const out = [];
        const walk = (node, depth) => {
          const label = String(node.name || '');
          const prefix = depth > 0 ? ('\u2014 '.repeat(depth)) : '';
          out.push({
            id: node.id,
            indented: prefix + label,
            name: label,
            searchable: label.toLowerCase()
          });
          if (node.children && node.children.length) {
            node.children.sort((a,b)=> String(a.name||'').localeCompare(String(b.name||'')));
            node.children.forEach(ch => walk(ch, depth+1));
          }
        };
        roots.sort((a,b)=> String(a.name||'').localeCompare(String(b.name||'')));
        roots.forEach(r => walk(r, 0));
        return out;
      },

      filterCategories() {
        const term = (this.categorySearch || '').trim().toLowerCase();
        const base = term
          ? this.catsFlat.filter(cat => cat.searchable.includes(term))
          : this.catsFlat.slice();
        const selectedId = this.categoryId ? String(this.categoryId) : null;
        if (selectedId && !base.some(cat => String(cat.id) === selectedId)) {
          const selectedCat = this.catsFlat.find(cat => String(cat.id) === selectedId);
          if (selectedCat) base.unshift(selectedCat);
        }
        this.catsFiltered = base;
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

