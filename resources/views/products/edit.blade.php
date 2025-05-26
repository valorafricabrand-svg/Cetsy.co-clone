@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl py-10"
     x-data="{ name: '{{ addslashes($product->name) }}', slug: '{{ addslashes($product->slug) }}' }"
     @input.debounce.500ms="
       slug = name.toLowerCase()
                  .replace(/[^a-z0-9]+/g,'-')
                  .replace(/(^-|-$)/g,'');
     ">
  <h2 class="text-3xl font-bold mb-8 text-center">Edit Product</h2>

  @if($errors->any())
    <div class="mb-6 p-4 bg-red-100 text-red-700 rounded">
      <ul class="list-disc pl-5 mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf @method('PUT')

    <!-- Name & Slug (do not touch this logic) -->
    <div class="mb-4">
      <label for="name" class="block font-medium mb-1">Name</label>
      <input id="name" type="text" x-model="name" name="name" value="{{ old('name', $product->name) }}"
             class="w-full border rounded px-3 py-2" required>
    </div>
    <div class="mb-4">
      <label for="slug" class="block font-medium mb-1">Slug</label>
      <input id="slug" type="text" x-model="slug" name="slug" value="{{ old('slug', $product->slug) }}"
             class="w-full border rounded px-3 py-2" readonly>
      <small class="text-gray-500">
        URL: <code>{{ url('products') }}/<span x-text="slug"></span></code>
      </small>
    </div>

    <!-- Group the rest of the fields into cards -->
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-xl font-semibold mb-4">Product Details</h3>
      <div class="mb-4">
        <label for="category_id" class="block font-medium mb-1">Category</label>
        <select id="category_id" name="category_id" class="w-full border rounded px-3 py-2">
          <option value="">-- Select Category --</option>
          @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id)==$category->id)>
              {{ $category->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="product_type" class="block font-medium mb-1">Product Type</label>
          <select id="product_type" name="product_type" class="w-full border rounded px-3 py-2" required>
            <option value="">-- Select Type --</option>
            <option value="physical" @selected(old('product_type', $product->product_type)=='physical')>Physical</option>
            <option value="digital" @selected(old('product_type', $product->product_type)=='digital')>Digital</option>
          </select>
        </div>
        <div>
          <label for="condition" class="block font-medium mb-1">Condition</label>
          <select id="condition" name="condition" class="w-full border rounded px-3 py-2" required>
            <option value="">-- Select Condition --</option>
            <option value="new" @selected(old('condition', $product->condition)=='new')>New</option>
            <option value="refurbished" @selected(old('condition', $product->condition)=='refurbished')>Refurbished</option>
            <option value="used" @selected(old('condition', $product->condition)=='used')>Used</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-xl font-semibold mb-4">Pricing & Inventory</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="price" class="block font-medium mb-1">Selling Price (KES)</label>
          <input id="price" type="number" name="price" value="{{ old('price', $product->price) }}"
                 class="w-full border rounded px-3 py-2" min="0" step="0.01" required>
        </div>
        <div>
          <label for="discount_price" class="block font-medium mb-1">Price after discount (KES)</label>
          <input id="discount_price" type="number" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}"
                 class="w-full border rounded px-3 py-2" min="0" step="0.01" required>
        </div>
        <div>
          <label for="stock" class="block font-medium mb-1">Stock</label>
          <input id="stock" type="number" name="stock" value="{{ old('stock', $product->stock) }}"
                 class="w-full border rounded px-3 py-2" min="0" required>
        </div>
        <div>
          <label for="low_stock" class="block font-medium mb-1">Low Stock Alert</label>
          <input id="low_stock" type="number" name="low_stock" value="{{ old('low_stock', $product->low_stock) }}"
                 class="w-full border rounded px-3 py-2" min="0" required>
        </div>
      </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-xl font-semibold mb-4">Variants & Customization</h3>
      @if($variants && $variants->count())
        <div class="mb-6">
          <h4 class="font-semibold mb-2">Edit Existing Variants</h4>
          <table class="w-full border mb-2">
            <thead>
              <tr>
                <th class="border px-2 py-1">Size</th>
                <th class="border px-2 py-1">Color</th>
                <th class="border px-2 py-1">Material</th>
                <th class="border px-2 py-1">Price</th>
                <th class="border px-2 py-1">Image</th>
              </tr>
            </thead>
            <tbody>
              @foreach($variants as $i => $variant)
                <tr class="{{ $i % 2 == 0 ? 'bg-gray-50' : '' }}">
                  <td class="border px-2 py-2">
                    <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant->id }}">
                    <input type="text" name="variants[{{ $i }}][size]" value="{{ old('variants.'.$i.'.size', $variant->size) }}"
                      class="w-full border rounded px-2 py-1" placeholder="Size">
                  </td>
                  <td class="border px-2 py-2">
                    <input type="text" name="variants[{{ $i }}][color]" value="{{ old('variants.'.$i.'.color', $variant->color) }}"
                      class="w-full border rounded px-2 py-1" placeholder="Color">
                  </td>
                  <td class="border px-2 py-2">
                    <input type="text" name="variants[{{ $i }}][material]" value="{{ old('variants.'.$i.'.material', $variant->material) }}"
                      class="w-full border rounded px-2 py-1" placeholder="Material">
                  </td>
                  <td class="border px-2 py-2">
                    <input type="number" name="variants[{{ $i }}][price]" value="{{ old('variants.'.$i.'.price', $variant->price) }}"
                      class="w-full border rounded px-2 py-1" min="0" step="0.01" placeholder="Price">
                  </td>
                  <td class="border px-2 py-2 text-center">
                    @if($variant->image)
                      <img src="{{ asset('storage/' . $variant->image) }}" class="w-12 h-12 object-cover rounded mb-1 mx-auto border">
                    @endif
                    <input type="file" name="variants[{{ $i }}][image]" accept="image/*" class="block w-full text-xs">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
      <div class="mb-2">
        <label>Sizes</label>
        <input type="text" id="sizes" class="w-full border rounded px-3 py-2 mb-1" placeholder="e.g., Small, Medium, Large (comma separated)">
      </div>
      <div class="mb-2">
        <label>Colors</label>
        <div id="color-options" class="flex flex-wrap gap-2">
          @php
            $colors = [
              'Red' => '#ff0000', 'Blue' => '#0000ff', 'Black' => '#000000', 'White' => '#ffffff',
              'Green' => '#008000', 'Yellow' => '#ffff00', 'Purple' => '#800080', 'Orange' => '#ffa500',
              'Pink' => '#ffc0cb', 'Gray' => '#808080', 'Brown' => '#a52a2a', 'Cyan' => '#00ffff'
            ];
          @endphp
          @foreach($colors as $name => $hex)
            <label class="flex items-center space-x-1 cursor-pointer">
              <input type="checkbox" name="color_checkboxes[]" value="{{ $name }}">
              <span class="inline-block w-5 h-5 rounded" style="background: {{ $hex }}; border: 1px solid #ccc;"></span>
              <span>{{ $name }}</span>
            </label>
          @endforeach
        </div>
      </div>
      <div class="mb-2">
        <label>Materials</label>
        <input type="text" id="materials" class="w-full border rounded px-3 py-2 mb-1" placeholder="e.g., Cotton, Leather (comma separated)">
      </div>
      <button type="button" id="generate-variants" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded mb-3">Generate Variants</button>
      <div id="variants-table-wrapper"></div>
    </div>

    <div id="digital-fields" class="bg-white shadow rounded-lg p-6" style="display: none;">
      <h3 class="text-xl font-semibold mb-4">Digital Product Details</h3>
      <div class="mb-2">
        <label for="download_file">Downloadable File</label>
        <input type="file" id="download_file" name="download_file" class="w-full border rounded px-3 py-2" accept=".pdf,.zip,.rar,.epub,.mobi,.exe,.dmg,.mp3,.mp4,.avi,.mov,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
      </div>
      <div class="mb-2">
        <label for="download_limit">Download Limit</label>
        <input type="number" id="download_limit" name="download_limit" class="w-full border rounded px-3 py-2" min="1" placeholder="e.g., 5" value="{{ old('download_limit', $product->download_limit) }}">
        <small class="text-gray-500">Maximum number of times a buyer can download the file.</small>
      </div>
      <div class="mb-2">
        <label for="access_expiry">Access Expiry (days)</label>
        <input type="number" id="access_expiry" name="access_expiry" class="w-full border rounded px-3 py-2" min="1" placeholder="e.g., 30" value="{{ old('access_expiry', $product->access_expiry) }}">
        <small class="text-gray-500">Number of days the download link remains active after purchase.</small>
      </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <label for="productDescription" class="block font-medium mb-1">Description</label>
      <textarea id="textarea" name="description" class="form-control @error('description') is-invalid @enderror w-full border rounded px-3 py-2">
        {{ old('description', $product->description) }}
      </textarea>
      @error('description')
        <span class="invalid-feedback">{{ $message }}</span>
      @enderror
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <div class="mb-4">
        <label for="status" class="block font-medium mb-1">Status</label>
        <select id="status" name="status" class="w-full border rounded px-3 py-2" required>
          <option value="draft"   @selected(old('status', $product->status)=='draft')>Draft</option>
          <option value="active"  @selected(old('status', $product->status)=='active')>Active</option>
          <option value="archived"@selected(old('status', $product->status)=='archived')>Archived</option>
        </select>
      </div>
      <div>
        <label for="images" class="block font-medium mb-1">Product Images</label>
        <input id="images" type="file" name="images[]" multiple accept="image/*" class="w-full">
        @if($product->media->count())
          <div class="flex flex-wrap gap-2 mt-2">
            @foreach($product->media as $media)
              <img src="{{ asset('storage/' . $media->url) }}"
                   class="w-24 h-24 object-cover rounded">
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit"
              class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow font-semibold text-lg">
        Update Product
      </button>
    </div>
  </form>
