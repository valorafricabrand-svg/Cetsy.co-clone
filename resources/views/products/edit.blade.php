@extends('layouts.app')

@section('content')
<!-- Include SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Edit Listing</h2>
                <a href="{{ route('products.index') }}" class="btn btn-outline-dark">
                    <i class="fas fa-arrow-left me-1"></i> Back to Products
                </a>
            </div>

            <!-- Flash & Errors -->
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <strong>Fix the following errors:</strong>
                    <ul class="mt-2 mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Main Product Update Form --}}
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="card shadow rounded-4 border-0 p-4 mb-5">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Product Name</label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}" required autofocus>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Type -->
                <div class="mb-3">
                    <label for="type" class="form-label fw-semibold">Product Type</label>
                    <select name="type" id="type"
                            class="form-select @error('type') is-invalid @enderror" required>
                        <option value="physical" @selected(old('type', $product->type) == 'physical')>Physical</option>
                        <option value="digital" @selected(old('type', $product->type) == 'digital')>Digital Download</option>
                        <option value="service" @selected(old('type', $product->type) == 'service')>Service</option>
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="6">{{ old('description', $product->description) }}</textarea>
                    @error('description') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Price & Discount -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label fw-semibold">Price (KES)</label>
                        <input type="number" name="price" id="price"
                               class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price', $product->price) }}" required min="0" step="0.01">
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="discount_price" class="form-label fw-semibold">Discount Price</label>
                        <input type="number" name="discount_price" id="discount_price"
                               class="form-control @error('discount_price') is-invalid @enderror"
                               value="{{ old('discount_price', $product->discount_price) }}" min="0" step="0.01">
                        @error('discount_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Stock -->
                <div class="mb-3 mt-3" id="stockSection">
                    <label for="stock" class="form-label fw-semibold">Stock Quantity</label>
                    <input type="number" name="stock" id="stock"
                           class="form-control @error('stock') is-invalid @enderror"
                           value="{{ old('stock', $product->stock) }}" min="0" step="1">
                    @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Digital File Upload -->
                <div class="mb-4" id="digitalFileSection" style="display: none;">
                    <label for="digital_file" class="form-label fw-semibold">Replace Digital File</label>
                    <input type="file" name="digital_file" id="digital_file"
                           class="form-control @error('digital_file') is-invalid @enderror"
                           accept=".zip,.pdf,.mp3,.mp4,.docx,.xlsx,.pptx">
                    @error('digital_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Shipping Profiles (Only show if type is physical) -->
                <div class="mb-4" id="shippingProfilesSection" style="display:none;">
                    <label class="form-label fw-semibold">Shipping Profiles <small class="text-muted">(Select one or more)</small></label>

                    <div class="row g-3">
                        @foreach($shippingProfiles as $profile)
                        <div class="col-12 col-md-6">
                            <div class="card shadow-sm border rounded p-3 d-flex flex-row align-items-center">
                                <div class="form-check flex-shrink-0 me-3" style="min-width: 24px;">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="shipping_profiles[]"
                                        value="{{ $profile->id }}"
                                        id="shipping_profile_{{ $profile->id }}"
                                        {{ in_array($profile->id, old('shipping_profiles', $assignedProfiles ?? [])) ? 'checked' : '' }}
                                    >
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-semibold" for="shipping_profile_{{ $profile->id }}">
                                        {{ $profile->name }}
                                    </label>
                                    <div class="text-muted small">
                                        KES {{ number_format($profile->base_rate, 2) }} &middot; {{ $profile->delivery_days }} day{{ $profile->delivery_days > 1 ? 's' : '' }}
                                        @if($profile->pickup_available)
                                            <span class="badge bg-success ms-2">Pickup</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-check ms-3 flex-shrink-0" style="min-width: 70px;">
                                    <input
                                        type="radio"
                                        class="form-check-input"
                                        name="default_shipping_profile"
                                        value="{{ $profile->id }}"
                                        id="default_shipping_profile_{{ $profile->id }}"
                                        {{ old('default_shipping_profile', $defaultProfileId) == $profile->id ? 'checked' : '' }}
                                        {{ !in_array($profile->id, old('shipping_profiles', $assignedProfiles ?? [])) ? 'disabled' : '' }}
                                        aria-label="Set default shipping profile for {{ $profile->name }}"
                                    >
                                    <label class="form-check-label small" for="default_shipping_profile_{{ $profile->id }}">Default</label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @error('shipping_profiles') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    @error('default_shipping_profile') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                </div>

                <!-- Save Button -->
                <div class="d-grid mt-4">
                    <button class="btn btn-success btn-lg rounded-pill" type="submit">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>

            {{-- Existing Digital Files --}}
            @if($product->digitalFiles->count())
                <div class="card shadow-sm border rounded-4 mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center rounded-top-4">
                        <h5 class="mb-0">Current Digital Files</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($product->digitalFiles as $file)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ Storage::url($file->filepath) }}" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-file-download me-2"></i>{{ $file->filename }}
                                    </a>
                                    <form action="{{ route('digital-files.destroy', $file) }}" method="POST" onsubmit="return confirm('Delete this digital file?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Existing Images Card --}}
            <div class="card shadow-sm border rounded-4 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center rounded-top-4">
                    <h5 class="mb-0">Current Images</h5>
                </div>
                <div class="card-body">
                    @if($product->media->count())
                        <div class="row g-3 mb-3">
                            @foreach($product->media as $media)
                                <div class="col-6 col-sm-4 col-md-3">
                                    <div class="card h-100 shadow-sm border">
                                        <img 
                                            src="{{ asset('storage/' . $media->url) }}" 
                                            class="card-img-top object-fit-cover" 
                                            style="height: 140px; cursor: pointer;" 
                                            alt="Image"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#imageModal" 
                                            data-img-url="{{ asset('storage/' . $media->url) }}"
                                            data-img-alt="{{ $product->name }}"
                                        >

                                        <div class="card-footer p-2 text-center">
                                            <form action="{{ route('media.destroy', $media) }}" method="POST" onsubmit="return confirm('Delete this image?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                    <i class="fas fa-trash"></i> Remove
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

            {{-- New Image Upload & Preview Card --}}
            <div
              x-data="imageUploadSortable()"
              class="card shadow-sm border rounded-4"
            >
              <div
                class="card-header bg-light d-flex justify-content-between align-items-center rounded-top-4"
              >
                <h5 class="mb-0">Upload & Preview New Images</h5>
              </div>

              <form action="{{ route('media.upload', $product) }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                <div
                  class="border rounded p-3 mb-3 text-center"
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
                    name="media[]"
                  />
                </div>

                <template x-if="previews.length === 0">
                  <p class="text-muted">No images selected yet.</p>
                </template>

                <div class="row g-3 mb-3" id="previewList" style="min-height: 120px;">
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
                          class="position-absolute bottom-0 start-0 bg-dark bg-opacity-50 text-white px-1 small text-truncate"
                          style="max-width: 100%;"
                          x-text="file.name"
                        ></div>
                      </div>
                    </div>
                  </template>
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn btn-primary rounded-pill">
                    <i class="fas fa-upload me-1"></i> Upload Images
                  </button>
                </div>
              </form>
            </div>

        </div>
    </div>
