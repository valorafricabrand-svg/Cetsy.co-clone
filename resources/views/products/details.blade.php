@extends('theme.'.theme().'.layouts.app')
@section('title', $product->name . ' | Edit Details')

@section('main')
@php $current = \Illuminate\Support\Facades\Route::currentRouteName(); @endphp

<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6" x-data="detailsForm()" x-init="init()">
        @include('products.partials.edit-tabs', ['product' => $product, 'current' => $current])

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 mt-3">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 pl-3">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
  @endif

  <div class="flex justify-between items-center mt-3 mb-3">
    <h2 class="mb-0">{{ $product->name }} - Edit Details</h2>
    <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-900 text-slate-900 hover:bg-slate-100 px-3 py-1.5 text-xs"><i class="fas fa-arrow-left mr-1"></i>Back</a>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="p-4 sm:p-5">
      <form action="{{ route('products.details.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PATCH')

        <div class="grid grid-cols-12 gap-4 gap-3">
          <div class="col-span-12 md:col-span-8">
            <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Listing Name</label>
            <input type="text" name="name" id="name" spellcheck="true" autocapitalize="sentences" autocomplete="on"
                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   value="{{ old('name', $product->name) }}" required autofocus>
            @error('name') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>

          <div class="col-span-12 md:col-span-4">
            <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Listing Type</label>
            <select name="type" x-model="type" @change="loadCategories()"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
              <option value="">Choose type</option>
              <option value="physical" @selected(old('type',$product->type)=='physical')>Physical</option>
              <option value="digital"  @selected(old('type',$product->type)=='digital')>Digital</option>
              <option value="service"  @selected(old('type',$product->type)=='service')>Service</option>
            </select>
            @error('type') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>

          <div class="col-span-12 md:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Category</label>
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
                     aria-controls="details-category-suggestion-list"
                     aria-label="Search categories">
              <input type="hidden" name="category_id" :value="categoryId || ''">
              <div id="details-category-suggestion-list"
                   class="absolute z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
                   
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
                          class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 truncate"
                          :class="{ 'active': idx === catHighlightIndex }"
                          @click="selectCategory(cat)">
                    <span x-text="cat.indented"></span>
                  </button>
                </template>
              </div>
            </div>
            <div class="mt-1 text-xs text-slate-500" x-show="categoryId && !showCatSuggestions">
              Selected: <span class="font-semibold" x-text="currentCategoryLabel()"></span>
            </div>
            <div x-show="loading" class="mt-1 text-xs text-slate-500">Loading categories...</div>
            <div x-show="!loading && categorySearch && !catsFiltered.length" class="mt-1 text-xs text-slate-500" x-cloak>
              No categories match your search.
            </div>
            <div x-show="fallback && !loading" class="mt-1 text-xs text-slate-500 text-amber-600">Showing all categories (fallback). Ask admin to tag categories by type.</div>
            @error('category_id') <div class="mt-1 text-xs text-rose-600 block">{{ $message }}</div> @enderror
          </div>

          

          <div class="col-span-12">
            <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Description</label>
            <textarea id="description" name="description" rows="8" spellcheck="true"
                      class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('description') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                      placeholder="Full product description...">{{ old('description',$product->description ?? '') }}</textarea>
            @error('description') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>

          <div class="col-span-12" x-show="type==='digital'" x-cloak>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="text-sm font-semibold text-slate-900">Digital Delivery</h3>
                  <p class="mt-1 text-xs text-slate-600">Buyers can only download or open what you add here. Upload a real file, or paste a secure external link such as Google Drive.</p>
                </div>
                @if($product->digitalFiles->isEmpty())
                  <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-800">No delivery asset added yet</span>
                @endif
              </div>

              <div class="mt-4 grid gap-3 md:grid-cols-2">
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-300">
                  <input type="radio" name="digital_delivery_method" value="{{ \App\Models\DigitalFile::SOURCE_UPLOAD }}" x-model="digitalDeliveryMethod" class="mt-1 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  <span>
                    <span class="block text-sm font-semibold text-slate-900">Upload file</span>
                    <span class="mt-1 block text-xs text-slate-500">Best for PDFs, ZIPs, audio, or any file you want Cetsy to serve directly.</span>
                  </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-300">
                  <input type="radio" name="digital_delivery_method" value="{{ \App\Models\DigitalFile::SOURCE_EXTERNAL_URL }}" x-model="digitalDeliveryMethod" class="mt-1 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                  <span>
                    <span class="block text-sm font-semibold text-slate-900">External link</span>
                    <span class="mt-1 block text-xs text-slate-500">Use this when the asset lives on Google Drive, Dropbox, Vimeo, or another hosted platform.</span>
                  </span>
                </label>
              </div>

              <div class="mt-4" x-show="digitalDeliveryMethod==='{{ \App\Models\DigitalFile::SOURCE_UPLOAD }}'" x-cloak>
                <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Digital File</label>
                <input type="file" name="digital_file"
                       class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('digital_file') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                       accept=".zip,.pdf,.mp3,.mp4,.doc,.docx,.xlsx,.ppt,.pptx">
                @error('digital_file') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                <div class="mt-1 text-xs text-slate-500">Use this for files you want buyers to download from Cetsy. If your phone shows “memory low” or the file is too large, switch to External link instead.</div>
              </div>

              <div class="mt-4 grid gap-4 md:grid-cols-2" x-show="digitalDeliveryMethod==='{{ \App\Models\DigitalFile::SOURCE_EXTERNAL_URL }}'" x-cloak>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">Link Label</label>
                  <input type="text" name="digital_link_name" value="{{ old('digital_link_name') }}"
                         class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('digital_link_name') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                         placeholder="e.g. Watch video on Google Drive">
                  @error('digital_link_name') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 font-semibold">External Download Link</label>
                  <input type="url" name="digital_link_url" value="{{ old('digital_link_url') }}"
                         class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('digital_link_url') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                         placeholder="https://drive.google.com/...">
                  @error('digital_link_url') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-800">
                Tip: if you paste a Google Drive link, make sure the file or folder is shared so buyers can access it after purchase.
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 flex gap-2">
          <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500"><i class="fas fa-save mr-1"></i> Save</button>
          <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  {{-- Existing Digital Files (if any) --}}
  @if($product->digitalFiles->count())
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mt-4">
      <div class="border-b border-slate-200 px-4 py-3 bg-slate-100"><h5 class="mb-0">Current Delivery Assets</h5></div>
      <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 list-group-flush">
        @foreach($product->digitalFiles as $file)
          <li class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <a href="{{ route('digital-files.download',$file) }}" target="_blank" class="inline-flex max-w-full items-center text-sm font-medium text-slate-800 hover:text-emerald-700">
                <i class="fas {{ $file->isExternalUrl() ? 'fa-link' : 'fa-file-download' }} mr-2"></i>
                <span class="truncate">{{ $file->filename }}</span>
              </a>
              <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span class="inline-flex rounded-full border px-2 py-0.5 {{ $file->isExternalUrl() ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                  {{ $file->isExternalUrl() ? 'External link' : 'Uploaded file' }}
                </span>
                @if($file->isExternalUrl() && $file->external_url)
                  <span class="truncate">{{ preg_replace('/^www\\./i', '', (string) parse_url($file->external_url, PHP_URL_HOST)) }}</span>
                @elseif($file->filesize)
                  <span>{{ number_format($file->filesize / 1024 / 1024, 2) }} MB</span>
                @endif
              </div>
            </div>
            <form action="{{ route('digital-files.destroy',$file) }}" method="POST" onsubmit="return confirm('Delete this delivery asset?')">
              @csrf @method('DELETE')
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50"><i class="fas fa-trash"></i></button>
            </form>
          </li>
        @endforeach
      </ul>
    </div>
  @endif
      </div>
    </div>
  </div>
