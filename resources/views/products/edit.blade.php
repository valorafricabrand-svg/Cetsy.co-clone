@extends('layouts.app')

@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Edit Listing</h2>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back to Products
    </a>
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

  <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="row g-4">
          <!-- Name -->
          <div class="col-12">
            <label for="name" class="form-label fw-semibold">Product Name</label>
            <input type="text" id="name" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $product->name) }}" required autofocus>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Type -->
          <div class="col-md-6">
            <label for="type" class="form-label fw-semibold">Product Type</label>
            <select id="type" name="type"
                    class="form-select @error('type') is-invalid @enderror" required>
              <option value="">Choose type</option>
              <option value="physical" @selected(old('type', $product->type)=='physical')>Physical</option>
              <option value="digital"  @selected(old('type', $product->type)=='digital')>Digital</option>
              <option value="service"  @selected(old('type', $product->type)=='service')>Service</option>
            </select>
            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Category -->
          <div class="col-md-6">
            <label for="category_id" class="form-label fw-semibold">Category</label>
            <select id="category_id" name="category_id"
                    class="form-select @error('category_id') is-invalid @enderror" required>
              <option value="">Select category</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                  @selected(old('category_id',$product->category_id)==$cat->id)>
                  {{ $cat->name }}
                </option>
              @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Description -->
          <div class="col-12">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description"
                      class="form-control @error('description') is-invalid @enderror"
                      rows="6">{{ old('description', $product->description) }}</textarea>
            @error('description') <div class="text-danger mt-1">{{ $message }}</div> @enderror
          </div>

          <!-- Price & Discount -->
          <div class="col-md-6">
            <label for="price" class="form-label fw-semibold">Price (KES)</label>
            <input type="number" id="price" name="price"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price', $product->price) }}" required min="0" step="0.01">
            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label for="discount_price" class="form-label fw-semibold">Discount Price</label>
            <input type="number" id="discount_price" name="discount_price"
                   class="form-control @error('discount_price') is-invalid @enderror"
                   value="{{ old('discount_price', $product->discount_price) }}" min="0" step="0.01">
            @error('discount_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Stock -->
          <div class="col-md-6" id="stockSection">
            <label for="stock" class="form-label fw-semibold">Stock Quantity</label>
            <input type="number" id="stock" name="stock"
                   class="form-control @error('stock') is-invalid @enderror"
                   value="{{ old('stock', $product->stock) }}" min="0" step="1">
            @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Digital File -->
          <div class="col-md-6" id="digitalFileSection" style="display:none;">
            <label for="digital_file" class="form-label fw-semibold">Digital File</label>
            <input type="file" id="digital_file" name="digital_file"
                   class="form-control @error('digital_file') is-invalid @enderror"
                   accept=".zip,.pdf,.mp3,.mp4,.docx,.xlsx,.pptx">
            @error('digital_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <!-- Shipping Profiles -->
          <div class="col-12" id="shippingProfilesSection" style="display:none;">
            <label class="form-label fw-semibold">Shipping Profiles</label>
            <div class="row gy-3">
              @foreach($shippingProfiles as $profile)
                <div class="col-md-6">
                  <div class="d-flex align-items-start border rounded p-3">
                    {{-- Checkbox --}}
                    <div class="form-check me-3 mt-1">
                      <input class="form-check-input" type="checkbox"
                             name="shipping_profiles[]" value="{{ $profile->id }}"
                             id="sp_{{ $profile->id }}"
                             {{ in_array($profile->id, old('shipping_profiles',$assignedProfiles??[]))?'checked':'' }}>
                    </div>
                    {{-- Details --}}
                    <div class="flex-grow-1">
                      <label class="form-check-label fw-semibold" for="sp_{{ $profile->id }}">
                        {{ $profile->name }}
                      </label>
                      <div class="small text-muted">
                        KES {{ number_format($profile->base_rate,2) }} · 
                        {{ $profile->delivery_days }} day{{ $profile->delivery_days>1?'s':'' }}
                        @if($profile->pickup_available)
                          <span class="badge bg-success ms-2">Pickup</span>
                        @endif
                      </div>
                    </div>
                    {{-- Radio --}}
                    <div class="form-check ms-3 mt-1">
                      <input class="form-check-input" type="radio"
                             name="default_shipping_profile"
                             value="{{ $profile->id }}"
                             id="default_{{ $profile->id }}"
                             {{ old('default_shipping_profile',$defaultProfileId)==$profile->id?'checked':'' }}>
                      <label class="form-check-label small" for="default_{{ $profile->id }}">
                        Default
                      </label>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
            @error('shipping_profiles')        <div class="text-danger mt-2">{{ $message }}</div> @enderror
            @error('default_shipping_profile') <div class="text-danger mt-2">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      <div class="d-grid mb-5">
        <button type="submit" class="btn btn-success btn-lg rounded-pill">
          <i class="fas fa-save me-1"></i> Save Changes
        </button>
      </div>
    </form>

    {{-- Existing Digital Files --}}
    @if($product->digitalFiles->count())
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">Current Digital Files</h5>
        </div>
        <ul class="list-group list-group-flush">
          @foreach($product->digitalFiles as $file)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="{{ route('digital-files.download', $file) }}" target="_blank">
                <i class="fas fa-file-download me-2"></i>{{ $file->filename }}
              </a>
              <form action="{{ route('digital-files.destroy',$file) }}" method="POST" onsubmit="return confirm('Delete this file?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Existing Images --}}
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-light">
        <h5 class="mb-0">Current Images</h5>
      </div>
      <div class="card-body">
        @if($product->media->count())
          <div class="row g-3">
            @foreach($product->media as $media)
              <div class="col-6 col-sm-4 col-md-3">
                <div class="card">
                  <img src="{{ asset('storage/'.$media->url) }}" class="card-img-top"
                       style="height:140px;object-fit:cover;"
                       data-bs-toggle="modal" data-bs-target="#imageModal"
                       data-img-url="{{ asset('storage/'.$media->url) }}">
                  <div class="card-footer text-center py-2">
                    <form action="{{ route('media.destroy',$media) }}" method="POST" onsubmit="return confirm('Remove image?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                      </button>
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

    {{-- Upload & Preview New Images --}}
    <div x-data="imageUploadSortable()" class="card shadow-sm">
      <div class="card-header bg-light">
        <h5 class="mb-0">Upload & Preview New Images</h5>
      </div>
      <form action="{{ route('media.upload',$product) }}" method="POST" enctype="multipart/form-data" class="card-body">
        @csrf
        <div class="border border-dashed rounded-3 py-5 text-center mb-4"
             @click="$refs.fileInput.click()" @drop.prevent="handleDrop" @dragover.prevent
             style="cursor:pointer;">
          <p class="text-muted mb-0">Drag & drop images here or click to select</p>
          <input type="file" multiple accept="image/*" class="d-none" x-ref="fileInput"
                 @change="handleFiles($event.target.files)" name="media[]">
        </div>

        <template x-if="previews.length">
          <div class="row g-3 mb-4" id="previewList">
            <template x-for="(file,i) in previews" :key="file.id">
              <div class="col-6 col-sm-4 col-md-3">
                <div class="position-relative rounded overflow-hidden" style="height:140px;">
                  <img :src="file.url" class="w-100 h-100 object-fit-cover">
                  <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                          @click.prevent="removeFile(i)">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </template>
          </div>
        </template>

        <div class="d-grid">
          <button class="btn btn-primary rounded-pill">
            <i class="fas fa-upload me-1"></i> Upload Images
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

{{-- Image Preview Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 bg-transparent">
      <div class="modal-body p-0">
        <img src="" id="modalImage" class="w-100 rounded">
      </div>
      <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
  // TinyMCE
  tinymce.init({
    selector: '#description',
    plugins: 'image link media code fullscreen',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | image link media | code fullscreen',
    menubar: false,
    height: 300
  });

  // Toggle sections
  document.getElementById('type').addEventListener('change', function(){
    const show = { digital: ['digitalFileSection'], physical: ['stockSection','shippingProfilesSection'] };
    ['stockSection','digitalFileSection','shippingProfilesSection'].forEach(id=>{
      document.getElementById(id).style.display = show[this.value]?.includes(id) ? 'block' : 'none';
    });
  });
  window.addEventListener('DOMContentLoaded', ()=> {
    document.getElementById('type').dispatchEvent(new Event('change'));
  });

  // Auto-default when checkbox toggled
  document.addEventListener('change', e=>{
    if(!e.target.matches('input[name="shipping_profiles[]"]')) return;
    const id = e.target.value;
    if(e.target.checked){
      document.getElementById('default_'+id).checked = true;
    } else if(document.getElementById('default_'+id).checked){
      const next = document.querySelector('input[name="shipping_profiles[]"]:checked');
      if(next) document.getElementById('default_'+next.value).checked = true;
    }
  });

  // Alpine.js: Image Upload + Sortable
  function imageUploadSortable(){
    return {
      previews: [], idCounter:0, sortable:null,
      handleFiles(files){ Array.from(files).forEach(f=> this.previewFile(f)); },
      previewFile(file){
        let reader=new FileReader();
        reader.onload=e=>{
          this.previews.push({id:this.idCounter++,url:e.target.result,fileObject:file});
          this.$nextTick(()=> this.initSortable());
        };
        reader.readAsDataURL(file);
      },
      removeFile(i){ this.previews.splice(i,1); },
      handleDrop(e){ if(e.dataTransfer.files.length) this.handleFiles(e.dataTransfer.files); },
      initSortable(){
        if(this.sortable) this.sortable.destroy();
        this.sortable = Sortable.create(document.getElementById('previewList'), {
          animation:150,
          onEnd:evt=>{
            let m=this.previews.splice(evt.oldIndex,1)[0];
            this.previews.splice(evt.newIndex,0,m);
          }
        });
      }
    }
  }
</script>
@endpush
@endsection
