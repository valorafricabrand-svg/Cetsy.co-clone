{{-- resources/views/products/create.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Create New Listing')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Create New Listing</h1>
              <p class="mt-1 text-sm text-slate-500">Add a new physical product, digital item, or service to your shop.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">
              <i class="fas fa-arrow-left mr-2"></i> Back to Listings
            </a>
          </div>
        </div>

        @php
          $flashStyles = [
              'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
              'info' => 'border-sky-200 bg-sky-50 text-sky-800',
              'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
              'danger' => 'border-rose-200 bg-rose-50 text-rose-800',
          ];
        @endphp
        @foreach (['success','info','warning','danger'] as $msg)
          @if(session()->has($msg))
            <div class="rounded-xl border px-4 py-3 text-sm {{ $flashStyles[$msg] }}">{{ session($msg) }}</div>
          @endif
        @endforeach

        @if($errors->any())
          <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <strong>Please fix:</strong>
            <ul class="mt-2 list-disc space-y-1 pl-5">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" x-data="listingForm()" x-init="init()">
          <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="$el.submit()">
            @csrf

            <div class="mb-4">
              <label class="mb-1 block text-sm font-semibold text-slate-700">Listing Name</label>
              <input type="text" name="name" id="name" spellcheck="true" autocapitalize="sentences" autocomplete="on"
                     class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                     value="{{ old('name') }}" placeholder="e.g. Handmade Wooden Spoon" required>
              @error('name')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
              <label class="mb-1 block text-sm font-semibold text-slate-700">Product Type</label>
              <select name="type" id="type"
                      class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                      x-model="type" @change="loadCategories()" required>
                <option value="">Select type</option>
                <option value="physical" @selected(old('type')=='physical')>Physical</option>
                <option value="service"  @selected(old('type')=='service')>Service</option>
                <option value="digital"  @selected(old('type')=='digital')>Digital</option>
              </select>
              @error('type')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
              <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
              <div class="relative">
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
                     class="absolute z-50 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
                     x-cloak
                     x-show="showCatSuggestions"
                     x-transition
                     @mousedown.prevent>
                  <template x-if="loading"><div class="block rounded-lg px-3 py-2 text-sm text-slate-500">Loading categories...</div></template>
                  <template x-if="!loading && !catsFiltered.length"><div class="block rounded-lg px-3 py-2 text-sm text-slate-500">No categories match your search.</div></template>
                  <template x-for="(cat, idx) in catsFiltered" :key="cat.id">
                    <button type="button" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" :class="{ 'bg-emerald-50 text-emerald-700': idx === catHighlightIndex }" @click="selectCategory(cat)">
                      <span x-text="cat.indented"></span>
                    </button>
                  </template>
                </div>
              </div>
              <div class="mt-1 text-xs text-slate-500" x-show="categoryId && !showCatSuggestions">Selected: <span class="font-semibold" x-text="currentCategoryLabel()"></span></div>
              <div x-show="loading" class="mt-1 text-xs text-slate-500">Loading categories...</div>
              <div x-show="!loading && categorySearch && !catsFiltered.length" class="mt-1 text-xs text-slate-500" x-cloak>No categories match your search.</div>
              <div x-show="fallback && !loading" class="mt-1 text-xs text-amber-600">Showing all categories. Ask admin to tag categories by listing type for better filtering.</div>
              @error('category_id')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
              <label for="description" class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
              <textarea id="description" name="description" rows="6" spellcheck="true" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('description') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">{{ old('description') }}</textarea>
              @error('description')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4 grid grid-cols-12 gap-3">
              <div class="col-span-12 md:col-span-6">
                <label class="mb-1 block text-sm font-semibold text-slate-700" x-text="type==='service' ? 'Priced From ({{ get_currency() }})' : 'Price ({{ get_currency() }})'">Price ({{ get_currency() }})</label>
                <input type="number" name="price" step="0.01" min="0" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('price') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" x-bind:placeholder="type==='service' ? 'Enter starting price' : ''" value="{{ old('price') }}" required>
                @error('price')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
              </div>
              <div class="col-span-12 md:col-span-6">
                <label class="mb-1 block text-sm font-semibold text-slate-700">% Discount <small class="text-slate-500">(optional)</small></label>
                <input type="number" name="discount_percent" step="1" min="0" max="100" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('discount_percent') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" value="{{ old('discount_percent') }}" placeholder="Enter discount percentage">
                @error('discount_percent')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="mb-4 grid grid-cols-12 gap-3">
              <div class="col-span-12 md:col-span-6">
                <label class="mb-1 block text-sm font-semibold text-slate-700" x-text="type==='service' ? 'Service Area' : 'Country of Origin'">Country of Origin</label>
                <select name="country_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('country_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
                  <option value="">Choose a country</option>
                  @foreach($countries as $c)
                    <option value="{{ $c->id }}" @selected(old('country_id')==$c->id)>{{ $c->name }}</option>
                  @endforeach
                </select>
                @error('country_id')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
              </div>
              <div class="col-span-12 md:col-span-6">
                <template x-if="type!=='service'">
                  <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Origin Postal Code</label>
                    <input type="text" name="origin_postal_code" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('origin_postal_code') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" value="{{ old('origin_postal_code') }}" placeholder="e.g. 90210">
                    @error('origin_postal_code')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
                  </div>
                </template>
                <template x-if="type==='service'">
                  <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">State(s)/Region(s)</label>
                    <div class="flex w-full flex-wrap gap-2 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                      <template x-for="(tag,i) in serviceRegions" :key="i">
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                          <span x-text="tag"></span>
                          <button type="button" class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full text-emerald-700 hover:bg-emerald-200" aria-label="Remove" @click="serviceRegions.splice(i,1)">&times;</button>
                        </span>
                      </template>
                      <input type="text" class="min-w-[10rem] flex-1 border-0 bg-transparent p-0 text-sm text-slate-700 focus:outline-none" placeholder="Type a region and press Enter" x-model="serviceRegionInput" @keydown.enter.prevent="addServiceRegion()" @keydown.comma.prevent="addServiceRegion()">
                    </div>
                    <input type="hidden" name="origin_postal_code" :value="serviceRegions.join(',')">
                    <div class="mt-1 text-xs text-slate-500">Add multiple regions; press Enter after each.</div>
                  </div>
                </template>
              </div>
            </div>

            <template x-if="type !== 'digital'">
              <div class="mb-4">
                <label class="mb-1 block text-sm font-semibold text-slate-700" x-text="type==='service' ? 'Response Time (optional)' : 'Processing Time'">Processing Time</label>
                <select name="processing_time_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('processing_time_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                  <option value="" x-text="type==='service' ? 'Choose a response time' : 'Choose a processing time'">Choose a processing time</option>
                  @foreach($processingTimes as $pt)
                    <option value="{{ $pt->id }}" @selected(old('processing_time_id')==$pt->id)>{{ $pt->name }} ({{ $pt->days }} days)</option>
                  @endforeach
                </select>
                @error('processing_time_id')<div class="mt-1 text-xs text-rose-600">{{ $message }}</div>@enderror
              </div>
            </template>

            <div>
              <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-base font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">
                <i class="fas fa-check-circle mr-2"></i> Continue
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
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
  height: 260,
  min_height: 220,
  menubar: true,

  /* Enable extra plugins for styling */
  plugins: [
    'advlist autolink lists link image charmap preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table paste code help wordcount',
    'quickbars emoticons autoresize'
  ],

  /* Add font-size, font-family, line-height and Format Painter to toolbar */
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
  autoresize_bottom_margin: 16,
  content_style: 'body { min-height:220px !important; }',
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
        height: 260,
        min_height: 220,
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
        autoresize_bottom_margin: 16,
        content_style: 'body { min-height:220px !important; }',
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



