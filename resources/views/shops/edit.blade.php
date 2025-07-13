{{-- resources/views/shops/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content">
    <h2 class="text-center fw-bold mb-5">Edit Your Shop</h2>

    {{-- Flash Success --}}
    @if(session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form 
      action="{{ route('seller.shops.update', $shop) }}" 
      method="POST" 
      enctype="multipart/form-data"
      x-data="{ 
        name: '{{ old('name', $shop->name) }}', 
        slug: '{{ old('slug', $shop->slug) }}' 
      }"
      @input.debounce.300ms="
        slug = name.toLowerCase()
                   .trim()
                   .replace(/[^a-z0-9]+/g,'-')
                   .replace(/(^-|-$)/g,'');
      "
    >
      @csrf
      @method('PATCH')

      {{-- 1) Shop Preferences --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">1. Shop Preferences</div>
        <div class="card-body row g-3">
          <div class="col-md-4">
            <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
            <select name="language" id="language" required class="form-select">
              <option value="" disabled selected>Select language</option>
              <option value="English" {{ old('language', $shop->language)=='English'?'selected':'' }}>English</option>
              <option value="Swahili" {{ old('language', $shop->language)=='Swahili'?'selected':'' }}>Swahili</option>
            </select>
            <div class="invalid-feedback">Please select a language.</div>
          </div>
          <div class="col-md-4">
            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
            <select name="country" id="country" required class="form-select">
              <option value="" disabled selected>Select country</option>
              @foreach($countries as $country)
                <option value="{{ $country->id }}" {{ old('country', $shop->country)==$country->id?'selected':'' }}>{{ $country->name }}</option>
              @endforeach
            </select>
            <div class="invalid-feedback">Please select a country.</div>
          </div>
          <div class="col-md-4">
            <label for="currency" class="form-label">
              Currency <span class="text-danger">*</span>
            </label>
            <select
              id="currency"
              name="currency"
              class="form-select @error('currency') is-invalid @enderror"
              required
            >
              <option value="" disabled {{ old('currency', $shop->currency) ? '' : 'selected' }}>
                Select a currency
              </option>
              @foreach(currencies() as $code => $name)
                <option
                  value="{{ $code }}"
                  {{ old('currency', $shop->currency) === $code ? 'selected' : '' }}
                >
                  ({{ $code }})
                </option>
              @endforeach
            </select>
            @error('currency')
              <div class="invalid-feedback">{{ $message }}</div>
            @else
              <div class="invalid-feedback">Please select a currency.</div>
            @enderror
          </div>

        </div>
      </div>

      {{-- 2) Name & Slug --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">2. Name Your Shop</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="name" class="form-label">Shop Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" x-model="name" class="form-control" required>
          </div>
          <div>
            <label for="slug" class="form-label">Slug (URL Identifier)</label>
            <div class="input-group">
              <span class="input-group-text">{{ url('shop') }}/</span>
              <input type="text" id="slug" name="slug" x-model="slug" class="form-control" required>
            </div>
            <div class="form-text">You may customize, but it must be unique.</div>
          </div>
        </div>
      </div>

      {{-- 3) Shop Description --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">3. Describe Your Shop</div>
        <div class="card-body">
          <!-- <div class="mb-3">
            <label for="bio" class="form-label">Shop Description <span class="text-danger">*</span></label>
            <textarea id="bio" name="bio" class="form-control" rows="4" required>{{ old('bio', $shop->bio) }}</textarea>
            <div class="form-text">Tell customers about your shop and what makes it special.</div>
          </div> -->
          <div class="col-12">
            <label for="bio" class="form-label fw-semibold">Shop Description</label>
            <textarea id="bio" name="bio"
                      class="form-control @error('bio') is-invalid @enderror"
                      rows="6">{{ old('bio', $shop->bio) }}</textarea>
            @error('bio') <div class="text-danger mt-1">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      {{-- 3b) Shop Announcement --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">3b. Shop Announcement</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="announcement" class="form-label">Shop Announcement</label>
            <textarea id="bio" name="announcement" class="form-control" rows="2">{{ old('announcement', $shop->announcement) }}</textarea>
            <div class="form-text">This announcement will appear at the top of your shop page.</div>
          </div>
        </div>
      </div>

      {{-- 3c) Shop Policies --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">3c. Shop Policies</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="policies" class="form-label">Shop Policies</label>
            <textarea id="bio" name="policies" class="form-control" rows="3">{{ old('policies', $shop->policies) }}</textarea>
            <div class="form-text">Describe your shop's return, shipping, and other important policies.</div>
          </div>
        </div>
      </div>

      {{-- 4) Billing Info --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">4. Share Your Billing Info</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
            <input type="text" id="address" name="address" value="{{ old('address', $shop->address) }}" class="form-control" required>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="city" class="form-label">City <span class="text-danger">*</span></label>
              <input type="text" id="city" name="city" value="{{ old('city', $shop->city) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="postal" class="form-label">Postal Code <span class="text-danger">*</span></label>
              <input type="text" id="postal" name="postal" value="{{ old('postal', $shop->postal) }}" class="form-control" required>
            </div>
          </div>
        </div>
      </div>

      {{-- 5) Security --}}
      <div class="card mb-4">
        <div class="card-header fw-semibold">5. Your Shop Security</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="password" class="form-label">Confirm Your Password <span class="text-danger">*</span></label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your account password">
          </div>
          <input type="hidden" name="enable_2fa" value="0">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="enable_2fa" name="enable_2fa" {{ old('enable_2fa', $shop->enable_2fa) ? 'checked' : '' }}>
            <label class="form-check-label" for="enable_2fa">Enable two-factor authentication</label>
          </div>

          <div class="mt-4">
            <label for="logo" class="form-label">Logo (optional)</label>
            <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
            @if($shop->logo_url)
              <div class="mt-2">
                <img src="{{ $shop->logo_url }}" alt="logo" class="rounded-circle" width="50" height="50">
              </div>
            @endif
          </div>

          <div class="mt-4">
            <label for="featured_image" class="form-label">Featured Image (optional)</label>
            <input class="form-control" type="file" id="featured_image" name="featured_image" accept="image/*">
            <div class="form-text">This image will be displayed prominently on your shop page. Recommended size: 1200x400 pixels.</div>
            @if($shop->featured_image)
              <div class="mt-2">
                <img src="{{ asset('storage/' . $shop->featured_image) }}" alt="featured image" class="img-fluid rounded" style="max-width: 300px; max-height: 150px; object-fit: cover;">
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Submit --}}
      <div class="text-end">
        <a href="{{ route('seller.shops.show', $shop) }}" class="btn btn-secondary px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
      </div>
    </form>
</div>

<!-- Ensure jQuery is loaded before Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('#currency').select2({
    placeholder: 'Select a currency',
    width: '100%',
    minimumResultsForSearch: 0 // Always show search box
  });
  $('#country').select2({
    placeholder: 'Select country',
    width: '100%',
    minimumResultsForSearch: 0 // Always show search box
  });
});
</script>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
  // TinyMCE
  tinymce.init({
    selector: '#bio',
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

@endsection
