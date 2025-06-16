@extends('layouts.app')

@section('content')
<div class="content">
  <h2 class="text-center mb-4">Edit Product</h2>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('products.update', $product) }}"
        method="POST"
        enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Name & Slug --}}
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label for="name" class="form-label">Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value="{{ old('name', $product->name) }}"
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
          value="{{ old('slug', $product->slug) }}"
          class="form-control"
          readonly
        >
        <div class="form-text">
          URL: <code>{{ url('products') }}/<span id="slug-preview">{{ $product->slug }}</span></code>
        </div>
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
              <option value="{{ $cat->id }}"
                @selected(old('category_id', $product->category_id)==$cat->id)>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="product_type" class="form-label">Product Type</label>
            <select id="product_type" name="product_type" class="form-select" required>
              <option value="">— Choose —</option>
              <option value="physical"
                @selected(old('product_type', $product->product_type)=='physical')>
                Physical
              </option>
              <option value="digital"
                @selected(old('product_type', $product->product_type)=='digital')>
                Digital
              </option>
            </select>
          </div>
          <div class="col-md-6">
            <label for="condition" class="form-label">Condition</label>
            <select id="condition" name="condition" class="form-select" required>
              <option value="">— Choose —</option>
              <option value="new" @selected(old('condition', $product->condition)=='new')>New</option>
              <option value="refurbished" @selected(old('condition', $product->condition)=='refurbished')>Refurbished</option>
              <option value="used" @selected(old('condition', $product->condition)=='used')>Used</option>
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
            <input type="number" id="price" name="price"
                   value="{{ old('price', $product->price) }}"
                   class="form-control" step="0.01" min="0" required>
          </div>
          <div class="col-md-6">
            <label for="discount_price" class="form-label">Discount Price</label>
            <input type="number" id="discount_price" name="discount_price"
                   value="{{ old('discount_price', $product->discount_price) }}"
                   class="form-control" step="0.01" min="0" required>
          </div>
          <div class="col-md-6">
            <label for="stock" class="form-label">Stock</label>
            <input type="number" id="stock" name="stock"
                   value="{{ old('stock', $product->stock) }}"
                   class="form-control" min="0" required>
          </div>
          <div class="col-md-6">
            <label for="low_stock" class="form-label">Low Stock Alert</label>
            <input type="number" id="low_stock" name="low_stock"
                   value="{{ old('low_stock', $product->low_stock) }}"
                   class="form-control" min="0" required>
          </div>
        </div>
      </div>
    </div>

    {{-- Edit Existing Variants --}}
    @if(isset($variants) && $variants->count())
      <div class="card mb-4">
        <div class="card-header">Existing Variants</div>
        <div class="card-body p-0">
          <table class="table table-bordered mb-0">
            <thead>
              <tr>
                <th>Size</th><th>Color</th><th>Material</th><th>Price</th><th>Image</th>
              </tr>
            </thead>
            <tbody>
              @foreach($variants as $i => $var)
                <tr>
                  <td>
                    <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $var->id }}">
                    <input type="text" name="variants[{{ $i }}][size]"
                      value="{{ old('variants.'.$i.'.size', $var->size) }}"
                      class="form-control form-control-sm" placeholder="Size">
                  </td>
                  <td>
                    <input type="text" name="variants[{{ $i }}][color]"
                      value="{{ old('variants.'.$i.'.color', $var->color) }}"
                      class="form-control form-control-sm" placeholder="Color">
                  </td>
                  <td>
                    <input type="text" name="variants[{{ $i }}][material]"
                      value="{{ old('variants.'.$i.'.material', $var->material) }}"
                      class="form-control form-control-sm" placeholder="Material">
                  </td>
                  <td>
                    <input type="number" name="variants[{{ $i }}][price]"
                      value="{{ old('variants.'.$i.'.price', $var->price) }}"
                      class="form-control form-control-sm" step="0.01" min="0">
                  </td>
                  <td class="text-center">
                    @if($var->image)
                      <img src="{{ asset('storage/'.$var->image) }}" class="rounded mb-1" style="width:48px;height:48px;object-fit:cover;">
                    @endif
                    <input type="file" name="variants[{{ $i }}][image]" class="form-control form-control-sm">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

    {{-- Variants Generator --}}
    <div class="card mb-4">
      <div class="card-header">Generate New Variants</div>
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
                <input class="form-check-input" type="checkbox" name="color_checkboxes[]" id="col-{{ $n }}" value="{{ $n }}">
                <label class="form-check-label" for="col-{{ $n }}">
                  <span class="d-inline-block rounded-circle border" style="width:1rem;height:1rem;background:{{ $h }};"></span> {{ $n }}
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

    {{-- Digital Fields --}}
    <div class="collapse mb-4" id="digital-fields">
      <div class="card">
        <div class="card-header">Digital Product Details</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Downloadable File</label>
            <input type="file" name="download_file" class="form-control" accept=".pdf,.zip,.mp3,.mp4">
          </div>
          <div class="mb-3">
            <label class="form-label">Download Limit</label>
            <input type="number" name="download_limit"
                   value="{{ old('download_limit', $product->download_limit) }}"
                   class="form-control" min="1">
            <div class="form-text">Max times buyer can download.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Access Expiry (days)</label>
            <input type="number" name="access_expiry"
                   value="{{ old('access_expiry', $product->access_expiry) }}"
                   class="form-control" min="1">
            <div class="form-text">Days link remains active.</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Description --}}
    <div class="card mb-4">
      <div class="card-body">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea>
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
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="draft"    @selected(old('status', $product->status)=='draft')>Draft</option>
              <option value="active"   @selected(old('status', $product->status)=='active')>Active</option>
              <option value="archived" @selected(old('status', $product->status)=='archived')>Archived</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Product Images</label>
            <input type="file" name="images[]" multiple class="form-control" accept="image/*">
            @if($product->media->count())
              <div class="mt-2 d-flex flex-wrap gap-2">
                @foreach($product->media as $m)
                  <img src="{{ asset('storage/'.$m->url) }}" class="rounded" style="width:64px;height:64px;object-fit:cover;">
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-success btn-lg">Update Product</button>
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
  // Slug sync
  const nameEl = document.getElementById('name');
  const slugEl = document.getElementById('slug');
  const preview = document.getElementById('slug-preview');
  nameEl.addEventListener('input', () => {
    let s = nameEl.value.toLowerCase()
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/^-+|-+$/g,'');
    slugEl.value = s;
    preview.textContent = s;
  });

  // Toggle digital fields collapse
  const prodType = document.getElementById('product_type');
  prodType.addEventListener('change', () => {
    let digi = new bootstrap.Collapse(document.getElementById('digital-fields'), {
      toggle: prodType.value==='digital'
    });
  });

  // Cartesian helper
  function cartesian(arr){
    return arr.reduce((a,b)=>a.flatMap(x=>b.map(y=>x.concat([y]))),[[]]);
  }

  // Generate variants
  document.getElementById('generate-variants').addEventListener('click', ()=>{
    // same logic as before...
    const sizes = document.getElementById('sizes').value.split(',').map(s=>s.trim()).filter(Boolean);
    const colors = Array.from(document.querySelectorAll('input[name="color_checkboxes[]"]:checked')).map(i=>i.value);
    const materials = document.getElementById('materials').value.split(',').map(s=>s.trim()).filter(Boolean);
    const opts = [sizes,colors,materials].filter(o=>o.length);
    if(!opts.length){
      document.getElementById('variants-table-wrapper').innerHTML =
        '<div class="text-danger">Please enter at least one option.</div>';
      return;
    }
    const variants = cartesian(opts);
    let html = `<table class="table table-bordered mb-3"><thead><tr>`;
    if(sizes.length)    html += '<th>Size</th>';
    if(colors.length)   html += '<th>Color</th>';
    if(materials.length)html += '<th>Material</th>';
    html += '<th>SKU</th><th>Price</th><th>Image</th></tr></thead><tbody>';
    variants.forEach((v,i)=>{
      html += '<tr>';
      let idx=0;
      if(sizes.length){
        html+=`<td><input type="hidden" name="variants[${i}][size]" value="${v[idx]||''}">${v[idx++]||''}</td>`;
      }
      if(colors.length){
        html+=`<td><input type="hidden" name="variants[${i}][color]" value="${v[idx]||''}">${v[idx++]||''}</td>`;
      }
      if(materials.length){
        html+=`<td><input type="hidden" name="variants[${i}][material]" value="${v[idx]||''}">${v[idx++]||''}</td>`;
      }
      html+=`<td><em>Auto</em></td>
             <td><input type="number" name="variants[${i}][price]" class="form-control form-control-sm" step="0.01" min="0" required></td>
             <td><input type="file" name="variants[${i}][image]" class="form-control form-control-sm"></td>`;
      html+='</tr>';
    });
    html += '</tbody></table>';
    document.getElementById('variants-table-wrapper').innerHTML = html;
  });
</script>
@endsection
