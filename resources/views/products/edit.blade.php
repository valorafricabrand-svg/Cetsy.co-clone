{{-- resources/views/products/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0 tex">Edit Listing</h2>
    <div>
      <a href="{{ route('products.index') }}" class="btn btn-outline-secondary me-2">
        <i class="fas fa-arrow-left me-1"></i> Back to Listings
      </a>

      <a href="{{ route('products.show', $product) }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-eye me-1"></i> View
      </a>

      <a href="{{ route('products.create') }}" class="btn btn-primary rounded-pill">
        <i class="fas fa-plus me-1"></i> Add New Listing
      </a>
    </div>
  </div>

  {{-- Validation Errors --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('products.update', $product) }}"
        method="POST" enctype="multipart/form-data"
        x-data="listingForm()"
        x-init="init()"
        @submit.prevent="$el.submit()"
  >
    @csrf @method('PUT')

    {{-- ───────────────────────────────────────────────────────────────────────────────
       Listing Details
    ─────────────────────────────────────────────────────────────────────────────── --}}
    <div class="card mb-4 shadow-sm">
      <div class="card-body p-4">
        <div class="row g-4">

          {{-- Name --}}
          <div class="col-12">
            <label for="name" class="form-label fw-semibold">Listing Name</label>
            <input type="text" id="name" name="name" spellcheck="true" autocapitalize="sentences" autocomplete="on"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name',$product->name) }}" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Type --}}
          <div class="col-md-6">
            <label for="type" class="form-label fw-semibold">Listing Type</label>
            <select id="type" name="type" x-model="type"
                    @change="loadCategories()"
                    class="form-select @error('type') is-invalid @enderror"
                    required>
              <option value="">Choose type</option>
              <option value="physical" @selected(old('type',$product->type)=='physical')>Physical</option>
              <option value="digital"  @selected(old('type',$product->type)=='digital')>Digital</option>
              <option value="service"  @selected(old('type',$product->type)=='service')>Service</option>
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Category --}}
          <div class="col-md-6">
            <label for="category_id" class="form-label fw-semibold">Category</label>
            <select id="category_id" name="category_id" x-model="categoryId"
                    class="form-select @error('category_id') is-invalid @enderror"
                    required>
              <option value="">Choose category</option>
              {{-- filled by Alpine.js --}}
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Description --}}
          <div class="col-12">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="6" spellcheck="true"
                      class="form-control @error('description') is-invalid @enderror">{{ old('description',$product->description) }}</textarea>
            @error('description')<div class="text-danger mt-1">{{ $message }}</div>@enderror
          </div>

          {{-- Price & Discount --}}
          <div class="col-md-6">
            <label for="price" class="form-label fw-semibold">Price ({{ get_currency() }})</label>
            <input type="number" id="price" name="price" step="0.01" min="0"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price',$product->price) }}" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label for="discount_percent" class="form-label fw-semibold">% Discount</label>
            <input
              type="number"
              id="discount_percent"
              name="discount_percent"
              step="1"
              min="1"
              max="100"
              class="form-control @error('discount_percent') is-invalid @enderror"
              value="{{ old('discount_percent', $product->discount_percent) }}"
            >
            @error('discount_percent')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Stock (Physical only) --}}
          <div class="col-md-6" id="stockSection">
            <label for="stock" class="form-label fw-semibold">Stock Quantity</label>
            <input type="number" id="stock" name="stock" min="0" step="1"
                   class="form-control @error('stock') is-invalid @enderror"
                   value="{{ old('stock',$product->stock) }}">
            @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Digital File (Digital only) --}}
          <div class="col-md-6" id="digitalFileSection" style="display:none;">
            <label for="digital_file" class="form-label fw-semibold">Digital File</label>
            <input type="file" id="digital_file" name="digital_file"
                   class="form-control @error('digital_file') is-invalid @enderror"
                   accept=".zip,.pdf,.mp3,.mp4,.docx,.xlsx,.pptx">
            @error('digital_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Country --}}
          <div class="col-md-6">
            <label for="country_id" class="form-label fw-semibold">Country of Origin</label>
            <select id="country_id" name="country_id"
                    class="form-select @error('country_id') is-invalid @enderror" required>
              <option value="" disabled>Choose a country</option>
              @foreach($countries as $country)
                <option value="{{ $country->id }}"
                  @selected(old('country_id',$product->country_id)==$country->id)>
                  {{ $country->name }}
                </option>
              @endforeach
            </select>
            @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Postal Code --}}
          <div class="col-md-6">
            <label for="origin_postal_code" class="form-label fw-semibold">Origin Postal Code</label>
            <input type="text" id="origin_postal_code" name="origin_postal_code"
                   class="form-control @error('origin_postal_code') is-invalid @enderror"
                   value="{{ old('origin_postal_code',$product->origin_postal_code) }}">
            @error('origin_postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Processing Time --}}
          <div class="col-md-6">
            <label for="processing_time_id" class="form-label fw-semibold">Processing Time</label>
            <select id="processing_time_id" name="processing_time_id"
                    class="form-select @error('processing_time_id') is-invalid @enderror">
              <option value="" disabled>Choose a processing time</option>
              @foreach($processingTimes as $pt)
                <option value="{{ $pt->id }}"
                  @selected(old('processing_time_id',$product->processing_time_id)==$pt->id)>
                  {{ $pt->name }} — {{ $pt->days }} day{{ $pt->days>1?'s':'' }}
                </option>
              @endforeach
            </select>
            @error('processing_time_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Shipping Profiles (Physical only) --}}
          <div class="col-12" id="shippingProfilesSection" style="display:none;">
            <label class="form-label fw-semibold">Shipping Profiles</label>
            <div class="row gy-3">
              @foreach($shippingProfiles as $profile)
                <div class="col-md-6">
                  <div class="d-flex align-items-start border rounded p-3">
                    <div class="form-check me-3 mt-1">
                      <input class="form-check-input" type="checkbox"
                             name="shipping_profiles[]" value="{{ $profile->id }}"
                             id="sp_{{ $profile->id }}"
                             {{ in_array($profile->id, old('shipping_profiles',$assignedProfiles))?'checked':'' }}>
                    </div>
                    <div class="flex-grow-1">
                      <label class="form-check-label fw-semibold" for="sp_{{ $profile->id }}">
                        {{ $profile->name }}
                      </label>
                      <div class="small text-muted">
                        {{ get_currency() }}{{ number_format($profile->base_rate,2) }} ·
                        {{ $profile->delivery_days }} day{{ $profile->delivery_days>1?'s':'' }}
                        @if($profile->pickup_available)
                          <span class="badge bg-success ms-2">Pickup</span>
                        @endif
                      </div>
                    </div>
                    <div class="form-check ms-3 mt-1">
                      <input class="form-check-input" type="radio"
                             name="default_shipping_profile" value="{{ $profile->id }}"
                             id="default_{{ $profile->id }}"
                             {{ old('default_shipping_profile',$defaultProfileId)==$profile->id?'checked':'' }}>
                      <label class="form-check-label small" for="default_{{ $profile->id }}">Default</label>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
            @error('shipping_profiles')<div class="text-danger mt-2">{{ $message }}</div>@enderror
            @error('default_shipping_profile')<div class="text-danger mt-2">{{ $message }}</div>@enderror
          </div>

        </div>
      </div>
    </div>

    {{-- Save Changes --}}
    <div class="d-grid mb-5">
      <button type="submit" class="btn btn-success btn-lg rounded-pill">
        <i class="fas fa-save me-1"></i> Save Changes
      </button>
    </div>
  </form>

  {{-- Existing Digital Files --}}
  @if($product->digitalFiles->count())
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-light"><h5>Current Digital Files</h5></div>
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

  {{-- Variations section intentionally omitted per your request --}}
  @include('products._variation',['product'=>$product])


  @include('products._edit_media')
</div>

{{-- Image Preview Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 bg-transparent">
      <div class="modal-body p-0">
        <img src="" id="modalImage" class="w-100 rounded" alt="Preview">
      </div>
      <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
// Show/hide stock, digital, shipping sections when type changes
function toggleSections() {
  const t = document.getElementById('type')?.value || '';
  document.getElementById('stockSection').style.display            = (t==='physical') ? 'block' : 'none';
  document.getElementById('digitalFileSection').style.display      = (t==='digital')  ? 'block' : 'none';
  document.getElementById('shippingProfilesSection').style.display = (t==='physical') ? 'block' : 'none';
}
document.getElementById('type')?.addEventListener('change', toggleSections);
window.addEventListener('DOMContentLoaded', toggleSections);

// Auto-choose default shipping profile if only one checked / keep one default selected
document.addEventListener('change', e=>{
  if (!e.target.matches('input[name="shipping_profiles[]"]')) return;
  const id = e.target.value;
  if (e.target.checked) {
    document.getElementById('default_'+id).checked = true;
  } else if (document.getElementById('default_'+id).checked) {
    const next = document.querySelector('input[name="shipping_profiles[]"]:checked');
    if (next) document.getElementById('default_'+next.value).checked = true;
  }
});

// Sortable previews for new images (used in products._edit_media if it hooks into this)
function imageUploadSortable(){
  return {
    previews: [], idCounter:0, sortable:null,
    handleFiles(files){ [...files].forEach(f=> this.previewFile(f)); },
    previewFile(file){
      const r = new FileReader();
      r.onload = e=>{
        this.previews.push({id:this.idCounter++,url:e.target.result,fileObject:file});
        this.$nextTick(()=>this.initSortable());
      };
      r.readAsDataURL(file);
    },
    removeFile(i){ this.previews.splice(i,1); },
    handleDrop(e){ if(e.dataTransfer.files.length) this.handleFiles(e.dataTransfer.files); },
    initSortable(){
      if(this.sortable) this.sortable.destroy();
      this.sortable = Sortable.create(document.getElementById('previewList'), {
        animation:150,
        onEnd:evt=>{
          const m = this.previews.splice(evt.oldIndex,1)[0];
          this.previews.splice(evt.newIndex,0,m);
        }
      });
    }
  }
}

// Alpine form for dynamic category loading (no variants on this page)
function listingForm(){
  return {
    type: '{{ old('type',$product->type) }}',
    categoryId: '{{ old('category_id',$product->category_id) }}',
    init(){
      this.loadCategories();
      toggleSections();
    },
    async loadCategories(){
      const sel = document.getElementById('category_id');
      if(!sel) return;
      sel.innerHTML = '<option>Loading…</option>';
      if(!this.type){
        sel.innerHTML = '<option value="">Choose category</option>';
        return;
      }
      try {
        const res = await fetch(`/api/categories/by-type/${encodeURIComponent(this.type)}`);
        if(!res.ok) throw new Error();
        const cats = await res.json();
        sel.innerHTML = '<option value="">Choose category</option>';
        cats.forEach(c=> {
          const o = document.createElement('option');
          o.value = c.id; o.text = c.name;
          if(String(c.id)===String(this.categoryId)) o.selected=true;
          sel.append(o);
        });
      } catch {
        sel.innerHTML = '<option>Error loading categories</option>';
      }
    },
  }
}

// Bootstrap 5 image preview modal wiring
document.getElementById('imageModal')?.addEventListener('show.bs.modal', function (event) {
  const trigger = event.relatedTarget;
  if (!trigger) return;
  const url = trigger.getAttribute('data-img-url');
  const img = document.getElementById('modalImage');
  if (img && url) img.src = url;
});
</script>

<!-- Include TinyMCE from the local directory -->
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
tinymce.init({
  selector: '#description',
  height: 400,
  min_height: 400,
  menubar: true,
  plugins: [
    'advlist autolink lists link image charmap print preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table paste code help wordcount',
    'formatpainter', 'lineheight', 'textcolor'
  ],
  toolbar: [
    'undo redo | formatpainter | fontselect fontsizeselect |',
    'lineheightselect | bold italic underline strikethrough forecolor backcolor |',
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
  lineheight_formats: '1 1.2 1.5 1.8 2 3',
  browser_contextmenu: true,
  browser_spellcheck: true,
  gecko_spellcheck: true,
  contextmenu: 'link image inserttable | cell row column',
  branding: false,
  content_css: '{{ asset("css/tinymce-content.css") }}',
  content_style: 'body { min-height:400px !important; }',
  setup(editor) {
    editor.on('change', () => editor.save());
  }
});
</script>
@endpush
