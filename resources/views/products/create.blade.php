@extends('layouts.app')

@section('content')
<div class="content">
  <h2 class="text-center mb-4">Add New Product</h2>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Name & Slug --}}
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label for="name" class="form-label">Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value="{{ old('name') }}"
          class="form-control"
          required
        >
      </div>
      <div class="col-md-6">
        <label for="slug" class="form-label">Slug</label>
        <input
          type="text"
          id="slug"
          name="slug"
          value="{{ old('slug') }}"
          class="form-control"
          readonly
        >
        <div class="form-text">URL: <code>{{ url('products') }}/<span id="slug-preview"></span></code></div>
      </div>
    </div>

    {{-- Product Details --}}
    <div class="card mb-4">
      <div class="card-header">Product Details</div>
      <div class="card-body">
        <div class="mb-3">
          <label for="category_id" class="form-label">Category</label>
          <select id="category_id" name="category_id" class="form-select">
            <option value="">— Select Category —</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected(old('category_id')==$cat->id)>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="product_type" class="form-label">Product Type</label>
            <select id="product_type" name="product_type" class="form-select" required>
              <option value="">— Select Type —</option>
              <option value="physical" @selected(old('product_type')=='physical')>Physical</option>
              <option value="digital"  @selected(old('product_type')=='digital')>Digital</option>
            </select>
          </div>
          <div class="col-md-6">
            <label for="condition" class="form-label">Condition</label>
            <select id="condition" name="condition" class="form-select" required>
              <option value="">— Select Condition —</option>
              <option value="new"         @selected(old('condition')=='new')>New</option>
              <option value="refurbished" @selected(old('condition')=='refurbished')>Refurbished</option>
              <option value="used"        @selected(old('condition')=='used')>Used</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    {{-- Pricing & Inventory --}}
    <div class="card mb-4">
      <div class="card-header">Pricing & Inventory</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="price" class="form-label">Price (KES)</label>
            <input
              type="number"
              id="price"
              name="price"
              value="{{ old('price') }}"
              class="form-control"
              min="0"
              step="0.01"
              required
            >
          </div>
          <div class="col-md-6">
            <label for="discount_price" class="form-label">Discount Price (KES)</label>
            <input
              type="number"
              id="discount_price"
              name="discount_price"
              value="{{ old('discount_price') }}"
              class="form-control"
              min="0"
              step="0.01"
              required
            >
          </div>
          <div class="col-md-6">
            <label for="stock" class="form-label">Stock</label>
            <input
              type="number"
              id="stock"
              name="stock"
              value="{{ old('stock',0) }}"
              class="form-control"
              min="0"
              required
            >
          </div>
          <div class="col-md-6">
            <label for="low_stock" class="form-label">Low Stock Alert</label>
            <input
              type="number"
              id="low_stock"
              name="low_stock"
              value="{{ old('low_stock',0) }}"
              class="form-control"
              min="0"
              required
            >
          </div>
        </div>
      </div>
    </div>

    {{-- Variants & Customization --}}
    <div class="card mb-4">
      <div class="card-header">Variants & Customization</div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Sizes (comma-separated)</label>
          <input id="sizes" class="form-control"> 
        </div>
        <div class="mb-3">
          <label class="form-label">Colors</label>
          <div class="d-flex flex-wrap gap-2">
            @php
              $colors = [
                'Red'=>'#f00','Blue'=>'#00f','Black'=>'#000','White'=>'#fff',
                'Green'=>'#080','Yellow'=>'#ff0','Purple'=>'#800','Orange'=>'#fa0'
              ];
            @endphp
            @foreach($colors as $n=>$h)
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="color_checkboxes[]" value="{{ $n }}" id="color-{{ $n }}">
                <label class="form-check-label" for="color-{{ $n }}">
                  <span class="d-inline-block border rounded-circle" style="width:1rem;height:1rem;background:{{ $h }};"></span>
                  {{ $n }}
                </label>
              </div>
            @endforeach
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Materials (comma-separated)</label>
          <input id="materials" class="form-control">
        </div>
        <button type="button" id="generate-variants" class="btn btn-primary mb-3">Generate Variants</button>
        <div id="variants-table-wrapper"></div>
      </div>
    </div>

    {{-- Digital Fields (collapse) --}}
    <div class="card mb-4 collapse" id="digital-fields">
      <div class="card-header">Digital Product Details</div>
      <div class="card-body">
        <div class="mb-3">
          <label for="download_file" class="form-label">Downloadable File</label>
          <input type="file" id="download_file" name="download_file" class="form-control" accept=".zip,.pdf,.mp3,.mp4">
        </div>
        <div class="mb-3">
          <label for="download_limit" class="form-label">Download Limit</label>
          <input type="number" id="download_limit" name="download_limit" class="form-control" min="1">
          <div class="form-text">Max times buyer can download.</div>
        </div>
        <div class="mb-3">
          <label for="access_expiry" class="form-label">Access Expiry (days)</label>
          <input type="number" id="access_expiry" name="access_expiry" class="form-control" min="1">
          <div class="form-text">Days link remains active.</div>
        </div>
      </div>
    </div>

    {{-- Description --}}
    <div class="card mb-4">
      <div class="card-body">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" class="form-control" rows="6">{{ old('description') }}</textarea>
        @error('description')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>
    </div>

    {{-- Status & Images --}}
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select" required>
              <option value="draft"    @selected(old('status')=='draft')>Draft</option>
              <option value="active"   @selected(old('status')=='active')>Active</option>
              <option value="archived" @selected(old('status')=='archived')>Archived</option>
            </select>
          </div>
          <div class="col-md-6">
            <label for="images" class="form-label">Product Images</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*" class="form-control">
          </div>
        </div>
      </div>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-success btn-lg">Save Product</button>
    </div>
  </form>
