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
  [x-cloak]{display:none!important;}
</style>
@endpush

{{-- This wrapper now controls both existing-image cropping and new-image uploads --}}
<div x-data="modernUploader()" x-init="init()">

  {{-- ------------------ Current Media (with Crop button) ------------------ --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4 shadow-sm">
    <div class="border-b border-slate-200 px-4 py-3 bg-slate-100"><h5>Current Media</h5></div>
    <div class="p-4 sm:p-5">
      @if($product->media->count())
        <div class="flex justify-between items-center flex-wrap gap-2 mb-3" x-show="existingCount > 0">
          <div class="text-xs text-slate-500" x-cloak x-text="selectedExisting.length ? `${selectedExisting.length} selected` : ''"></div>
          <div class="ms-auto">
            <button type="button"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-rose-600 text-rose-700 hover:bg-rose-50 px-3 py-1.5 text-xs inline-flex items-center"
                    x-cloak
                    x-show="selectedExisting.length"
                    :disabled="deletingExisting"
                    @click="confirmBulkDelete()">
              <template x-if="!deletingExisting">
                <span><i class="fas fa-trash mr-1"></i>Delete selected</span>
              </template>
              <template x-if="deletingExisting">
                <span class="inline-flex items-center">
                  <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                  Deleting...
                </span>
              </template>
            </button>
          </div>
        </div>
        <div class="grid grid-cols-12 gap-4 gap-3" x-show="existingCount > 0">
          @foreach($product->media as $media)
            <div class="col-span-6 sm:col-span-4 md:col-span-3" data-media-id="{{ $media->id }}">
              <div class="rounded-2xl border border-slate-200 bg-white shadow-sm position-relative h-full"
                   :class="selectedExisting.includes('{{ (string) $media->id }}') ? 'border-danger border-2 shadow' : ''">
                <div class="position-absolute top-0 start-0 m-2">
                  <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           value="{{ $media->id }}"
                           x-model="selectedExisting"
                           :disabled="deletingExisting">
                  </div>
                </div>
                @if($media->type === 'video')
                  <video src="{{ asset('storage/'.$media->url) }}" class="card-img-top" style="height:140px;object-fit:cover;" controls></video>
                @else
                  <img src="{{ asset('storage/'.$media->url) }}"
                       id="media-img-{{ $media->id }}"
                       class="card-img-top"
                       style="height:140px;object-fit:cover;">
                @endif
                <div class="border-t border-slate-200 px-4 py-3 text-center py-2">
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
                  @if($media->type !== 'video')
                  <button type="button"
                          class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50"
                          @click.prevent="openExistingCrop({{ $media->id }}, '{{ asset('storage/'.$media->url) }}')">
                    <i class="fas fa-crop"></i> Crop
                  </button>
                  @endif

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
        <p class="text-slate-500" x-show="existingCount === 0" x-cloak>No media uploaded yet.</p>
      @else
        <p class="text-slate-500">No media uploaded yet.</p>
      @endif
    </div>
  </div>

  {{-- ------------------ Upload New Media ------------------ --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm mb-5">
    <div class="border-b border-slate-200 px-4 py-3 bg-slate-100 flex justify-between items-center">
      <h5 class="mb-0"><i class="bi bi-images mr-2"></i>Upload Media</h5>
      <small class="text-slate-500" x-text="items.length ? `${items.length} selected` : ''"></small>
    </div>
    <form x-ref="form"
          action="{{ route('media.upload', $product) }}"
          method="POST"
          enctype="multipart/form-data"
          class="p-4 sm:p-5">
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
          <i class="bi bi-cloud-arrow-up fs-2 block mb-2"></i>
          Drag & drop images or videos here or click to browse
        </p>
        <small class="text-slate-500">Images up to 5MB ------ Videos up to 50MB</small>
        <input type="file"
               class="hidden"
               multiple
               accept="image/*,video/*"
               x-ref="file"
               @change="addFiles($event.target.files)">
      </div>

      {{-- Preview grid --}}
      <template x-if="items.length">
        <div class="grid grid-cols-12 gap-4 gap-3 mb-4" id="previewList">
          <template x-for="(it,i) in items" :key="it.id">
            <div class="col-span-6 sm:col-span-4 md:col-span-3">
              <div class="position-relative border rounded thumb overflow-hidden shadow-sm">
                <template x-if="it.type==='video'">
                  <video :src="it.url" class="w-full h-full" controls></video>
                </template>
                <template x-if="it.type==='image'">
                  <img :src="it.url" draggable="false">
                </template>
                <div class="position-absolute top-0 end-0 m-1 flex flex-col gap-1">
                  <template x-if="it.type==='image'">
                    <button type="button"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-amber-500 text-slate-900 hover:bg-amber-400 toolbar-btn"
                            @click.prevent="openNewCrop(i)"
                            title="Crop">
                      <i class="fas fa-crop"></i>
                    </button>
                  </template>
                  <button type="button"
                          class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-rose-600 text-white hover:bg-rose-500 toolbar-btn"
                          @click.prevent="remove(i)"
                          title="Remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="position-absolute bottom-0 start-0 m-1">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-dark bg-opacity-75 text-xs" x-text="i+1"></span>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0" x-show="it.progress !== undefined">
                  <div class="progress-mini"><div :style="`width:${it.progress}%;`"></div></div>
                </div>
              </div>
              <div class="text-truncate text-xs text-slate-500 mt-1" x-text="it.file.name"></div>
            </div>
          </template>
        </div>
      </template>

      {{-- Submit --}}
      <template x-if="items.length">
        <div class="d-grid">
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 rounded-full" @click.prevent="submit">
            <span x-show="!loading"><i class="fas fa-upload mr-1"></i> Upload Media</span>
            <span x-show="loading" class="spinner-border spinner-border-sm"></span>
          </button>
        </div>
      </template>
    </form>
  </div>

  {{-- ------------------ Crop Modal (shared) ------------------ --}}
  <div class="modal" id="cropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900 flex items-center">
            <i class="fas fa-crop mr-2"></i> Crop Image
          </h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal"></button>
        </div>
        <div class="px-4 py-4">
          {{-- Aspect chips --}}
          <div class="mb-3 flex flex-wrap gap-2">
            <template x-for="r in ratios" :key="r.label">
              <span class="ratio-chip"
                    :class="{'active':activeRatio===r.value}"
                    @click="setRatio(r.value)">
                <span x-text="r.label"></span>
              </span>
            </template>
            <div class="ms-auto flex gap-2">
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="zoom(0.1)"  title="Zoom In"><i class="fas fa-search-plus"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="zoom(-0.1)" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="rotate(-45)" title="Rotate Left"><i class="fas fa-undo"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="rotate(45)"  title="Rotate Right"><i class="fas fa-redo"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="flipX()"     title="Flip Horizontal"><i class="fas fa-arrows-alt-h"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="flipY()"     title="Flip Vertical"><i class="fas fa-arrows-alt-v"></i></button>
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" @click="reset()"     title="Reset"><i class="fas fa-sync"></i></button>
            </div>
          </div>
          <div id="cropWrapper">
            <img id="cropImage" src="">
          </div>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <div class="me-auto text-xs text-slate-500" x-text="dimText"></div>
          <div class="flex items-center mr-3">
            <label class="text-xs mr-2">Quality</label>
            <input type="range" min="60" max="100" step="2" x-model="quality" style="width:120px">
          </div>
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-bs-dismiss="modal">Cancel</button>
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" id="cropApplyBtn">
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
    // ------------ Upload state ------------
    items: [], dragging:false, loading:false,

    // ------------ Existing media state ------------
    selectedExisting: [],
    existingCount: {{ $product->media->count() }},
    deletingExisting:false,
    deleteUrlBase: '{{ url('media') }}',
    bulkDeleteUrl: '{{ route('media.bulk-destroy', $product) }}',
    csrfToken: '{{ csrf_token() }}',

    // ------------ Crop state ------------
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

    // ------------ Existing media actions ------------
    confirmDeleteExisting(id){
      if(this.deletingExisting) return;
      if(!confirm('Remove this media item?')) return;
      this.performDelete([id], false);
    },
    confirmBulkDelete(){
      if(this.deletingExisting || !this.selectedExisting.length) return;
      const count = this.selectedExisting.length;
      const label = count === 1 ? 'this media item' : `${count} media items`;
      if(!confirm(`Remove ${label}?`)) return;
      const ids = this.selectedExisting.map(id => Number(id)).filter(Number.isFinite);
      this.performDelete(ids, true);
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
        }else{
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
          const node = document.querySelector(`[data-media-id="${id}"]`);
          if(node){
            node.remove();
            removed++;
          }
        });
        if(removed){
          this.existingCount = Math.max(0, this.existingCount - removed);
        }
        this.selectedExisting = this.selectedExisting.filter(id => !stringIds.includes(id));
        if(this.existingCount === 0){
          this.selectedExisting = [];
        }
      }catch(error){
        console.error(error);
        alert('Failed to delete media. Please try again.');
      }finally{
        this.deletingExisting = false;
      }
    },


    // ------------ New uploads ------------
    handleDrop(e){ this.dragging=false; this.addFiles(e.dataTransfer.files); },
    addFiles(files){
      [...files].forEach(f=>{
        const isImage = f.type.startsWith('image/');
        const isVideo = f.type.startsWith('video/');
        if(!isImage && !isVideo) return;
        const max = isImage ? 5*1024*1024 : 50*1024*1024;
        if(f.size > max){
          alert(`${f.name} is larger than ${isImage ? '5MB' : '50MB'}`);
          return;
        }
        const url = URL.createObjectURL(f);
        this.items.push({id:crypto.randomUUID(), file:f, url, type:isVideo?'video':'image'});
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

    // ------------ Open cropper ------------
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

    // ------------ Crop controls ------------
    setRatio(r){ this.activeRatio = r; this.cropper.setAspectRatio(r); },
    zoom(v){ this.cropper.zoom(v); },
    rotate(d){ this.cropper.rotate(d); },
    flipX(){ this.flipXState = !this.flipXState; this.cropper.scaleX(this.flipXState?-1:1); },
    flipY(){ this.flipYState = !this.flipYState; this.cropper.scaleY(this.flipYState?-1:1); },
    reset(){ this.cropper.reset(); this.flipXState = this.flipYState = false; this.activeRatio = NaN; },

    // ------------ Apply crop ------------
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

    // ------------ Crop existing on server ------------
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

    // ------------ Remove & Submit ------------
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


