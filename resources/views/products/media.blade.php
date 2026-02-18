{{-- resources/views/products/media.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title', $product->name . ' | Media')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
<style>
  :root{ --accent:#0d6efd; --accent-light:rgba(13,110,253,.08); }
  .dropzone{transition:.18s;border:2px dashed #ced4da;}
  .dropzone.drag{background:var(--accent-light);border-color:var(--accent)!important;color:var(--accent);}
  .thumb{height:170px}
  .thumb img{width:100%;height:100%;object-fit:cover;user-select:none}
  .toolbar-action{min-width:34px}
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

@section('main')
@php $current = \Illuminate\Support\Facades\Route::currentRouteName(); @endphp

<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6" x-data="mediaPage({ existingIds: @json($product->media->pluck('id')) })" x-init="init()">
        @include('products.partials.edit-tabs', ['product' => $product, 'current' => $current])

  {{-- Flash --}}
  @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 mt-3">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 pl-3">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- ===== Current Media ===== --}}
  <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3 bg-slate-100 flex flex-wrap gap-2 items-center justify-between">
      <h5 class="mb-0">Current Media</h5>
      @if($product->media->count())
        <div class="flex items-center gap-2" x-show="existingIds.length" x-cloak>
          <button type="button"
                  class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50"
                  @click="toggleSelectAllExisting()"
                  :disabled="existingIds.length === 0">
            <span x-text="selectedExisting.length && selectedExisting.length === existingIds.length ? 'Clear Selection' : 'Select All'"></span>
          </button>
          <span class="text-slate-500 text-xs" x-show="selectedExisting.length" x-text="`${selectedExisting.length} selected`"></span>
          <button type="button"
                  class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50 flex items-center gap-2"
                  :disabled="selectedExisting.length === 0 || deletingExisting"
                  @click="confirmBulkDelete()">
            <template x-if="!deletingExisting">
              <span class="flex items-center gap-2">
                <i class="fas fa-trash"></i>
                <span>Delete Selected</span>
                <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-600"
                      x-show="selectedExisting.length"
                      x-text="selectedExisting.length"></span>
              </span>
            </template>
            <template x-if="deletingExisting">
              <span class="flex items-center gap-2">
                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent" role="status" aria-hidden="true"></span>
                <span>Deleting...</span>
              </span>
            </template>
          </button>
        </div>
      @endif
    </div>
    <div class="p-4 sm:p-5">
      @if($product->media->count())
        <div class="grid grid-cols-12 gap-4 gap-3">
          @foreach($product->media as $media)
            @php
              $mediaUrl = asset('storage/'.$media->url);
              $isFeatured = $product->featured_image === $mediaUrl;
            @endphp
            <div class="col-span-6 sm:col-span-4 md:col-span-3">
              <div data-media-id="{{ $media->id }}" class="relative rounded-2xl border border-slate-200 bg-white shadow-sm"
                   :class="selectedExisting.includes({{ $media->id }}) ? 'border-emerald-500 border-2 shadow' : ''">
                <div class="absolute left-0 top-0 m-2 rounded-full bg-white shadow-sm">
                  <input type="checkbox"
                         class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                         x-model.number="selectedExisting"
                         value="{{ $media->id }}">
                </div>
                @if($media->type === 'video')
                  <video src="{{ $mediaUrl }}" class="h-[140px] w-full object-cover" controls></video>
                @else
                  <img src="{{ $mediaUrl }}"
                       id="media-img-{{ $media->id }}"
                       class="h-[140px] w-full object-cover"
                       style="height:140px;object-fit:cover;">
                @endif
                <div class="border-t border-slate-200 px-4 py-3 text-center py-2">
                  {{-- Make featured --}}
                  <form action="{{ route('products.setFeaturedImage', $product) }}" method="POST" class="inline-block">
                    @csrf @method('PATCH')
                    <input type="hidden" name="featured_image" value="{{ $media->url }}">
                    <button type="submit" class="inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold {{ $isFeatured ? 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                      {{ $isFeatured?'Featured':'Make primary' }}
                    </button>
                  </form>

                  {{-- Crop existing (form-submitted) --}}
                  @if($media->type !== 'video')
                  <button type="button"
                          class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50"
                          @click="openExistingCrop({{ $media->id }}, '{{ $mediaUrl }}')">
                    <i class="fas fa-crop"></i> Crop
                  </button>
                  @endif

                  {{-- Delete --}}
                    <button type="button"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50"
                            :disabled="deletingExisting"
                            @click.prevent="confirmDeleteExisting({{ $media->id }})"
                            title="Delete media">
                      <i class="fas fa-trash"></i>
                    </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-slate-500 mb-0">No media uploaded yet.</p>
      @endif
    </div>
  </div>

  {{-- ===== Upload New Media (normal form submit) ===== --}}
  <div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3 bg-slate-100 flex justify-between items-center">
      <h5 class="mb-0"><i class="fas fa-images mr-2"></i>Upload Media</h5>
      <small class="text-slate-500" x-text="items.length ? `${items.length} selected` : ''"></small>
    </div>

    <form action="{{ route('media.upload', $product) }}"
          method="POST"
          enctype="multipart/form-data"
          class="p-4 sm:p-5"
          x-ref="uploadForm"
          @submit="beforeUploadSubmit">
      @csrf

      {{-- real file input for non-cropped files --}}
      <input type="file"
             name="media[]"
             class="hidden"
             multiple
             accept="image/*,video/*"
             x-ref="fileInput"
             @change="seedFromNative($event)">

      {{-- hidden container to hold cropped base64 overrides --}}
      <div x-ref="b64Container"></div>

      {{-- Dropzone --}}
      <div class="dropzone mb-4 rounded-2xl py-5 text-center"
           :class="{'drag':dragging}"
           @click="$refs.fileInput.click()"
           @dragenter.prevent="dragging=true"
           @dragover.prevent="dragging=true"
           @dragleave.prevent="dragging=false"
           @drop.prevent="handleDrop($event)"
           style="cursor:pointer;">
        <p class="mb-1">
          <i class="fas fa-cloud-arrow-up mb-2 block text-2xl"></i>
          Drag & drop images or videos here or click to browse
        </p>
        <small class="text-slate-500">Images up to 5MB - Videos up to 50MB</small>
      </div>

      {{-- Previews --}}
      <template x-if="items.length">
        <div class="grid grid-cols-12 gap-4 gap-3 mb-4" id="previewList">
          <template x-for="(it,i) in items" :key="it.id">
            <div class="col-span-6 sm:col-span-4 md:col-span-3">
              <div class="relative overflow-hidden rounded border shadow-sm thumb">
                <template x-if="it.type==='video'">
                  <video :src="it.previewUrl" class="w-full h-full" controls></video>
                </template>
                <template x-if="it.type==='image'">
                  <img :src="it.previewUrl" draggable="false">
                </template>
                <div class="absolute right-0 top-0 m-1 flex flex-col gap-1">

                  <template x-if="it.type==='image'">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-amber-500 text-slate-900 hover:bg-amber-400 toolbar-action" @click.prevent="openNewCrop(i)" title="Crop">
                      <i class="fas fa-crop"></i>
                    </button>
                  </template>
                  <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-rose-600 text-white hover:bg-rose-500 toolbar-action"
                          @click.prevent="removeNew(i)"
                          title="Remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="absolute bottom-0 left-0 m-1">
                  <span class="inline-flex items-center rounded-full bg-slate-900/75 px-2 py-0.5 text-xs font-medium text-white" x-text="i+1"></span>
                </div>
              </div>
              <div class="mt-1 truncate text-xs text-slate-500" x-text="it.name"></div>
            </div>
          </template>
        </div>
      </template>

      {{-- Submit --}}
      <template x-if="items.length">
        <div class="grid">
          <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 rounded-full">
            <i class="fas fa-upload mr-1"></i> Upload Media
          </button>
        </div>
      </template>
    </form>
  </div>

  {{-- ===== Shared Crop Modal (used for both new & existing) ===== --}}
  <div x-cloak x-show="isCropOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60" @click="closeCropModal()"></div>
    <div class="relative w-full max-w-6xl max-h-[92vh] overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900 flex items-center">
            <i class="fas fa-crop mr-2"></i> Crop Image
          </h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="closeCropModal()">×</button>
        </div>
        <div class="px-4 py-4">
          <div class="mb-3 flex flex-wrap gap-2">
            <template x-for="r in ratios" :key="r.label">
              <span class="ratio-chip"
                    :class="{'active':activeRatio===r.value}"
                    @click="setRatio(r.value)">
                <span x-text="r.label"></span>
              </span>
            </template>
            <div class="ml-auto flex gap-2">
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="zoom(0.1)"  title="Zoom In"><i class="fas fa-search-plus"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="zoom(-0.1)" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="rotate(-45)" title="Rotate Left"><i class="fas fa-undo"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="rotate(45)"  title="Rotate Right"><i class="fas fa-redo"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="flipX()"     title="Flip Horizontal"><i class="fas fa-arrows-alt-h"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="flipY()"     title="Flip Vertical"><i class="fas fa-arrows-alt-v"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="reset()"     title="Reset"><i class="fas fa-sync"></i></button>
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
          <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
            <div class="mr-auto text-xs text-slate-500" x-text="dimText"></div>
            <div class="flex items-center mr-3">
              <label class="text-xs mr-2">Quality</label>
              <input type="range" min="60" max="100" step="2" x-model.number="quality" style="width:120px">
            </div>
            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" @click="closeCropModal()">Cancel</button>
            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" id="cropApplyBtn">
              <span x-show="!cropSaving">Apply</span>
              <span x-show="cropSaving" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent"></span>
            </button>
          </div>
        </form>
      </div>
  </div>

      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
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
      // Existing media selection & deletion
      existingIds: normalizedExisting,
      selectedExisting: [],
      deletingExisting:false,
      deleteUrlBase: '{{ url('media') }}',
      bulkDeleteUrl: '{{ route('media.bulk-destroy', $product) }}',
      csrfToken: '{{ csrf_token() }}',
    // Crop
    cropper:null, isCropOpen:false, cropSaving:false,
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
      // Attach once for crop action button.
      document.getElementById('cropApplyBtn').addEventListener('click', ()=> this.applyCrop());

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
      this.isCropOpen = true;
      this.$nextTick(() => {
        setTimeout(() => {
          if(this.cropper) this.cropper.destroy();
          this.cropper = new Cropper(document.getElementById('cropImage'), {
            viewMode:1, autoCropArea:1, responsive:true, background:false,
            movable:true, zoomable:true, rotatable:true,
            crop: e => { this.dimText = `${Math.round(e.detail.width)} x ${Math.round(e.detail.height)} px`; }
          });
        }, 25);
      });
    },

    closeCropModal(){
      this.isCropOpen = false;
      if (this.cropper) {
        this.cropper.destroy();
        this.cropper = null;
      }
      this.cropSaving = false;
      this.existingMediaId = null;
      this.newIndex = null;
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
      this.closeCropModal();
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

    confirmDeleteExisting(id){
        if(this.deletingExisting) return;
        if(!confirm('Delete this media item?')) return;
        this.performDelete([id], false);
      },

      confirmBulkDelete(){
        if(this.deletingExisting || this.selectedExisting.length === 0) return;
        const count = this.selectedExisting.length;
        const label = count === 1 ? 'this media item' : `${count} media items`;
        if(!confirm(`Delete ${label}?`)) return;
        this.performDelete(this.selectedExisting, true);
      },

      async performDelete(ids, forceBulk = false){
        const uniqueIds = Array.from(new Set((ids || []).map(id => Number(id)).filter(Number.isFinite)));
        if(!uniqueIds.length) return;
        this.deletingExisting = true;
        try{
          if(forceBulk || uniqueIds.length > 1){
            const res = await fetch(this.bulkDeleteUrl, {
              method:'DELETE',
              headers:{
                'X-CSRF-TOKEN': this.csrfToken,
                'Content-Type':'application/json',
                'Accept':'application/json'
              },
              body: JSON.stringify({media_ids: uniqueIds})
            });
            if(!res.ok) throw new Error(await res.text());
          } else {
            const res = await fetch(`${this.deleteUrlBase}/${uniqueIds[0]}`, {
              method:'DELETE',
              headers:{
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept':'application/json'
              }
            });
            if(!res.ok) throw new Error(await res.text());
          }

          const stringIds = uniqueIds.map(String);
          let removed = 0;
          stringIds.forEach(id => {
            const node = document.querySelector(`[data-media-id=\"${id}\"]`);
            if(node){
              node.remove();
              removed++;
            }
          });

          if(removed){
            this.existingIds = this.existingIds
              .filter(id => !uniqueIds.includes(Number(id)))
              .map(id => Number(id));
          }

          this.selectedExisting = this.selectedExisting
            .filter(id => !uniqueIds.includes(Number(id)))
            .map(id => Number(id));

          if(this.existingIds.length === 0){
            this.selectedExisting = [];
          }
        }catch(error){
          console.error(error);
          alert('Failed to delete media. Please try again.');
        }finally{
          this.deletingExisting = false;
        }
      },
  }
}
</script>
@endpush










