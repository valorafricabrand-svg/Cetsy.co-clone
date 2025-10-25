{{-- resources/views/products/media.blade.php --}}
@extends('layouts.app')
@section('title', $product->name . ' | Media')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
<style>
  :root{ --accent:#0d6efd; --accent-light:rgba(13,110,253,.08); }
  .page-header-sticky{position:sticky;top:0;z-index:1020;background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
  .tab-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;white-space:nowrap}
  .tab-scroll .nav-link{border-radius:999px}
  .rounded-4,.rounded-top-4{border-radius:1rem!important}

  .dropzone{transition:.18s;border:2px dashed #ced4da;}
  .dropzone.drag{background:var(--accent-light);border-color:var(--accent)!important;color:var(--accent);}
  .thumb{height:170px}
  .thumb img{width:100%;height:100%;object-fit:cover;user-select:none}
  .toolbar-btn{min-width:34px}
  .progress-mini{height:4px;background:#e9ecef;border-radius:2px;overflow:hidden}
  .progress-mini>div{height:100%;background:var(--accent);width:0;transition:width .2s}
  [x-cloak]{display:none!important;}

  /* Cropper wrapper */
  #cropWrapper{position:relative;width:100%;height:72vh;max-height:calc(100vh - 220px);background:#111;overflow:hidden;}
  #cropWrapper img{max-width:100%;display:block}
  .ratio-chip{cursor:pointer;padding:.25rem .6rem;border:1px solid #ced4da;border-radius:1rem;font-size:.75rem;transition:.15s;}
  .ratio-chip.active,.ratio-chip:hover{background:var(--accent);color:#fff;border-color:var(--accent);}
</style>
@endpush

@section('content')
@php $current = \Illuminate\Support\Facades\Route::currentRouteName(); @endphp

<div class="content" x-data="mediaPage({ existingIds: @json($product->media->pluck('id')) })" x-init="init()">

  {{-- Tabs header --}}
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

  {{-- Flash --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger mt-3">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 ps-3">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- ===== Current Media ===== --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-light d-flex flex-wrap gap-2 align-items-center justify-content-between">
      <h5 class="mb-0">Current Media</h5>
      @if($product->media->count())
        <div class="d-flex align-items-center gap-2" x-show="existingIds.length" x-cloak>
          <button type="button"
                  class="btn btn-sm btn-outline-secondary"
                  @click="toggleSelectAllExisting()"
                  :disabled="existingIds.length === 0">
            <span x-text="selectedExisting.length && selectedExisting.length === existingIds.length ? 'Clear Selection' : 'Select All'"></span>
          </button>
          <span class="text-muted small" x-show="selectedExisting.length" x-text="`${selectedExisting.length} selected`"></span>
          <form action="{{ route('media.bulk-destroy', $product) }}"
                method="POST"
                x-ref="bulkDeleteForm"
                class="d-inline">
            @csrf
            @method('DELETE')
            <div x-ref="bulkDeleteContainer"></div>
            <button type="button"
                    class="btn btn-sm btn-outline-danger d-flex align-items-center gap-2"
                    :disabled="selectedExisting.length === 0"
                    @click="submitBulkDelete">
              <i class="fas fa-trash"></i>
              <span>Delete Selected</span>
              <span class="badge bg-danger bg-opacity-10 text-danger"
                    x-show="selectedExisting.length"
                    x-text="selectedExisting.length"></span>
            </button>
          </form>
        </div>
      @endif
    </div>
    <div class="card-body">
      @if($product->media->count())
        <div class="row g-3">
          @foreach($product->media as $media)
            @php
              $mediaUrl = asset('storage/'.$media->url);
              $isFeatured = $product->featured_image === $mediaUrl;
            @endphp
            <div class="col-6 col-sm-4 col-md-3">
              <div class="card position-relative"
                   :class="selectedExisting.includes({{ $media->id }}) ? 'border-primary border-2 shadow' : ''">
                <div class="position-absolute top-0 start-0 m-2 bg-white rounded-pill shadow-sm">
                  <input type="checkbox"
                         class="form-check-input"
                         x-model.number="selectedExisting"
                         value="{{ $media->id }}">
                </div>
                @if($media->type === 'video')
                  <video src="{{ $mediaUrl }}" class="card-img-top" style="height:140px;object-fit:cover;" controls></video>
                @else
                  <img src="{{ $mediaUrl }}"
                       id="media-img-{{ $media->id }}"
                       class="card-img-top"
                       style="height:140px;object-fit:cover;">
                @endif
                <div class="card-footer text-center py-2">
                  {{-- Make featured --}}
                  <form action="{{ route('products.setFeaturedImage', $product) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="featured_image" value="{{ $media->url }}">
                    <button type="submit" class="btn btn-sm {{ $isFeatured?'btn-outline-warning':'btn-outline-success' }}">
                      {{ $isFeatured?'Featured':'Make primary' }}
                    </button>
                  </form>

                  {{-- Crop existing (form-submitted) --}}
                  @if($media->type !== 'video')
                  <button type="button"
                          class="btn btn-sm btn-outline-secondary"
                          @click="openExistingCrop({{ $media->id }}, '{{ $mediaUrl }}')">
                    <i class="fas fa-crop"></i> Crop
                  </button>
                  @endif

                  {{-- Delete --}}
                  <form action="{{ route('media.destroy', $media) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Remove media?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-muted mb-0">No media uploaded yet.</p>
      @endif
    </div>
  </div>

  {{-- ===== Upload New Media (normal form submit) ===== --}}
  <div class="card shadow-sm mb-5">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="bi bi-images me-2"></i>Upload Media</h5>
      <small class="text-muted" x-text="items.length ? `${items.length} selected` : ''"></small>
    </div>

    <form action="{{ route('media.upload', $product) }}"
          method="POST"
          enctype="multipart/form-data"
          class="card-body"
          x-ref="uploadForm"
          @submit="beforeUploadSubmit">
      @csrf

      {{-- real file input for non-cropped files --}}
      <input type="file"
             name="media[]"
             class="d-none"
             multiple
             accept="image/*,video/*"
             x-ref="fileInput"
             @change="seedFromNative($event)">

      {{-- hidden container to hold cropped base64 overrides --}}
      <div x-ref="b64Container"></div>

      {{-- Dropzone --}}
      <div class="dropzone rounded-3 py-5 text-center mb-4"
           :class="{'drag':dragging}"
           @click="$refs.fileInput.click()"
           @dragenter.prevent="dragging=true"
           @dragover.prevent="dragging=true"
           @dragleave.prevent="dragging=false"
           @drop.prevent="handleDrop($event)"
           style="cursor:pointer;">
        <p class="mb-1">
          <i class="bi bi-cloud-arrow-up fs-2 d-block mb-2"></i>
          Drag & drop images or videos here or click to browse
        </p>
        <small class="text-muted">Images up to 5MB • Videos up to 50MB</small>
      </div>

      {{-- Previews --}}
      <template x-if="items.length">
        <div class="row g-3 mb-4" id="previewList">
          <template x-for="(it,i) in items" :key="it.id">
            <div class="col-6 col-sm-4 col-md-3">
              <div class="position-relative border rounded thumb overflow-hidden shadow-sm">
                <template x-if="it.type==='video'">
                  <video :src="it.previewUrl" class="w-100 h-100" controls></video>
                </template>
                <template x-if="it.type==='image'">
                  <img :src="it.previewUrl" draggable="false">
                </template>
                <div class="position-absolute top-0 end-0 m-1 d-flex flex-column gap-1">

                  <template x-if="it.type==='image'">
                    <button type="button" class="btn btn-sm btn-warning toolbar-btn" @click.prevent="openNewCrop(i)" title="Crop">
                      <i class="fas fa-crop"></i>
                    </button>
                  </template>
                  <button type="button" class="btn btn-sm btn-danger toolbar-btn"
                          @click.prevent="removeNew(i)"
                          title="Remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="position-absolute bottom-0 start-0 m-1">
                  <span class="badge bg-dark bg-opacity-75 small" x-text="i+1"></span>
                </div>
              </div>
              <div class="text-truncate small text-muted mt-1" x-text="it.name"></div>
            </div>
          </template>
        </div>
      </template>

      {{-- Submit --}}
      <template x-if="items.length">
        <div class="d-grid">
          <button type="submit" class="btn btn-success rounded-pill">
            <i class="fas fa-upload me-1"></i> Upload Media
          </button>
        </div>
      </template>
    </form>
  </div>

  {{-- ===== Shared Crop Modal (used for both new & existing) ===== --}}
  <div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center">
            <i class="fas fa-crop me-2"></i> Crop Image
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 d-flex flex-wrap gap-2">
            <template x-for="r in ratios" :key="r.label">
              <span class="ratio-chip"
                    :class="{'active':activeRatio===r.value}"
                    @click="setRatio(r.value)">
                <span x-text="r.label"></span>
              </span>
            </template>
            <div class="ms-auto d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm" @click="zoom(0.1)"  title="Zoom In"><i class="fas fa-search-plus"></i></button>
              <button class="btn btn-outline-secondary btn-sm" @click="zoom(-0.1)" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
              <button class="btn btn-outline-secondary btn-sm" @click="rotate(-45)" title="Rotate Left"><i class="fas fa-undo"></i></button>
              <button class="btn btn-outline-secondary btn-sm" @click="rotate(45)"  title="Rotate Right"><i class="fas fa-redo"></i></button>
              <button class="btn btn-outline-secondary btn-sm" @click="flipX()"     title="Flip Horizontal"><i class="fas fa-arrows-alt-h"></i></button>
              <button class="btn btn-outline-secondary btn-sm" @click="flipY()"     title="Flip Vertical"><i class="fas fa-arrows-alt-v"></i></button>
              <button class="btn btn-outline-secondary btn-sm" @click="reset()"     title="Reset"><i class="fas fa-sync"></i></button>
            </div>
          </div>
          <div id="cropWrapper"><img id="cropImage" src=""></div>
        </div>

        {{-- Existing-image crop FORM (normal submit, no fetch) --}}
        <form method="POST"
              :action="existingCropAction"
              x-ref="existingCropForm">
          @csrf
          <input type="hidden" name="cropped_image_b64" x-ref="existingB64">
          <input type="hidden" name="quality" x-ref="existingQuality">
          <div class="modal-footer">
            <div class="me-auto small text-muted" x-text="dimText"></div>
            <div class="d-flex align-items-center me-3">
              <label class="small me-2">Quality</label>
              <input type="range" min="60" max="100" step="2" x-model.number="quality" style="width:120px">
            </div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="cropApplyBtn">
              <span x-show="!cropSaving">Apply</span>
              <span x-show="cropSaving" class="spinner-border spinner-border-sm"></span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
{{-- Bootstrap bundle (required for modal) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
function mediaPage(config = {}){
  const normalizedExisting = Array.isArray(config.existingIds)
    ? config.existingIds.map(id => Number(id))
    : [];
  return {
    // Upload (new images)
    items: [], dragging:false,
    // Existing media selection
    existingIds: normalizedExisting,
    selectedExisting: [],
    // Crop
    cropper:null, cropModal:null, cropSaving:false,
    activeRatio: NaN, quality: 92, dimText:'',
    flipXState:false, flipYState:false,
    // Context flags
    existingMediaId:null, newIndex:null,
    existingCropAction: '',

    ratios:[
      {label:'Free', value:NaN},
      {label:'1:1',  value:1},
      {label:'4:3',  value:4/3},
      {label:'3:4',  value:3/4},
      {label:'16:9', value:16/9},
      {label:'9:16', value:9/16},
    ],

    init(){
      const modalEl = document.getElementById('cropModal');
      this.cropModal = new bootstrap.Modal(modalEl, { backdrop:'static' });
      // Attach BEFORE showing to avoid race
      document.getElementById('cropApplyBtn').addEventListener('click', ()=> this.applyCrop());

      this.$watch('selectedExisting', () => this.updateBulkField());
      this.updateBulkField();
    },

    // ---------- Upload list helpers ----------
    seedFromNative(e){
      if(!e.target.files?.length) return;
      this.addFiles(e.target.files);
    },
    handleDrop(e){
      this.dragging=false;
      if(e.dataTransfer?.files?.length) this.addFiles(e.dataTransfer.files);
    },
    addFiles(fileList){
      [...fileList].forEach(file=>{
        const isImage = file.type.startsWith('image/');
        const isVideo = file.type.startsWith('video/');
        if(!isImage && !isVideo) return;
        const max = isImage ? 5*1024*1024 : 50*1024*1024;
        if(file.size > max){
          alert(`${file.name} is larger than ${isImage ? '5MB' : '50MB'}`);
          return;
        }
        const url = URL.createObjectURL(file);
        this.items.push({
          id: crypto.randomUUID(),
          file, name: file.name,
          previewUrl: url,       // shown in the grid
          b64: null,             // set after crop (dataURL)
          b64Name: file.name,    // name to carry with b64
          type: isVideo ? 'video' : 'image',
        });
      });
    },
    removeNew(i){
      const it = this.items[i];
      if(it?.previewUrl) URL.revokeObjectURL(it.previewUrl);
      this.items.splice(i,1);
      if(this.items.length===0){
        // clear native input so empty submit doesn't send ghosts
        if(this.$refs.fileInput) this.$refs.fileInput.value = '';
      }
    },

    // ---------- Open crop for NEW image ----------
    openNewCrop(index){
      this.existingMediaId = null;
      this.newIndex = index;
      const img = document.getElementById('cropImage');
      img.src = this.items[index].previewUrl;
      this.prepareAndShowCropper();
    },

    // ---------- Open crop for EXISTING image ----------
    openExistingCrop(mediaId, url){
      this.newIndex = null;
      this.existingMediaId = mediaId;

      const img = document.getElementById('cropImage');
      img.src = url;

      // Create the action URL for the existing-crop form
      this.existingCropAction = @json(route('media.crop', ['media' => '__ID__'])).replace('__ID__', String(mediaId));

      this.prepareAndShowCropper();
    },

    prepareAndShowCropper(){
      this.flipXState = this.flipYState = false;
      this.activeRatio = NaN;
      this.dimText = '';

      const modalEl = document.getElementById('cropModal');

      const onShown = () => {
        if(this.cropper) this.cropper.destroy();
        this.cropper = new Cropper(document.getElementById('cropImage'), {
          viewMode:1, autoCropArea:1, responsive:true, background:false,
          movable:true, zoomable:true, rotatable:true,
          crop: e => { this.dimText = `${Math.round(e.detail.width)} x ${Math.round(e.detail.height)} px`; }
        });
      };

      modalEl.addEventListener('shown.bs.modal', onShown, { once:true });
      this.cropModal.show();
    },

    // ---------- Cropper controls ----------
    setRatio(r){ this.activeRatio=r; if(this.cropper) this.cropper.setAspectRatio(r); },
    zoom(v){ if(this.cropper) this.cropper.zoom(v); },
    rotate(d){ if(this.cropper) this.cropper.rotate(d); },
    flipX(){ if(!this.cropper) return; this.flipXState=!this.flipXState; this.cropper.scaleX(this.flipXState?-1:1); },
    flipY(){ if(!this.cropper) return; this.flipYState=!this.flipYState; this.cropper.scaleY(this.flipYState?-1:1); },
    reset(){ if(this.cropper){ this.cropper.reset(); this.flipXState=this.flipYState=false; this.activeRatio=NaN; }},

    // ---------- Apply crop ----------
    applyCrop(){
      if(!this.cropper) return;
      this.cropSaving = true;

      // Canvas (cap huge sizes)
      const canvas = this.cropper.getCroppedCanvas({ maxWidth:4096, maxHeight:4096 });
      const dataURL = canvas.toDataURL('image/jpeg', this.quality/100);

      if(this.existingMediaId){
        // Put into hidden form fields and submit normally
        this.$refs.existingB64.value = dataURL;
        this.$refs.existingQuality.value = String(this.quality);
        this.$refs.existingCropForm.submit();
        // Let the page reload/redirect; no need to cleanup here
        return;
      }

      // NEW image: store dataURL on the item and create/replace a hidden input
      if(this.newIndex !== null){
        const it = this.items[this.newIndex];
        it.b64 = dataURL; // mark as cropped-override
        it.file = null;   // drop original file so only the cropped version uploads
        // Update preview to reflect the crop
        if(it.previewUrl){ URL.revokeObjectURL(it.previewUrl); }
        it.previewUrl = dataURL;

        // Also drop/rename extension to .jpg for the override
        const base = it.name.replace(/\.[^.]+$/,'');
        it.b64Name = base + '-cropped.jpg';
      }

      // Close & cleanup
      this.cropSaving = false;
      this.cropModal.hide();
      this.cropper.destroy();
      this.cropper = null;
      this.existingMediaId = null;
      this.newIndex = null;
    },

    // ---------- Selection helpers ----------
    toggleSelectAllExisting(){
      if(!this.existingIds.length){
        this.selectedExisting = [];
        return;
      }
      if(this.selectedExisting.length === this.existingIds.length){
        this.selectedExisting = [];
      } else {
        this.selectedExisting = [...this.existingIds];
      }
    },

    // ---------- Submit upload form (normal POST) ----------
    beforeUploadSubmit(e){
      // For each CROPPED item, append hidden inputs media_b64[] + media_b64_names[]
      // Your controller should prefer these over the matching original file, if both exist.
      const holder = this.$refs.b64Container;
      holder.innerHTML = '';

      const b64Names = [];
      this.items.forEach(it=>{
        if(it.b64){
          const in1 = document.createElement('input');
          in1.type = 'hidden';
          in1.name = 'media_b64[]';
          in1.value = it.b64;
          holder.appendChild(in1);

          const in2 = document.createElement('input');
          in2.type = 'hidden';
          in2.name = 'media_b64_names[]';
          in2.value = it.b64Name || it.name || 'image.jpg';
          holder.appendChild(in2);

          b64Names.push(in2.value);
        }
      });

      // Rebuild the native file input so it only carries uncropped files
      if(this.$refs.fileInput){
        try {
          const dt = new DataTransfer();
          this.items.forEach(it => {
            if(it.file instanceof File){
              dt.items.add(it.file);
            }
          });
          if(dt.items.length){
            this.$refs.fileInput.files = dt.files;
          } else {
            this.$refs.fileInput.value = '';
          }
        } catch (error) {
          // Fallback: if DataTransfer is unavailable and we have cropped items, clear input
          const hasCropped = this.items.some(it => it.b64);
          if(hasCropped){
            this.$refs.fileInput.value = '';
          }
        }
      }

      // Normal form submit proceeds.
    },

    submitBulkDelete(){
      if(this.selectedExisting.length === 0) return;
      if(!confirm(`Delete ${this.selectedExisting.length} selected media item${this.selectedExisting.length === 1 ? '' : 's'}?`)){
        return;
      }
      this.updateBulkField();
      if(this.$refs.bulkDeleteForm){
        this.$refs.bulkDeleteForm.submit();
      }
    },

    updateBulkField(){
      const holder = this.$refs && this.$refs.bulkDeleteContainer;
      if(!holder) return;
      holder.innerHTML = '';
      this.selectedExisting.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'media_ids[]';
        input.value = id;
        holder.appendChild(input);
      });
    },
  }
}
</script>
@endpush