</div>

{{-- Image Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0 shadow-none">
      <div class="modal-body p-0">
        <img src="" id="modalImage" class="w-100 rounded" alt="Large view">
      </div>
      <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
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

    // Toggle stock/digital file inputs & shipping profiles based on product type
    document.getElementById('type').addEventListener('change', function () {
        const digital = document.getElementById('digitalFileSection');
        const stock = document.getElementById('stockSection');
        const shippingProfiles = document.getElementById('shippingProfilesSection');

        if (this.value === 'digital') {
            digital.style.display = 'block';
            stock.style.display = 'none';
            shippingProfiles.style.display = 'none';
        } else if (this.value === 'service') {
            digital.style.display = 'none';
            stock.style.display = 'none';
            shippingProfiles.style.display = 'none';
        } else if (this.value === 'physical') {
            digital.style.display = 'none';
            stock.style.display = 'block';
            shippingProfiles.style.display = 'block';
        } else {
            digital.style.display = 'none';
            stock.style.display = 'none';
            shippingProfiles.style.display = 'none';
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
        document.getElementById('type').dispatchEvent(new Event('change'));
    });

    // Modal image show handler
    var imageModal = document.getElementById('imageModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
        var img = event.relatedTarget;
        var imgSrc = img.getAttribute('data-img-url');
        var imgAlt = img.getAttribute('data-img-alt') || '';
        var modalImage = imageModal.querySelector('#modalImage');

        modalImage.src = imgSrc;
        modalImage.alt = imgAlt;
    });

    // Alpine.js component for image upload previews and sortable
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
        };
    }
</script>
@endsection
