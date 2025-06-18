@extends('layouts.app')

@section('content')
<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="content">
  <div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

      <!-- Flash Messages -->
      @foreach (['success', 'info', 'warning', 'danger'] as $msg)
        @if(session()->has($msg))
          <div class="alert alert-{{ $msg }} alert-dismissible fade show rounded-3" role="alert">
            {{ session($msg) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
      @endforeach

      <!-- Validation Errors -->
      @if ($errors->any())
        <div class="alert alert-danger rounded-3">
          <strong><i class="fas fa-exclamation-circle me-1"></i> Please fix the following errors:</strong>
          <ul class="mt-2 mb-0 ps-3">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="card shadow rounded-4 border-0">
        <div class="card-header bg-success text-white rounded-top-4">
          <h4 class="mb-0">Create New Product</h4>
        </div>
        <div class="card-body p-4">
          <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" x-data="imageUploadSortable()" @submit="prepareFiles">
            @csrf

            <!-- Name -->
            <div class="mb-3">
              <label for="name" class="form-label fw-semibold">Product Name</label>
              <input type="text" name="name" id="name"
                     class="form-control form-control-lg @error('name') is-invalid @enderror"
                     value="{{ old('name') }}" placeholder="e.g., Handmade Wooden Spoon" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Type -->
            <div class="mb-3">
              <label for="type" class="form-label fw-semibold">Product Type</label>
              <select name="type" id="type"
                      class="form-select form-select-lg @error('type') is-invalid @enderror" required>
                <option value="">Select product type</option>
                <option value="physical" @selected(old('type') === 'physical')>Physical</option>
                <option value="digital" @selected(old('type') === 'digital')>Digital Download</option>
                <option value="service" @selected(old('type') === 'service')>Service</option>
              </select>
              @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
              <label for="description" class="form-label fw-semibold">Description</label>
              <textarea name="description" id="description" class="form-control"
                        rows="6" placeholder="Write a compelling product description...">{{ old('description') }}</textarea>
              @error('description') <div class="text-danger mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Price & Discount -->
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="price" class="form-label fw-semibold">Price (KES)</label>
                <input type="number" name="price" id="price"
                       class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price') }}" placeholder="e.g., 2500" required>
                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6 mb-3">
                <label for="discount_price" class="form-label fw-semibold">Discount Price (Optional)</label>
                <input type="number" name="discount_price" id="discount_price"
                       class="form-control @error('discount_price') is-invalid @enderror"
                       value="{{ old('discount_price') }}" placeholder="e.g., 2000">
                @error('discount_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>

            <!-- Stock -->
            <div class="mb-3" id="stockSection">
              <label for="stock" class="form-label fw-semibold">Stock Quantity</label>
              <input type="number" name="stock" id="stock"
                     class="form-control @error('stock') is-invalid @enderror"
                     value="{{ old('stock') }}" placeholder="e.g., 10">
              @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Digital File -->
            <div class="mb-4" id="digitalFileSection" style="display: none;">
              <label for="digital_file" class="form-label fw-semibold">Upload Digital File</label>
              <input type="file" name="digital_file" id="digital_file"
                     class="form-control @error('digital_file') is-invalid @enderror"
                     accept=".zip,.pdf,.mp3,.mp4,.docx,.xlsx,.pptx">
              <div class="form-text">Upload the downloadable product file (max 10MB)</div>
              @error('digital_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Drag & Drop File Upload with Preview & Reorder -->
            <div
              class="border rounded p-3 mb-4 text-center"
              @drop.prevent="handleDrop($event)"
              @dragover.prevent
              style="cursor: pointer;"
              @click="$refs.fileInput.click()"
            >
              <p class="mb-0 text-muted">
                Drag & drop images here or click to select files
              </p>
              <input
                type="file"
                multiple
                accept="image/*"
                class="d-none"
                x-ref="fileInput"
                @change="handleFiles($event.target.files)"
              />
            </div>

            <template x-if="previews.length === 0">
              <p class="text-muted">No images selected yet.</p>
            </template>

            <div class="row g-3 mb-4" id="previewList" style="min-height: 120px;">
              <template x-for="(file, index) in previews" :key="file.id">
                <div class="col-6 col-sm-4 col-md-3">
                  <div
                    class="position-relative border rounded-3 overflow-hidden"
                    style="height: 140px;"
                    x-bind:title="file.name"
                  >
                    <img
                      x-bind:src="file.url"
                      class="w-100 h-100 object-fit-cover"
                      alt="Preview"
                    />
                    <button
                      type="button"
                      class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                      @click.prevent="removeFile(index)"
                      title="Remove image"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                    <div
                      class="position-absolute bottom-0 start-0 bg-dark bg-opacity-50 text-white px-1 small"
                      style="max-width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;"
                      x-text="file.name"
                    ></div>
                  </div>
                </div>
              </template>
            </div>

            <!-- Hidden inputs for files to be sent -->
            <template x-for="file in previews" :key="file.id">
              <input type="file" :ref="'file_' + file.id" name="media[]" x-bind:files="file.fileObject" class="d-none" />
            </template>

            <!-- Submit -->
            <div class="d-grid">
              <button class="btn btn-success btn-lg rounded-pill" type="submit">
                <i class="fas fa-check-circle me-2"></i>Publish Product
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
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

  document.getElementById('type').addEventListener('change', function () {
    const digital = document.getElementById('digitalFileSection');
    const stock = document.getElementById('stockSection');

    if (this.value === 'digital') {
      digital.style.display = 'block';
      stock.style.display = 'none';
    } else if (this.value === 'service') {
      digital.style.display = 'none';
      stock.style.display = 'none';
    } else {
      digital.style.display = 'none';
      stock.style.display = 'block';
    }
  });

  window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('type').dispatchEvent(new Event('change'));
  });

  function imageUploadSortable() {
    return {
      previews: [],
      idCounter: 0,
      sortable: null,
      handleFiles(files) {
        for (let i = 0; i < files.length; i++) {
          this.previewFile(files[i]);
        }
      },
      previewFile(file) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.previews.push({
            id: this.idCounter++,
            url: e.target.result,
            name: file.name,
            fileObject: file,
          });
          this.$nextTick(() => this.initSortable());
        };
        reader.readAsDataURL(file);
      },
      removeFile(index) {
        this.previews.splice(index, 1);
      },
      handleDrop(event) {
        const dt = event.dataTransfer;
        if (dt.files.length) {
          this.handleFiles(dt.files);
        }
      },
      initSortable() {
        if (this.sortable) this.sortable.destroy();

        this.sortable = Sortable.create(
          document.getElementById('previewList'),
          {
            animation: 150,
            onEnd: (evt) => {
              const movedItem = this.previews.splice(evt.oldIndex, 1)[0];
              this.previews.splice(evt.newIndex, 0, movedItem);
            },
          }
        );
      },
      prepareFiles(event) {
        // Because <input type="file"> is read-only,
        // we cannot assign files programmatically here,
        // so just submit normally (files are included).
        // This function is a placeholder for custom upload if needed.
      }
    }
  }
</script>
@endsection
