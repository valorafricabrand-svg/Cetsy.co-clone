{{-- resources/views/products/create.blade.php --}}
@extends('layouts.app')

@section('content')
@php
  $firstProfileId       = $shippingProfiles->first()->id ?? null;
  $selectedProfiles     = old('shipping_profiles', $firstProfileId ? [$firstProfileId] : []);
  $defaultShippingProfile = old('default_shipping_profile', $firstProfileId);
@endphp

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Create New Listing</h2>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back to Listings
    </a>
  </div>

  {{-- Flash --}}
  @foreach (['success','info','warning','danger'] as $msg)
    @if(session()->has($msg))
      <div class="alert alert-{{ $msg }} alert-dismissible fade show">
        {{ session($msg) }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  {{-- Validation --}}
  @if($errors->any())
    <div class="alert alert-danger">
      <strong><i class="fas fa-exclamation-circle me-1"></i>Please fix:</strong>
      <ul class="mt-2 mb-0 ps-3">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card shadow-sm rounded-4">
    <div class="card-header bg-success text-white rounded-top-4">
      <h4 class="mb-0">Listing Details</h4>
    </div>

    <form  action="{{ route('products.store') }}"
           method="POST"
           enctype="multipart/form-data"
           x-data="listingForm()"
           @submit.prevent="$el.submit()">
      @csrf
      <div class="card-body p-4">
        {{-- NAME --}}
        <div class="mb-3">
          <label class="form-label fw-semibold">Listing Name</label>
          <input  type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror"
                  value="{{ old('name') }}" placeholder="e.g. Handmade Wooden Spoon" required>
          @error('name') <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- TYPE --}}
        <div class="mb-3">
          <label class="form-label fw-semibold">Listing Type</label>
          <select name="type" id="type"
                  class="form-select form-select-lg @error('type') is-invalid @enderror"
                  x-on:change="toggleType" required>
            <option value="">Select type</option>
            <option value="physical" @selected(old('type')=='physical')>Physical</option>
            <option value="digital"  @selected(old('type')=='digital')>Digital Download</option>
            <option value="service"  @selected(old('type')=='service')>Service</option>
          </select>
          @error('type') <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- CATEGORY (triggers attribute template fetch) --}}
        <div class="mb-3">
          <label class="form-label fw-semibold">Category</label>
          <select  name="category_id" id="category_id" class="form-select form-select-lg @error('category_id') is-invalid @enderror"
                   x-on:change="loadTemplate" required>
            <option value="">Choose category</option>
            @foreach($categories as $parent)
              @if($parent->children->isNotEmpty())
                <optgroup label="{{ $parent->name }}">
                  @foreach($parent->children as $child)
                    <option value="{{ $child->id }}" @selected(old('category_id')==$child->id)>
                      {{ $child->name }}
                    </option>
                  @endforeach
                </optgroup>
              @endif
            @endforeach
          </select>
          @error('category_id') <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- ATTRIBUTE PICKER (auto-rendered) --}}
        <template x-if="template.length">
          <div class="mb-4 border rounded p-3">
            <h6 class="fw-semibold mb-3">Options</h6>
            <template x-for="attr in template" :key="attr.id">
              <div class="mb-3">
                <label class="form-label" x-text="attr.name"></label>
                <select class="form-select" x-model="selected[attr.id]">
                  <option value="">Choose &hellip;</option>
                  <template x-for="val in attr.values" :key="val.id">
                    <option :value="val.id" x-text="val.value"></option>
                  </template>
                </select>
              </div>
            </template>
            <button type="button" class="btn btn-outline-primary btn-sm" @click="addVariation">
              <i class="fas fa-plus me-1"></i>Add Variation
            </button>
          </div>
        </template>

        {{-- VARIATION TABLE --}}
        <template x-if="variations.length">
          <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <template x-for="attr in template" :key="attr.id">
                    <th x-text="attr.name"></th>
                  </template>
                  <th>SKU</th><th>Price</th><th>Stock</th><th></th>
                </tr>
              </thead>
              <tbody>
                <template x-for="(v,i) in variations" :key="v.key">
                  <tr>
                    <template x-for="valId in v.valueIds" :key="valId">
                      <td x-text="lookup[valId]"></td>
                    </template>
                    <td><input  type="text" class="form-control" :name="`variations[${i}][sku]`" required></td>
                    <td><input  type="number" step="0.01" min="0" class="form-control" :name="`variations[${i}][price]`" required></td>
                    <td><input  type="number" step="1" min="0" class="form-control" :name="`variations[${i}][stock]`" required></td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-outline-danger" @click="variations.splice(i,1)">
                        &times;
                      </button>
                      <!-- hidden valueId[] -->
                      <template x-for="valId in v.valueIds" :key="valId">
                        <input type="hidden" :name="`variations[${i}][values][]`" :value="valId">
                      </template>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </template>

        {{-- DESCRIPTION --}}
        <div class="mb-4">
          <label class="form-label fw-semibold">Description</label>
          <textarea id="description" name="description" rows="6"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Write a compelling description…">{{ old('description') }}</textarea>
          @error('description') <div class="text-danger mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- PRICE / DISCOUNT --}}
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Base Price ({{ get_currency() }})</label>
            <input type="number" name="price" step="0.01" min="0"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price') }}" required>
            @error('price') <div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Discount Price</label>
            <input type="number" name="discount_price" step="0.01" min="0"
                   class="form-control @error('discount_price') is-invalid @enderror"
                   value="{{ old('discount_price') }}">
            @error('discount_price') <div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- STOCK (hidden for service/digital) --}}
        <div id="stockSection" class="mb-4">
          <label class="form-label fw-semibold">Stock Quantity</label>
          <input type="number" name="stock" min="0" step="1"
                 class="form-control @error('stock') is-invalid @enderror"
                 value="{{ old('stock') }}">
          @error('stock') <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- DIGITAL FILE --}}
        <div id="digitalFileSection" style="display:none;" class="mb-4">
          <label class="form-label fw-semibold">Upload Digital File</label>
          <input type="file" name="digital_file" accept=".zip,.pdf,.mp3,.mp4,.docx,.xlsx,.pptx"
                 class="form-control @error('digital_file') is-invalid @enderror">
          <div class="form-text">Max 10 MB</div>
          @error('digital_file') <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- COUNTRY --}}
        <div class="mb-3">
          <label class="form-label fw-semibold">Country of origin</label>
          <select name="country_id"
                  class="form-select form-select-lg @error('country_id') is-invalid @enderror" required>
            <option value="" disabled {{ old('country_id')=='' ? 'selected':'' }}>Choose a country</option>
            @foreach($countries as $country)
              <option value="{{ $country->id }}" @selected(old('country_id')==$country->id)>
                  {{ $country->name }}
              </option>
            @endforeach
          </select>
          @error('country_id') <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- SHIPPING PROFILES (physical only) --}}
        <div id="shippingProfilesSection" style="display:none;" class="mb-4">
          <div class="d-flex justify-content-between mb-2">
            <label class="form-label fw-semibold">Shipping Profiles <small class="text-muted">(select one or more)</small></label>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newProfileModal">
              <i class="fas fa-plus-circle me-1"></i>New Profile
            </button>
          </div>
          <div class="row gy-3">
            @foreach($shippingProfiles as $profile)
              <div class="col-md-6">
                <div class="d-flex p-3 border rounded align-items-start">
                  <div class="form-check me-3 mt-1">
                    <input  class="form-check-input"
                            type="checkbox"
                            name="shipping_profiles[]"
                            value="{{ $profile->id }}"
                            id="sp_{{ $profile->id }}"
                            {{ in_array($profile->id,$selectedProfiles)?'checked':'' }}>
                  </div>
                  <div class="flex-grow-1">
                    <label class="fw-semibold" for="sp_{{ $profile->id }}">{{ $profile->name }}</label>
                    <div class="small text-muted">
                      {{ get_currency() }} {{ number_format($profile->base_rate,2) }} ·
                      {{ $profile->delivery_days }} day{{ $profile->delivery_days>1?'s':'' }}
                      @if($profile->pickup_available)
                        <span class="badge bg-success ms-1">Pickup</span>
                      @endif
                    </div>
                  </div>
                  <div class="form-check ms-3 mt-1">
                    <input  class="form-check-input"
                            type="radio"
                            name="default_shipping_profile"
                            value="{{ $profile->id }}"
                            id="df_{{ $profile->id }}"
                            {{ $defaultShippingProfile==$profile->id?'checked':'' }}>
                    <label class="small" for="df_{{ $profile->id }}">Default</label>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
          @error('shipping_profiles') <div class="text-danger mt-2">{{ $message }}</div>@enderror
          @error('default_shipping_profile') <div class="text-danger mt-2">{{ $message }}</div>@enderror
        </div>

        {{-- IMAGE UPLOAD --}}
        <div class="border rounded p-3 mb-4 text-center" style="cursor:pointer;"
             @drop.prevent="handleDrop"
             @dragover.prevent
             @click="$refs.fileInput.click()">
          <p class="mb-0 text-muted">Drag & drop images here or click to select</p>
          <input  type="file" multiple accept="image/*" class="d-none" x-ref="fileInput"
                  @change="handleFiles($event.target.files)" name="media[]">
        </div>

        <template x-if="previews.length">
          <div class="row g-3 mb-4" id="previewList">
            <template x-for="(file,i) in previews" :key="file.id">
              <div class="col-6 col-sm-4 col-md-3">
                <div class="position-relative rounded overflow-hidden" style="height:140px;">
                  <img :src="file.url" class="w-100 h-100 object-fit-cover">
                  <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                          @click.prevent="removeFile(i)">&times;</button>
                </div>
              </div>
            </template>
          </div>
        </template>
        <template x-if="!previews.length">
          <p class="text-muted mb-4">No images selected yet.</p>
        </template>

        {{-- SUBMIT --}}
        <div class="d-grid">
          <button type="submit" class="btn btn-success btn-lg rounded-pill">
            <i class="fas fa-check-circle me-2"></i>Publish Product
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- NEW SHIPPING PROFILE MODAL --}}
@include('shipping_profiles._create_modal', ['countries'=>$countries])

