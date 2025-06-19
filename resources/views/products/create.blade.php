@extends('layouts.app')

@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Create New Product</h2>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back to Products
    </a>
  </div>

  {{-- Flash Messages --}}
  @foreach (['success','info','warning','danger'] as $msg)
    @if(session()->has($msg))
      <div class="alert alert-{{ $msg }} alert-dismissible fade show" role="alert">
        {{ session($msg) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  {{-- Validation Errors --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <strong><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</strong>
      <ul class="mt-2 mb-0 ps-3">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card shadow-sm rounded-4">
    <div class="card-header bg-success text-white rounded-top-4">
      <h4 class="mb-0">New Product Details</h4>
    </div>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data"
          x-data="imageUploadSortable()" @submit.prevent="$el.submit()">
      @csrf
      <div class="card-body p-4">
        {{-- Name --}}
        <div class="mb-3">
          <label for="name" class="form-label fw-semibold">Product Name</label>
          <input type="text" id="name" name="name"
                 class="form-control form-control-lg @error('name') is-invalid @enderror"
                 value="{{ old('name') }}" placeholder="e.g., Handmade Wooden Spoon" required>
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Type --}}
        <div class="mb-3">
          <label for="type" class="form-label fw-semibold">Product Type</label>
          <select id="type" name="type"
                  class="form-select form-select-lg @error('type') is-invalid @enderror" required>
            <option value="">Select type</option>
            <option value="physical" @selected(old('type')=='physical')>Physical</option>
            <option value="digital"  @selected(old('type')=='digital')>Digital Download</option>
            <option value="service"  @selected(old('type')=='service')>Service</option>
          </select>
          @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Category --}}
        <div class="mb-3">
          <label for="category_id" class="form-label fw-semibold">Category</label>
          <select id="category_id" name="category_id"
                  class="form-select form-select-lg @error('category_id') is-invalid @enderror" required>
            <option value="">Choose a category</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected(old('category_id')==$cat->id)>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
          @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Description --}}
        <div class="mb-4">
          <label for="description" class="form-label fw-semibold">Description</label>
          <textarea id="description" name="description"
                    class="form-control @error('description') is-invalid @enderror"
                    rows="6" placeholder="Write a compelling product description...">{{ old('description') }}</textarea>
          @error('description') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Price & Discount --}}
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label for="price" class="form-label fw-semibold">Price (KES)</label>
            <input type="number" id="price" name="price"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price') }}" placeholder="e.g., 2500" min="0" step="0.01" required>
            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label for="discount_price" class="form-label fw-semibold">Discount Price (Optional)</label>
            <input type="number" id="discount_price" name="discount_price"
                   class="form-control @error('discount_price') is-invalid @enderror"
                   value="{{ old('discount_price') }}" placeholder="e.g., 2000" min="0" step="0.01">
            @error('discount_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Stock --}}
        <div class="mb-4" id="stockSection">
          <label for="stock" class="form-label fw-semibold">Stock Quantity</label>
          <input type="number" id="stock" name="stock"
                 class="form-control @error('stock') is-invalid @enderror"
                 value="{{ old('stock') }}" placeholder="e.g., 10" min="0" step="1">
          @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Digital File --}}
        <div class="mb-4" id="digitalFileSection" style="display:none;">
          <label for="digital_file" class="form-label fw-semibold">Upload Digital File</label>
          <input type="file" id="digital_file" name="digital_file"
                 class="form-control @error('digital_file') is-invalid @enderror"
                 accept=".zip,.pdf,.mp3,.mp4,.docx,.xlsx,.pptx">
          <div class="form-text">Max size: 10MB</div>
          @error('digital_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Shipping Profiles --}}
        <div class="mb-4" id="shippingProfilesSection" style="display:none;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-semibold">
              Shipping Profiles <small class="text-muted">(Select one or more)</small>
            </label>
            <button type="button"
                    class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#newProfileModal">
              <i class="fas fa-plus-circle me-1"></i> New Profile
            </button>
          </div>
          <div class="row gy-3">
            @foreach($shippingProfiles as $profile)
              <div class="col-md-6">
                <div class="d-flex align-items-start border rounded p-3">
                  {{-- Checkbox --}}
                  <div class="form-check me-3 mt-1">
                    <input class="form-check-input" type="checkbox"
                           name="shipping_profiles[]" value="{{ $profile->id }}"
                           id="sp_{{ $profile->id }}"
                           {{ in_array($profile->id, old('shipping_profiles', [])) ? 'checked' : '' }}>
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
                           {{ old('default_shipping_profile') == $profile->id ? 'checked' : '' }}>
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

        {{-- Image Upload & Preview --}}
        <div class="border rounded p-3 mb-4 text-center"
             @drop.prevent="handleDrop" @dragover.prevent
             @click="$refs.fileInput.click()" style="cursor:pointer;">
          <p class="mb-0 text-muted">Drag & drop images here or click to select</p>
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
        <template x-if="!previews.length">
          <p class="text-muted mb-4">No images selected yet.</p>
        </template>

        {{-- Submit --}}
        <div class="d-grid">
          <button type="submit" class="btn btn-success btn-lg rounded-pill">
            <i class="fas fa-check-circle me-2"></i> Publish Product
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- New Profile Modal --}}
<div class="modal fade" id="newProfileModal" tabindex="-1" aria-labelledby="newProfileLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('shipping-profiles.store') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" id="newProfileLabel">Add Shipping Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{-- Profile Name & Country --}}
        <div class="mb-3">
          <label for="profile_name" class="form-label">Name</label>
          <input type="text" id="profile_name" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="country" class="form-label">Country (ISO Code)</label>
          <input type="text" id="country" name="country" maxlength="3"
                 class="form-control @error('country') is-invalid @enderror"
                 value="{{ old('country','KE') }}" required>
          @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
          <div class="form-text">2–3 letter ISO code (e.g. KE, UG, TZ).</div>
        </div>
        {{-- Rate & Days --}}
        <div class="row g-3">
          <div class="col-md-6">
            <label for="base_rate" class="form-label">Base Rate (KES)</label>
            <input type="number" id="base_rate" name="base_rate" class="form-control" min="0" step="0.01" required>
          </div>
          <div class="col-md-6">
            <label for="delivery_days" class="form-label">Delivery Days</label>
            <input type="number" id="delivery_days" name="delivery_days" class="form-control" min="1" required>
          </div>
        </div>
        <div class="form-check form-switch mt-3">
          <input class="form-check-input" type="checkbox" id="pickup_available" name="pickup_available">
          <label class="form-check-label" for="pickup_available">Pickup Available</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Profile</button>
      </div>
    </form>
  </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
  // TinyMCE init
  tinymce.init({
    selector: '#description',
    plugins: 'image link media code fullscreen',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | image link media | code fullscreen',
    menubar: false, height: 300
  });

  // Toggle physical/digital sections
  document.getElementById('type').addEventListener('change', function(){
    const phys = this.value==='physical',
          digi = this.value==='digital';
    document.getElementById('stockSection').style.display            = phys ? 'block':'none';
    document.getElementById('shippingProfilesSection').style.display = phys ? 'block':'none';
    document.getElementById('digitalFileSection').style.display      = digi ? 'block':'none';
  });
  window.addEventListener('DOMContentLoaded', ()=> {
    document.getElementById('type').dispatchEvent(new Event('change'));
  });

  // When a checkbox is toggled, auto-select it as default
  document.addEventListener('change', function(e){
    if(e.target.matches('input[name="shipping_profiles[]"]')){
      const id = e.target.value;
      // if checked, make it default
      if(e.target.checked){
        document.getElementById('default_'+id).checked = true;
      } else {
        // if unchecking the one that was default, pick another
        if(document.getElementById('default_'+id).checked){
          const next = document.querySelector('input[name="shipping_profiles[]"]:checked');
          if(next){
            document.getElementById('default_'+next.value).checked = true;
          }
        }
      }
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
          this.previews.push({id:this.idCounter++, url:e.target.result, fileObject:file});
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
          onEnd: evt=>{
            let m=this.previews.splice(evt.oldIndex,1)[0];
            this.previews.splice(evt.newIndex,0,m);
          }
        });
      },
      prepareFiles(){ this.$el.submit(); }
    }
  }
</script>
@endsection