</div>

{{-- TinyMCE --}}
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
tinymce.init({
  selector: '#description',
  plugins: 'image link media code fullscreen',
  toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | image link media | code fullscreen',
  menubar: false,
  height: 300
});
</script>

<script>
// Slug generation
document.getElementById('name').addEventListener('input', function(){
  let s = this.value.toLowerCase()
    .replace(/[^a-z0-9]+/g,'-')
    .replace(/^-+|-+$/g,'');
  document.getElementById('slug').value = s;
  document.getElementById('slug-preview').textContent = s;
});

// Toggle digital fields
document.getElementById('product_type').addEventListener('change', function(){
  let digi = new bootstrap.Collapse(document.getElementById('digital-fields'), {
    toggle: this.value==='digital'
  });
});

// Cartesian helper
function cartesian(arr){return arr.reduce((a,b)=>a.flatMap(x=>b.map(y=>x.concat([y]))),[[]]);}

// Generate variants
document.getElementById('generate-variants').addEventListener('click', function(){
  let sizes = document.getElementById('sizes').value.split(',').map(s=>s.trim()).filter(Boolean);
  let colors = Array.from(document.querySelectorAll('input[name="color_checkboxes[]"]:checked')).map(i=>i.value);
  let materials = document.getElementById('materials').value.split(',').map(s=>s.trim()).filter(Boolean);
  let opts=[sizes,colors,materials].filter(o=>o.length);
  if(!opts.length){
    document.getElementById('variants-table-wrapper').innerHTML = '<div class="text-danger">Enter at least one option.</div>';
    return;
  }
  let variants = cartesian(opts);
  let html = `<table class="table table-bordered mb-3"><thead><tr>`;
  if(sizes.length)    html += '<th>Size</th>';
  if(colors.length)   html += '<th>Color</th>';
  if(materials.length)html += '<th>Material</th>';
  html += '<th>SKU</th><th>Price</th><th>Image</th></tr></thead><tbody>';
  variants.forEach((v,i)=>{
    html += '<tr>';
    if(sizes.length)    html += `<td><input type="hidden" name="variants[${i}][size]"   value="${v[0]||''}">${v[0]||''}</td>`;
    if(colors.length)   html += `<td><input type="hidden" name="variants[${i}][color]"  value="${v[sizes.length]||''}">${v[sizes.length]||''}</td>`;
    if(materials.length)html += `<td><input type="hidden" name="variants[${i}][material]"value="${v[sizes.length+colors.length]||''}">${v[sizes.length+colors.length]||''}</td>`;
    html += `<td><em>Auto</em></td>
             <td><input type="number" name="variants[${i}][price]" class="form-control" required></td>
             <td><input type="file" name="variants[${i}][image]" accept="image/*" class="form-control"></td>`;
    html += '</tr>';
  });
  html += '</tbody></table>';
  document.getElementById('variants-table-wrapper').innerHTML = html;
});
</script>
@endsection