{{-- LIBS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>

{{-- MAIN SCRIPT --}}
<script>
tinymce.init({
  selector:'#description',
  plugins:'image link media code fullscreen',
  toolbar:'undo redo | bold italic | alignleft aligncenter alignright | image link media | code fullscreen',
  menubar:false,height:300
});

function listingForm() {
  return {
    /* -------------- image sortable ------------ */
    previews:[],idCounter:0,sortable:null,
    handleFiles(files){[...files].forEach(f=>this.previewFile(f));},
    previewFile(file){
      const reader=new FileReader();
      reader.onload=e=>{
        this.previews.push({id:this.idCounter++,url:e.target.result,file});
        this.$nextTick(()=>this.initSortable());
      };
      reader.readAsDataURL(file);
    },
    removeFile(i){this.previews.splice(i,1);},
    handleDrop(e){this.handleFiles(e.dataTransfer.files);},
    initSortable(){
      if(this.sortable) this.sortable.destroy();
      this.sortable=Sortable.create(document.getElementById('previewList'),{
        animation:150,
        onEnd:evt=>{
          const m=this.previews.splice(evt.oldIndex,1)[0];
          this.previews.splice(evt.newIndex,0,m);
        }
      });
    },

    /* -------------- type toggle ------------ */
    toggleType(e){
      const v=e.target.value;
      $('#stockSection, #shippingProfilesSection').toggle(v==='physical');
      $('#digitalFileSection').toggle(v==='digital');
    },

    /* -------------- category attribute template ------------ */
    template:[],selected:{},lookup:{},variations:[],
    async loadTemplate(){
      this.template=[];this.selected={};this.variations=[];
      const catId=document.getElementById('category_id').value;
      if(!catId) return;
      const res=await fetch(`/categories/${catId}/attribute-template`);
      this.template=await res.json();
      // build lookup valueId => text
      this.lookup={};
      this.template.forEach(attr=>{
        attr.values.forEach(v=>{this.lookup[v.id]=v.value;});
      });
    },
    addVariation(){
      // ensure all attributes chosen
      const ids=Object.values(this.selected).filter(Boolean).map(Number);
      if(ids.length!==this.template.length) { alert('Select all option values first'); return; }
      // avoid duplicates
      if(this.variations.some(v=>JSON.stringify(v.valueIds)==JSON.stringify(ids))) return;
      this.variations.push({key:Date.now(),valueIds:[...ids]});
    }
  }
}

// auto-init type sections on load
document.addEventListener('DOMContentLoaded',()=>{
  document.getElementById('type').dispatchEvent(new Event('change'));
});
</script>
@endsection
