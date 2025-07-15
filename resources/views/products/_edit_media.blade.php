{{-- resources/views/products/_edit_media.blade.php --}}

{{-- 1 ▸ Existing digital downloads ------------------------------------ --}}
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
          <form action="{{ route('digital-files.destroy', $file) }}"
                method="POST"
                onsubmit="return confirm('Delete this file?');">
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

{{-- 2 ▸ Existing images gallery --------------------------------------- --}}
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
              <img src="{{ asset('storage/'.$media->url) }}"
                   class="card-img-top"
                   style="height:140px;object-fit:cover;"
                   data-bs-toggle="modal"
                   data-bs-target="#imageModal"
                   data-img-url="{{ asset('storage/'.$media->url) }}">
              <div class="card-footer text-center py-2">

                {{-- Feature / Un-feature --}}
                <form action="{{ route('products.setFeaturedImage', $product) }}"
                      method="POST" class="d-inline">
                  @csrf @method('PATCH')
                  <input type="hidden" name="featured_image" value="{{ $media->url }}">
                  <button type="submit"
                          class="btn btn-sm {{ $product->featured_image === $media->url
                              ? 'btn-outline-warning'
                              : 'btn-outline-success' }}">
                    {{ $product->featured_image === $media->url ? 'Featured' : 'Make as primary image' }}
                  </button>
                </form>

                {{-- Delete --}}
                <form action="{{ route('media.destroy', $media) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Remove image?');">
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

{{-- 3 ▸ Upload + live preview (Alpine + Sortable) ---------------------- --}}
<div x-data="imageUploadSortable()" class="card shadow-sm">
  <div class="card-header bg-light">
    <h5 class="mb-0">Upload &amp; Preview New Images</h5>
  </div>
  <form action="{{ route('media.upload', $product) }}"
        method="POST" enctype="multipart/form-data" class="card-body">
    @csrf
    <div class="border border-dashed rounded-3 py-5 text-center mb-4"
         style="cursor:pointer;"
         @click="$refs.fileInput.click()"
         @drop.prevent="handleDrop"
         @dragover.prevent>
      <p class="text-muted mb-0">Drag &amp; drop images here or click to select</p>
      <input type="file" multiple accept="image/*" class="d-none" x-ref="fileInput"
             @change="handleFiles($event.target.files)" name="media[]">
    </div>

    <template x-if="previews.length">
      <div class="row g-3 mb-4" id="previewList">
        <template x-for="(file,i) in previews" :key="file.id">
          <div class="col-6 col-sm-4 col-md-3">
            <div class="position-relative rounded overflow-hidden" style="height:140px;">
              <img :src="file.url" class="w-100 h-100 object-fit-cover">
              <button type="button"
                      class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
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

{{-- 4 ▸ Modal for image preview --------------------------------------- --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 bg-transparent">
      <div class="modal-body p-0">
        <img src="" id="modalImage" class="w-100 rounded">
      </div>
      <button type="button"
              class="btn-close position-absolute top-0 end-0 m-3"
              data-bs-dismiss="modal"></button>
    </div>
  </div>
</div>
