{{-- resources/views/products/create.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('main')
<div class="content">
  <div class="flex justify-between items-center mb-4">
    <h2 class="mb-0">Create New Listing</h2>
    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">
      <i class="fas fa-arrow-left mr-1"></i> Back to Listings
    </a>
  </div>

  {{-- Flash & Validation --}}
  @foreach (['success','info','warning','danger'] as $msg)
    @if(session()->has($msg))
      <div class="alert alert-{{ $msg }} alert-dismissible fade show">
        {{ session($msg) }}
        <button class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach
  @if($errors->any())
    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800"><strong>Please fix:</strong>
      <ul class="mt-2 mb-0 pl-3">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm rounded-4 p-4" x-data="listingForm()" x-init="init()">
    <form action="{{ route('products.store') }}"
          method="POST"
          enctype="multipart/form-data"
          @submit.prevent="$el.submit()">
      @csrf

      {{-- 1) Listing Name --}}
      <div class="mb-3">
        <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Listing Name</label>
        <input type="text" name="name" id="name" spellcheck="true" autocapitalize="sentences" autocomplete="on"
               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 form-control-lg @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
               value="{{ old('name') }}"
               placeholder="e.g. Handmade Wooden Spoon" required>
        @error('name')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
      </div>

      {{-- 2) Product Type --}}
      <div class="mb-3">
        <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Product Type</label>
        <select name="type" id="type"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                x-model="type"
                @change="loadCategories()"
                required>
          <option value="">Select type</option>
          <option value="physical" @selected(old('type')=='physical')>Physical</option>
          <option value="service"  @selected(old('type')=='service')>Service</option>
          <option value="digital"  @selected(old('type')=='digital')>Digital</option>
        </select>
        @error('type')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
      </div>

      {{-- 3) Category --}}
      <div class="mb-3">
        <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Category</label>
        <div class="position-relative">
          <input type="text"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('category_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                 placeholder="Search categories..."
                 x-model="categorySearch"
                 @input="handleCategoryInput()"
                 @focus="openCategorySuggestions()"
                 @keydown.arrow-down.prevent="moveCategoryHighlight(1)"
                 @keydown.arrow-up.prevent="moveCategoryHighlight(-1)"
                 @keydown.enter.prevent="selectHighlightedCategory()"
                 @keydown.escape.prevent="closeCategorySuggestions()"
                 @blur="handleCategoryBlur()"
                 autocomplete="off"
                 role="combobox"
                 aria-autocomplete="list"
                 :aria-expanded="showCatSuggestions ? 'true' : 'false'"
                 aria-controls="category-suggestion-list"
                 aria-label="Search categories">
          <input type="hidden" name="category_id" :value="categoryId || ''">
          <div id="category-suggestion-list"
               class="absolute z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl w-full shadow-sm mt-1"
               :class="{ 'show': showCatSuggestions }"
               style="max-height: 16rem; overflow-y: auto;"
               x-cloak
               x-show="showCatSuggestions"
               x-transition
               @mousedown.prevent>
            <template x-if="loading">
              <div class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 text-slate-500">Loading categories...</div>
            </template>
            <template x-if="!loading && !catsFiltered.length">
              <div class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 text-slate-500">No categories match your search.</div>
            </template>
            <template x-for="(cat, idx) in catsFiltered" :key="cat.id">
              <button type="button"
                      class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 text-truncate"
                      :class="{ 'active': idx === catHighlightIndex }"
                      @click="selectCategory(cat)">
                <span x-text="cat.indented"></span>
              </button>
            </template>
          </div>
        </div>
        <div class="mt-1 text-xs text-slate-500 text-slate-500 mt-1" x-show="categoryId && !showCatSuggestions">
          Selected: <span class="font-semibold" x-text="currentCategoryLabel()"></span>
        </div>
        <div x-show="loading" class="mt-1 text-xs text-slate-500 text-slate-500 mt-1">Loading categories...</div>
        <div x-show="!loading && categorySearch && !catsFiltered.length" class="mt-1 text-xs text-slate-500 text-slate-500 mt-1" x-cloak>
          No categories match your search.
        </div>
        <div x-show="fallback && !loading" class="mt-1 text-xs text-slate-500 text-amber-600">
          Showing all categories. Ask admin to tag categories by listing type for better filtering.
        </div>
        @error('category_id')<div class="mt-1 text-xs text-rose-600 block">{{ $message }}</div>@enderror
      </div>

  {{-- Description --}}
          <div class="col-span-12">
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Description</label>
            <textarea id="description" name="description" rows="6" spellcheck="true"
                      class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('description') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">{{ old('description') }}</textarea>
            @error('description')<div class="text-rose-600 mt-1">{{ $message }}</div>@enderror
          </div>

      {{-- 5) Price & Discount --}}
      <div class="grid grid-cols-12 gap-4 gap-3 mb-4">
        <div class="md:col-span-6">
          <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold"
                 x-text="type==='service' ? 'Priced From ({{ get_currency() }})' : 'Price ({{ get_currency() }})'">
            Price ({{ get_currency() }})
          </label>
          <input type="number" name="price" step="0.01" min="0"
                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('price') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                 x-bind:placeholder="type==='service' ? 'Enter starting price' : ''"
                 value="{{ old('price') }}" required>
          @error('price')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
        </div>
     <div class="md:col-span-6">
  <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">% Discount <small class="text-slate-500">(optional)</small></label>
  <input
    type="number"
    name="discount_percent"
    step="1"
    min="0"
    max="100"
    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('discount_percent') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
    value="{{ old('discount_percent') }}"
    placeholder="Enter discount percentage"
  >
  @error('discount_percent')
    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
  @enderror