</section>
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

    type: @json(old('type', $product->type)),

    categoryId: @json(old('category_id', $product->category_id)),

    fallback: false,

    categories: [],

    catsFlat: [],

    catsFiltered: [],

    categorySearch: '',

    loading: false,
    showCatSuggestions: false,
    catHighlightIndex: -1,
    digitalDeliveryMethod: @json(old('digital_delivery_method', \App\Models\DigitalFile::SOURCE_UPLOAD)),

    init(){ if(!this.type && this.categoryId){ const all = Array.isArray(window.__ALL_CATEGORIES__) ? window.__ALL_CATEGORIES__ : []; const cat = all.find(x => String(x.id)===String(this.categoryId)); if (cat && cat.listing_type){ const rev = {products:'physical', services:'service', digital:'digital'}; this.type = rev[String(cat.listing_type)] || this.type; } } this.loadCategories(); },

    filterLocalByType(tp){

      const map = { physical: 'products', service: 'services', digital: 'digital' };

      const want = map[String(tp) || ''] || null;

      if (!want) return [];

      const all = Array.isArray(window.__ALL_CATEGORIES__) ? window.__ALL_CATEGORIES__ : [];

      return all.filter(c => (String(c.listing_type || '').toLowerCase() === want));

    },

    flattenWithIndent(items){

      const byId = new Map();

      const roots = [];

      (items || []).forEach(it => byId.set(String(it.id), { ...it, children: [] }));

      byId.forEach(node => {

        const pid = node.parent_id ? String(node.parent_id) : '';

        if (pid && byId.has(pid)) byId.get(pid).children.push(node); else roots.push(node);

      });

      const out = [];

      const walk = (n,d) => {

        const label = String(n.name || '');

        const p = d>0 ? ('\u2014 '.repeat(d)) : '';

        out.push({ id:n.id, indented: p + label, name: label, searchable: label.toLowerCase() });

        (n.children || []).sort((a,b)=>String(a.name||'').localeCompare(String(b.name||''))).forEach(c=>walk(c,d+1));

      };

      roots.sort((a,b)=>String(a.name||'').localeCompare(String(b.name||''))).forEach(r=>walk(r,0));

      return out;

    },

    async loadCategories(){

      if(!this.type){

        this.categories = [];

        this.catsFlat = [];

        this.catsFiltered = [];

        this.categorySearch = '';

        this.showCatSuggestions = false;

        this.catHighlightIndex = -1;

        this.loading = false;

        return;

      }

      this.loading = true;

      this.categorySearch = '';

      this.showCatSuggestions = false;

      this.catHighlightIndex = -1;

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

        this.categories = Array.isArray(cats) ? cats : [];

        this.catsFlat = this.flattenWithIndent(this.categories);

        this.filterCategories();

      }catch(e){

        console.warn('Categories load warning:', e);

        this.fallback=false;

        const cats = this.filterLocalByType(this.type);

        this.categories = cats;

        this.catsFlat = this.flattenWithIndent(cats);

        this.filterCategories();

      } finally {

        this.loading = false;

        this.syncCategoryInputFromSelection();

      }

    },

    filterCategories(){

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

    openCategorySuggestions(){

      if (this.loading) return;

      this.showCatSuggestions = true;

      if (!this.catsFiltered.length) this.filterCategories();

      if (this.catHighlightIndex === -1 && this.catsFiltered.length) {

        this.catHighlightIndex = 0;

      }

    },

    closeCategorySuggestions(){

      this.showCatSuggestions = false;

      this.catHighlightIndex = -1;

    },

    handleCategoryBlur(){

      setTimeout(() => this.closeCategorySuggestions(), 120);

    },

    handleCategoryInput(){

      this.categoryId = null;

      this.filterCategories();

      this.catHighlightIndex = this.catsFiltered.length ? 0 : -1;

      this.openCategorySuggestions();

    },

    moveCategoryHighlight(step){

      if (!this.catsFiltered.length) return;

      if (!this.showCatSuggestions) this.openCategorySuggestions();

      if (this.catHighlightIndex === -1) {

        this.catHighlightIndex = step > 0 ? 0 : this.catsFiltered.length - 1;

        return;

      }

      const max = this.catsFiltered.length;

      this.catHighlightIndex = (this.catHighlightIndex + step + max) % max;

    },

    selectHighlightedCategory(){

      if (!this.catsFiltered.length) return;

      const idx = this.catHighlightIndex >= 0 ? this.catHighlightIndex : 0;

      const cat = this.catsFiltered[idx];

      if (cat) this.selectCategory(cat);

    },

    selectCategory(cat){

      if (!cat) {

        this.categoryId = null;

        this.categorySearch = '';

      } else {

        this.categoryId = cat.id;

        this.categorySearch = cat.name;

      }

      this.closeCategorySuggestions();

    },

    currentCategoryLabel(){

      const selected = this.catsFlat.find(cat => String(cat.id) === String(this.categoryId));

      return selected ? selected.name : '';

    },

    syncCategoryInputFromSelection(){

      const label = this.currentCategoryLabel();

      this.categorySearch = label || '';

    }

  }

};(function(){
  function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
  onReady(function(){
    const el = document.getElementById('description');
    if (!el) { console.warn('Missing #description'); return; }
    const start = function(){
      try { const inst = tinymce.get('description'); if (inst) inst.remove(); } catch(_) {}
      tinymce.init({
        selector:'#description',
        height: 260,
        menubar:true,
        plugins:'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
        toolbar:'undo redo | styles | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code',
        branding:false,
        browser_spellcheck: true,
        gecko_spellcheck: true,
        elementpath: false,
        base_url: '{{ asset('assets/js/tinymce') }}'
      });
    };
    if (window.tinymce) { start(); }
    else {
      const s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js';
      s.referrerPolicy = 'origin';
      s.onload = start;
      s.onerror = function(){ console.warn('TinyMCE CDN failed to load'); };
      document.head.appendChild(s);
    }
  });
})();
</script>
@endpush