</div>

<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
<script type="text/javascript">
    tinymce.init({
        selector: "textarea#textarea",
        plugins: "image advcode link lists media table code wordcount fullscreen",
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | link image media | code fullscreen",
        menubar: "file edit view insert format tools table help",
        height: 400,
        image_title: true,
        automatic_uploads: true,
        promotion: false,
        branding: false,
        file_picker_types: 'image',
        file_picker_callback: function (cb, value, meta) {
            let input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = function () {
                let file = this.files[0];
                let reader = new FileReader();
                reader.onload = function () {
                    let id = 'blobid' + (new Date()).getTime();
                    let blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    let base64 = reader.result.split(',')[1];
                    let blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        },
    });
</script>

<script>
function cartesian(arr) {
  return arr.reduce(function(a, b) {
    return a.flatMap(d => b.map(e => [ ...d, e ]));
  }, [[]]);
}

document.getElementById('generate-variants').addEventListener('click', function() {
  const sizes = document.getElementById('sizes').value.split(',').map(s => s.trim()).filter(Boolean);
  const colors = Array.from(document.querySelectorAll('input[name=\"color_checkboxes[]\"]:checked')).map(cb => cb.value);
  const materials = document.getElementById('materials').value.split(',').map(s => s.trim()).filter(Boolean);

  let options = [];
  if (sizes.length) options.push(sizes);
  if (colors.length) options.push(colors);
  if (materials.length) options.push(materials);

  if (options.length === 0) {
    document.getElementById('variants-table-wrapper').innerHTML = '<p class=\"text-red-500\">Please enter at least one option.</p>';
    return;
  }

  const variants = cartesian(options);

  let table = `<table class=\"w-full border mb-2\"><thead>
    <tr>
      ${sizes.length ? '<th class=\"border px-2 py-1\">Size</th>' : ''}
      ${colors.length ? '<th class=\"border px-2 py-1\">Color</th>' : ''}
      ${materials.length ? '<th class=\"border px-2 py-1\">Material</th>' : ''}
      <th class=\"border px-2 py-1\">SKU</th>
      <th class=\"border px-2 py-1\">Price</th>
      <th class=\"border px-2 py-1\">Image</th>
    </tr>
    </thead><tbody>`;

  const sizeIdx = options.indexOf(sizes);
  const colorIdx = options.indexOf(colors);
  const materialIdx = options.indexOf(materials);

  variants.forEach((variant, i) => {
    table += '<tr>';
    if (sizes.length) table += `<td class="border px-2 py-1"><input type="hidden" name="variants[${i}][size]" value="${variant[sizeIdx] || ''}">${variant[sizeIdx] || ''}</td>`;
    if (colors.length) table += `<td class="border px-2 py-1"><input type="hidden" name="variants[${i}][color]" value="${variant[colorIdx] || ''}">${variant[colorIdx] || ''}</td>`;
    if (materials.length) table += `<td class="border px-2 py-1"><input type="hidden" name="variants[${i}][material]" value="${variant[materialIdx] || ''}">${variant[materialIdx] || ''}</td>`;
    table += `<td class=\"border px-2 py-1\"><span class=\"text-gray-400 italic\">Auto</span></td>`;
    table += `
      <td class="border px-2 py-1"><input type="number" name="variants[${i}][price]" class="w-full border rounded px-2 py-1" min="0" step="0.01" required></td>
      <td class="border px-2 py-1"><input type="file" name="variants[${i}][image]" accept="image/*"></td>
    </tr>`;
  });

  table += '</tbody></table>';
  document.getElementById('variants-table-wrapper').innerHTML = table;
});
</script>

<script>
document.getElementById('product_type').addEventListener('change', function() {
  const digitalFields = document.getElementById('digital-fields');
  if (this.value === 'digital') {
    digitalFields.style.display = '';
  } else {
    digitalFields.style.display = 'none';
  }
});
</script>
@endsection
