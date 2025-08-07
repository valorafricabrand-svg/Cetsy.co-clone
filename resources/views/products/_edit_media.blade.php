{{-- resources/views/products/edit.blade.php --}}



@push('styles')
<link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
<style>
  :root{
    --accent:#0d6efd;
    --accent-light:rgba(13,110,253,.08);
  }
  .dropzone{transition:.18s;border:2px dashed #ced4da;}
  .dropzone.drag{background:var(--accent-light);border-color:var(--accent)!important;color:var(--accent);}
  .thumb{height:170px}
  .thumb img{width:100%;height:100%;object-fit:cover;user-select:none}
  .toolbar-btn{min-width:34px}
  #cropWrapper{
    position:relative;width:100%;height:72vh;max-height:calc(100vh - 220px);background:#111;overflow:hidden;
  }
  #cropWrapper img{max-width:100%;display:block}
  .cropper-container,.cropper-wrap-box,.cropper-canvas,.cropper-drag-box{
    width:100%!important;height:100%!important;
  }
  .ratio-chip{
    cursor:pointer;padding:.25rem .6rem;border:1px solid #ced4da;border-radius:1rem;font-size:.75rem;transition:.15s;
  }
  .ratio-chip.active, .ratio-chip:hover{background:var(--accent);color:#fff;border-color:var(--accent);}
  .progress-mini{height:4px;background:#e9ecef;border-radius:2px;overflow:hidden}
  .progress-mini > div{height:100%;background:var(--accent);width:0;transition:width .2s}
</style>
@endpush

{{-- This wrapper now controls both existing-image cropping and new-image uploads --}}
<div x-data="modernUploader()" x-init="init()">

  {{-- ─── Current Images (with Crop button) ─── --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-light"><h5>Current Images</h5></div>
    <div class="card-body">
      @if($product->media->count())
        <div class="row g-3">
          @foreach($product->media as $media)
            <div class="col-6 col-sm-4 col-md-3">
              <div class="card">
                <img src="{{ asset('storage/'.$media->url) }}"
                     id="media-img-{{ $media->id }}"
                     class="card-img-top"
                     style="height:140px;object-fit:cover;">
                <div class="card-footer text-center py-2">
                  @php
                    $mediaUrl = asset('storage/'.$media->url);
                    $isFeatured = $product->featured_image === $mediaUrl;
                  @endphp
                  <form action="{{ route('products.setFeaturedImage', $product) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="featured_image" value="{{ $media->url }}">
                    <button type="submit"
                            class="btn btn-sm {{ $isFeatured?'btn-outline-warning':'btn-outline-success' }}">
                      {{ $isFeatured?'Featured':'Make as primary image' }}
                    </button>
                  </form>

                  {{-- Crop existing image --}}
                  <button type="button"
                          class="btn btn-sm btn-outline-secondary"
                          @click.prevent="openExistingCrop({{ $media->id }}, '{{ asset('storage/'.$media->url) }}')">
                    <i class="fas fa-crop"></i> Crop
                  </button>

                  <form action="{{ route('media.destroy', $media) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Remove image?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-muted">No images uploaded yet.</p>
      @endif
    </div>
  </div>

  {{-- ─── Upload & Crop New Images ─── --}}
  <div class="card shadow-sm mb-5">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="bi bi-images me-2"></i>Upload & Crop Images</h5>
      <small class="text-muted" x-text="items.length ? `${items.length} selected` : ''"></small>
    </div>
    <form x-ref="form"
          action="{{ route('media.upload', $product) }}"
          method="POST"
          enctype="multipart/form-data"
          class="card-body">
      @csrf

      {{-- Drop zone --}}
      <div class="dropzone rounded-3 py-5 text-center mb-4"
           :class="{'drag':dragging}"
           @click="$refs.file.click()"
           @dragenter.prevent="dragging=true"
           @dragover.prevent="dragging=true"
           @dragleave.prevent="dragging=false"
           @drop.prevent="handleDrop($event)"
           style="cursor:pointer;">
        <p class="mb-1">
          <i class="bi bi-cloud-arrow-up fs-2 d-block mb-2"></i>
          Drag & drop images here or click to browse
        </p>
        <small class="text-muted">PNG • JPG • WebP • up to 5MB each</small>
        <input type="file"
               class="d-none"
               multiple
               accept="image/*"
               x-ref="file"
               @change="addFiles($event.target.files)">
      </div>

      {{-- Preview grid --}}
      <template x-if="items.length">
        <div class="row g-3 mb-4" id="previewList">
          <template x-for="(it,i) in items" :key="it.id">
            <div class="col-6 col-sm-4 col-md-3">
              <div class="position-relative border rounded thumb overflow-hidden shadow-sm">
                <img :src="it.url" draggable="false">
                <div class="position-absolute top-0 end-0 m-1 d-flex flex-column gap-1">
                  <button type="button"
                          class="btn btn-sm btn-warning toolbar-btn"
                          @click.prevent="openNewCrop(i)"
                          title="Crop">
                    <i class="fas fa-crop"></i>
                  </button>
                  <button type="button"
                          class="btn btn-sm btn-danger toolbar-btn"
                          @click.prevent="remove(i)"
                          title="Remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="position-absolute bottom-0 start-0 m-1">
                  <span class="badge bg-dark bg-opacity-75 small" x-text="i+1"></span>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0" x-show="it.progress !== undefined">
                  <div class="progress-mini"><div :style="`width:${it.progress}%;`"></div></div>
                </div>
              </div>
              <div class="text-truncate small text-muted mt-1" x-text="it.file.name"></div>
            </div>
          </template>
        </div>
      </template>

      {{-- Submit --}}
      <template x-if="items.length">
        <div class="d-grid">
          <button class="btn btn-success rounded-pill" @click.prevent="submit">
            <span x-show="!loading"><i class="fas fa-upload me-1"></i> Upload Images</span>
            <span x-show="loading" class="spinner-border spinner-border-sm"></span>
          </button>
        </div>
      </template>
    </form>
  </div>

  {{-- ─── Crop Modal (shared) ─── --}}
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
          {{-- Aspect chips --}}
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
          <div id="cropWrapper">
            <img id="cropImage" src="">
          </div>
        </div>
        <div class="modal-footer">
          <div class="me-auto small text-muted" x-text="dimText"></div>
          <div class="d-flex align-items-center me-3">
            <label class="small me-2">Quality</label>
            <input type="range" min="60" max="100" step="2" x-model="quality" style="width:120px">
          </div>
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" id="cropApplyBtn">
            <span x-show="!cropSaving">Apply</span>
            <span x-show="cropSaving" class="spinner-border spinner-border-sm"></span>
          </button>
        </div>
      </div>
    </div>
  </div>

</div>


<script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function modernUploader(){
  return {
    // ── Upload state ──
    items: [], dragging:false, loading:false,

    // ── Crop state ──
    cropper:null,
    cropModal:null,
    currentIndex:null,
    existingMediaId:null,
    dimText:'',
    flipXState:false,
    flipYState:false,
    cropSaving:false,
    quality:92,
    ratios:[
      {label:'Free', value:NaN},
      {label:'1:1', value:1},
      {label:'4:3', value:4/3},
      {label:'3:4', value:3/4},
      {label:'16:9',value:16/9},
      {label:'9:16',value:9/16},
    ],
    activeRatio:NaN,
    _sortable:null,

    init(){
      // initialize Bootstrap modal
      this.cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
      document.getElementById('cropApplyBtn').addEventListener('click', ()=> this.applyCrop());
    },

    // ── New uploads ──
    handleDrop(e){ this.dragging=false; this.addFiles(e.dataTransfer.files); },
    addFiles(files){
      [...files].forEach(f=>{
        if(!f.type.startsWith('image/')) return;
        const url = URL.createObjectURL(f);
        this.items.push({id:crypto.randomUUID(), file:f, url});
      });
      this.$nextTick(()=> this.initSortable());
    },
    initSortable(){
      if(this._sortable || !document.getElementById('previewList')) return;
      this._sortable = new Sortable(document.getElementById('previewList'),{
        animation:150,
        onEnd:(e)=>{
          const moved = this.items.splice(e.oldIndex,1)[0];
          this.items.splice(e.newIndex,0,moved);
        }
      });
    },

    // ── Open cropper ──
    openNewCrop(i){ this.currentIndex = i; this.openCropper(this.items[i].url); },
    openExistingCrop(id, url){
      this.existingMediaId = id;
      this.openCropper(url);
    },
    openCropper(src){
      const img = document.getElementById('cropImage');
      img.src = src;
      this.flipXState = this.flipYState = false;
      this.activeRatio = NaN;
      this.dimText = '';
      this.cropModal.show();
      document.getElementById('cropModal').addEventListener('shown.bs.modal', ()=>{
        if(this.cropper) this.cropper.destroy();
        this.cropper = new Cropper(img,{
          viewMode:1,
          autoCropArea:1,
          responsive:true,
          background:false,
          movable:true,
          zoomable:true,
          rotatable:true,
          crop:(e)=>{ this.dimText = `${Math.round(e.detail.width)} x ${Math.round(e.detail.height)} px`; }
        });
      },{once:true});
    },

    // ── Crop controls ──
    setRatio(r){ this.activeRatio = r; this.cropper.setAspectRatio(r); },
    zoom(v){ this.cropper.zoom(v); },
    rotate(d){ this.cropper.rotate(d); },
    flipX(){ this.flipXState = !this.flipXState; this.cropper.scaleX(this.flipXState?-1:1); },
    flipY(){ this.flipYState = !this.flipYState; this.cropper.scaleY(this.flipYState?-1:1); },
    reset(){ this.cropper.reset(); this.flipXState = this.flipYState = false; this.activeRatio = NaN; },

    // ── Apply crop ──
    applyCrop(){
      if(this.existingMediaId){
        return this.applyExistingCrop();
      }
      // new-upload logic
      this.cropSaving = true;
      const canvas = this.cropper.getCroppedCanvas({maxWidth:4096,maxHeight:4096});
      canvas.toBlob(blob=>{
        const old = this.items[this.currentIndex];
        const name = old.file.name.replace(/\.[^.]+$/,'') + '-cropped.jpg';
        const file = new File([blob], name, {type:'image/jpeg', lastModified:Date.now()});
        const url  = URL.createObjectURL(file);
        URL.revokeObjectURL(old.url);
        this.items[this.currentIndex] = {...old, file, url};
        this.cropSaving = false;
        this.cropModal.hide();
        this.cropper.destroy(); this.cropper = null;
      }, 'image/jpeg', this.quality/100);
    },

    // ── Crop existing on server ──
    async applyExistingCrop(){
      this.cropSaving = true;
      const canvas = this.cropper.getCroppedCanvas({maxWidth:4096,maxHeight:4096});
      canvas.toBlob(async blob=>{
        const fd = new FormData();
        fd.append('_token','{{ csrf_token() }}');
        fd.append('cropped_image', blob, 'cropped.jpg');
        try {
          const res = await fetch(`{{ url('media') }}/${this.existingMediaId}/crop`, {
            method:'POST',
            body: fd
          });
          if(!res.ok) throw await res.text();
          const data = await res.json();
          document.getElementById(`media-img-${this.existingMediaId}`).src = data.url;
        } catch(e) {
          console.error(e);
          alert('Crop failed.');
        } finally {
          this.cropModal.hide();
          this.cropper.destroy(); this.cropper = null;
          this.existingMediaId = null;
          this.cropSaving = false;
        }
      }, 'image/jpeg', this.quality/100);
    },

    // ── Remove & Submit ──
    remove(i){
      URL.revokeObjectURL(this.items[i].url);
      this.items.splice(i,1);
    },
    async submit(){
      this.loading = true;
      const fd = new FormData(this.$refs.form);
      this.items.forEach((it,idx)=>{
        fd.append('media[]', it.file, it.file.name);
        fd.append('order[]', idx);
      });
      try {
        const res = await fetch(this.$refs.form.action, {
          method:'POST',
          body: fd,
          headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
        });
        if(!res.ok) throw await res.text();
        location.reload();
      } catch(e) {
        console.error(e);
        alert('Upload failed. Check console.');
        this.loading = false;
      }
    },
  }
}
</script>