</div>

      </div>



      {{-- 7) Country & Postal Code --}}
      <div class="grid grid-cols-12 gap-4 gap-3 mb-4">
        <div class="md:col-span-6">
          <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold"
                 x-text="type==='service' ? 'Service Area' : 'Country of Origin'">Country of Origin</label>
          <select name="country_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('country_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
            <option value="">Choose a country</option>
            @foreach($countries as $c)
              <option value="{{ $c->id }}" @selected(old('country_id')==$c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
          @error('country_id')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
        </div>
        <div class="md:col-span-6">
          <template x-if="type!=='service'">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Origin Postal Code</label>
              <input type="text" name="origin_postal_code"
                     class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('origin_postal_code') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                     value="{{ old('origin_postal_code') }}" placeholder="e.g. 90210">
              @error('origin_postal_code')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
            </div>
          </template>
          <template x-if="type==='service'">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">State(s)/Region(s)</label>
              <div class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 flex flex-wrap gap-2 py-2">
                <template x-for="(tag,i) in serviceRegions" :key="i">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-primary bg-opacity-10 text-primary">
                    <span x-text="tag"></span>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 text-white hover:bg-white/20 hover:text-white ml-1" aria-label="Remove"
                            style="filter: invert(1); opacity:.6" @click="serviceRegions.splice(i,1)"></button>
                  </span>
                </template>
                <input type="text" class="border-0 flex-grow-1" placeholder="Type a region and press Enter"
                       x-model="serviceRegionInput"
                       @keydown.enter.prevent="addServiceRegion()"
                       @keydown.",".prevent="addServiceRegion()">
              </div>
              <input type="hidden" name="origin_postal_code" :value="serviceRegions.join(',')">
              <div class="mt-1 text-xs text-slate-500">Add multiple regions; press Enter after each.</div>
            </div>
          </template>
        </div>
      </div>

      {{-- 8) Processing Time --}}
      <div class="mb-4">
        <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold"
               x-text="type==='service' ? 'Response Time (optional)' : 'Processing Time'">Processing Time</label>
        <select name="processing_time_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_time_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
          <option value="" x-text="type==='service' ? 'Choose a Response time' : 'Choose a processing time'">Choose a processing time</option>
          @foreach($processingTimes as $pt)
            <option value="{{ $pt->id }}" @selected(old('processing_time_id')==$pt->id)>
              {{ $pt->name }} ({{ $pt->days }} days)
            </option>
          @endforeach
        </select>
        @error('processing_time_id')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
      </div>



  
 

      {{-- Submit --}}
      <div class="d-grid">
        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-5 py-2.5 text-base rounded-full">
          <i class="fas fa-check-circle mr-2"></i> Continue
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
      showCatSuggestions: false,
      catHighlightIndex: -1,
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
        this.showCatSuggestions = false;
        this.catHighlightIndex = -1;
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
            this.syncCategoryInputFromSelection();
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
        if (!this.catsFiltered.length) {
          this.catHighlightIndex = -1;
        } else if (this.catHighlightIndex >= this.catsFiltered.length) {
          this.catHighlightIndex = this.catsFiltered.length - 1;
        }
      },

      openCategorySuggestions() {
        if (this.loading) return;
        this.showCatSuggestions = true;
        if (!this.catsFiltered.length) this.filterCategories();
        if (this.catHighlightIndex === -1 && this.catsFiltered.length) {
          this.catHighlightIndex = 0;
        }
      },
      closeCategorySuggestions() {
        this.showCatSuggestions = false;
        this.catHighlightIndex = -1;
      },
      handleCategoryBlur() {
        setTimeout(() => this.closeCategorySuggestions(), 120);
      },
      handleCategoryInput() {
        this.categoryId = null;
        this.filterCategories();
        this.catHighlightIndex = this.catsFiltered.length ? 0 : -1;
        this.openCategorySuggestions();
      },
      moveCategoryHighlight(step) {
        if (!this.catsFiltered.length) return;
        if (!this.showCatSuggestions) this.openCategorySuggestions();
        if (this.catHighlightIndex === -1) {
          this.catHighlightIndex = step > 0 ? 0 : this.catsFiltered.length - 1;
          return;
        }
        const max = this.catsFiltered.length;
        this.catHighlightIndex = (this.catHighlightIndex + step + max) % max;
      },
      selectHighlightedCategory() {
        if (!this.catsFiltered.length) return;
        const idx = this.catHighlightIndex >= 0 ? this.catHighlightIndex : 0;
        const cat = this.catsFiltered[idx];
        if (cat) this.selectCategory(cat);
      },
      selectCategory(cat) {
        if (!cat) {
          this.categoryId = null;
          this.categorySearch = '';
        } else {
          this.categoryId = cat.id;
          this.categorySearch = cat.name;
        }
        this.closeCategorySuggestions();
      },
      currentCategoryLabel() {
        const selected = this.catsFlat.find(cat => String(cat.id) === String(this.categoryId));
        return selected ? selected.name : '';
      },
      syncCategoryInputFromSelection() {
        const label = this.currentCategoryLabel();
        this.categorySearch = label || '';
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

  /* Add fontâ€‘size, fontâ€‘family, lineâ€‘height and Format Painter to toolbar */
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



